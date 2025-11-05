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
        return '$'.number_format($this->price, 2);
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
