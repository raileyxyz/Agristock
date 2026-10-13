<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Notifications\UserInviteNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserManagementService
{
    /**
     * Get a paginated, filtered, searchable list of users.
     */
    public function list(?string $search, ?string $role, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->select(['id', 'name', 'email', 'email_verified_at', 'phone', 'address', 'avatar', 'role', 'status', 'last_login_at', 'created_at'])
            ->search($search)
            ->role($role)
            ->statusFilter($status)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getSummary(): array
    {
        return [
            'active' => User::where('status', Status::ACTIVE->value)->count(),
            'inactive' => User::where('status', Status::ARCHIVED->value)->count(),
        ];
    }

    public function getStatistics(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('status', Status::ACTIVE->value)->count(),
            'archived' => User::where('status', Status::ARCHIVED->value)->count(),
        ];
    }

    public function getRoleUserCounts()
    {
        return User::query()
            ->active()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');
    }

    public function create(array $data, User $actor): array
    {
        try {
            $user = DB::transaction(function () use ($data, $actor) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make(Str::random(40)),
                    'role' => $data['role'],
                    'status' => Status::ACTIVE->value,
                ]);

                event(new \App\Events\NewUserAdded($user, $actor));

                return $user;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to create user', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Could not create the user. Please try again.'];
        }

        if (! $this->sendInvite($user)) {
            return [
                'success' => true,
                'message' => 'User created, but the invite email could not be sent. Use "Resend invite" from the options menu.',
            ];
        }

        return ['success' => true, 'message' => "User created. An invite email was sent to {$user->email}."];
    }

    public function resendInvite(User $user): array
    {
        if ($user->status !== Status::ACTIVE) {
            return ['success' => false, 'message' => 'Archived users cannot be invited.'];
        }

        if (! $this->sendInvite($user)) {
            return ['success' => false, 'message' => 'The invite email could not be sent. Please check the mail settings and try again.'];
        }

        return ['success' => true, 'message' => "Invite email sent to {$user->email}."];
    }

    private function sendInvite(User $user): bool
    {
        try {
            $user->notify(new UserInviteNotification(Password::createToken($user)));

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send user invite', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    private function sendVerification(User $user): bool
    {
        try {
            $user->sendEmailVerificationNotification();

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send email verification', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function update(User $user, array $data, User $actor): array
    {
        $emailChanged = strcasecmp($data['email'], $user->email) !== 0;

        try {
            $user = DB::transaction(function () use ($user, $data, $actor, $emailChanged) {
                $oldRole = $user->role->value;
                $oldStatus = $user->status;

                $payload = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => $data['role'],
                ];

                if (isset($data['status'])) {
                    $payload['status'] = $data['status'];
                }

                if (! empty($data['password'])) {
                    $payload['password'] = Hash::make($data['password']);
                }

                if ($emailChanged) {
                    $user->email_verified_at = null;
                }

                $user->update($payload);
                $user = $user->fresh();

                if ($oldRole !== $user->role->value) {
                    event(new \App\Events\UserRoleChanged($user, $oldRole, $user->role->value, $actor));
                }

                if ($oldStatus === \App\Enums\Status::ARCHIVED && $user->status === \App\Enums\Status::ACTIVE) {
                    event(new \App\Events\UserAccountRestored($user, $actor));
                }

                if ($oldStatus === \App\Enums\Status::ACTIVE && $user->status === \App\Enums\Status::ARCHIVED) {
                    event(new \App\Events\UserAccountArchived($user, $actor));
                }

                return $user;
            });
        } catch (\Throwable $e) {
            Log::error('Failed to update user', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Could not update the user. Please try again.'];
        }

        if (! $emailChanged || $user->status !== Status::ACTIVE) {
            return ['success' => true, 'message' => 'User updated successfully.'];
        }

        if (! $this->sendVerification($user)) {
            return [
                'success' => true,
                'message' => 'User updated, but the verification email could not be sent. Use "Resend invite" from the options menu.',
            ];
        }

        return ['success' => true, 'message' => "User updated. A verification link was sent to {$user->email}."];
    }

    public function archive(User $user, User $actor): void
    {
        $user->update(['status' => Status::ARCHIVED]);

        event(new \App\Events\UserAccountArchived($user, $actor));
    }
}
