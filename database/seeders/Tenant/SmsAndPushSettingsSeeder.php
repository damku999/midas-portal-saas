<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class SmsAndPushSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // ========================================
            // SMS NOTIFICATION SETTINGS
            // ========================================
            [
                'category' => 'notifications',
                'key' => 'sms_notifications_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable/disable SMS notifications globally',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            [
                'category' => 'notifications',
                'key' => 'sms_provider',
                'value' => 'twilio',
                'type' => 'select',
                'description' => 'SMS provider (twilio, nexmo, sns)',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            [
                'category' => 'notifications',
                'key' => 'sms_character_limit',
                'value' => '160',
                'type' => 'number',
                'description' => 'Maximum characters per SMS message',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            [
                'category' => 'notifications',
                'key' => 'sms_sender_id',
                'value' => 'InsureAdv',
                'type' => 'text',
                'description' => 'SMS sender ID/name',
                'is_encrypted' => false,
                'is_active' => true,
            ],

            // Twilio Settings
            [
                'category' => 'notifications',
                'key' => 'sms_twilio_account_sid',
                'value' => '',
                'type' => 'text',
                'description' => 'Twilio Account SID',
                'is_encrypted' => true,
                'is_active' => true,
            ],
            [
                'category' => 'notifications',
                'key' => 'sms_twilio_auth_token',
                'value' => '',
                'type' => 'text',
                'description' => 'Twilio Auth Token',
                'is_encrypted' => true,
                'is_active' => true,
            ],
            [
                'category' => 'notifications',
                'key' => 'sms_twilio_from_number',
                'value' => '',
                'type' => 'text',
                'description' => 'Twilio phone number (with country code)',
                'is_encrypted' => false,
                'is_active' => true,
            ],

            // Nexmo Settings
            [
                'category' => 'notifications',
                'key' => 'sms_nexmo_api_key',
                'value' => '',
                'type' => 'text',
                'description' => 'Nexmo/Vonage API Key',
                'is_encrypted' => true,
                'is_active' => false,
            ],
            [
                'category' => 'notifications',
                'key' => 'sms_nexmo_api_secret',
                'value' => '',
                'type' => 'text',
                'description' => 'Nexmo/Vonage API Secret',
                'is_encrypted' => true,
                'is_active' => false,
            ],
            [
                'category' => 'notifications',
                'key' => 'sms_nexmo_from',
                'value' => '',
                'type' => 'text',
                'description' => 'Nexmo sender name or number',
                'is_encrypted' => false,
                'is_active' => false,
            ],

            // AWS SNS Settings
            [
                'category' => 'notifications',
                'key' => 'sms_sns_key',
                'value' => '',
                'type' => 'text',
                'description' => 'AWS SNS Access Key',
                'is_encrypted' => true,
                'is_active' => false,
            ],
            [
                'category' => 'notifications',
                'key' => 'sms_sns_secret',
                'value' => '',
                'type' => 'text',
                'description' => 'AWS SNS Secret Key',
                'is_encrypted' => true,
                'is_active' => false,
            ],
            [
                'category' => 'notifications',
                'key' => 'sms_sns_region',
                'value' => 'us-east-1',
                'type' => 'text',
                'description' => 'AWS SNS Region',
                'is_encrypted' => false,
                'is_active' => false,
            ],

            // ========================================
            // PUSH NOTIFICATION SETTINGS
            // ========================================
            [
                'category' => 'notifications',
                'key' => 'push_notifications_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable/disable push notifications globally',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            [
                'category' => 'notifications',
                'key' => 'push_fcm_server_key',
                'value' => '',
                'type' => 'textarea',
                'description' => 'Firebase Cloud Messaging Server Key',
                'is_encrypted' => true,
                'is_active' => true,
            ],
            [
                'category' => 'notifications',
                'key' => 'push_fcm_sender_id',
                'value' => '',
                'type' => 'text',
                'description' => 'Firebase Cloud Messaging Sender ID',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            [
                'category' => 'notifications',
                'key' => 'push_default_icon',
                'value' => '/images/logo.png',
                'type' => 'text',
                'description' => 'Default push notification icon URL',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            [
                'category' => 'notifications',
                'key' => 'push_default_sound',
                'value' => 'default',
                'type' => 'text',
                'description' => 'Default push notification sound',
                'is_encrypted' => false,
                'is_active' => true,
            ],

            // ========================================
            // MULTI-CHANNEL SETTINGS
            // ========================================
            [
                'category' => 'notifications',
                'key' => 'quiet_hours_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable quiet hours for notifications',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            [
                'category' => 'notifications',
                'key' => 'quiet_hours_start',
                'value' => '22:00',
                'type' => 'time',
                'description' => 'Quiet hours start time (24-hour format)',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            [
                'category' => 'notifications',
                'key' => 'quiet_hours_end',
                'value' => '08:00',
                'type' => 'time',
                'description' => 'Quiet hours end time (24-hour format)',
                'is_encrypted' => false,
                'is_active' => true,
            ],
            [
                'category' => 'notifications',
                'key' => 'fallback_chain',
                'value' => 'push,whatsapp,sms,email',
                'type' => 'text',
                'description' => 'Default notification fallback chain (comma-separated)',
                'is_encrypted' => false,
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('SMS and Push notification settings seeded successfully!');
    }
}
