<?php

namespace App\Contracts\Repositories;

use App\Models\Customer;
use App\Models\FamilyGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Family Group Repository Interface
 *
 * Defines methods for FamilyGroup data access operations.
 * Extends BaseRepositoryInterface for common CRUD operations.
 */
interface FamilyGroupRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get paginated list of family groups with filtering and search
     */
    public function getFamilyGroupsWithFilters(Request $request, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get family group with all relationships loaded
     */
    public function getFamilyGroupWithMembers(int $familyGroupId): ?FamilyGroup;

    /**
     * Get family groups by family head
     */
    public function getFamilyGroupsByHead(int $familyHeadId): Collection;

    /**
     * Get family groups by customer (either as head or member)
     */
    public function getFamilyGroupsByCustomer(int $customerId): Collection;

    /**
     * Get available customers for family group creation
     * (customers not in any family group)
     */
    public function getAvailableCustomers(): Collection;

    /**
     * Get available customers for family group editing
     * (customers not in other family groups)
     */
    public function getAvailableCustomersForEdit(int $familyGroupId): Collection;

    /**
     * Check if customer is already in a family group
     */
    public function isCustomerInFamilyGroup(int $customerId): bool;

    /**
     * Get family members for a specific family group
     */
    public function getFamilyMembers(int $familyGroupId): Collection;

    /**
     * Update family head for a family group
     */
    public function updateFamilyHead(int $familyGroupId, int $newFamilyHeadId): bool;

    /**
     * Remove customer from family group
     */
    public function removeCustomerFromFamilyGroup(int $customerId): bool;

    /**
     * Get family group statistics
     */
    public function getFamilyGroupStatistics(): array;

    /**
     * Search family groups by name or family head
     */
    public function searchFamilyGroups(string $searchTerm, int $limit = 20): Collection;

    /**
     * Get all family groups with relationships for export
     */
    public function getAllFamilyGroupsWithRelationships(): Collection;
}
