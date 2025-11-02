<?php

namespace App\Models;

use Database\Factories\TrustedDeviceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
/**
 * App\Models\TrustedDevice
 *
 * @property int $id
 * @property string $authenticatable_type
 * @property int $authenticatable_id
 * @property string $device_id
 * @property string $device_name
 * @property string|null $device_type
 * @property string|null $browser
 * @property string|null $platform
 * @property string $ip_address
 * @property string $user_agent
 * @property Carbon|null $last_used_at
 * @property Carbon $trusted_at
 * @property Carbon|null $expires_at
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|Model $authenticatable
 *
 * @method static Builder|TrustedDevice active()
 * @method static TrustedDeviceFactory factory($count = null, $state = [])
 * @method static Builder|TrustedDevice newModelQuery()
 * @method static Builder|TrustedDevice newQuery()
 * @method static Builder|TrustedDevice query()
 * @method static Builder|TrustedDevice valid()
 * @method static Builder|TrustedDevice whereAuthenticatableId($value)
 * @method static Builder|TrustedDevice whereAuthenticatableType($value)
 * @method static Builder|TrustedDevice whereBrowser($value)
 * @method static Builder|TrustedDevice whereCreatedAt($value)
 * @method static Builder|TrustedDevice whereDeviceId($value)
 * @method static Builder|TrustedDevice whereDeviceName($value)
 * @method static Builder|TrustedDevice whereDeviceType($value)
 * @method static Builder|TrustedDevice whereExpiresAt($value)
 * @method static Builder|TrustedDevice whereId($value)
 * @method static Builder|TrustedDevice whereIpAddress($value)
 * @method static Builder|TrustedDevice whereIsActive($value)
 * @method static Builder|TrustedDevice whereLastUsedAt($value)
 * @method static Builder|TrustedDevice wherePlatform($value)
 * @method static Builder|TrustedDevice whereTrustedAt($value)
 * @method static Builder|TrustedDevice whereUpdatedAt($value)
 * @method static Builder|TrustedDevice whereUserAgent($value)
 *
 * @mixin Model
 */
class TrustedDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'authenticatable_type',
        'authenticatable_id',
        'device_id',
        'device_name',
        'device_type',
        'browser',
        'platform',
        'ip_address',
        'user_agent',
        'last_used_at',
        'trusted_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'trusted_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the authenticatable entity (User or Customer)
     */
    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Generate device fingerprint from request
     */
    public static function generateDeviceId(string $userAgent, string $ipAddress, ?string $additionalData = null): string
    {
        $fingerprint = $userAgent.$ipAddress.($additionalData ?? '');

        return hash('sha256', $fingerprint);
    }

    /**
     * Create trusted device from request
     */
    public static function createFromRequest(
        $authenticatable,
        Request $request,
        ?string $deviceName = null
    ): self {
        $userAgent = $request->userAgent() ?? '';
        $ipAddress = $request->ip();
        $deviceId = self::generateDeviceId($userAgent, $ipAddress);

        // Check if device already exists for this user
        $existingDevice = self::query()->where('authenticatable_type', $authenticatable::class)
            ->where('authenticatable_id', $authenticatable->id)
            ->where('device_id', $deviceId)
            ->first();

        if ($existingDevice) {
            // If device exists but is inactive, reactivate it
            if (! $existingDevice->is_active) {
                $existingDevice->update([
                    'device_name' => $deviceName ?? $existingDevice->device_name,
                    'last_used_at' => now(),
                    'trusted_at' => now(),
                    'expires_at' => now()->addDays(config('security.device_trust_duration', 30)),
                    'is_active' => true,
                ]);

                return $existingDevice;
            }

            // If device is already active, update last used and return existing
            $existingDevice->update([
                'device_name' => $deviceName ?? $existingDevice->device_name,
                'last_used_at' => now(),
            ]);

            return $existingDevice;
        }

        // Parse user agent for device info
        $deviceInfo = self::parseUserAgent($userAgent);

        // Create new device
        return self::query()->create([
            'authenticatable_type' => $authenticatable::class,
            'authenticatable_id' => $authenticatable->id,
            'device_id' => $deviceId,
            'device_name' => $deviceName ?? $deviceInfo['device_name'],
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'platform' => $deviceInfo['platform'],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'last_used_at' => now(),
            'trusted_at' => now(),
            'expires_at' => now()->addDays(config('security.device_trust_duration', 30)),
            'is_active' => true,
        ]);
    }

    /**
     * Parse user agent string for device information
     */
    protected static function parseUserAgent(string $userAgent): array
    {
        $deviceType = 'desktop';
        $browser = 'Unknown';
        $platform = 'Unknown';
        $deviceName = 'Unknown Device';

        // Detect mobile devices
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            $deviceType = 'mobile';
            if (str_contains($userAgent, 'iPad')) {
                $deviceType = 'tablet';
                $deviceName = 'iPad';
                $platform = 'iOS';
            } elseif (str_contains($userAgent, 'iPhone')) {
                $deviceName = 'iPhone';
                $platform = 'iOS';
            } elseif (str_contains($userAgent, 'Android')) {
                $platform = 'Android';
                $deviceName = 'Android Device';
            }
        }

        // Detect browser
        if (str_contains($userAgent, 'Chrome') && in_array(str_contains($userAgent, 'Edg'), [0, false], true)) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Safari') && in_array(str_contains($userAgent, 'Chrome'), [0, false], true)) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Edg')) {
            $browser = 'Edge';
        }

        // Detect platform if not mobile
        if ($deviceType === 'desktop') {
            if (str_contains($userAgent, 'Windows')) {
                $platform = 'Windows';
                $deviceName = 'Windows PC';
            } elseif (str_contains($userAgent, 'Mac')) {
                $platform = 'macOS';
                $deviceName = 'Mac';
            } elseif (str_contains($userAgent, 'Linux')) {
                $platform = 'Linux';
                $deviceName = 'Linux PC';
            }
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
            'device_name' => $deviceName,
        ];
    }

    /**
     * Check if device is still valid and trusted
     */
    public function isValid(): bool
    {
        return $this->is_active &&
               (! $this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Update last used timestamp
     */
    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Revoke trust for this device
     */
    public function revoke(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Extend trust period
     */
    public function extendTrust(?int $days = null): void
    {
        $days ??= config('security.device_trust_duration', 30);
        $this->update(['expires_at' => now()->addDays($days)]);
    }

    /**
     * Get device display name with platform info
     */
    public function getDisplayName(): string
    {
        return $this->device_name.
               ($this->browser ? sprintf(' (%s)', $this->browser) : '').
               ($this->platform ? ' - '.$this->platform : '');
    }

    /**
     * Scope for active devices only
     */
    protected function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for valid (active and not expired) devices
     */
    protected function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(static function ($q): void {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
