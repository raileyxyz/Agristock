<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\StockOut;
use App\Models\StockAdjustment;

it('classifies stock report counts correctly', function () {
    $manager = User::factory()->manager()->create();

    $outOfStock = Product::factory()->create(['reorder_point' => 20]);
    Inventory::factory()->for($outOfStock)->create(['quantity' => 0, 'remaining_quantity' => 0]);

    $lowCritical = Product::factory()->create(['reorder_point' => 20]);
    Inventory::factory()->for($lowCritical)->create(['quantity' => 10, 'remaining_quantity' => 10]);

    $inStock = Product::factory()->create(['reorder_point' => 20]);
    Inventory::factory()->for($inStock)->create(['quantity' => 50, 'remaining_quantity' => 50]);

    $summary = $this->actingAs($manager)->get('/reports/stock')->viewData('summary');

    expect($summary['total_skus'])->toBe(3)
        ->and($summary['out_of_stock'])->toBe(1)
        ->and($summary['low_critical'])->toBe(1)
        ->and($summary['in_stock'])->toBe(1);
});

it('sums total received, consumed, and adjustment counts for the movement report', function () {
    $manager = User::factory()->manager()->create();
    $product = Product::factory()->create();

    $inventory = Inventory::factory()->for($product)->create(['quantity' => 100, 'remaining_quantity' => 100]);

    StockOut::factory()->create(['product_id' => $product->id, 'quantity' => 30]);
    StockAdjustment::factory()->create(['inventory_id' => $inventory->id]);

    $summary = $this->actingAs($manager)->get('/reports/movement')->viewData('summary');

    expect($summary['total_received'])->toBe(100.0)
        ->and($summary['total_consumed'])->toBe(30.0)
        ->and($summary['adjustments_count'])->toBe(1);
});

it('buckets expiry batches into expired, within 60 days, and safe', function () {
    $manager = User::factory()->manager()->create();
    $product = Product::factory()->create(['expiry_track' => true]);

    Inventory::factory()->for($product)->create(['expiry_date' => now()->subDay()]);
    Inventory::factory()->for($product)->create(['expiry_date' => now()->addDays(45)]);
    Inventory::factory()->for($product)->create(['expiry_date' => now()->addDays(90)]);

    $summary = $this->actingAs($manager)->get('/reports/expiry')->viewData('summary');

    expect($summary['expired'])->toBe(1)
        ->and($summary['within_60'])->toBe(1)
        ->and($summary['safe'])->toBe(1);
});
