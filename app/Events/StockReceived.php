<?php

namespace App\Events;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class StockReceived
{
    use Dispatchable;

    public function __construct(
        public Inventory $inventory,
        public User $actor
    ) {}
}
