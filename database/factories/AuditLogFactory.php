<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition()
    {
        $eventCategories = ['authentication', 'data_modification', 'access', 'configuration', 'security'];
        $events = ['login', 'logout', 'create', 'update', 'delete', 'view', 'export'];
        $riskLevels = ['low', 'medium', 'high', 'critical'];

        return [
            'auditable_type' => 'App\\Models\\User',
            'auditable_id' => User::factory(),
            'actor_type' => 'App\\Models\\User',
            'actor_id' => User::factory(),
            'event' => $this->faker->randomElement($events),
            'event_category' => $this->faker->randomElement($eventCategories),
            'old_values' => [],
            'new_values' => ['field' => $this->faker->word()],
            'metadata' => ['source' => 'web', 'action' => $this->faker->word()],
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'session_id' => $this->faker->uuid(),
            'request_id' => $this->faker->uuid(),
            'risk_score' => $this->faker->numberBetween(0, 100),
            'risk_level' => $this->faker->randomElement($riskLevels),
            'risk_factors' => [],
            'is_suspicious' => false,
            'location_country' => $this->faker->country(),
            'location_city' => $this->faker->city(),
            'location_lat' => $this->faker->latitude(),
            'location_lng' => $this->faker->longitude(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function suspicious()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_suspicious' => true,
                'risk_score' => $this->faker->numberBetween(70, 100),
                'risk_level' => $this->faker->randomElement(['high', 'critical']),
                'risk_factors' => ['unusual_location', 'unusual_time', 'multiple_failures'],
            ];
        });
    }
}
