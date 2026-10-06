<?php

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;

it('hides depleted batches from current stock by default', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    $inStock = Inventory::factory()->for($product)->create(['remaining_quantity' => 5]);
    Inventory::factory()->for($product)->create(['remaining_quantity' => 0]);

    $ids = $this->actingAs($staff)->get('/inventories')
        ->viewData('inventories')->getCollection()->pluck('id')->all();

    expect($ids)->toBe([$inStock->id]);
});

it('shows only depleted batches when filtering by depleted', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    Inventory::factory()->for($product)->create(['remaining_quantity' => 5]);
    $depleted = Inventory::factory()->for($product)->create(['remaining_quantity' => 0]);

    $ids = $this->actingAs($staff)->get('/inventories?stock=depleted')
        ->viewData('inventories')->getCollection()->pluck('id')->all();

    expect($ids)->toBe([$depleted->id]);
});

it('shows every batch when filtering by all', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    $inStock = Inventory::factory()->for($product)->create(['remaining_quantity' => 5]);
    $depleted = Inventory::factory()->for($product)->create(['remaining_quantity' => 0]);

    $ids = $this->actingAs($staff)->get('/inventories?stock=all')
        ->viewData('inventories')->getCollection()->pluck('id')->sort()->values()->all();

    expect($ids)->toBe([$inStock->id, $depleted->id]);
});

it('counts only batches with stock in the current stock summary', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    Inventory::factory()->for($product)->create(['remaining_quantity' => 5, 'location' => 'Main Warehouse']);
    Inventory::factory()->for($product)->create(['remaining_quantity' => 5, 'location' => 'Storage Room A']);
    Inventory::factory()->for($product)->create(['remaining_quantity' => 0, 'location' => 'Storage Room B']);

    $summary = $this->actingAs($staff)->get('/inventories')->viewData('summary');

    expect($summary['total_items'])->toBe(2)
        ->and($summary['total_locations'])->toBe(2);
});
