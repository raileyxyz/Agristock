<?php

namespace App\Models;

use App\Enums\Status;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'avatar',
        'password',
        'role',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'email',
        'phone',
        'address',
        'password',
        'role',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'role' => UserRole::class,
            'status' => Status::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::MANAGER;
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::STAFF;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', Status::ACTIVE->value);
    }

    public function scopeRole(Builder $query, UserRole|string|null $role): Builder
    {
        return $query->when($role, fn (Builder $q) => $q->where('role', $role instanceof UserRole ? $role->value : $role));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $q) use ($term) {
            $q->where(function (Builder $inner) use ($term) {
                $inner->where('name', 'like', "%{$term}%")
                      ->orWhere('email', 'like', "%{$term}%");
            });
        });
    }

    public function scopeStatusFilter(Builder $query, ?string $filter): Builder
    {
        return match ($filter) {
            'active' => $query->where('status', Status::ACTIVE->value)->whereNotNull('email_verified_at'),
            'pending' => $query->where('status', Status::ACTIVE->value)->whereNull('email_verified_at'),
            'archived' => $query->where('status', Status::ARCHIVED->value),
            'deleted' => $query->where('status', Status::DELETED->value),
            default => $query,
        };
    }
}
