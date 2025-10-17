<?php

namespace Database\Factories;

use App\Models\RelationshipManager;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelationshipManagerFactory extends Factory
{
    protected $model = RelationshipManager::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'mobile_number' => $this->faker->phoneNumber(),
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
