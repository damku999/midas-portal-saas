<?php

namespace App\Models;

use App\Traits\TableRecordObserver;
use Database\Factories\InsuranceCompanyFactory;
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
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * App\Models\InsuranceCompany
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
 * @method static InsuranceCompanyFactory factory($count = null, $state = [])
 * @method static Builder|InsuranceCompany newModelQuery()
 * @method static Builder|InsuranceCompany newQuery()
 * @method static Builder|InsuranceCompany onlyTrashed()
 * @method static Builder|InsuranceCompany permission($permissions)
 * @method static Builder|InsuranceCompany query()
 * @method static Builder|InsuranceCompany role($roles, $guard = null)
 * @method static Builder|InsuranceCompany whereCreatedAt($value)
 * @method static Builder|InsuranceCompany whereCreatedBy($value)
 * @method static Builder|InsuranceCompany whereDeletedAt($value)
 * @method static Builder|InsuranceCompany whereDeletedBy($value)
 * @method static Builder|InsuranceCompany whereEmail($value)
 * @method static Builder|InsuranceCompany whereId($value)
 * @method static Builder|InsuranceCompany whereMobileNumber($value)
 * @method static Builder|InsuranceCompany whereName($value)
 * @method static Builder|InsuranceCompany whereStatus($value)
 * @method static Builder|InsuranceCompany whereUpdatedAt($value)
 * @method static Builder|InsuranceCompany whereUpdatedBy($value)
 * @method static Builder|InsuranceCompany withTrashed()
 * @method static Builder|InsuranceCompany withoutTrashed()
 *
 * @mixin Model
 */
class InsuranceCompany extends Authenticatable
{
    use BelongsToTenant;
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
        return $this->hasMany(CustomerInsurance::class, 'insurance_company_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
