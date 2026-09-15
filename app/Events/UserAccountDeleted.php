<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class UserAccountDeleted
{
    use Dispatchable;

    public function __construct(
        public string $deletedUserName,
        public string $deletedUserEmail
    ) {}
}
