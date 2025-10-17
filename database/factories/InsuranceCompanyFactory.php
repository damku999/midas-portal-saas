<?php

namespace Database\Factories;

use App\Models\InsuranceCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

class InsuranceCompanyFactory extends Factory
{
    protected $model = InsuranceCompany::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company().' Insurance',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => 0,
            'updated_by' => 0,
        ];
    }
}
