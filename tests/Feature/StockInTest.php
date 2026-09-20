<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Inventory;

it('redirects guests to login', function () {
    $product = Product::factory()->create();

    $response = $this->post('/inventories', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 20,
    ]);

    $response->assertRedirect('/login');
});

it('allows staff to record a stock in', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create(['expiry_track' => false]);

    $response = $this->actingAs($staff)->post('/inventories', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 50,
    ]);

    $response->assertRedirect(route('inventories.create'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('inventories', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 50,
        'remaining_quantity' => 50,
        'user_id' => $staff->id,
    ]);
});

it('allows manager to record a stock in', function () {
    $manager = User::factory()->manager()->create();
    $product = Product::factory()->create(['expiry_track' => false]);

    $response = $this->actingAs($manager)->post('/inventories', [
        'product_id' => $product->id,
        'location' => 'Storage Room A',
        'quantity' => 30,
    ]);

    $response->assertRedirect(route('inventories.create'));
    $response->assertSessionHasNoErrors();
});

it('auto-generates a batch number when none is provided', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create(['expiry_track' => false]);

    $this->actingAs($staff)->post('/inventories', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 10,
    ]);

    $inventory = Inventory::where('product_id', $product->id)->first();

    expect($inventory->batch_number)->not->toBeNull()
        ->and($inventory->batch_number)->toStartWith('BT-');
});

it('requires an expiry date when the product is expiry-tracked', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create(['expiry_track' => true]);

    $response = $this->actingAs($staff)->post('/inventories', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 10,
    ]);

    $response->assertSessionHasErrors('expiry_date');
    $this->assertDatabaseCount('inventories', 0);
});

it('does not require an expiry date when the product is not expiry-tracked', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create(['expiry_track' => false]);

    $response = $this->actingAs($staff)->post('/inventories', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 10,
    ]);

    $response->assertSessionHasNoErrors();
});

it('adds the product category to the supplier when stocking in with a new category', function () {
    $staff = User::factory()->staff()->create();

    $fertilizer = Category::factory()->create(['name' => 'Fertilizers']);
    $seeds = Category::factory()->create(['name' => 'Seeds']);

    $supplier = Supplier::factory()->create();
    $supplier->categories()->attach($fertilizer->id);

    $seedProduct = Product::factory()->create([
        'category_id' => $seeds->id,
        'expiry_track' => false,
    ]);

    $this->actingAs($staff)->post('/inventories', [
        'product_id' => $seedProduct->id,
        'supplier_id' => $supplier->id,
        'location' => 'Main Warehouse',
        'quantity' => 25,
    ]);

    expect($supplier->categories()->pluck('categories.id')->sort()->values()->all())
        ->toBe([$fertilizer->id, $seeds->id]);
});

it('does not duplicate the category when it is already assigned to the supplier', function () {
    $staff = User::factory()->staff()->create();

    $fertilizer = Category::factory()->create(['name' => 'Fertilizers']);
    $supplier = Supplier::factory()->create();
    $supplier->categories()->attach($fertilizer->id);

    $fertilizerProduct = Product::factory()->create([
        'category_id' => $fertilizer->id,
        'expiry_track' => false,
    ]);

    $this->actingAs($staff)->post('/inventories', [
        'product_id' => $fertilizerProduct->id,
        'supplier_id' => $supplier->id,
        'location' => 'Main Warehouse',
        'quantity' => 25,
    ]);

    expect($supplier->categories()->count())->toBe(1);
});

it('does not touch supplier categories when no supplier is selected', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create(['expiry_track' => false]);

    $response = $this->actingAs($staff)->post('/inventories', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 25,
    ]);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('inventories', [
        'product_id' => $product->id,
        'supplier_id' => null,
    ]);
});

it('notifies eligible active users but excludes the actor', function () {
    $actor = User::factory()->staff()->create();
    $manager = User::factory()->manager()->create();
    $otherStaff = User::factory()->staff()->create();

    $product = Product::factory()->create(['expiry_track' => false]);

    $this->actingAs($actor)->post('/inventories', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 15,
    ]);

    expect($actor->fresh()->unreadNotifications()->count())->toBe(0)
        ->and($manager->fresh()->unreadNotifications()->count())->toBe(1)
        ->and($otherStaff->fresh()->unreadNotifications()->count())->toBe(1);
});
