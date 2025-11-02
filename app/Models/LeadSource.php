<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class LeadSource extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    // Relationships

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'source_id');
    }

    // Query Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
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

    public function getActiveLeadsCount(): int
    {
        return $this->leads()->active()->count();
    }

    public function getConvertedLeadsCount(): int
    {
        return $this->leads()->converted()->count();
    }
}
