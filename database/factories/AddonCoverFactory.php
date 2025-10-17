<?php

namespace Database\Factories;

use App\Models\AddonCover;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddonCoverFactory extends Factory
{
    protected $model = AddonCover::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Zero Depreciation',
                'Engine Protection',
                'Road Side Assistance',
                'NCB Protection',
                'Invoice Protection',
                'Key Replacement',
                'Personal Accident',
                'Tyre Protection',
                'Consumables',
                'Return to Invoice',
                'Passenger Cover',
                'Electrical/Electronics Cover',
                'CNG/LPG Cover',
                'Emergency Transportation',
                'Hospital Cash',
            ]),
            'description' => $this->faker->sentence(),
            'order_no' => $this->faker->numberBetween(1, 100),
            'status' => $this->faker->boolean(80), // 80% chance of being active
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    public function withOrderNumber(int $orderNo): static
    {
        return $this->state(fn (array $attributes) => [
            'order_no' => $orderNo,
        ]);
    }
}
