<?php

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Services\BatchNumberGeneratorService;
use App\Services\SkuGeneratorService;

it('continues the batch sequence from the highest number even after a gap', function () {
    $year = now()->format('Y');
    Inventory::factory()->create(['batch_number' => "BT-{$year}-001"]);
    Inventory::factory()->create(['batch_number' => "BT-{$year}-007"]);

    expect(app(BatchNumberGeneratorService::class)->generate())->toBe("BT-{$year}-008");
});

it('keeps counting correctly past 999 batches', function () {
    $year = now()->format('Y');
    Inventory::factory()->create(['batch_number' => "BT-{$year}-999"]);
    Inventory::factory()->create(['batch_number' => "BT-{$year}-1000"]);

    expect(app(BatchNumberGeneratorService::class)->generate())->toBe("BT-{$year}-1001");
});

it('does not reuse an sku when two categories share the same prefix', function () {
    $fertilizer = Category::factory()->create(['name' => 'Fertilizer']);
    $fertility = Category::factory()->create(['name' => 'Fertility']);
    Product::factory()->create(['category_id' => $fertilizer->id, 'sku' => 'FER-001']);

    expect(app(SkuGeneratorService::class)->generate($fertility))->toBe('FER-002');
});

it('builds a clean sku prefix from a category name with spaces or symbols', function () {
    $category = Category::factory()->create(['name' => 'A & B']);

    expect(app(SkuGeneratorService::class)->generate($category))->toBe('ABX-001');
});
