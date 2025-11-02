<?php

namespace App\Models;

use Database\Factories\CustomerTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
/**
 * App\Models\CustomerType
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
 * @property-read Collection<int, Customer> $customers
 * @property-read int|null $customers_count
 *
 * @method static Builder|CustomerType active()
 * @method static CustomerTypeFactory factory($count = null, $state = [])
 * @method static Builder|CustomerType newModelQuery()
 * @method static Builder|CustomerType newQuery()
 * @method static Builder|CustomerType onlyTrashed()
 * @method static Builder|CustomerType ordered()
 * @method static Builder|CustomerType query()
 * @method static Builder|CustomerType whereCreatedAt($value)
 * @method static Builder|CustomerType whereCreatedBy($value)
 * @method static Builder|CustomerType whereDeletedAt($value)
 * @method static Builder|CustomerType whereDeletedBy($value)
 * @method static Builder|CustomerType whereDescription($value)
 * @method static Builder|CustomerType whereId($value)
 * @method static Builder|CustomerType whereName($value)
 * @method static Builder|CustomerType whereSortOrder($value)
 * @method static Builder|CustomerType whereStatus($value)
 * @method static Builder|CustomerType whereUpdatedAt($value)
 * @method static Builder|CustomerType whereUpdatedBy($value)
 * @method static Builder|CustomerType withTrashed()
 * @method static Builder|CustomerType withoutTrashed()
 *
 * @mixin Model
 */
class CustomerType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'customer_types';

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
     * Customers that belong to this type
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_type_id');
    }

    /**
     * Scope: Active customer types only
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
