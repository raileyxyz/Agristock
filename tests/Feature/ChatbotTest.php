<?php

use App\Models\Category;
use App\Models\ChatMessage;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\ChatbotService;
use App\Services\ChatbotToolService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.openrouter.key' => 'test-key',
        'services.openrouter.model' => 'test-model',
    ]);

    Http::preventStrayRequests();
});

function chatTextResponse(string $content): array
{
    return [
        'choices' => [
            ['message' => ['role' => 'assistant', 'content' => $content]],
        ],
    ];
}

function chatToolCallResponse(string $tool = 'get_low_stock_products', string $arguments = '{}'): array
{
    return [
        'choices' => [
            [
                'message' => [
                    'role' => 'assistant',
                    'content' => null,
                    'tool_calls' => [
                        [
                            'id' => 'call_1',
                            'type' => 'function',
                            'function' => ['name' => $tool, 'arguments' => $arguments],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

function chatAddStock(Product $product, float $quantity): Inventory
{
    return Inventory::create([
        'product_id' => $product->id,
        'user_id' => User::factory()->staff()->create()->id,
        'quantity' => $quantity,
        'remaining_quantity' => $quantity,
    ]);
}

/*
|--------------------------------------------------------------------------
| ChatbotService
|--------------------------------------------------------------------------
*/

it('returns the reply and saves both messages', function () {
    Http::fake(['openrouter.ai/*' => Http::response(chatTextResponse('Hello po!'))]);
    $user = User::factory()->staff()->create();

    $result = app(ChatbotService::class)->ask($user, 'Hi');

    expect($result['success'])->toBeTrue()
        ->and($result['message'])->toBe('Hello po!');

    $this->assertDatabaseHas('chat_messages', ['user_id' => $user->id, 'role' => 'user', 'content' => 'Hi']);
    $this->assertDatabaseHas('chat_messages', ['user_id' => $user->id, 'role' => 'assistant', 'content' => 'Hello po!']);
});

it('strips markdown bold from the reply', function () {
    Http::fake(['openrouter.ai/*' => Http::response(chatTextResponse('**Yara** Fertilizers'))]);
    $user = User::factory()->staff()->create();

    $result = app(ChatbotService::class)->ask($user, 'Suppliers?');

    expect($result['message'])->toBe('Yara Fertilizers');
});

it('runs a tool call and then returns the final answer', function () {
    Http::fake([
        'openrouter.ai/*' => Http::sequence()
            ->push(chatToolCallResponse())
            ->push(chatTextResponse('Walang low stock ngayon.')),
    ]);
    $user = User::factory()->staff()->create();

    $result = app(ChatbotService::class)->ask($user, 'Which products are low in stock?');

    expect($result['success'])->toBeTrue()
        ->and($result['message'])->toBe('Walang low stock ngayon.');

    Http::assertSentCount(2);
    Http::assertSent(fn ($request) => collect($request['messages'])->contains('role', 'tool'));
});

it('returns a friendly message on rate limit and saves nothing', function () {
    Http::fake(['openrouter.ai/*' => Http::response(['error' => 'rate limited'], 429)]);
    $user = User::factory()->staff()->create();

    $result = app(ChatbotService::class)->ask($user, 'Hi');

    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('Busy');

    $this->assertDatabaseCount('chat_messages', 0);
});

it('handles an OpenRouter server error and saves nothing', function () {
    Http::fake(['openrouter.ai/*' => Http::response(['error' => 'boom'], 500)]);
    $user = User::factory()->staff()->create();

    $result = app(ChatbotService::class)->ask($user, 'Hi');

    expect($result['success'])->toBeFalse();
    $this->assertDatabaseCount('chat_messages', 0);
});

it('stops after the max number of tool rounds', function () {
    Http::fake(['openrouter.ai/*' => Http::response(chatToolCallResponse())]);
    $user = User::factory()->staff()->create();

    $result = app(ChatbotService::class)->ask($user, 'Hi');

    expect($result['success'])->toBeFalse();
    Http::assertSentCount(4); // MAX_TOOL_ROUNDS
    $this->assertDatabaseCount('chat_messages', 0);
});

it('sends only the last 10 history messages to the model', function () {
    Http::fake(['openrouter.ai/*' => Http::response(chatTextResponse('ok'))]);
    $user = User::factory()->staff()->create();

    foreach (range(1, 15) as $i) {
        ChatMessage::create(['user_id' => $user->id, 'role' => 'user', 'content' => "msg {$i}"]);
    }

    app(ChatbotService::class)->ask($user, 'latest');

    Http::assertSent(fn ($request) => count($request['messages']) === 12);
});

/*
|--------------------------------------------------------------------------
| ChatbotToolService
|--------------------------------------------------------------------------
*/

it('returns an error array for an unknown tool', function () {
    $result = app(ChatbotToolService::class)->execute('does_not_exist', []);

    expect($result)->toHaveKey('error');
});

it('returns the total stock and unit of a product across batches', function () {
    $unit = Unit::factory()->create(['name' => 'Bag (50kg)']);
    $product = Product::factory()->create([
        'name' => 'Urea Fertilizer 50kg',
        'unit_id' => $unit->id,
        'status' => 'Active',
        'minimum_stock' => 10,
        'reorder_point' => 20,
    ]);
    chatAddStock($product, 30);
    chatAddStock($product, 5);

    $result = app(ChatbotToolService::class)->execute('get_product_stock', ['product_name' => 'Urea']);

    expect($result['found'])->toBeTrue()
        ->and($result['products'])->toHaveCount(1)
        ->and($result['products'][0]['total_stock'])->toBe(35.0)
        ->and($result['products'][0]['unit'])->toBe('Bag (50kg)');
});

it('does not return archived products in the stock lookup', function () {
    Product::factory()->create(['name' => 'Old Urea', 'status' => 'Archived']);

    $result = app(ChatbotToolService::class)->execute('get_product_stock', ['product_name' => 'Old Urea']);

    expect($result['found'])->toBeFalse();
});

it('does not leak an archived product through a SKU match', function () {
    Product::factory()->create(['name' => 'Something Else', 'sku' => 'URE-999', 'status' => 'Archived']);

    $result = app(ChatbotToolService::class)->execute('get_product_stock', ['product_name' => 'URE-999']);

    expect($result['found'])->toBeFalse();
});

it('lists only active products at or below the reorder point', function () {
    $low = Product::factory()->create(['name' => 'Low Item', 'status' => 'Active', 'minimum_stock' => 5, 'reorder_point' => 20]);
    $healthy = Product::factory()->create(['name' => 'Healthy Item', 'status' => 'Active', 'minimum_stock' => 5, 'reorder_point' => 20]);
    $archived = Product::factory()->create(['name' => 'Archived Item', 'status' => 'Archived', 'minimum_stock' => 5, 'reorder_point' => 20]);

    chatAddStock($low, 10);
    chatAddStock($healthy, 100);
    chatAddStock($archived, 1);

    $result = app(ChatbotToolService::class)->execute('get_low_stock_products', []);

    expect($result['count'])->toBe(1)
        ->and($result['products'][0]['name'])->toBe('Low Item');
});

it('returns zero low stock products when nothing needs reorder', function () {
    $result = app(ChatbotToolService::class)->execute('get_low_stock_products', []);

    expect($result['count'])->toBe(0);
});

it('finds active suppliers by category name', function () {
    $category = Category::factory()->create(['name' => 'Fertilizers', 'status' => 'Active']);
    $supplier = Supplier::factory()->create(['company_name' => 'Yara Fertilizers Inc.', 'status' => 'Active']);
    $supplier->categories()->attach($category->id);

    $result = app(ChatbotToolService::class)->execute('find_suppliers', ['keyword' => 'Fertilizer']);

    expect($result['found'])->toBeTrue()
        ->and($result['suppliers'][0]['company_name'])->toBe('Yara Fertilizers Inc.')
        ->and($result['suppliers'][0]['categories'])->toBe(['Fertilizers']);
});

it('finds suppliers by product name through the product category', function () {
    $category = Category::factory()->create(['name' => 'Fertilizers', 'status' => 'Active']);
    Product::factory()->create(['name' => 'Urea Fertilizer 50kg', 'category_id' => $category->id, 'status' => 'Active']);
    $supplier = Supplier::factory()->create(['company_name' => 'Yara Fertilizers Inc.', 'status' => 'Active']);
    $supplier->categories()->attach($category->id);

    $result = app(ChatbotToolService::class)->execute('find_suppliers', ['keyword' => 'Urea']);

    expect($result['found'])->toBeTrue()
        ->and($result['suppliers'][0]['company_name'])->toBe('Yara Fertilizers Inc.');
});

it('does not return archived suppliers', function () {
    $category = Category::factory()->create(['name' => 'Seeds', 'status' => 'Active']);
    $supplier = Supplier::factory()->create(['status' => 'Archived']);
    $supplier->categories()->attach($category->id);

    $result = app(ChatbotToolService::class)->execute('find_suppliers', ['keyword' => 'Seeds']);

    expect($result['found'])->toBeFalse();
});

it('does not match suppliers through an archived category', function () {
    $category = Category::factory()->create(['name' => 'Pesticides', 'status' => 'Archived']);
    $supplier = Supplier::factory()->create(['status' => 'Active']);
    $supplier->categories()->attach($category->id);

    $result = app(ChatbotToolService::class)->execute('find_suppliers', ['keyword' => 'Pesticides']);

    expect($result['found'])->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Controller / routes
|--------------------------------------------------------------------------
*/

it('blocks guests from the chatbot endpoints', function () {
    $this->getJson(route('chatbot.messages.index'))->assertUnauthorized();
    $this->postJson(route('chatbot.messages.store'), ['message' => 'Hi'])->assertUnauthorized();
    $this->deleteJson(route('chatbot.messages.destroy'))->assertUnauthorized();
});

it('returns a reply for an authenticated user', function () {
    Http::fake(['openrouter.ai/*' => Http::response(chatTextResponse('Hello!'))]);
    $user = User::factory()->staff()->create();

    $this->actingAs($user)
        ->postJson(route('chatbot.messages.store'), ['message' => 'Hi'])
        ->assertOk()
        ->assertJson(['success' => true, 'message' => 'Hello!']);
});

it('validates the chatbot message', function () {
    $user = User::factory()->staff()->create();

    $this->actingAs($user)
        ->postJson(route('chatbot.messages.store'), ['message' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors('message');

    $this->actingAs($user)
        ->postJson(route('chatbot.messages.store'), ['message' => str_repeat('a', 501)])
        ->assertStatus(422)
        ->assertJsonValidationErrors('message');
});

it('returns 502 when the AI request fails', function () {
    Http::fake(['openrouter.ai/*' => Http::response([], 429)]);
    $user = User::factory()->staff()->create();

    $this->actingAs($user)
        ->postJson(route('chatbot.messages.store'), ['message' => 'Hi'])
        ->assertStatus(502)
        ->assertJson(['success' => false]);
});

it('returns only the user\'s own messages in order', function () {
    $user = User::factory()->staff()->create();
    $other = User::factory()->staff()->create();

    ChatMessage::create(['user_id' => $user->id, 'role' => 'user', 'content' => 'first']);
    ChatMessage::create(['user_id' => $user->id, 'role' => 'assistant', 'content' => 'second']);
    ChatMessage::create(['user_id' => $other->id, 'role' => 'user', 'content' => 'not mine']);

    $this->actingAs($user)
        ->getJson(route('chatbot.messages.index'))
        ->assertOk()
        ->assertJsonCount(2, 'messages')
        ->assertJsonPath('messages.0.content', 'first')
        ->assertJsonPath('messages.1.content', 'second');
});

it('clears only the user\'s own messages', function () {
    $user = User::factory()->staff()->create();
    $other = User::factory()->staff()->create();

    ChatMessage::create(['user_id' => $user->id, 'role' => 'user', 'content' => 'mine']);
    ChatMessage::create(['user_id' => $other->id, 'role' => 'user', 'content' => 'theirs']);

    $this->actingAs($user)->deleteJson(route('chatbot.messages.destroy'))->assertOk();

    $this->assertDatabaseMissing('chat_messages', ['content' => 'mine']);
    $this->assertDatabaseHas('chat_messages', ['content' => 'theirs']);
});
