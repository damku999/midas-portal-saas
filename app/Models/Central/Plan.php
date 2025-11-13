<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
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
        'name',
        'slug',
        'description',
        'price',
        'billing_interval',
        'features',
        'max_users',
        'max_customers',
        'max_leads_per_month',
        'storage_limit_gb',
        'is_active',
        'sort_order',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'max_users' => 'integer',
        'max_customers' => 'integer',
        'max_leads_per_month' => 'integer',
        'storage_limit_gb' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the subscriptions for this plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Check if the plan has unlimited users.
     */
    public function hasUnlimitedUsers(): bool
    {
        return $this->max_users === -1;
    }

    /**
     * Check if the plan has unlimited customers.
     */
    public function hasUnlimitedCustomers(): bool
    {
        return $this->max_customers === -1;
    }

    /**
     * Check if the plan has unlimited leads.
     */
    public function hasUnlimitedLeads(): bool
    {
        return $this->max_leads_per_month === -1;
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '₹'.number_format($this->price, 2);
    }

    /**
     * Get price with GST (18%).
     */
    public function getPriceWithGstAttribute(): float
    {
        return round($this->price * 1.18, 2);
    }

    /**
     * Get CGST amount (9%).
     */
    public function getCgstAmountAttribute(): float
    {
        return round($this->price * 0.09, 2);
    }

    /**
     * Get SGST amount (9%).
     */
    public function getSgstAmountAttribute(): float
    {
        return round($this->price * 0.09, 2);
    }

    /**
     * Get total GST amount (18%).
     */
    public function getTotalGstAttribute(): float
    {
        return round($this->price * 0.18, 2);
    }

    /**
     * Get formatted price with GST.
     */
    public function getFormattedPriceWithGstAttribute(): string
    {
        return '₹'.number_format($this->price_with_gst, 2);
    }

    /**
     * Get human-readable billing interval.
     */
    public function getBillingIntervalLabelAttribute(): string
    {
        return match ($this->billing_interval) {
            'week' => 'Weekly',
            'month' => 'Monthly',
            'two_month' => 'Every 2 Months',
            'quarter' => 'Quarterly',
            'six_month' => 'Half-Yearly',
            'year' => 'Yearly',
            default => ucfirst($this->billing_interval),
        };
    }

    /**
     * Get human-readable max users.
     */
    public function getMaxUsersLabelAttribute(): string
    {
        return $this->max_users === -1 ? 'Unlimited' : number_format($this->max_users) . ' users';
    }

    /**
     * Get human-readable max customers.
     */
    public function getMaxCustomersLabelAttribute(): string
    {
        return $this->max_customers === -1 ? 'Unlimited' : number_format($this->max_customers) . ' customers';
    }

    /**
     * Get human-readable max leads per month.
     */
    public function getMaxLeadsLabelAttribute(): string
    {
        return $this->max_leads_per_month === -1 ? 'Unlimited' : number_format($this->max_leads_per_month) . ' leads/month';
    }

    /**
     * Get human-readable storage limit.
     */
    public function getStorageLimitLabelAttribute(): string
    {
        return number_format($this->storage_limit_gb) . ' GB storage';
    }

    /**
     * Get annual price based on billing interval.
     */
    public function getAnnualPriceAttribute(): float
    {
        return match ($this->billing_interval) {
            'week' => $this->price * 52,
            'month' => $this->price * 12,
            'two_month' => $this->price * 6,
            'quarter' => $this->price * 4,
            'six_month' => $this->price * 2,
            'year' => $this->price,
            default => $this->price * 12,
        };
    }

    /**
     * Scope to get only active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }
}
