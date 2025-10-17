<?php

namespace Database\Factories;

use App\Models\Claim;
use App\Models\ClaimDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClaimDocumentFactory extends Factory
{
    protected $model = ClaimDocument::class;

    public function definition()
    {
        return [
            'claim_id' => Claim::factory(),
            'document_name' => $this->faker->randomElement([
                'Discharge Summary',
                'Medical Bills',
                'Diagnostic Reports',
                'RC Copy',
                'Police FIR',
                'Insurance Policy Copy',
                'Estimate Bills',
                'Photo Identity Proof',
            ]),
            'is_required' => $this->faker->boolean(70), // 70% chance of being required
            'is_submitted' => $this->faker->boolean(40), // 40% chance of being submitted
            'submitted_date' => $this->faker->optional(0.4)->dateTimeBetween('-15 days', 'now'),
            'notes' => $this->faker->optional()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function required()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_required' => true,
            ];
        });
    }

    public function submitted()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_submitted' => true,
                'submitted_date' => $this->faker->dateTimeBetween('-15 days', 'now'),
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_submitted' => false,
                'submitted_date' => null,
            ];
        });
    }
}
