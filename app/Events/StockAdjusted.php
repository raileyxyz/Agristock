<?php

namespace App\Events;

use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class StockAdjusted
{
    use Dispatchable;

    public function __construct(
        public StockAdjustment $adjustment,
        public User $actor
    ) {}
}
