<?php

namespace App\Repositories\Contracts;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface LeadRepositoryInterface
{
    /**
     * Get all leads with optional filters
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find lead by ID
     */
    public function findById(int $id): ?Lead;

    /**
     * Find lead by lead number
     */
    public function findByLeadNumber(string $leadNumber): ?Lead;

    /**
     * Create new lead
     */
    public function create(array $data): Lead;

    /**
     * Update existing lead
     */
    public function update(int $id, array $data): Lead;

    /**
     * Delete lead (soft delete)
     */
    public function delete(int $id): bool;

    /**
     * Restore soft deleted lead
     */
    public function restore(int $id): bool;

    /**
     * Force delete lead
     */
    public function forceDelete(int $id): bool;

    /**
     * Get leads by status
     */
    public function getByStatus(int $statusId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get leads by source
     */
    public function getBySource(int $sourceId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get leads assigned to user
     */
    public function getAssignedTo(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get active leads (not converted or lost)
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get converted leads
     */
    public function getConverted(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get lost leads
     */
    public function getLost(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get leads with follow-up due
     */
    public function getFollowUpDue(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get leads with overdue follow-up
     */
    public function getFollowUpOverdue(int $perPage = 15): LengthAwarePaginator;

    /**
     * Search leads by name, email, or mobile
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator;

    /**
     * Update lead status
     */
    public function updateStatus(int $id, int $statusId, ?string $notes = null): Lead;

    /**
     * Assign lead to user
     */
    public function assignTo(int $id, int $userId): Lead;

    /**
     * Convert lead to customer
     */
    public function convertToCustomer(int $id, int $customerId, ?string $notes = null): Lead;

    /**
     * Mark lead as lost
     */
    public function markAsLost(int $id, string $reason): Lead;

    /**
     * Get lead statistics
     */
    public function getStatistics(): array;

    /**
     * Get leads with relationships
     */
    public function getWithRelations(int $id, array $relations = []): ?Lead;
}
