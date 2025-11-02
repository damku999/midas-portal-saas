<?php

namespace App\Models;

use App\Traits\TableRecordObserver;
use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * App\Models\Quotation
 *
 * @property int $id
 * @property int $customer_id
 * @property string|null $vehicle_number
 * @property string $make_model_variant
 * @property string $rto_location
 * @property string $manufacturing_year
 * @property Carbon|null $date_of_registration
 * @property int $cubic_capacity_kw
 * @property int $seating_capacity
 * @property string $fuel_type
 * @property string $ncb_percentage
 * @property string|null $idv_vehicle
 * @property string|null $idv_trailer
 * @property string|null $idv_cng_lpg_kit
 * @property string|null $idv_electrical_accessories
 * @property string|null $idv_non_electrical_accessories
 * @property string|null $total_idv
 * @property array|null $addon_covers
 * @property string $policy_type
 * @property int $policy_tenure_years
 * @property string $status
 * @property Carbon|null $sent_at
 * @property string|null $whatsapp_number
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Customer|null $customer
 * @property-read Collection<int, QuotationCompany> $quotationCompanies
 * @property-read int|null $quotation_companies_count
 * @property-read QuotationStatus|null $quotationStatus
 *
 * @method static QuotationFactory factory($count = null, $state = [])
 * @method static Builder|Quotation newModelQuery()
 * @method static Builder|Quotation newQuery()
 * @method static Builder|Quotation onlyTrashed()
 * @method static Builder|Quotation query()
 * @method static Builder|Quotation whereAddonCovers($value)
 * @method static Builder|Quotation whereCreatedAt($value)
 * @method static Builder|Quotation whereCreatedBy($value)
 * @method static Builder|Quotation whereCubicCapacityKw($value)
 * @method static Builder|Quotation whereCustomerId($value)
 * @method static Builder|Quotation whereDateOfRegistration($value)
 * @method static Builder|Quotation whereDeletedAt($value)
 * @method static Builder|Quotation whereDeletedBy($value)
 * @method static Builder|Quotation whereFuelType($value)
 * @method static Builder|Quotation whereId($value)
 * @method static Builder|Quotation whereIdvCngLpgKit($value)
 * @method static Builder|Quotation whereIdvElectricalAccessories($value)
 * @method static Builder|Quotation whereIdvNonElectricalAccessories($value)
 * @method static Builder|Quotation whereIdvTrailer($value)
 * @method static Builder|Quotation whereIdvVehicle($value)
 * @method static Builder|Quotation whereMakeModelVariant($value)
 * @method static Builder|Quotation whereManufacturingYear($value)
 * @method static Builder|Quotation whereNcbPercentage($value)
 * @method static Builder|Quotation whereNotes($value)
 * @method static Builder|Quotation wherePolicyTenureYears($value)
 * @method static Builder|Quotation wherePolicyType($value)
 * @method static Builder|Quotation whereRtoLocation($value)
 * @method static Builder|Quotation whereSeatingCapacity($value)
 * @method static Builder|Quotation whereSentAt($value)
 * @method static Builder|Quotation whereStatus($value)
 * @method static Builder|Quotation whereTotalIdv($value)
 * @method static Builder|Quotation whereUpdatedAt($value)
 * @method static Builder|Quotation whereUpdatedBy($value)
 * @method static Builder|Quotation whereVehicleNumber($value)
 * @method static Builder|Quotation whereWhatsappNumber($value)
 * @method static Builder|Quotation withTrashed()
 * @method static Builder|Quotation withoutTrashed()
 *
 * @mixin Model
 */
class Quotation extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;
    use TableRecordObserver;

    protected $fillable = [
        'customer_id',
        'vehicle_number',
        'make_model_variant',
        'rto_location',
        'manufacturing_year',
        'date_of_registration',
        'cubic_capacity_kw',
        'seating_capacity',
        'fuel_type',
        'ncb_percentage',
        'idv_vehicle',
        'idv_trailer',
        'idv_cng_lpg_kit',
        'idv_electrical_accessories',
        'idv_non_electrical_accessories',
        'total_idv',
        'addon_covers',
        'policy_type',
        'policy_tenure_years',
        'status',
        'sent_at',
        'whatsapp_number',
        'notes',
    ];

    protected $casts = [
        'date_of_registration' => 'date',
        'ncb_percentage' => 'decimal:2',
        'idv_vehicle' => 'decimal:2',
        'idv_trailer' => 'decimal:2',
        'idv_cng_lpg_kit' => 'decimal:2',
        'idv_electrical_accessories' => 'decimal:2',
        'idv_non_electrical_accessories' => 'decimal:2',
        'total_idv' => 'decimal:2',
        'addon_covers' => 'array',
        'sent_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(static function (Quotation $quotation): void {
            // Delete all related quotation companies
            $quotation->quotationCompanies()->delete();

            // Clean up activity logs for this quotation and its companies
            Activity::query()->where('subject_type', Quotation::class)
                ->where('subject_id', $quotation->id)
                ->delete();

            Activity::query()->where('subject_type', QuotationCompany::class)
                ->whereIn('subject_id', $quotation->quotationCompanies()->pluck('id'))
                ->delete();
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotationCompanies(): HasMany
    {
        return $this->hasMany(QuotationCompany::class)->orderBy('ranking');
    }

    public function quotationStatus(): BelongsTo
    {
        return $this->belongsTo(QuotationStatus::class);
    }

    public function recommendedQuote(): ?QuotationCompany
    {
        return $this->quotationCompanies()->where('is_recommended', true)->first();
    }

    public function bestQuote(): ?QuotationCompany
    {
        return $this->quotationCompanies()->orderBy('final_premium')->first();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function isVehicleInsurance(): bool
    {
        return ! empty($this->vehicle_number);
    }

    public function getQuoteReference(): string
    {
        return 'QT/'.date('y').'/'.str_pad($this->id, 8, '0', STR_PAD_LEFT);
    }
}
