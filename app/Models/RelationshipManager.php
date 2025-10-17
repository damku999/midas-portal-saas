<?php

namespace App\Models;

use App\Traits\TableRecordObserver;
use Database\Factories\RelationshipManagerFactory;
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
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\RelationshipManager
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
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 *
 * @method static RelationshipManagerFactory factory($count = null, $state = [])
 * @method static Builder|RelationshipManager newModelQuery()
 * @method static Builder|RelationshipManager newQuery()
 * @method static Builder|RelationshipManager onlyTrashed()
 * @method static Builder|RelationshipManager permission($permissions)
 * @method static Builder|RelationshipManager query()
 * @method static Builder|RelationshipManager role($roles, $guard = null)
 * @method static Builder|RelationshipManager whereCreatedAt($value)
 * @method static Builder|RelationshipManager whereCreatedBy($value)
 * @method static Builder|RelationshipManager whereDeletedAt($value)
 * @method static Builder|RelationshipManager whereDeletedBy($value)
 * @method static Builder|RelationshipManager whereEmail($value)
 * @method static Builder|RelationshipManager whereId($value)
 * @method static Builder|RelationshipManager whereMobileNumber($value)
 * @method static Builder|RelationshipManager whereName($value)
 * @method static Builder|RelationshipManager whereStatus($value)
 * @method static Builder|RelationshipManager whereUpdatedAt($value)
 * @method static Builder|RelationshipManager whereUpdatedBy($value)
 * @method static Builder|RelationshipManager withTrashed()
 * @method static Builder|RelationshipManager withoutTrashed()
 *
 * @mixin Model
 */
class RelationshipManager extends Authenticatable
{
    use HasApiTokens;
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
        return $this->hasMany(CustomerInsurance::class, 'relationship_manager_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
