<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;

// ── Product Management ──────────────────────────────────────

it('allows manager to view, create, and update products, but not delete', function () {
    $manager = User::factory()->manager()->create();
    $product = Product::factory()->create();

    $this->actingAs($manager)->get('/products')->assertOk();
    $this->actingAs($manager)->get('/products/create')->assertOk();
    $this->actingAs($manager)->delete("/products/{$product->id}")->assertForbidden();
});

it('allows staff to view products only, not create, update, or delete', function () {
    $staff = User::factory()->staff()->create();
    $product = Product::factory()->create();

    $this->actingAs($staff)->get('/products')->assertOk();
    $this->actingAs($staff)->get('/products/create')->assertForbidden();
    $this->actingAs($staff)->put("/products/{$product->id}", [])->assertForbidden();
    $this->actingAs($staff)->delete("/products/{$product->id}")->assertForbidden();
});

it('allows admin full access to products including delete', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $this->actingAs($admin)->get('/products')->assertOk();
    $this->actingAs($admin)->get('/products/create')->assertOk();
    $this->actingAs($admin)->delete("/products/{$product->id}")->assertRedirect();
});

// ── Supplier Management ──────────────────────────────────────

it('forbids manager from updating or deleting a supplier', function () {
    $manager = User::factory()->manager()->create();
    $supplier = Supplier::factory()->create();

    $this->actingAs($manager)->get("/suppliers/{$supplier->id}/edit")->assertForbidden();
    $this->actingAs($manager)->delete("/suppliers/{$supplier->id}")->assertForbidden();
});

it('allows admin to update and delete a supplier', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::factory()->create();

    $this->actingAs($admin)->get("/suppliers/{$supplier->id}/edit")->assertOk();
    $this->actingAs($admin)->delete("/suppliers/{$supplier->id}")->assertRedirect();
});

// ── Reports ──────────────────────────────────────

it('forbids staff from viewing movement and expiry reports, but allows the stock report', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)->get('/reports/stock')->assertOk();
    $this->actingAs($staff)->get('/reports/movement')->assertForbidden();
    $this->actingAs($staff)->get('/reports/expiry')->assertForbidden();
});

it('allows manager to view all report types', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)->get('/reports/stock')->assertOk();
    $this->actingAs($manager)->get('/reports/movement')->assertOk();
    $this->actingAs($manager)->get('/reports/expiry')->assertOk();
});

// ── User Management (Policy-based) ──────────────────────────────────────

it('allows admin and manager to view the users list, but not staff', function () {
    $admin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();
    $staff = User::factory()->staff()->create();

    $this->actingAs($admin)->get('/users')->assertOk();
    $this->actingAs($manager)->get('/users')->assertOk();
    $this->actingAs($staff)->get('/users')->assertForbidden();
});

it('forbids manager from creating, updating, or deleting users', function () {
    $manager = User::factory()->manager()->create();
    $target = User::factory()->staff()->create();

    $this->actingAs($manager)->get('/users/create')->assertForbidden();

    $this->actingAs($manager)->put("/users/{$target->id}", [
        'name' => $target->name,
        'email' => $target->email,
        'role' => 'Staff',
        'status' => 'Active',
    ])->assertForbidden();

    $this->actingAs($manager)->delete("/users/{$target->id}")->assertForbidden();
});

it('prevents an admin from deleting their own account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->delete("/users/{$admin->id}")->assertForbidden();
});

it('allows admin to delete a different user', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->staff()->create();

    $this->actingAs($admin)->delete("/users/{$target->id}")->assertRedirect();
});

// ── Account Status ──────────────────────────────────────

it('logs out an archived user and blocks further access', function () {
    $user = User::factory()->staff()->archived()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect('/login');
    $this->assertGuest();
});
