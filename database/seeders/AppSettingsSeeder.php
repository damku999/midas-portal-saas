<?php

namespace Database\Seeders;

use App\Services\AppSettingService;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cache before seeding
        AppSettingService::clearCache();

        // ========================================
        // CATEGORY: Application
        // ========================================
        $applicationSettings = [
            'app_name' => [
                'value' => env('APP_NAME', 'Insurance Admin Panel'),
                'type' => 'string',
                'description' => 'Application Display Name',
                'is_encrypted' => false,
            ],
            'app_timezone' => [
                'value' => 'Asia/Kolkata',
                'type' => 'string',
                'description' => 'Application Timezone (Valid PHP timezone identifier)',
                'is_encrypted' => false,
            ],
            'app_locale' => [
                'value' => config('app.locale', 'en'),
                'type' => 'string',
                'description' => 'Default Language (en, hi, etc.)',
                'is_encrypted' => false,
            ],
            'app_currency' => [
                'value' => 'INR',
                'type' => 'string',
                'description' => 'Default Currency Code (INR, USD, EUR, GBP)',
                'is_encrypted' => false,
            ],
            'app_currency_symbol' => [
                'value' => '₹',
                'type' => 'string',
                'description' => 'Default Currency Symbol',
                'is_encrypted' => false,
            ],
            'app_date_format' => [
                'value' => 'd/m/Y',
                'type' => 'string',
                'description' => 'Date Format (d/m/Y, Y-m-d, m/d/Y)',
                'is_encrypted' => false,
            ],
            'app_time_format' => [
                'value' => '12h',
                'type' => 'string',
                'description' => 'Time Format (12h for AM/PM, 24h for 24-hour)',
                'is_encrypted' => false,
            ],
            'pagination_default' => [
                'value' => '15',
                'type' => 'numeric',
                'description' => 'Default Items Per Page',
                'is_encrypted' => false,
            ],
            'session_lifetime' => [
                'value' => env('SESSION_LIFETIME', '120'),
                'type' => 'numeric',
                'description' => 'Session Timeout in Minutes',
                'is_encrypted' => false,
            ],
            'system_admin_emails' => [
                'value' => 'webmonks.in@gmail.com,admin@webmonks.in',
                'type' => 'string',
                'description' => 'System Administrator Emails (comma-separated) - Users with these emails get full system access',
                'is_encrypted' => false,
            ],
            'notification_test_phone' => [
                'value' => '919800071314',
                'type' => 'string',
                'description' => 'Default Test Phone Number for Notification Testing',
                'is_encrypted' => false,
            ],
            'notification_test_email' => [
                'value' => 'test@example.com',
                'type' => 'email',
                'description' => 'Default Test Email for Notification Testing',
                'is_encrypted' => false,
            ],
        ];

        AppSettingService::setBulk($applicationSettings, 'application');

        // ========================================
        // CATEGORY: WhatsApp
        // ========================================
        $whatsappSettings = [
            'whatsapp_sender_id' => [
                'value' => '919800071314',
                'type' => 'string',
                'description' => 'WhatsApp API Sender ID',
                'is_encrypted' => false,
            ],
            'whatsapp_base_url' => [
                'value' => 'https://api.botmastersender.com/api/v1/',
                'type' => 'url',
                'description' => 'WhatsApp API Base URL',
                'is_encrypted' => false,
            ],
            'whatsapp_auth_token' => [
                'value' => '53eb1f03-90be-49ce-9dbe-b23fe982b31f',
                'type' => 'text',
                'description' => 'WhatsApp API Authentication Token',
                'is_encrypted' => true,
            ],
        ];

        AppSettingService::setBulk($whatsappSettings, 'whatsapp');

        // ========================================
        // CATEGORY: Mail
        // ========================================
        $mailSettings = [
            'mail_default_driver' => [
                'value' => env('MAIL_MAILER', 'smtp'),
                'type' => 'string',
                'description' => 'Default Mail Driver (smtp, sendmail, mailgun, etc.)',
                'is_encrypted' => false,
            ],
            'mail_from_address' => [
                'value' => env('MAIL_FROM_ADDRESS', 'support@midastech.in'),
                'type' => 'email',
                'description' => 'Default From Email Address',
                'is_encrypted' => false,
            ],
            'mail_from_name' => [
                'value' => env('MAIL_FROM_NAME', env('APP_NAME', 'MIDAS Portal')),
                'type' => 'string',
                'description' => 'Default From Name',
                'is_encrypted' => false,
            ],
            'mail_smtp_host' => [
                'value' => env('MAIL_HOST', 'smtp.hostinger.com'),
                'type' => 'string',
                'description' => 'SMTP Server Host',
                'is_encrypted' => false,
            ],
            'mail_smtp_port' => [
                'value' => env('MAIL_PORT', '465'),
                'type' => 'numeric',
                'description' => 'SMTP Server Port (25, 465, 587, 2525)',
                'is_encrypted' => false,
            ],
            'mail_smtp_encryption' => [
                'value' => env('MAIL_ENCRYPTION', 'ssl'),
                'type' => 'string',
                'description' => 'SMTP Encryption (tls, ssl, or null)',
                'is_encrypted' => false,
            ],
            'mail_smtp_username' => [
                'value' => env('MAIL_USERNAME', ''),
                'type' => 'email',
                'description' => 'SMTP Authentication Username',
                'is_encrypted' => true,
            ],
            'mail_smtp_password' => [
                'value' => env('MAIL_PASSWORD', ''),
                'type' => 'text',
                'description' => 'SMTP Authentication Password',
                'is_encrypted' => true,
            ],
        ];

        AppSettingService::setBulk($mailSettings, 'mail');

        // ========================================
        // CATEGORY: Notifications
        // ========================================
        $notificationSettings = [
            'email_notifications_enabled' => [
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Master Toggle for Email Notifications',
                'is_encrypted' => false,
            ],
            'whatsapp_notifications_enabled' => [
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Master Toggle for WhatsApp Notifications',
                'is_encrypted' => false,
            ],
            'renewal_reminder_days' => [
                'value' => '30,15,7,1',
                'type' => 'string',
                'description' => 'Days Before Expiry to Send Renewal Reminders (comma-separated: 30,15,7,1)',
                'is_encrypted' => false,
            ],
            'birthday_wishes_enabled' => [
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Send Birthday Wishes to Customers Automatically',
                'is_encrypted' => false,
            ],
        ];

        AppSettingService::setBulk($notificationSettings, 'notifications');

        // ========================================
        // CATEGORY: Company
        // ========================================
        $companySettings = [
            'company_name' => [
                'value' => 'Parth Rawal Insurance Advisor',
                'type' => 'string',
                'description' => 'Company/Business Name',
                'is_encrypted' => false,
            ],
            'company_advisor_name' => [
                'value' => 'Parth Rawal',
                'type' => 'string',
                'description' => 'Insurance Advisor Name',
                'is_encrypted' => false,
            ],
            'company_website' => [
                'value' => 'https://webmonks.in',
                'type' => 'url',
                'description' => 'Company Website URL',
                'is_encrypted' => false,
            ],
            'company_phone' => [
                'value' => '+91 80000 71314',
                'type' => 'string',
                'description' => 'Company Contact Phone Number (display format)',
                'is_encrypted' => false,
            ],
            'company_phone_whatsapp' => [
                'value' => '919800071314',
                'type' => 'string',
                'description' => 'WhatsApp Phone Number (API format without + or spaces)',
                'is_encrypted' => false,
            ],
            'company_title' => [
                'value' => 'Your Trusted Insurance Advisor',
                'type' => 'string',
                'description' => 'Company Professional Title/Role',
                'is_encrypted' => false,
            ],
            'company_tagline' => [
                'value' => 'Think of Insurance, Think of Us.',
                'type' => 'string',
                'description' => 'Company Tagline/Motto',
                'is_encrypted' => false,
            ],
        ];

        AppSettingService::setBulk($companySettings, 'company');

        // ========================================
        // CATEGORY: CDN Configuration
        // ========================================
        $cdnSettings = [
            // Bootstrap
            'cdn_bootstrap_js' => [
                'value' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
                'type' => 'url',
                'description' => 'Bootstrap JavaScript Bundle CDN URL',
                'is_encrypted' => false,
            ],

            // jQuery
            'cdn_jquery_url' => [
                'value' => 'https://code.jquery.com/jquery-3.7.1.min.js',
                'type' => 'url',
                'description' => 'jQuery CDN URL',
                'is_encrypted' => false,
            ],

            // Select2
            'cdn_select2_css' => [
                'value' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
                'type' => 'url',
                'description' => 'Select2 CSS CDN URL',
                'is_encrypted' => false,
            ],
            'cdn_select2_js' => [
                'value' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
                'type' => 'url',
                'description' => 'Select2 JavaScript CDN URL',
                'is_encrypted' => false,
            ],
            'cdn_select2_bootstrap_theme_css' => [
                'value' => 'https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css',
                'type' => 'url',
                'description' => 'Select2 Bootstrap 5 Theme CSS CDN URL',
                'is_encrypted' => false,
            ],

            // Flatpickr Date Picker
            'cdn_flatpickr_css' => [
                'value' => 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
                'type' => 'url',
                'description' => 'Flatpickr CSS CDN URL',
                'is_encrypted' => false,
            ],
            'cdn_flatpickr_js' => [
                'value' => 'https://cdn.jsdelivr.net/npm/flatpickr',
                'type' => 'url',
                'description' => 'Flatpickr JavaScript CDN URL',
                'is_encrypted' => false,
            ],
            'cdn_flatpickr_monthselect_css' => [
                'value' => 'https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css',
                'type' => 'url',
                'description' => 'Flatpickr Month Select Plugin CSS CDN URL',
                'is_encrypted' => false,
            ],
            'cdn_flatpickr_monthselect_js' => [
                'value' => 'https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js',
                'type' => 'url',
                'description' => 'Flatpickr Month Select Plugin JavaScript CDN URL',
                'is_encrypted' => false,
            ],

            // Chart.js
            'cdn_chartjs_url' => [
                'value' => 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
                'type' => 'url',
                'description' => 'Chart.js CDN URL',
                'is_encrypted' => false,
            ],

            // Font Awesome Icons
            'cdn_fontawesome_css' => [
                'value' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css',
                'type' => 'url',
                'description' => 'Font Awesome CSS CDN URL',
                'is_encrypted' => false,
            ],

            // Google Fonts
            'cdn_google_fonts_inter' => [
                'value' => 'https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap',
                'type' => 'url',
                'description' => 'Google Fonts - Inter Family CDN URL',
                'is_encrypted' => false,
            ],
            'cdn_google_fonts_combined' => [
                'value' => 'https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:wght@200;300;400;600;700;800;900&display=swap',
                'type' => 'url',
                'description' => 'Google Fonts - Combined Inter & Nunito CDN URL',
                'is_encrypted' => false,
            ],

            // Bootstrap Datepicker
            'cdn_bootstrap_datepicker_css' => [
                'value' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css',
                'type' => 'url',
                'description' => 'Bootstrap Datepicker CSS CDN URL',
                'is_encrypted' => false,
            ],
            'cdn_bootstrap_datepicker_js' => [
                'value' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js',
                'type' => 'url',
                'description' => 'Bootstrap Datepicker JavaScript CDN URL',
                'is_encrypted' => false,
            ],
        ];

        AppSettingService::setBulk($cdnSettings, 'cdn');

        // ========================================
        // CATEGORY: Branding Assets
        // ========================================
        $brandingSettings = [
            'company_logo_path' => [
                'value' => 'images/parth_logo.png',
                'type' => 'image',
                'description' => 'Company Logo Image (Upload)',
                'is_encrypted' => false,
            ],
            'company_logo_alt' => [
                'value' => 'Parth Rawal Insurance Advisor',
                'type' => 'string',
                'description' => 'Company Logo Alt Text (SEO)',
                'is_encrypted' => false,
            ],
            'company_favicon_path' => [
                'value' => 'images/icon.png',
                'type' => 'image',
                'description' => 'Favicon Image (Upload)',
                'is_encrypted' => false,
            ],
            'company_email_logo_height' => [
                'value' => '60px',
                'type' => 'string',
                'description' => 'Logo Height in Email Templates',
                'is_encrypted' => false,
            ],
            'company_sidebar_logo_height' => [
                'value' => '60px',
                'type' => 'string',
                'description' => 'Logo Height in Sidebar Navigation',
                'is_encrypted' => false,
            ],
        ];

        AppSettingService::setBulk($brandingSettings, 'branding');

        // ========================================
        // CATEGORY: Footer Configuration
        // ========================================
        $footerSettings = [
            'footer_developer_name' => [
                'value' => 'Midas Tech',
                'type' => 'string',
                'description' => 'Developer/Company Name for Footer Credits',
                'is_encrypted' => false,
            ],
            'footer_developer_url' => [
                'value' => 'https://midastech.in',
                'type' => 'url',
                'description' => 'Developer Website URL',
                'is_encrypted' => false,
            ],
            'footer_show_developer' => [
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Show Developer Credits in Footer',
                'is_encrypted' => false,
            ],
            'footer_show_year' => [
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Show Current Year in Footer',
                'is_encrypted' => false,
            ],
            'footer_copyright_text' => [
                'value' => 'Copyright © Midas Tech',
                'type' => 'string',
                'description' => 'Copyright Text for Footer',
                'is_encrypted' => false,
            ],
        ];

        AppSettingService::setBulk($footerSettings, 'footer');

        // ========================================
        // CATEGORY: Assets Management
        // ========================================
        $assetsSettings = [
            'assets_version' => [
                'value' => '1.0.0',
                'type' => 'string',
                'description' => 'Static Assets Version (for cache busting)',
                'is_encrypted' => false,
            ],
            'assets_cache_busting' => [
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable Cache Busting for CSS/JS Files',
                'is_encrypted' => false,
            ],
        ];

        AppSettingService::setBulk($assetsSettings, 'assets');

        // ========================================
        // CATEGORY: Theme Configuration
        // ========================================
        $themeSettings = [
            // Primary Brand Colors
            'theme_primary_color' => [
                'value' => '#4e73df',
                'type' => 'color',
                'description' => 'Primary Brand Color (Hex)',
                'is_encrypted' => false,
            ],
            'theme_secondary_color' => [
                'value' => '#858796',
                'type' => 'color',
                'description' => 'Secondary Color (Hex)',
                'is_encrypted' => false,
            ],
            'theme_success_color' => [
                'value' => '#1cc88a',
                'type' => 'color',
                'description' => 'Success Color (Hex)',
                'is_encrypted' => false,
            ],
            'theme_info_color' => [
                'value' => '#36b9cc',
                'type' => 'color',
                'description' => 'Info Color (Hex)',
                'is_encrypted' => false,
            ],
            'theme_warning_color' => [
                'value' => '#f6c23e',
                'type' => 'color',
                'description' => 'Warning Color (Hex)',
                'is_encrypted' => false,
            ],
            'theme_danger_color' => [
                'value' => '#e74a3b',
                'type' => 'color',
                'description' => 'Danger/Error Color (Hex)',
                'is_encrypted' => false,
            ],
            'theme_light_color' => [
                'value' => '#f8f9fc',
                'type' => 'color',
                'description' => 'Light Background Color (Hex)',
                'is_encrypted' => false,
            ],
            'theme_dark_color' => [
                'value' => '#5a5c69',
                'type' => 'color',
                'description' => 'Dark Text Color (Hex)',
                'is_encrypted' => false,
            ],

            // Sidebar Theme
            'theme_sidebar_bg_color' => [
                'value' => '#4e73df',
                'type' => 'color',
                'description' => 'Sidebar Background Color (Hex)',
                'is_encrypted' => false,
            ],
            'theme_sidebar_text_color' => [
                'value' => '#ffffff',
                'type' => 'color',
                'description' => 'Sidebar Text Color (Hex)',
                'is_encrypted' => false,
            ],
            'theme_sidebar_hover_color' => [
                'value' => 'rgba(255, 255, 255, 0.1)',
                'type' => 'color',
                'description' => 'Sidebar Hover Background Color (Hex/RGBA)',
                'is_encrypted' => false,
            ],
            'theme_sidebar_active_color' => [
                'value' => 'rgba(255, 255, 255, 0.15)',
                'type' => 'color',
                'description' => 'Sidebar Active Item Background Color (Hex/RGBA)',
                'is_encrypted' => false,
            ],

            // Typography
            'theme_primary_font' => [
                'value' => 'Inter',
                'type' => 'string',
                'description' => 'Primary Font Family',
                'is_encrypted' => false,
            ],
            'theme_secondary_font' => [
                'value' => 'Nunito',
                'type' => 'string',
                'description' => 'Secondary Font Family',
                'is_encrypted' => false,
            ],

            // Component Styles
            'theme_border_radius' => [
                'value' => '0.35rem',
                'type' => 'string',
                'description' => 'Border Radius for All Components (buttons, cards, inputs)',
                'is_encrypted' => false,
            ],
            'theme_box_shadow' => [
                'value' => '0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15)',
                'type' => 'string',
                'description' => 'Box Shadow for Cards and Elevated Elements',
                'is_encrypted' => false,
            ],
            'theme_animation_speed' => [
                'value' => '0.3s',
                'type' => 'string',
                'description' => 'Animation/Transition Speed (0.2s = fast, 0.3s = normal, 0.5s = slow)',
                'is_encrypted' => false,
            ],

            // Topbar/Header
            'theme_topbar_bg_color' => [
                'value' => '#ffffff',
                'type' => 'color',
                'description' => 'Top Navigation Bar Background Color (Hex)',
                'is_encrypted' => false,
            ],
            'theme_topbar_text_color' => [
                'value' => '#5a5c69',
                'type' => 'color',
                'description' => 'Top Navigation Bar Text Color (Hex)',
                'is_encrypted' => false,
            ],

            // Background Colors
            'theme_body_bg_color' => [
                'value' => '#f8f9fc',
                'type' => 'color',
                'description' => 'Main Body Background Color (Hex)',
                'is_encrypted' => false,
            ],
            'theme_content_bg_color' => [
                'value' => '#ffffff',
                'type' => 'color',
                'description' => 'Content Area Background Color (Hex)',
                'is_encrypted' => false,
            ],

            // Link Colors
            'theme_link_color' => [
                'value' => '#4e73df',
                'type' => 'color',
                'description' => 'Default Link Color (Hex)',
                'is_encrypted' => false,
            ],
            'theme_link_hover_color' => [
                'value' => '#224abe',
                'type' => 'color',
                'description' => 'Link Hover Color (Hex)',
                'is_encrypted' => false,
            ],

            // Theme Mode
            'theme_mode' => [
                'value' => 'light',
                'type' => 'string',
                'description' => 'Theme Mode: light or dark',
                'is_encrypted' => false,
            ],
            'theme_enable_dark_mode' => [
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable Dark Mode Toggle for Users',
                'is_encrypted' => false,
            ],
        ];

        AppSettingService::setBulk($themeSettings, 'theme');

        // ========================================
        // CATEGORY: SMS Configuration
        // ========================================
        $smsSettings = [
            'sms_enabled' => [
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable SMS Notifications',
                'is_encrypted' => false,
            ],
            'sms_provider' => [
                'value' => 'twilio',
                'type' => 'string',
                'description' => 'SMS Provider (twilio, nexmo, sns)',
                'is_encrypted' => false,
            ],
            'sms_sender_id' => [
                'value' => '',
                'type' => 'string',
                'description' => 'SMS Sender ID/Phone Number',
                'is_encrypted' => false,
            ],
            'sms_character_limit' => [
                'value' => '160',
                'type' => 'numeric',
                'description' => 'SMS Character Limit Per Message',
                'is_encrypted' => false,
            ],
            'sms_twilio_account_sid' => [
                'value' => env('TWILIO_ACCOUNT_SID', ''),
                'type' => 'text',
                'description' => 'Twilio Account SID',
                'is_encrypted' => true,
            ],
            'sms_twilio_auth_token' => [
                'value' => env('TWILIO_AUTH_TOKEN', ''),
                'type' => 'text',
                'description' => 'Twilio Auth Token',
                'is_encrypted' => true,
            ],
            'sms_twilio_from_number' => [
                'value' => env('TWILIO_FROM', ''),
                'type' => 'string',
                'description' => 'Twilio From Phone Number',
                'is_encrypted' => false,
            ],
        ];

        AppSettingService::setBulk($smsSettings, 'sms');

        // ========================================
        // CATEGORY: Push Notification Configuration
        // ========================================
        $pushSettings = [
            'push_enabled' => [
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable Push Notifications',
                'is_encrypted' => false,
            ],
            'push_fcm_server_key' => [
                'value' => env('FCM_SERVER_KEY', ''),
                'type' => 'text',
                'description' => 'Firebase Cloud Messaging Server Key',
                'is_encrypted' => true,
            ],
            'push_fcm_sender_id' => [
                'value' => env('FCM_SENDER_ID', ''),
                'type' => 'string',
                'description' => 'Firebase Cloud Messaging Sender ID',
                'is_encrypted' => false,
            ],
            'push_fcm_api_url' => [
                'value' => 'https://fcm.googleapis.com/fcm/send',
                'type' => 'url',
                'description' => 'FCM API Endpoint URL',
                'is_encrypted' => false,
            ],
            'push_deep_linking_enabled' => [
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable Deep Linking in Push Notifications',
                'is_encrypted' => false,
            ],
            'push_action_buttons_enabled' => [
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable Action Buttons in Push Notifications',
                'is_encrypted' => false,
            ],
        ];

        AppSettingService::setBulk($pushSettings, 'push');

        // ========================================
        // CATEGORY: Chart Colors Configuration
        // ========================================
        $chartSettings = [
            'chart_color_primary' => [
                'value' => 'rgba(79, 70, 229, 0.8)',
                'type' => 'color',
                'description' => 'Chart Primary Color',
                'is_encrypted' => false,
            ],
            'chart_color_success' => [
                'value' => 'rgba(34, 197, 94, 0.8)',
                'type' => 'color',
                'description' => 'Chart Success Color',
                'is_encrypted' => false,
            ],
            'chart_color_warning' => [
                'value' => 'rgba(251, 146, 60, 0.8)',
                'type' => 'color',
                'description' => 'Chart Warning Color',
                'is_encrypted' => false,
            ],
            'chart_color_info' => [
                'value' => 'rgba(14, 165, 233, 0.8)',
                'type' => 'color',
                'description' => 'Chart Info Color',
                'is_encrypted' => false,
            ],
            'chart_color_danger' => [
                'value' => 'rgba(239, 68, 68, 0.8)',
                'type' => 'color',
                'description' => 'Chart Danger Color',
                'is_encrypted' => false,
            ],
            'chart_grid_color' => [
                'value' => '#f1f5f9',
                'type' => 'color',
                'description' => 'Chart Grid Line Color',
                'is_encrypted' => false,
            ],
            'chart_text_color' => [
                'value' => '#64748b',
                'type' => 'color',
                'description' => 'Chart Text/Label Color',
                'is_encrypted' => false,
            ],
            'chart_tooltip_bg' => [
                'value' => 'rgba(255, 255, 255, 0.95)',
                'type' => 'color',
                'description' => 'Chart Tooltip Background Color',
                'is_encrypted' => false,
            ],
        ];

        AppSettingService::setBulk($chartSettings, 'chart');

        $this->command->info('✅ App Settings seeded successfully!');
        $this->command->info('📊 Categories: application, whatsapp, mail, notifications, company, cdn, branding, footer, assets, theme, sms, push, chart');
    }
}