<?php

namespace App\Services;

use App\Contracts\Repositories\PolicyRepositoryInterface;
use App\Contracts\Services\PolicyServiceInterface;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Traits\WhatsAppApiTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PolicyService extends BaseService implements PolicyServiceInterface
{
    use WhatsAppApiTrait;

    public function __construct(
        private PolicyRepositoryInterface $policyRepository
    ) {}

    /**
     * Get paginated list of policies with filtering.
     *
     * Retrieves policies with comprehensive filtering options including search,
     * customer, insurance company, policy type, status, and date range filters.
     *
     * @param  Request  $request  HTTP request with filter parameters (search, customer_id, insurance_company_id, etc.)
     * @return LengthAwarePaginator Paginated policy collection with 10 items per page
     */
    public function getPolicies(Request $request): LengthAwarePaginator
    {
        $filters = [
            'search' => $request->input('search'),
            'customer_id' => $request->input('customer_id'),
            'insurance_company_id' => $request->input('insurance_company_id'),
            'policy_type_id' => $request->input('policy_type_id'),
            'status' => $request->input('status'),
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
        ];

        return $this->policyRepository->getPaginated($filters, 10);
    }

    /**
     * Create a new insurance policy record.
     *
     * Creates a new policy within a database transaction to ensure data consistency.
     *
     * @param  array  $data  Policy data including customer, company, dates, and premium information
     * @return CustomerInsurance The newly created policy instance
     */
    public function createPolicy(array $data): CustomerInsurance
    {
        return $this->createInTransaction(
            fn (): Model => $this->policyRepository->create($data)
        );
    }

    /**
     * Update existing insurance policy information.
     *
     * Updates policy data within a transaction to maintain data integrity.
     *
     * @param  CustomerInsurance  $customerInsurance  The policy instance to update
     * @param  array  $data  Updated policy data
     * @return bool True if update successful, false otherwise
     */
    public function updatePolicy(CustomerInsurance $customerInsurance, array $data): bool
    {
        return $this->updateInTransaction(
            fn (): Model => $this->policyRepository->update($customerInsurance, $data)
        );
    }

    /**
     * Get all policies belonging to a specific customer.
     *
     * Retrieves customer's insurance policies for portfolio view and management.
     *
     * @param  Customer  $customer  The customer to retrieve policies for
     * @return Collection Collection of customer's insurance policies
     */
    public function getCustomerPolicies(Customer $customer): Collection
    {
        return $this->policyRepository->getByCustomer($customer->id);
    }

    /**
     * Get policies expiring within specified days for renewal processing.
     *
     * Identifies policies approaching expiration to trigger renewal reminders
     * and proactive customer outreach.
     *
     * @param  int  $daysAhead  Number of days ahead to check for expiring policies (default 30)
     * @return Collection Collection of policies due for renewal
     */
    public function getPoliciesDueForRenewal(int $daysAhead = 30): Collection
    {
        return $this->policyRepository->getDueForRenewal($daysAhead);
    }

    /**
     * Send renewal reminder WhatsApp message to policy holder.
     *
     * Sends contextual renewal reminder based on days remaining until policy expiration.
     * Uses notification template system with dynamic message selection (30/15/7 days or expired).
     * Logs send status for tracking renewal campaign effectiveness.
     *
     * @param  CustomerInsurance  $customerInsurance  The policy requiring renewal reminder
     * @return bool True if message sent successfully, false otherwise
     */
    public function sendRenewalReminder(CustomerInsurance $customerInsurance): bool
    {
        try {
            // Determine notification type based on days remaining
            $daysRemaining = now()->diffInDays($customerInsurance->policy_end_date);

            if ($daysRemaining <= 0) {
                $notificationTypeCode = 'renewal_expired';
            } elseif ($daysRemaining <= 7) {
                $notificationTypeCode = 'renewal_7_days';
            } elseif ($daysRemaining <= 15) {
                $notificationTypeCode = 'renewal_15_days';
            } else {
                $notificationTypeCode = 'renewal_30_days';
            }

            // Try to get message from template, fallback to hardcoded
            $templateService = app(TemplateService::class);
            $message = $templateService->renderFromInsurance($notificationTypeCode, 'whatsapp', $customerInsurance);

            if (! $message) {
                // Fallback to old hardcoded message
                $message = $this->generateRenewalReminderMessage($customerInsurance);
            }

            $result = $this->whatsAppSendMessage($message, $customerInsurance->customer->mobile_number);

            if ($result) {
                Log::info('Renewal reminder sent successfully', [
                    'policy_id' => $customerInsurance->id,
                    'customer_id' => $customerInsurance->customer_id,
                    'policy_number' => $customerInsurance->policy_number,
                    'notification_type' => $notificationTypeCode,
                ]);
            }

            return $result;
        } catch (\Throwable $throwable) {
            Log::error('Failed to send renewal reminder', [
                'policy_id' => $customerInsurance->id,
                'customer_id' => $customerInsurance->customer_id,
                'error' => $throwable->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get all policies for a customer's family group.
     *
     * Retrieves policies for all family members if customer is family head,
     * otherwise returns only customer's own policies for privacy.
     *
     * @param  Customer  $customer  The customer to retrieve family policies for
     * @return Collection Collection of family policies or empty collection if not in family group
     */
    public function getFamilyPolicies(Customer $customer): Collection
    {
        if (! $customer->hasFamily()) {
            return collect([]);
        }

        if ($customer->isFamilyHead()) {
            return $this->policyRepository->getByFamilyGroup($customer->family_group_id);
        }

        // Non-family head customers can only see their own policies
        return $this->getCustomerPolicies($customer);
    }

    /**
     * Check if customer has permission to view specific policy.
     *
     * Enforces access control rules: customers can view their own policies,
     * and family heads can view family member policies.
     *
     * @param  Customer  $customer  The customer requesting policy access
     * @param  CustomerInsurance  $customerInsurance  The policy to check access for
     * @return bool True if customer can view policy, false otherwise
     */
    public function canCustomerViewPolicy(Customer $customer, CustomerInsurance $customerInsurance): bool
    {
        // Customer can view their own policy
        if ($customerInsurance->customer_id === $customer->id) {
            return true;
        }

        // Family head can view family member policies
        if ($customer->isFamilyHead() && $customer->hasFamily()) {
            $policyCustomer = $customerInsurance->customer;

            return $policyCustomer->family_group_id === $customer->family_group_id;
        }

        return false;
    }

    /**
     * Get policy statistics for dashboard.
     *
     * Aggregates policy metrics including counts by status, type, and company.
     *
     * @return array Associative array with policy statistics and metrics
     */
    public function getPolicyStatistics(): array
    {
        return $this->policyRepository->getStatistics();
    }

    /**
     * Get all policies from specific insurance company.
     *
     * @param  int  $companyId  The insurance company ID
     * @return Collection Collection of policies from the specified company
     */
    public function getPoliciesByCompany(int $companyId): Collection
    {
        return $this->policyRepository->getByInsuranceCompany($companyId);
    }

    /**
     * Get all active policies.
     *
     * @return Collection Collection of currently active policies
     */
    public function getActivePolicies(): Collection
    {
        return $this->policyRepository->getActive();
    }

    /**
     * Get all expired policies.
     *
     * @return Collection Collection of expired policies requiring renewal
     */
    public function getExpiredPolicies(): Collection
    {
        return $this->policyRepository->getExpired();
    }

    /**
     * Get policies by policy type (vehicle, health, life, etc.).
     *
     * @param  int  $policyTypeId  The policy type ID
     * @return Collection Collection of policies matching the type
     */
    public function getPoliciesByType(int $policyTypeId): Collection
    {
        return $this->policyRepository->getByPolicyType($policyTypeId);
    }

    /**
     * Search policies by query string.
     *
     * Performs full-text search across policy number, customer name, and registration number.
     *
     * @param  string  $query  Search term
     * @return Collection Collection of matching policies
     */
    public function searchPolicies(string $query): Collection
    {
        return $this->policyRepository->search($query);
    }

    /**
     * Delete policy record within transaction.
     *
     * @param  CustomerInsurance  $customerInsurance  The policy to delete
     * @return bool True if deletion successful
     */
    public function deletePolicy(CustomerInsurance $customerInsurance): bool
    {
        return $this->deleteInTransaction(
            fn (): bool => $this->policyRepository->delete($customerInsurance)
        );
    }

    /**
     * Update policy active status.
     *
     * @param  CustomerInsurance  $customerInsurance  The policy to update
     * @param  int  $status  New status (0 = inactive, 1 = active)
     * @return bool True if update successful
     */
    public function updatePolicyStatus(CustomerInsurance $customerInsurance, int $status): bool
    {
        return $this->updateInTransaction(
            fn (): Model => $this->policyRepository->update($customerInsurance, ['status' => $status])
        );
    }

    /**
     * Get count of policies grouped by status.
     *
     * @return array Associative array with status counts
     */
    public function getPolicyCountByStatus(): array
    {
        return $this->policyRepository->getCountByStatus();
    }

    /**
     * Check if policy exists by ID.
     *
     * @param  int  $policyId  The policy ID to verify
     * @return bool True if policy exists
     */
    public function policyExists(int $policyId): bool
    {
        return $this->policyRepository->exists($policyId);
    }

    /**
     * Get high-priority policies for renewal processing.
     *
     * Retrieves policies expiring in next 7 days requiring immediate attention.
     *
     * @return Collection Collection of urgent renewal policies
     */
    public function getPoliciesForRenewalProcessing(): Collection
    {
        // Get policies due for renewal in next 7 days for priority processing
        return $this->getPoliciesDueForRenewal(7);
    }

    /**
     * Send renewal reminders to multiple policies in bulk.
     *
     * Processes batch renewal reminders for policies expiring within specified days.
     * Tracks success/failure rates for campaign effectiveness monitoring.
     *
     * @param  int|null  $daysAhead  Number of days ahead to check (default 30)
     * @return array Results array with 'total', 'sent', 'failed' counts and error details
     */
    public function sendBulkRenewalReminders(?int $daysAhead = null): array
    {
        $daysAhead ??= 30;
        $policies = $this->getPoliciesDueForRenewal($daysAhead);

        $results = [
            'total' => $policies->count(),
            'sent' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($policies as $policy) {
            $sent = $this->sendRenewalReminder($policy);

            if ($sent) {
                $results['sent']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'policy_id' => $policy->id,
                    'policy_number' => $policy->policy_number,
                    'customer_name' => $policy->customer->name,
                ];
            }
        }

        return $results;
    }

    /**
     * Generate renewal reminder message.
     */
    private function generateRenewalReminderMessage(CustomerInsurance $customerInsurance): string
    {
        $customer = $customerInsurance->customer;
        $daysRemaining = now()->diffInDays($customerInsurance->policy_end_date);

        $message = "🔔 *Policy Renewal Reminder*\n\n";
        $message .= "Dear *{$customer->name}*,\n\n";
        $message .= "Your insurance policy is due for renewal:\n\n";
        $message .= "📋 *Policy Details:*\n";
        $message .= "• Policy No: *{$customerInsurance->policy_number}*\n";
        $message .= "• Company: *{$customerInsurance->insuranceCompany->name}*\n";
        $message .= "• Type: *{$customerInsurance->policyType->name}*\n";
        $message .= "• End Date: *{$customerInsurance->policy_end_date->format('d M Y')}*\n";
        $message .= "• Days Remaining: *{$daysRemaining} days*\n\n";

        if ($daysRemaining <= 7) {
            $message .= "⚠️ *URGENT: Your policy expires in {$daysRemaining} days!*\n\n";
        }

        $message .= "📞 Please contact us to renew your policy and avoid any lapse in coverage.\n\n";
        $message .= "Best regards,\n";
        $message .= company_advisor_name()."\n";
        $message .= company_website()."\n";
        $message .= company_title()."\n";

        return $message.('"'.company_tagline().'"');
    }
}
