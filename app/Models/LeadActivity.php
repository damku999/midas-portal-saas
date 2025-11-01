<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class LeadActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'activity_type',
        'subject',
        'description',
        'outcome',
        'next_action',
        'scheduled_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Activity types
    public const TYPE_CALL = 'call';
    public const TYPE_EMAIL = 'email';
    public const TYPE_MEETING = 'meeting';
    public const TYPE_NOTE = 'note';
    public const TYPE_STATUS_CHANGE = 'status_change';
    public const TYPE_ASSIGNMENT = 'assignment';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_QUOTATION = 'quotation';

    // Relationships

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Query Scopes

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('activity_type', $type);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->whereNotNull('scheduled_at')
            ->whereNull('completed_at');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('scheduled_at')
            ->whereNull('completed_at')
            ->where('scheduled_at', '<', now());
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('scheduled_at', today());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereNotNull('scheduled_at')
            ->whereNull('completed_at')
            ->where('scheduled_at', '>=', now());
    }

    public function scopeByLead(Builder $query, int $leadId): Builder
    {
        return $query->where('lead_id', $leadId);
    }

    public function scopeByCreator(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper Methods

    public function isCompleted(): bool
    {
        return !is_null($this->completed_at);
    }

    public function isPending(): bool
    {
        return is_null($this->completed_at);
    }

    public function isOverdue(): bool
    {
        if (is_null($this->scheduled_at) || !is_null($this->completed_at)) {
            return false;
        }

        return Carbon::parse($this->scheduled_at)->isPast();
    }

    public function markAsCompleted(): void
    {
        $this->update(['completed_at' => now()]);
    }

    public static function getActivityTypes(): array
    {
        return [
            self::TYPE_CALL => 'Call',
            self::TYPE_EMAIL => 'Email',
            self::TYPE_MEETING => 'Meeting',
            self::TYPE_NOTE => 'Note',
            self::TYPE_STATUS_CHANGE => 'Status Change',
            self::TYPE_ASSIGNMENT => 'Assignment',
            self::TYPE_DOCUMENT => 'Document',
            self::TYPE_QUOTATION => 'Quotation',
        ];
    }
}
