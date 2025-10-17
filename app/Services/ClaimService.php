<?php

namespace App\Services;

use App\Contracts\Repositories\ClaimRepositoryInterface;
use App\Contracts\Services\ClaimServiceInterface;
use App\Http\Requests\StoreClaimRequest;
use App\Http\Requests\UpdateClaimRequest;
use App\Models\Claim;
use App\Models\CustomerInsurance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Claim Service
 *
 * Handles Claim business logic including document and stage management.
 * Inherits transaction management from BaseService.
 */
class ClaimService extends BaseService implements ClaimServiceInterface
{
    /**
     * Constructor
     */
    public function __construct(
        /**
         * Claim Repository instance
         */
        private readonly ClaimRepositoryInterface $claimRepository
    ) {}

    /**
     * Retrieve paginated claims with filtering and search capabilities.
     *
     * Fetches claims list with support for multiple filter dimensions
     * delegated to repository layer:
     * - Search: Claim number, customer name, policy details
     * - Status: Active/inactive claims filtering
     * - Date ranges: Claim date, settlement date filtering
     * - Insurance type: Vehicle, Health, Property filtering
     *
     * @param  Request  $request  HTTP request with filter and pagination parameters
     * @return LengthAwarePaginator Paginated claims with customer and insurance relationships
     */
    public function getClaims(Request $request): LengthAwarePaginator
    {
        return $this->claimRepository->getClaimsWithFilters($request);
    }

    /**
     * Create a new insurance claim with automated setup workflow.
     *
     * This method orchestrates comprehensive claim creation within a transaction:
     * 1. Fetches customer insurance and extracts customer_id
     * 2. Generates unique claim number (auto-incremented)
     * 3. Sets WhatsApp number from customer if not provided
     * 4. Creates claim record with validated data
     * 5. Creates default document checklist based on insurance type:
     *    - Vehicle: FIR, Survey Report, RC Book, Driving License, etc.
     *    - Health: Discharge Summary, Bills, Reports, ID Proof, etc.
     * 6. Creates initial claim stage ("Claim Registered")
     * 7. Creates liability detail record with appropriate claim type:
     *    - Health insurance → Cashless by default
     *    - Other types → Reimbursement by default
     * 8. Sends claim creation notification email (after transaction)
     *
     * Transaction ensures claim, documents, stage, and liability are created atomically.
     *
     * @param  StoreClaimRequest  $storeClaimRequest  Validated claim creation request
     * @return Claim Newly created claim with all relationships loaded
     *
     * @throws ModelNotFoundException If insurance not found
     * @throws QueryException On database constraint violations
     */
    public function createClaim(StoreClaimRequest $storeClaimRequest): Claim
    {
        return $this->createInTransaction(static function () use ($storeClaimRequest) {
            // Get customer insurance details
            $customerInsurance = CustomerInsurance::with('customer')->findOrFail($storeClaimRequest->customer_insurance_id);

            // Generate claim number
            $claimNumber = Claim::generateClaimNumber();

            // Create claim
            $claimData = $storeClaimRequest->validated();
            $claimData['claim_number'] = $claimNumber;
            $claimData['customer_id'] = $customerInsurance->customer_id;

            // Set WhatsApp number from customer if not provided
            if (empty($claimData['whatsapp_number'])) {
                $claimData['whatsapp_number'] = $customerInsurance->customer->mobile_number;
            }

            $claim = Claim::query()->create($claimData);

            // Create default documents based on insurance type
            $claim->createDefaultDocuments();

            // Create initial stage
            $claim->createInitialStage();

            // Create basic liability detail record with correct claim type
            $claim->liabilityDetail()->create([
                'claim_type' => $claim->insurance_type === 'Health' ? 'Cashless' : 'Reimbursement',
                'notes' => 'Initial liability detail record created',
            ]);

            // Send email notification if enabled (after transaction)
            $claim->sendClaimCreatedNotification();

            Log::info('Claim created successfully', [
                'claim_id' => $claim->id,
                'claim_number' => $claim->claim_number,
                'customer_id' => $claim->customer_id,
                'user_id' => auth()->id(),
            ]);

            return $claim;
        });
    }

    /**
     * Update an existing insurance claim with relationship synchronization.
     *
     * This method handles claim updates within a transaction:
     * 1. If customer_insurance_id changed:
     *    - Fetches new insurance with customer relationship
     *    - Updates customer_id to maintain data consistency
     * 2. Sets WhatsApp number from new customer if not provided
     * 3. Updates claim record with validated data
     * 4. Synchronizes liability detail claim_type if insurance_type changed:
     *    - Health → Cashless
     *    - Other → Reimbursement
     *
     * Transaction ensures claim and liability detail updates are atomic,
     * maintaining referential integrity across related records.
     *
     * @param  UpdateClaimRequest  $updateClaimRequest  Validated claim update request
     * @param  Claim  $claim  Claim instance to update
     * @return bool True on successful update
     *
     * @throws ModelNotFoundException If new insurance not found
     * @throws QueryException On database constraint violations
     */
    public function updateClaim(UpdateClaimRequest $updateClaimRequest, Claim $claim): bool
    {
        return $this->updateInTransaction(static function () use ($updateClaimRequest, $claim) {
            // Get customer insurance details if changed
            if ($updateClaimRequest->customer_insurance_id !== $claim->customer_insurance_id) {
                $customerInsurance = CustomerInsurance::with('customer')->findOrFail($updateClaimRequest->customer_insurance_id);
                $updateData = $updateClaimRequest->validated();
                $updateData['customer_id'] = $customerInsurance->customer_id;
            } else {
                $updateData = $updateClaimRequest->validated();
            }

            // Set WhatsApp number from customer if not provided
            if (empty($updateData['whatsapp_number']) && isset($customerInsurance)) {
                $updateData['whatsapp_number'] = $customerInsurance->customer->mobile_number;
            }

            $updated = $claim->update($updateData);

            // Sync insurance_type to liability detail's claim_type if insurance_type was updated
            if (isset($updateData['insurance_type']) && $claim->liabilityDetail) {
                $newClaimType = $updateData['insurance_type'] === 'Health' ? 'Cashless' : 'Reimbursement';
                $claim->liabilityDetail->update(['claim_type' => $newClaimType]);
            }

            if ($updated) {
                Log::info('Claim updated successfully', [
                    'claim_id' => $claim->id,
                    'claim_number' => $claim->claim_number,
                    'user_id' => auth()->id(),
                ]);
            }

            return $updated;
        });
    }

    /**
     * Toggle claim active/inactive status.
     *
     * Updates the status field (0 = inactive, 1 = active) for soft-disabling
     * claims without full deletion. Preserves claim history while removing
     * from active claims lists. Logs all status changes for audit trail.
     *
     * @param  int  $claimId  Claim ID to update
     * @param  bool  $status  New status (true = active, false = inactive)
     * @return bool True on successful update
     *
     * @throws \Exception On database errors or if claim not found
     */
    public function updateClaimStatus(int $claimId, bool $status): bool
    {
        try {
            $updated = $this->claimRepository->updateStatus($claimId, $status);

            if ($updated) {
                Log::info('Claim status updated', [
                    'claim_id' => $claimId,
                    'status' => $status,
                    'user_id' => auth()->id(),
                ]);
            }

            return $updated;

        } catch (\Exception $exception) {
            Log::error('Failed to update claim status', [
                'claim_id' => $claimId,
                'status' => $status,
                'error' => $exception->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $exception;
        }
    }

    /**
     * Soft delete a claim record.
     *
     * Performs Laravel soft delete (sets deleted_at timestamp) rather than
     * hard deletion, preserving claim history for audit and reporting purposes.
     * Soft-deleted claims are automatically excluded from standard queries
     * but can be recovered if needed.
     *
     * Logs deletion for audit trail with claim number and user context.
     *
     * @param  Claim  $claim  Claim instance to soft delete
     * @return bool True on successful soft deletion
     *
     * @throws \Exception On database errors
     */
    public function deleteClaim(Claim $claim): bool
    {
        try {
            $deleted = $claim->delete();

            if ($deleted) {
                Log::info('Claim deleted successfully', [
                    'claim_id' => $claim->id,
                    'claim_number' => $claim->claim_number,
                    'user_id' => auth()->id(),
                ]);
            }

            return $deleted;

        } catch (\Exception $exception) {
            Log::error('Failed to delete claim', [
                'claim_id' => $claim->id,
                'error' => $exception->getMessage(),
                'user_id' => auth()->id(),
            ]);
            throw $exception;
        }
    }

    /**
     * Search active insurance policies with wildcard matching for claim creation.
     *
     * This method provides intelligent policy search for claim form autocomplete:
     * - Searches across: policy number, registration number, customer name/email/mobile
     * - Minimum 3 characters required to prevent excessive results
     * - Returns only active policies (status = true)
     * - Limits to 20 results for performance
     * - Auto-suggests insurance type (Vehicle/Health) based on policy type keywords
     *
     * Each result includes:
     * - Formatted display text with customer, policy, and company details
     * - Customer contact information (name, email, mobile)
     * - Policy identifiers (policy number, registration number)
     * - Suggested insurance type for pre-filling claim form
     *
     * Critical for user experience: enables quick policy lookup during claim entry
     * without requiring exact matches.
     *
     * @param  string  $searchTerm  Search query (min 3 characters)
     * @return array Array of matching policies with customer and insurance details
     */
    public function searchPolicies(string $searchTerm): array
    {
        if (strlen($searchTerm) < 3) {
            return [];
        }

        $policies = CustomerInsurance::with([
            'customer:id,name,email,mobile_number',
            'insuranceCompany:id,name',
            'policyType:id,name',
        ])
            ->where(static function (Builder $builder) use ($searchTerm): void {
                $builder->where('policy_no', 'like', sprintf('%%%s%%', $searchTerm))
                    ->orWhere('registration_no', 'like', sprintf('%%%s%%', $searchTerm))
                    ->orWhereHas('customer', static function (Builder $builder) use ($searchTerm): void {
                        $builder->where('name', 'like', sprintf('%%%s%%', $searchTerm))
                            ->orWhere('email', 'like', sprintf('%%%s%%', $searchTerm))
                            ->orWhere('mobile_number', 'like', sprintf('%%%s%%', $searchTerm));
                    });
            })
            ->where('status', true) // Only active policies
            ->limit(20)
            ->get();

        return $policies->map(fn ($policy): array => [
            'id' => $policy->id,
            'text' => $this->formatPolicyText($policy),
            'customer_name' => $policy->customer->name ?? '',
            'customer_email' => $policy->customer->email ?? '',
            'customer_mobile' => $policy->customer->mobile_number ?? '',
            'policy_no' => $policy->policy_no ?? '',
            'registration_no' => $policy->registration_no ?? '',
            'insurance_company' => $policy->insuranceCompany->name ?? '',
            'policy_type' => $policy->policyType->name ?? '',
            'suggested_insurance_type' => $this->suggestInsuranceType($policy),
        ])->toArray();
    }

    /**
     * Format policy text for display in dropdown.
     */
    private function formatPolicyText(CustomerInsurance $customerInsurance): string
    {
        $parts = [];

        if ($customerInsurance->customer) {
            $parts[] = $customerInsurance->customer->name;
        }

        if ($customerInsurance->policy_no) {
            $parts[] = 'Policy: '.$customerInsurance->policy_no;
        }

        if ($customerInsurance->registration_no) {
            $parts[] = 'Reg: '.$customerInsurance->registration_no;
        }

        if ($customerInsurance->insuranceCompany) {
            $parts[] = $customerInsurance->insuranceCompany->name;
        }

        return implode(' - ', $parts);
    }

    /**
     * Suggest insurance type based on policy type.
     */
    private function suggestInsuranceType(CustomerInsurance $customerInsurance): string
    {
        // Check policy type or other indicators to suggest insurance type
        $policyTypeName = strtolower($customerInsurance->policyType->name ?? '');

        // Vehicle insurance indicators
        $vehicleKeywords = ['motor', 'vehicle', 'car', 'bike', 'auto', 'comprehensive', 'third party'];
        foreach ($vehicleKeywords as $keyword) {
            if (str_contains($policyTypeName, $keyword)) {
                return 'Vehicle';
            }
        }

        // Health insurance indicators
        $healthKeywords = ['health', 'medical', 'mediclaim', 'hospital', 'disease'];
        foreach ($healthKeywords as $healthKeyword) {
            if (str_contains($policyTypeName, $healthKeyword)) {
                return 'Health';
            }
        }

        // If registration number exists, likely vehicle
        if (! empty($customerInsurance->registration_no)) {
            return 'Vehicle';
        }

        // Default to Health if unclear
        return 'Health';
    }

    /**
     * Retrieve claim statistics for dashboard analytics.
     *
     * Fetches comprehensive claim metrics for dashboard display:
     * - Total claims count
     * - Claims by status (pending, approved, rejected, settled)
     * - Claims by insurance type (Vehicle, Health, Property)
     * - Recent claims activity
     * - Settlement rate percentage
     *
     * Delegates to repository layer for optimized queries. Returns empty
     * array on errors to prevent dashboard crashes, with error logging
     * for troubleshooting.
     *
     * @return array Statistics array with claim counts and percentages, empty on error
     */
    public function getClaimStatistics(): array
    {
        try {
            return $this->claimRepository->getClaimStatistics();

        } catch (\Exception $exception) {
            Log::error('Failed to get claim statistics', [
                'error' => $exception->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return [];
        }
    }
}
