<?php

namespace Database\Factories;

use App\Models\Claim;
use App\Models\ClaimStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClaimStageFactory extends Factory
{
    protected $model = ClaimStage::class;

    public function definition()
    {
        return [
            'claim_id' => Claim::factory(),
            'stage_name' => $this->faker->randomElement([
                'Document Collection',
                'Document Review',
                'Survey Initiated',
                'Survey Completed',
                'Processing',
                'Approved',
                'Payment Initiated',
                'Settled',
            ]),
            'description' => $this->faker->sentence(),
            'notes' => $this->faker->optional()->paragraph(),
            'stage_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'is_current' => false,
            'is_completed' => $this->faker->boolean(70),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function current()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_current' => true,
                'is_completed' => false,
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_current' => false,
                'is_completed' => true,
            ];
        });
    }
}
