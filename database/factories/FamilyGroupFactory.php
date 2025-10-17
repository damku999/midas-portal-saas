<?php

namespace Database\Factories;

use App\Models\FamilyGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class FamilyGroupFactory extends Factory
{
    protected $model = FamilyGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true).' Family',
            'family_head_id' => null, // Will be set after creating customers
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
