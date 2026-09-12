<?php

namespace App\Events;

use App\Models\StockOut;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class StockOutRecorded
{
    use Dispatchable;

    public function __construct(
        public StockOut $stockOut,
        public User $actor
    ) {}
}
