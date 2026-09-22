<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\Factory;

class StockOutFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'location' => 'Main Warehouse',
            'quantity' => $this->faker->randomFloat(2, 1, 20),
            'reason' => 'Sale',
        ];
    }
}
