<?php

use App\Models\Product;
use App\Models\Inventory;
use App\Services\StockDeductionService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->service = new StockDeductionService();
});

it('deducts from a single batch when it has enough stock', function () {
    $product = Product::factory()->create();

    $batch = Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse',
        'quantity' => 50,
        'remaining_quantity' => 50,
    ]);

    $consumed = $this->service->deduct($product, 'Main Warehouse', 20);

    expect($consumed)->toHaveCount(1)
        ->and($consumed[0]['batch_number'])->toBe($batch->batch_number)
        ->and($batch->fresh()->remaining_quantity)->toEqual(30);
});

it('follows FIFO (oldest batch first) when the product is not expiry-tracked', function () {
    $product = Product::factory()->create(['expiry_track' => false]);

    $older = Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse', 'quantity' => 10, 'remaining_quantity' => 10,
        'created_at' => now()->subDays(5),
    ]);

    $newer = Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse', 'quantity' => 10, 'remaining_quantity' => 10,
        'created_at' => now(),
    ]);

    $consumed = $this->service->deduct($product, 'Main Warehouse', 5);

    expect($consumed[0]['batch_number'])->toBe($older->batch_number)
        ->and($older->fresh()->remaining_quantity)->toEqual(5)
        ->and($newer->fresh()->remaining_quantity)->toEqual(10);
});

it('follows FEFO (earliest expiry first) when the product is expiry-tracked', function () {
    $product = Product::factory()->create(['expiry_track' => true]);

    $expiresLater = Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse', 'quantity' => 10, 'remaining_quantity' => 10,
        'expiry_date' => now()->addDays(30), 'created_at' => now()->subDays(10),
    ]);

    $expiresSoon = Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse', 'quantity' => 10, 'remaining_quantity' => 10,
        'expiry_date' => now()->addDays(5), 'created_at' => now(),
    ]);

    $consumed = $this->service->deduct($product, 'Main Warehouse', 5);

    expect($consumed[0]['batch_number'])->toBe($expiresSoon->batch_number)
        ->and($expiresSoon->fresh()->remaining_quantity)->toEqual(5)
        ->and($expiresLater->fresh()->remaining_quantity)->toEqual(10);
});

it('spills over into the next batch when the first batch is not enough', function () {
    $product = Product::factory()->create(['expiry_track' => false]);

    $first = Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse', 'quantity' => 10, 'remaining_quantity' => 10,
        'created_at' => now()->subDay(),
    ]);

    $second = Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse', 'quantity' => 10, 'remaining_quantity' => 10,
        'created_at' => now(),
    ]);

    $consumed = $this->service->deduct($product, 'Main Warehouse', 15);

    expect($consumed)->toHaveCount(2)
        ->and($first->fresh()->remaining_quantity)->toEqual(0)
        ->and($second->fresh()->remaining_quantity)->toEqual(5);
});

it('ignores batches from a different location', function () {
    $product = Product::factory()->create();

    Inventory::factory()->for($product)->create([
        'location' => 'Storage Room A', 'quantity' => 50, 'remaining_quantity' => 50,
    ]);

    expect(fn () => $this->service->deduct($product, 'Main Warehouse', 10))
        ->toThrow(ValidationException::class);
});

it('ignores batches that are already fully consumed', function () {
    $product = Product::factory()->create();

    Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse', 'quantity' => 50, 'remaining_quantity' => 0,
    ]);

    expect(fn () => $this->service->deduct($product, 'Main Warehouse', 10))
        ->toThrow(ValidationException::class);
});

it('throws a validation exception when requested quantity exceeds available stock', function () {
    $product = Product::factory()->create();

    Inventory::factory()->for($product)->create([
        'location' => 'Main Warehouse', 'quantity' => 10, 'remaining_quantity' => 10,
    ]);

    expect(fn () => $this->service->deduct($product, 'Main Warehouse', 15))
        ->toThrow(ValidationException::class);
});

it('can create users with different roles via factory', function () {
    $admin = \App\Models\User::factory()->admin()->create();
    $staff = \App\Models\User::factory()->staff()->create();

    expect($admin->role->value)->toBe('Admin')
        ->and($staff->role->value)->toBe('Staff');
});
