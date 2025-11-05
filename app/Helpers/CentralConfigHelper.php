<?php

/**
 * Central Configuration Helper
 *
 * Provides default configuration values from the central application
 * that new tenants can inherit or override.
 */

if (!function_exists('central_config')) {
    /**
     * Get central configuration value
     *
     * These are default values defined at the central/infrastructure level
     * that new tenants inherit unless they provide custom values.
     *
     * @param string $key Configuration key
     * @param mixed $default Fallback default value
     * @return mixed
     */
    function central_config(string $key, $default = null)
    {
        return match ($key) {
            // Branding Assets
            'logo' => env('CENTRAL_LOGO_PATH', 'images/logo.png'),
            'favicon' => env('CENTRAL_FAVICON_PATH', 'images/logo-icon@2000x.png'),
            'logo_alt' => env('CENTRAL_LOGO_ALT', 'Midas Tech'),

            // Theme Colors
            'primary_color' => env('CENTRAL_PRIMARY_COLOR', '#17a2b8'),
            'secondary_color' => env('CENTRAL_SECONDARY_COLOR', '#6c757d'),
            'success_color' => env('CENTRAL_SUCCESS_COLOR', '#28a745'),
            'info_color' => env('CENTRAL_INFO_COLOR', '#5fd0e3'),
            'warning_color' => env('CENTRAL_WARNING_COLOR', '#f6c23e'),
            'danger_color' => env('CENTRAL_DANGER_COLOR', '#e74a3b'),

            // Footer Branding
            'developer_name' => env('CENTRAL_DEVELOPER_NAME', 'Midas Tech'),
            'developer_url' => env('CENTRAL_DEVELOPER_URL', 'https://midastech.in'),
            'copyright_text' => env('CENTRAL_COPYRIGHT_TEXT', 'Copyright © Midas Tech'),

            // WhatsApp Configuration
            'whatsapp_sender' => env('CENTRAL_WHATSAPP_SENDER', config('whatsapp.sender_id', '919800071314')),
            'whatsapp_auth_token' => env('CENTRAL_WHATSAPP_AUTH_TOKEN', config('whatsapp.auth_token', '')),
            'whatsapp_base_url' => env('CENTRAL_WHATSAPP_BASE_URL', config('whatsapp.base_url', 'https://api.botmastersender.com/api/v1/')),

            // Email Configuration
            'mail_from_address' => env('CENTRAL_MAIL_FROM', 'support@midastech.in'),
            'mail_from_name' => env('CENTRAL_MAIL_FROM_NAME', 'Midas Portal'),
            'mail_host' => env('CENTRAL_MAIL_HOST', config('mail.mailers.smtp.host', 'smtp.hostinger.com')),
            'mail_port' => env('CENTRAL_MAIL_PORT', config('mail.mailers.smtp.port', '465')),
            'mail_encryption' => env('CENTRAL_MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption', 'ssl')),
            'mail_username' => env('CENTRAL_MAIL_USERNAME', config('mail.mailers.smtp.username', '')),
            'mail_password' => env('CENTRAL_MAIL_PASSWORD', config('mail.mailers.smtp.password', '')),

            // Localization Defaults
            'timezone' => env('CENTRAL_TIMEZONE', 'Asia/Kolkata'),
            'locale' => env('CENTRAL_LOCALE', 'en'),
            'currency' => env('CENTRAL_CURRENCY', 'INR'),
            'currency_symbol' => env('CENTRAL_CURRENCY_SYMBOL', '₹'),
            'date_format' => env('CENTRAL_DATE_FORMAT', 'd/m/Y'),
            'time_format' => env('CENTRAL_TIME_FORMAT', '12h'),

            // Application Defaults
            'pagination_default' => env('CENTRAL_PAGINATION_DEFAULT', 15),
            'session_lifetime' => env('CENTRAL_SESSION_LIFETIME', 120),

            // Fallback for unknown keys
            default => $default
        };
    }
}

if (!function_exists('central_logo')) {
    /**
     * Get central logo path
     */
    function central_logo(): string
    {
        return central_config('logo');
    }
}

if (!function_exists('central_logo_asset')) {
    /**
     * Get full central logo asset URL
     */
    function central_logo_asset(): string
    {
        return asset(central_logo());
    }
}

if (!function_exists('central_favicon')) {
    /**
     * Get central favicon path
     */
    function central_favicon(): string
    {
        return central_config('favicon');
    }
}

if (!function_exists('central_favicon_asset')) {
    /**
     * Get full central favicon asset URL
     */
    function central_favicon_asset(): string
    {
        return asset(central_favicon());
    }
}

if (!function_exists('central_primary_color')) {
    /**
     * Get central primary brand color
     */
    function central_primary_color(): string
    {
        return central_config('primary_color');
    }
}

if (!function_exists('central_whatsapp_config')) {
    /**
     * Get complete central WhatsApp configuration
     */
    function central_whatsapp_config(): array
    {
        return [
            'sender_id' => central_config('whatsapp_sender'),
            'auth_token' => central_config('whatsapp_auth_token'),
            'base_url' => central_config('whatsapp_base_url'),
        ];
    }
}

if (!function_exists('central_mail_config')) {
    /**
     * Get complete central mail configuration
     */
    function central_mail_config(): array
    {
        return [
            'from_address' => central_config('mail_from_address'),
            'from_name' => central_config('mail_from_name'),
            'host' => central_config('mail_host'),
            'port' => central_config('mail_port'),
            'encryption' => central_config('mail_encryption'),
            'username' => central_config('mail_username'),
            'password' => central_config('mail_password'),
        ];
    }
}
