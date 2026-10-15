<?php

namespace App\Events;

use App\Models\AccountDeletionRequest;
use Illuminate\Foundation\Events\Dispatchable;

class AccountDeletionRequested
{
    use Dispatchable;

    public function __construct(public AccountDeletionRequest $deletionRequest) {}
}
