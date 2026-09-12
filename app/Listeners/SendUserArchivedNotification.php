<?php

namespace App\Listeners;

use App\Events\UserAccountArchived;
use App\Services\NotificationDispatchService;

class SendUserArchivedNotification
{
    public function __construct(
        protected NotificationDispatchService $dispatchService
    ) {}

    public function handle(UserAccountArchived $event): void
    {
        $this->dispatchService->sendToEligibleUsers(
            'user_archived',
            'User Account Archived',
            "{$event->actor->name} archived the account of {$event->archivedUser->name}.",
            route('users.index'),
            $event->actor
        );
    }
}
