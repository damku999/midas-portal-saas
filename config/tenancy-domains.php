<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Tenant Domains
    |--------------------------------------------------------------------------
    |
    | List of domains that can be used for tenant subdomains. The first one
    | will be used as the default in production. For local development, you
    | can add additional domains like .midastech.testing.in
    |
    */

    'domains' => [
        'midastech.in' => [
            'label' => 'midastech.in (Production)',
            'enabled' => true,
            'environment' => ['production'],
        ],
        'midastech.testing.in' => [
            'label' => 'midastech.testing.in (Testing)',
            'enabled' => true,
            'environment' => ['local', 'testing'],
        ],
        'localhost:8085' => [
            'label' => 'localhost:8085 (Local)',
            'enabled' => true,
            'environment' => ['local'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Domain
    |--------------------------------------------------------------------------
    |
    | This is the default domain that will be pre-selected in the form.
    | It's based on the APP_DOMAIN environment variable, but you can
    | change it here if needed.
    |
    */

    'default' => env('APP_DOMAIN', 'midastech.in'),

    /*
    |--------------------------------------------------------------------------
    | Auto-detect Environment
    |--------------------------------------------------------------------------
    |
    | If enabled, only domains matching the current environment will be shown.
    | If disabled, all enabled domains will be shown.
    |
    */

    'auto_detect_environment' => true,
];
