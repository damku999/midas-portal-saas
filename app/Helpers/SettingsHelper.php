<?php

if (! function_exists('app_currency')) {
    function app_currency(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('app_currency', 'application', 'INR');
    }
}

if (! function_exists('app_currency_symbol')) {
    function app_currency_symbol(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('app_currency_symbol', 'application', '₹');
    }
}

if (! function_exists('app_date_format')) {
    function app_date_format(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('app_date_format', 'application', 'd/m/Y');
    }
}

if (! function_exists('app_time_format')) {
    function app_time_format(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('app_time_format', 'application', '12h');
    }
}

if (! function_exists('format_indian_currency')) {
    function format_indian_currency($amount): string
    {
        return app_currency_symbol().' '.number_format($amount, 2);
    }
}

if (! function_exists('format_app_date')) {
    function format_app_date($date): string
    {
        if (! $date) {
            return 'N/A';
        }

        return \Carbon\Carbon::parse($date)->format(app_date_format());
    }
}

if (! function_exists('format_app_time')) {
    function format_app_time($datetime): string
    {
        if (! $datetime) {
            return 'N/A';
        }
        $format = app_time_format() === '24h' ? 'H:i' : 'h:i A';

        return \Carbon\Carbon::parse($datetime)->format($format);
    }
}

if (! function_exists('format_app_datetime')) {
    function format_app_datetime($datetime): string
    {
        if (! $datetime) {
            return 'N/A';
        }
        $dateFormat = app_date_format();
        $timeFormat = app_time_format() === '24h' ? 'H:i' : 'h:i A';

        return \Carbon\Carbon::parse($datetime)->format($dateFormat.' '.$timeFormat);
    }
}

if (! function_exists('pagination_per_page')) {
    /**
     * Get default pagination items per page
     */
    function pagination_per_page(): int
    {
        return (int) app(\App\Services\AppSettingService::class)
            ->get('pagination_default', 'application', 15);
    }
}

if (! function_exists('is_email_notification_enabled')) {
    function is_email_notification_enabled(): bool
    {
        return app(\App\Services\AppSettingService::class)
            ->get('email_notifications_enabled', 'notifications', true) === 'true';
    }
}

if (! function_exists('is_whatsapp_notification_enabled')) {
    function is_whatsapp_notification_enabled(): bool
    {
        return app(\App\Services\AppSettingService::class)
            ->get('whatsapp_notifications_enabled', 'notifications', true) === 'true';
    }
}

if (! function_exists('is_birthday_wishes_enabled')) {
    function is_birthday_wishes_enabled(): bool
    {
        return app(\App\Services\AppSettingService::class)
            ->get('birthday_wishes_enabled', 'notifications', true) === 'true';
    }
}

if (! function_exists('get_renewal_reminder_days')) {
    function get_renewal_reminder_days(): array
    {
        $days = app(\App\Services\AppSettingService::class)
            ->get('renewal_reminder_days', 'notifications', '30,15,7,1');

        return array_map('intval', explode(',', $days));
    }
}

if (! function_exists('company_name')) {
    function company_name(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('company_name', 'company', 'Parth Rawal Insurance Advisor');
    }
}

if (! function_exists('company_advisor_name')) {
    function company_advisor_name(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('company_advisor_name', 'company', 'Parth Rawal');
    }
}

if (! function_exists('company_website')) {
    function company_website(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('company_website', 'company', 'https://parthrawal.in');
    }
}

if (! function_exists('company_phone')) {
    function company_phone(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('company_phone', 'company', '+91 97277 93123');
    }
}

if (! function_exists('company_phone_whatsapp')) {
    function company_phone_whatsapp(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('company_phone_whatsapp', 'company', '919727793123');
    }
}

if (! function_exists('company_title')) {
    function company_title(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('company_title', 'company', 'Your Trusted Insurance Advisor');
    }
}

if (! function_exists('company_tagline')) {
    function company_tagline(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('company_tagline', 'company', 'Think of Insurance, Think of Us.');
    }
}

if (! function_exists('email_from_address')) {
    function email_from_address(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('email_from_address', config('mail.from.address', 'noreply@example.com'));
    }
}

if (! function_exists('email_from_name')) {
    function email_from_name(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('email_from_name', config('mail.from.name', company_name()));
    }
}

if (! function_exists('email_reply_to')) {
    function email_reply_to(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('email_reply_to', email_from_address());
    }
}

// ========================================
// Security Helper Functions
// ========================================

if (! function_exists('is_system_admin')) {
    /**
     * Check if an email belongs to a system administrator
     * Supports exact email match and domain wildcards (e.g., @webmonks.in)
     */
    function is_system_admin(string $email): bool
    {
        $adminEmails = app(\App\Services\AppSettingService::class)
            ->get('system_admin_emails', 'application', '');

        if (empty($adminEmails)) {
            return false;
        }

        $emailList = array_map('trim', explode(',', $adminEmails));

        // Check exact match
        if (in_array($email, $emailList, true)) {
            return true;
        }

        // Check domain match (e.g., @webmonks.in matches any email ending with @webmonks.in)
        foreach ($emailList as $adminEmail) {
            if (str_starts_with($adminEmail, '@') && str_ends_with($email, $adminEmail)) {
                return true;
            }
        }

        return false;
    }
}

// ========================================
// CDN & Assets Helper Functions
// ========================================

if (! function_exists('cdn_url')) {
    /**
     * Get CDN URL from app settings
     */
    function cdn_url(string $key, string $default = ''): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get($key, 'cdn', $default);
    }
}

if (! function_exists('versioned_asset')) {
    /**
     * Get asset URL with version query string for cache busting
     */
    function versioned_asset(string $path): string
    {
        $version = app(\App\Services\AppSettingService::class)
            ->get('assets_version', 'assets', '1.0.0');
        $useCacheBusting = app(\App\Services\AppSettingService::class)
            ->get('assets_cache_busting', 'assets', 'true') === 'true';

        $url = asset($path);

        if ($useCacheBusting) {
            return $url . '?v=' . $version;
        }

        return $url;
    }
}

// ========================================
// Branding Helper Functions
// ========================================

if (! function_exists('company_logo')) {
    /**
     * Get company logo path or alt text
     */
    function company_logo(string $type = 'path'): string
    {
        $key = $type === 'alt' ? 'company_logo_alt' : 'company_logo_path';
        $default = $type === 'alt' ? 'Company Logo' : 'images/parth_logo.png';

        return app(\App\Services\AppSettingService::class)
            ->get($key, 'branding', $default);
    }
}

if (! function_exists('company_logo_asset')) {
    /**
     * Get full company logo asset URL
     */
    function company_logo_asset(): string
    {
        return asset(company_logo());
    }
}

if (! function_exists('company_favicon')) {
    /**
     * Get company favicon path
     */
    function company_favicon(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('company_favicon_path', 'branding', 'images/icon.png');
    }
}

if (! function_exists('company_favicon_asset')) {
    /**
     * Get full company favicon asset URL
     */
    function company_favicon_asset(): string
    {
        return asset(company_favicon());
    }
}

// ========================================
// Footer Helper Functions
// ========================================

if (! function_exists('footer_developer_name')) {
    /**
     * Get footer developer name
     */
    function footer_developer_name(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('footer_developer_name', 'footer', 'Developer');
    }
}

if (! function_exists('footer_developer_url')) {
    /**
     * Get footer developer URL
     */
    function footer_developer_url(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('footer_developer_url', 'footer', '#');
    }
}

if (! function_exists('footer_copyright_text')) {
    /**
     * Get footer copyright text
     */
    function footer_copyright_text(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('footer_copyright_text', 'footer', 'Copyright');
    }
}

if (! function_exists('show_footer_developer')) {
    /**
     * Check if developer credit should be shown in footer
     */
    function show_footer_developer(): bool
    {
        return app(\App\Services\AppSettingService::class)
            ->get('footer_show_developer', 'footer', 'true') === 'true';
    }
}

if (! function_exists('show_footer_year')) {
    /**
     * Check if year should be shown in footer
     */
    function show_footer_year(): bool
    {
        return app(\App\Services\AppSettingService::class)
            ->get('footer_show_year', 'footer', 'true') === 'true';
    }
}

// ========================================
// Generic Helper Functions
// ========================================

if (! function_exists('app_setting')) {
    /**
     * Generic helper to get any app setting
     */
    function app_setting(string $key, string $category, $default = null)
    {
        return app(\App\Services\AppSettingService::class)
            ->get($key, $category, $default);
    }
}

// ========================================
// Theme Helper Functions
// ========================================

if (! function_exists('theme_color')) {
    /**
     * Get theme color setting
     */
    function theme_color(string $colorType): string
    {
        $key = 'theme_' . $colorType . '_color';
        $defaults = [
            'primary' => '#4e73df',
            'secondary' => '#858796',
            'success' => '#1cc88a',
            'info' => '#36b9cc',
            'warning' => '#f6c23e',
            'danger' => '#e74a3b',
            'light' => '#f8f9fc',
            'dark' => '#5a5c69',
        ];

        return app(\App\Services\AppSettingService::class)
            ->get($key, 'theme', $defaults[$colorType] ?? '#000000');
    }
}

if (! function_exists('theme_primary_color')) {
    function theme_primary_color(): string
    {
        return theme_color('primary');
    }
}

if (! function_exists('theme_sidebar_bg_color')) {
    function theme_sidebar_bg_color(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_sidebar_bg_color', 'theme', '#4e73df');
    }
}

if (! function_exists('theme_sidebar_text_color')) {
    function theme_sidebar_text_color(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_sidebar_text_color', 'theme', '#ffffff');
    }
}

if (! function_exists('theme_primary_font')) {
    function theme_primary_font(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_primary_font', 'theme', 'Inter');
    }
}

if (! function_exists('theme_secondary_font')) {
    function theme_secondary_font(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_secondary_font', 'theme', 'Nunito');
    }
}

if (! function_exists('theme_mode')) {
    function theme_mode(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_mode', 'theme', 'light');
    }
}

if (! function_exists('is_dark_mode_enabled')) {
    function is_dark_mode_enabled(): bool
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_enable_dark_mode', 'theme', 'false') === 'true';
    }
}

// ========================================
// SMS Helper Functions
// ========================================

if (! function_exists('is_sms_enabled')) {
    /**
     * Check if SMS notifications are enabled
     */
    function is_sms_enabled(): bool
    {
        return app(\App\Services\AppSettingService::class)
            ->get('sms_enabled', 'sms', 'false') === 'true';
    }
}

if (! function_exists('sms_provider')) {
    /**
     * Get SMS provider (twilio, nexmo, sns)
     */
    function sms_provider(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('sms_provider', 'sms', 'twilio');
    }
}

if (! function_exists('sms_character_limit')) {
    /**
     * Get SMS character limit
     */
    function sms_character_limit(): int
    {
        return (int) app(\App\Services\AppSettingService::class)
            ->get('sms_character_limit', 'sms', 160);
    }
}

// ========================================
// Push Notification Helper Functions
// ========================================

if (! function_exists('is_push_enabled')) {
    /**
     * Check if push notifications are enabled
     */
    function is_push_enabled(): bool
    {
        return app(\App\Services\AppSettingService::class)
            ->get('push_enabled', 'push', 'false') === 'true';
    }
}

if (! function_exists('is_push_deep_linking_enabled')) {
    /**
     * Check if deep linking is enabled in push notifications
     */
    function is_push_deep_linking_enabled(): bool
    {
        return app(\App\Services\AppSettingService::class)
            ->get('push_deep_linking_enabled', 'push', 'true') === 'true';
    }
}

if (! function_exists('is_push_action_buttons_enabled')) {
    /**
     * Check if action buttons are enabled in push notifications
     */
    function is_push_action_buttons_enabled(): bool
    {
        return app(\App\Services\AppSettingService::class)
            ->get('push_action_buttons_enabled', 'push', 'true') === 'true';
    }
}

// ========================================
// Chart Helper Functions
// ========================================

if (! function_exists('chart_color')) {
    /**
     * Get chart color setting
     */
    function chart_color(string $colorType): string
    {
        $key = 'chart_color_' . $colorType;
        $defaults = [
            'primary' => 'rgba(79, 70, 229, 0.8)',
            'success' => 'rgba(34, 197, 94, 0.8)',
            'warning' => 'rgba(251, 146, 60, 0.8)',
            'info' => 'rgba(14, 165, 233, 0.8)',
            'danger' => 'rgba(239, 68, 68, 0.8)',
        ];

        return app(\App\Services\AppSettingService::class)
            ->get($key, 'chart', $defaults[$colorType] ?? 'rgba(0, 0, 0, 0.8)');
    }
}

if (! function_exists('chart_grid_color')) {
    /**
     * Get chart grid color
     */
    function chart_grid_color(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('chart_grid_color', 'chart', '#f1f5f9');
    }
}

if (! function_exists('chart_text_color')) {
    /**
     * Get chart text color
     */
    function chart_text_color(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('chart_text_color', 'chart', '#64748b');
    }
}

if (! function_exists('chart_tooltip_bg')) {
    /**
     * Get chart tooltip background color
     */
    function chart_tooltip_bg(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('chart_tooltip_bg', 'chart', 'rgba(255, 255, 255, 0.95)');
    }
}

if (! function_exists('chart_colors_array')) {
    /**
     * Get array of chart colors for multi-series charts
     */
    function chart_colors_array(): array
    {
        return [
            chart_color('primary'),
            chart_color('success'),
            chart_color('warning'),
            chart_color('info'),
            chart_color('danger'),
        ];
    }
}

if (! function_exists('chart_border_colors_array')) {
    /**
     * Get array of chart border colors (full opacity versions)
     */
    function chart_border_colors_array(): array
    {
        return array_map(function($color) {
            // Convert rgba(r, g, b, 0.8) to rgba(r, g, b, 1)
            return preg_replace('/,\s*0\.\d+\s*\)$/', ', 1)', $color);
        }, chart_colors_array());
    }
}

// Additional Theme Helper Functions
if (! function_exists('theme_sidebar_hover_color')) {
    function theme_sidebar_hover_color(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_sidebar_hover_color', 'theme', 'rgba(255, 255, 255, 0.1)');
    }
}

if (! function_exists('theme_sidebar_active_color')) {
    function theme_sidebar_active_color(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_sidebar_active_color', 'theme', 'rgba(255, 255, 255, 0.15)');
    }
}

if (! function_exists('theme_border_radius')) {
    function theme_border_radius(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_border_radius', 'theme', '0.35rem');
    }
}

if (! function_exists('theme_box_shadow')) {
    function theme_box_shadow(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_box_shadow', 'theme', '0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15)');
    }
}

if (! function_exists('theme_animation_speed')) {
    function theme_animation_speed(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_animation_speed', 'theme', '0.3s');
    }
}

if (! function_exists('theme_topbar_bg_color')) {
    function theme_topbar_bg_color(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_topbar_bg_color', 'theme', '#ffffff');
    }
}

if (! function_exists('theme_topbar_text_color')) {
    function theme_topbar_text_color(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_topbar_text_color', 'theme', '#5a5c69');
    }
}

if (! function_exists('theme_body_bg_color')) {
    function theme_body_bg_color(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_body_bg_color', 'theme', '#f8f9fc');
    }
}

if (! function_exists('theme_content_bg_color')) {
    function theme_content_bg_color(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_content_bg_color', 'theme', '#ffffff');
    }
}

if (! function_exists('theme_link_color')) {
    function theme_link_color(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_link_color', 'theme', '#4e73df');
    }
}

if (! function_exists('theme_link_hover_color')) {
    function theme_link_hover_color(): string
    {
        return app(\App\Services\AppSettingService::class)
            ->get('theme_link_hover_color', 'theme', '#224abe');
    }
}

if (! function_exists('theme_styles')) {
    /**
     * Generate CSS custom properties for theme customization
     * All theme settings are converted to CSS custom properties for easy override
     */
    function theme_styles(): string
    {
        $styles = [
            // Brand Colors
            '--theme-primary' => theme_color('primary'),
            '--theme-secondary' => theme_color('secondary'),
            '--theme-success' => theme_color('success'),
            '--theme-info' => theme_color('info'),
            '--theme-warning' => theme_color('warning'),
            '--theme-danger' => theme_color('danger'),
            '--theme-light' => theme_color('light'),
            '--theme-dark' => theme_color('dark'),

            // Sidebar Colors
            '--theme-sidebar-bg' => theme_sidebar_bg_color(),
            '--theme-sidebar-text' => theme_sidebar_text_color(),
            '--theme-sidebar-hover' => theme_sidebar_hover_color(),
            '--theme-sidebar-active' => theme_sidebar_active_color(),

            // Topbar/Header Colors
            '--theme-topbar-bg' => theme_topbar_bg_color(),
            '--theme-topbar-text' => theme_topbar_text_color(),

            // Background Colors
            '--theme-body-bg' => theme_body_bg_color(),
            '--theme-content-bg' => theme_content_bg_color(),

            // Link Colors
            '--theme-link-color' => theme_link_color(),
            '--theme-link-hover' => theme_link_hover_color(),

            // Typography
            '--theme-primary-font' => theme_primary_font(),
            '--theme-secondary-font' => theme_secondary_font(),

            // Component Styles
            '--theme-border-radius' => theme_border_radius(),
            '--theme-box-shadow' => theme_box_shadow(),
            '--theme-animation-speed' => theme_animation_speed(),
        ];

        $css = [];
        foreach ($styles as $property => $value) {
            $css[] = "{$property}: {$value};";
        }

        return implode(' ', $css);
    }
}
