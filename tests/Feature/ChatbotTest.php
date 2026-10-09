<?php

use App\Models\Category;
use App\Models\ChatMessage;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockOut;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\ChatbotService;
use App\Services\ChatbotToolService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Gate;

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
        'batch_number' => 'BATCH-' . uniqid(),
        'location' => 'Warehouse A',
    ]);
}

function chatUser(): User
{
    return User::factory()->staff()->create();
}
/*
|--------------------------------------------------------------------------
| ChatbotService
|--------------------------------------------------------------------------
*/

it('returns the reply and saves both messages', function () {
    Http::fake(['openrouter.ai/*' => Http::response(chatTextResponse('Hello!'))]);
    $user = User::factory()->staff()->create();

    $result = app(ChatbotService::class)->ask($user, 'Hi');

    expect($result['success'])->toBeTrue()
        ->and($result['message'])->toBe('Hello!');

    $this->assertDatabaseHas('chat_messages', ['user_id' => $user->id, 'role' => 'user', 'content' => 'Hi']);
    $this->assertDatabaseHas('chat_messages', ['user_id' => $user->id, 'role' => 'assistant', 'content' => 'Hello!']);
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
            ->push(chatTextResponse('There are no low stock products right now.')),
    ]);
    $user = User::factory()->staff()->create();

    $result = app(ChatbotService::class)->ask($user, 'Which products are low in stock?');

    expect($result['success'])->toBeTrue()
        ->and($result['message'])->toBe('There are no low stock products right now.');

    Http::assertSentCount(2);
    Http::assertSent(fn ($request) => collect($request['messages'])->contains('role', 'tool'));
});

it('returns a friendly message on rate limit and saves nothing', function () {
    Http::fake(['openrouter.ai/*' => Http::response(['error' => 'rate limited'], 429)]);
    $user = User::factory()->staff()->create();

    $result = app(ChatbotService::class)->ask($user, 'Hi');

    expect($result['success'])->toBeFalse()
        ->and($result['message'])->toContain('rate limit');

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
    $result = app(ChatbotToolService::class)->execute(chatUser(), 'does_not_exist', []);

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

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_product_stock', ['product_name' => 'Urea']);

    expect($result['found'])->toBeTrue()
        ->and($result['products'])->toHaveCount(1)
        ->and($result['products'][0]['total_stock'])->toBe(35.0)
        ->and($result['products'][0]['unit'])->toBe('Bag (50kg)');
});

it('does not return archived products in the stock lookup', function () {
    Product::factory()->create(['name' => 'Old Urea', 'status' => 'Archived']);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_product_stock', ['product_name' => 'Old Urea']);

    expect($result['found'])->toBeFalse();
});

it('does not leak an archived product through a SKU match', function () {
    Product::factory()->create(['name' => 'Something Else', 'sku' => 'URE-999', 'status' => 'Archived']);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_product_stock', ['product_name' => 'URE-999']);

    expect($result['found'])->toBeFalse();
});

it('lists only active products at or below the reorder point', function () {
    $low = Product::factory()->create(['name' => 'Low Item', 'status' => 'Active', 'minimum_stock' => 5, 'reorder_point' => 20]);
    $healthy = Product::factory()->create(['name' => 'Healthy Item', 'status' => 'Active', 'minimum_stock' => 5, 'reorder_point' => 20]);
    $archived = Product::factory()->create(['name' => 'Archived Item', 'status' => 'Archived', 'minimum_stock' => 5, 'reorder_point' => 20]);

    chatAddStock($low, 10);
    chatAddStock($healthy, 100);
    chatAddStock($archived, 1);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_low_stock_products', []);

    expect($result['count'])->toBe(1)
        ->and($result['products'][0]['name'])->toBe('Low Item');
});

it('returns zero low stock products when nothing needs reorder', function () {
    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_low_stock_products', []);

    expect($result['count'])->toBe(0);
});

it('finds active suppliers by category name', function () {
    $category = Category::factory()->create(['name' => 'Fertilizers', 'status' => 'Active']);
    $supplier = Supplier::factory()->create(['company_name' => 'Yara Fertilizers Inc.', 'status' => 'Active']);
    $supplier->categories()->attach($category->id);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'find_suppliers', ['keyword' => 'Fertilizer']);

    expect($result['found'])->toBeTrue()
        ->and($result['suppliers'][0]['company_name'])->toBe('Yara Fertilizers Inc.')
        ->and($result['suppliers'][0]['categories'])->toBe(['Fertilizers']);
});

it('finds suppliers by product name through the product category', function () {
    $category = Category::factory()->create(['name' => 'Fertilizers', 'status' => 'Active']);
    Product::factory()->create(['name' => 'Urea Fertilizer 50kg', 'category_id' => $category->id, 'status' => 'Active']);
    $supplier = Supplier::factory()->create(['company_name' => 'Yara Fertilizers Inc.', 'status' => 'Active']);
    $supplier->categories()->attach($category->id);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'find_suppliers', ['keyword' => 'Urea']);

    expect($result['found'])->toBeTrue()
        ->and($result['suppliers'][0]['company_name'])->toBe('Yara Fertilizers Inc.');
});

it('does not return archived suppliers', function () {
    $category = Category::factory()->create(['name' => 'Seeds', 'status' => 'Active']);
    $supplier = Supplier::factory()->create(['status' => 'Archived']);
    $supplier->categories()->attach($category->id);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'find_suppliers', ['keyword' => 'Seeds']);

    expect($result['found'])->toBeFalse();
});

it('does not match suppliers through an archived category', function () {
    $category = Category::factory()->create(['name' => 'Pesticides', 'status' => 'Archived']);
    $supplier = Supplier::factory()->create(['status' => 'Active']);
    $supplier->categories()->attach($category->id);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'find_suppliers', ['keyword' => 'Pesticides']);

    expect($result['found'])->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Tool permissions
|--------------------------------------------------------------------------
*/

it('only references abilities that exist in config/abilities.php', function () {
    foreach (ChatbotToolService::TOOL_ABILITIES as $tool => $ability) {
        expect(array_key_exists($ability, config('abilities')))->toBeTrue("{$tool} uses an unknown ability: {$ability}");
    }
});

it('offers the user every tool they are allowed to use', function () {
    $names = collect(app(ChatbotToolService::class)->definitions(chatUser()))
        ->pluck('function.name')
        ->all();

    expect($names)->toBe(['get_product_stock', 'get_low_stock_products', 'find_suppliers', 'get_expiry_status', 'get_recent_movements', 'get_product_details', 'get_stock_by_location', 'get_inventory_summary',]);
});

it('hides a tool when the user lacks its ability', function () {
    Gate::define('suppliers.view', fn () => false);

    $names = collect(app(ChatbotToolService::class)->definitions(chatUser()))
        ->pluck('function.name')
        ->all();

    expect($names)->toContain('get_product_stock')
        ->and($names)->not->toContain('find_suppliers');
});

it('refuses to run a tool the user lacks the ability for', function () {
    Gate::define('suppliers.view', fn () => false);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'find_suppliers', ['keyword' => 'Seeds']);

    expect($result)->toBe(['error' => 'You do not have access to this information.']);
});

it('sends only the allowed tools to the model', function () {
    Gate::define('suppliers.view', fn () => false);
    Http::fake(['openrouter.ai/*' => Http::response(chatTextResponse('ok'))]);

    app(ChatbotService::class)->ask(chatUser(), 'Hi');

    Http::assertSent(function ($request) {
        $names = collect($request['tools'])->pluck('function.name');

        return $names->contains('get_product_stock') && ! $names->contains('find_suppliers');
    });
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

/*
|--------------------------------------------------------------------------
| Out of stock
|--------------------------------------------------------------------------
*/

it('can list only the products that are completely out of stock', function () {
    Product::factory()->create(['name' => 'Empty Item', 'status' => 'Active', 'minimum_stock' => 5, 'reorder_point' => 20]);
    $low = Product::factory()->create(['name' => 'Low Item', 'status' => 'Active', 'minimum_stock' => 5, 'reorder_point' => 20]);
    chatAddStock($low, 10);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_low_stock_products', ['only_out_of_stock' => true]);

    expect($result['count'])->toBe(1)
        ->and($result['products'][0]['name'])->toBe('Empty Item');
});

it('does not filter products when only_out_of_stock is the string false', function () {
    Product::factory()->create(['name' => 'Empty Item', 'status' => 'Active', 'minimum_stock' => 5, 'reorder_point' => 20]);
    $low = Product::factory()->create(['name' => 'Low Item', 'status' => 'Active', 'minimum_stock' => 5, 'reorder_point' => 20]);
    chatAddStock($low, 10);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_low_stock_products', ['only_out_of_stock' => 'false']);

    expect($result['count'])->toBe(2);
});

/*
|--------------------------------------------------------------------------
| Expiry status
|--------------------------------------------------------------------------
*/

it('lists expired and expiring batches that still have stock', function () {
    $product = Product::factory()->create(['name' => 'Urea Fertilizer', 'status' => 'Active', 'expiry_track' => true]);

    Inventory::factory()->for($product)->create(['batch_number' => 'B-EXPIRED', 'expiry_date' => now()->subDays(3), 'remaining_quantity' => 5]);
    Inventory::factory()->for($product)->create(['batch_number' => 'B-SOON', 'expiry_date' => now()->addDays(10), 'remaining_quantity' => 8]);
    Inventory::factory()->for($product)->create(['batch_number' => 'B-SAFE', 'expiry_date' => now()->addDays(200), 'remaining_quantity' => 8]);
    Inventory::factory()->for($product)->create(['batch_number' => 'B-DEPLETED', 'expiry_date' => now()->subDays(3), 'remaining_quantity' => 0]);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_expiry_status', []);

    expect($result['summary']['expired'])->toBe(1)
        ->and($result['summary']['expiring_within_30_days'])->toBe(1)
        ->and($result['expired']['total'])->toBe(1)
        ->and($result['expired']['items'][0]['batch_number'])->toBe('B-EXPIRED')
        ->and($result['expired']['items'][0]['days_overdue'])->toBe(3)
        ->and($result['expiring_soon']['items'][0]['batch_number'])->toBe('B-SOON')
        ->and($result['expiring_soon']['items'][0]['days_left'])->toBe(10);
});

it('returns only the requested expiry group', function () {
    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_expiry_status', ['status' => 'expired']);

    expect($result)->toHaveKey('expired')
        ->and($result)->not->toHaveKey('expiring_soon');
});

it('ignores products that do not track expiry', function () {
    $product = Product::factory()->create(['status' => 'Active', 'expiry_track' => false]);
    Inventory::factory()->for($product)->create(['expiry_date' => now()->subDays(3), 'remaining_quantity' => 5]);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_expiry_status', []);

    expect($result['summary']['expired'])->toBe(0);
});

it('refuses the expiry tool when the user lacks the reports.expiry ability', function () {
    Gate::define('reports.expiry', fn () => false);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_expiry_status', []);

    expect($result)->toBe(['error' => 'You do not have access to this information.']);
});

/*
|--------------------------------------------------------------------------
| Recent movements
|--------------------------------------------------------------------------
*/

it('returns stock in and stock out movements with who recorded them', function () {
    $staff = User::factory()->staff()->create(['name' => 'Staff One']);
    $manager = User::factory()->manager()->create(['name' => 'Manager One']);
    $product = Product::factory()->create(['name' => 'Urea Fertilizer']);

    Inventory::factory()->for($product)->create([
        'user_id' => $manager->id,
        'quantity' => 50,
        'remaining_quantity' => 50,
        'batch_number' => 'B-1',
        'created_at' => now()->subHour(),
    ]);
    StockOut::factory()->create(['product_id' => $product->id, 'user_id' => $staff->id, 'quantity' => 5, 'reason' => 'Sale']);

    $result = app(ChatbotToolService::class)->execute($staff, 'get_recent_movements', []);

    expect($result['total'])->toBe(2)
        ->and($result['movements'][0]['type'])->toBe('Stock Out')
        ->and($result['movements'][0]['done_by'])->toBe('Staff One')
        ->and($result['movements'][0]['quantity'])->toBe(-5.0)
        ->and($result['movements'][1]['type'])->toBe('Stock In')
        ->and($result['movements'][1]['done_by'])->toBe('Manager One');
});

it('filters movements by type', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    Inventory::factory()->for($product)->create(['user_id' => $staff->id]);
    StockOut::factory()->create(['product_id' => $product->id, 'user_id' => $staff->id]);

    $result = app(ChatbotToolService::class)->execute($staff, 'get_recent_movements', ['type' => 'stock-out']);

    expect($result['total'])->toBe(1)
        ->and($result['movements'][0]['type'])->toBe('Stock Out');
});

it('filters movements by product search', function () {
    $staff = User::factory()->staff()->create();
    $urea = Product::factory()->create(['name' => 'Urea Fertilizer']);
    $hose = Product::factory()->create(['name' => 'Garden Hose']);

    StockOut::factory()->create(['product_id' => $urea->id, 'user_id' => $staff->id]);
    StockOut::factory()->create(['product_id' => $hose->id, 'user_id' => $staff->id]);

    $result = app(ChatbotToolService::class)->execute($staff, 'get_recent_movements', ['type' => 'stock-out', 'search' => 'Urea']);

    expect($result['total'])->toBe(1)
        ->and($result['movements'][0]['product'])->toBe('Urea Fertilizer');
});

it('can limit movements to the ones the current user recorded', function () {
    $staff = User::factory()->staff()->create();
    $other = User::factory()->staff()->create();
    $product = Product::factory()->create();

    StockOut::factory()->create(['product_id' => $product->id, 'user_id' => $staff->id, 'quantity' => 1]);
    StockOut::factory()->create(['product_id' => $product->id, 'user_id' => $other->id, 'quantity' => 2]);

    $result = app(ChatbotToolService::class)->execute($staff, 'get_recent_movements', ['type' => 'stock-out', 'only_mine' => true]);

    expect($result['total'])->toBe(1)
        ->and($result['movements'][0]['done_by'])->toBe($staff->name);
});

it('limits movements to the last N days', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    StockOut::factory()->create(['product_id' => $product->id, 'user_id' => $staff->id, 'created_at' => now()->subDays(10)]);
    StockOut::factory()->create(['product_id' => $product->id, 'user_id' => $staff->id, 'created_at' => now()]);

    $result = app(ChatbotToolService::class)->execute($staff, 'get_recent_movements', ['type' => 'stock-out', 'days' => 7]);

    expect($result['total'])->toBe(1);
});

it('includes adjustments, like the inventory history page does', function () {
    $staff = User::factory()->staff()->create();
    $manager = User::factory()->manager()->create(['name' => 'Manager One']);
    $inventory = Inventory::factory()->for(Product::factory()->create())->create(['quantity' => 20, 'remaining_quantity' => 20]);

    StockAdjustment::factory()->create([
        'inventory_id' => $inventory->id,
        'user_id' => $manager->id,
        'system_quantity' => 20,
        'actual_quantity' => 18,
    ]);

    $result = app(ChatbotToolService::class)->execute($staff, 'get_recent_movements', ['type' => 'adjustment']);

    expect($result['total'])->toBe(1)
        ->and($result['movements'][0]['type'])->toBe('Adjustment')
        ->and($result['movements'][0]['quantity'])->toBe(-2.0)
        ->and($result['movements'][0]['done_by'])->toBe('Manager One');
});

it('pages through movements 15 at a time', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    StockOut::factory()->count(16)->create(['product_id' => $product->id, 'user_id' => $staff->id]);

    $page1 = app(ChatbotToolService::class)->execute($staff, 'get_recent_movements', ['type' => 'stock-out']);
    $page2 = app(ChatbotToolService::class)->execute($staff, 'get_recent_movements', ['type' => 'stock-out', 'page' => 2]);

    expect($page1['movements'])->toHaveCount(15)
        ->and($page1['has_more'])->toBeTrue()
        ->and($page2['movements'])->toHaveCount(1)
        ->and($page2['has_more'])->toBeFalse();
});

it('offers managers every tool, including the value and movement summary', function () {
    $names = collect(app(ChatbotToolService::class)->definitions(User::factory()->manager()->create()))
        ->pluck('function.name')
        ->all();

    expect($names)->toHaveCount(10)
        ->and($names)->toContain('get_inventory_value')
        ->and($names)->toContain('get_movement_summary');
});

/*
|--------------------------------------------------------------------------
| Product details
|--------------------------------------------------------------------------
*/

it('returns the details of an active product including prices', function () {
    $category = Category::factory()->create(['name' => 'Fertilizers']);
    $unit = Unit::factory()->create(['name' => 'Bag (50kg)']);
    $product = Product::factory()->create([
        'name' => 'Urea Fertilizer 50kg',
        'category_id' => $category->id,
        'unit_id' => $unit->id,
        'cost_price' => 50,
        'selling_price' => 80,
        'minimum_stock' => 10,
        'reorder_point' => 20,
        'expiry_track' => true,
        'status' => 'Active',
    ]);
    chatAddStock($product, 35);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_product_details', ['product_name' => 'Urea']);

    expect($result['found'])->toBeTrue()
        ->and($result['products'][0]['category'])->toBe('Fertilizers')
        ->and($result['products'][0]['unit'])->toBe('Bag (50kg)')
        ->and($result['products'][0]['cost_price'])->toBe(50.0)
        ->and($result['products'][0]['selling_price'])->toBe(80.0)
        ->and($result['products'][0]['tracks_expiry'])->toBeTrue()
        ->and($result['products'][0]['total_stock'])->toBe(35.0);
});

it('does not return archived products in the product details', function () {
    Product::factory()->create(['name' => 'Old Urea', 'status' => 'Archived']);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_product_details', ['product_name' => 'Old Urea']);

    expect($result['found'])->toBeFalse();
});

it('requires a product name for the product details', function () {
    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_product_details', []);

    expect($result)->toHaveKey('error');
});

/*
|--------------------------------------------------------------------------
| Stock by location
|--------------------------------------------------------------------------
*/

it('summarizes stock per storage location and ignores depleted batches', function () {
    $a = Product::factory()->create(['status' => 'Active']);
    $b = Product::factory()->create(['status' => 'Active']);

    Inventory::factory()->for($a)->create(['location' => 'Main Warehouse', 'remaining_quantity' => 5]);
    Inventory::factory()->for($b)->create(['location' => 'Main Warehouse', 'remaining_quantity' => 5]);
    Inventory::factory()->for($a)->create(['location' => 'Storage Room A', 'remaining_quantity' => 5]);
    Inventory::factory()->for($a)->create(['location' => 'Storage Room B', 'remaining_quantity' => 0]);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_stock_by_location', []);
    $locations = collect($result['locations'])->keyBy('location');

    expect($locations->keys()->all())->toBe(['Main Warehouse', 'Storage Room A'])
        ->and($locations['Main Warehouse']['products'])->toBe(2)
        ->and($locations['Main Warehouse']['batches'])->toBe(2)
        ->and($locations['Storage Room A']['products'])->toBe(1);
});

it('shows what is stored in a location', function () {
    $unit = Unit::factory()->create(['name' => 'Piece']);
    $nozzles = Product::factory()->create(['name' => 'Spray Nozzles', 'unit_id' => $unit->id, 'status' => 'Active']);
    $hose = Product::factory()->create(['name' => 'Garden Hose', 'status' => 'Active']);

    Inventory::factory()->for($nozzles)->create(['location' => 'Storage Room A', 'remaining_quantity' => 12]);
    Inventory::factory()->for($nozzles)->create(['location' => 'Storage Room A', 'remaining_quantity' => 3]);
    Inventory::factory()->for($hose)->create(['location' => 'Main Warehouse', 'remaining_quantity' => 9]);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_stock_by_location', ['location' => 'Storage Room A']);

    expect($result['found'])->toBeTrue()
        ->and($result['items'])->toHaveCount(1)
        ->and($result['items'][0]['product'])->toBe('Spray Nozzles')
        ->and($result['items'][0]['quantity'])->toBe(15.0)
        ->and($result['items'][0]['unit'])->toBe('Piece')
        ->and($result['items'][0]['batches'])->toBe(2);
});

it('shows where a product is stored', function () {
    $product = Product::factory()->create(['name' => 'Urea Fertilizer', 'status' => 'Active']);

    Inventory::factory()->for($product)->create(['location' => 'Storage Room A', 'remaining_quantity' => 4]);
    Inventory::factory()->for($product)->create(['location' => 'Main Warehouse', 'remaining_quantity' => 6]);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_stock_by_location', ['product_name' => 'Urea']);

    expect(collect($result['items'])->pluck('location')->all())->toBe(['Main Warehouse', 'Storage Room A'])
        ->and(collect($result['items'])->pluck('quantity')->all())->toBe([6.0, 4.0]);
});

it('does not list archived products in stock by location', function () {
    $product = Product::factory()->create(['status' => 'Archived']);
    Inventory::factory()->for($product)->create(['location' => 'Main Warehouse', 'remaining_quantity' => 5]);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_stock_by_location', ['location' => 'Main Warehouse']);

    expect($result['found'])->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Inventory summary
|--------------------------------------------------------------------------
*/

it('summarizes the inventory for staff without the value', function () {
    $inStock = Product::factory()->create(['status' => 'Active', 'reorder_point' => 20]);
    $low = Product::factory()->create(['status' => 'Active', 'reorder_point' => 20]);
    Product::factory()->create(['status' => 'Active', 'reorder_point' => 20]);
    chatAddStock($inStock, 100);
    chatAddStock($low, 10);
    Supplier::factory()->create(['status' => 'Active']);

    $result = app(ChatbotToolService::class)->execute(chatUser(), 'get_inventory_summary', []);

    expect($result['products'])->toBe(['total_active' => 3, 'in_stock' => 1, 'low_stock' => 1, 'out_of_stock' => 1])
        ->and($result['active_suppliers'])->toBe(1)
        ->and($result)->toHaveKey('expiry')
        ->and($result)->not->toHaveKey('inventory_value');
});

it('includes the inventory value in the summary for managers', function () {
    $manager = User::factory()->manager()->create();
    $product = Product::factory()->create(['status' => 'Active', 'cost_price' => 50]);
    chatAddStock($product, 10);

    $result = app(ChatbotToolService::class)->execute($manager, 'get_inventory_summary', []);

    expect($result['inventory_value'])->toBe(500.0);
});

/*
|--------------------------------------------------------------------------
| Inventory value
|--------------------------------------------------------------------------
*/

it('returns the current inventory value for managers', function () {
    $manager = User::factory()->manager()->create();
    chatAddStock(Product::factory()->create(['cost_price' => 50]), 10);
    chatAddStock(Product::factory()->create(['cost_price' => 20]), 5);

    $result = app(ChatbotToolService::class)->execute($manager, 'get_inventory_value', []);

    expect($result['current_value'])->toBe(600.0)
        ->and($result)->not->toHaveKey('monthly_trend');
});

it('includes the monthly trend when asked', function () {
    $manager = User::factory()->manager()->create();
    chatAddStock(Product::factory()->create(['cost_price' => 50]), 10);

    $result = app(ChatbotToolService::class)->execute($manager, 'get_inventory_value', ['include_monthly_trend' => true]);

    expect($result['monthly_trend']['months'])->toHaveCount(6)
        ->and($result['monthly_trend']['months'][5]['value'])->toBe(500.0);
});

it('does not give staff the inventory value or the movement summary', function () {
    $tools = app(ChatbotToolService::class);
    $staff = chatUser();
    $error = ['error' => 'You do not have access to this information.'];

    expect($tools->execute($staff, 'get_inventory_value', []))->toBe($error)
        ->and($tools->execute($staff, 'get_movement_summary', []))->toBe($error);
});

it('lets managers and admins use the manager-only tools', function () {
    $tools = app(ChatbotToolService::class);

    foreach ([User::factory()->manager()->create(), User::factory()->admin()->create()] as $user) {
        expect($tools->execute($user, 'get_inventory_value', []))->toHaveKey('current_value')
            ->and($tools->execute($user, 'get_movement_summary', []))->toHaveKey('total_received');
    }
});

/*
|--------------------------------------------------------------------------
| Movement summary
|--------------------------------------------------------------------------
*/

it('summarizes movements for all time', function () {
    $manager = User::factory()->manager()->create();
    $product = Product::factory()->create(['name' => 'Urea Fertilizer']);
    $inventory = Inventory::factory()->for($product)->create(['quantity' => 50, 'remaining_quantity' => 30]);
    StockOut::factory()->create(['product_id' => $product->id, 'user_id' => $manager->id, 'quantity' => 20]);
    StockAdjustment::factory()->create(['inventory_id' => $inventory->id, 'user_id' => $manager->id]);

    $result = app(ChatbotToolService::class)->execute($manager, 'get_movement_summary', []);

    expect($result['range'])->toBe('all')
        ->and($result['total_received'])->toBe(50.0)
        ->and($result['total_consumed'])->toBe(20.0)
        ->and($result['adjustments_count'])->toBe(1)
        ->and($result['top_products_by_consumed'][0])->toBe(['product' => 'Urea Fertilizer', 'received' => 50.0, 'consumed' => 20.0]);
});

it('limits the movement summary to the chosen range', function () {
    $manager = User::factory()->manager()->create();
    $product = Product::factory()->create();
    Inventory::factory()->for($product)->create(['quantity' => 40, 'remaining_quantity' => 40, 'created_at' => now()->subDays(40)]);
    Inventory::factory()->for($product)->create(['quantity' => 10, 'remaining_quantity' => 10]);

    $tools = app(ChatbotToolService::class);

    $recent = $tools->execute($manager, 'get_movement_summary', ['range' => '30d']);
    $custom = $tools->execute($manager, 'get_movement_summary', [
        'start_date' => now()->subDays(50)->toDateString(),
        'end_date' => now()->subDays(30)->toDateString(),
    ]);

    expect($recent['total_received'])->toBe(10.0)
        ->and($custom['range'])->toBe('custom')
        ->and($custom['total_received'])->toBe(40.0);
});

it('rejects an invalid date in the movement summary', function () {
    $manager = User::factory()->manager()->create();

    $result = app(ChatbotToolService::class)->execute($manager, 'get_movement_summary', ['start_date' => 'not-a-date']);

    expect($result)->toBe(['error' => 'Invalid date. Use the format YYYY-MM-DD.']);
});
