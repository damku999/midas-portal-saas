<?php

namespace Database\Factories;

use App\Models\TwoFactorAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TwoFactorAttemptFactory extends Factory
{
    protected $model = TwoFactorAttempt::class;

    public function definition()
    {
        $codeTypes = ['totp', 'recovery', 'sms'];
        $successful = $this->faker->boolean(70);

        return [
            'authenticatable_type' => 'App\\Models\\User',
            'authenticatable_id' => User::factory(),
            'code_type' => $this->faker->randomElement($codeTypes),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'successful' => $successful,
            'failure_reason' => $successful ? null : $this->faker->randomElement(['Invalid code', 'Expired code', 'Too many attempts']),
            'attempted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function successful()
    {
        return $this->state(function (array $attributes) {
            return [
                'successful' => true,
                'failure_reason' => null,
            ];
        });
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'successful' => false,
                'failure_reason' => $this->faker->randomElement(['Invalid code', 'Expired code', 'Too many attempts']),
            ];
        });
    }
}
