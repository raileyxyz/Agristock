<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Inventory;

it('redirects guests to login', function () {
    $product = Product::factory()->create();

    $response = $this->post('/stock-outs', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 5,
        'reason' => 'Sale',
    ]);

    $response->assertRedirect('/login');
});

it('allows staff to record a stock out and deducts inventory', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    $batch = Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse',
        'quantity' => 50,
        'remaining_quantity' => 50,
    ]);

    $response = $this->actingAs($staff)->post('/stock-outs', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 20,
        'reason' => 'Sale',
    ]);

    $response->assertRedirect(route('stock-outs.create'));
    $response->assertSessionHas('success');

    expect($batch->fresh()->remaining_quantity)->toEqual(30);

    $this->assertDatabaseHas('stock_outs', [
        'product_id' => $product->id,
        'quantity' => 20,
        'reason' => 'Sale',
        'user_id' => $staff->id,
    ]);
});

it('allows manager to record a stock out', function () {
    $manager = User::factory()->manager()->create();
    $product = Product::factory()->create();

    Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse',
        'quantity' => 50,
        'remaining_quantity' => 50,
    ]);

    $response = $this->actingAs($manager)->post('/stock-outs', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 10,
        'reason' => 'Damaged',
    ]);

    $response->assertRedirect(route('stock-outs.create'));
    $response->assertSessionHasNoErrors();
});

it('fails validation with a clear error when quantity exceeds available stock', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse',
        'quantity' => 10,
        'remaining_quantity' => 10,
    ]);

    $response = $this->actingAs($staff)->post('/stock-outs', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 999,
        'reason' => 'Sale',
    ]);

    $response->assertSessionHasErrors('quantity');

    $this->assertDatabaseCount('stock_outs', 0);
});

it('mirrors a transfer as a new inventory batch at the destination location', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    $origin = Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse',
        'quantity' => 30,
        'remaining_quantity' => 30,
    ]);

    $response = $this->actingAs($staff)->post('/stock-outs', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 15,
        'reason' => 'Transfer',
        'transfer_to' => 'Storage Room A',
    ]);

    $response->assertRedirect(route('stock-outs.create'));

    expect($origin->fresh()->remaining_quantity)->toEqual(15);

    $this->assertDatabaseHas('inventories', [
        'product_id' => $product->id,
        'location' => 'Storage Room A',
        'remaining_quantity' => 15,
    ]);
});

it('rejects a transfer to the same location as the source', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse',
        'quantity' => 30,
        'remaining_quantity' => 30,
    ]);

    $response = $this->actingAs($staff)->post('/stock-outs', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 10,
        'reason' => 'Transfer',
        'transfer_to' => 'Main Warehouse',
    ]);

    $response->assertSessionHasErrors('transfer_to');
});

it('notifies eligible active users but excludes the actor and archived users', function () {
    $actor = User::factory()->staff()->create();
    $manager = User::factory()->manager()->create();
    $otherStaff = User::factory()->staff()->create();
    $archivedManager = User::factory()->manager()->archived()->create();

    $product = Product::factory()->create();

    Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse',
        'quantity' => 50,
        'remaining_quantity' => 50,
    ]);

    $this->actingAs($actor)->post('/stock-outs', [
        'product_id' => $product->id,
        'location' => 'Main Warehouse',
        'quantity' => 5,
        'reason' => 'Sale',
    ]);

    expect($actor->fresh()->unreadNotifications()->count())->toBe(0)
        ->and($manager->fresh()->unreadNotifications()->count())->toBe(1)
        ->and($otherStaff->fresh()->unreadNotifications()->count())->toBe(1)
        ->and($archivedManager->fresh()->unreadNotifications()->count())->toBe(0);
});
