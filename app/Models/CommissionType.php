<?php

namespace App\Models;

use Database\Factories\CommissionTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\CommissionType
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property bool $status
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, CustomerInsurance> $customerInsurances
 * @property-read int|null $customer_insurances_count
 *
 * @method static Builder|CommissionType active()
 * @method static CommissionTypeFactory factory($count = null, $state = [])
 * @method static Builder|CommissionType newModelQuery()
 * @method static Builder|CommissionType newQuery()
 * @method static Builder|CommissionType onlyTrashed()
 * @method static Builder|CommissionType ordered()
 * @method static Builder|CommissionType query()
 * @method static Builder|CommissionType whereCreatedAt($value)
 * @method static Builder|CommissionType whereCreatedBy($value)
 * @method static Builder|CommissionType whereDeletedAt($value)
 * @method static Builder|CommissionType whereDeletedBy($value)
 * @method static Builder|CommissionType whereDescription($value)
 * @method static Builder|CommissionType whereId($value)
 * @method static Builder|CommissionType whereName($value)
 * @method static Builder|CommissionType whereSortOrder($value)
 * @method static Builder|CommissionType whereStatus($value)
 * @method static Builder|CommissionType whereUpdatedAt($value)
 * @method static Builder|CommissionType whereUpdatedBy($value)
 * @method static Builder|CommissionType withTrashed()
 * @method static Builder|CommissionType withoutTrashed()
 *
 * @mixin Model
 */
class CommissionType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'commission_types';

    protected $fillable = [
        'name',
        'description',
        'status',
        'sort_order',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Customer insurances that use this commission type
     */
    public function customerInsurances()
    {
        return $this->hasMany(CustomerInsurance::class, 'commission_type_id');
    }

    /**
     * Scope: Active commission types only
     */
    protected function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope: Ordered by sort order
     */
    protected function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
