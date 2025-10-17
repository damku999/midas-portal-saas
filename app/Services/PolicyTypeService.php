<?php

namespace App\Services;

use App\Models\PolicyType;

/**
 * Policy Type Service
 *
 * Handles PolicyType business logic operations.
 * Inherits transaction management from BaseService.
 */
class PolicyTypeService extends BaseService
{
    /**
     * Create a new policy type
     *
     * @throws \Throwable
     */
    public function createPolicyType(array $data): PolicyType
    {
        return $this->createInTransaction(
            fn () => PolicyType::query()->create($data)
        );
    }

    /**
     * Update an existing policy type
     *
     * @throws \Throwable
     */
    public function updatePolicyType(PolicyType $policyType, array $data): bool
    {
        return $this->updateInTransaction(
            fn () => $policyType->update($data)
        );
    }

    /**
     * Delete a policy type
     *
     * @throws \Throwable
     */
    public function deletePolicyType(PolicyType $policyType): bool
    {
        return $this->deleteInTransaction(
            fn () => $policyType->delete()
        );
    }

    /**
     * Update policy type status
     *
     * @throws \Throwable
     */
    public function updateStatus(int $policyTypeId, int $status): bool
    {
        return $this->executeInTransaction(
            fn () => PolicyType::whereId($policyTypeId)->update(['status' => $status])
        );
    }
}
