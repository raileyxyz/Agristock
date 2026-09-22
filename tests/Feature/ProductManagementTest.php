<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;

function validProductPayload(array $overrides = []): array
{
    $category = Category::factory()->create();
    $unit = Unit::factory()->create();

    return array_merge([
        'name' => 'Test Product ' . uniqid(),
        'category_id' => $category->id,
        'unit_id' => $unit->id,
        'sku' => 'SKU-' . uniqid(),
        'cost_price' => 50,
        'selling_price' => 100,
        'minimum_stock' => 10,
        'reorder_point' => 20,
        'status' => 'Active',
    ], $overrides);
}

it('allows manager to create a product', function () {
    $manager = User::factory()->manager()->create();

    $response = $this->actingAs($manager)->post('/products', validProductPayload([
        'name' => 'Urea Fertilizer 50kg',
    ]));

    $response->assertRedirect(route('products.create'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('products', [
        'name' => 'Urea Fertilizer 50kg',
        'status' => 'Active',
    ]);
});

it('rejects a duplicate product name', function () {
    $manager = User::factory()->manager()->create();
    Product::factory()->create(['name' => 'Urea Fertilizer 50kg']);

    $response = $this->actingAs($manager)->post('/products', validProductPayload([
        'name' => 'Urea Fertilizer 50kg',
    ]));

    $response->assertSessionHasErrors('name');
});

it('rejects a selling price lower than the cost price', function () {
    $manager = User::factory()->manager()->create();

    $response = $this->actingAs($manager)->post('/products', validProductPayload([
        'cost_price' => 100,
        'selling_price' => 50,
    ]));

    $response->assertSessionHasErrors('selling_price');
});

it('rejects a reorder point lower than the minimum stock', function () {
    $manager = User::factory()->manager()->create();

    $response = $this->actingAs($manager)->post('/products', validProductPayload([
        'minimum_stock' => 20,
        'reorder_point' => 10,
    ]));

    $response->assertSessionHasErrors('reorder_point');
});

it('allows manager to update basic product fields', function () {
    $manager = User::factory()->manager()->create();
    $product = Product::factory()->create(['name' => 'Old Name', 'status' => 'Active']);

    $response = $this->actingAs($manager)->put("/products/{$product->id}", validProductPayload([
        'name' => 'New Name',
        'category_id' => $product->category_id,
        'unit_id' => $product->unit_id,
        'sku' => $product->sku,
        'status' => 'Active',
    ]));

    $response->assertRedirect(route('products.index'));
    expect($product->fresh()->name)->toBe('New Name');
});

it('prevents manager from changing a product status via update', function () {
    $manager = User::factory()->manager()->create();
    $product = Product::factory()->create(['status' => 'Active']);

    $this->actingAs($manager)->put("/products/{$product->id}", validProductPayload([
        'category_id' => $product->category_id,
        'unit_id' => $product->unit_id,
        'sku' => $product->sku,
        'status' => 'Archived',
    ]));

    expect($product->fresh()->status)->toBe(\App\Enums\Status::ACTIVE);
});

it('allows admin to change a product status via update', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['status' => 'Active']);

    $this->actingAs($admin)->put("/products/{$product->id}", validProductPayload([
        'category_id' => $product->category_id,
        'unit_id' => $product->unit_id,
        'sku' => $product->sku,
        'status' => 'Archived',
    ]));

    expect($product->fresh()->status)->toBe(\App\Enums\Status::ARCHIVED);
});

it('archives a product instead of deleting it', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['status' => 'Active']);

    $response = $this->actingAs($admin)->delete("/products/{$product->id}");

    $response->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'status' => 'Archived',
    ]);
});
