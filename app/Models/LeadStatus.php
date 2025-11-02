<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class LeadStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active',
        'is_converted',
        'is_lost',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_converted' => 'boolean',
        'is_lost' => 'boolean',
        'display_order' => 'integer',
    ];

    // Relationships

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'status_id');
    }

    // Query Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeConverted(Builder $query): Builder
    {
        return $query->where('is_converted', true);
    }

    public function scopeLost(Builder $query): Builder
    {
        return $query->where('is_lost', true);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('is_converted', false)
            ->where('is_lost', false);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    // Helper Methods

    public function getLeadsCount(): int
    {
        return $this->leads()->count();
    }

    public function isConvertedStatus(): bool
    {
        return $this->is_converted;
    }

    public function isLostStatus(): bool
    {
        return $this->is_lost;
    }

    public function isActiveStatus(): bool
    {
        return ! $this->is_converted && ! $this->is_lost;
    }
}
