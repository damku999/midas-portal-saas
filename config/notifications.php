<?php

/**
 * Notifications Configuration
 *
 * Note: These values are overridden by database settings from app_settings table.
 * See DynamicConfigServiceProvider for runtime configuration loading.
 * Edit values via Admin Panel: /app-settings (category: notifications)
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Email Notifications
    |--------------------------------------------------------------------------
    |
    | Master toggle for all email notifications.
    | When disabled, no email notifications will be sent.
    |
    */
    'email_enabled' => env('EMAIL_NOTIFICATIONS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Notifications
    |--------------------------------------------------------------------------
    |
    | Master toggle for all WhatsApp notifications.
    | When disabled, no WhatsApp messages will be sent.
    |
    */
    'whatsapp_enabled' => env('WHATSAPP_NOTIFICATIONS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Birthday Wishes
    |--------------------------------------------------------------------------
    |
    | Enable or disable automatic birthday wishes to customers.
    | Sent via scheduled command: send:birthday-wishes
    |
    */
    'birthday_wishes_enabled' => env('BIRTHDAY_WISHES_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Renewal Reminder Days
    |--------------------------------------------------------------------------
    |
    | Comma-separated list of days before policy expiry to send reminders.
    | Default: 30,15,7,1 (30 days, 15 days, 7 days, 1 day before expiry)
    |
    */
    'renewal_reminder_days' => env('RENEWAL_REMINDER_DAYS', '30,15,7,1'),

    /*
    |--------------------------------------------------------------------------
    | SMS Notifications
    |--------------------------------------------------------------------------
    |
    | Master toggle for all SMS notifications.
    | When disabled, no SMS messages will be sent.
    |
    */
    'sms_enabled' => env('SMS_NOTIFICATIONS_ENABLED', false),
    'sms_provider' => env('SMS_PROVIDER', 'twilio'),
    'sms_character_limit' => env('SMS_CHARACTER_LIMIT', 160),
    'sms_sender_id' => env('SMS_SENDER_ID', 'InsureAdv'),

    // Twilio Settings
    'twilio_account_sid' => env('TWILIO_ACCOUNT_SID', ''),
    'twilio_auth_token' => env('TWILIO_AUTH_TOKEN', ''),
    'twilio_from_number' => env('TWILIO_FROM_NUMBER', ''),

    /*
    |--------------------------------------------------------------------------
    | Push Notifications
    |--------------------------------------------------------------------------
    |
    | Master toggle for all push notifications.
    | When disabled, no push notifications will be sent.
    |
    */
    'push_enabled' => env('PUSH_NOTIFICATIONS_ENABLED', false),

    // Firebase Cloud Messaging (FCM) Settings
    'fcm_server_key' => env('FCM_SERVER_KEY', ''),
    'fcm_sender_id' => env('FCM_SENDER_ID', ''),
    'push_default_icon' => env('PUSH_DEFAULT_ICON', '/images/logo.png'),
    'push_default_sound' => env('PUSH_DEFAULT_SOUND', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Multi-Channel Settings
    |--------------------------------------------------------------------------
    |
    | Default fallback chain for multi-channel sending
    |
    */
    'fallback_chain' => ['push', 'whatsapp', 'sms', 'email'],

    /*
    |--------------------------------------------------------------------------
    | Quiet Hours
    |--------------------------------------------------------------------------
    |
    | Default quiet hours when intrusive notifications (WhatsApp, SMS) are limited
    |
    */
    'quiet_hours' => [
        'enabled' => env('QUIET_HOURS_ENABLED', true),
        'start' => env('QUIET_HOURS_START', '22:00'),
        'end' => env('QUIET_HOURS_END', '08:00'),
    ],
];
