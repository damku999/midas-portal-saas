<?php

namespace App\Models;

use Database\Factories\NotificationTemplateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\NotificationTemplate
 *
 * @property int $id
 * @property int $notification_type_id
 * @property string $channel
 * @property string|null $subject
 * @property string $template_content
 * @property array|null $available_variables
 * @property string|null $sample_output
 * @property bool $is_active
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $category
 * @property-read string $name
 * @property-read string $type
 * @property-read NotificationType|null $notificationType
 * @property-read Collection<int, NotificationTemplateTestLog> $testLogs
 * @property-read int|null $test_logs_count
 * @property-read User|null $updater
 * @property-read Collection<int, NotificationTemplateVersion> $versions
 * @property-read int|null $versions_count
 *
 * @method static NotificationTemplateFactory factory($count = null, $state = [])
 * @method static Builder|NotificationTemplate newModelQuery()
 * @method static Builder|NotificationTemplate newQuery()
 * @method static Builder|NotificationTemplate query()
 * @method static Builder|NotificationTemplate whereAvailableVariables($value)
 * @method static Builder|NotificationTemplate whereChannel($value)
 * @method static Builder|NotificationTemplate whereCreatedAt($value)
 * @method static Builder|NotificationTemplate whereId($value)
 * @method static Builder|NotificationTemplate whereIsActive($value)
 * @method static Builder|NotificationTemplate whereNotificationTypeId($value)
 * @method static Builder|NotificationTemplate whereSampleOutput($value)
 * @method static Builder|NotificationTemplate whereSubject($value)
 * @method static Builder|NotificationTemplate whereTemplateContent($value)
 * @method static Builder|NotificationTemplate whereUpdatedAt($value)
 * @method static Builder|NotificationTemplate whereUpdatedBy($value)
 *
 * @mixin Model
 */
class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_type_id',
        'channel',
        'subject',
        'template_content',
        'available_variables',
        'sample_output',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'available_variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the notification type for this template
     *
     * @return BelongsTo
     */
    public function notificationType()
    {
        return $this->belongsTo(NotificationType::class);
    }

    /**
     * Render template with provided data
     */
    public function render(array $data): string
    {
        $content = $this->template_content;

        foreach ($data as $key => $value) {
            $content = str_replace('{'.$key.'}', $value, $content);
        }

        return $content;
    }

    /**
     * Get updater user
     *
     * @return BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get version history for this template
     *
     * @return HasMany
     */
    public function versions()
    {
        return $this->hasMany(NotificationTemplateVersion::class, 'template_id');
    }

    /**
     * Get test logs for this template
     *
     * @return HasMany
     */
    public function testLogs()
    {
        return $this->hasMany(NotificationTemplateTestLog::class, 'template_id');
    }

    /**
     * Get display name from notification type
     */
    protected function getNameAttribute(): string
    {
        return $this->notificationType->name ?? 'Unknown';
    }

    /**
     * Get type code from notification type
     */
    protected function getTypeAttribute(): string
    {
        return $this->notificationType->code ?? 'unknown';
    }

    /**
     * Get category from notification type
     */
    protected function getCategoryAttribute(): string
    {
        return $this->notificationType->category ?? 'unknown';
    }
}
