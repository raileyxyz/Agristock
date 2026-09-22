<?php

use App\Models\User;
use App\Models\Unit;
use App\Models\Product;

it('allows manager to create a unit', function () {
    $manager = User::factory()->manager()->create();

    $response = $this->actingAs($manager)->post('/units', [
        'name' => 'Kilogram',
        'abbreviation' => 'kg',
    ]);

    $response->assertRedirect(route('units.index'));
    $this->assertDatabaseHas('units', ['name' => 'Kilogram', 'abbreviation' => 'kg']);
});

it('rejects a duplicate unit name', function () {
    $manager = User::factory()->manager()->create();
    Unit::factory()->create(['name' => 'Kilogram']);

    $response = $this->actingAs($manager)->post('/units', [
        'name' => 'Kilogram',
        'abbreviation' => 'kgg',
    ]);

    $response->assertSessionHasErrors('name');
});

it('rejects a duplicate unit abbreviation', function () {
    $manager = User::factory()->manager()->create();
    Unit::factory()->create(['abbreviation' => 'kg']);

    $response = $this->actingAs($manager)->post('/units', [
        'name' => 'Kilo',
        'abbreviation' => 'kg',
    ]);

    $response->assertSessionHasErrors('abbreviation');
});

it('allows manager to update a unit', function () {
    $manager = User::factory()->manager()->create();
    $unit = Unit::factory()->create(['name' => 'Old Name', 'abbreviation' => 'old']);

    $this->actingAs($manager)->put("/units/{$unit->id}", [
        'name' => 'New Name',
        'abbreviation' => 'new',
    ]);

    expect($unit->fresh()->name)->toBe('New Name');
});

it('prevents deleting a unit that is still used by a product', function () {
    $admin = User::factory()->admin()->create();
    $unit = Unit::factory()->create();
    Product::factory()->create(['unit_id' => $unit->id]);

    $response = $this->actingAs($admin)->delete("/units/{$unit->id}");

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('units', ['id' => $unit->id]);
});

it('allows deleting a unit that is not used by any product', function () {
    $admin = User::factory()->admin()->create();
    $unit = Unit::factory()->create();

    $response = $this->actingAs($admin)->delete("/units/{$unit->id}");

    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('units', ['id' => $unit->id]);
});
