<?php

namespace App\Contracts\Repositories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Branch Repository Interface
 *
 * Defines methods for Branch data access operations.
 * Extends BaseRepositoryInterface for common CRUD operations.
 */
interface BranchRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get paginated list of branches with filtering and search
     */
    public function getBranchesWithFilters(Request $request, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get branches by status
     */
    public function getBranchesByStatus(bool $status): Collection;

    /**
     * Get active branches for dropdown/select options
     */
    public function getActiveBranches(): Collection;

    /**
     * Get branch with customer insurances count
     */
    public function getBranchWithInsurancesCount(int $branchId): ?Branch;

    /**
     * Search branches by name
     */
    public function searchBranches(string $searchTerm, int $limit = 20): Collection;

    /**
     * Get branch statistics
     */
    public function getBranchStatistics(): array;
}
