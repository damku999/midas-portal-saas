<?php

namespace Database\Factories;

use App\Models\AppSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppSettingFactory extends Factory
{
    protected $model = AppSetting::class;

    // Static counter to ensure unique keys across test suite
    protected static $keyCounter = 0;

    public function definition()
    {
        $types = ['string', 'integer', 'boolean', 'json', 'text'];
        $categories = ['general', 'email', 'sms', 'whatsapp', 'notification', 'business', 'technical', 'security'];

        // Generate unique key using timestamp + counter to avoid database duplicates
        self::$keyCounter++;
        $uniqueKey = 'test_setting_'.time().'_'.self::$keyCounter.'_'.$this->faker->unique()->numberBetween(1000, 9999);

        return [
            'key' => $uniqueKey,
            'value' => $this->faker->word(),
            'type' => $this->faker->randomElement($types),
            'category' => $this->faker->randomElement($categories),
            'description' => $this->faker->sentence(),
            'is_encrypted' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function encrypted()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_encrypted' => true,
            ];
        });
    }
}
