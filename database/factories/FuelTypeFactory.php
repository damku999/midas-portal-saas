<?php

namespace Database\Factories;

use App\Models\FuelType;
use Illuminate\Database\Eloquent\Factories\Factory;

class FuelTypeFactory extends Factory
{
    protected $model = FuelType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Petrol', 'Diesel', 'CNG', 'LPG', 'Electric', 'Hybrid']),
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
