<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;

/**
 * Policy Repository Interface
 *
 * Extends base repository functionality for Policy-specific operations.
 * Common CRUD operations are inherited from BaseRepositoryInterface.
 */
interface PolicyRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all policies with optional filters.
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Get policies by customer ID.
     */
    public function getByCustomer(int $customerId): Collection;

    /**
     * Get policies by insurance company.
     */
    public function getByInsuranceCompany(int $companyId): Collection;

    /**
     * Get expired policies.
     */
    public function getExpired(): Collection;

    /**
     * Get policies due for renewal within specified days.
     */
    public function getDueForRenewal(int $daysAhead = 30): Collection;

    /**
     * Get policies by family group.
     */
    public function getByFamilyGroup(int $familyGroupId): Collection;

    /**
     * Get policies by policy type.
     */
    public function getByPolicyType(int $policyTypeId): Collection;

    /**
     * Search policies by policy number or customer name.
     */
    public function search(string $query): Collection;

    /**
     * Get policy statistics.
     */
    public function getStatistics(): array;

    /**
     * Check if policy exists.
     */
    public function exists(int $id): bool;

    /**
     * Get policy count by status.
     */
    public function getCountByStatus(): array;
}
