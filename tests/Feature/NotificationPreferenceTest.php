<?php

use App\Models\User;
use App\Models\NotificationPreference;
use App\Services\NotificationDispatchService;
use App\Services\NotificationPreferenceService;

it('does not notify a user who has disabled a specific notification type', function () {
    $enabledUser = User::factory()->admin()->create();
    $disabledUser = User::factory()->admin()->create();

    NotificationPreference::create([
        'user_id' => $disabledUser->id,
        'notification_type' => 'stock_adjusted',
        'enabled' => false,
    ]);

    app(NotificationDispatchService::class)->sendToEligibleUsers(
        'stock_adjusted',
        'Stock Adjustment Made',
        'Test body',
        null,
        null
    );

    expect($enabledUser->fresh()->unreadNotifications()->count())->toBe(1)
        ->and($disabledUser->fresh()->unreadNotifications()->count())->toBe(0);
});

it('persists an updated preference and correctly reports it changed', function () {
    $admin = User::factory()->admin()->create();
    $service = app(NotificationPreferenceService::class);

    expect($service->isEnabled($admin, 'stock_adjusted'))->toBeTrue();

    $allowedTypes = collect(config('notification_types'))
        ->filter(fn ($meta) => in_array($admin->role, $meta['roles'], true))
        ->keys();

    $preferences = $allowedTypes
        ->mapWithKeys(fn ($type) => [$type => $type !== 'stock_adjusted'])
        ->all();

    $changed = $service->updatePreferences($admin, $preferences);

    expect($changed)->toBeTrue()
        ->and($service->isEnabled($admin, 'stock_adjusted'))->toBeFalse();
});
