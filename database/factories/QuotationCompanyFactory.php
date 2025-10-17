<?php

namespace Database\Factories;

use App\Models\InsuranceCompany;
use App\Models\Quotation;
use App\Models\QuotationCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationCompanyFactory extends Factory
{
    protected $model = QuotationCompany::class;

    public function definition(): array
    {
        $basicOdPremium = $this->faker->numberBetween(8000, 25000);
        $tpPremium = $this->faker->numberBetween(2000, 5000);
        $cngLpgPremium = $this->faker->numberBetween(0, 800);
        $totalOdPremium = $basicOdPremium + $cngLpgPremium;

        $addonPremium = $this->faker->numberBetween(2000, 8000);
        $netPremium = $totalOdPremium + $tpPremium + $addonPremium;

        $sgstAmount = $netPremium * 0.09;
        $cgstAmount = $netPremium * 0.09;
        $totalPremium = $netPremium + $sgstAmount + $cgstAmount;

        $roadsideAssistance = $this->faker->numberBetween(100, 200);
        $finalPremium = $totalPremium + $roadsideAssistance;

        return [
            'quotation_id' => Quotation::factory(),
            'insurance_company_id' => InsuranceCompany::factory(),
            'quote_number' => 'QT/'.date('y').'/'.$this->faker->numerify('########'),
            'policy_type' => 'Comprehensive',
            'policy_tenure_years' => 1,
            'idv_vehicle' => $this->faker->numberBetween(300000, 1200000),
            'idv_trailer' => 0,
            'idv_cng_lpg_kit' => $this->faker->numberBetween(0, 35000),
            'idv_electrical_accessories' => $this->faker->numberBetween(0, 50000),
            'idv_non_electrical_accessories' => $this->faker->numberBetween(0, 30000),
            'total_idv' => $this->faker->numberBetween(350000, 1300000),
            'basic_od_premium' => $basicOdPremium,
            'tp_premium' => $tpPremium,
            'cng_lpg_premium' => $cngLpgPremium,
            'total_od_premium' => $totalOdPremium,
            'addon_covers_breakdown' => [
                'Zero Depreciation' => ['price' => $this->faker->numberBetween(2000, 4000), 'note' => ''],
                'Engine Protection' => ['price' => $this->faker->numberBetween(500, 1500), 'note' => ''],
                'Road Side Assistance' => ['price' => 180, 'note' => '24x7 Support'],
                'NCB Protection' => ['price' => $this->faker->numberBetween(200, 500), 'note' => ''],
            ],
            'total_addon_premium' => $addonPremium,
            'net_premium' => $netPremium,
            'sgst_amount' => round($sgstAmount, 2),
            'cgst_amount' => round($cgstAmount, 2),
            'total_premium' => round($totalPremium, 2),
            'roadside_assistance' => $roadsideAssistance,
            'final_premium' => round($finalPremium, 2),
            'is_recommended' => false,
            'recommendation_note' => null,
            'ranking' => $this->faker->numberBetween(1, 5),
            'benefits' => 'Comprehensive coverage with add-on benefits, 24/7 customer support, quick claim settlement.',
            'exclusions' => 'Pre-existing damages, wear and tear, consequential damages not covered.',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function recommended(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recommended' => true,
            'recommendation_note' => 'Best value for comprehensive coverage',
            'ranking' => 1,
        ]);
    }

    public function thirdParty(): static
    {
        return $this->state(fn (array $attributes) => [
            'policy_type' => 'Third Party',
            'basic_od_premium' => 0,
            'total_od_premium' => 0,
            'addon_covers_breakdown' => [],
            'total_addon_premium' => 0,
            'net_premium' => $attributes['tp_premium'] ?? 3500,
            'sgst_amount' => round(($attributes['tp_premium'] ?? 3500) * 0.09, 2),
            'cgst_amount' => round(($attributes['tp_premium'] ?? 3500) * 0.09, 2),
        ]);
    }

    public function withHighPremium(): static
    {
        return $this->state(function (array $attributes) {
            $highPremium = 35000;
            $sgst = round($highPremium * 0.09, 2);
            $cgst = round($highPremium * 0.09, 2);
            $final = $highPremium + $sgst + $cgst + 150;

            return [
                'net_premium' => $highPremium,
                'sgst_amount' => $sgst,
                'cgst_amount' => $cgst,
                'final_premium' => round($final, 2),
            ];
        });
    }

    public function withLowPremium(): static
    {
        return $this->state(function (array $attributes) {
            $lowPremium = 12000;
            $sgst = round($lowPremium * 0.09, 2);
            $cgst = round($lowPremium * 0.09, 2);
            $final = $lowPremium + $sgst + $cgst + 150;

            return [
                'net_premium' => $lowPremium,
                'sgst_amount' => $sgst,
                'cgst_amount' => $cgst,
                'final_premium' => round($final, 2),
            ];
        });
    }
}
