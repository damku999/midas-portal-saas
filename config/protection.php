<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Protected Records Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the protected records system that
    | prevents deletion or modification of critical system records.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Protected Email Addresses
    |--------------------------------------------------------------------------
    |
    | Specific email addresses that should be protected from deletion and
    | status changes. These are typically super-admin accounts.
    |
    */
    'protected_emails' => [
        'webmonks.in@gmail.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Protected Email Domains
    |--------------------------------------------------------------------------
    |
    | Email domains where ALL emails should be protected. Any email ending
    | with these domains will be automatically protected.
    |
    */
    'protected_domains' => [
        'webmonks.in',
    ],

    /*
    |--------------------------------------------------------------------------
    | Protection Rules
    |--------------------------------------------------------------------------
    |
    | Configure what actions are blocked for protected records.
    |
    */
    'rules' => [
        'prevent_deletion' => true,
        'prevent_soft_deletion' => true,
        'prevent_force_deletion' => true,
        'prevent_status_deactivation' => true,
        'prevent_email_change' => true,
        'prevent_role_change' => false, // Allow role changes for protected users
    ],

    /*
    |--------------------------------------------------------------------------
    | Protection Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for protection system events.
    |
    */
    'logging' => [
        'enabled' => true,
        'log_channel' => env('PROTECTION_LOG_CHANNEL', 'stack'),
        'log_level' => env('PROTECTION_LOG_LEVEL', 'warning'),
        'log_to_database' => true,
        'log_table' => 'audit_logs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Protection Messages
    |--------------------------------------------------------------------------
    |
    | User-friendly messages displayed when protection is triggered.
    |
    */
    'messages' => [
        'deletion_prevented' => 'This record is protected and cannot be deleted. It belongs to the Webmonks system administration.',
        'status_change_prevented' => 'This record is protected and cannot be deactivated. It belongs to the Webmonks system administration.',
        'email_change_prevented' => 'This record is protected and the email address cannot be modified. It belongs to the Webmonks system administration.',
        'modification_prevented' => 'This record is protected and cannot be modified in this way. It belongs to the Webmonks system administration.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Emergency Bypass
    |--------------------------------------------------------------------------
    |
    | Emergency bypass configuration for critical situations.
    | WARNING: Use with extreme caution!
    |
    */
    'emergency_bypass' => [
        'enabled' => env('PROTECTION_EMERGENCY_BYPASS', false),
        'requires_confirmation' => true,
        'log_all_bypasses' => true,
        'bypass_key' => env('PROTECTION_BYPASS_KEY', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Protected Models
    |--------------------------------------------------------------------------
    |
    | List of models that use the protection system. Used for bulk operations
    | and system-wide protection checks.
    |
    */
    'protected_models' => [
        \App\Models\User::class,
        \App\Models\Customer::class,
        \App\Models\Lead::class,
        \App\Models\Broker::class,
        \App\Models\Branch::class,
        \App\Models\ReferenceUser::class,
        \App\Models\RelationshipManager::class,
        \App\Models\InsuranceCompany::class,
    ],

];
