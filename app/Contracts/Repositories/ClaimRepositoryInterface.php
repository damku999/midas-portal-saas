<?php

namespace App\Contracts\Repositories;

use App\Models\Claim;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Claim Repository Interface
 *
 * Defines methods for Claim data access operations.
 * Extends BaseRepositoryInterface for common CRUD operations.
 */
interface ClaimRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get paginated list of claims with advanced filtering and search
     */
    public function getClaimsWithFilters(Request $request, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get claims by status
     */
    public function getClaimsByStatus(bool $status): Collection;

    /**
     * Get claims by insurance type
     */
    public function getClaimsByInsuranceType(string $insuranceType): Collection;

    /**
     * Get claims within date range
     */
    public function getClaimsByDateRange(string $dateFrom, string $dateTo): Collection;

    /**
     * Get claim statistics for dashboard
     */
    public function getClaimStatistics(): array;

    /**
     * Search claims by multiple criteria
     */
    public function searchClaims(string $searchTerm, int $limit = 20): Collection;

    /**
     * Get claims for specific customer
     */
    public function getClaimsByCustomer(int $customerId): Collection;

    /**
     * Get claims for specific customer insurance
     */
    public function getClaimsByCustomerInsurance(int $customerInsuranceId): Collection;

    /**
     * Generate next claim number
     */
    public function generateClaimNumber(): string;
}
