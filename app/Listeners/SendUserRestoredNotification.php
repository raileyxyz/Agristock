<?php

namespace App\Listeners;

use App\Events\UserAccountRestored;
use App\Services\NotificationDispatchService;

class SendUserRestoredNotification
{
    public function __construct(
        protected NotificationDispatchService $dispatchService
    ) {}

    public function handle(UserAccountRestored $event): void
    {
        $this->dispatchService->sendToEligibleUsers(
            'user_restored',
            'User Account Restored',
            "{$event->actor->name} restored access for {$event->restoredUser->name} ({$event->restoredUser->role->value}).",
            route('users.index'),
            $event->actor
        );
    }
}
