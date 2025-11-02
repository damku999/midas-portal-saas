<?php

namespace App\Models;

use App\Traits\TableRecordObserver;
use Database\Factories\FuelTypeFactory;
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
/**
 * App\Models\FuelType
 *
 * @property int $id
 * @property string|null $name
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
 * @method static FuelTypeFactory factory($count = null, $state = [])
 * @method static Builder|FuelType newModelQuery()
 * @method static Builder|FuelType newQuery()
 * @method static Builder|FuelType onlyTrashed()
 * @method static Builder|FuelType permission($permissions)
 * @method static Builder|FuelType query()
 * @method static Builder|FuelType role($roles, $guard = null)
 * @method static Builder|FuelType whereCreatedAt($value)
 * @method static Builder|FuelType whereCreatedBy($value)
 * @method static Builder|FuelType whereDeletedAt($value)
 * @method static Builder|FuelType whereDeletedBy($value)
 * @method static Builder|FuelType whereId($value)
 * @method static Builder|FuelType whereName($value)
 * @method static Builder|FuelType whereStatus($value)
 * @method static Builder|FuelType whereUpdatedAt($value)
 * @method static Builder|FuelType whereUpdatedBy($value)
 * @method static Builder|FuelType withTrashed()
 * @method static Builder|FuelType withoutTrashed()
 *
 * @mixin Model
 */
class FuelType extends Authenticatable
{
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
    ];

    public function customerInsurances()
    {
        return $this->hasMany(CustomerInsurance::class, 'fuel_type_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
