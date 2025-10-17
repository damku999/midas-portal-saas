<?php

namespace App\Traits\Customer;

use App\Models\Customer\CustomerSecuritySettings;
use App\Models\Customer\CustomerTrustedDevice;
use App\Models\Customer\CustomerTwoFactorAuth;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

/**
 * Customer-specific Two Factor Authentication Trait
 * Separate from admin 2FA to prevent conflicts
 */
trait HasCustomerTwoFactorAuth
{
    /**
     * Customer's two factor auth relationship
     */
    public function customerTwoFactorAuth(): MorphOne
    {
        return $this->morphOne(CustomerTwoFactorAuth::class, 'authenticatable')
            ->where('authenticatable_type', static::class);
    }

    /**
     * Customer's security settings relationship
     */
    public function customerSecuritySettings(): MorphOne
    {
        return $this->morphOne(CustomerSecuritySettings::class, 'settingable')
            ->where('settingable_type', static::class);
    }

    /**
     * Check if customer has 2FA enabled
     */
    public function hasCustomerTwoFactorEnabled(): bool
    {
        $twoFactor = $this->customerTwoFactorAuth;
        $settings = $this->customerSecuritySettings;

        return $twoFactor &&
               $twoFactor->is_active &&
               $twoFactor->isFullyConfigured() &&
               $settings &&
               $settings->two_factor_enabled;
    }

    /**
     * Check if customer has 2FA setup pending
     */
    public function hasCustomerTwoFactorPending(): bool
    {
        $twoFactor = $this->customerTwoFactorAuth;

        return $twoFactor &&
               $twoFactor->is_active &&
               $twoFactor->isPendingConfirmation();
    }

    /**
     * Enable customer 2FA
     */
    public function enableCustomerTwoFactor(): CustomerTwoFactorAuth
    {
        $google2fa = new Google2FA;

        // Create or get existing 2FA record
        $twoFactor = $this->customerTwoFactorAuth ?: new CustomerTwoFactorAuth;
        $twoFactor->authenticatable()->associate($this);

        // Generate secret and recovery codes
        $twoFactor->secret = $google2fa->generateSecretKey();
        $twoFactor->recovery_codes = $twoFactor->generateRecoveryCodes();
        $twoFactor->enabled_at = now();
        $twoFactor->is_active = true;
        $twoFactor->confirmed_at = null; // Will be set when confirmed

        $twoFactor->save();

        return $twoFactor;
    }

    /**
     * Confirm customer 2FA with verification code
     */
    public function confirmCustomerTwoFactor(string $code): bool
    {
        $twoFactor = $this->customerTwoFactorAuth;

        if (! $twoFactor || ! $twoFactor->secret) {
            return false;
        }

        $google2fa = new Google2FA;
        $isValid = $google2fa->verifyKey($twoFactor->secret, $code);

        if ($isValid) {
            $twoFactor->confirm();

            // Also update security settings
            $this->enableCustomerTwoFactorInSettings();

            return true;
        }

        return false;
    }

    /**
     * Disable customer 2FA
     */
    public function disableCustomerTwoFactor(): bool
    {
        $twoFactor = $this->customerTwoFactorAuth;

        if ($twoFactor) {
            $twoFactor->disable();
            // Also clear from security settings
            $this->disableCustomerTwoFactorInSettings();

            return true;
        }

        return false;
    }

    /**
     * Verify customer 2FA code
     */
    public function verifyCustomerTwoFactorCode(string $code): bool
    {
        $twoFactor = $this->customerTwoFactorAuth;

        if (! $twoFactor || ! $twoFactor->isFullyConfigured()) {
            return false;
        }

        $google2fa = new Google2FA;

        // Try as TOTP code first
        if ($google2fa->verifyKey($twoFactor->secret, $code)) {
            return true;
        }

        // Try as recovery code
        return $twoFactor->useRecoveryCode($code);
    }

    /**
     * Get customer 2FA QR code URL
     */
    public function getCustomerTwoFactorQrCodeUrl(): ?string
    {
        $twoFactor = $this->customerTwoFactorAuth;

        if (! $twoFactor || ! $twoFactor->secret) {
            return null;
        }

        $google2fa = new Google2FA;
        $companyName = config('app.name', 'Laravel App');
        $userEmail = $this->email ?? $this->name ?? 'Customer';

        return $google2fa->getQRCodeUrl(
            $companyName.' (Customer Portal)',
            $userEmail,
            $twoFactor->secret
        );
    }

    /**
     * Enable 2FA in customer security settings
     */
    public function enableCustomerTwoFactorInSettings(): bool
    {
        $settings = $this->getOrCreateCustomerSecuritySettings();
        $settings->enableTwoFactor();

        return true;
    }

    /**
     * Disable 2FA in customer security settings
     */
    public function disableCustomerTwoFactorInSettings(): bool
    {
        $settings = $this->customerSecuritySettings;
        if ($settings) {
            $settings->disableTwoFactor();
        }

        return true;
    }

    /**
     * Get or create customer security settings
     */
    public function getOrCreateCustomerSecuritySettings(): CustomerSecuritySettings
    {
        $settings = $this->customerSecuritySettings;

        if (! $settings) {
            $settings = new CustomerSecuritySettings(CustomerSecuritySettings::getDefaults());
            $settings->settingable()->associate($this);
            $settings->save();
        }

        return $settings;
    }

    /**
     * Customer's trusted devices relationship
     */
    public function customerTrustedDevices(): MorphMany
    {
        return $this->morphMany(CustomerTrustedDevice::class, 'authenticatable')
            ->where('authenticatable_type', static::class);
    }

    /**
     * Get active customer trusted devices
     */
    public function getActiveCustomerTrustedDevices()
    {
        return $this->customerTrustedDevices()->active()->get();
    }

    /**
     * Check if current device is trusted
     */
    public function isCustomerDeviceTrusted(Request $request): bool
    {
        $deviceId = CustomerTrustedDevice::generateDeviceId($request->userAgent(), $request->ip());

        return $this->customerTrustedDevices()
            ->where('device_id', $deviceId)
            ->active()
            ->exists();
    }

    /**
     * Trust current device for customer
     */
    public function trustCustomerDevice(Request $request, int $trustDurationDays = 30): CustomerTrustedDevice
    {
        $deviceId = CustomerTrustedDevice::generateDeviceId($request->userAgent(), $request->ip());
        $deviceInfo = CustomerTrustedDevice::parseUserAgent($request->userAgent());
        $deviceName = CustomerTrustedDevice::createDeviceName($deviceInfo);

        // Revoke any existing device with same ID
        $this->customerTrustedDevices()
            ->where('device_id', $deviceId)
            ->update(['is_active' => false]);

        // Create new trusted device
        $trustedDevice = new CustomerTrustedDevice([
            'device_id' => $deviceId,
            'device_name' => $deviceName,
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'platform' => $deviceInfo['platform'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_used_at' => now(),
            'trusted_at' => now(),
            'expires_at' => now()->addDays($trustDurationDays),
            'is_active' => true,
        ]);

        $trustedDevice->authenticatable()->associate($this);
        $trustedDevice->save();

        return $trustedDevice;
    }

    /**
     * Revoke customer device trust
     */
    public function revokeCustomerDeviceTrust(string $deviceId): bool
    {
        // Check if it's a numeric ID (database ID) or device_id hash
        $device = null;

        if (is_numeric($deviceId)) {
            // Database ID lookup
            $device = $this->customerTrustedDevices()
                ->where('id', $deviceId)
                ->first();
        } else {
            // Device ID hash lookup
            $device = $this->customerTrustedDevices()
                ->where('device_id', $deviceId)
                ->first();
        }

        if ($device) {
            $device->revoke();

            return true;
        }

        return false;
    }

    /**
     * Revoke all customer trusted devices
     */
    public function revokeAllCustomerTrustedDevices(): int
    {
        return $this->customerTrustedDevices()
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Clean up expired customer trusted devices
     */
    public function cleanupExpiredCustomerDevices(): int
    {
        return $this->customerTrustedDevices()
            ->where('is_active', true)
            ->where('expires_at', '<', now())
            ->update(['is_active' => false]);
    }

    /**
     * Customer's 2FA verification attempts relationship
     */
    public function customerTwoFactorAttempts(): MorphMany
    {
        return $this->morphMany(\App\Models\TwoFactorAttempt::class, 'authenticatable')
            ->where('authenticatable_type', static::class);
    }

    /**
     * Verify customer recovery code (similar to admin verifyRecoveryCode)
     */
    public function verifyCustomerRecoveryCode(string $code): bool
    {
        if (! $this->customerTwoFactorAuth) {
            return false;
        }

        return $this->customerTwoFactorAuth->useRecoveryCode($code);
    }

    /**
     * Log customer 2FA attempt (similar to admin logTwoFactorAttempt)
     */
    public function logCustomerTwoFactorAttempt(
        string $codeType,
        bool $successful,
        \Illuminate\Http\Request $request,
        ?string $failureReason = null
    ): void {
        $this->customerTwoFactorAttempts()->create([
            'code_type' => $codeType,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?? '',
            'successful' => $successful,
            'failure_reason' => $failureReason,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Get recent failed customer 2FA attempts count (similar to admin getRecentFailedTwoFactorAttempts)
     */
    public function getRecentFailedCustomerTwoFactorAttempts(int $minutes = 15): int
    {
        return $this->customerTwoFactorAttempts()
            ->where('attempted_at', '>=', now()->subMinutes($minutes))
            ->where('successful', false)
            ->count();
    }

    /**
     * Check if customer 2FA attempts are rate limited (similar to admin isTwoFactorRateLimited)
     */
    public function isTwoFactorRateLimited(): bool
    {
        return $this->getRecentFailedCustomerTwoFactorAttempts() >= 5;
    }

    /**
     * Alias method for compatibility with service calls
     * This ensures the same method name works for both Customer and User models
     */
    public function logTwoFactorAttempt(
        string $codeType,
        bool $successful,
        \Illuminate\Http\Request $request,
        ?string $failureReason = null
    ): void {
        $this->logCustomerTwoFactorAttempt($codeType, $successful, $request, $failureReason);
    }

    /**
     * Alias method for compatibility with service calls
     * This ensures the same method name works for both Customer and User models
     */
    public function getRecentFailedTwoFactorAttempts(int $minutes = 15): int
    {
        return $this->getRecentFailedCustomerTwoFactorAttempts($minutes);
    }

    /**
     * Alias method for compatibility with admin service calls
     * This ensures admin service methods work with Customer models
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        return $this->verifyCustomerTwoFactorCode($code);
    }

    /**
     * Alias method for compatibility with admin service calls
     * This ensures admin service methods work with Customer models
     */
    public function verifyRecoveryCode(string $code): bool
    {
        return $this->verifyCustomerRecoveryCode($code);
    }
}
