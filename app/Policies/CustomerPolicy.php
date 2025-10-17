<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Determine whether the customer can view any customers (family members).
     */
    public function viewAny(Customer $customer): bool
    {
        return $customer->hasFamily();
    }

    /**
     * Determine whether the customer can view another customer's profile.
     */
    public function view(Customer $currentCustomer, Customer $targetCustomer): bool
    {
        if ($currentCustomer->id === $targetCustomer->id) {
            return true;
        }

        return $currentCustomer->hasFamily() &&
               $currentCustomer->isInSameFamilyAs($targetCustomer);
    }

    /**
     * Determine whether the customer can view a specific insurance policy.
     */
    public function viewPolicy(Customer $customer, CustomerInsurance $policy): bool
    {
        if ($policy->customer_id === $customer->id) {
            return true;
        }

        if (! $customer->hasFamily()) {
            return false;
        }

        $viewablePolicyIds = $customer->getViewableInsurance()->pluck('id');

        return $viewablePolicyIds->contains($policy->id);
    }

    /**
     * Determine whether the customer can download a policy document.
     */
    public function downloadPolicy(Customer $customer, CustomerInsurance $policy): bool
    {
        return $this->viewPolicy($customer, $policy);
    }

    /**
     * Determine whether the customer can view family data.
     */
    public function viewFamilyData(Customer $customer): bool
    {
        return $customer->hasFamily() && $customer->status;
    }

    /**
     * Determine whether the customer can view all family policies (family head privilege).
     */
    public function viewAllFamilyPolicies(Customer $customer): bool
    {
        return $customer->hasFamily() && $customer->isFamilyHead();
    }

    /**
     * Determine whether the customer can change their password.
     */
    public function changePassword(Customer $customer): bool
    {
        return $customer->status;
    }

    /**
     * Admin-only methods for admin users
     */
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Customer $customer): bool
    {
        return true;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return true;
    }

    public function restore(User $user, Customer $customer): bool
    {
        return true;
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return true;
    }
}
