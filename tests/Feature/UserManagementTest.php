<?php

use App\Models\User;
use App\Notifications\UserInviteNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;

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

it('creates a user without a password and sends an invite', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/users', [
        'name' => 'Juan Dela Cruz',
        'email' => 'Juan@Example.com',
        'role' => 'Staff',
    ])->assertRedirect(route('users.index'));

    $user = User::where('email', 'juan@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, UserInviteNotification::class);
});

it('lets the invited user set a password, which verifies the email', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/users', [
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@example.com',
        'role' => 'Manager',
    ]);

    $user = User::where('email', 'juan@example.com')->first();
    $token = null;

    Notification::assertSentTo($user, UserInviteNotification::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    auth()->logout();

    $this->post('/reset-password', [
        'token' => $token,
        'email' => 'juan@example.com',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertRedirect(route('login'));

    expect($user->fresh()->email_verified_at)->not->toBeNull();

    $this->post('/login', ['email' => 'juan@example.com', 'password' => 'new-password-123']);
    $this->assertAuthenticatedAs($user->fresh());
});

it('lets admin resend an invite to an active user', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $pending = User::factory()->staff()->unverified()->create();

    $this->actingAs($admin)->post(route('users.resend-invite', $pending))
        ->assertRedirect(route('users.index'));

    Notification::assertSentTo($pending, UserInviteNotification::class);
});

it('does not resend an invite to an archived user', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $archived = User::factory()->staff()->archived()->unverified()->create();

    $this->actingAs($admin)->post(route('users.resend-invite', $archived))
        ->assertSessionHas('error');

    Notification::assertNothingSent();
});

it('forbids non-admin roles from creating users or resending invites', function (string $role) {
    Notification::fake();
    $actor = User::factory()->{$role}()->create();
    $target = User::factory()->staff()->unverified()->create();

    $this->actingAs($actor)->post('/users', [
        'name' => 'Juan Dela Cruz',
        'email' => 'juan@example.com',
        'role' => 'Staff',
    ])->assertForbidden();

    $this->actingAs($actor)->post(route('users.resend-invite', $target))->assertForbidden();

    expect(User::where('email', 'juan@example.com')->exists())->toBeFalse();
    Notification::assertNothingSent();
})->with(['manager', 'staff']);

it('shows the pending invite badge only for users who have not verified their email', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->staff()->unverified()->create(['name' => 'Pending Person']);
    User::factory()->staff()->create(['name' => 'Verified Person']);

    $response = $this->actingAs($admin)->get('/users');

    $response->assertOk();
    expect(substr_count($response->getContent(), '>Pending invite</span>'))->toBe(1);
});

it('resets verification and sends a verification link when admin changes a user email', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $target = User::factory()->staff()->create();

    $this->actingAs($admin)->put("/users/{$target->id}", [
        'name' => $target->name,
        'email' => 'New.Address@Example.com',
        'role' => 'Staff',
        'status' => 'Active',
    ])->assertRedirect(route('users.index'));

    $target->refresh();

    expect($target->email)->toBe('new.address@example.com')
        ->and($target->email_verified_at)->toBeNull();

    Notification::assertSentTo($target, VerifyEmail::class);
});

it('keeps verification when admin saves a user without changing the email', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $target = User::factory()->staff()->create(['email' => 'Mixed.Case@example.com']);

    $this->actingAs($admin)->put("/users/{$target->id}", [
        'name' => 'Renamed User',
        'email' => $target->email,
        'role' => 'Staff',
        'status' => 'Active',
    ]);

    Notification::assertNotSentTo($target, VerifyEmail::class);
    expect($target->fresh()->email_verified_at)->not->toBeNull();
});

it('rejects a badly formatted email when admin updates a user', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->staff()->create();

    $this->actingAs($admin)->put("/users/{$target->id}", [
        'name' => $target->name,
        'email' => 'not-an-email',
        'role' => 'Staff',
        'status' => 'Active',
    ])->assertSessionHasErrors('email');
});

it('forbids non-admin roles from changing a user email', function (string $role) {
    Notification::fake();
    $actor = User::factory()->{$role}()->create();
    $target = User::factory()->staff()->create();
    $oldEmail = $target->email;

    $this->actingAs($actor)->put("/users/{$target->id}", [
        'name' => $target->name,
        'email' => 'new@example.com',
        'role' => 'Staff',
        'status' => 'Active',
    ])->assertForbidden();

    expect($target->fresh()->email)->toBe($oldEmail);
    Notification::assertNothingSent();
})->with(['manager', 'staff']);

it('filters the users list by status', function () {
    $admin = User::factory()->admin()->create(['name' => 'Admin Person']);
    User::factory()->staff()->create(['name' => 'Verified Staff']);
    User::factory()->staff()->unverified()->create(['name' => 'Pending Staff']);
    User::factory()->staff()->archived()->create(['name' => 'Archived Staff']);

    $this->actingAs($admin)->get('/users?status=active')
        ->assertSee('Verified Staff')
        ->assertDontSee('Pending Staff')
        ->assertDontSee('Archived Staff');

    $this->actingAs($admin)->get('/users?status=pending')
        ->assertSee('Pending Staff')
        ->assertDontSee('Verified Staff')
        ->assertDontSee('Archived Staff');

    $this->actingAs($admin)->get('/users?status=archived')
        ->assertSee('Archived Staff')
        ->assertDontSee('Verified Staff')
        ->assertDontSee('Pending Staff');
});

it('lets admin and manager use the status filter but not staff', function (string $role, int $expected) {
    $actor = User::factory()->{$role}()->create();

    $this->actingAs($actor)->get('/users?status=pending')->assertStatus($expected);
})->with([['admin', 200], ['manager', 200], ['staff', 403]]);

it('requires the admin password to create an admin account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/users', [
        'name' => 'New Admin',
        'email' => 'newadmin@example.com',
        'role' => 'Admin',
    ])->assertSessionHasErrors('current_password');

    $this->assertDatabaseMissing('users', ['email' => 'newadmin@example.com']);
});

it('rejects a wrong password when creating an admin account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/users', [
        'name' => 'New Admin',
        'email' => 'newadmin@example.com',
        'role' => 'Admin',
        'current_password' => 'wrong-password',
    ])->assertSessionHasErrors('current_password');

    $this->assertDatabaseMissing('users', ['email' => 'newadmin@example.com']);
});

it('creates an admin account when the correct password is given', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/users', [
        'name' => 'New Admin',
        'email' => 'newadmin@example.com',
        'role' => 'Admin',
        'current_password' => 'password',
    ])->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', ['email' => 'newadmin@example.com', 'role' => 'Admin']);
});

it('does not require a password for non-admin roles', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/users', [
        'name' => 'Juan', 'email' => 'juan@example.com', 'role' => 'Manager',
    ])->assertRedirect(route('users.index'));
});
