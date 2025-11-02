<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * App\Models\NotificationDeliveryTracking
 *
 * @property int $id
 * @property int $notification_log_id
 * @property string $status
 * @property Carbon $tracked_at
 * @property array|null $provider_status
 * @property array|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read NotificationLog|null $notificationLog
 *
 * @method static Builder|NotificationDeliveryTracking newModelQuery()
 * @method static Builder|NotificationDeliveryTracking newQuery()
 * @method static Builder|NotificationDeliveryTracking query()
 * @method static Builder|NotificationDeliveryTracking whereCreatedAt($value)
 * @method static Builder|NotificationDeliveryTracking whereId($value)
 * @method static Builder|NotificationDeliveryTracking whereMetadata($value)
 * @method static Builder|NotificationDeliveryTracking whereNotificationLogId($value)
 * @method static Builder|NotificationDeliveryTracking whereProviderStatus($value)
 * @method static Builder|NotificationDeliveryTracking whereStatus($value)
 * @method static Builder|NotificationDeliveryTracking whereTrackedAt($value)
 * @method static Builder|NotificationDeliveryTracking whereUpdatedAt($value)
 *
 * @mixin Model
 */
class NotificationDeliveryTracking extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $table = 'notification_delivery_tracking';

    protected $fillable = [
        'notification_log_id',
        'status',
        'tracked_at',
        'provider_status',
        'metadata',
    ];

    protected $casts = [
        'tracked_at' => 'datetime',
        'provider_status' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the notification log this tracking belongs to
     */
    public function notificationLog()
    {
        return $this->belongsTo(NotificationLog::class);
    }
}
