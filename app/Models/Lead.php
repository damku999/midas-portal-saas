<?php

namespace App\Models;

use App\Traits\ProtectedRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Lead extends Model
{
    use HasFactory;
    use ProtectedRecord;
    use SoftDeletes;

    protected $fillable = [
        'lead_number',
        'name',
        'email',
        'mobile_number',
        'alternate_mobile',
        'city',
        'state',
        'pincode',
        'address',
        'date_of_birth',
        'age',
        'occupation',
        'source_id',
        'product_interest',
        'status_id',
        'priority',
        'assigned_to',
        'relationship_manager_id',
        'reference_user_id',
        'next_follow_up_date',
        'remarks',
        'converted_customer_id',
        'converted_at',
        'conversion_notes',
        'lost_reason',
        'lost_at',
        'created_by',
        'updated_by',
        'is_protected',
        'protected_reason',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'next_follow_up_date' => 'date',
        'converted_at' => 'datetime',
        'lost_at' => 'datetime',
        'is_protected' => 'boolean',
    ];

    // Relationships

    public function source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function relationshipManager(): BelongsTo
    {
        return $this->belongsTo(RelationshipManager::class, 'relationship_manager_id');
    }

    public function referenceUser(): BelongsTo
    {
        return $this->belongsTo(ReferenceUser::class, 'reference_user_id');
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->orderBy('created_at', 'desc');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LeadDocument::class);
    }

    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(LeadWhatsAppMessage::class)->orderBy('created_at', 'desc');
    }

    public function whatsappCampaigns(): BelongsToMany
    {
        return $this->belongsToMany(LeadWhatsAppCampaign::class, 'lead_whatsapp_campaign_leads', 'lead_id', 'campaign_id')
            ->withPivot(['status', 'sent_at', 'delivered_at', 'read_at', 'error_message', 'retry_count', 'last_retry_at'])
            ->withTimestamps();
    }

    // Query Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('status', function ($q) {
            $q->where('is_converted', false)->where('is_lost', false);
        });
    }

    public function scopeConverted(Builder $query): Builder
    {
        return $query->whereHas('status', function ($q) {
            $q->where('is_converted', true);
        });
    }

    public function scopeLost(Builder $query): Builder
    {
        return $query->whereHas('status', function ($q) {
            $q->where('is_lost', true);
        });
    }

    public function scopeByStatus(Builder $query, int $statusId): Builder
    {
        return $query->where('status_id', $statusId);
    }

    public function scopeBySource(Builder $query, int $sourceId): Builder
    {
        return $query->where('source_id', $sourceId);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeFollowUpDue(Builder $query): Builder
    {
        return $query->whereNotNull('next_follow_up_date')
            ->whereDate('next_follow_up_date', '<=', now());
    }

    public function scopeFollowUpOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('next_follow_up_date')
            ->whereDate('next_follow_up_date', '<', now());
    }

    // Mutators & Accessors

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            if (empty($lead->lead_number)) {
                $lead->lead_number = static::generateLeadNumber();
            }

            if (!empty($lead->date_of_birth) && empty($lead->age)) {
                $lead->age = Carbon::parse($lead->date_of_birth)->age;
            }
        });

        static::updating(function ($lead) {
            if ($lead->isDirty('date_of_birth') && !empty($lead->date_of_birth)) {
                $lead->age = Carbon::parse($lead->date_of_birth)->age;
            }
        });
    }

    public static function generateLeadNumber(): string
    {
        $yearMonth = now()->format('Ym');
        $prefix = 'LD-' . $yearMonth . '-';

        $lastLead = static::where('lead_number', 'like', $prefix . '%')
            ->orderBy('lead_number', 'desc')
            ->first();

        if ($lastLead) {
            $lastNumber = (int) substr($lastLead->lead_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }

    // Helper Methods

    public function isConverted(): bool
    {
        return $this->status->is_converted ?? false;
    }

    public function isLost(): bool
    {
        return $this->status->is_lost ?? false;
    }

    public function isActive(): bool
    {
        return !$this->isConverted() && !$this->isLost();
    }

    public function hasFollowUpDue(): bool
    {
        if (!$this->next_follow_up_date) {
            return false;
        }

        return Carbon::parse($this->next_follow_up_date)->isPast();
    }
}
