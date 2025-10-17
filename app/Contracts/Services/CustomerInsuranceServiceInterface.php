<?php

namespace App\Contracts\Services;

use App\Models\CustomerInsurance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface CustomerInsuranceServiceInterface
{
    /**
     * Get paginated customer insurances with filters
     */
    public function getCustomerInsurances(Request $request): LengthAwarePaginator;

    /**
     * Get data for create/edit forms
     */
    public function getFormData(): array;

    /**
     * Get validation rules for store operation
     */
    public function getStoreValidationRules(): array;

    /**
     * Get validation rules for update operation
     */
    public function getUpdateValidationRules(): array;

    /**
     * Get validation rules for renewal operation
     */
    public function getRenewalValidationRules(): array;

    /**
     * Prepare data for storage from request
     */
    public function prepareStorageData(Request $request): array;

    /**
     * Create customer insurance with business logic
     */
    public function createCustomerInsurance(array $data): CustomerInsurance;

    /**
     * Update customer insurance with business logic
     */
    public function updateCustomerInsurance(CustomerInsurance $customerInsurance, array $data): CustomerInsurance;

    /**
     * Delete customer insurance with cleanup
     */
    public function deleteCustomerInsurance(CustomerInsurance $customerInsurance): bool;

    /**
     * Update status with validation
     */
    public function updateStatus(int $customerInsuranceId, int $status): bool;

    /**
     * Renew policy with full business logic
     */
    public function renewPolicy(CustomerInsurance $customerInsurance, array $data): CustomerInsurance;

    /**
     * Handle file uploads with naming conventions
     */
    public function handleFileUpload(Request $request, CustomerInsurance $customerInsurance): void;

    /**
     * Send WhatsApp document with message
     */
    public function sendWhatsAppDocument(CustomerInsurance $customerInsurance): bool;

    /**
     * Send renewal reminder via WhatsApp
     */
    public function sendRenewalReminderWhatsApp(CustomerInsurance $customerInsurance): bool;

    /**
     * Export customer insurances to Excel
     */
    public function exportCustomerInsurances(): \Symfony\Component\HttpFoundation\BinaryFileResponse;

    /**
     * Get customer policies by customer ID
     */
    public function getCustomerPolicies(int $customerId): Collection;

    /**
     * Get expiring policies within specified days
     */
    public function getExpiringPolicies(int $days = 30): Collection;

    /**
     * Calculate commission breakdown for policy
     */
    public function calculateCommissionBreakdown(CustomerInsurance $customerInsurance): array;
}
