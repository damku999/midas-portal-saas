<?php

namespace Database\Factories;

use App\Models\NotificationType;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationTypeFactory extends Factory
{
    protected $model = NotificationType::class;

    public function definition()
    {
        $types = [
            ['name' => 'Birthday Wishes', 'code' => 'birthday', 'category' => 'personal'],
            ['name' => 'Anniversary Wishes', 'code' => 'anniversary', 'category' => 'personal'],
            ['name' => 'Policy Renewal', 'code' => 'renewal', 'category' => 'policy'],
            ['name' => 'Policy Expiry', 'code' => 'expiry', 'category' => 'policy'],
            ['name' => 'Claim Status', 'code' => 'claim_status', 'category' => 'claim'],
        ];

        $type = $this->faker->randomElement($types);

        return [
            'name' => $type['name'],
            'code' => $type['code'],
            'category' => $type['category'],
            'description' => $this->faker->sentence(),
            'default_whatsapp_enabled' => $this->faker->boolean(80),
            'default_email_enabled' => $this->faker->boolean(60),
            'is_active' => true,
            'order_no' => $this->faker->numberBetween(1, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
