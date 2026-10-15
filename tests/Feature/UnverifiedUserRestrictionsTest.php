<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

it('lets an unverified user view pages', function (string $role) {
    $user = User::factory()->{$role}()->unverified()->create();

    $this->actingAs($user)->get('/dashboard')->assertOk();

    $this->actingAs($user)->get('/profile')
        ->assertOk()
        ->assertSee('You can view data, but you cannot make changes until you verify it.');
})->with(['admin', 'manager', 'staff']);

it('blocks changes and forms for unverified users', function (string $role, string $method, string $uri) {
    $user = User::factory()->{$role}()->unverified()->create();

    $this->actingAs($user)->{$method}($uri)
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status', 'verification-required');
})->with([
    ['staff', 'post', '/inventories'],
    ['staff', 'post', '/stock-outs'],
    ['staff', 'get', '/inventories/create'],
    ['staff', 'get', '/stock-outs/create'],
    ['manager', 'post', '/stock-adjustments'],
    ['manager', 'post', '/products'],
    ['manager', 'post', '/suppliers'],
    ['manager', 'get', '/stock-adjustments/create'],
    ['admin', 'post', '/users'],
    ['admin', 'post', '/categories'],
    ['admin', 'get', '/users/create'],
]);

it('returns 403 for json requests from unverified users', function () {
    $user = User::factory()->staff()->unverified()->create();

    $this->actingAs($user)->postJson('/stock-outs')->assertForbidden();
});

it('lets an unverified user use profile, verification and logout', function (string $role) {
    Notification::fake();
    $user = User::factory()->{$role}()->unverified()->create(['name' => 'Test User']);

    $this->actingAs($user)->patch('/profile', [
        'name' => 'Renamed User',
        'email' => $user->email,
    ])->assertSessionHasNoErrors()->assertRedirect('/profile');

    expect($user->fresh()->name)->toBe('Renamed User');

    $this->actingAs($user)->post('/email/verification-notification')
        ->assertSessionHas('status', 'verification-link-sent');

    Notification::assertSentTo($user, VerifyEmail::class);

    $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));
    $this->assertGuest();
})->with(['admin', 'manager', 'staff']);

it('does not block a verified user and unlocks once verified', function () {
    $user = User::factory()->staff()->unverified()->create();

    $this->actingAs($user)->post('/stock-outs')
        ->assertSessionHas('status', 'verification-required');

    $user->markEmailAsVerified();

    $this->actingAs($user->fresh())->post('/stock-outs')
        ->assertSessionMissing('status');
});
