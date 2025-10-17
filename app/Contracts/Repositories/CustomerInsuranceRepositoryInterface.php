<?php

namespace App\Contracts\Repositories;

use App\Models\CustomerInsurance;
use Illuminate\Database\Eloquent\Collection;

/**
 * Customer Insurance Repository Interface
 *
 * Extends base repository functionality for CustomerInsurance-specific operations.
 * Common CRUD operations are inherited from BaseRepositoryInterface.
 */
interface CustomerInsuranceRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get customer insurances by customer ID.
     */
    public function getByCustomerId(int $customerId): Collection;

    /**
     * Get expiring policies within specified days.
     */
    public function getExpiringPolicies(int $days = 30): Collection;

    /**
     * Find customer insurance with specific relations.
     */
    public function findWithRelations(int $id, array $relations = []): ?CustomerInsurance;

    /**
     * Get active customer insurances.
     */
    public function getActiveCustomerInsurances(): Collection;
}
