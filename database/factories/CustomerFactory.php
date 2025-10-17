<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'mobile_number' => $this->faker->phoneNumber(),
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
