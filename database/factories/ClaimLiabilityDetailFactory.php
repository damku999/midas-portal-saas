<?php

namespace Database\Factories;

use App\Models\Claim;
use App\Models\ClaimLiabilityDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClaimLiabilityDetailFactory extends Factory
{
    protected $model = ClaimLiabilityDetail::class;

    public function definition()
    {
        $claimAmount = $this->faker->numberBetween(10000, 500000);
        $salvageAmount = $this->faker->numberBetween(0, $claimAmount * 0.1);
        $lessClaim = $this->faker->numberBetween(0, $claimAmount * 0.05);
        $lessDeductions = $this->faker->numberBetween(0, $claimAmount * 0.1);

        return [
            'claim_id' => Claim::factory(),
            'claim_type' => $this->faker->randomElement(['Cashless', 'Reimbursement']),
            'claim_amount' => $claimAmount,
            'salvage_amount' => $salvageAmount,
            'less_claim_charge' => $lessClaim,
            'amount_to_be_paid' => $claimAmount - $salvageAmount - $lessClaim,
            'less_salvage_amount' => $salvageAmount,
            'less_deductions' => $lessDeductions,
            'claim_amount_received' => $this->faker->optional(0.3)->numberBetween($claimAmount * 0.8, $claimAmount),
            'notes' => $this->faker->optional()->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function cashless()
    {
        return $this->state(function (array $attributes) {
            return [
                'claim_type' => 'Cashless',
            ];
        });
    }

    public function reimbursement()
    {
        return $this->state(function (array $attributes) {
            return [
                'claim_type' => 'Reimbursement',
            ];
        });
    }
}
