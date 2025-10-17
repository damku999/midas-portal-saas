<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    public function definition(): array
    {
        $manufacturingYear = $this->faker->numberBetween(2015, 2023);
        $idvVehicle = $this->faker->numberBetween(300000, 1200000);

        return [
            'customer_id' => Customer::factory(),
            'vehicle_number' => strtoupper($this->faker->regexify('[A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{4}')),
            'make_model_variant' => $this->faker->randomElement([
                'Maruti Swift VDI',
                'Hyundai i20 Sportz',
                'Honda City ZX CVT',
                'Toyota Innova Crysta',
                'Mahindra XUV300 W8',
                'Tata Nexon XZ Plus',
            ]),
            'rto_location' => $this->faker->randomElement([
                'Mumbai Central', 'Pune', 'Nagpur', 'Nashik', 'Aurangabad',
            ]),
            'manufacturing_year' => $manufacturingYear,
            'cubic_capacity_kw' => $this->faker->numberBetween(800, 2500),
            'seating_capacity' => $this->faker->randomElement([2, 4, 5, 7, 8]),
            'fuel_type' => $this->faker->randomElement(['Petrol', 'Diesel', 'CNG', 'Electric', 'Hybrid']),
            'ncb_percentage' => $this->faker->randomElement([0, 20, 25, 35, 45, 50]),
            'idv_vehicle' => $idvVehicle,
            'idv_trailer' => 0,
            'idv_cng_lpg_kit' => $this->faker->randomElement([0, 15000, 25000, 35000]),
            'idv_electrical_accessories' => $this->faker->numberBetween(0, 50000),
            'idv_non_electrical_accessories' => $this->faker->numberBetween(0, 30000),
            'total_idv' => $idvVehicle + $this->faker->numberBetween(0, 80000),
            'addon_covers' => $this->faker->randomElements([
                'Zero Depreciation',
                'Engine Protection',
                'Road Side Assistance',
                'NCB Protection',
                'Invoice Protection',
                'Key Replacement',
                'Personal Accident',
                'Tyre Protection',
                'Consumables',
            ], $this->faker->numberBetween(2, 5)),
            'policy_type' => $this->faker->randomElement(['Comprehensive', 'Third Party']),
            'policy_tenure_years' => $this->faker->randomElement([1, 2, 3]),
            'status' => $this->faker->randomElement(['Draft', 'Generated', 'Sent']),
            'sent_at' => $this->faker->optional()->dateTimeThisYear(),
            'whatsapp_number' => '+91'.$this->faker->numerify('##########'),
            'notes' => $this->faker->optional()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Draft',
            'sent_at' => null,
        ]);
    }

    public function generated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Generated',
            'sent_at' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Sent',
            'sent_at' => $this->faker->dateTimeThisMonth(),
        ]);
    }

    public function comprehensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'policy_type' => 'Comprehensive',
            'addon_covers' => [
                'Zero Depreciation',
                'Engine Protection',
                'Road Side Assistance',
                'NCB Protection',
            ],
        ]);
    }

    public function thirdParty(): static
    {
        return $this->state(fn (array $attributes) => [
            'policy_type' => 'Third Party',
            'addon_covers' => [],
        ]);
    }
}
