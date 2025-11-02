<?php

namespace App\Models;

use App\Traits\TableRecordObserver;
use Database\Factories\ClaimLiabilityDetailFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
/**
 * App\Models\ClaimLiabilityDetail
 *
 * @property int $id
 * @property int $claim_id
 * @property string $claim_type
 * @property string|null $claim_amount
 * @property string|null $salvage_amount
 * @property string|null $less_claim_charge
 * @property string|null $amount_to_be_paid
 * @property string|null $less_salvage_amount
 * @property string|null $less_deductions
 * @property string|null $claim_amount_received
 * @property string|null $hospital_name
 * @property string|null $hospital_address
 * @property string|null $garage_name
 * @property string|null $garage_address
 * @property string|null $estimated_amount
 * @property string|null $approved_amount
 * @property string|null $final_amount
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Claim|null $claim
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 *
 * @method static ClaimLiabilityDetailFactory factory($count = null, $state = [])
 * @method static Builder|ClaimLiabilityDetail newModelQuery()
 * @method static Builder|ClaimLiabilityDetail newQuery()
 * @method static Builder|ClaimLiabilityDetail onlyTrashed()
 * @method static Builder|ClaimLiabilityDetail permission($permissions)
 * @method static Builder|ClaimLiabilityDetail query()
 * @method static Builder|ClaimLiabilityDetail role($roles, $guard = null)
 * @method static Builder|ClaimLiabilityDetail whereAmountToBePaid($value)
 * @method static Builder|ClaimLiabilityDetail whereApprovedAmount($value)
 * @method static Builder|ClaimLiabilityDetail whereClaimAmount($value)
 * @method static Builder|ClaimLiabilityDetail whereClaimAmountReceived($value)
 * @method static Builder|ClaimLiabilityDetail whereClaimId($value)
 * @method static Builder|ClaimLiabilityDetail whereClaimType($value)
 * @method static Builder|ClaimLiabilityDetail whereCreatedAt($value)
 * @method static Builder|ClaimLiabilityDetail whereCreatedBy($value)
 * @method static Builder|ClaimLiabilityDetail whereDeletedAt($value)
 * @method static Builder|ClaimLiabilityDetail whereDeletedBy($value)
 * @method static Builder|ClaimLiabilityDetail whereEstimatedAmount($value)
 * @method static Builder|ClaimLiabilityDetail whereFinalAmount($value)
 * @method static Builder|ClaimLiabilityDetail whereGarageAddress($value)
 * @method static Builder|ClaimLiabilityDetail whereGarageName($value)
 * @method static Builder|ClaimLiabilityDetail whereHospitalAddress($value)
 * @method static Builder|ClaimLiabilityDetail whereHospitalName($value)
 * @method static Builder|ClaimLiabilityDetail whereId($value)
 * @method static Builder|ClaimLiabilityDetail whereLessClaimCharge($value)
 * @method static Builder|ClaimLiabilityDetail whereLessDeductions($value)
 * @method static Builder|ClaimLiabilityDetail whereLessSalvageAmount($value)
 * @method static Builder|ClaimLiabilityDetail whereNotes($value)
 * @method static Builder|ClaimLiabilityDetail whereSalvageAmount($value)
 * @method static Builder|ClaimLiabilityDetail whereUpdatedAt($value)
 * @method static Builder|ClaimLiabilityDetail whereUpdatedBy($value)
 * @method static Builder|ClaimLiabilityDetail withTrashed()
 * @method static Builder|ClaimLiabilityDetail withoutTrashed()
 *
 * @mixin Model
 */
class ClaimLiabilityDetail extends Model
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use LogsActivity;
    use SoftDeletes;
    use TableRecordObserver;

    protected $fillable = [
        'claim_id',
        'claim_type',
        'hospital_name',
        'hospital_address',
        'garage_name',
        'garage_address',
        'estimated_amount',
        'approved_amount',
        'final_amount',
        'claim_amount',
        'salvage_amount',
        'less_claim_charge',
        'amount_to_be_paid',
        'less_salvage_amount',
        'less_deductions',
        'claim_amount_received',
        'notes',
    ];

    protected $casts = [
        'estimated_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    /**
     * Get the claim that owns the liability detail.
     */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    /**
     * Check if this is a cashless claim.
     */
    public function isCashless(): bool
    {
        return $this->claim_type === 'Cashless';
    }

    /**
     * Check if this is a reimbursement claim.
     */
    public function isReimbursement(): bool
    {
        return $this->claim_type === 'Reimbursement';
    }

    /**
     * Get the facility name (hospital or garage).
     */
    public function getFacilityName(): ?string
    {
        return $this->hospital_name ?? $this->garage_name;
    }

    /**
     * Get the facility address.
     */
    public function getFacilityAddress(): ?string
    {
        return $this->hospital_address ?? $this->garage_address;
    }

    /**
     * Get formatted estimated amount.
     */
    public function getFormattedEstimatedAmount(): string
    {
        return $this->estimated_amount ? '₹'.number_format($this->estimated_amount, 2) : '-';
    }

    /**
     * Get formatted approved amount.
     */
    public function getFormattedApprovedAmount(): string
    {
        return $this->approved_amount ? '₹'.number_format($this->approved_amount, 2) : '-';
    }

    /**
     * Get formatted final amount.
     */
    public function getFormattedFinalAmount(): string
    {
        return $this->final_amount ? '₹'.number_format($this->final_amount, 2) : '-';
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
