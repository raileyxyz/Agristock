<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockAdjustmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_id' => Inventory::factory(),
            'user_id' => User::factory(),
            'system_quantity' => 20,
            'actual_quantity' => 18,
            'reason' => 'Physical Count',
        ];
    }
}
