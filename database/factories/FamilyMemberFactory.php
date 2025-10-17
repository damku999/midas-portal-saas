<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use Illuminate\Database\Eloquent\Factories\Factory;

class FamilyMemberFactory extends Factory
{
    protected $model = FamilyMember::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'family_group_id' => FamilyGroup::factory(),
            'relationship' => $this->faker->randomElement([
                'Self', 'Spouse', 'Son', 'Daughter', 'Father', 'Mother',
                'Brother', 'Sister', 'Father-in-law', 'Mother-in-law',
            ]),
            'is_head' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function head(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship' => 'Self',
            'is_head' => true,
        ]);
    }

    public function spouse(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship' => 'Spouse',
            'is_head' => false,
        ]);
    }

    public function child(): static
    {
        return $this->state(fn (array $attributes) => [
            'relationship' => $this->faker->randomElement(['Son', 'Daughter']),
            'is_head' => false,
        ]);
    }
}
