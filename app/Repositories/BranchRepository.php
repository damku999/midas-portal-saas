<?php

namespace App\Repositories;

use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Branch Repository
 *
 * Handles Branch data access operations.
 * Inherits common CRUD operations from AbstractBaseRepository.
 */
class BranchRepository extends AbstractBaseRepository implements BranchRepositoryInterface
{
    /**
     * The model class name
     */
    protected string $modelClass = Branch::class;

    /**
     * Searchable fields for the getPaginated method
     */
    protected array $searchableFields = [
        'name', 'email', 'mobile_number',
    ];

    /**
     * Get paginated list of branches with filtering and search
     */
    public function getBranchesWithFilters(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = Branch::withCount('customerInsurances');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get branches by status
     */
    public function getBranchesByStatus(bool $status): Collection
    {
        return Branch::where('status', $status)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active branches for dropdown/select options
     */
    public function getActiveBranches(): Collection
    {
        return Branch::where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Get branch with customer insurances count
     */
    public function getBranchWithInsurancesCount(int $branchId): ?Branch
    {
        return Branch::withCount('customerInsurances')
            ->find($branchId);
    }

    /**
     * Search branches by name
     */
    public function searchBranches(string $searchTerm, int $limit = 20): Collection
    {
        if (strlen($searchTerm) < 2) {
            return collect();
        }

        return Branch::where('status', true)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            })
            ->limit($limit)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get branch statistics
     */
    public function getBranchStatistics(): array
    {
        return [
            'total_branches' => Branch::count(),
            'active_branches' => Branch::where('status', true)->count(),
            'inactive_branches' => Branch::where('status', false)->count(),
            'branches_with_insurances' => Branch::has('customerInsurances')->count(),
            'total_customer_insurances' => Branch::withCount('customerInsurances')
                ->get()
                ->sum('customer_insurances_count'),
            'this_month_branches' => Branch::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }
}
