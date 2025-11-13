<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Models\Tenant;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

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
        'tenant_id',
        'plan_id',
        'status',
        'is_trial',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'next_billing_date',
        'mrr',
        'payment_gateway',
        'gateway_subscription_id',
        'gateway_customer_id',
        'payment_method',
        'cancelled_at',
        'cancellation_reason',
        'metadata',
        'auto_renew',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_trial' => 'boolean',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'next_billing_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'mrr' => 'decimal:2',
        'payment_method' => 'array',
        'metadata' => 'array',
        'auto_renew' => 'boolean',
    ];

    /**
     * Get the tenant that owns the subscription.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Get the plan for the subscription.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get all payments for the subscription.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get all invoices for the subscription.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class)->orderBy('invoice_date', 'desc');
    }

    /**
     * Check if subscription is active.
     * Includes trials that haven't expired yet.
     */
    public function isActive(): bool
    {
        // Check if subscription has expired (ends_at date passed)
        if ($this->hasExpired()) {
            return false;
        }

        // Consider active if status is 'active' OR on valid trial
        return $this->status === 'active' || $this->onTrial();
    }

    /**
     * Check if subscription is on trial.
     */
    public function onTrial(): bool
    {
        return $this->is_trial && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has ended.
     */
    public function trialEnded(): bool
    {
        return $this->is_trial && $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Check if subscription has expired (based on ends_at date).
     */
    public function hasExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Check if subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || ! is_null($this->cancelled_at);
    }

    /**
     * Check if subscription is past due.
     */
    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    /**
     * Check if subscription is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Get days remaining in trial.
     */
    public function trialDaysRemaining(): int
    {
        if (! $this->onTrial()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(?string $reason = null): bool
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Suspend the subscription.
     */
    public function suspend(): bool
    {
        $this->update(['status' => 'suspended']);

        return true;
    }

    /**
     * Resume the subscription.
     */
    public function resume(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        $this->update(['status' => 'active']);

        return true;
    }

    /**
     * Scope to get active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get trial subscriptions.
     */
    public function scopeOnTrial($query)
    {
        return $query->where('is_trial', true)
            ->where('trial_ends_at', '>', now());
    }

    /**
     * Scope to get expired trials.
     */
    public function scopeTrialExpired($query)
    {
        return $query->where('is_trial', true)
            ->where('trial_ends_at', '<=', now());
    }

    /**
     * Scope to get cancelled subscriptions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope to get expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('ends_at')
            ->where('ends_at', '<=', now());
    }
}
