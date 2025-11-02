<?php

namespace App\Models;

use App\Traits\TableRecordObserver;
use Database\Factories\AddonCoverFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * App\Models\AddonCover
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $order_no
 * @property bool $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 *
 * @method static AddonCoverFactory factory($count = null, $state = [])
 * @method static Builder|AddonCover newModelQuery()
 * @method static Builder|AddonCover newQuery()
 * @method static Builder|AddonCover onlyTrashed()
 * @method static Builder|AddonCover permission($permissions)
 * @method static Builder|AddonCover query()
 * @method static Builder|AddonCover role($roles, $guard = null)
 * @method static Builder|AddonCover whereCreatedAt($value)
 * @method static Builder|AddonCover whereCreatedBy($value)
 * @method static Builder|AddonCover whereDeletedAt($value)
 * @method static Builder|AddonCover whereDeletedBy($value)
 * @method static Builder|AddonCover whereDescription($value)
 * @method static Builder|AddonCover whereId($value)
 * @method static Builder|AddonCover whereName($value)
 * @method static Builder|AddonCover whereOrderNo($value)
 * @method static Builder|AddonCover whereStatus($value)
 * @method static Builder|AddonCover whereUpdatedAt($value)
 * @method static Builder|AddonCover whereUpdatedBy($value)
 * @method static Builder|AddonCover withTrashed()
 * @method static Builder|AddonCover withoutTrashed()
 *
 * @mixin Model
 */
class AddonCover extends Authenticatable
{
    use BelongsToTenant;
    use HasFactory;
    use HasRoles;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;
    use TableRecordObserver;

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'order_no',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'order_no' => 'integer',
        'status' => 'boolean',
    ];

    /**
     * Handle smart ordering when saving
     */
    protected static function booted()
    {
        static::saving(static function ($addonCover): void {
            self::handleSmartOrdering($addonCover);
        });
    }

    /**
     * Smart ordering system with auto-assignment and conflict resolution
     */
    private static function handleSmartOrdering($addonCover): void
    {
        // If order_no is 0, auto-assign next available number
        if ($addonCover->order_no == 0) {
            $addonCover->order_no = self::getNextAvailableOrder();

            return;
        }

        // For updates, get the original order number
        $originalOrderNo = null;
        if ($addonCover->exists) {
            $originalOrderNo = $addonCover->getOriginal('order_no');
        }

        // If this is an update and order didn't change, no need to process
        if ($originalOrderNo !== null && $originalOrderNo == $addonCover->order_no) {
            return;
        }

        // Check for duplicate order numbers (excluding this record)
        $existingCover = static::query()->where('order_no', $addonCover->order_no)
            ->where('id', '!=', $addonCover->id ?? 0)
            ->first();

        if ($existingCover) {
            // If updating: shift others and move this record to desired position
            if ($originalOrderNo !== null) {
                // First, close the gap from the original position
                static::query()->where('order_no', '>', $originalOrderNo)
                    ->where('id', '!=', $addonCover->id)
                    ->decrement('order_no');
            }

            // Then shift all covers at or after the new position
            static::query()->where('order_no', '>=', $addonCover->order_no)
                ->where('id', '!=', $addonCover->id ?? 0)
                ->increment('order_no');
        } elseif ($originalOrderNo !== null && $originalOrderNo < $addonCover->order_no) {
            // Moving to a higher position: close the gap from original position
            static::query()->where('order_no', '>', $originalOrderNo)
                ->where('order_no', '<=', $addonCover->order_no)
                ->where('id', '!=', $addonCover->id)
                ->decrement('order_no');
        } elseif ($originalOrderNo !== null && $originalOrderNo > $addonCover->order_no) {
            // Moving to a lower position: shift others up
            static::query()->where('order_no', '>=', $addonCover->order_no)
                ->where('order_no', '<', $originalOrderNo)
                ->where('id', '!=', $addonCover->id)
                ->increment('order_no');
        }
    }

    /**
     * Get next available order number after the last order
     */
    private static function getNextAvailableOrder(): int|float
    {
        $lastOrder = static::query()->max('order_no') ?? 0;

        return $lastOrder + 1;
    }

    /**
     * Get ordered addon covers for display
     */
    public static function getOrdered($status = 1)
    {
        return static::query()->where('status', $status)
            ->orderBy('order_no', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
