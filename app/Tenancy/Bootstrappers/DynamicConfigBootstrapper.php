<?php

namespace App\Tenancy\Bootstrappers;

use App\Services\AppSettingService;
use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class DynamicConfigBootstrapper implements TenancyBootstrapper
{
    /** @var Application */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Bootstrap tenancy - load tenant-specific configurations
     */
    public function bootstrap(Tenant $tenant): void
    {
        try {
            // Load Application Settings
            $this->loadApplicationSettings();

            // Load WhatsApp Settings
            $this->loadWhatsAppSettings();

            // Load Mail Settings
            $this->loadMailSettings();

            // Load Notification Settings
            $this->loadNotificationSettings();

            // Load SMS Settings
            $this->loadSmsSettings();

            // Load Push Notification Settings
            $this->loadPushSettings();
        } catch (\Exception $e) {
            // Silently fail during migration/installation
            \Log::debug('DynamicConfigBootstrapper failed: ' . $e->getMessage());
        }
    }

    /**
     * Revert tenancy bootstrapping
     */
    public function revert(): void
    {
        // Nothing to revert - config changes are request-scoped
    }

    /**
     * Load Application Settings
     */
    protected function loadApplicationSettings(): void
    {
        $settings = AppSettingService::getByCategory('application');

        if (!empty($settings)) {
            config([
                'app.name' => $settings['app_name'] ?? config('app.name'),
                'app.timezone' => $settings['app_timezone'] ?? config('app.timezone'),
                'app.locale' => $settings['app_locale'] ?? config('app.locale'),
                'app.currency' => $settings['app_currency'] ?? 'INR',
                'app.currency_symbol' => $settings['app_currency_symbol'] ?? '₹',
                'app.date_format' => $settings['app_date_format'] ?? 'd/m/Y',
                'app.time_format' => $settings['app_time_format'] ?? '12h',
                'app.pagination_default' => (int) ($settings['pagination_default'] ?? 15),
                'session.lifetime' => (int) ($settings['session_lifetime'] ?? config('session.lifetime')),
            ]);
        }
    }

    /**
     * Load WhatsApp Settings
     */
    protected function loadWhatsAppSettings(): void
    {
        $settings = AppSettingService::getByCategory('whatsapp');

        if (!empty($settings)) {
            config([
                'whatsapp.sender_id' => $settings['whatsapp_sender_id'] ?? config('whatsapp.sender_id'),
                'whatsapp.base_url' => $settings['whatsapp_base_url'] ?? config('whatsapp.base_url'),
                'whatsapp.auth_token' => $settings['whatsapp_auth_token'] ?? config('whatsapp.auth_token'),
            ]);
        }
    }

    /**
     * Load Mail Settings
     */
    protected function loadMailSettings(): void
    {
        $settings = AppSettingService::getByCategory('mail');

        if (!empty($settings)) {
            config([
                'mail.default' => $settings['mail_default_driver'] ?? config('mail.default'),
                'mail.from.address' => $settings['mail_from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $settings['mail_from_name'] ?? config('mail.from.name'),
                'mail.mailers.smtp.host' => $settings['mail_smtp_host'] ?? config('mail.mailers.smtp.host'),
                'mail.mailers.smtp.port' => (int) ($settings['mail_smtp_port'] ?? config('mail.mailers.smtp.port')),
                'mail.mailers.smtp.encryption' => $settings['mail_smtp_encryption'] ?? config('mail.mailers.smtp.encryption'),
                'mail.mailers.smtp.username' => $settings['mail_smtp_username'] ?? config('mail.mailers.smtp.username'),
                'mail.mailers.smtp.password' => $settings['mail_smtp_password'] ?? config('mail.mailers.smtp.password'),
            ]);
        }
    }

    /**
     * Load Notification Settings
     */
    protected function loadNotificationSettings(): void
    {
        $settings = AppSettingService::getByCategory('notifications');

        if (!empty($settings)) {
            config([
                'notifications.email_enabled' => filter_var($settings['email_notifications_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'notifications.whatsapp_enabled' => filter_var($settings['whatsapp_notifications_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'notifications.renewal_reminder_days' => $settings['renewal_reminder_days'] ?? '30,15,7,1',
                'notifications.birthday_wishes_enabled' => filter_var($settings['birthday_wishes_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * Load SMS Settings
     */
    protected function loadSmsSettings(): void
    {
        $settings = AppSettingService::getByCategory('sms');

        if (!empty($settings)) {
            config([
                'sms.enabled' => filter_var($settings['sms_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'sms.provider' => $settings['sms_provider'] ?? 'twilio',
                'sms.sender_id' => $settings['sms_sender_id'] ?? '',
                'sms.character_limit' => (int) ($settings['sms_character_limit'] ?? 160),
                'sms.twilio.account_sid' => $settings['sms_twilio_account_sid'] ?? env('TWILIO_ACCOUNT_SID', ''),
                'sms.twilio.auth_token' => $settings['sms_twilio_auth_token'] ?? env('TWILIO_AUTH_TOKEN', ''),
                'sms.twilio.from' => $settings['sms_twilio_from_number'] ?? env('TWILIO_FROM', ''),
            ]);
        }
    }

    /**
     * Load Push Notification Settings
     */
    protected function loadPushSettings(): void
    {
        $settings = AppSettingService::getByCategory('push');

        if (!empty($settings)) {
            config([
                'push.enabled' => filter_var($settings['push_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'push.fcm.server_key' => $settings['push_fcm_server_key'] ?? env('FCM_SERVER_KEY', ''),
                'push.fcm.sender_id' => $settings['push_fcm_sender_id'] ?? env('FCM_SENDER_ID', ''),
                'push.fcm.api_url' => $settings['push_fcm_api_url'] ?? 'https://fcm.googleapis.com/fcm/send',
                'push.deep_linking_enabled' => filter_var($settings['push_deep_linking_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'push.action_buttons_enabled' => filter_var($settings['push_action_buttons_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
