<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | SMS notification settings loaded from app_settings table.
    | These values are managed via Admin Panel: /app-settings
    |
    */

    // SMS Provider (twilio, nexmo, sns)
    'provider' => env('SMS_PROVIDER', 'twilio'),

    // SMS Enabled
    'enabled' => env('SMS_ENABLED', false),

    // SMS Character Limit
    'character_limit' => env('SMS_CHARACTER_LIMIT', 160),

    // SMS Sender ID
    'sender_id' => env('SMS_SENDER_ID', 'InsureAdv'),

    /*
    |--------------------------------------------------------------------------
    | Twilio Configuration
    |--------------------------------------------------------------------------
    */

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID', ''),
        'auth_token' => env('TWILIO_AUTH_TOKEN', ''),
        'from_number' => env('TWILIO_FROM_NUMBER', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Nexmo/Vonage Configuration
    |--------------------------------------------------------------------------
    */

    'nexmo' => [
        'api_key' => env('NEXMO_API_KEY', ''),
        'api_secret' => env('NEXMO_API_SECRET', ''),
        'from' => env('NEXMO_FROM', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS SNS Configuration
    |--------------------------------------------------------------------------
    */

    'sns' => [
        'key' => env('AWS_SNS_KEY', ''),
        'secret' => env('AWS_SNS_SECRET', ''),
        'region' => env('AWS_SNS_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | URL Shortening
    |--------------------------------------------------------------------------
    */

    'url_shortening' => [
        'enabled' => false, // Enable URL shortening for SMS
        'service' => 'bitly', // bitly, tinyurl, custom
        'api_key' => env('URL_SHORTENER_API_KEY', ''),
    ],
];
