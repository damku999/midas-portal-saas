<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * App\Models\NotificationLog
 *
 * @property int $id
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property int|null $notification_type_id
 * @property int|null $template_id
 * @property string $channel
 * @property string $recipient
 * @property string|null $subject
 * @property string $message_content
 * @property array|null $variables_used
 * @property string $status
 * @property Carbon|null $sent_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $read_at
 * @property string|null $error_message
 * @property array|null $api_response
 * @property int|null $sent_by
 * @property int $retry_count
 * @property Carbon|null $next_retry_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, NotificationDeliveryTracking> $deliveryTracking
 * @property-read int|null $delivery_tracking_count
 * @property-read string $channel_icon
 * @property-read string $status_color
 * @property-read Model|Model $notifiable
 * @property-read NotificationType|null $notificationType
 * @property-read User|null $sender
 * @property-read NotificationTemplate|null $template
 *
 * @method static Builder|NotificationLog channel($channel)
 * @method static Builder|NotificationLog delivered()
 * @method static Builder|NotificationLog failed()
 * @method static Builder|NotificationLog newModelQuery()
 * @method static Builder|NotificationLog newQuery()
 * @method static Builder|NotificationLog onlyTrashed()
 * @method static Builder|NotificationLog pending()
 * @method static Builder|NotificationLog query()
 * @method static Builder|NotificationLog readyToRetry()
 * @method static Builder|NotificationLog sent()
 * @method static Builder|NotificationLog whereApiResponse($value)
 * @method static Builder|NotificationLog whereChannel($value)
 * @method static Builder|NotificationLog whereCreatedAt($value)
 * @method static Builder|NotificationLog whereDeletedAt($value)
 * @method static Builder|NotificationLog whereDeliveredAt($value)
 * @method static Builder|NotificationLog whereErrorMessage($value)
 * @method static Builder|NotificationLog whereId($value)
 * @method static Builder|NotificationLog whereMessageContent($value)
 * @method static Builder|NotificationLog whereNextRetryAt($value)
 * @method static Builder|NotificationLog whereNotifiableId($value)
 * @method static Builder|NotificationLog whereNotifiableType($value)
 * @method static Builder|NotificationLog whereNotificationTypeId($value)
 * @method static Builder|NotificationLog whereReadAt($value)
 * @method static Builder|NotificationLog whereRecipient($value)
 * @method static Builder|NotificationLog whereRetryCount($value)
 * @method static Builder|NotificationLog whereSentAt($value)
 * @method static Builder|NotificationLog whereSentBy($value)
 * @method static Builder|NotificationLog whereStatus($value)
 * @method static Builder|NotificationLog whereSubject($value)
 * @method static Builder|NotificationLog whereTemplateId($value)
 * @method static Builder|NotificationLog whereUpdatedAt($value)
 * @method static Builder|NotificationLog whereVariablesUsed($value)
 * @method static Builder|NotificationLog withTrashed()
 * @method static Builder|NotificationLog withoutTrashed()
 *
 * @mixin Model
 */
class NotificationLog extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'notification_type_id',
        'template_id',
        'channel',
        'recipient',
        'subject',
        'message_content',
        'variables_used',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
        'error_message',
        'api_response',
        'sent_by',
        'retry_count',
        'next_retry_at',
    ];

    protected $casts = [
        'variables_used' => 'array',
        'api_response' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    /**
     * Get the notifiable entity (polymorphic relation)
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Get the notification type
     */
    public function notificationType()
    {
        return $this->belongsTo(NotificationType::class);
    }

    /**
     * Get the template used
     */
    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    /**
     * Get the user who sent this notification
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Get delivery tracking records
     */
    public function deliveryTracking()
    {
        return $this->hasMany(NotificationDeliveryTracking::class);
    }

    /**
     * Scope for pending notifications
     */
    protected function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for sent notifications
     */
    protected function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for failed notifications
     */
    protected function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for delivered notifications
     */
    protected function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope for a specific channel
     */
    protected function scopeChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope for notifications ready to retry
     */
    protected function scopeReadyToRetry($query)
    {
        return $query->where('status', 'failed')
            ->where('retry_count', '<', 3)
            ->where(static function ($q): void {
                $q->whereNull('next_retry_at')
                    ->orWhere('next_retry_at', '<=', now());
            });
    }

    /**
     * Check if notification was successful
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, ['sent', 'delivered', 'read']);
    }

    /**
     * Check if notification failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if notification can be retried
     */
    public function canRetry(): bool
    {
        return $this->isFailed() && $this->retry_count < 3;
    }

    /**
     * Get status badge color for UI
     */
    protected function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'sent' => 'info',
            'delivered' => 'success',
            'read' => 'primary',
            'failed' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get channel icon for UI
     */
    protected function getChannelIconAttribute(): string
    {
        return match ($this->channel) {
            'whatsapp' => 'fab fa-whatsapp',
            'email' => 'fas fa-envelope',
            'sms' => 'fas fa-sms',
            default => 'fas fa-bell',
        };
    }
}
