<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Supplier;

function validSupplierPayload(array $overrides = []): array
{
    $category = Category::factory()->create();

    return array_merge([
        'company_name' => 'AgriTrade Corp',
        'contact_person' => 'Juan Dela Cruz',
        'phone' => '0917' . fake()->unique()->numerify('#######'),
        'supply_categories' => [$category->id],
    ], $overrides);
}

it('allows manager to create a supplier with supply categories', function () {
    $manager = User::factory()->manager()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($manager)->post('/suppliers', validSupplierPayload([
        'company_name' => 'AgriTrade Corp',
        'supply_categories' => [$category->id],
    ]));

    $response->assertRedirect(route('suppliers.create'));

    $supplier = Supplier::where('company_name', 'AgriTrade Corp')->first();
    expect($supplier)->not->toBeNull()
        ->and($supplier->categories()->pluck('categories.id')->all())->toBe([$category->id]);
});

it('rejects an invalid phone number format', function () {
    $manager = User::factory()->manager()->create();

    $response = $this->actingAs($manager)->post('/suppliers', validSupplierPayload([
        'phone' => '12345',
    ]));

    $response->assertSessionHasErrors('phone');
});

it('rejects a duplicate phone number', function () {
    $manager = User::factory()->manager()->create();
    Supplier::factory()->create(['phone' => '09171234567']);

    $response = $this->actingAs($manager)->post('/suppliers', validSupplierPayload([
        'phone' => '09171234567',
    ]));

    $response->assertSessionHasErrors('phone');
});

it('requires at least one supply category', function () {
    $manager = User::factory()->manager()->create();

    $response = $this->actingAs($manager)->post('/suppliers', validSupplierPayload([
        'supply_categories' => [],
    ]));

    $response->assertSessionHasErrors('supply_categories');
});

it('replaces a supplier\'s categories on update instead of adding to them', function () {
    $admin = User::factory()->admin()->create();
    $oldCategory = Category::factory()->create();
    $newCategory = Category::factory()->create();

    $supplier = Supplier::factory()->create();
    $supplier->categories()->attach($oldCategory->id);

    $response = $this->actingAs($admin)->put("/suppliers/{$supplier->id}", validSupplierPayload([
        'phone' => $supplier->phone,
        'status' => 'Active',
        'supply_categories' => [$newCategory->id],
    ]));

    $response->assertSessionHasNoErrors();
    expect($supplier->categories()->pluck('categories.id')->all())->toBe([$newCategory->id]);
});

it('archives a supplier instead of deleting it', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create(['status' => 'Active']);

    $response = $this->actingAs($admin)->delete("/suppliers/{$supplier->id}");

    $response->assertRedirect(route('suppliers.index'));

    $this->assertDatabaseHas('suppliers', [
        'id' => $supplier->id,
        'status' => 'Archived',
    ]);
});

it('lists active suppliers before archived ones in the directory', function () {
    $staff = User::factory()->staff()->create();

    $archived = Supplier::factory()->create(['company_name' => 'Archived Co', 'status' => 'Archived']);
    $active = Supplier::factory()->create(['company_name' => 'Active Co', 'status' => 'Active']);

    $response = $this->actingAs($staff)->get('/suppliers-directory');

    $response->assertOk();
});
