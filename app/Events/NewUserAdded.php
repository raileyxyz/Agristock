<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class NewUserAdded
{
    use Dispatchable;

    public function __construct(
        public User $newUser,
        public User $actor
    ) {}
}
