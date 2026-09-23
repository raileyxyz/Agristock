<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{
    /**
     * Get a paginated, filtered, searchable list of users.
     */
    public function list(?string $search, ?string $role, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->select(['id', 'name', 'email', 'phone', 'address', 'avatar', 'role', 'status', 'last_login_at', 'created_at'])
            ->search($search)
            ->role($role)
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

    public function create(array $data, User $actor): User
    {
        return DB::transaction(function () use ($data, $actor) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'status' => Status::ACTIVE->value,
            ]);

            event(new \App\Events\NewUserAdded($user, $actor));

            return $user;
        });
    }

    public function update(User $user, array $data, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $actor) {
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
    }

    public function archive(User $user, User $actor): void
    {
        $user->update(['status' => Status::ARCHIVED]);

        event(new \App\Events\UserAccountArchived($user, $actor));
    }
}
