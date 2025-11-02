<?php

namespace App\Models;

use App\Traits\TableRecordObserver;
use Database\Factories\ReferenceUserFactory;
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
 * App\Models\ReferenceUser
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $mobile_number
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Collection<int, CustomerInsurance> $customerInsurances
 * @property-read int|null $customer_insurances_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 *
 * @method static ReferenceUserFactory factory($count = null, $state = [])
 * @method static Builder|ReferenceUser newModelQuery()
 * @method static Builder|ReferenceUser newQuery()
 * @method static Builder|ReferenceUser onlyTrashed()
 * @method static Builder|ReferenceUser permission($permissions)
 * @method static Builder|ReferenceUser query()
 * @method static Builder|ReferenceUser role($roles, $guard = null)
 * @method static Builder|ReferenceUser whereCreatedAt($value)
 * @method static Builder|ReferenceUser whereCreatedBy($value)
 * @method static Builder|ReferenceUser whereDeletedAt($value)
 * @method static Builder|ReferenceUser whereDeletedBy($value)
 * @method static Builder|ReferenceUser whereEmail($value)
 * @method static Builder|ReferenceUser whereId($value)
 * @method static Builder|ReferenceUser whereMobileNumber($value)
 * @method static Builder|ReferenceUser whereName($value)
 * @method static Builder|ReferenceUser whereStatus($value)
 * @method static Builder|ReferenceUser whereUpdatedAt($value)
 * @method static Builder|ReferenceUser whereUpdatedBy($value)
 * @method static Builder|ReferenceUser withTrashed()
 * @method static Builder|ReferenceUser withoutTrashed()
 *
 * @mixin Model
 */
class ReferenceUser extends Authenticatable
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
        'email',
        'mobile_number',
        'status',
    ];

    public function customerInsurances()
    {
        return $this->hasMany(CustomerInsurance::class, 'reference_user_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
