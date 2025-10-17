<?php

namespace Database\Factories;

use App\Models\CustomerType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerTypeFactory extends Factory
{
    protected $model = CustomerType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Individual', 'Corporate', 'VIP', 'SME', 'Enterprise']),
            'description' => $this->faker->sentence(),
            'status' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
