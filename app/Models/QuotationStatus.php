<?php

namespace App\Models;

use Database\Factories\QuotationStatusFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * App\Models\QuotationStatus
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string|null $color
 * @property bool $is_active
 * @property bool $is_final
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Quotation> $quotations
 * @property-read int|null $quotations_count
 *
 * @method static Builder|QuotationStatus active()
 * @method static QuotationStatusFactory factory($count = null, $state = [])
 * @method static Builder|QuotationStatus final()
 * @method static Builder|QuotationStatus newModelQuery()
 * @method static Builder|QuotationStatus newQuery()
 * @method static Builder|QuotationStatus nonFinal()
 * @method static Builder|QuotationStatus onlyTrashed()
 * @method static Builder|QuotationStatus ordered()
 * @method static Builder|QuotationStatus query()
 * @method static Builder|QuotationStatus whereColor($value)
 * @method static Builder|QuotationStatus whereCreatedAt($value)
 * @method static Builder|QuotationStatus whereCreatedBy($value)
 * @method static Builder|QuotationStatus whereDeletedAt($value)
 * @method static Builder|QuotationStatus whereDeletedBy($value)
 * @method static Builder|QuotationStatus whereDescription($value)
 * @method static Builder|QuotationStatus whereId($value)
 * @method static Builder|QuotationStatus whereIsActive($value)
 * @method static Builder|QuotationStatus whereIsFinal($value)
 * @method static Builder|QuotationStatus whereName($value)
 * @method static Builder|QuotationStatus whereSortOrder($value)
 * @method static Builder|QuotationStatus whereUpdatedAt($value)
 * @method static Builder|QuotationStatus whereUpdatedBy($value)
 * @method static Builder|QuotationStatus withTrashed()
 * @method static Builder|QuotationStatus withoutTrashed()
 *
 * @mixin Model
 */
class QuotationStatus extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'quotation_statuses';

    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active',
        'is_final',
        'sort_order',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_final' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Quotations that have this status
     */
    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'quotation_status_id');
    }

    /**
     * Scope: Active statuses only
     */
    protected function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Final statuses only (accepted/rejected)
     */
    protected function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    /**
     * Scope: Non-final statuses (draft/generated/sent)
     */
    protected function scopeNonFinal($query)
    {
        return $query->where('is_final', false);
    }

    /**
     * Scope: Ordered by sort order
     */
    protected function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the status color for UI display
     */
    protected function getColorAttribute($value)
    {
        return $value ?: '#6c757d'; // Default gray color
    }
}
