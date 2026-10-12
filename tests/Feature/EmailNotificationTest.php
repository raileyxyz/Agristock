<?php

use App\Models\User;
use App\Models\NotificationPreference;
use App\Notifications\AppEmailNotification;
use App\Notifications\AppNotification;
use App\Services\NotificationDispatchService;
use Illuminate\Support\Facades\Notification;

function dispatchAlert(string $type): void
{
    app(NotificationDispatchService::class)->sendToEligibleUsers(
        $type, 'Test Alert', 'Test body', null, null
    );
}

it('emails critical alerts to every eligible role', function (string $type, string $role) {
    Notification::fake();
    $user = User::factory()->{$role}()->create();

    dispatchAlert($type);

    Notification::assertSentTo($user, AppNotification::class);
    Notification::assertSentTo($user, AppEmailNotification::class);
})->with(['critical_stock', 'expired_products'])->with(['admin', 'manager', 'staff']);

it('does not email types that are in-app only', function (string $role) {
    Notification::fake();
    $user = User::factory()->{$role}()->create();

    dispatchAlert('low_stock');

    Notification::assertSentTo($user, AppNotification::class);
    Notification::assertNotSentTo($user, AppEmailNotification::class);
})->with(['admin', 'manager', 'staff']);

it('does not email a user whose email is not verified', function () {
    Notification::fake();
    $user = User::factory()->manager()->unverified()->create();

    dispatchAlert('critical_stock');

    Notification::assertSentTo($user, AppNotification::class);
    Notification::assertNotSentTo($user, AppEmailNotification::class);
});

it('sends no email or in-app alert when the user disabled the type', function () {
    Notification::fake();
    $user = User::factory()->staff()->create();

    NotificationPreference::create([
        'user_id' => $user->id,
        'notification_type' => 'critical_stock',
        'enabled' => false,
    ]);

    dispatchAlert('critical_stock');

    Notification::assertNothingSentTo($user);
});
