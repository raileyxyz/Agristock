<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AppNotification;

class NotificationDispatchService
{
    public function __construct(
        protected NotificationPreferenceService $preferenceService
    ) {}

    /**
     * Send a notification to every eligible, opted-in, active user — excluding the actor.
     */
    public function sendToEligibleUsers(string $type, string $title, string $body, ?string $url, ?User $actor): void
    {
        $meta = config("notification_types.{$type}");

        if (! $meta) {
            return;
        }

        $recipients = User::query()
            ->where('status', 'Active')
            ->whereIn('role', $meta['roles'])
            ->when($actor, fn ($q) => $q->where('id', '!=', $actor->id))
            ->get()
            ->filter(fn (User $user) => $this->preferenceService->isEnabled($user, $type));

        foreach ($recipients as $recipient) {
            $recipient->notify(new AppNotification($type, $title, $body, $url));
        }
    }
}
