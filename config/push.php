<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Push Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Push notification settings loaded from app_settings table.
    | These values are managed via Admin Panel: /app-settings
    |
    */

    // Push Notifications Enabled
    'enabled' => env('PUSH_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (FCM) Configuration
    |--------------------------------------------------------------------------
    */

    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY', ''),
        'sender_id' => env('FCM_SENDER_ID', ''),
        'api_url' => 'https://fcm.googleapis.com/fcm/send',
        'topic_prefix' => 'insurance_app_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Push Notification Settings
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'icon' => env('PUSH_DEFAULT_ICON', '/images/logo.png'),
        'sound' => env('PUSH_DEFAULT_SOUND', 'default'),
        'badge' => 1,
        'priority' => 'high',
        'ttl' => 86400,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rich Notification Settings
    |--------------------------------------------------------------------------
    */

    'rich_notifications' => [
        'enabled' => true,

        'max_image_size' => 1024, // KB

        'allowed_image_types' => ['jpg', 'jpeg', 'png'],

        'image_storage_path' => 'push_images',
    ],

    /*
    |--------------------------------------------------------------------------
    | Deep Linking Configuration
    |--------------------------------------------------------------------------
    */

    'deep_linking' => [
        'enabled' => true,

        'scheme' => 'insuranceapp://', // App deep link scheme

        'routes' => [
            'insurance' => 'insuranceapp://insurance/{id}',
            'quotation' => 'insuranceapp://quotation/{id}',
            'claim' => 'insuranceapp://claim/{id}',
            'profile' => 'insuranceapp://profile',
            'home' => 'insuranceapp://home',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Buttons
    |--------------------------------------------------------------------------
    */

    'action_buttons' => [
        'enabled' => true,

        'max_actions' => 3, // Maximum action buttons per notification

        'predefined_actions' => [
            'view' => ['action' => 'view', 'title' => 'View', 'icon' => 'eye'],
            'reply' => ['action' => 'reply', 'title' => 'Reply', 'icon' => 'reply'],
            'dismiss' => ['action' => 'dismiss', 'title' => 'Dismiss', 'icon' => 'close'],
            'renew' => ['action' => 'renew', 'title' => 'Renew Now', 'icon' => 'refresh'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Device Token Management
    |--------------------------------------------------------------------------
    */

    'device_management' => [
        'max_devices_per_customer' => 5, // Maximum devices per customer

        'token_expiry_days' => 90, // Days before inactive device is removed

        'auto_cleanup' => true, // Automatically remove inactive devices
    ],
];
