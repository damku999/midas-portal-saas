<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerAuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerAuditLogFactory extends Factory
{
    protected $model = CustomerAuditLog::class;

    public function definition()
    {
        return [
            'customer_id' => Customer::factory(),
            'action' => $this->faker->randomElement(['login', 'logout', 'profile_update', 'password_change', 'view_policy', 'download_document']),
            'success' => $this->faker->boolean(90), // 90% success rate
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'metadata' => json_encode([
                'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
                'platform' => $this->faker->randomElement(['Windows', 'MacOS', 'Linux', 'iOS', 'Android']),
                'session_id' => $this->faker->uuid(),
            ]),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the action was successful.
     */
    public function successful()
    {
        return $this->state(function (array $attributes) {
            return [
                'success' => true,
            ];
        });
    }

    /**
     * Indicate that the action failed.
     */
    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'success' => false,
                'metadata' => json_encode(array_merge(
                    json_decode($attributes['metadata'], true),
                    ['error' => $this->faker->sentence()]
                )),
            ];
        });
    }

    /**
     * Create for a specific action.
     */
    public function action(string $action)
    {
        return $this->state(function (array $attributes) use ($action) {
            return [
                'action' => $action,
            ];
        });
    }
}
