<?php

/**
 * Payment Gateway Configuration
 *
 * SECURITY FIX #8: Bank details moved from hardcoded values to environment variables
 * All sensitive payment information should be stored in .env file
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Bank Transfer Details
    |--------------------------------------------------------------------------
    |
    | Bank account details for manual bank transfers. These values should be
    | configured in the .env file and never committed to version control.
    |
    */

    'bank' => [
        'account_name' => env('BANK_ACCOUNT_NAME', 'Company Name'),
        'account_number' => env('BANK_ACCOUNT_NUMBER', ''),
        'ifsc_code' => env('BANK_IFSC_CODE', ''),
        'bank_name' => env('BANK_NAME', ''),
        'branch' => env('BANK_BRANCH', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Razorpay Configuration
    |--------------------------------------------------------------------------
    */

    'razorpay' => [
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe Configuration
    |--------------------------------------------------------------------------
    */

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Settings
    |--------------------------------------------------------------------------
    */

    'default_gateway' => env('DEFAULT_PAYMENT_GATEWAY', 'razorpay'),
    'currency' => env('PAYMENT_CURRENCY', 'INR'),

];
