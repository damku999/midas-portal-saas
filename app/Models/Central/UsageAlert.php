<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Models\Tenant;

class UsageAlert extends Model
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
        'resource_type',
        'threshold_level',
        'usage_percentage',
        'current_usage',
        'limit_value',
        'alert_status',
        'sent_at',
        'acknowledged_at',
        'resolved_at',
        'notification_channels',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'usage_percentage' => 'decimal:2',
        'current_usage' => 'integer',
        'limit_value' => 'integer',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'notification_channels' => 'array',
    ];

    /**
     * Get the tenant that owns the alert.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Check if alert is for warning threshold (80%).
     */
    public function isWarning(): bool
    {
        return $this->threshold_level === 'warning';
    }

    /**
     * Check if alert is for critical threshold (90%).
     */
    public function isCritical(): bool
    {
        return $this->threshold_level === 'critical';
    }

    /**
     * Check if alert is for exceeded threshold (100%).
     */
    public function isExceeded(): bool
    {
        return $this->threshold_level === 'exceeded';
    }

    /**
     * Check if alert has been sent.
     */
    public function isSent(): bool
    {
        return $this->alert_status === 'sent' && !is_null($this->sent_at);
    }

    /**
     * Check if alert has been acknowledged by tenant.
     */
    public function isAcknowledged(): bool
    {
        return $this->alert_status === 'acknowledged' && !is_null($this->acknowledged_at);
    }

    /**
     * Check if alert has been resolved (usage dropped below threshold).
     */
    public function isResolved(): bool
    {
        return $this->alert_status === 'resolved' && !is_null($this->resolved_at);
    }

    /**
     * Mark alert as sent.
     */
    public function markAsSent(array $channels = ['email']): bool
    {
        return $this->update([
            'alert_status' => 'sent',
            'sent_at' => now(),
            'notification_channels' => $channels,
        ]);
    }

    /**
     * Mark alert as acknowledged by tenant.
     */
    public function acknowledge(?string $notes = null): bool
    {
        return $this->update([
            'alert_status' => 'acknowledged',
            'acknowledged_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Mark alert as resolved when usage drops below threshold.
     */
    public function resolve(?string $notes = null): bool
    {
        return $this->update([
            'alert_status' => 'resolved',
            'resolved_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Get severity level as string (for UI display).
     */
    public function getSeverityAttribute(): string
    {
        return match ($this->threshold_level) {
            'warning' => 'warning',
            'critical' => 'danger',
            'exceeded' => 'critical',
            default => 'info',
        };
    }

    /**
     * Get severity color (for UI badges).
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->threshold_level) {
            'warning' => '#f59e0b', // amber-500
            'critical' => '#ef4444', // red-500
            'exceeded' => '#dc2626', // red-600
            default => '#06b6d4', // cyan-500
        };
    }

    /**
     * Get human-readable resource type.
     */
    public function getResourceTypeDisplayAttribute(): string
    {
        return match ($this->resource_type) {
            'users' => 'Staff Users',
            'customers' => 'Customers',
            'storage' => 'Storage Space',
            default => ucfirst($this->resource_type),
        };
    }

    /**
     * Get human-readable threshold level.
     */
    public function getThresholdDisplayAttribute(): string
    {
        return match ($this->threshold_level) {
            'warning' => 'Warning (80%)',
            'critical' => 'Critical (90%)',
            'exceeded' => 'Limit Exceeded (100%)',
            default => ucfirst($this->threshold_level),
        };
    }

    /**
     * Get formatted usage display (e.g., "45 / 50 users").
     */
    public function getUsageDisplayAttribute(): string
    {
        $unit = $this->resource_type === 'storage' ? 'GB' : $this->resource_type;

        if ($this->limit_value === -1) {
            return "{$this->current_usage} {$unit} (Unlimited)";
        }

        return "{$this->current_usage} / {$this->limit_value} {$unit}";
    }

    /**
     * Scope to get active (unresolved) alerts.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('alert_status', ['pending', 'sent', 'acknowledged'])
            ->whereNull('resolved_at');
    }

    /**
     * Scope to get alerts by tenant.
     */
    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to get alerts by resource type.
     */
    public function scopeForResource($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope to get alerts by threshold level.
     */
    public function scopeAtThreshold($query, string $thresholdLevel)
    {
        return $query->where('threshold_level', $thresholdLevel);
    }

    /**
     * Scope to get recent alerts (last 30 days).
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Scope to get unsent alerts.
     */
    public function scopeUnsent($query)
    {
        return $query->where('alert_status', 'pending')
            ->whereNull('sent_at');
    }
}
