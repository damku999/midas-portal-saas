<?php

namespace Database\Factories;

use App\Models\TrustedDevice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrustedDeviceFactory extends Factory
{
    protected $model = TrustedDevice::class;

    public function definition()
    {
        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge'];
        $platforms = ['Windows', 'macOS', 'Linux', 'Android', 'iOS'];
        $deviceTypes = ['desktop', 'mobile', 'tablet'];

        return [
            'authenticatable_type' => 'App\\Models\\User',
            'authenticatable_id' => User::factory(),
            'device_id' => $this->faker->sha256(),
            'device_name' => $this->faker->randomElement(['My Laptop', 'My Phone', 'Office PC', 'Home Desktop']),
            'device_type' => $this->faker->randomElement($deviceTypes),
            'browser' => $this->faker->randomElement($browsers),
            'platform' => $this->faker->randomElement($platforms),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'last_used_at' => now(),
            'trusted_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'expires_at' => now()->addDays(30),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => now()->subDays(1),
                'is_active' => false,
            ];
        });
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
