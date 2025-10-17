<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\NotificationTemplateTestLog
 *
 * @property int $id
 * @property int|null $template_id
 * @property string $channel
 * @property string $recipient
 * @property string|null $subject
 * @property string $message_content
 * @property string $status
 * @property string|null $error_message
 * @property array|null $response_data
 * @property int|null $sent_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $sender
 * @property-read NotificationTemplate|null $template
 *
 * @method static Builder|NotificationTemplateTestLog newModelQuery()
 * @method static Builder|NotificationTemplateTestLog newQuery()
 * @method static Builder|NotificationTemplateTestLog query()
 * @method static Builder|NotificationTemplateTestLog whereChannel($value)
 * @method static Builder|NotificationTemplateTestLog whereCreatedAt($value)
 * @method static Builder|NotificationTemplateTestLog whereErrorMessage($value)
 * @method static Builder|NotificationTemplateTestLog whereId($value)
 * @method static Builder|NotificationTemplateTestLog whereMessageContent($value)
 * @method static Builder|NotificationTemplateTestLog whereRecipient($value)
 * @method static Builder|NotificationTemplateTestLog whereResponseData($value)
 * @method static Builder|NotificationTemplateTestLog whereSentBy($value)
 * @method static Builder|NotificationTemplateTestLog whereStatus($value)
 * @method static Builder|NotificationTemplateTestLog whereSubject($value)
 * @method static Builder|NotificationTemplateTestLog whereTemplateId($value)
 * @method static Builder|NotificationTemplateTestLog whereUpdatedAt($value)
 *
 * @mixin Model
 */
class NotificationTemplateTestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'channel',
        'recipient',
        'subject',
        'message_content',
        'status',
        'error_message',
        'response_data',
        'sent_by',
    ];

    protected $casts = [
        'response_data' => 'array',
    ];

    /**
     * Get the template this test log belongs to
     */
    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    /**
     * Get the user who sent this test
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
