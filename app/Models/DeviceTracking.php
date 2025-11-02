<?php

namespace App\Models;

use Database\Factories\DeviceTrackingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
/**
 * App\Models\DeviceTracking
 *
 * @property int $id
 * @property string $trackable_type
 * @property int $trackable_id
 * @property string $device_id
 * @property string|null $device_name
 * @property string $device_type
 * @property string $browser
 * @property string|null $browser_version
 * @property string $operating_system
 * @property string|null $os_version
 * @property string $platform
 * @property array|null $screen_resolution
 * @property array|null $hardware_info
 * @property string $user_agent
 * @property array $fingerprint_data
 * @property int $trust_score
 * @property bool $is_trusted
 * @property Carbon $first_seen_at
 * @property Carbon $last_seen_at
 * @property Carbon|null $trusted_at
 * @property Carbon|null $trust_expires_at
 * @property array|null $location_history
 * @property array|null $ip_history
 * @property int $login_count
 * @property int $failed_login_attempts
 * @property Carbon|null $last_failed_login_at
 * @property bool $is_blocked
 * @property string|null $blocked_reason
 * @property Carbon|null $blocked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $display_name
 * @property-read string|null $last_ip
 * @property-read array|null $last_location
 * @property-read Model|Model $trackable
 *
 * @method static Builder|DeviceTracking active(int $days = 30)
 * @method static Builder|DeviceTracking blocked()
 * @method static DeviceTrackingFactory factory($count = null, $state = [])
 * @method static Builder|DeviceTracking highRisk(int $threshold = 70)
 * @method static Builder|DeviceTracking newModelQuery()
 * @method static Builder|DeviceTracking newQuery()
 * @method static Builder|DeviceTracking query()
 * @method static Builder|DeviceTracking suspicious()
 * @method static Builder|DeviceTracking trusted()
 * @method static Builder|DeviceTracking untrusted()
 * @method static Builder|DeviceTracking whereBlockedAt($value)
 * @method static Builder|DeviceTracking whereBlockedReason($value)
 * @method static Builder|DeviceTracking whereBrowser($value)
 * @method static Builder|DeviceTracking whereBrowserVersion($value)
 * @method static Builder|DeviceTracking whereCreatedAt($value)
 * @method static Builder|DeviceTracking whereDeviceId($value)
 * @method static Builder|DeviceTracking whereDeviceName($value)
 * @method static Builder|DeviceTracking whereDeviceType($value)
 * @method static Builder|DeviceTracking whereFailedLoginAttempts($value)
 * @method static Builder|DeviceTracking whereFingerprintData($value)
 * @method static Builder|DeviceTracking whereFirstSeenAt($value)
 * @method static Builder|DeviceTracking whereHardwareInfo($value)
 * @method static Builder|DeviceTracking whereId($value)
 * @method static Builder|DeviceTracking whereIpHistory($value)
 * @method static Builder|DeviceTracking whereIsBlocked($value)
 * @method static Builder|DeviceTracking whereIsTrusted($value)
 * @method static Builder|DeviceTracking whereLastFailedLoginAt($value)
 * @method static Builder|DeviceTracking whereLastSeenAt($value)
 * @method static Builder|DeviceTracking whereLocationHistory($value)
 * @method static Builder|DeviceTracking whereLoginCount($value)
 * @method static Builder|DeviceTracking whereOperatingSystem($value)
 * @method static Builder|DeviceTracking whereOsVersion($value)
 * @method static Builder|DeviceTracking wherePlatform($value)
 * @method static Builder|DeviceTracking whereScreenResolution($value)
 * @method static Builder|DeviceTracking whereTrackableId($value)
 * @method static Builder|DeviceTracking whereTrackableType($value)
 * @method static Builder|DeviceTracking whereTrustExpiresAt($value)
 * @method static Builder|DeviceTracking whereTrustScore($value)
 * @method static Builder|DeviceTracking whereTrustedAt($value)
 * @method static Builder|DeviceTracking whereUpdatedAt($value)
 * @method static Builder|DeviceTracking whereUserAgent($value)
 *
 * @mixin Model
 */
class DeviceTracking extends Model
{
    use HasFactory;

    protected $table = 'device_tracking';

    protected $fillable = [
        'trackable_type',
        'trackable_id',
        'device_id',
        'device_name',
        'device_type',
        'browser',
        'browser_version',
        'operating_system',
        'os_version',
        'platform',
        'screen_resolution',
        'hardware_info',
        'user_agent',
        'fingerprint_data',
        'trust_score',
        'is_trusted',
        'first_seen_at',
        'last_seen_at',
        'trusted_at',
        'trust_expires_at',
        'location_history',
        'ip_history',
        'login_count',
        'failed_login_attempts',
        'last_failed_login_at',
        'is_blocked',
        'blocked_reason',
        'blocked_at',
    ];

    protected $casts = [
        'screen_resolution' => 'array',
        'hardware_info' => 'array',
        'fingerprint_data' => 'array',
        'location_history' => 'array',
        'ip_history' => 'array',
        'trust_score' => 'integer',
        'login_count' => 'integer',
        'failed_login_attempts' => 'integer',
        'is_trusted' => 'boolean',
        'is_blocked' => 'boolean',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'trusted_at' => 'datetime',
        'trust_expires_at' => 'datetime',
        'last_failed_login_at' => 'datetime',
        'blocked_at' => 'datetime',
    ];

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(DeviceSession::class);
    }

    public function securityEvents(): HasMany
    {
        return $this->hasMany(DeviceSecurityEvent::class);
    }

    protected function scopeTrusted($query)
    {
        return $query->where('is_trusted', true)
            ->where(static function ($q): void {
                $q->whereNull('trust_expires_at')
                    ->orWhere('trust_expires_at', '>', now());
            });
    }

    protected function scopeUntrusted($query)
    {
        return $query->where('is_trusted', false);
    }

    protected function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }

    protected function scopeActive($query, int $days = 30)
    {
        return $query->where('last_seen_at', '>=', now()->subDays($days));
    }

    protected function scopeHighRisk($query, int $threshold = 70)
    {
        return $query->where('trust_score', '<', $threshold);
    }

    protected function scopeSuspicious($query)
    {
        return $query->where(static function ($q): void {
            $q->where('failed_login_attempts', '>=', 3)
                ->orWhere('trust_score', '<', 30)
                ->orWhereHas('securityEvents', static function ($eventQuery): void {
                    $eventQuery->where('event_severity', 'high')
                        ->orWhere('event_severity', 'critical')
                        ->where('is_resolved', false);
                });
        });
    }

    public function isTrusted(): bool
    {
        return $this->is_trusted && ! $this->isTrustExpired();
    }

    public function isTrustExpired(): bool
    {
        return $this->trust_expires_at && $this->trust_expires_at->isPast();
    }

    public function isBlocked(): bool
    {
        return $this->is_blocked;
    }

    public function isHighRisk(): bool
    {
        return $this->trust_score < 50 || $this->failed_login_attempts >= 3;
    }

    public function grantTrust(int $durationDays = 30, ?string $reason = null): void
    {
        $this->update([
            'is_trusted' => true,
            'trusted_at' => now(),
            'trust_expires_at' => now()->addDays($durationDays),
            'trust_score' => min(100, $this->trust_score + 20),
        ]);

        $this->logSecurityEvent('trust_granted', 'medium', $reason ?? 'Device trust granted by user');
    }

    public function revokeTrust(?string $reason = null): void
    {
        $this->update([
            'is_trusted' => false,
            'trusted_at' => null,
            'trust_expires_at' => null,
        ]);

        $this->logSecurityEvent('trust_revoked', 'medium', $reason ?? 'Device trust revoked');
    }

    public function blockDevice(string $reason): void
    {
        $this->update([
            'is_blocked' => true,
            'is_trusted' => false,
            'blocked_reason' => $reason,
            'blocked_at' => now(),
        ]);

        $this->logSecurityEvent('device_blocked', 'high', 'Device blocked: '.$reason);
    }

    public function unblockDevice(?string $reason = null): void
    {
        $this->update([
            'is_blocked' => false,
            'blocked_reason' => null,
            'blocked_at' => null,
        ]);

        $this->logSecurityEvent('device_unblocked', 'medium', $reason ?? 'Device unblocked');
    }

    public function recordSuccessfulLogin(string $ip, ?array $location = null): void
    {
        $this->increment('login_count');
        $this->update([
            'last_seen_at' => now(),
            'failed_login_attempts' => 0, // Reset on successful login
            'trust_score' => min(100, $this->trust_score + 2), // Increase trust slightly
        ]);

        $this->updateLocationHistory($location);
        $this->updateIpHistory($ip);

        $this->logSecurityEvent('successful_login', 'low', 'Successful login recorded');
    }

    public function recordFailedLogin(string $ip, string $reason): void
    {
        $this->increment('failed_login_attempts');
        $this->update([
            'last_failed_login_at' => now(),
            'trust_score' => max(0, $this->trust_score - 5), // Decrease trust
        ]);

        $this->updateIpHistory($ip);

        $this->logSecurityEvent('failed_login', 'medium', 'Failed login: '.$reason);

        // Auto-block after too many failures
        if ($this->failed_login_attempts >= 5) {
            $this->blockDevice('Too many failed login attempts');
        }
    }

    public function updateLastSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }

    public function calculateTrustScore(): int
    {
        $score = 50; // Base score

        // Age factor (older devices are more trusted)
        $daysSinceFirstSeen = $this->first_seen_at->diffInDays(now());
        $score += min(20, floor($daysSinceFirstSeen / 7)); // +1 per week, max +20

        // Usage frequency
        $score += min(15, $this->login_count); // +1 per login, max +15

        // Failed login penalty
        $score -= $this->failed_login_attempts * 3;

        // Trust history bonus
        if ($this->is_trusted) {
            $score += 10;
        }

        // Recent activity bonus
        if ($this->last_seen_at && $this->last_seen_at->isAfter(now()->subWeek())) {
            $score += 5;
        }

        // Security events penalty
        $criticalEvents = $this->securityEvents()
            ->where('event_severity', 'critical')
            ->where('is_resolved', false)
            ->count();
        $score -= $criticalEvents * 10;

        return max(0, min(100, $score));
    }

    public function updateTrustScore(): void
    {
        $newScore = $this->calculateTrustScore();
        $this->update(['trust_score' => $newScore]);

        // Auto-block if score is too low
        if ($newScore < 20 && ! $this->is_blocked) {
            $this->blockDevice('Trust score too low');
        }
    }

    protected function getDisplayNameAttribute(): string
    {
        if ($this->device_name) {
            return $this->device_name;
        }

        return sprintf('%s on %s', $this->browser, $this->operating_system);
    }

    protected function getLastLocationAttribute(): ?array
    {
        $history = $this->location_history ?? [];

        return end($history) ?: null;
    }

    protected function getLastIpAttribute(): ?string
    {
        $history = $this->ip_history ?? [];

        return end($history) ?: null;
    }

    public function getActivitySummary(int $days = 30): array
    {
        $sessions = $this->sessions()
            ->where('started_at', '>=', now()->subDays($days))
            ->get();

        return [
            'total_sessions' => $sessions->count(),
            'total_duration' => $sessions->sum('duration_seconds'),
            'avg_session_duration' => $sessions->avg('duration_seconds'),
            'unique_ips' => $sessions->unique('ip_address')->count(),
            'unique_locations' => $sessions->whereNotNull('location_city')->unique('location_city')->count(),
            'suspicious_sessions' => $sessions->where('is_suspicious', true)->count(),
        ];
    }

    public static function generateDeviceId(array $fingerprintData): string
    {
        // Create a unique device ID based on fingerprint data
        $fingerprintString = json_encode($fingerprintData);

        return 'device_'.hash('sha256', $fingerprintString);
    }

    protected function logSecurityEvent(string $type, string $severity, string $description, array $data = []): void
    {
        $this->securityEvents()->create([
            'event_type' => $type,
            'event_severity' => $severity,
            'description' => $description,
            'event_data' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'occurred_at' => now(),
        ]);
    }

    protected function updateLocationHistory(?array $location = null): void
    {
        if ($location === null || $location === []) {
            return;
        }

        $history = $this->location_history ?? [];
        $history[] = array_merge($location, ['timestamp' => now()->toISOString()]);

        // Keep only last 50 locations
        if (count($history) > 50) {
            $history = array_slice($history, -50);
        }

        $this->update(['location_history' => $history]);
    }

    protected function updateIpHistory(string $ip): void
    {
        $history = $this->ip_history ?? [];

        // Add new IP if not already the most recent
        if (empty($history) || end($history)['ip'] !== $ip) {
            $history[] = [
                'ip' => $ip,
                'timestamp' => now()->toISOString(),
            ];

            // Keep only last 100 IPs
            if (count($history) > 100) {
                $history = array_slice($history, -100);
            }

            $this->update(['ip_history' => $history]);
        }
    }
}
