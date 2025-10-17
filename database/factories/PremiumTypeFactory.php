<?php

namespace Database\Factories;

use App\Models\PremiumType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PremiumTypeFactory extends Factory
{
    protected $model = PremiumType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Comprehensive', 'Third Party', 'Standalone OD', 'Package Policy']),
            'is_vehicle' => $this->faker->boolean(80),
            'is_life_insurance_policies' => $this->faker->boolean(20),
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
