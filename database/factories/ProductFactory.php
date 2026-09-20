<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'unit_id' => Unit::factory(),
            'name' => $this->faker->words(3, true),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-####')),
            'minimum_stock' => 10,
            'reorder_point' => 20,
            'cost_price' => $this->faker->randomFloat(2, 10, 100),
            'selling_price' => $this->faker->randomFloat(2, 100, 200),
            'description' => $this->faker->sentence(),
            'status' => 'Active',
            'expiry_track' => false,
        ];
    }
}
