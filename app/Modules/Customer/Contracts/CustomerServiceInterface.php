<?php

namespace App\Modules\Customer\Contracts;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface CustomerServiceInterface
{
    public function getCustomers(Request $request): LengthAwarePaginator;

    public function createCustomer(StoreCustomerRequest $request): Customer;

    public function updateCustomer(UpdateCustomerRequest $request, Customer $customer): bool;

    public function updateCustomerStatus(int $customerId, int $status): bool;

    public function deleteCustomer(Customer $customer): bool;

    public function getActiveCustomersForSelection(): Collection;

    public function getCustomersByFamily(int $familyGroupId): Collection;

    public function getCustomersByType(string $type): Collection;

    public function searchCustomers(string $query): Collection;

    public function getCustomerStatistics(): array;

    public function customerExists(int $customerId): bool;

    public function findByEmail(string $email): ?Customer;

    public function findByMobileNumber(string $mobileNumber): ?Customer;

    public function sendOnboardingMessage(Customer $customer): bool;
}
