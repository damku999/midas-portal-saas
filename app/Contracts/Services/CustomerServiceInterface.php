<?php

namespace App\Contracts\Services;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface CustomerServiceInterface
{
    /**
     * Get paginated list of customers with filters and sorting.
     */
    public function getCustomers(Request $request): LengthAwarePaginator;

    /**
     * Create a new customer with document handling.
     */
    public function createCustomer(StoreCustomerRequest $request): Customer;

    /**
     * Update an existing customer with document handling.
     */
    public function updateCustomer(UpdateCustomerRequest $request, Customer $customer): bool;

    /**
     * Update customer status with validation.
     */
    public function updateCustomerStatus(int $customerId, int $status): bool;

    /**
     * Delete customer with proper cleanup.
     */
    public function deleteCustomer(Customer $customer): bool;

    /**
     * Handle file uploads for customer documents.
     */
    public function handleCustomerDocuments(StoreCustomerRequest|UpdateCustomerRequest $request, Customer $customer): void;

    /**
     * Send WhatsApp onboarding message to customer.
     */
    public function sendOnboardingMessage(Customer $customer): bool;

    /**
     * Get customers for dropdown/selection.
     */
    public function getActiveCustomersForSelection(): \Illuminate\Database\Eloquent\Collection;
}
