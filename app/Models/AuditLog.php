<?php

namespace App\Models;

use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\AuditLog
 *
 * @property int $id
 * @property string $auditable_type
 * @property int $auditable_id
 * @property string|null $actor_type
 * @property int|null $actor_id
 * @property string|null $action
 * @property string $event
 * @property string $event_category
 * @property string|null $target_type
 * @property int|null $target_id
 * @property string|null $properties
 * @property array|null $old_values
 * @property array|null $new_values
 * @property array|null $metadata
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $session_id
 * @property string|null $request_id
 * @property Carbon $occurred_at
 * @property string $severity
 * @property int|null $risk_score
 * @property string|null $risk_level
 * @property array|null $risk_factors
 * @property bool $is_suspicious
 * @property string|null $category
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|Model $actor
 * @property-read Model|Model $auditable
 * @property-read string|null $formatted_location
 * @property-read string $risk_badge_class
 *
 * @method static Builder|AuditLog byEventCategory(string $category)
 * @method static Builder|AuditLog byRiskScore(int $minScore)
 * @method static AuditLogFactory factory($count = null, $state = [])
 * @method static Builder|AuditLog highRisk()
 * @method static Builder|AuditLog newModelQuery()
 * @method static Builder|AuditLog newQuery()
 * @method static Builder|AuditLog query()
 * @method static Builder|AuditLog recentActivity(int $hours = 24)
 * @method static Builder|AuditLog suspicious()
 * @method static Builder|AuditLog whereAction($value)
 * @method static Builder|AuditLog whereActorId($value)
 * @method static Builder|AuditLog whereActorType($value)
 * @method static Builder|AuditLog whereAuditableId($value)
 * @method static Builder|AuditLog whereAuditableType($value)
 * @method static Builder|AuditLog whereCategory($value)
 * @method static Builder|AuditLog whereCreatedAt($value)
 * @method static Builder|AuditLog whereEvent($value)
 * @method static Builder|AuditLog whereEventCategory($value)
 * @method static Builder|AuditLog whereId($value)
 * @method static Builder|AuditLog whereIpAddress($value)
 * @method static Builder|AuditLog whereIsSuspicious($value)
 * @method static Builder|AuditLog whereMetadata($value)
 * @method static Builder|AuditLog whereNewValues($value)
 * @method static Builder|AuditLog whereOccurredAt($value)
 * @method static Builder|AuditLog whereOldValues($value)
 * @method static Builder|AuditLog whereProperties($value)
 * @method static Builder|AuditLog whereRequestId($value)
 * @method static Builder|AuditLog whereRiskFactors($value)
 * @method static Builder|AuditLog whereRiskLevel($value)
 * @method static Builder|AuditLog whereRiskScore($value)
 * @method static Builder|AuditLog whereSessionId($value)
 * @method static Builder|AuditLog whereSeverity($value)
 * @method static Builder|AuditLog whereTargetId($value)
 * @method static Builder|AuditLog whereTargetType($value)
 * @method static Builder|AuditLog whereUpdatedAt($value)
 * @method static Builder|AuditLog whereUserAgent($value)
 *
 * @mixin Model
 */
class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'actor_type',
        'actor_id',
        'event',
        'event_category',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
        'session_id',
        'request_id',
        'risk_score',
        'risk_level',
        'risk_factors',
        'is_suspicious',
        'location_country',
        'location_city',
        'location_lat',
        'location_lng',
        'occurred_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'risk_factors' => 'array',
        'is_suspicious' => 'boolean',
        'occurred_at' => 'datetime',
        'location_lat' => 'decimal:8',
        'location_lng' => 'decimal:8',
        'risk_score' => 'integer',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    protected function scopeHighRisk($query)
    {
        return $query->where('risk_level', 'high')
            ->orWhere('risk_level', 'critical');
    }

    protected function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    protected function scopeByEventCategory($query, string $category)
    {
        return $query->where('event_category', $category);
    }

    protected function scopeByRiskScore($query, int $minScore)
    {
        return $query->where('risk_score', '>=', $minScore);
    }

    protected function scopeRecentActivity($query, int $hours = 24)
    {
        return $query->where('occurred_at', '>=', now()->subHours($hours));
    }

    protected function getFormattedLocationAttribute(): ?string
    {
        if ($this->location_city && $this->location_country) {
            return sprintf('%s, %s', $this->location_city, $this->location_country);
        }

        return $this->location_country;
    }

    protected function getRiskBadgeClassAttribute(): string
    {
        return match ($this->risk_level) {
            'critical' => 'badge-danger',
            'high' => 'badge-warning',
            'medium' => 'badge-info',
            'low' => 'badge-success',
            default => 'badge-secondary',
        };
    }

    public function hasRiskFactor(string $factor): bool
    {
        return in_array($factor, $this->risk_factors ?? []);
    }
}
