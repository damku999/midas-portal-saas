<?php

/**
 * WhatsApp API Configuration
 *
 * Note: These values are overridden by database settings from app_settings table.
 * See DynamicConfigServiceProvider for runtime configuration loading.
 * Edit values via Admin Panel: /app-settings (category: whatsapp)
 */

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Sender ID
    |--------------------------------------------------------------------------
    |
    | The WhatsApp sender ID used for sending messages via the API.
    | This is typically your WhatsApp business number in international format
    | without + or spaces (e.g., 919727793123 for +91 97277 93123)
    |
    */
    'sender_id' => env('WHATSAPP_SENDER_ID', '919727793123'),

    /*
    |--------------------------------------------------------------------------
    | WhatsApp API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for your WhatsApp API provider.
    | Include the trailing slash.
    |
    */
    'base_url' => env('WHATSAPP_BASE_URL', 'https://api.botmastersender.com/api/v1/'),

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Authentication Token
    |--------------------------------------------------------------------------
    |
    | The authentication token/API key for your WhatsApp API provider.
    | Keep this secure and never commit to version control.
    |
    */
    'auth_token' => env('WHATSAPP_AUTH_TOKEN', ''),
];
