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
            "{$event->actor->name} approved the deletion of {$event->deletedUserName} ({$event->deletedUserEmail}).",
            route('users.index'),
            $event->actor
        );
    }
}
