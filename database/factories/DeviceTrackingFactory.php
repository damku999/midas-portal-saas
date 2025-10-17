<?php

namespace Database\Factories;

use App\Models\DeviceTracking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceTrackingFactory extends Factory
{
    protected $model = DeviceTracking::class;

    public function definition()
    {
        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge'];
        $os = ['Windows', 'macOS', 'Linux', 'Android', 'iOS'];
        $deviceTypes = ['desktop', 'mobile', 'tablet'];

        return [
            'trackable_type' => 'App\\Models\\User',
            'trackable_id' => User::factory(),
            'device_id' => 'device_'.$this->faker->uuid(),
            'device_name' => $this->faker->randomElement(['My Laptop', 'My Phone', 'Office PC', 'Home Desktop']),
            'device_type' => $this->faker->randomElement($deviceTypes),
            'browser' => $this->faker->randomElement($browsers),
            'browser_version' => $this->faker->numerify('##.#'),
            'operating_system' => $this->faker->randomElement($os),
            'os_version' => $this->faker->numerify('##.#'),
            'platform' => $this->faker->randomElement($os),
            'screen_resolution' => ['width' => 1920, 'height' => 1080],
            'hardware_info' => ['cpu_cores' => 4, 'memory' => '8GB'],
            'user_agent' => $this->faker->userAgent(),
            'fingerprint_data' => ['canvas' => $this->faker->sha256(), 'webgl' => $this->faker->sha256()],
            'trust_score' => $this->faker->numberBetween(0, 100),
            'is_trusted' => false,
            'first_seen_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'last_seen_at' => now(),
            'trusted_at' => null,
            'trust_expires_at' => null,
            'location_history' => [],
            'ip_history' => [],
            'login_count' => $this->faker->numberBetween(1, 50),
            'failed_login_attempts' => 0,
            'last_failed_login_at' => null,
            'is_blocked' => false,
            'blocked_reason' => null,
            'blocked_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function trusted()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_trusted' => true,
                'trust_score' => $this->faker->numberBetween(70, 100),
                'trusted_at' => now()->subDays($this->faker->numberBetween(1, 30)),
                'trust_expires_at' => now()->addDays(30),
            ];
        });
    }

    public function blocked()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_blocked' => true,
                'is_trusted' => false,
                'blocked_reason' => 'Too many failed login attempts',
                'blocked_at' => now(),
                'failed_login_attempts' => $this->faker->numberBetween(5, 10),
            ];
        });
    }
}
