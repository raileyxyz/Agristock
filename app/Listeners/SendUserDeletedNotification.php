<?php

namespace App\Listeners;

use App\Events\UserAccountDeleted;
use App\Services\NotificationDispatchService;

class SendUserDeletedNotification
{
    public function __construct(
        protected NotificationDispatchService $dispatchService
    ) {}

    public function handle(UserAccountDeleted $event): void
    {
        $this->dispatchService->sendToEligibleUsers(
            'user_deleted',
            'User Account Deleted',
            "{$event->deletedUserName} ({$event->deletedUserEmail}) permanently deleted their own account.",
            route('users.index'),
            null
        );
    }
}
