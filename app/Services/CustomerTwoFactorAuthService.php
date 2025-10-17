<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Customer-specific Two Factor Authentication Service
 * Separate from admin 2FA to prevent conflicts
 */
class CustomerTwoFactorAuthService
{
    /**
     * Enable 2FA for customer
     */
    public function enableTwoFactor(Customer $customer): array
    {
        try {
            if ($customer->hasCustomerTwoFactorEnabled()) {
                throw new \Exception('Two-factor authentication is already enabled.');
            }

            // Enable 2FA (this creates secret and recovery codes)
            $twoFactor = $customer->enableCustomerTwoFactor();

            // Refresh the relationship to ensure it's loaded
            $customer->load('customerTwoFactorAuth');

            Log::info('Customer 2FA setup started', [
                'customer_id' => $customer->id,
                'email' => $customer->email ?? 'N/A',
            ]);

            // Generate QR code
            $qrCodeUrl = $customer->getCustomerTwoFactorQrCodeUrl();

            if (in_array($qrCodeUrl, [null, '', '0'], true)) {
                throw new \Exception('Failed to generate QR code URL. Please try again.');
            }

            $qrCodeSvg = $this->generateQrCodeSvg($qrCodeUrl);

            return [
                'qr_code_url' => $qrCodeUrl,
                'qr_code_svg' => $qrCodeSvg,
                'recovery_codes' => $twoFactor->recovery_codes,
            ];
        } catch (\Exception $exception) {
            Log::error('Failed to enable customer 2FA', [
                'customer_id' => $customer->id,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            throw $exception;
        }
    }

    /**
     * Confirm 2FA setup with verification code
     */
    public function confirmTwoFactor(Customer $customer, string $code, Request $request): bool
    {
        try {
            if (! $customer->hasCustomerTwoFactorPending()) {
                if (method_exists($customer, 'logTwoFactorAttempt')) {
                    $customer->logTwoFactorAttempt('totp', false, $request, '2FA setup not pending');
                }

                throw new \Exception('Two-factor authentication setup is not pending confirmation.');
            }

            // Check rate limiting for setup confirmation too
            if (method_exists($customer, 'isTwoFactorRateLimited') && $customer->isTwoFactorRateLimited()) {
                if (method_exists($customer, 'logTwoFactorAttempt')) {
                    $customer->logTwoFactorAttempt('totp', false, $request, 'Rate limited during setup');
                }

                throw new \Exception('Too many failed attempts. Please try again in 15 minutes.');
            }

            $verified = $customer->confirmCustomerTwoFactor($code);

            if (! $verified) {
                if (method_exists($customer, 'logTwoFactorAttempt')) {
                    $customer->logTwoFactorAttempt('totp', false, $request, 'Invalid setup code');
                }

                throw new \Exception('The verification code is invalid.');
            }

            // Log successful setup confirmation
            if (method_exists($customer, 'logTwoFactorAttempt')) {
                $customer->logTwoFactorAttempt('totp', true, $request);
            }

            Log::info('Customer 2FA confirmed and enabled', [
                'customer_id' => $customer->id,
                'email' => $customer->email ?? 'N/A',
                'ip_address' => $request->ip(),
            ]);

            return true;
        } catch (\Exception $exception) {
            Log::error('Failed to confirm customer 2FA', [
                'customer_id' => $customer->id,
                'error' => $exception->getMessage(),
                'code_length' => strlen($code),
                'recent_failed_attempts' => method_exists($customer, 'getRecentFailedTwoFactorAttempts') ? $customer->getRecentFailedTwoFactorAttempts() : 0,
            ]);
            throw $exception;
        }
    }

    /**
     * Disable 2FA for customer
     */
    public function disableTwoFactor(Customer $customer, ?string $currentPassword = null, bool $skipPasswordCheck = false): bool
    {
        try {
            if (! $customer->hasCustomerTwoFactorEnabled()) {
                throw new \Exception('Two-factor authentication is not enabled.');
            }

            // Verify current password (unless explicitly skipped for family head actions)
            if (! $skipPasswordCheck && ($currentPassword !== null && $currentPassword !== '' && $currentPassword !== '0') && ! $customer->checkPassword($currentPassword)) {
                throw new \Exception('Current password is incorrect.');
            }

            // Check password is provided when not skipping
            if (! $skipPasswordCheck && in_array($currentPassword, [null, '', '0'], true)) {
                throw new \Exception('Current password is required.');
            }

            $disabled = $customer->disableCustomerTwoFactor();

            if (! $disabled) {
                throw new \Exception('Failed to disable two-factor authentication.');
            }

            Log::info('Customer 2FA disabled', [
                'customer_id' => $customer->id,
                'email' => $customer->email ?? 'N/A',
                'skip_password_check' => $skipPasswordCheck,
            ]);

            return true;
        } catch (\Exception $exception) {
            Log::error('Failed to disable customer 2FA', [
                'customer_id' => $customer->id,
                'error' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    /**
     * Verify 2FA code for customer
     */
    public function verifyCode(Customer $customer, string $code, ?Request $request = null): bool
    {
        try {
            if (! $customer->hasCustomerTwoFactorEnabled()) {
                if ($request instanceof Request && method_exists($customer, 'logTwoFactorAttempt')) {
                    $customer->logTwoFactorAttempt('totp', false, $request, '2FA not enabled');
                }

                throw new \Exception('Two-factor authentication is not enabled.');
            }

            // Check rate limiting
            if (method_exists($customer, 'isTwoFactorRateLimited') && $customer->isTwoFactorRateLimited()) {
                if ($request instanceof Request && method_exists($customer, 'logTwoFactorAttempt')) {
                    $customer->logTwoFactorAttempt('totp', false, $request, 'Rate limited');
                }

                throw new \Exception('Too many failed attempts. Please try again in 15 minutes.');
            }

            if (method_exists($customer, 'verifyCustomerTwoFactorCode')) {
                $isValid = $customer->verifyCustomerTwoFactorCode($code);
            } else {
                throw new \Exception('Customer 2FA verification method not available.');
            }

            if ($request instanceof Request && method_exists($customer, 'logTwoFactorAttempt')) {
                $customer->logTwoFactorAttempt('totp', $isValid, $request, $isValid ? null : 'Invalid code');
            }

            return $isValid;
        } catch (\Exception $exception) {
            Log::error('Failed to verify customer 2FA code', [
                'customer_id' => $customer->id,
                'error' => $exception->getMessage(),
                'code_length' => strlen($code),
                'recent_failed_attempts' => method_exists($customer, 'getRecentFailedTwoFactorAttempts') ? $customer->getRecentFailedTwoFactorAttempts() : 0,
            ]);

            return false;
        }
    }

    /**
     * Generate new recovery codes for customer
     */
    public function generateNewRecoveryCodes(Customer $customer): array
    {
        try {
            if (! $customer->hasCustomerTwoFactorEnabled()) {
                throw new \Exception('Two-factor authentication is not enabled.');
            }

            $twoFactor = $customer->customerTwoFactorAuth;
            $codes = $twoFactor->generateRecoveryCodes();

            Log::info('New customer recovery codes generated', [
                'customer_id' => $customer->id,
                'codes_count' => count($codes),
            ]);

            return $codes;
        } catch (\Exception $exception) {
            Log::error('Failed to generate customer recovery codes', [
                'customer_id' => $customer->id,
                'error' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    /**
     * Get customer 2FA status
     */
    public function getStatus(Customer $customer): array
    {
        $twoFactor = $customer->customerTwoFactorAuth;
        $settings = $customer->customerSecuritySettings;

        return [
            'enabled' => $customer->hasCustomerTwoFactorEnabled(),
            'pending_confirmation' => $customer->hasCustomerTwoFactorPending(),
            'recovery_codes_count' => $twoFactor ? $twoFactor->getRemainingRecoveryCodesCount() : 0,
            'settings_enabled' => $settings && $settings->two_factor_enabled,
            'trusted_devices_count' => $customer->getActiveCustomerTrustedDevices()->count(),
        ];
    }

    /**
     * Get customer trusted devices
     */
    public function getTrustedDevices(Customer $customer): array
    {
        // Clean up expired devices first
        $customer->cleanupExpiredCustomerDevices();

        $devices = $customer->getActiveCustomerTrustedDevices();

        return $devices->map(fn ($device): array => [
            'id' => $device->id,
            'device_id' => $device->device_id,
            'device_name' => $device->device_name,
            'device_type' => $device->device_type,
            'browser' => $device->browser,
            'platform' => $device->platform,
            'ip_address' => $device->ip_address,
            'last_used_at' => $device->last_used_at?->format('Y-m-d H:i:s'),
            'trusted_at' => $device->trusted_at?->format('Y-m-d H:i:s'),
            'expires_at' => $device->expires_at?->format('Y-m-d H:i:s'),
            'is_current' => false, // Will be set by controller if needed
        ])->toArray();
    }

    /**
     * Trust device for customer
     */
    public function trustDevice(Customer $customer, Request $request, int $trustDurationDays = 30): array
    {
        try {
            $trustedDevice = $customer->trustCustomerDevice($request, $trustDurationDays);

            Log::info('Customer device trusted', [
                'customer_id' => $customer->id,
                'device_name' => $trustedDevice->device_name,
                'expires_at' => $trustedDevice->expires_at->format('Y-m-d H:i:s'),
            ]);

            return [
                'success' => true,
                'device_name' => $trustedDevice->device_name,
                'expires_at' => $trustedDevice->expires_at->format('Y-m-d H:i:s'),
            ];
        } catch (\Exception $exception) {
            Log::error('Failed to trust customer device', [
                'customer_id' => $customer->id,
                'error' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }

    /**
     * Revoke device trust for customer
     */
    public function revokeDeviceTrust(Customer $customer, string $deviceId): bool
    {
        try {
            $revoked = $customer->revokeCustomerDeviceTrust($deviceId);

            if ($revoked) {
                Log::info('Customer device trust revoked', [
                    'customer_id' => $customer->id,
                    'device_id' => $deviceId,
                ]);
            }

            return $revoked;
        } catch (\Exception $exception) {
            Log::error('Failed to revoke customer device trust', [
                'customer_id' => $customer->id,
                'device_id' => $deviceId,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if device is trusted for customer
     */
    public function isDeviceTrusted(Customer $customer, Request $request): bool
    {
        try {
            return $customer->isCustomerDeviceTrusted($request);
        } catch (\Exception $exception) {
            Log::error('Failed to check customer device trust', [
                'customer_id' => $customer->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verify 2FA code during login (similar to admin verifyTwoFactorLogin)
     */
    public function verifyTwoFactorLogin(Customer $customer, string $code, string $codeType, Request $request): bool
    {
        try {
            if (! $customer->hasCustomerTwoFactorEnabled()) {
                if (method_exists($customer, 'logTwoFactorAttempt')) {
                    $customer->logTwoFactorAttempt($codeType, false, $request, '2FA not enabled');
                }

                throw new \Exception('Two-factor authentication is not enabled.');
            }

            // Check if customer is rate limited
            if (method_exists($customer, 'isTwoFactorRateLimited') && $customer->isTwoFactorRateLimited()) {
                if (method_exists($customer, 'logTwoFactorAttempt')) {
                    $customer->logTwoFactorAttempt($codeType, false, $request, 'Rate limited');
                }

                throw new \Exception('Too many failed attempts. Please try again in 15 minutes.');
            }

            $isValid = false;

            // Verify based on code type
            switch ($codeType) {
                case 'totp':
                    if (method_exists($customer, 'verifyCustomerTwoFactorCode')) {
                        $isValid = $customer->verifyCustomerTwoFactorCode($code);
                    } else {
                        throw new \Exception('Customer 2FA verification method not available.');
                    }

                    break;
                case 'recovery':
                    if (method_exists($customer, 'verifyCustomerRecoveryCode')) {
                        $isValid = $customer->verifyCustomerRecoveryCode($code);
                    } else {
                        throw new \Exception('Customer recovery code verification method not available.');
                    }

                    break;
                default:
                    if (method_exists($customer, 'logTwoFactorAttempt')) {
                        $customer->logTwoFactorAttempt($codeType, false, $request, 'Invalid code type');
                    }

                    throw new \Exception('Invalid code type.');
            }

            if (! $isValid) {
                if (method_exists($customer, 'logTwoFactorAttempt')) {
                    $customer->logTwoFactorAttempt($codeType, false, $request, 'Invalid code');
                }

                throw new \Exception('The verification code is invalid.');
            }

            // Log successful attempt
            if (method_exists($customer, 'logTwoFactorAttempt')) {
                $customer->logTwoFactorAttempt($codeType, true, $request);
            }

            Log::info('Customer 2FA login verification successful', [
                'customer_id' => $customer->id,
                'email' => $customer->email ?? 'N/A',
                'code_type' => $codeType,
                'ip_address' => $request->ip(),
            ]);

            return true;
        } catch (\Exception $exception) {
            Log::error('Failed to verify customer 2FA login', [
                'customer_id' => $customer->id,
                'error' => $exception->getMessage(),
                'code_type' => $codeType,
                'recent_failed_attempts' => method_exists($customer, 'getRecentFailedTwoFactorAttempts') ? $customer->getRecentFailedTwoFactorAttempts() : 0,
            ]);
            throw $exception;
        }
    }

    /**
     * Generate QR code SVG
     */
    private function generateQrCodeSvg(string $url): string
    {
        $svg = QrCode::format('svg')
            ->size(400)
            ->errorCorrection('M')
            ->margin(1)
            ->generate($url);

        // Remove XML declaration for better HTML integration
        $svg = preg_replace('/<\?xml[^>]*\?>/i', '', (string) $svg);

        // Clean up and optimize the SVG
        $svg = trim($svg);

        return $svg;
    }
}
