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
            ->select(['id', 'name', 'email', 'role', 'created_at'])
            ->search($search)
            ->role($role)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
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

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);

            return $user->fresh();
        });
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
