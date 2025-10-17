<?php

namespace Database\Factories;

use App\Models\CommissionType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionTypeFactory extends Factory
{
    protected $model = CommissionType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Standard', 'Premium', 'Corporate', 'Special Rate', 'Volume Discount']),
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
