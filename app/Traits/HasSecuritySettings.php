<?php

namespace App\Traits;

use App\Models\SecuritySetting;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSecuritySettings
{
    /**
     * Get the security settings
     */
    public function securitySettings(): MorphOne
    {
        return $this->morphOne(SecuritySetting::class, 'settingable');
    }

    /**
     * Get or create security settings with defaults
     */
    public function getOrCreateSecuritySettings(): SecuritySetting
    {
        return $this->securitySettings ?: $this->securitySettings()->create(
            SecuritySetting::getDefaults()
        );
    }

    /**
     * Update security setting
     */
    public function updateSecuritySetting(string $key, $value): bool
    {
        $settings = $this->getOrCreateSecuritySettings();

        return $settings->update([$key => $value]);
    }

    /**
     * Get security setting value
     */
    public function getSecuritySetting(string $key, $default = null)
    {
        $settings = $this->securitySettings;
        if (! $settings) {
            $defaults = SecuritySetting::getDefaults();

            return $defaults[$key] ?? $default;
        }

        return $settings->$key ?? $default;
    }

    /**
     * Check if login notifications are enabled
     */
    public function hasLoginNotificationsEnabled(): bool
    {
        return $this->getSecuritySetting('login_notifications', true);
    }

    /**
     * Check if security alerts are enabled
     */
    public function hasSecurityAlertsEnabled(): bool
    {
        return $this->getSecuritySetting('security_alerts', true);
    }

    /**
     * Check if device tracking is enabled
     */
    public function hasDeviceTrackingEnabled(): bool
    {
        return $this->getSecuritySetting('device_tracking_enabled', true);
    }

    /**
     * Get session timeout in seconds
     */
    public function getSessionTimeout(): int
    {
        return $this->getSecuritySetting('session_timeout', 7200);
    }

    /**
     * Get device trust duration in days
     */
    public function getDeviceTrustDuration(): int
    {
        return $this->getSecuritySetting('device_trust_duration', 30);
    }

    /**
     * Get notification preferences
     */
    public function getNotificationPreferences(): array
    {
        return $this->getSecuritySetting('notification_preferences', [
            'email_login_alerts' => true,
            'email_security_alerts' => true,
            'email_2fa_changes' => true,
            'sms_security_alerts' => false,
            'sms_login_alerts' => false,
        ]);
    }

    /**
     * Update notification preference
     */
    public function updateNotificationPreference(string $key, bool $value): bool
    {
        $settings = $this->getOrCreateSecuritySettings();
        $settings->updateNotificationPreference($key, $value);

        return true;
    }

    /**
     * Enable 2FA in security settings
     */
    public function enableTwoFactorInSettings(): bool
    {
        return $this->updateSecuritySetting('two_factor_enabled', true);
    }

    /**
     * Disable 2FA in security settings
     */
    public function disableTwoFactorInSettings(): bool
    {
        return $this->updateSecuritySetting('two_factor_enabled', false);
    }
}
