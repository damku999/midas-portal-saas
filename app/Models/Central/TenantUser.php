<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class TenantUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'central';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_super_admin',
        'is_support_admin',
        'is_billing_admin',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_super_admin' => 'boolean',
        'is_support_admin' => 'boolean',
        'is_billing_admin' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'array',
    ];

    /**
     * Get the audit logs for this user.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }

    /**
     * Check if user is a support admin.
     */
    public function isSupportAdmin(): bool
    {
        return $this->is_support_admin || $this->is_super_admin;
    }

    /**
     * Check if user is a billing admin.
     */
    public function isBillingAdmin(): bool
    {
        return $this->is_billing_admin || $this->is_super_admin;
    }

    /**
     * Check if user has access to tenants.
     */
    public function canManageTenants(): bool
    {
        return $this->is_super_admin || $this->is_support_admin;
    }

    /**
     * Check if user has access to billing.
     */
    public function canManageBilling(): bool
    {
        return $this->is_super_admin || $this->is_billing_admin;
    }

    /**
     * Check if user can impersonate tenant users.
     */
    public function canImpersonate(): bool
    {
        return $this->is_super_admin || $this->is_support_admin;
    }

    /**
     * Update last login information.
     */
    public function updateLastLogin(string $ipAddress = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress ?? request()->ip(),
        ]);
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get super admins.
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('is_super_admin', true);
    }

    /**
     * Scope to get support admins.
     */
    public function scopeSupportAdmins($query)
    {
        return $query->where(function ($q) {
            $q->where('is_support_admin', true)
              ->orWhere('is_super_admin', true);
        });
    }

    /**
     * Scope to get billing admins.
     */
    public function scopeBillingAdmins($query)
    {
        return $query->where(function ($q) {
            $q->where('is_billing_admin', true)
              ->orWhere('is_super_admin', true);
        });
    }
}
