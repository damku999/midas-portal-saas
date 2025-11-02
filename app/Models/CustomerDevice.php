<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
/**
 * App\Models\CustomerDevice
 *
 * @property int $id
 * @property int $customer_id
 * @property string $device_type
 * @property string $device_token
 * @property string|null $device_name
 * @property string|null $device_model
 * @property string|null $os_version
 * @property string|null $app_version
 * @property Carbon|null $last_active_at
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer|null $customer
 *
 * @method static Builder|CustomerDevice active()
 * @method static Builder|CustomerDevice newModelQuery()
 * @method static Builder|CustomerDevice newQuery()
 * @method static Builder|CustomerDevice ofType(string $type)
 * @method static Builder|CustomerDevice query()
 * @method static Builder|CustomerDevice whereAppVersion($value)
 * @method static Builder|CustomerDevice whereCreatedAt($value)
 * @method static Builder|CustomerDevice whereCustomerId($value)
 * @method static Builder|CustomerDevice whereDeviceModel($value)
 * @method static Builder|CustomerDevice whereDeviceName($value)
 * @method static Builder|CustomerDevice whereDeviceToken($value)
 * @method static Builder|CustomerDevice whereDeviceType($value)
 * @method static Builder|CustomerDevice whereId($value)
 * @method static Builder|CustomerDevice whereIsActive($value)
 * @method static Builder|CustomerDevice whereLastActiveAt($value)
 * @method static Builder|CustomerDevice whereOsVersion($value)
 * @method static Builder|CustomerDevice whereUpdatedAt($value)
 *
 * @mixin Model
 */
class CustomerDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'device_type',
        'device_token',
        'device_name',
        'device_model',
        'os_version',
        'app_version',
        'last_active_at',
        'is_active',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the customer that owns this device
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope for active devices only
     */
    protected function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific device type
     */
    protected function scopeOfType($query, string $type)
    {
        return $query->where('device_type', $type);
    }

    /**
     * Update last active timestamp
     */
    public function markActive(): void
    {
        $this->last_active_at = now();
        $this->save();
    }

    /**
     * Deactivate this device
     */
    public function deactivate(): void
    {
        $this->is_active = false;
        $this->save();
    }
}
