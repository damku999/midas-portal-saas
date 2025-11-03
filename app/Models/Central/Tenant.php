<?php

namespace App\Models\Central;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Boot the model and register model event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically delete associated domains when tenant is deleted
        static::deleting(function ($tenant) {
            $tenant->domains()->delete();
        });
    }

    /**
     * Get the current subscription for the tenant.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'tenant_id', 'id')->latestOfMany();
    }

    /**
     * Get all subscriptions for the tenant.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'tenant_id', 'id');
    }

    /**
     * Get the current plan through the subscription.
     */
    public function plan()
    {
        return $this->hasOneThrough(
            Plan::class,
            Subscription::class,
            'tenant_id', // Foreign key on subscriptions table
            'id', // Foreign key on plans table
            'id', // Local key on tenants table
            'plan_id' // Local key on subscriptions table
        )->latestOfMany();
    }

    /**
     * Get audit logs for this tenant.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'tenant_id', 'id');
    }

    /**
     * Check if tenant is active.
     */
    public function isActive(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    /**
     * Check if tenant is on trial (instance method).
     */
    public function isOnTrial(): bool
    {
        return $this->subscription && $this->subscription->onTrial();
    }

    /**
     * Check if tenant is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->subscription && $this->subscription->isSuspended();
    }

    /**
     * Check if tenant has exceeded plan limits.
     */
    public function hasExceededLimits(string $limitType): bool
    {
        if (! $this->plan) {
            return false;
        }

        $currentUsage = $this->getCurrentUsage($limitType);
        $limit = $this->getPlanLimit($limitType);

        if ($limit === -1) {
            return false; // Unlimited
        }

        return $currentUsage >= $limit;
    }

    /**
     * Get current usage for a specific limit type.
     */
    public function getCurrentUsage(string $limitType): int
    {
        // This will be implemented with actual usage tracking
        // For now, return 0
        return $this->data[$limitType.'_usage'] ?? 0;
    }

    /**
     * Get plan limit for a specific type.
     */
    public function getPlanLimit(string $limitType): int
    {
        if (! $this->plan) {
            return 0;
        }

        return match ($limitType) {
            'users' => $this->plan->max_users,
            'customers' => $this->plan->max_customers,
            'leads' => $this->plan->max_leads_per_month,
            'storage' => $this->plan->storage_limit_gb,
            default => 0,
        };
    }

    /**
     * Get usage percentage for a specific limit type.
     */
    public function getUsagePercentage(string $limitType): float
    {
        $limit = $this->getPlanLimit($limitType);

        if ($limit === -1) {
            return 0; // Unlimited
        }

        if ($limit === 0) {
            return 100;
        }

        $usage = $this->getCurrentUsage($limitType);

        return min(100, ($usage / $limit) * 100);
    }

    /**
     * Get tenant's primary domain.
     */
    public function getPrimaryDomain(): ?string
    {
        return $this->domains()->first()?->domain;
    }

    /**
     * Get tenant's database name.
     */
    public function getDatabaseName(): string
    {
        return config('tenancy.database.prefix').$this->id.config('tenancy.database.suffix');
    }

    /**
     * Scope to get active tenants.
     */
    public function scopeActive($query)
    {
        return $query->whereHas('subscription', function ($q) {
            $q->where('status', 'active');
        });
    }

    /**
     * Scope to get trial tenants.
     */
    public function scopeOnTrial($query)
    {
        return $query->whereHas('subscription', function ($q) {
            $q->where('is_trial', true)
                ->where('trial_ends_at', '>', now());
        });
    }

    /**
     * Scope to get suspended tenants.
     */
    public function scopeSuspended($query)
    {
        return $query->whereHas('subscription', function ($q) {
            $q->where('status', 'suspended');
        });
    }
}
