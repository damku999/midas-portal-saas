<?php

namespace Database\Factories;

use App\Models\TwoFactorAuth;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TwoFactorAuthFactory extends Factory
{
    protected $model = TwoFactorAuth::class;

    public function definition()
    {
        // Generate fake recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(bin2hex(random_bytes(4)));
        }

        return [
            'authenticatable_type' => 'App\\Models\\User',
            'authenticatable_id' => User::factory(),
            'secret' => $this->faker->regexify('[A-Z2-7]{32}'), // Base32 TOTP secret
            'recovery_codes' => $recoveryCodes,
            'enabled_at' => now(),
            'confirmed_at' => now(),
            'is_active' => true,
            'backup_method' => $this->faker->randomElement(['sms', 'email']),
            'backup_destination' => $this->faker->phoneNumber(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'confirmed_at' => null,
                'is_active' => false,
            ];
        });
    }

    public function disabled()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
                'secret' => null,
                'recovery_codes' => null,
            ];
        });
    }
}
