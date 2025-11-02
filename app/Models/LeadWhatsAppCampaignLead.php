<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class LeadWhatsAppCampaignLead extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $table = 'lead_whatsapp_campaign_leads';

    protected $fillable = [
        'campaign_id',
        'lead_id',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
        'error_message',
        'retry_count',
        'last_retry_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'last_retry_at' => 'datetime',
    ];

    // Relationships

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(LeadWhatsAppCampaign::class, 'campaign_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    // Helper Methods

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return in_array($this->status, ['sent', 'delivered', 'read']);
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isDelivered(): bool
    {
        return in_array($this->status, ['delivered', 'read']);
    }

    public function isRead(): bool
    {
        return $this->status === 'read';
    }

    public function canRetry(): bool
    {
        if (! $this->isFailed()) {
            return false;
        }

        $maxRetries = $this->campaign->max_retry_attempts ?? 3;

        return $this->retry_count < $maxRetries;
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function incrementRetryCount(): void
    {
        $this->increment('retry_count');
        $this->update(['last_retry_at' => now()]);
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->whereIn('status', ['sent', 'delivered', 'read']);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeDelivered($query)
    {
        return $query->whereIn('status', ['delivered', 'read']);
    }

    public function scopeRetryable($query)
    {
        return $query->where('status', 'failed')
            ->whereRaw('retry_count < (SELECT max_retry_attempts FROM lead_whatsapp_campaigns WHERE id = campaign_id)');
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    public function scopeForLead($query, int $leadId)
    {
        return $query->where('lead_id', $leadId);
    }
}
