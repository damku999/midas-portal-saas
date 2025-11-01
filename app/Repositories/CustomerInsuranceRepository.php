<?php

namespace App\Repositories;

use App\Contracts\Repositories\CustomerInsuranceRepositoryInterface;
use App\Models\CustomerInsurance;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Customer Insurance Repository
 *
 * Extends base repository functionality for CustomerInsurance-specific operations.
 * Common CRUD operations are inherited from AbstractBaseRepository.
 */
class CustomerInsuranceRepository extends AbstractBaseRepository implements CustomerInsuranceRepositoryInterface
{
    protected string $modelClass = CustomerInsurance::class;

    protected array $searchableFields = ['policy_no', 'registration_no'];

    /**
     * Override base getPaginated to support complex joins and filtering
     */
    public function getPaginated(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $query = CustomerInsurance::select([
            'customer_insurances.*',
            'customers.name as customer_name',
            'branches.name as branch_name',
            'brokers.name as broker_name',
            'relationship_managers.name as relationship_manager_name',
            'premium_types.name AS policy_type_name',
        ])
            ->leftJoin('customers', 'customers.id', '=', 'customer_insurances.customer_id')
            ->leftJoin('branches', 'branches.id', '=', 'customer_insurances.branch_id')
            ->leftJoin('brokers', 'brokers.id', '=', 'customer_insurances.broker_id')
            ->leftJoin('relationship_managers', 'relationship_managers.id', '=', 'customer_insurances.relationship_manager_id')
            ->leftJoin('premium_types', 'premium_types.id', '=', 'customer_insurances.premium_type_id');

        // Apply filters
        if (! empty($request->search)) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'LIKE', '%'.$search.'%')
                    ->orWhere('customer_insurances.policy_no', 'LIKE', '%'.$search.'%')
                    ->orWhere('customer_insurances.registration_no', 'LIKE', '%'.$search.'%');
            });
        }

        if (! empty($request->customer_id)) {
            $query->where('customer_insurances.customer_id', $request->customer_id);
        }

        if (! empty($request->insurance_company_id)) {
            $query->where('customer_insurances.insurance_company_id', $request->insurance_company_id);
        }

        if (! empty($request->status)) {
            $query->where('customer_insurances.status', $request->status);
        }

        return $query->orderBy('customer_insurances.created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find customer insurance with specific relations.
     */
    public function findWithRelations(int $id, array $relations = []): ?CustomerInsurance
    {
        $defaultRelations = ['customer', 'insuranceCompany', 'branch', 'broker', 'relationshipManager'];
        $loadRelations = empty($relations) ? $defaultRelations : $relations;

        return CustomerInsurance::with($loadRelations)->find($id);
    }

    /**
     * Get customer insurances by customer ID.
     */
    public function getByCustomerId(int $customerId): Collection
    {
        return CustomerInsurance::where('customer_id', $customerId)
            ->with(['insuranceCompany', 'branch', 'broker'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Override getAllForExport to include relationships.
     */
    public function getAllForExport(): Collection
    {
        return CustomerInsurance::with(['customer', 'insuranceCompany', 'branch', 'broker', 'relationshipManager'])->get();
    }

    /**
     * Get expiring policies within specified days.
     */
    public function getExpiringPolicies(int $days = 30): Collection
    {
        $expiryDate = Carbon::now()->addDays($days);

        return CustomerInsurance::with(['customer', 'insuranceCompany'])
            ->where('status', 1)
            ->where('expired_date', '<=', $expiryDate)
            ->where('expired_date', '>', Carbon::now())
            ->orderBy('expired_date', 'asc')
            ->get();
    }

    /**
     * Get active customer insurances.
     */
    public function getActiveCustomerInsurances(): Collection
    {
        return CustomerInsurance::where('status', true)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get count of customer insurances within date range.
     */
    public function getCountByDateRange($startDate, $endDate): int
    {
        return CustomerInsurance::whereBetween('created_at', [$startDate, $endDate])->count();
    }

    /**
     * Get sum of a column within date range.
     */
    public function getSumByDateRange(string $column, $startDate, $endDate): float
    {
        return (float) CustomerInsurance::whereBetween('created_at', [$startDate, $endDate])
            ->sum($column) ?? 0;
    }

    /**
     * Get recent customer insurances.
     */
    public function getRecent(int $limit = 10): Collection
    {
        return CustomerInsurance::with(['customer', 'insuranceCompany'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get active policies count.
     */
    public function getActiveCount(): int
    {
        return CustomerInsurance::where('status', 1)->count();
    }

    /**
     * Get count of policies.
     */
    public function getCount(): int
    {
        return CustomerInsurance::count();
    }

    /**
     * Get policies by status.
     */
    public function getByStatus(string $status): Collection
    {
        return CustomerInsurance::where('status', $status)
            ->with(['customer', 'insuranceCompany'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get policies expiring within specified days.
     */
    public function getExpiringWithinDays(int $days): int
    {
        $expiryDate = Carbon::now()->addDays($days);

        return CustomerInsurance::where('status', 1)
            ->where('expired_date', '<=', $expiryDate)
            ->where('expired_date', '>', Carbon::now())
            ->count();
    }

    /**
     * Get new policies created this month.
     */
    public function getNewThisMonth(): int
    {
        return CustomerInsurance::whereBetween('created_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now(),
        ])->count();
    }

    /**
     * Get revenue by date range.
     */
    public function getRevenueByDateRange($startDate, $endDate): float
    {
        return (float) CustomerInsurance::whereBetween('created_at', [$startDate, $endDate])
            ->sum('final_premium_with_gst') ?? 0;
    }

    /**
     * Get policies by policy type.
     */
    public function getByPolicyType(int $policyTypeId): Collection
    {
        return CustomerInsurance::where('premium_type_id', $policyTypeId)
            ->with(['customer', 'insuranceCompany'])
            ->get();
    }

    /**
     * Get policies by branch.
     */
    public function getByBranch(int $branchId): Collection
    {
        return CustomerInsurance::where('branch_id', $branchId)
            ->with(['customer', 'insuranceCompany'])
            ->get();
    }

    /**
     * Get monthly revenue trends.
     */
    public function getMonthlyRevenueTrends(int $months = 12): array
    {
        $trends = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = Carbon::now()->subMonths($i)->startOfMonth();
            $endDate = Carbon::now()->subMonths($i)->endOfMonth();

            $revenue = $this->getRevenueByDateRange($startDate, $endDate);
            $count = $this->getCountByDateRange($startDate, $endDate);

            $trends[] = [
                'month' => $startDate->format('Y-m'),
                'month_name' => $startDate->format('M Y'),
                'revenue' => $revenue,
                'policies_count' => $count,
                'average_premium' => $count > 0 ? $revenue / $count : 0,
            ];
        }

        return $trends;
    }

    /**
     * Get top performing insurance companies.
     * Refactored: Cleaner approach with proper aggregate methods
     */
    public function getTopInsuranceCompanies(int $limit = 10): array
    {
        // Get all insurance companies with active policies
        $companies = DB::table('customer_insurances')
            ->join('insurance_companies', 'customer_insurances.insurance_company_id', '=', 'insurance_companies.id')
            ->select('insurance_companies.id', 'insurance_companies.name')
            ->where('customer_insurances.status', 1)
            ->groupBy('insurance_companies.id', 'insurance_companies.name')
            ->get();

        // Calculate aggregates for each company and map
        return $companies->map(function ($company) {
            $policies = DB::table('customer_insurances')
                ->where('insurance_company_id', $company->id)
                ->where('status', 1);

            return [
                'name' => $company->name,
                'policies_count' => $policies->count(),
                'total_revenue' => (float) $policies->sum('final_premium_with_gst') ?? 0,
                'average_premium' => (float) $policies->avg('final_premium_with_gst') ?? 0,
            ];
        })
            ->sortByDesc('total_revenue')
            ->take($limit)
            ->values()
            ->toArray();
    }
}
