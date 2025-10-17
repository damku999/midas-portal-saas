<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerInsuranceFactory extends Factory
{
    protected $model = CustomerInsurance::class;

    public function definition()
    {
        return [
            'customer_id' => Customer::factory(),
            'insurance_company_id' => InsuranceCompany::factory(),
            'policy_type_id' => PolicyType::factory(),
            'policy_no' => 'POL-'.$this->faker->unique()->numerify('########'),
            'registration_no' => $this->faker->regexify('[A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{4}'),
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
