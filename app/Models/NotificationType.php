<?php

namespace App\Models;

use Database\Factories\NotificationTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
/**
 * App\Models\NotificationType
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $category
 * @property string|null $description
 * @property bool $default_whatsapp_enabled
 * @property bool $default_email_enabled
 * @property bool $is_active
 * @property int $order_no
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, NotificationTemplate> $templates
 * @property-read int|null $templates_count
 *
 * @method static NotificationTypeFactory factory($count = null, $state = [])
 * @method static Builder|NotificationType newModelQuery()
 * @method static Builder|NotificationType newQuery()
 * @method static Builder|NotificationType onlyTrashed()
 * @method static Builder|NotificationType query()
 * @method static Builder|NotificationType whereCategory($value)
 * @method static Builder|NotificationType whereCode($value)
 * @method static Builder|NotificationType whereCreatedAt($value)
 * @method static Builder|NotificationType whereDefaultEmailEnabled($value)
 * @method static Builder|NotificationType whereDefaultWhatsappEnabled($value)
 * @method static Builder|NotificationType whereDeletedAt($value)
 * @method static Builder|NotificationType whereDescription($value)
 * @method static Builder|NotificationType whereId($value)
 * @method static Builder|NotificationType whereIsActive($value)
 * @method static Builder|NotificationType whereName($value)
 * @method static Builder|NotificationType whereOrderNo($value)
 * @method static Builder|NotificationType whereUpdatedAt($value)
 * @method static Builder|NotificationType withTrashed()
 * @method static Builder|NotificationType withoutTrashed()
 *
 * @mixin Model
 */
class NotificationType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'category',
        'description',
        'default_whatsapp_enabled',
        'default_email_enabled',
        'is_active',
        'order_no',
    ];

    protected $casts = [
        'default_whatsapp_enabled' => 'boolean',
        'default_email_enabled' => 'boolean',
        'is_active' => 'boolean',
        'order_no' => 'integer',
    ];

    /**
     * Get templates for this notification type
     *
     * @return HasMany
     */
    public function templates()
    {
        return $this->hasMany(NotificationTemplate::class);
    }
}
