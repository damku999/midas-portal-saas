<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class LeadWhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'lead_whatsapp_messages';

    protected $fillable = [
        'lead_id',
        'message',
        'attachment_path',
        'attachment_type',
        'status',
        'sent_at',
        'delivered_at',
        'read_at',
        'sent_by',
        'campaign_id',
        'error_message',
        'api_response',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'api_response' => 'array',
    ];

    // Relationships

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(LeadWhatsAppCampaign::class, 'campaign_id');
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

    public function hasAttachment(): bool
    {
        return ! empty($this->attachment_path);
    }

    public function getAttachmentUrl(): ?string
    {
        return $this->attachment_path ? asset('storage/'.$this->attachment_path) : null;
    }

    public function markAsSent(array $apiResponse = []): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'api_response' => $apiResponse,
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

    public function markAsFailed(string $errorMessage, array $apiResponse = []): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'api_response' => $apiResponse,
        ]);
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

    public function scopeWithAttachment($query)
    {
        return $query->whereNotNull('attachment_path');
    }

    public function scopeForLead($query, int $leadId)
    {
        return $query->where('lead_id', $leadId);
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    public function scopeSentBy($query, int $userId)
    {
        return $query->where('sent_by', $userId);
    }
}
