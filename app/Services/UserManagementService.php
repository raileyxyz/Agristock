<?php

namespace App\Services;

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
            ->select(['id', 'name', 'email', 'role', 'status', 'last_login_at', 'created_at'])
            ->search($search)
            ->role($role)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getSummary(): array
    {
        return [
            'active' => User::where('status', 'Active')->count(),
            'inactive' => User::where('status', 'Archived')->count(),
        ];
    }

    public function getStatistics(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('status', 'Active')->count(),
            'archived' => User::where('status', 'Archived')->count(),
        ];
    }

    public function getRoleUserCounts()
    {
        return User::query()
            ->where('status', 'Active')
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'status' => 'Active',
            ]);
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
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

            return $user->fresh();
        });
    }

    public function archive(User $user): void
    {
        $user->update(['status' => 'Archived']);
    }
}
