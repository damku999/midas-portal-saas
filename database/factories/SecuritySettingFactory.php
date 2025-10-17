<?php

namespace Database\Factories;

use App\Models\SecuritySetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecuritySettingFactory extends Factory
{
    protected $model = SecuritySetting::class;

    public function definition()
    {
        return [
            'settingable_type' => 'App\\Models\\User',
            'settingable_id' => User::factory(),
            'two_factor_enabled' => false,
            'device_tracking_enabled' => true,
            'login_notifications' => true,
            'security_alerts' => true,
            'session_timeout' => 7200, // 2 hours in seconds
            'device_trust_duration' => 30, // 30 days
            'notification_preferences' => [
                'email_login_alerts' => true,
                'email_security_alerts' => true,
                'email_2fa_changes' => true,
                'sms_security_alerts' => false,
                'sms_login_alerts' => false,
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function withTwoFactor()
    {
        return $this->state(function (array $attributes) {
            return [
                'two_factor_enabled' => true,
            ];
        });
    }
}
