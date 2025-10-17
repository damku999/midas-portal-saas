<?php

namespace App\Traits;

use App\Models\TrustedDevice;
use App\Models\TwoFactorAttempt;
use App\Models\TwoFactorAuth;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasTwoFactorAuth
{
    /**
     * Get the two-factor authentication record
     */
    public function twoFactorAuth(): MorphOne
    {
        return $this->morphOne(TwoFactorAuth::class, 'authenticatable');
    }

    /**
     * Get trusted devices
     */
    public function trustedDevices(): MorphMany
    {
        return $this->morphMany(TrustedDevice::class, 'authenticatable');
    }

    /**
     * Get 2FA verification attempts
     */
    public function twoFactorAttempts(): MorphMany
    {
        return $this->morphMany(TwoFactorAttempt::class, 'authenticatable');
    }

    /**
     * Check if user has 2FA enabled
     */
    public function hasTwoFactorEnabled(): bool
    {
        return $this->twoFactorAuth &&
               $this->twoFactorAuth->is_active &&
               $this->twoFactorAuth->isFullyConfigured();
    }

    /**
     * Check if 2FA setup is in progress
     */
    public function hasTwoFactorPending(): bool
    {
        return $this->twoFactorAuth &&
               $this->twoFactorAuth->isPendingConfirmation();
    }

    /**
     * Get or create 2FA record
     */
    public function getOrCreateTwoFactorAuth(): TwoFactorAuth
    {
        return $this->twoFactorAuth ?: $this->twoFactorAuth()->create([
            'is_active' => false,
        ]);
    }

    /**
     * Enable 2FA (start setup process)
     */
    public function enableTwoFactor(?string $secret = null): TwoFactorAuth
    {
        $twoFactor = $this->getOrCreateTwoFactorAuth();

        $secret = $secret ?: $this->generateTwoFactorSecret();

        $twoFactor->update([
            'secret' => $secret,
            'enabled_at' => now(),
            'confirmed_at' => null,
            'is_active' => false, // Will be true after confirmation
        ]);

        // Generate recovery codes
        $twoFactor->generateRecoveryCodes();

        return $twoFactor;
    }

    /**
     * Disable 2FA completely
     */
    public function disableTwoFactor(): void
    {
        if ($this->twoFactorAuth) {
            $this->twoFactorAuth->disable();
        }

        // Revoke all trusted devices
        $this->trustedDevices()->update(['is_active' => false]);
    }

    /**
     * Confirm 2FA setup with TOTP code
     */
    public function confirmTwoFactor(string $code): bool
    {
        if (! $this->twoFactorAuth || ! $this->twoFactorAuth->secret) {
            return false;
        }

        if ($this->verifyTwoFactorCode($code)) {
            $this->twoFactorAuth->confirm();

            return true;
        }

        return false;
    }

    /**
     * Verify TOTP code
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        if (! $this->twoFactorAuth || ! $this->twoFactorAuth->secret) {
            return false;
        }

        $secret = $this->twoFactorAuth->secret;

        return $this->validateTOTP($secret, $code);
    }

    /**
     * Verify recovery code
     */
    public function verifyRecoveryCode(string $code): bool
    {
        if (! $this->twoFactorAuth) {
            return false;
        }

        return $this->twoFactorAuth->useRecoveryCode($code);
    }

    /**
     * Check if device is trusted
     */
    public function isDeviceTrusted(\Illuminate\Http\Request $request): bool
    {
        $deviceId = TrustedDevice::generateDeviceId(
            $request->userAgent() ?? '',
            $request->ip()
        );

        return $this->trustedDevices()
            ->where('device_id', $deviceId)
            ->valid()
            ->exists();
    }

    /**
     * Trust current device
     */
    public function trustDevice(\Illuminate\Http\Request $request, ?string $deviceName = null): TrustedDevice
    {
        return TrustedDevice::createFromRequest($this, $request, $deviceName);
    }

    /**
     * Get active trusted devices
     */
    public function getActiveTrustedDevices()
    {
        return $this->trustedDevices()->valid()->orderBy('last_used_at', 'desc')->get();
    }

    /**
     * Revoke device trust
     */
    public function revokeDeviceTrust(int $deviceId): bool
    {
        $device = $this->trustedDevices()->find($deviceId);
        if ($device) {
            $device->revoke();

            return true;
        }

        return false;
    }

    /**
     * Generate TOTP secret
     */
    protected function generateTwoFactorSecret(): string
    {
        // Generate a random 160-bit (32 character) secret key
        $secret = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 alphabet
        for ($i = 0; $i < 32; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }

        return $secret;
    }

    /**
     * Validate TOTP code
     */
    protected function validateTOTP(string $secret, string $code, int $window = 1): bool
    {
        $timeSlice = floor(time() / 30);

        // Check current time slice and adjacent slices for clock drift
        for ($i = -$window; $i <= $window; $i++) {
            $calculatedCode = $this->generateTOTP($secret, $timeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate TOTP code
     */
    protected function generateTOTP(string $secret, int $timeSlice): string
    {
        // Convert base32 secret to binary
        $secretBinary = $this->base32Decode($secret);

        // Convert time slice to 8-byte big-endian
        $time = pack('N*', 0).pack('N*', $timeSlice);

        // Generate HMAC-SHA1
        $hash = hash_hmac('sha1', $time, $secretBinary, true);

        // Dynamic truncation
        $offset = ord($hash[19]) & 0xF;
        $code = (
            ((ord($hash[$offset + 0]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;

        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode base32 string
     */
    protected function base32Decode(string $secret): string
    {
        $secret = strtoupper($secret);
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $v = 0;
        $vbits = 0;

        for ($i = 0, $j = strlen($secret); $i < $j; $i++) {
            $v <<= 5;
            $v += strpos($alphabet, $secret[$i]);
            $vbits += 5;

            if ($vbits >= 8) {
                $output .= chr($v >> ($vbits - 8));
                $vbits -= 8;
            }
        }

        return $output;
    }

    /**
     * Get 2FA QR code URL
     */
    public function getTwoFactorQrCodeUrl(): ?string
    {
        if (! $this->twoFactorAuth || ! $this->twoFactorAuth->secret) {
            return null;
        }

        $issuerName = 'Midas Tech';
        $userName = $this->name ?? 'User';
        $userIdentifier = $this->email ?? $this->mobile_number ?? "user_{$this->id}";
        $displayLabel = "{$userName} ({$userIdentifier})";

        $secret = $this->twoFactorAuth->secret;

        return "otpauth://totp/{$issuerName}:{$displayLabel}?secret={$secret}&issuer={$issuerName}";
    }

    /**
     * Log 2FA attempt
     */
    public function logTwoFactorAttempt(
        string $codeType,
        bool $successful,
        \Illuminate\Http\Request $request,
        ?string $failureReason = null
    ): void {
        $this->twoFactorAttempts()->create([
            'code_type' => $codeType,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?? '',
            'successful' => $successful,
            'failure_reason' => $failureReason,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Get recent failed 2FA attempts count
     */
    public function getRecentFailedTwoFactorAttempts(int $minutes = 15): int
    {
        return $this->twoFactorAttempts()
            ->where('attempted_at', '>=', now()->subMinutes($minutes))
            ->where('successful', false)
            ->count();
    }

    /**
     * Check if 2FA attempts are rate limited
     */
    public function isTwoFactorRateLimited(): bool
    {
        return $this->getRecentFailedTwoFactorAttempts() >= 5;
    }
}
