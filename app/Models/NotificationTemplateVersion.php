<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\NotificationTemplateVersion
 *
 * @property int $id
 * @property int $template_id
 * @property int $version_number
 * @property string $channel
 * @property string|null $subject
 * @property string $template_content
 * @property array|null $available_variables
 * @property bool $is_active
 * @property int|null $changed_by
 * @property string $change_type
 * @property string|null $change_notes
 * @property Carbon $changed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $changer
 * @property-read NotificationTemplate|null $template
 *
 * @method static Builder|NotificationTemplateVersion newModelQuery()
 * @method static Builder|NotificationTemplateVersion newQuery()
 * @method static Builder|NotificationTemplateVersion query()
 * @method static Builder|NotificationTemplateVersion whereAvailableVariables($value)
 * @method static Builder|NotificationTemplateVersion whereChangeNotes($value)
 * @method static Builder|NotificationTemplateVersion whereChangeType($value)
 * @method static Builder|NotificationTemplateVersion whereChangedAt($value)
 * @method static Builder|NotificationTemplateVersion whereChangedBy($value)
 * @method static Builder|NotificationTemplateVersion whereChannel($value)
 * @method static Builder|NotificationTemplateVersion whereCreatedAt($value)
 * @method static Builder|NotificationTemplateVersion whereId($value)
 * @method static Builder|NotificationTemplateVersion whereIsActive($value)
 * @method static Builder|NotificationTemplateVersion whereSubject($value)
 * @method static Builder|NotificationTemplateVersion whereTemplateContent($value)
 * @method static Builder|NotificationTemplateVersion whereTemplateId($value)
 * @method static Builder|NotificationTemplateVersion whereUpdatedAt($value)
 * @method static Builder|NotificationTemplateVersion whereVersionNumber($value)
 *
 * @mixin Model
 */
class NotificationTemplateVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'version_number',
        'channel',
        'subject',
        'template_content',
        'available_variables',
        'is_active',
        'changed_by',
        'change_type',
        'change_notes',
        'changed_at',
    ];

    protected $casts = [
        'available_variables' => 'array',
        'is_active' => 'boolean',
        'changed_at' => 'datetime',
    ];

    /**
     * Get the template this version belongs to
     */
    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    /**
     * Get the user who made this change
     */
    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
