<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadWhatsAppCampaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'lead_whatsapp_campaigns';

    protected $fillable = [
        'name',
        'description',
        'message_template',
        'attachment_path',
        'attachment_type',
        'status',
        'target_criteria',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_leads',
        'sent_count',
        'failed_count',
        'delivered_count',
        'read_count',
        'messages_per_minute',
        'auto_retry_failed',
        'max_retry_attempts',
        'created_by',
    ];

    protected $casts = [
        'target_criteria' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'auto_retry_failed' => 'boolean',
    ];

    // Relationships

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(LeadWhatsAppMessage::class, 'campaign_id');
    }

    public function campaignLeads(): HasMany
    {
        return $this->hasMany(LeadWhatsAppCampaignLead::class, 'campaign_id');
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class, 'lead_whatsapp_campaign_leads', 'campaign_id', 'lead_id')
            ->withPivot(['status', 'sent_at', 'delivered_at', 'read_at', 'error_message', 'retry_count', 'last_retry_at'])
            ->withTimestamps();
    }

    // Helper Methods

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canExecute(): bool
    {
        return in_array($this->status, ['draft', 'scheduled', 'paused']);
    }

    public function canPause(): bool
    {
        return $this->status === 'active';
    }

    public function hasAttachment(): bool
    {
        return !empty($this->attachment_path);
    }

    public function getAttachmentUrl(): ?string
    {
        return $this->attachment_path ? asset('storage/' . $this->attachment_path) : null;
    }

    public function getSuccessRate(): float
    {
        if ($this->sent_count === 0) {
            return 0;
        }

        return round(($this->delivered_count / $this->sent_count) * 100, 2);
    }

    public function getDeliveryRate(): float
    {
        if ($this->total_leads === 0) {
            return 0;
        }

        return round(($this->delivered_count / $this->total_leads) * 100, 2);
    }

    public function getReadRate(): float
    {
        if ($this->delivered_count === 0) {
            return 0;
        }

        return round(($this->read_count / $this->delivered_count) * 100, 2);
    }

    public function getPendingCount(): int
    {
        return $this->total_leads - $this->sent_count - $this->failed_count;
    }

    public function getFailureRate(): float
    {
        if ($this->total_leads === 0) {
            return 0;
        }

        return round(($this->failed_count / $this->total_leads) * 100, 2);
    }

    public function incrementSent(): void
    {
        $this->increment('sent_count');
    }

    public function incrementFailed(): void
    {
        $this->increment('failed_count');
    }

    public function incrementDelivered(): void
    {
        $this->increment('delivered_count');
    }

    public function incrementRead(): void
    {
        $this->increment('read_count');
    }

    public function markAsActive(): void
    {
        $this->update([
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsPaused(): void
    {
        $this->update(['status' => 'paused']);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    // Scopes

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    public function scopeDueForExecution($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }

    public function scopeCreatedBy($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }
}
