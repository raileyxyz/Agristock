<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Inventory;

it('redirects guests to login', function () {
    $inventory = Inventory::factory()->create();

    $response = $this->post('/stock-adjustments', [
        'inventory_id' => $inventory->id,
        'actual_quantity' => 10,
        'reason' => 'Physical Count',
    ]);

    $response->assertRedirect('/login');
});

it('forbids staff from making a stock adjustment', function () {
    $staff = User::factory()->staff()->create();
    $inventory = Inventory::factory()->create();

    $response = $this->actingAs($staff)->post('/stock-adjustments', [
        'inventory_id' => $inventory->id,
        'actual_quantity' => 10,
        'reason' => 'Physical Count',
    ]);

    $response->assertForbidden();
});

it('allows manager to record a stock adjustment', function () {
    $manager = User::factory()->manager()->create();

    $inventory = Inventory::factory()->create([
        'quantity' => 50,
        'remaining_quantity' => 50,
    ]);

    $response = $this->actingAs($manager)->post('/stock-adjustments', [
        'inventory_id' => $inventory->id,
        'actual_quantity' => 42,
        'reason' => 'Physical Count',
        'notes' => 'Recount after audit',
    ]);

    $response->assertRedirect(route('stock-adjustments.create'));
    $response->assertSessionHas('success');

    expect($inventory->fresh()->remaining_quantity)->toEqual(42);

    $this->assertDatabaseHas('stock_adjustments', [
        'inventory_id' => $inventory->id,
        'user_id' => $manager->id,
        'system_quantity' => 50,
        'actual_quantity' => 42,
        'reason' => 'Physical Count',
    ]);
});

it('allows admin to record a stock adjustment', function () {
    $admin = User::factory()->admin()->create();

    $inventory = Inventory::factory()->create([
        'quantity' => 20,
        'remaining_quantity' => 20,
    ]);

    $response = $this->actingAs($admin)->post('/stock-adjustments', [
        'inventory_id' => $inventory->id,
        'actual_quantity' => 0,
        'reason' => 'Theft/Loss',
    ]);

    $response->assertRedirect(route('stock-adjustments.create'));

    expect($inventory->fresh()->remaining_quantity)->toEqual(0);
});

it('rejects an invalid reason', function () {
    $manager = User::factory()->manager()->create();
    $inventory = Inventory::factory()->create();

    $response = $this->actingAs($manager)->post('/stock-adjustments', [
        'inventory_id' => $inventory->id,
        'actual_quantity' => 10,
        'reason' => 'Not A Real Reason',
    ]);

    $response->assertSessionHasErrors('reason');
    $this->assertDatabaseCount('stock_adjustments', 0);
});

it('rejects a negative actual quantity', function () {
    $manager = User::factory()->manager()->create();
    $inventory = Inventory::factory()->create();

    $response = $this->actingAs($manager)->post('/stock-adjustments', [
        'inventory_id' => $inventory->id,
        'actual_quantity' => -5,
        'reason' => 'Physical Count',
    ]);

    $response->assertSessionHasErrors('actual_quantity');
    $this->assertDatabaseCount('stock_adjustments', 0);
});

it('notifies admin and manager but not staff, and excludes the actor', function () {
    $actorManager = User::factory()->manager()->create();
    $otherManager = User::factory()->manager()->create();
    $admin = User::factory()->admin()->create();
    $staff = User::factory()->staff()->create();

    $inventory = Inventory::factory()->create([
        'quantity' => 30,
        'remaining_quantity' => 30,
    ]);

    $this->actingAs($actorManager)->post('/stock-adjustments', [
        'inventory_id' => $inventory->id,
        'actual_quantity' => 25,
        'reason' => 'Damaged Goods',
    ]);

    expect($actorManager->fresh()->unreadNotifications()->count())->toBe(0)
        ->and($otherManager->fresh()->unreadNotifications()->count())->toBe(1)
        ->and($admin->fresh()->unreadNotifications()->count())->toBe(1)
        ->and($staff->fresh()->unreadNotifications()->count())->toBe(0);
});
