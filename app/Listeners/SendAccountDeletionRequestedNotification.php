<?php

namespace App\Listeners;

use App\Events\AccountDeletionRequested;
use App\Services\NotificationDispatchService;

class SendAccountDeletionRequestedNotification
{
    public function __construct(
        protected NotificationDispatchService $dispatchService
    ) {}

    public function handle(AccountDeletionRequested $event): void
    {
        $user = $event->deletionRequest->user;

        $this->dispatchService->sendToEligibleUsers(
            'deletion_requested',
            'Account Deletion Request',
            "{$user->name} ({$user->role->value}) asked to delete their account. Review the request.",
            route('users.deletion-requests.index'),
            null
        );
    }
}
