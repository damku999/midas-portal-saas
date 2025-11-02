<?php

namespace App\Models\Customer;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
/**
 * App\Models\Customer\CustomerSecuritySettings
 *
 * @property int $id
 * @property string $settingable_type
 * @property int $settingable_id
 * @property bool $two_factor_enabled
 * @property bool $device_tracking_enabled
 * @property bool $login_notifications
 * @property bool $security_alerts
 * @property int $session_timeout
 * @property int $device_trust_duration
 * @property array|null $notification_preferences
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|Model $settingable
 *
 * @method static Builder|CustomerSecuritySettings customersOnly()
 * @method static Builder|CustomerSecuritySettings newModelQuery()
 * @method static Builder|CustomerSecuritySettings newQuery()
 * @method static Builder|CustomerSecuritySettings query()
 * @method static Builder|CustomerSecuritySettings whereCreatedAt($value)
 * @method static Builder|CustomerSecuritySettings whereDeviceTrackingEnabled($value)
 * @method static Builder|CustomerSecuritySettings whereDeviceTrustDuration($value)
 * @method static Builder|CustomerSecuritySettings whereId($value)
 * @method static Builder|CustomerSecuritySettings whereLoginNotifications($value)
 * @method static Builder|CustomerSecuritySettings whereNotificationPreferences($value)
 * @method static Builder|CustomerSecuritySettings whereSecurityAlerts($value)
 * @method static Builder|CustomerSecuritySettings whereSessionTimeout($value)
 * @method static Builder|CustomerSecuritySettings whereSettingableId($value)
 * @method static Builder|CustomerSecuritySettings whereSettingableType($value)
 * @method static Builder|CustomerSecuritySettings whereTwoFactorEnabled($value)
 * @method static Builder|CustomerSecuritySettings whereUpdatedAt($value)
 *
 * @mixin Model
 */
class CustomerSecuritySettings extends Model
{
    protected $table = 'security_settings';

    protected $fillable = [
        'settingable_type',
        'settingable_id',
        'two_factor_enabled',
        'device_tracking_enabled',
        'login_notifications',
        'security_alerts',
        'session_timeout',
        'device_trust_duration',
        'notification_preferences',
    ];

    protected $casts = [
        'two_factor_enabled' => 'boolean',
        'device_tracking_enabled' => 'boolean',
        'login_notifications' => 'boolean',
        'security_alerts' => 'boolean',
        'session_timeout' => 'integer',
        'device_trust_duration' => 'integer',
        'notification_preferences' => 'array',
    ];

    /**
     * Get the settingable model (Customer only)
     */
    public function settingable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to only customer records
     */
    protected function scopeCustomersOnly($query)
    {
        return $query->where('settingable_type', Customer::class);
    }

    /**
     * Get default settings for customers
     */
    public static function getDefaults(): array
    {
        return [
            'two_factor_enabled' => false,
            'device_tracking_enabled' => true,
            'login_notifications' => true,
            'security_alerts' => true,
            'session_timeout' => 3600, // 1 hour
            'device_trust_duration' => 30, // 30 days
            'notification_preferences' => [
                'email_login_alerts' => true,
                'email_security_alerts' => true,
                'email_2fa_changes' => true,
                'sms_security_alerts' => false,
                'sms_login_alerts' => false,
            ],
        ];
    }

    /**
     * Update notification preference
     */
    public function updateNotificationPreference(string $key, bool $value): void
    {
        $preferences = $this->notification_preferences ?? [];
        $preferences[$key] = $value;
        $this->notification_preferences = $preferences;
        $this->save();
    }

    /**
     * Enable 2FA in settings
     */
    public function enableTwoFactor(): void
    {
        $this->two_factor_enabled = true;
        $this->save();
    }

    /**
     * Disable 2FA in settings
     */
    public function disableTwoFactor(): void
    {
        $this->two_factor_enabled = false;
        $this->save();
    }
}
