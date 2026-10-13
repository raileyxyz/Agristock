<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();
    $originalEmail = $user->email;

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame($originalEmail, $user->email);
    $this->assertNotNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

it('does not let a manager or staff change their own email from the profile page', function (string $role) {
    Notification::fake();
    $user = User::factory()->{$role}()->create(['name' => 'Test User']);
    $originalEmail = $user->email;

    $this->actingAs($user)->patch('/profile', [
        'name' => 'Renamed User',
        'email' => 'new-address@example.com',
    ])->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Renamed User')
        ->and($user->email)->toBe($originalEmail)
        ->and($user->email_verified_at)->not->toBeNull();

    Notification::assertNothingSent();
})->with(['manager', 'staff']);

it('lets an admin change their own email with the current password and sends a verification link', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create(['name' => 'Admin User']);

    $this->actingAs($admin)->patch('/profile', [
        'name' => 'Admin User',
        'email' => 'new-admin@example.com',
        'current_password' => 'password',
    ])->assertSessionHasNoErrors()->assertRedirect('/profile');

    $admin->refresh();

    expect($admin->email)->toBe('new-admin@example.com')
        ->and($admin->email_verified_at)->toBeNull();

    Notification::assertSentTo($admin, VerifyEmail::class);
});

it('requires the current password when an admin changes their email', function (?string $password) {
    Notification::fake();
    $admin = User::factory()->admin()->create(['name' => 'Admin User']);
    $originalEmail = $admin->email;

    $payload = ['name' => 'Admin User', 'email' => 'new-admin@example.com'];

    if ($password !== null) {
        $payload['current_password'] = $password;
    }

    $this->actingAs($admin)->patch('/profile', $payload)->assertSessionHasErrors('current_password');

    expect($admin->fresh()->email)->toBe($originalEmail);
    Notification::assertNothingSent();
})->with([[null], ['wrong-password']]);

it('does not send a verification link when an admin keeps the same email', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create(['name' => 'Admin User']);

    $this->actingAs($admin)->patch('/profile', [
        'name' => 'Renamed Admin',
        'email' => $admin->email,
    ])->assertSessionHasNoErrors();

    Notification::assertNothingSent();
    expect($admin->fresh()->email_verified_at)->not->toBeNull();
});

it('rejects a duplicate or invalid email when an admin changes their own email', function () {
    $admin = User::factory()->admin()->create(['name' => 'Admin User']);
    $other = User::factory()->staff()->create();

    $this->actingAs($admin)->patch('/profile', [
        'name' => 'Admin User',
        'email' => $other->email,
        'current_password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->actingAs($admin)->patch('/profile', [
        'name' => 'Admin User',
        'email' => 'not-an-email',
        'current_password' => 'password',
    ])->assertSessionHasErrors('email');
});

it('shows the unverified notice on the profile page', function () {
    $user = User::factory()->staff()->unverified()->create();

    $this->actingAs($user)->get('/profile')
        ->assertOk()
        ->assertSee('Your email address is unverified.');
});
