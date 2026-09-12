<?php

namespace App\Listeners;

use App\Events\NewUserAdded;
use App\Services\NotificationDispatchService;

class SendNewUserAddedNotification
{
    public function __construct(
        protected NotificationDispatchService $dispatchService
    ) {}

    public function handle(NewUserAdded $event): void
    {
        $this->dispatchService->sendToEligibleUsers(
            'user_added',
            'New User Added',
            "{$event->actor->name} added a new {$event->newUser->role->value}: {$event->newUser->name}.",
            route('users.index'),
            $event->actor
        );
    }
}
