<?php

use App\Models\User;
use App\Notifications\AppNotification;

it('marks a notification as read and redirects to its target', function () {
    $user = User::factory()->staff()->create();
    $user->notify(new AppNotification('expired_products', 'Expired Products', 'x',
        route('inventories.index', ['search' => 'BATCH-1'])));
    $n = $user->notifications()->first();

    $this->actingAs($user)->get(route('notifications.open', $n->id))
        ->assertRedirect('/inventories?search=BATCH-1');

    expect($n->fresh()->read_at)->not->toBeNull();
});

it('falls back to the dashboard when a notification has no url', function () {
    $user = User::factory()->staff()->create();
    $user->notify(new AppNotification('weekly_report', 'T', 'B', null));

    $this->actingAs($user)->get(route('notifications.open', $user->notifications()->first()->id))
        ->assertRedirect(route('dashboard'));
});

it('cannot open another user\'s notification', function () {
    $owner = User::factory()->staff()->create();
    $owner->notify(new AppNotification('weekly_report', 'T', 'B', null));

    $this->actingAs(User::factory()->staff()->create())
        ->get(route('notifications.open', $owner->notifications()->first()->id))
        ->assertNotFound();
});
