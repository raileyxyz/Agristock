<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;

it('counts active and archived products separately', function () {
    $staff = User::factory()->staff()->create();

    Product::factory()->count(2)->create(['status' => 'Active']);
    Product::factory()->create(['status' => 'Archived']);

    $response = $this->actingAs($staff)->get('/dashboard');

    $summary = $response->viewData('summary');

    expect($summary['total_products'])->toBe(2)
        ->and($summary['total_products_archived'])->toBe(1);
});

it('counts only active categories', function () {
    $staff = User::factory()->staff()->create();

    Category::factory()->count(2)->create(['status' => 'Active']);
    Category::factory()->create(['status' => 'Archived']);

    $response = $this->actingAs($staff)->get('/dashboard');

    expect($response->viewData('summary')['total_categories'])->toBe(2);
});

it('classifies low stock vs critical stock correctly', function () {
    $staff = User::factory()->staff()->create();

    $critical = Product::factory()->create(['minimum_stock' => 10, 'reorder_point' => 20]);
    Inventory::factory()->for($critical)->create(['quantity' => 5, 'remaining_quantity' => 5]);

    $low = Product::factory()->create(['minimum_stock' => 10, 'reorder_point' => 20]);
    Inventory::factory()->for($low)->create(['quantity' => 15, 'remaining_quantity' => 15]);

    $healthy = Product::factory()->create(['minimum_stock' => 10, 'reorder_point' => 20]);
    Inventory::factory()->for($healthy)->create(['quantity' => 30, 'remaining_quantity' => 30]);

    $summary = $this->actingAs($staff)->get('/dashboard')->viewData('summary');

    expect($summary['low_stock_count'])->toBe(2)
        ->and($summary['low_stock_critical'])->toBe(1);
});

it('calculates the current inventory value from remaining quantity times cost price', function () {
    $staff = User::factory()->staff()->create();

    $product = Product::factory()->create(['cost_price' => 50]);
    Inventory::factory()->for($product)->create(['quantity' => 10, 'remaining_quantity' => 10]);

    $summary = $this->actingAs($staff)->get('/dashboard')->viewData('summary');

    expect((float) $summary['monthly_inventory_value'])->toBe(500.0);
});

it('counts expired and expiring-soon batches only for expiry-tracked products', function () {
    $staff = User::factory()->staff()->create();

    $trackedProduct = Product::factory()->create(['expiry_track' => true]);
    Inventory::factory()->for($trackedProduct)->create(['expiry_date' => now()->subDay()]); // expired
    Inventory::factory()->for($trackedProduct)->create(['expiry_date' => now()->addDays(10)]); // within 30

    $untrackedProduct = Product::factory()->create(['expiry_track' => false]);
    Inventory::factory()->for($untrackedProduct)->create(['expiry_date' => now()->subDay()]); // should be ignored

    $summary = $this->actingAs($staff)->get('/dashboard')->viewData('summary');

    expect($summary['expired_count'])->toBe(1)
        ->and($summary['expiring_soon_count'])->toBe(1);
});
