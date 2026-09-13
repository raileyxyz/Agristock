<?php

namespace App\Events;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class StockLevelChanged
{
    use Dispatchable;

    public function __construct(
        public Product $product,
        public float $before,
        public float $after,
        public User $actor
    ) {}
}
