<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Models\Tenant;

class Payment extends Model
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
        'subscription_id',
        'payment_gateway',
        'gateway_payment_id',
        'gateway_order_id',
        'amount',
        'currency',
        'status',
        'type',
        'description',
        'gateway_response',
        'metadata',
        'paid_at',
        'failed_at',
        'refunded_at',
        'failure_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the payment.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Get the subscription that owns the payment.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted(string $gatewayPaymentId, array $gatewayResponse = []): bool
    {
        return $this->update([
            'status' => 'completed',
            'gateway_payment_id' => $gatewayPaymentId,
            'gateway_response' => $gatewayResponse,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(string $reason, array $gatewayResponse = []): bool
    {
        return $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'gateway_response' => $gatewayResponse,
            'failed_at' => now(),
        ]);
    }

    /**
     * Mark payment as refunded.
     */
    public function markAsRefunded(array $gatewayResponse = []): bool
    {
        return $this->update([
            'status' => 'refunded',
            'gateway_response' => array_merge($this->gateway_response ?? [], $gatewayResponse),
            'refunded_at' => now(),
        ]);
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment has failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Scope to get completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get payments for a specific gateway.
     */
    public function scopeForGateway($query, string $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }
}
