<?php

namespace Database\Factories;

use App\Models\QuotationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationStatusFactory extends Factory
{
    protected $model = QuotationStatus::class;

    public function definition()
    {
        $statuses = [
            ['name' => 'Draft', 'color' => '#6c757d', 'is_final' => false],
            ['name' => 'Generated', 'color' => '#0dcaf0', 'is_final' => false],
            ['name' => 'Sent', 'color' => '#0d6efd', 'is_final' => false],
            ['name' => 'Accepted', 'color' => '#198754', 'is_final' => true],
            ['name' => 'Rejected', 'color' => '#dc3545', 'is_final' => true],
        ];

        $status = $this->faker->randomElement($statuses);

        return [
            'name' => $status['name'],
            'description' => $this->faker->sentence(),
            'color' => $status['color'],
            'is_active' => true,
            'is_final' => $status['is_final'],
            'sort_order' => $this->faker->numberBetween(1, 100),
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
