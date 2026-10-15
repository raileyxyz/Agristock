<?php

namespace App\Services;

use App\Enums\DeletionRequestStatus;
use App\Enums\Status;
use App\Events\AccountDeletionRequested;
use App\Events\UserAccountDeleted;
use App\Models\AccountDeletionRequest;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountDeletionRequestService
{
    public function __construct(
        protected NotificationPreferenceService $preferenceService
    ) {}

    public function latestFor(User $user): ?AccountDeletionRequest
    {
        return AccountDeletionRequest::where('user_id', $user->id)->latest('id')->first();
    }

    public function pending(): Collection
    {
        return AccountDeletionRequest::pending()->with('user')->oldest()->get();
    }

    public function recentlyReviewed(int $limit = 10): Collection
    {
        return AccountDeletionRequest::where('status', '!=', DeletionRequestStatus::PENDING->value)
            ->with(['user', 'decider'])
            ->latest('decided_at')
            ->limit($limit)
            ->get();
    }

    public function request(User $user, string $reason): array
    {
        if ($user->isAdmin()) {
            return ['success' => false, 'message' => 'Administrator accounts cannot be deleted from Settings.'];
        }

        if (AccountDeletionRequest::where('user_id', $user->id)->pending()->exists()) {
            return ['success' => false, 'message' => 'You already have a pending deletion request.'];
        }

        try {
            $request = AccountDeletionRequest::create([
                'user_id' => $user->id,
                'reason' => $reason,
                'status' => DeletionRequestStatus::PENDING,
            ]);

            event(new AccountDeletionRequested($request));
        } catch (\Throwable $e) {
            Log::error('Failed to create deletion request', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Could not send your request. Please try again.'];
        }

        return ['success' => true, 'message' => 'Your request was sent. An administrator will review it. You can keep using the system until then.'];
    }

    public function approve(AccountDeletionRequest $request, User $admin): array
    {
        if ($request->status !== DeletionRequestStatus::PENDING) {
            return ['success' => false, 'message' => 'This request has already been reviewed.'];
        }

        $target = $request->user;

        try {
            DB::transaction(function () use ($request, $target, $admin) {
                $target->update(['status' => Status::DELETED]);

                $request->update([
                    'status' => DeletionRequestStatus::APPROVED,
                    'decided_by' => $admin->id,
                    'decided_at' => now(),
                ]);
            });

            event(new UserAccountDeleted($target->name, $target->email, $admin));
        } catch (\Throwable $e) {
            Log::error('Failed to approve deletion request', ['request_id' => $request->id, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Could not approve the request. Please try again.'];
        }

        return ['success' => true, 'message' => "{$target->name}'s account was marked as Deleted."];
    }

    public function reject(AccountDeletionRequest $request, User $admin, ?string $note): array
    {
        if ($request->status !== DeletionRequestStatus::PENDING) {
            return ['success' => false, 'message' => 'This request has already been reviewed.'];
        }

        $requester = $request->user;

        try {
            $request->update([
                'status' => DeletionRequestStatus::REJECTED,
                'decided_by' => $admin->id,
                'decided_at' => now(),
                'admin_note' => $note,
            ]);

            if ($this->preferenceService->isEnabled($requester, 'deletion_decided')) {
                $body = 'An administrator declined your request to delete your account.';

                if ($note) {
                    $body .= " Note: {$note}";
                }

                $requester->notify(new AppNotification(
                    'deletion_decided',
                    'Deletion request declined',
                    $body,
                    route('profile.edit', ['tab' => 'security'])
                ));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to reject deletion request', ['request_id' => $request->id, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Could not decline the request. Please try again.'];
        }

        return ['success' => true, 'message' => "The request of {$requester->name} was declined."];
    }
}
