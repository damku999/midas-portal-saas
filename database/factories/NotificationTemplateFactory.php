<?php

namespace Database\Factories;

use App\Models\NotificationTemplate;
use App\Models\NotificationType;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    public function definition()
    {
        return [
            'notification_type_id' => NotificationType::factory(),
            'channel' => $this->faker->randomElement(['whatsapp', 'email', 'sms']),
            'subject' => $this->faker->sentence(),
            'template_content' => $this->faker->paragraph().' {customer_name} {policy_number}',
            'available_variables' => ['customer_name', 'policy_number', 'company_name', 'date'],
            'sample_output' => $this->faker->paragraph(),
            'is_active' => true,
            'updated_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function whatsapp()
    {
        return $this->state(function (array $attributes) {
            return [
                'channel' => 'whatsapp',
                'subject' => null,
            ];
        });
    }

    public function email()
    {
        return $this->state(function (array $attributes) {
            return [
                'channel' => 'email',
                'subject' => $this->faker->sentence(),
            ];
        });
    }
}
