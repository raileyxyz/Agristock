<?php

namespace App\Services;

use App\Models\NotificationPreference;
use App\Models\User;

class NotificationPreferenceService
{
    /**
     * Notification types visible to this user's role, grouped by category,
     * each with its current enabled state (saved preference, or config default).
     */
    public function getPreferencesForUser(User $user): array
    {
        $saved = NotificationPreference::where('user_id', $user->id)
            ->pluck('enabled', 'notification_type');

        $grouped = [];

        foreach (config('notification_types') as $type => $meta) {
            if (! in_array($user->role, $meta['roles'], true)) {
                continue;
            }

            $grouped[$meta['category']][] = [
                'type' => $type,
                'label' => $meta['label'],
                'description' => $meta['description'],
                'enabled' => $saved->has($type) ? (bool) $saved[$type] : $meta['default_enabled'],
            ];
        }

        return $grouped;
    }

    public function updatePreferences(User $user, array $preferences): void
    {
        $allowedTypes = collect(config('notification_types'))
            ->filter(fn ($meta) => in_array($user->role, $meta['roles'], true))
            ->keys();

        foreach ($allowedTypes as $type) {
            NotificationPreference::updateOrCreate(
                ['user_id' => $user->id, 'notification_type' => $type],
                ['enabled' => (bool) ($preferences[$type] ?? false)]
            );
        }
    }

    public function isEnabled(User $user, string $type): bool
    {
        $meta = config("notification_types.{$type}");

        if (! $meta || ! in_array($user->role, $meta['roles'], true)) {
            return false;
        }

        $preference = NotificationPreference::where('user_id', $user->id)
            ->where('notification_type', $type)
            ->first();

        return $preference ? (bool) $preference->enabled : $meta['default_enabled'];
    }
}
