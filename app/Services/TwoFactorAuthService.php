<?php

namespace App\Services;

use App\Models\TrustedDevice;
use App\Models\TwoFactorAttempt;
use App\Models\TwoFactorAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorAuthService extends BaseService
{
    /**
     * Enable 2FA for user - start setup process
     */
    public function enableTwoFactor($user): array
    {
        return $this->createInTransaction(function () use ($user): array {
            // Check if already enabled
            if ($user->hasTwoFactorEnabled()) {
                throw new \Exception('Two-factor authentication is already enabled.');
            }

            // Enable 2FA (this creates secret and recovery codes)
            $twoFactor = $user->enableTwoFactor();

            Log::info('2FA setup started', [
                'user_type' => $user::class,
                'user_id' => $user->id,
                'email' => $user->email ?? 'N/A',
            ]);

            // Refresh user to ensure relationship is loaded
            $user->refresh();

            $qrCodeUrl = $user->getTwoFactorQrCodeUrl();

            return [
                'secret' => $twoFactor->secret,
                'qr_code_url' => $qrCodeUrl,
                'recovery_codes' => $twoFactor->recovery_codes,
                'qr_code_svg' => $qrCodeUrl ? $this->generateQrCodeSvg($qrCodeUrl) : null,
            ];
        });
    }

    /**
     * Confirm 2FA setup with verification code
     */
    public function confirmTwoFactor($user, string $code, Request $request): bool
    {
        return $this->updateInTransaction(static function () use ($user, $code, $request): bool {
            // Rate limiting check
            if ($user->isTwoFactorRateLimited()) {
                $user->logTwoFactorAttempt('totp', false, $request, 'Rate limited');
                throw new \Exception('Too many failed attempts. Please try again later.');
            }

            // Verify the code
            if (! $user->confirmTwoFactor($code)) {
                $user->logTwoFactorAttempt('totp', false, $request, 'Invalid code');
                throw new \Exception('Invalid verification code. Please try again.');
            }

            // Log successful confirmation
            $user->logTwoFactorAttempt('totp', true, $request);

            Log::info('2FA confirmed and enabled', [
                'user_type' => $user::class,
                'user_id' => $user->id,
                'email' => $user->email ?? 'N/A',
                'ip_address' => $request->ip(),
            ]);

            return true;
        });
    }

    /**
     * Disable 2FA for user
     */
    public function disableTwoFactor($user, ?string $currentPassword = null): bool
    {
        return $this->updateInTransaction(static function () use ($user, $currentPassword): bool {
            // Verify current password if provided (for security)
            if ($currentPassword && method_exists($user, 'checkPassword') && ! $user->checkPassword($currentPassword)) {
                throw new \Exception('Current password is incorrect.');
            }

            // Disable 2FA
            $user->disableTwoFactor();

            Log::info('2FA disabled', [
                'user_type' => $user::class,
                'user_id' => $user->id,
                'email' => $user->email ?? 'N/A',
            ]);

            return true;
        });
    }

    /**
     * Verify 2FA code during login
     */
    public function verifyTwoFactorLogin($user, string $code, string $codeType, Request $request): bool
    {
        return $this->executeInTransaction(static function () use ($user, $code, $codeType, $request): bool {
            // Rate limiting check
            if ($user->isTwoFactorRateLimited()) {
                $user->logTwoFactorAttempt($codeType, false, $request, 'Rate limited');
                throw new \Exception('Too many failed attempts. Please try again later.');
            }
            // Verify based on code type
            $isValid = match ($codeType) {
                'totp' => $user->verifyTwoFactorCode($code),
                'recovery' => $user->verifyRecoveryCode($code),
                default => throw new \Exception('Invalid code type.'),
            };

            // Log the attempt
            $user->logTwoFactorAttempt(
                $codeType,
                $isValid,
                $request,
                $isValid ? null : 'Invalid code'
            );

            if (! $isValid) {
                if ($codeType === 'recovery') {
                    throw new \Exception('Invalid recovery code. This code may have already been used or is not valid.');
                }
                throw new \Exception('Invalid verification code.');
            }

            Log::info('2FA login verification successful', [
                'user_type' => $user::class,
                'user_id' => $user->id,
                'code_type' => $codeType,
                'ip_address' => $request->ip(),
            ]);

            return true;
        });
    }

    /**
     * Trust current device
     */
    public function trustDevice($user, Request $request, ?string $deviceName = null): array
    {
        return $this->createInTransaction(static function () use ($user, $request, $deviceName): array {
            $userAgent = $request->userAgent() ?? '';
            $ipAddress = $request->ip();
            $deviceId = TrustedDevice::generateDeviceId($userAgent, $ipAddress);

            // Check if device already exists and is active
            $existingDevice = TrustedDevice::query()->where('authenticatable_type', $user::class)
                ->where('authenticatable_id', $user->id)
                ->where('device_id', $deviceId)
                ->where('is_active', true)
                ->first();

            $wasAlreadyTrusted = (bool) $existingDevice;
            $device = $user->trustDevice($request, $deviceName);

            Log::info('Device trusted', [
                'user_type' => $user::class,
                'user_id' => $user->id,
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
                'ip_address' => $request->ip(),
                'was_already_trusted' => $wasAlreadyTrusted,
            ]);

            return [
                'device' => $device,
                'was_already_trusted' => $wasAlreadyTrusted,
            ];
        });
    }

    /**
     * Revoke device trust
     */
    public function revokeDeviceTrust($user, int $deviceId): bool
    {
        return $this->updateInTransaction(static function () use ($user, $deviceId) {
            $success = $user->revokeDeviceTrust($deviceId);

            if ($success) {
                Log::info('Device trust revoked', [
                    'user_type' => $user::class,
                    'user_id' => $user->id,
                    'device_id' => $deviceId,
                ]);
            }

            return $success;
        });
    }

    /**
     * Generate new recovery codes
     */
    public function generateNewRecoveryCodes($user): array
    {
        return $this->updateInTransaction(static function () use ($user) {
            if (! $user->hasTwoFactorEnabled()) {
                throw new \Exception('Two-factor authentication is not enabled.');
            }

            $codes = $user->twoFactorAuth->generateRecoveryCodes();

            Log::info('New recovery codes generated', [
                'user_type' => $user::class,
                'user_id' => $user->id,
                'codes_count' => count($codes),
            ]);

            return $codes;
        });
    }

    /**
     * Get 2FA status for user
     */
    public function getTwoFactorStatus($user): array
    {
        $twoFactor = $user->twoFactorAuth;

        return [
            'enabled' => $user->hasTwoFactorEnabled(),
            'pending_confirmation' => $user->hasTwoFactorPending(),
            'recovery_codes_count' => $twoFactor ? $twoFactor->getRemainingRecoveryCodesCount() : 0,
            'trusted_devices_count' => $user->getActiveTrustedDevices()->count(),
            'recent_attempts' => $user->getRecentFailedTwoFactorAttempts(),
            'is_rate_limited' => $user->isTwoFactorRateLimited(),
        ];
    }

    /**
     * Get status for user (alias for consistency with customer service)
     */
    public function getStatus($user): array
    {
        return $this->getTwoFactorStatus($user);
    }

    /**
     * Get trusted devices for user
     */
    public function getTrustedDevices($user): array
    {
        return $user->getActiveTrustedDevices()->map(fn ($device): array => [
            'id' => $device->id,
            'device_name' => $device->device_name,
            'display_name' => $device->getDisplayName(),
            'device_type' => $device->device_type,
            'browser' => $device->browser,
            'platform' => $device->platform,
            'ip_address' => $device->ip_address,
            'last_used_at' => $device->last_used_at,
            'trusted_at' => $device->trusted_at,
            'expires_at' => $device->expires_at,
        ])->toArray();
    }

    /**
     * Check if device is trusted
     */
    public function isDeviceTrusted($user, Request $request): bool
    {
        return $user->isDeviceTrusted($request);
    }

    /**
     * Generate QR code SVG
     */
    protected function generateQrCodeSvg(string $url): string
    {
        return QrCode::format('svg')
            ->size(200)
            ->margin(2)
            ->generate($url);
    }

    /**
     * Cleanup expired devices and old attempts
     */
    public function cleanupExpiredData(): int
    {
        return $this->executeInTransaction(static function (): float|int|array {
            $expiredDevices = TrustedDevice::query()->where('expires_at', '<', now())
                ->where('is_active', true)
                ->count();

            // Deactivate expired devices
            TrustedDevice::query()->where('expires_at', '<', now())
                ->update(['is_active' => false]);

            // Delete old 2FA attempts (keep last 30 days)
            $oldAttempts = TwoFactorAttempt::query()->where('attempted_at', '<', now()->subDays(30))
                ->count();

            TwoFactorAttempt::query()->where('attempted_at', '<', now()->subDays(30))
                ->delete();

            Log::info('2FA cleanup completed', [
                'expired_devices' => $expiredDevices,
                'old_attempts_deleted' => $oldAttempts,
            ]);

            return $expiredDevices + $oldAttempts;
        });
    }

    /**
     * Get 2FA statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_users_with_2fa' => TwoFactorAuth::query()->where('is_active', true)->count(),
            'total_trusted_devices' => TrustedDevice::valid()->count(),
            'recent_2fa_attempts' => TwoFactorAttempt::query()->where('attempted_at', '>=', now()->subDay())->count(),
            'successful_2fa_attempts_today' => TwoFactorAttempt::query()->where('attempted_at', '>=', now()->startOfDay())
                ->where('successful', true)
                ->count(),
            'failed_2fa_attempts_today' => TwoFactorAttempt::query()->where('attempted_at', '>=', now()->startOfDay())
                ->where('successful', false)
                ->count(),
        ];
    }
}
