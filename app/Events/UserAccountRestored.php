<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class UserAccountRestored
{
    use Dispatchable;

    public function __construct(
        public User $restoredUser,
        public User $actor
    ) {}
}
