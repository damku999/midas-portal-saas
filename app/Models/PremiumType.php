<?php

namespace App\Models;

use App\Traits\TableRecordObserver;
use Database\Factories\PremiumTypeFactory;
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
 * App\Models\PremiumType
 *
 * @property int $id
 * @property string|null $name
 * @property int $is_vehicle
 * @property int $is_life_insurance_policies
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
 * @method static PremiumTypeFactory factory($count = null, $state = [])
 * @method static Builder|PremiumType newModelQuery()
 * @method static Builder|PremiumType newQuery()
 * @method static Builder|PremiumType onlyTrashed()
 * @method static Builder|PremiumType permission($permissions)
 * @method static Builder|PremiumType query()
 * @method static Builder|PremiumType role($roles, $guard = null)
 * @method static Builder|PremiumType whereCreatedAt($value)
 * @method static Builder|PremiumType whereCreatedBy($value)
 * @method static Builder|PremiumType whereDeletedAt($value)
 * @method static Builder|PremiumType whereDeletedBy($value)
 * @method static Builder|PremiumType whereId($value)
 * @method static Builder|PremiumType whereIsLifeInsurancePolicies($value)
 * @method static Builder|PremiumType whereIsVehicle($value)
 * @method static Builder|PremiumType whereName($value)
 * @method static Builder|PremiumType whereStatus($value)
 * @method static Builder|PremiumType whereUpdatedAt($value)
 * @method static Builder|PremiumType whereUpdatedBy($value)
 * @method static Builder|PremiumType withTrashed()
 * @method static Builder|PremiumType withoutTrashed()
 *
 * @mixin Model
 */
class PremiumType extends Authenticatable
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
        'is_vehicle',
        'is_life_insurance_policies',
    ];

    public function customerInsurances()
    {
        return $this->hasMany(CustomerInsurance::class, 'premium_type_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
