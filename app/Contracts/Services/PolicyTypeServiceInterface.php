<?php

namespace App\Contracts\Services;

use App\Models\PolicyType;

interface PolicyTypeServiceInterface
{
    /**
     * Create a new policy type
     *
     * @throws \Throwable
     */
    public function createPolicyType(array $data): PolicyType;

    /**
     * Update an existing policy type
     *
     * @throws \Throwable
     */
    public function updatePolicyType(PolicyType $policyType, array $data): bool;

    /**
     * Delete a policy type
     *
     * @throws \Throwable
     */
    public function deletePolicyType(PolicyType $policyType): bool;

    /**
     * Update policy type status
     *
     * @throws \Throwable
     */
    public function updateStatus(int $policyTypeId, int $status): bool;
}
