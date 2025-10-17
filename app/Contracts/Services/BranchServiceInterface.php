<?php

namespace App\Contracts\Services;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Branch Service Interface
 *
 * Defines business logic operations for Branch management.
 * Handles branch operations, status management, and reporting.
 */
interface BranchServiceInterface
{
    /**
     * Get paginated list of branches with filters
     */
    public function getBranches(Request $request, int $perPage = 10): LengthAwarePaginator;

    /**
     * Create a new branch
     */
    public function createBranch(array $data): Branch;

    /**
     * Update an existing branch
     */
    public function updateBranch(Branch $branch, array $data): bool;

    /**
     * Delete a branch
     */
    public function deleteBranch(Branch $branch): bool;

    /**
     * Update branch status
     */
    public function updateBranchStatus(int $branchId, bool $status): bool;

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

    /**
     * Get all branches for export
     */
    public function getAllBranchesForExport(): Collection;
}
