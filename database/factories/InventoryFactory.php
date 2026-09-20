<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 10, 100);

        return [
            'product_id' => Product::factory(),
            'supplier_id' => null,
            'quantity' => $quantity,
            'remaining_quantity' => $quantity,
            'batch_number' => strtoupper($this->faker->unique()->bothify('BATCH-####')),
            'expiry_date' => null,
            'location' => 'Main Warehouse',
            'notes' => null,
        ];
    }
}
