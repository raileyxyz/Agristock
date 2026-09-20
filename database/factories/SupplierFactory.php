<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_name' => $this->faker->unique()->company(),
            'contact_person' => $this->faker->name(),
            'phone' => $this->faker->unique()->numerify('09#########'),
            'email' => $this->faker->unique()->companyEmail(),
            'address' => $this->faker->address(),
            'status' => 'Active',
            'notes' => null,
        ];
    }
}
