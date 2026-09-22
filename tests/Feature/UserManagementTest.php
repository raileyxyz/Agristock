<?php

use App\Models\User;

it('allows admin to create a new user', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/users', [
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@example.com',
        'role' => 'Staff',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'juan@example.com',
        'role' => 'Staff',
        'status' => 'Active',
    ]);
});

it('rejects a duplicate email on create', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'juan@example.com']);

    $response = $this->actingAs($admin)->post('/users', [
        'name' => 'Another Juan',
        'email' => 'juan@example.com',
        'role' => 'Staff',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

it('allows admin to update a user\'s basic details', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->staff()->create(['name' => 'Old Name']);

    $this->actingAs($admin)->put("/users/{$target->id}", [
        'name' => 'New Name',
        'email' => $target->email,
        'role' => 'Staff',
        'status' => 'Active',
    ]);

    expect($target->fresh()->name)->toBe('New Name');
});

it('notifies other admins when a new user is created, excluding the actor', function () {
    $actorAdmin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $manager = User::factory()->manager()->create();

    $this->actingAs($actorAdmin)->post('/users', [
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@example.com',
        'role' => 'Staff',
        'password' => 'password123',
    ]);

    expect($actorAdmin->fresh()->unreadNotifications()->count())->toBe(0)
        ->and($otherAdmin->fresh()->unreadNotifications()->count())->toBe(1)
        ->and($manager->fresh()->unreadNotifications()->count())->toBe(0);
});

it('notifies other admins when a user\'s role is changed', function () {
    $actorAdmin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $target = User::factory()->staff()->create();

    $this->actingAs($actorAdmin)->put("/users/{$target->id}", [
        'name' => $target->name,
        'email' => $target->email,
        'role' => 'Manager',
        'status' => 'Active',
    ]);

    expect($target->fresh()->role->value)->toBe('Manager')
        ->and($otherAdmin->fresh()->unreadNotifications()->count())->toBe(1);
});

it('notifies other admins when a user account is archived via update', function () {
    $actorAdmin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $target = User::factory()->staff()->create(['status' => 'Active']);

    $this->actingAs($actorAdmin)->put("/users/{$target->id}", [
        'name' => $target->name,
        'email' => $target->email,
        'role' => 'Staff',
        'status' => 'Archived',
    ]);

    expect($target->fresh()->status)->toBe(\App\Enums\Status::ARCHIVED)
        ->and($otherAdmin->fresh()->unreadNotifications()->count())->toBe(1);
});

it('notifies other admins when an archived user account is restored', function () {
    $actorAdmin = User::factory()->admin()->create();
    $otherAdmin = User::factory()->admin()->create();
    $target = User::factory()->staff()->archived()->create();

    $this->actingAs($actorAdmin)->put("/users/{$target->id}", [
        'name' => $target->name,
        'email' => $target->email,
        'role' => 'Staff',
        'status' => 'Active',
    ]);

    expect($target->fresh()->status)->toBe(\App\Enums\Status::ACTIVE)
        ->and($otherAdmin->fresh()->unreadNotifications()->count())->toBe(1);
});

it('archives a user via destroy instead of deleting the record', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->staff()->create(['status' => 'Active']);

    $response = $this->actingAs($admin)->delete("/users/{$target->id}");

    $response->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'id' => $target->id,
        'status' => 'Archived',
    ]);
});
