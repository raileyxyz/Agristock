<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class UserRoleChanged
{
    use Dispatchable;

    public function __construct(
        public User $targetUser,
        public string $oldRole,
        public string $newRole,
        public User $actor
    ) {}
}
