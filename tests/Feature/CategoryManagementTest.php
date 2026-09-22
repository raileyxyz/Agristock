<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Product;

it('allows manager to create a category', function () {
    $manager = User::factory()->manager()->create();

    $response = $this->actingAs($manager)->post('/categories', [
        'name' => 'Fertilizers',
        'description' => 'All fertilizer products',
    ]);

    $response->assertRedirect(route('categories.index'));
    $this->assertDatabaseHas('categories', ['name' => 'Fertilizers']);
});

it('rejects a duplicate category name', function () {
    $manager = User::factory()->manager()->create();
    Category::factory()->create(['name' => 'Fertilizers']);

    $response = $this->actingAs($manager)->post('/categories', [
        'name' => 'Fertilizers',
    ]);

    $response->assertSessionHasErrors('name');
});

it('allows manager to update basic category fields', function () {
    $manager = User::factory()->manager()->create();
    $category = Category::factory()->create(['name' => 'Old Name', 'status' => 'Active']);

    $this->actingAs($manager)->put("/categories/{$category->id}", [
        'name' => 'New Name',
        'status' => 'Active',
    ]);

    expect($category->fresh()->name)->toBe('New Name');
});

it('prevents manager from changing a category status via update', function () {
    $manager = User::factory()->manager()->create();
    $category = Category::factory()->create(['name' => 'Seeds', 'status' => 'Active']);

    $this->actingAs($manager)->put("/categories/{$category->id}", [
        'name' => 'Seeds',
        'status' => 'Archived',
    ]);

    expect($category->fresh()->status)->toBe(\App\Enums\Status::ACTIVE);
});

it('allows admin to change a category status via update', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Seeds', 'status' => 'Active']);

    $this->actingAs($admin)->put("/categories/{$category->id}", [
        'name' => 'Seeds',
        'status' => 'Archived',
    ]);

    expect($category->fresh()->status)->toBe(\App\Enums\Status::ARCHIVED);
});

it('prevents archiving a category that still has active products', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['status' => 'Active']);
    Product::factory()->create(['category_id' => $category->id, 'status' => 'Active']);

    $response = $this->actingAs($admin)->delete("/categories/{$category->id}");

    $response->assertSessionHas('error');
    expect($category->fresh()->status)->toBe(\App\Enums\Status::ACTIVE);
});

it('allows archiving a category with no active products', function () {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create(['status' => 'Active']);
    Product::factory()->create(['category_id' => $category->id, 'status' => 'Archived']);

    $response = $this->actingAs($admin)->delete("/categories/{$category->id}");

    $response->assertSessionHas('success');
    expect($category->fresh()->status)->toBe(\App\Enums\Status::ARCHIVED);
});
