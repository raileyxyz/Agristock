<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Kilogram', 'Sack', 'Piece', 'Liter']),
            'abbreviation' => strtoupper($this->faker->unique()->lexify('???')),
            'used_in' => null,
        ];
    }
}
