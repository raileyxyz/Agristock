<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class UserAccountArchived
{
    use Dispatchable;

    public function __construct(
        public User $archivedUser,
        public User $actor
    ) {}
}
