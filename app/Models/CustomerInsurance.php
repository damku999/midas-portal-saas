<?php

namespace App\Models;

use App\Traits\TableRecordObserver;
use Database\Factories\CustomerInsuranceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * App\Models\CustomerInsurance
 *
 * @property int $id
 * @property string|null $issue_date
 * @property int|null $branch_id
 * @property int|null $broker_id
 * @property int|null $relationship_manager_id
 * @property int|null $customer_id
 * @property int|null $insurance_company_id
 * @property int|null $premium_type_id
 * @property int|null $policy_type_id
 * @property int|null $fuel_type_id
 * @property string|null $policy_no
 * @property string|null $registration_no
 * @property string|null $rto
 * @property string|null $make_model
 * @property string|null $commission_on
 * @property string|null $start_date
 * @property string|null $expired_date
 * @property string|null $tp_expiry_date
 * @property string|null $maturity_date
 * @property float|null $od_premium
 * @property float|null $tp_premium
 * @property float|null $net_premium
 * @property float|null $premium_amount
 * @property float|null $gst
 * @property float|null $final_premium_with_gst
 * @property float|null $sgst1
 * @property float|null $cgst1
 * @property float|null $cgst2
 * @property float|null $sgst2
 * @property float|null $my_commission_percentage
 * @property float|null $my_commission_amount
 * @property float|null $transfer_commission_percentage
 * @property float|null $transfer_commission_amount
 * @property float|null $reference_commission_percentage
 * @property float|null $reference_commission_amount
 * @property float|null $actual_earnings
 * @property float|null $ncb_percentage
 * @property string|null $mode_of_payment
 * @property string|null $cheque_no
 * @property string|null $policy_document_path
 * @property string|null $gross_vehicle_weight
 * @property string|null $mfg_year
 * @property int|null $reference_by
 * @property string|null $plan_name
 * @property string|null $premium_paying_term
 * @property string|null $policy_term
 * @property string|null $sum_insured
 * @property string|null $pension_amount_yearly
 * @property string|null $approx_maturity_amount
 * @property string|null $life_insurance_payment_mode
 * @property string|null $remarks
 * @property int $status
 * @property int $is_renewed
 * @property string|null $renewed_date
 * @property int|null $new_insurance_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Branch|null $branch
 * @property-read Broker|null $broker
 * @property-read Collection<int, Claim> $claims
 * @property-read int|null $claims_count
 * @property-read CommissionType|null $commissionType
 * @property-read Customer|null $customer
 * @property-read FuelType|null $fuelType
 * @property-read mixed $expired_date_formatted
 * @property-read mixed $issue_date_formatted
 * @property-read mixed $maturity_date_formatted
 * @property-read mixed $start_date_formatted
 * @property-read mixed $tp_expiry_date_formatted
 * @property-read InsuranceCompany|null $insuranceCompany
 * @property-read PolicyType|null $policyType
 * @property-read PremiumType|null $premiumType
 * @property-read RelationshipManager|null $relationshipManager
 *
 * @method static CustomerInsuranceFactory factory($count = null, $state = [])
 * @method static Builder|CustomerInsurance newModelQuery()
 * @method static Builder|CustomerInsurance newQuery()
 * @method static Builder|CustomerInsurance onlyTrashed()
 * @method static Builder|CustomerInsurance query()
 * @method static Builder|CustomerInsurance whereActualEarnings($value)
 * @method static Builder|CustomerInsurance whereApproxMaturityAmount($value)
 * @method static Builder|CustomerInsurance whereBranchId($value)
 * @method static Builder|CustomerInsurance whereBrokerId($value)
 * @method static Builder|CustomerInsurance whereCgst1($value)
 * @method static Builder|CustomerInsurance whereCgst2($value)
 * @method static Builder|CustomerInsurance whereChequeNo($value)
 * @method static Builder|CustomerInsurance whereCommissionOn($value)
 * @method static Builder|CustomerInsurance whereCreatedAt($value)
 * @method static Builder|CustomerInsurance whereCreatedBy($value)
 * @method static Builder|CustomerInsurance whereCustomerId($value)
 * @method static Builder|CustomerInsurance whereDeletedAt($value)
 * @method static Builder|CustomerInsurance whereDeletedBy($value)
 * @method static Builder|CustomerInsurance whereExpiredDate($value)
 * @method static Builder|CustomerInsurance whereFinalPremiumWithGst($value)
 * @method static Builder|CustomerInsurance whereFuelTypeId($value)
 * @method static Builder|CustomerInsurance whereGrossVehicleWeight($value)
 * @method static Builder|CustomerInsurance whereGst($value)
 * @method static Builder|CustomerInsurance whereId($value)
 * @method static Builder|CustomerInsurance whereInsuranceCompanyId($value)
 * @method static Builder|CustomerInsurance whereIsRenewed($value)
 * @method static Builder|CustomerInsurance whereIssueDate($value)
 * @method static Builder|CustomerInsurance whereLifeInsurancePaymentMode($value)
 * @method static Builder|CustomerInsurance whereMakeModel($value)
 * @method static Builder|CustomerInsurance whereMaturityDate($value)
 * @method static Builder|CustomerInsurance whereMfgYear($value)
 * @method static Builder|CustomerInsurance whereModeOfPayment($value)
 * @method static Builder|CustomerInsurance whereMyCommissionAmount($value)
 * @method static Builder|CustomerInsurance whereMyCommissionPercentage($value)
 * @method static Builder|CustomerInsurance whereNcbPercentage($value)
 * @method static Builder|CustomerInsurance whereNetPremium($value)
 * @method static Builder|CustomerInsurance whereNewInsuranceId($value)
 * @method static Builder|CustomerInsurance whereOdPremium($value)
 * @method static Builder|CustomerInsurance wherePensionAmountYearly($value)
 * @method static Builder|CustomerInsurance wherePlanName($value)
 * @method static Builder|CustomerInsurance wherePolicyDocumentPath($value)
 * @method static Builder|CustomerInsurance wherePolicyNo($value)
 * @method static Builder|CustomerInsurance wherePolicyTerm($value)
 * @method static Builder|CustomerInsurance wherePolicyTypeId($value)
 * @method static Builder|CustomerInsurance wherePremiumAmount($value)
 * @method static Builder|CustomerInsurance wherePremiumPayingTerm($value)
 * @method static Builder|CustomerInsurance wherePremiumTypeId($value)
 * @method static Builder|CustomerInsurance whereReferenceBy($value)
 * @method static Builder|CustomerInsurance whereReferenceCommissionAmount($value)
 * @method static Builder|CustomerInsurance whereReferenceCommissionPercentage($value)
 * @method static Builder|CustomerInsurance whereRegistrationNo($value)
 * @method static Builder|CustomerInsurance whereRelationshipManagerId($value)
 * @method static Builder|CustomerInsurance whereRemarks($value)
 * @method static Builder|CustomerInsurance whereRenewedDate($value)
 * @method static Builder|CustomerInsurance whereRto($value)
 * @method static Builder|CustomerInsurance whereSgst1($value)
 * @method static Builder|CustomerInsurance whereSgst2($value)
 * @method static Builder|CustomerInsurance whereStartDate($value)
 * @method static Builder|CustomerInsurance whereStatus($value)
 * @method static Builder|CustomerInsurance whereSumInsured($value)
 * @method static Builder|CustomerInsurance whereTpExpiryDate($value)
 * @method static Builder|CustomerInsurance whereTpPremium($value)
 * @method static Builder|CustomerInsurance whereTransferCommissionAmount($value)
 * @method static Builder|CustomerInsurance whereTransferCommissionPercentage($value)
 * @method static Builder|CustomerInsurance whereUpdatedAt($value)
 * @method static Builder|CustomerInsurance whereUpdatedBy($value)
 * @method static Builder|CustomerInsurance withTrashed()
 * @method static Builder|CustomerInsurance withoutTrashed()
 *
 * @mixin Model
 */
class CustomerInsurance extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;
    use TableRecordObserver;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'issue_date',
        'branch_id',
        'broker_id',
        'relationship_manager_id',
        'customer_id',
        'insurance_company_id',
        'premium_type_id',
        'policy_type_id',
        'fuel_type_id',
        'policy_no',
        'registration_no',
        'rto',
        'make_model',
        'commission_on',
        'start_date',
        'expired_date',
        'tp_expiry_date',
        'maturity_date',
        'od_premium',
        'tp_premium',
        'net_premium',
        'premium_amount',
        'gst',
        'final_premium_with_gst',
        'sgst1',
        'cgst1',
        'cgst2',
        'sgst2',
        'my_commission_percentage',
        'my_commission_amount',
        'transfer_commission_percentage',
        'transfer_commission_amount',
        'reference_commission_percentage',
        'reference_commission_amount',
        'actual_earnings',
        'ncb_percentage',
        'mode_of_payment',
        'cheque_no',
        'insurance_status',
        'policy_document_path',
        'gross_vehicle_weight',
        'mfg_year',
        'reference_by',
        'plan_name',
        'premium_paying_term',
        'policy_term',
        'sum_insured',
        'pension_amount_yearly',
        'approx_maturity_amount',
        'life_insurance_payment_mode',
        'remarks',
        'status',
    ];

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    // Define the relationships here
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function broker()
    {
        return $this->belongsTo(Broker::class, 'broker_id');
    }

    public function relationshipManager()
    {
        return $this->belongsTo(RelationshipManager::class, 'relationship_manager_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function insuranceCompany()
    {
        return $this->belongsTo(InsuranceCompany::class, 'insurance_company_id');
    }

    public function premiumType()
    {
        return $this->belongsTo(PremiumType::class, 'premium_type_id');
    }

    public function policyType()
    {
        return $this->belongsTo(PolicyType::class, 'policy_type_id');
    }

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class, 'fuel_type_id');
    }

    public function commissionType()
    {
        return $this->belongsTo(CommissionType::class, 'commission_type_id');
    }

    /**
     * Get all claims for this insurance policy.
     */
    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    // =======================================================
    // DATE FORMATTING ACCESSORS & MUTATORS
    // =======================================================

    /**
     * Get issue date in UI format (d/m/Y)
     */
    protected function getIssueDateFormattedAttribute()
    {
        return formatDateForUi($this->issue_date);
    }

    /**
     * Set issue date from UI format (d/m/Y) to database format (Y-m-d)
     */
    protected function setIssueDateAttribute($value)
    {
        $this->attributes['issue_date'] = formatDateForDatabase($value);
    }

    /**
     * Get start date in UI format (d/m/Y)
     */
    protected function getStartDateFormattedAttribute()
    {
        return formatDateForUi($this->start_date);
    }

    /**
     * Set start date from UI format (d/m/Y) to database format (Y-m-d)
     */
    protected function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = formatDateForDatabase($value);
    }

    /**
     * Get expired date in UI format (d/m/Y)
     */
    protected function getExpiredDateFormattedAttribute()
    {
        return formatDateForUi($this->expired_date);
    }

    /**
     * Set expired date from UI format (d/m/Y) to database format (Y-m-d)
     */
    protected function setExpiredDateAttribute($value)
    {
        $this->attributes['expired_date'] = formatDateForDatabase($value);
    }

    /**
     * Get TP expiry date in UI format (d/m/Y)
     */
    protected function getTpExpiryDateFormattedAttribute()
    {
        return formatDateForUi($this->tp_expiry_date);
    }

    /**
     * Set TP expiry date from UI format (d/m/Y) to database format (Y-m-d)
     */
    protected function setTpExpiryDateAttribute($value)
    {
        $this->attributes['tp_expiry_date'] = formatDateForDatabase($value);
    }

    /**
     * Get maturity date in UI format (d/m/Y)
     */
    protected function getMaturityDateFormattedAttribute()
    {
        return formatDateForUi($this->maturity_date);
    }

    /**
     * Set maturity date from UI format (d/m/Y) to database format (Y-m-d)
     */
    protected function setMaturityDateAttribute($value)
    {
        $this->attributes['maturity_date'] = formatDateForDatabase($value);
    }
}
