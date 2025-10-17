<?php

namespace App\Contracts\Services;

use App\Models\Customer;
use App\Models\CustomerInsurance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface PolicyServiceInterface
{
    /**
     * Get paginated list of customer insurance policies.
     */
    public function getPolicies(Request $request): LengthAwarePaginator;

    /**
     * Create a new customer insurance policy.
     */
    public function createPolicy(array $data): CustomerInsurance;

    /**
     * Update an existing policy.
     */
    public function updatePolicy(CustomerInsurance $policy, array $data): bool;

    /**
     * Get policies for a specific customer.
     */
    public function getCustomerPolicies(Customer $customer): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get policies that are due for renewal.
     */
    public function getPoliciesDueForRenewal(int $daysAhead = 30): \Illuminate\Database\Eloquent\Collection;

    /**
     * Send renewal reminder for policy.
     */
    public function sendRenewalReminder(CustomerInsurance $policy): bool;

    /**
     * Get family policies if customer is family head.
     */
    public function getFamilyPolicies(Customer $customer): \Illuminate\Database\Eloquent\Collection;

    /**
     * Check if policy belongs to customer or family.
     */
    public function canCustomerViewPolicy(Customer $customer, CustomerInsurance $policy): bool;

    /**
     * Get policy statistics for dashboard.
     */
    public function getPolicyStatistics(): array;
}
