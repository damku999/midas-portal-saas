<?php

namespace App\Models;

use App\Helpers\DateHelper;
use App\Traits\TableRecordObserver;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
/**
 * App\Models\Report
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property array|null $selected_columns
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\User|null $user
 *
 * @method static ReportFactory factory($count = null, $state = [])
 * @method static Builder|Report newModelQuery()
 * @method static Builder|Report newQuery()
 * @method static Builder|Report onlyTrashed()
 * @method static Builder|Report permission($permissions)
 * @method static Builder|Report query()
 * @method static Builder|Report role($roles, $guard = null)
 * @method static Builder|Report whereCreatedAt($value)
 * @method static Builder|Report whereCreatedBy($value)
 * @method static Builder|Report whereDeletedAt($value)
 * @method static Builder|Report whereDeletedBy($value)
 * @method static Builder|Report whereId($value)
 * @method static Builder|Report whereName($value)
 * @method static Builder|Report whereSelectedColumns($value)
 * @method static Builder|Report whereUpdatedAt($value)
 * @method static Builder|Report whereUpdatedBy($value)
 * @method static Builder|Report whereUserId($value)
 * @method static Builder|Report withTrashed()
 * @method static Builder|Report withoutTrashed()
 *
 * @mixin Model
 */
class Report extends Authenticatable
{
    use HasFactory;
    use HasRoles;
    use LogsActivity;
    use SoftDeletes;
    use TableRecordObserver;

    protected $guarded = [];

    protected static $logName = 'User Reports';

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    protected $casts = ['selected_columns' => 'array'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function getInsuranceReport(array $filters)
    {

        // Note: Column selection will be handled by frontend/views,
        // this method just retrieves the data with all necessary relationships

        $customerInsurances = CustomerInsurance::with(
            'branch',
            'broker',
            'relationshipManager',
            'customer',
            'insuranceCompany',
            'premiumType',
            'policyType',
            'fuelType'
        )
            ->unless(empty($filters['record_creation_start_date']), static function ($query) use ($filters) {
                $startDate = DateHelper::isValidDatabaseFormat($filters['record_creation_start_date'])
                    ? $filters['record_creation_start_date']
                    : formatDateForDatabase($filters['record_creation_start_date']);

                return $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay()->format('Y-m-d H:i:s'));
            })
            ->unless(empty($filters['record_creation_end_date']), static function ($query) use ($filters) {
                $endDate = DateHelper::isValidDatabaseFormat($filters['record_creation_end_date'])
                    ? $filters['record_creation_end_date']
                    : formatDateForDatabase($filters['record_creation_end_date']);

                return $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay()->format('Y-m-d H:i:s'));
            })
            ->unless(empty($filters['issue_start_date']), static function ($query) use ($filters) {
                try {
                    $startDate = Carbon::createFromFormat('d/m/Y', $filters['issue_start_date'])->format('Y-m-d');

                    return $query->where('issue_date', '>=', $startDate);
                } catch (\Exception) {
                    return $query->where('issue_date', '>=', $filters['issue_start_date']);
                }
            })
            ->unless(empty($filters['issue_end_date']), static function ($query) use ($filters) {
                try {
                    $endDate = Carbon::createFromFormat('d/m/Y', $filters['issue_end_date'])->format('Y-m-d');

                    return $query->where('issue_date', '<=', $endDate);
                } catch (\Exception) {
                    return $query->where('issue_date', '<=', $filters['issue_end_date']);
                }
            })
            ->unless(empty($filters['broker_id']), fn ($query) => $query->whereHas('broker', static function ($query) use ($filters): void {
                $query->where('id', $filters['broker_id']);
            }))
            ->unless(empty($filters['relationship_manager_id']), fn ($query) => $query->whereHas('relationshipManager', static function ($query) use ($filters): void {
                $query->where('id', $filters['relationship_manager_id']);
            }))
            ->unless(empty($filters['insurance_company_id']), fn ($query) => $query->whereHas('insuranceCompany', static function ($query) use ($filters): void {
                $query->where('id', $filters['insurance_company_id']);
            }))
            ->unless(empty($filters['policy_type_id']), fn ($query) => $query->whereHas('policyType', static function ($query) use ($filters): void {
                $query->where('id', $filters['policy_type_id']);
            }))
            ->unless(empty($filters['fuel_type_id']), fn ($query) => $query->whereHas('fuelType', static function ($query) use ($filters): void {
                $query->where('id', $filters['fuel_type_id']);
            }))
            ->unless(empty($filters['premium_type_id']), fn ($query) => $query->whereHas('premiumType', static function ($query) use ($filters): void {
                if (is_array($filters['premium_type_id'])) {
                    $query->whereIn('id', $filters['premium_type_id']);
                } else {
                    $query->where('id', $filters['premium_type_id']);
                }
            }))
            ->unless(empty($filters['customer_id']), fn ($query) => $query->whereHas('customer', static function ($query) use ($filters): void {
                $query->where('id', $filters['customer_id']);
            }))
            ->unless(empty($filters['due_start_date']), static function ($query) use ($filters) {
                // Due dates can be in m/Y or mm/Y format, handle both
                try {
                    $dateStr = trim((string) $filters['due_start_date']);
                    $startDate = null;
                    $usedFormat = null;

                    // Try parsing with different formats
                    foreach (['m/Y', 'n/Y', 'Y-m', 'd/m/Y', 'M Y', 'F Y'] as $format) {
                        try {
                            $parsed = Carbon::createFromFormat($format, $dateStr);
                            if ($parsed && $parsed->year > 2000 && $parsed->year < 2100) {
                                $startDate = $parsed;
                                $usedFormat = $format;
                                break;
                            }
                        } catch (\Exception) {
                            continue;
                        }
                    }

                    if (! $startDate) {
                        throw new \Exception('Unable to parse date: '.$dateStr);
                    }

                    $formattedDate = $startDate->startOfMonth()->format('Y-m-d');

                    Log::info('✅ Due start date filter applied', [
                        'input' => $dateStr,
                        'format_used' => $usedFormat,
                        'parsed' => $formattedDate,
                        'query' => 'expired_date >= '.$formattedDate,
                    ]);

                    return $query->where('expired_date', '>=', $formattedDate);
                } catch (\Exception $exception) {
                    Log::error('❌ Due start date parsing failed', [
                        'input' => $filters['due_start_date'] ?? 'null',
                        'error' => $exception->getMessage(),
                    ]);

                    return $query;
                }
            })
            ->unless(empty($filters['due_end_date']), static function ($query) use ($filters) {
                // Due dates can be in m/Y or mm/Y format, handle both
                try {
                    $dateStr = trim((string) $filters['due_end_date']);
                    $endDate = null;
                    $usedFormat = null;

                    // Try parsing with different formats
                    foreach (['m/Y', 'n/Y', 'Y-m', 'd/m/Y', 'M Y', 'F Y'] as $format) {
                        try {
                            $parsed = Carbon::createFromFormat($format, $dateStr);
                            if ($parsed && $parsed->year > 2000 && $parsed->year < 2100) {
                                $endDate = $parsed;
                                $usedFormat = $format;
                                break;
                            }
                        } catch (\Exception) {
                            continue;
                        }
                    }

                    if (! $endDate) {
                        throw new \Exception('Unable to parse date: '.$dateStr);
                    }

                    $formattedDate = $endDate->endOfMonth()->format('Y-m-d');

                    Log::info('✅ Due end date filter applied', [
                        'input' => $dateStr,
                        'format_used' => $usedFormat,
                        'parsed' => $formattedDate,
                        'query' => 'expired_date <= '.$formattedDate,
                    ]);

                    return $query->where('expired_date', '<=', $formattedDate);
                } catch (\Exception $exception) {
                    Log::error('❌ Due end date parsing failed', [
                        'input' => $filters['due_end_date'] ?? 'null',
                        'error' => $exception->getMessage(),
                    ]);

                    return $query;
                }
            })
            ->unless(empty($filters['status']), static function ($query) use ($filters) {
                if ($filters['status'] === 'active') {
                    return $query->where('status', 1);
                }
                if ($filters['status'] === 'inactive') {
                    return $query->where('status', 0);
                }

                return $query;
            })
            ->unless(empty($filters['premium_amount_min']), fn ($query) => $query->where('final_premium_with_gst', '>=', $filters['premium_amount_min']))
            ->unless(empty($filters['premium_amount_max']), fn ($query) => $query->where('final_premium_with_gst', '<=', $filters['premium_amount_max']))
            ->get();

        return $customerInsurances;
    }
}
