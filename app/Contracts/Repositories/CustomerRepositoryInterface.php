<?php

namespace App\Contracts\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

/**
 * Customer Repository Interface
 *
 * Extends base repository functionality for Customer-specific operations.
 * Common CRUD operations are inherited from BaseRepositoryInterface.
 */
interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all customers with optional filters.
     */
    public function getAll(array $filters = []): Collection;

    /**
     * Find customer by email.
     */
    public function findByEmail(string $email): ?Customer;

    /**
     * Find customer by mobile number.
     */
    public function findByMobileNumber(string $mobileNumber): ?Customer;

    /**
     * Get customers by family group.
     */
    public function getByFamilyGroup(int $familyGroupId): Collection;

    /**
     * Get customers by type (Retail/Corporate).
     */
    public function getByType(string $type): Collection;

    /**
     * Search customers by name, email, or mobile.
     */
    public function search(string $query): Collection;

    /**
     * Check if customer exists by ID.
     */
    public function exists(int $id): bool;

    /**
     * Get customer count.
     */
    public function count(): int;

    /**
     * Get active customers
     */
    public function getActiveCustomers(): Collection;

    /**
     * Get customers with valid mobile numbers
     */
    public function getCustomersWithValidMobileNumbers(): Collection;

    /**
     * Get customers by array of IDs
     */
    public function getCustomersByIds(array $ids): Collection;
}
