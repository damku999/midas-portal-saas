<?php

namespace App\Models;

use App\Models\Customer\CustomerSecuritySettings;
use App\Models\Customer\CustomerTrustedDevice;
use App\Models\Customer\CustomerTwoFactorAuth;
use App\Traits\Auditable;
use App\Traits\Customer\HasCustomerTwoFactorAuth;
use App\Traits\ProtectedRecord;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
/**
 * App\Models\Customer
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $mobile_number
 * @property Carbon|null $date_of_birth
 * @property Carbon|null $wedding_anniversary_date
 * @property Carbon|null $engagement_anniversary_date
 * @property string|null $type
 * @property bool|null $status
 * @property array|null $notification_preferences
 * @property string|null $pan_card_number
 * @property string|null $aadhar_card_number
 * @property string|null $gst_number
 * @property-read string|null $pan_card_path
 * @property-read string|null $aadhar_card_path
 * @property-read string|null $gst_path
 * @property int|null $family_group_id Family group this customer belongs to
 * @property string|null $password Password for customer login
 * @property Carbon|null $password_changed_at
 * @property bool $must_change_password
 * @property Carbon|null $email_verified_at Email verification timestamp
 * @property string|null $email_verification_token
 * @property Carbon|null $password_reset_sent_at
 * @property string|null $password_reset_token
 * @property Carbon|null $password_reset_expires_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property-read Collection<int, CustomerDevice> $activeDevices
 * @property-read int|null $active_devices_count
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Collection<int, CustomerAuditLog> $auditLogs
 * @property-read int|null $audit_logs_count
 * @property-read Collection<int, Claim> $claims
 * @property-read int|null $claims_count
 * @property-read CustomerSecuritySettings|null $customerSecuritySettings
 * @property-read Collection<int, CustomerTrustedDevice> $customerTrustedDevices
 * @property-read int|null $customer_trusted_devices_count
 * @property-read Collection<int, TwoFactorAttempt> $customerTwoFactorAttempts
 * @property-read int|null $customer_two_factor_attempts_count
 * @property-read CustomerTwoFactorAuth|null $customerTwoFactorAuth
 * @property-read CustomerType|null $customerType
 * @property-read Collection<int, CustomerDevice> $devices
 * @property-read int|null $devices_count
 * @property-read FamilyGroup|null $familyGroup
 * @property-read FamilyMember|null $familyMember
 * @property-read Collection<int, FamilyMember> $familyMembers
 * @property-read int|null $family_members_count
 * @property-read mixed $date_of_birth_formatted
 * @property-read mixed $engagement_anniversary_date_formatted
 * @property-read mixed $wedding_anniversary_date_formatted
 * @property-read Collection<int, CustomerInsurance> $insurance
 * @property-read int|null $insurance_count
 * @property-read Collection<int, NotificationLog> $notificationLogs
 * @property-read int|null $notification_logs_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Quotation> $quotations
 * @property-read int|null $quotations_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 *
 * @method static CustomerFactory factory($count = null, $state = [])
 * @method static Builder|Customer newModelQuery()
 * @method static Builder|Customer newQuery()
 * @method static Builder|Customer onlyTrashed()
 * @method static Builder|Customer permission($permissions)
 * @method static Builder|Customer query()
 * @method static Builder|Customer role($roles, $guard = null)
 * @method static Builder|Customer whereAadharCardNumber($value)
 * @method static Builder|Customer whereAadharCardPath($value)
 * @method static Builder|Customer whereCreatedAt($value)
 * @method static Builder|Customer whereCreatedBy($value)
 * @method static Builder|Customer whereDateOfBirth($value)
 * @method static Builder|Customer whereDeletedAt($value)
 * @method static Builder|Customer whereDeletedBy($value)
 * @method static Builder|Customer whereEmail($value)
 * @method static Builder|Customer whereEmailVerificationToken($value)
 * @method static Builder|Customer whereEmailVerifiedAt($value)
 * @method static Builder|Customer whereEngagementAnniversaryDate($value)
 * @method static Builder|Customer whereFamilyGroupId($value)
 * @method static Builder|Customer whereGstNumber($value)
 * @method static Builder|Customer whereGstPath($value)
 * @method static Builder|Customer whereId($value)
 * @method static Builder|Customer whereMobileNumber($value)
 * @method static Builder|Customer whereMustChangePassword($value)
 * @method static Builder|Customer whereName($value)
 * @method static Builder|Customer whereNotificationPreferences($value)
 * @method static Builder|Customer wherePanCardNumber($value)
 * @method static Builder|Customer wherePanCardPath($value)
 * @method static Builder|Customer wherePassword($value)
 * @method static Builder|Customer wherePasswordChangedAt($value)
 * @method static Builder|Customer wherePasswordResetExpiresAt($value)
 * @method static Builder|Customer wherePasswordResetSentAt($value)
 * @method static Builder|Customer wherePasswordResetToken($value)
 * @method static Builder|Customer whereRememberToken($value)
 * @method static Builder|Customer whereStatus($value)
 * @method static Builder|Customer whereType($value)
 * @method static Builder|Customer whereUpdatedAt($value)
 * @method static Builder|Customer whereUpdatedBy($value)
 * @method static Builder|Customer whereWeddingAnniversaryDate($value)
 * @method static Builder|Customer withTrashed()
 * @method static Builder|Customer withoutTrashed()
 *
 * @mixin Model
 */
class Customer extends Authenticatable
{
    use Auditable;
    use HasApiTokens;
    use HasCustomerTwoFactorAuth;
    use HasFactory;
    use HasRoles;
    use LogsActivity;
    use Notifiable;
    use ProtectedRecord;
    use SoftDeletes;

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
        'wedding_anniversary_date',
        'date_of_birth',
        'engagement_anniversary_date',
        'pan_card_number',
        'aadhar_card_number',
        'gst_number',
        'pan_card_path',
        'aadhar_card_path',
        'gst_path',
        'type',
        'family_group_id',
        'password',
        'email_verified_at',
        'password_changed_at',
        'must_change_password',
        'email_verification_token',
        'password_reset_sent_at',
        'password_reset_token',
        'password_reset_expires_at',
        'notification_preferences',
        'is_protected',
        'protected_reason',
        'converted_from_lead_id',
        'converted_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'date_of_birth' => 'date',
        'wedding_anniversary_date' => 'date',
        'engagement_anniversary_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'must_change_password' => 'boolean',
        'password_reset_sent_at' => 'datetime',
        'password_reset_expires_at' => 'datetime',
        'notification_preferences' => 'array',
        'is_protected' => 'boolean',
        'converted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Override the boot method to handle customer guard authentication
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(static function ($model): void {
            // Check both customer and web guards for created_by
            if (Auth::guard('customer')->check()) {
                $model->created_by = Auth::guard('customer')->id();
                $model->updated_by = Auth::guard('customer')->id();
            } elseif (Auth::guard('web')->check()) {
                $model->created_by = Auth::guard('web')->id();
                $model->updated_by = Auth::guard('web')->id();
            } else {
                $model->created_by = 0;
                $model->updated_by = 0;
            }
        });

        static::updating(static function ($model): void {
            // Check both customer and web guards for updated_by
            if (Auth::guard('customer')->check()) {
                $model->updated_by = Auth::guard('customer')->id();
            } elseif (Auth::guard('web')->check()) {
                $model->updated_by = Auth::guard('web')->id();
            } else {
                $model->updated_by = 0;
            }
        });

        static::deleting(static function ($model): void {
            // Check both customer and web guards for deleted_by
            if (Auth::guard('customer')->check()) {
                $model->deleted_by = Auth::guard('customer')->id();
            } elseif (Auth::guard('web')->check()) {
                $model->deleted_by = Auth::guard('web')->id();
            } else {
                $model->deleted_by = 0;
            }

            $model->save();
        });
    }

    /**
     * Get the insurance for the customer.
     */
    public function insurance(): HasMany
    {
        return $this->hasMany(CustomerInsurance::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Get all devices for push notifications
     */
    public function devices(): HasMany
    {
        return $this->hasMany(CustomerDevice::class);
    }

    /**
     * Get active devices for push notifications
     */
    public function activeDevices(): HasMany
    {
        return $this->hasMany(CustomerDevice::class)->where('is_active', true);
    }

    /**
     * Get notification logs for this customer
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * Get all claims for the customer.
     */
    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    /**
     * Get the family group this customer belongs to.
     */
    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    /**
     * Get the customer type (Corporate/Retail) for this customer.
     */
    public function customerType(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class);
    }

    /**
     * Get the family member record for this customer.
     */
    public function familyMember(): HasOne
    {
        return $this->hasOne(FamilyMember::class);
    }

    /**
     * Get all family members if this customer is part of a family.
     */
    public function familyMembers(): HasMany
    {
        return $this->hasMany(FamilyMember::class, 'family_group_id', 'family_group_id');
    }

    /**
     * Get the original lead this customer was converted from.
     */
    public function originalLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'converted_from_lead_id');
    }

    /**
     * Get all family insurance policies if this customer is part of a family.
     */
    public function familyInsurance(): HasMany
    {
        // SECURITY FIX: Validate family_group_id to prevent SQL injection
        $familyGroupId = $this->validateFamilyGroupId($this->family_group_id);

        return $this->hasMany(CustomerInsurance::class, 'customer_id')
            ->whereHas('customer', static function ($query) use ($familyGroupId): void {
                $query->where('family_group_id', '=', $familyGroupId);
            });
    }

    /**
     * Check if customer is part of a family group.
     */
    public function hasFamily(): bool
    {
        return ! is_null($this->family_group_id);
    }

    /**
     * Check if customer is the family head.
     */
    public function isFamilyHead(): bool
    {
        if (! $this->hasFamily()) {
            return false;
        }

        return $this->familyMember?->is_head === true;
    }

    /**
     * Get all insurance policies this customer can view (own + family if head).
     */
    public function getViewableInsurance()
    {
        if ($this->isFamilyHead()) {
            // SECURITY FIX: Validate family_group_id to prevent SQL injection
            $familyGroupId = $this->validateFamilyGroupId($this->family_group_id);

            // Family head can view all family insurance
            return CustomerInsurance::query()->whereHas('customer', static function ($query) use ($familyGroupId): void {
                $query->where('family_group_id', '=', $familyGroupId);
            })->with(['customer', 'insuranceCompany', 'policyType', 'premiumType']);
        }

        // Regular members can only view their own insurance
        return $this->insurance()->with(['insuranceCompany', 'policyType', 'premiumType']);
    }

    /**
     * Check if this customer is in the same family as another customer.
     */
    public function isInSameFamilyAs(Customer $customer): bool
    {
        return $this->hasFamily() &&
               $customer->hasFamily() &&
               $this->family_group_id === $customer->family_group_id;
    }

    /**
     * Get privacy-safe customer data for family viewing.
     */
    public function getPrivacySafeData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->maskEmail($this->email),
            'mobile_number' => $this->maskMobile($this->mobile_number),
            'date_of_birth' => $this->date_of_birth?->format('M d'), // Hide year for privacy
            'status' => $this->status,
            'created_at' => $this->created_at->format('M Y'),
            'relationship' => $this->familyMember?->relationship,
        ];
    }

    /**
     * Mask email for privacy (show first 2 chars and domain).
     */
    protected function maskEmail(?string $email): ?string
    {
        if (in_array($email, [null, '', '0'], true)) {
            return null;
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $username = $parts[0];
        $domain = $parts[1];

        if (strlen($username) <= 2) {
            return $username.'@'.$domain;
        }

        return substr($username, 0, 2).str_repeat('*', strlen($username) - 2).'@'.$domain;
    }

    /**
     * Mask mobile number for privacy.
     */
    protected function maskMobile(?string $mobile): ?string
    {
        if (in_array($mobile, [null, '', '0'], true) || strlen($mobile) < 4) {
            return $mobile;
        }

        return substr($mobile, 0, 2).str_repeat('*', strlen($mobile) - 4).substr($mobile, -2);
    }

    /**
     * Check if customer can view sensitive data of another customer.
     */
    public function canViewSensitiveDataOf(Customer $customer): bool
    {
        // Can always view own data
        if ($this->id === $customer->id) {
            return true;
        }

        // Family head can view family members' data
        return $this->isFamilyHead() && $this->isInSameFamilyAs($customer);
    }

    /**
     * Get audit log for this customer.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(CustomerAuditLog::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function isActive(): bool
    {
        return $this->status === true;
    }

    public function isRetailCustomer(): bool
    {
        return $this->type === 'Retail';
    }

    public function isCorporateCustomer(): bool
    {
        return $this->type === 'Corporate';
    }

    protected function panCardPath(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value !== null && $value !== '' && $value !== '0' ? asset('storage/'.$value) : null,
        );
    }

    protected function aadharCardPath(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value !== null && $value !== '' && $value !== '0' ? asset('storage/'.$value) : null,
        );
    }

    protected function gstPath(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value !== null && $value !== '' && $value !== '0' ? asset('storage/'.$value) : null,
        );
    }

    /**
     * Generate a random password for the customer.
     */
    public static function generateDefaultPassword(): string
    {
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    }

    /**
     * Set a password and mark it for required change.
     */
    public function setDefaultPassword(?string $password = null): string
    {
        $plainPassword = $password ?? self::generateDefaultPassword();

        $this->update([
            'password' => Hash::make($plainPassword),
            'must_change_password' => true,
            'password_changed_at' => null,
            'email_verified_at' => null,
            'email_verification_token' => Str::random(60),
        ]);

        return $plainPassword;
    }

    /**
     * Set a custom password with admin control over password change requirement.
     */
    public function setCustomPassword(string $plainPassword, bool $forceChange = true): string
    {
        $this->update([
            'password' => Hash::make($plainPassword),
            'must_change_password' => $forceChange,
            'password_changed_at' => $forceChange ? null : now(),
            'email_verified_at' => null,
            'email_verification_token' => Str::random(60),
        ]);

        return $plainPassword;
    }

    /**
     * Change password and mark as user-changed.
     */
    public function changePassword(string $newPassword): void
    {
        $this->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => false,
            'password_changed_at' => now(),
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);
    }

    /**
     * Check if customer needs to change password.
     */
    public function needsPasswordChange(): bool
    {
        return (bool) $this->must_change_password;
    }

    /**
     * Check if customer's email is verified.
     */
    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * Generate email verification token.
     */
    public function generateEmailVerificationToken(): string
    {
        $token = Str::random(60);
        $this->update(['email_verification_token' => $token]);

        return $token;
    }

    /**
     * Verify email with token.
     */
    public function verifyEmail(string $token): bool
    {
        if ($this->email_verification_token === $token) {
            $this->update([
                'email_verified_at' => now(),
                'email_verification_token' => null,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Generate secure password reset token with expiration.
     */
    public function generatePasswordResetToken(): string
    {
        // Generate cryptographically secure token with higher entropy
        $token = bin2hex(random_bytes(32)); // 64 character hex string

        // Set expiration to 1 hour from now
        $expiresAt = now()->addHour();

        $this->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => $expiresAt,
            'password_reset_sent_at' => now(),
        ]);

        return $token;
    }

    /**
     * Verify password reset token and check expiration.
     */
    public function verifyPasswordResetToken(string $token): bool
    {
        if (! $this->password_reset_token || ! $this->password_reset_expires_at) {
            return false;
        }

        // Check if token matches
        if (! hash_equals($this->password_reset_token, $token)) {
            return false;
        }

        // Check if token has expired
        if (now()->isAfter($this->password_reset_expires_at)) {
            // Clear expired token
            $this->update([
                'password_reset_token' => null,
                'password_reset_expires_at' => null,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Clear password reset token after successful reset.
     */
    public function clearPasswordResetToken(): void
    {
        $this->update([
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);
    }

    /**
     * Mask PAN number for customer portal display (show first 3 and last 1 characters).
     * Example: CFDPB1228P -> CFD*****8P
     */
    public function getMaskedPanNumber(): ?string
    {
        if (! $this->pan_card_number) {
            return null;
        }

        $pan = $this->pan_card_number;
        $length = strlen($pan);

        if ($length < 4) {
            return str_repeat('*', $length);
        }

        // Show first 3 characters + stars + last 1 character
        return substr($pan, 0, 3).str_repeat('*', $length - 4).substr($pan, -1);
    }

    /**
     * Validate and sanitize family group ID to prevent SQL injection.
     */
    protected function validateFamilyGroupId($familyGroupId): int
    {
        // Check if family group ID is null
        if (is_null($familyGroupId)) {
            throw new \InvalidArgumentException('Family group ID cannot be null for family operations');
        }

        // Ensure it's an integer to prevent SQL injection
        if (! is_numeric($familyGroupId)) {
            throw new \InvalidArgumentException('Family group ID must be numeric');
        }

        $familyGroupId = (int) $familyGroupId;

        // Validate that it's a positive integer
        if ($familyGroupId <= 0) {
            throw new \InvalidArgumentException('Family group ID must be a positive integer');
        }

        // Additional security: Verify the family group actually exists and is active
        $familyGroupExists = DB::table('family_groups')
            ->where('id', '=', $familyGroupId)
            ->where('status', '=', true)
            ->exists();

        if (! $familyGroupExists) {
            throw new \InvalidArgumentException('Invalid or inactive family group ID');
        }

        return $familyGroupId;
    }

    // =======================================================
    // DATE FORMATTING ACCESSORS & MUTATORS
    // =======================================================

    /**
     * Get date of birth in UI format (d/m/Y)
     */
    protected function getDateOfBirthFormattedAttribute()
    {
        return formatDateForUi($this->date_of_birth);
    }

    /**
     * Set date of birth from UI format (d/m/Y) to database format (Y-m-d)
     */
    protected function setDateOfBirthAttribute($value)
    {
        $this->attributes['date_of_birth'] = formatDateForDatabase($value);
    }

    /**
     * Get wedding anniversary date in UI format (d/m/Y)
     */
    protected function getWeddingAnniversaryDateFormattedAttribute()
    {
        return formatDateForUi($this->wedding_anniversary_date);
    }

    /**
     * Set wedding anniversary date from UI format (d/m/Y) to database format (Y-m-d)
     */
    protected function setWeddingAnniversaryDateAttribute($value)
    {
        $this->attributes['wedding_anniversary_date'] = formatDateForDatabase($value);
    }

    /**
     * Get engagement anniversary date in UI format (d/m/Y)
     */
    protected function getEngagementAnniversaryDateFormattedAttribute()
    {
        return formatDateForUi($this->engagement_anniversary_date);
    }

    /**
     * Set engagement anniversary date from UI format (d/m/Y) to database format (Y-m-d)
     */
    protected function setEngagementAnniversaryDateAttribute($value)
    {
        $this->attributes['engagement_anniversary_date'] = formatDateForDatabase($value);
    }

    /**
     * Check if provided password matches current password
     */
    public function checkPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }
}
