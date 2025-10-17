<?php

namespace Database\Factories;

use App\Models\PolicyType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PolicyTypeFactory extends Factory
{
    protected $model = PolicyType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Health Insurance', 'Vehicle Insurance', 'Life Insurance']),
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => 0,
            'updated_by' => 0,
        ];
    }
}
