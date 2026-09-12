<?php

namespace App\Listeners;

use App\Events\UserRoleChanged;
use App\Services\NotificationDispatchService;

class SendUserRoleChangedNotification
{
    public function __construct(
        protected NotificationDispatchService $dispatchService
    ) {}

    public function handle(UserRoleChanged $event): void
    {
        $this->dispatchService->sendToEligibleUsers(
            'user_role_changed',
            'User Role Changed',
            "{$event->actor->name} changed {$event->targetUser->name}'s role from {$event->oldRole} to {$event->newRole}.",
            route('users.index'),
            $event->actor
        );
    }
}
