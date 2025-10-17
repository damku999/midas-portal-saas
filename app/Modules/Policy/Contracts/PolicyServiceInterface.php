<?php

namespace App\Modules\Policy\Contracts;

use App\Models\CustomerInsurance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface PolicyServiceInterface
{
    public function getPolicies(Request $request): LengthAwarePaginator;

    public function createPolicy(array $policyData): CustomerInsurance;

    public function updatePolicy(int $policyId, array $updateData): bool;

    public function renewPolicy(int $policyId, array $renewalData): CustomerInsurance;

    public function cancelPolicy(int $policyId, string $reason): bool;

    public function getPolicyById(int $id): ?CustomerInsurance;

    public function getActivePolicies(): Collection;

    public function getExpiringPolicies(int $daysAhead = 30): Collection;

    public function calculatePremium(array $policyData): float;

    public function calculateCommission(CustomerInsurance $policy): float;

    public function getPolicyStatistics(): array;

    public function searchPolicies(string $query): Collection;

    public function getCustomerPolicies(int $customerId): Collection;
}
