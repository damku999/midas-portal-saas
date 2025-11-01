<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasSecuritySettings;
use App\Traits\HasTwoFactorAuth;
use App\Traits\ProtectedRecord;
use App\Traits\TableRecordObserver;
use Database\Factories\UserFactory;
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
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $mobile_number
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property int $role_id 1=Admin, 2=TA/TP
 * @property int $status
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Collection<int, AuditLog> $auditLogs
 * @property-read int|null $audit_logs_count
 * @property-read string $full_name
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Report> $reports
 * @property-read int|null $reports_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read SecuritySetting|null $securitySettings
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read Collection<int, TrustedDevice> $trustedDevices
 * @property-read int|null $trusted_devices_count
 * @property-read Collection<int, TwoFactorAttempt> $twoFactorAttempts
 * @property-read int|null $two_factor_attempts_count
 * @property-read TwoFactorAuth|null $twoFactorAuth
 *
 * @method static UserFactory factory($count = null, $state = [])
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User onlyTrashed()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereCreatedBy($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereDeletedBy($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User whereMobileNumber($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereRoleId($value)
 * @method static Builder|User whereStatus($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUpdatedBy($value)
 * @method static Builder|User withTrashed()
 * @method static Builder|User withoutTrashed()
 *
 * @mixin Model
 */
class User extends Authenticatable
{
    use Auditable;
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use HasSecuritySettings;
    use HasTwoFactorAuth;
    use LogsActivity;
    use Notifiable;
    use ProtectedRecord;
    use SoftDeletes;
    use TableRecordObserver;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile_number',
        'role_id',
        'status',
        'password',
        'is_protected',
        'protected_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_protected' => 'boolean',
    ];

    /**
     * Get the user's full name.
     */
    protected function getFullNameAttribute(): string
    {
        return sprintf('%s %s', $this->first_name, $this->last_name);
    }

    protected static $logName = 'User profile';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Check if provided password matches current password
     */
    public function checkPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }
}
