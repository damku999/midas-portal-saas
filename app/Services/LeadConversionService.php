<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Customer;
use App\Models\LeadActivity;
use App\Models\LeadStatus;
use App\Repositories\Contracts\LeadRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LeadConversionService
{
    protected LeadRepositoryInterface $leadRepository;

    public function __construct(LeadRepositoryInterface $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    /**
     * Convert lead to customer automatically
     */
    public function convertLeadToCustomer(int $leadId, array $additionalData = []): array
    {
        DB::beginTransaction();

        try {
            $lead = $this->leadRepository->getWithRelations($leadId, [
                'source',
                'status',
                'assignedUser',
                'activities',
                'documents'
            ]);

            if (!$lead) {
                throw new \Exception("Lead not found with ID: {$leadId}");
            }

            // Validate lead can be converted
            $this->validateLeadForConversion($lead);

            // Check if customer already exists with same email or mobile
            $existingCustomer = $this->findExistingCustomer($lead);

            if ($existingCustomer) {
                // Link existing customer
                $customer = $existingCustomer;
                $isNewCustomer = false;
            } else {
                // Create new customer from lead data
                $customer = $this->createCustomerFromLead($lead, $additionalData);
                $isNewCustomer = true;
            }

            // Get converted status
            $convertedStatus = LeadStatus::where('is_converted', true)->first();

            if (!$convertedStatus) {
                throw new \Exception("No converted status found. Please ensure lead_statuses table has a status with is_converted = true");
            }

            // Update lead with conversion details
            $lead->update([
                'status_id' => $convertedStatus->id,
                'converted_customer_id' => $customer->id,
                'converted_at' => now(),
                'conversion_notes' => $additionalData['conversion_notes'] ?? 'Automatically converted to customer',
                'updated_by' => Auth::id(),
            ]);

            // Log conversion activity
            LeadActivity::create([
                'lead_id' => $lead->id,
                'activity_type' => LeadActivity::TYPE_STATUS_CHANGE,
                'subject' => 'Lead Converted to Customer',
                'description' => $isNewCustomer
                    ? "Lead successfully converted to new customer: {$customer->name} (ID: {$customer->id})"
                    : "Lead linked to existing customer: {$customer->name} (ID: {$customer->id})",
                'outcome' => 'Conversion successful',
                'created_by' => Auth::id(),
            ]);

            // Copy lead documents to customer if needed
            $this->copyLeadDocumentsToCustomer($lead, $customer);

            DB::commit();

            return [
                'success' => true,
                'customer' => $customer,
                'lead' => $lead->fresh(),
                'is_new_customer' => $isNewCustomer,
                'message' => $isNewCustomer
                    ? 'Lead successfully converted to new customer'
                    : 'Lead linked to existing customer',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate if lead can be converted
     */
    protected function validateLeadForConversion(Lead $lead): void
    {
        // Check if already converted
        if ($lead->isConverted()) {
            $customerId = $lead->converted_customer_id ?? 'Unknown';
            throw new \Exception("Lead has already been converted to customer" . ($customerId !== 'Unknown' ? " (ID: {$customerId})" : ''));
        }

        // Check if marked as lost
        if ($lead->isLost()) {
            throw new \Exception("Cannot convert a lead marked as lost. Reason: {$lead->lost_reason}");
        }

        // Validate required fields for customer creation
        if (empty($lead->name)) {
            throw new \Exception("Lead must have a name to be converted to customer");
        }

        if (empty($lead->mobile_number) && empty($lead->email)) {
            throw new \Exception("Lead must have either email or mobile number to be converted to customer");
        }
    }

    /**
     * Find existing customer with matching email or mobile
     */
    protected function findExistingCustomer(Lead $lead): ?Customer
    {
        $query = Customer::query();

        if (!empty($lead->email)) {
            $query->orWhere('email', $lead->email);
        }

        if (!empty($lead->mobile_number)) {
            $query->orWhere('mobile_number', $lead->mobile_number);
        }

        return $query->first();
    }

    /**
     * Create new customer from lead data
     */
    protected function createCustomerFromLead(Lead $lead, array $additionalData = []): Customer
    {
        $customerData = [
            'name' => $lead->name,
            'email' => $lead->email,
            'mobile_number' => $lead->mobile_number,
            'date_of_birth' => $lead->date_of_birth,
            'type' => $additionalData['type'] ?? 'Retail',
            'status' => 1, // Active
            'pan_card_number' => $additionalData['pan_card_number'] ?? null,
            'aadhar_card_number' => $additionalData['aadhar_card_number'] ?? null,
            'gst_number' => $additionalData['gst_number'] ?? null,
            'family_group_id' => $additionalData['family_group_id'] ?? null,
            'created_by' => Auth::id(),
        ];

        // Generate random password for customer portal access
        if (!empty($lead->email)) {
            $customerData['password'] = Hash::make(Str::random(12));
            $customerData['must_change_password'] = true;
        }

        return Customer::create($customerData);
    }

    /**
     * Copy lead documents to customer
     */
    protected function copyLeadDocumentsToCustomer(Lead $lead, Customer $customer): void
    {
        // This is a placeholder - implement based on your customer document structure
        // You may want to copy relevant documents from lead_documents to customer_documents

        $documents = $lead->documents;

        foreach ($documents as $document) {
            // Log document info in lead activity
            LeadActivity::create([
                'lead_id' => $lead->id,
                'activity_type' => LeadActivity::TYPE_DOCUMENT,
                'subject' => 'Document Available for Customer',
                'description' => "Document '{$document->file_name}' ({$document->document_type}) is available and linked to customer ID: {$customer->id}",
                'created_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Bulk convert multiple leads to customers
     */
    public function bulkConvertLeads(array $leadIds): array
    {
        $results = [
            'successful' => [],
            'failed' => [],
            'total' => count($leadIds),
        ];

        foreach ($leadIds as $leadId) {
            try {
                $result = $this->convertLeadToCustomer($leadId);
                $results['successful'][] = [
                    'lead_id' => $leadId,
                    'customer_id' => $result['customer']->id,
                    'is_new_customer' => $result['is_new_customer'],
                ];
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'lead_id' => $leadId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get conversion statistics
     */
    public function getConversionStatistics(array $filters = []): array
    {
        $query = Lead::query()->converted();

        if (!empty($filters['date_from'])) {
            $query->whereDate('converted_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('converted_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['source_id'])) {
            $query->where('source_id', $filters['source_id']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        $converted = $query->get();
        $total = Lead::query();

        if (!empty($filters['date_from'])) {
            $total->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $total->whereDate('created_at', '<=', $filters['date_to']);
        }

        $totalCount = $total->count();
        $convertedCount = $converted->count();

        return [
            'total_leads' => $totalCount,
            'converted_leads' => $convertedCount,
            'conversion_rate' => $totalCount > 0 ? round(($convertedCount / $totalCount) * 100, 2) : 0,
            'average_conversion_time_days' => $this->calculateAverageConversionTime($converted),
            'conversions_by_source' => $this->groupConversionsBySource($converted),
            'conversions_by_user' => $this->groupConversionsByUser($converted),
            'monthly_conversions' => $this->getMonthlyConversions($filters),
        ];
    }

    /**
     * Calculate average time from lead creation to conversion
     */
    protected function calculateAverageConversionTime($convertedLeads): float
    {
        if ($convertedLeads->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        foreach ($convertedLeads as $lead) {
            $totalDays += $lead->created_at->diffInDays($lead->converted_at);
        }

        return round($totalDays / $convertedLeads->count(), 2);
    }

    /**
     * Group conversions by source
     */
    protected function groupConversionsBySource($convertedLeads): array
    {
        return $convertedLeads->groupBy('source_id')->map(function ($group) {
            return [
                'source' => $group->first()->source->name ?? 'Unknown',
                'count' => $group->count(),
            ];
        })->values()->toArray();
    }

    /**
     * Group conversions by assigned user
     */
    protected function groupConversionsByUser($convertedLeads): array
    {
        return $convertedLeads->groupBy('assigned_to')->map(function ($group) {
            return [
                'user' => $group->first()->assignedUser->name ?? 'Unassigned',
                'count' => $group->count(),
            ];
        })->values()->toArray();
    }

    /**
     * Get monthly conversion trends
     */
    protected function getMonthlyConversions(array $filters = []): array
    {
        $query = DB::table('leads')
            ->selectRaw('DATE_FORMAT(converted_at, "%Y-%m") as month, COUNT(*) as count')
            ->whereNotNull('converted_at');

        if (!empty($filters['date_from'])) {
            $query->whereDate('converted_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('converted_at', '<=', $filters['date_to']);
        }

        return $query->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }
}
