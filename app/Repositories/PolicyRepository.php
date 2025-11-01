<?php

namespace App\Repositories;

use App\Contracts\Repositories\PolicyRepositoryInterface;
use App\Models\CustomerInsurance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Policy Repository
 *
 * Extends base repository functionality for Policy-specific operations.
 * Common CRUD operations are inherited from AbstractBaseRepository.
 */
class PolicyRepository extends AbstractBaseRepository implements PolicyRepositoryInterface
{
    protected string $modelClass = CustomerInsurance::class;

    protected array $searchableFields = ['policy_number'];

    public function getAll(array $filters = []): Collection
    {
        $query = CustomerInsurance::with(['customer', 'insuranceCompany', 'policyType', 'premiumType']);

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['insurance_company_id'])) {
            $query->where('insurance_company_id', $filters['insurance_company_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->get();
    }

    /**
     * Override base getPaginated to support complex filtering with relationships
     */
    public function getPaginated(Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $filters = $request->all();
        $query = CustomerInsurance::with(['customer', 'insuranceCompany', 'policyType', 'premiumType']);

        // Search filter
        if (! empty($filters['search'])) {
            $searchTerm = '%'.trim($filters['search']).'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('policy_number', 'LIKE', $searchTerm)
                    ->orWhereHas('customer', function ($customerQuery) use ($searchTerm) {
                        $customerQuery->where('name', 'LIKE', $searchTerm)
                            ->orWhere('mobile_number', 'LIKE', $searchTerm);
                    });
            });
        }

        // Customer filter
        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        // Insurance company filter
        if (! empty($filters['insurance_company_id'])) {
            $query->where('insurance_company_id', $filters['insurance_company_id']);
        }

        // Status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Policy type filter
        if (! empty($filters['policy_type_id'])) {
            $query->where('policy_type_id', $filters['policy_type_id']);
        }

        // Date range filter
        if (! empty($filters['from_date']) && ! empty($filters['to_date'])) {
            $query->whereBetween('created_at', [$filters['from_date'], $filters['to_date']]);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Override findById to include relationships
     */
    public function findById(int $id)
    {
        return CustomerInsurance::with(['customer', 'insuranceCompany', 'policyType', 'premiumType'])
            ->find($id);
    }

    /**
     * Override update method to match interface signature
     */
    public function update($entity, array $data)
    {
        if (is_int($entity)) {
            return CustomerInsurance::whereId($entity)->update($data);
        }

        return parent::update($entity, $data);
    }

    /**
     * Override delete method to match interface signature
     */
    public function delete($entity): bool
    {
        if (is_int($entity)) {
            return CustomerInsurance::whereId($entity)->delete();
        }

        return parent::delete($entity);
    }

    public function getByCustomer(int $customerId): Collection
    {
        return CustomerInsurance::with(['insuranceCompany', 'policyType', 'premiumType'])
            ->where('customer_id', $customerId)
            ->latest()
            ->get();
    }

    public function getByInsuranceCompany(int $companyId): Collection
    {
        return CustomerInsurance::with(['customer', 'policyType', 'premiumType'])
            ->where('insurance_company_id', $companyId)
            ->latest()
            ->get();
    }

    /**
     * Override getActive to include policy-specific logic
     */
    public function getActive(): Collection
    {
        return CustomerInsurance::with(['customer', 'insuranceCompany', 'policyType'])
            ->where('status', 1)
            ->where('policy_end_date', '>', now())
            ->get();
    }

    public function getExpired(): Collection
    {
        return CustomerInsurance::with(['customer', 'insuranceCompany', 'policyType'])
            ->where('policy_end_date', '<=', now())
            ->get();
    }

    public function getDueForRenewal(int $daysAhead = 30): Collection
    {
        $targetDate = Carbon::now()->addDays($daysAhead);

        return CustomerInsurance::with(['customer', 'insuranceCompany', 'policyType'])
            ->where('status', 1)
            ->where('policy_end_date', '>', now())
            ->where('policy_end_date', '<=', $targetDate)
            ->orderBy('policy_end_date')
            ->get();
    }

    public function getByFamilyGroup(int $familyGroupId): Collection
    {
        return CustomerInsurance::with(['customer', 'insuranceCompany', 'policyType'])
            ->whereHas('customer', function ($query) use ($familyGroupId) {
                $query->where('family_group_id', $familyGroupId);
            })
            ->latest()
            ->get();
    }

    public function getByPolicyType(int $policyTypeId): Collection
    {
        return CustomerInsurance::with(['customer', 'insuranceCompany'])
            ->where('policy_type_id', $policyTypeId)
            ->latest()
            ->get();
    }

    public function search(string $query): Collection
    {
        $searchTerm = '%'.trim($query).'%';

        return CustomerInsurance::with(['customer', 'insuranceCompany', 'policyType'])
            ->where(function ($q) use ($searchTerm) {
                $q->where('policy_number', 'LIKE', $searchTerm)
                    ->orWhereHas('customer', function ($customerQuery) use ($searchTerm) {
                        $customerQuery->where('name', 'LIKE', $searchTerm)
                            ->orWhere('mobile_number', 'LIKE', $searchTerm);
                    });
            })
            ->latest()
            ->get();
    }

    public function getStatistics(): array
    {
        $totalPolicies = CustomerInsurance::count();
        $activePolicies = CustomerInsurance::where('status', 1)
            ->where('policy_end_date', '>', now())
            ->count();
        $expiredPolicies = CustomerInsurance::where('policy_end_date', '<=', now())
            ->count();
        $renewalsDue = CustomerInsurance::where('status', 1)
            ->where('policy_end_date', '>', now())
            ->where('policy_end_date', '<=', Carbon::now()->addDays(30))
            ->count();

        return [
            'total' => $totalPolicies,
            'active' => $activePolicies,
            'expired' => $expiredPolicies,
            'renewals_due' => $renewalsDue,
        ];
    }

    public function exists(int $id): bool
    {
        return CustomerInsurance::where('id', $id)->exists();
    }

    public function getCountByStatus(): array
    {
        // Refactored: Using Eloquent groupBy with count() instead of selectRaw
        return CustomerInsurance::query()
            ->select('status')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => CustomerInsurance::where('status', $item->status)->count()];
            })
            ->toArray();
    }
}
