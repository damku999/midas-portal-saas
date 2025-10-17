<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\CustomerInsuranceRepositoryInterface;
use App\Http\Requests\StoreClaimRequest;
use App\Http\Requests\UpdateClaimRequest;
use App\Models\Claim;
use App\Services\ClaimService;
use App\Traits\ExportableTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Claim Controller
 *
 * Handles Claim CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class ClaimController extends AbstractBaseCrudController
{
    use ExportableTrait;

    public function __construct(
        private ClaimService $claimService,
        private CustomerInsuranceRepositoryInterface $customerInsuranceRepository
    ) {
        $this->middleware('auth:web'); // Explicitly use web guard for admin
        $this->setupCustomPermissionMiddleware([
            ['permission' => 'claim-list|claim-create|claim-edit|claim-delete', 'only' => ['index']],
            ['permission' => 'claim-create', 'only' => ['create', 'store', 'updateStatus']],
            ['permission' => 'claim-edit', 'only' => ['edit', 'update']],
            ['permission' => 'claim-delete', 'only' => ['delete']],
        ]);
    }

    /**
     * Display a listing of claims.
     */
    public function index(Request $request): View
    {
        try {
            $claims = $this->claimService->getClaims($request);

            return view('claims.index', [
                'claims' => $claims,
                'sortField' => $request->input('sort_field', 'created_at'),
                'sortOrder' => $request->input('sort_order', 'desc'),
                'request' => $request->all(),
            ]);
        } catch (\Throwable $throwable) {
            // Create empty paginated result to maintain view compatibility
            $lengthAwarePaginator = new LengthAwarePaginator(
                collect(), // empty collection
                0, // total count
                15, // per page
                1, // current page
                ['path' => request()->url(), 'pageName' => 'page']
            );

            return view('claims.index', [
                'claims' => $lengthAwarePaginator,
                'sortField' => 'created_at',
                'sortOrder' => 'desc',
                'request' => $request->all(),
                'error' => 'Failed to load claims: '.$throwable->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for creating a new claim.
     */
    public function create(): View
    {
        // Get customer insurances using repository
        $this->customerInsuranceRepository->getActiveCustomerInsurances();

        return view('claims.create', ['customerInsurances' => $customerInsurances]);
    }

    /**
     * Store a newly created claim in storage.
     */
    public function store(StoreClaimRequest $storeClaimRequest): RedirectResponse
    {
        try {
            $claim = $this->claimService->createClaim($storeClaimRequest);

            return $this->redirectWithSuccess('claims.index',
                'Claim created successfully. Claim Number: '.$claim->claim_number);
        } catch (\Throwable $throwable) {
            return $this->redirectWithError('Failed to create claim: '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified claim.
     */
    public function show(Claim $claim): View
    {
        $claim->load([
            'customer',
            'customerInsurance.insuranceCompany',
            'customerInsurance.policyType',
            'stages' => static function ($query): void {
                $query->orderBy('created_at', 'desc');
            },
            'documents',
            'liabilityDetail',
        ]);

        return view('claims.show', ['claim' => $claim]);
    }

    /**
     * Show the form for editing the specified claim.
     */
    public function edit(Claim $claim): View
    {
        $claim->load(['customer', 'customerInsurance']);

        return view('claims.edit', ['claim' => $claim]);
    }

    /**
     * Update the specified claim in storage.
     */
    public function update(UpdateClaimRequest $updateClaimRequest, Claim $claim): RedirectResponse
    {
        try {
            $updated = $this->claimService->updateClaim($updateClaimRequest, $claim);

            if ($updated) {
                return $this->redirectWithSuccess('claims.index',
                    $this->getSuccessMessage('Claim', 'updated'));
            }

            return $this->redirectWithError($this->getErrorMessage('Claim', 'update'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError('Failed to update claim: '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Update the status of the specified claim.
     */
    public function updateStatus(int $claimId, int $status): RedirectResponse
    {
        try {
            $updated = $this->claimService->updateClaimStatus($claimId, (bool) $status);

            if ($updated) {
                return $this->redirectWithSuccess('claims.index', $this->getSuccessMessage('Claim Status', 'updated'));
            }

            return $this->redirectWithError($this->getErrorMessage('Claim Status', 'update'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Claim Status', 'update').': '.$throwable->getMessage());
        }
    }

    /**
     * Remove the specified claim from storage (soft delete).
     */
    public function delete(Claim $claim): RedirectResponse
    {
        try {
            $deleted = $this->claimService->deleteClaim($claim);

            if ($deleted) {
                return $this->redirectWithSuccess('claims.index', $this->getSuccessMessage('Claim', 'deleted'));
            }

            return $this->redirectWithError($this->getErrorMessage('Claim', 'delete'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Claim', 'delete').': '.$throwable->getMessage());
        }
    }

    protected function getExportRelations(): array
    {
        return ['customer', 'customerInsurance'];
    }

    protected function getSearchableFields(): array
    {
        return ['claim_number', 'customer.name', 'description'];
    }

    protected function getExportConfig(Request $request): array
    {
        return [
            'format' => $request->get('format', 'xlsx'),
            'filename' => 'claims',
            'with_headings' => true,
            'auto_size' => true,
            'relations' => $this->getExportRelations(),
            'order_by' => ['column' => 'created_at', 'direction' => 'desc'],
            'headings' => ['ID', 'Claim Number', 'Customer', 'Insurance Type', 'Claim Type', 'Status', 'Claim Date', 'Created Date'],
            'mapping' => fn ($claim): array => [
                $claim->id,
                $claim->claim_number ?? 'Pending',
                $claim->customer->name ?? 'N/A',
                $claim->insurance_type ?? 'N/A',
                $claim->claim_type ?? 'N/A',
                $claim->status ? 'Active' : 'Inactive',
                $claim->claim_date ? $claim->claim_date->format('Y-m-d') : 'N/A',
                $claim->created_at->format('Y-m-d H:i:s'),
            ],
            'with_mapping' => true,
        ];
    }

    /**
     * Search for policies/insurances (AJAX endpoint for wildcard search).
     */
    public function searchPolicies(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->input('search', '');

            // Debug logging
            Log::info('Search Policies Request', [
                'search_term' => $searchTerm,
                'search_length' => strlen((string) $searchTerm),
            ]);

            if (strlen((string) $searchTerm) < 3) {
                return response()->json([
                    'results' => [],
                ]);
            }

            $policies = $this->claimService->searchPolicies($searchTerm);

            Log::info('Search Policies Results', [
                'search_term' => $searchTerm,
                'results_count' => count($policies),
            ]);

            return response()->json([
                'results' => $policies,
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'error' => 'Failed to search policies: '.$throwable->getMessage(),
            ], 500);
        }
    }

    /**
     * Get claim statistics (AJAX endpoint).
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->claimService->getClaimStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get statistics: '.$throwable->getMessage(),
            ], 500);
        }
    }

    /**
     * Send document list WhatsApp message (AJAX endpoint).
     */
    public function sendDocumentListWhatsApp(Claim $claim): JsonResponse
    {
        try {
            $result = $claim->sendDocumentListWhatsApp();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'preview' => $claim->insurance_type === 'Health'
                    ? $claim->getHealthInsuranceDocumentListMessage()
                    : $claim->getVehicleInsuranceDocumentListMessage(),
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send WhatsApp message: '.$throwable->getMessage(),
            ], 500);
        }
    }

    /**
     * Send pending documents WhatsApp message (AJAX endpoint).
     */
    public function sendPendingDocumentsWhatsApp(Claim $claim): JsonResponse
    {
        try {
            $result = $claim->sendPendingDocumentsWhatsApp();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'preview' => $claim->getPendingDocumentsMessage(),
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send WhatsApp message: '.$throwable->getMessage(),
            ], 500);
        }
    }

    /**
     * Send claim number WhatsApp message (AJAX endpoint).
     */
    public function sendClaimNumberWhatsApp(Claim $claim): JsonResponse
    {
        try {
            $result = $claim->sendClaimNumberWhatsApp();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'preview' => $claim->getClaimNumberNotificationMessage(),
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send WhatsApp message: '.$throwable->getMessage(),
            ], 500);
        }
    }

    /**
     * Get WhatsApp message preview (AJAX endpoint).
     */
    public function getWhatsAppPreview(Claim $claim, string $type): JsonResponse
    {
        try {
            $preview = '';

            switch ($type) {
                case 'document_list':
                    $preview = $claim->insurance_type === 'Health'
                        ? $claim->getHealthInsuranceDocumentListMessage()
                        : $claim->getVehicleInsuranceDocumentListMessage();
                    break;
                case 'pending_documents':
                    $preview = $claim->getPendingDocumentsMessage();
                    break;
                case 'claim_number':
                    $preview = $claim->getClaimNumberNotificationMessage();
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid message type',
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'preview' => $preview,
                'whatsapp_number' => $claim->getWhatsAppNumber(),
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get message preview: '.$throwable->getMessage(),
            ], 500);
        }
    }

    /**
     * Update document status (AJAX endpoint).
     */
    public function updateDocumentStatus(Request $request, Claim $claim, int $documentId): JsonResponse
    {
        try {
            $document = $claim->documents()->findOrFail($documentId);
            $isSubmitted = $request->boolean('is_submitted');

            if ($isSubmitted) {
                $document->markAsSubmitted();
            } else {
                $document->markAsNotSubmitted();
            }

            return response()->json([
                'success' => true,
                'message' => 'Document status updated successfully',
                'document_completion' => $claim->getDocumentCompletionPercentage(),
                'required_completion' => $claim->getRequiredDocumentCompletionPercentage(),
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document status: '.$throwable->getMessage(),
            ], 500);
        }
    }

    /**
     * Add new claim stage (AJAX endpoint).
     */
    public function addStage(Request $request, Claim $claim): JsonResponse
    {
        try {
            $request->validate([
                'stage_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'notes' => 'nullable|string',
                'send_whatsapp' => 'boolean',
            ]);

            // Mark current stage as not current
            $claim->stages()->where('is_current', true)->update(['is_current' => false]);

            // Create new stage
            $stage = $claim->stages()->create([
                'stage_name' => $request->stage_name,
                'description' => $request->description,
                'notes' => $request->notes,
                'is_current' => true,
                'is_completed' => false,
                'stage_date' => now(),
            ]);

            // Send WhatsApp if requested
            $whatsappResult = null;
            if ($request->boolean('send_whatsapp')) {
                $whatsappResult = $claim->sendStageUpdateWhatsApp($request->stage_name, $request->notes);
            }

            // Send email notification if enabled
            $claim->sendStageUpdateNotification($request->stage_name, $request->description, $request->notes);

            return response()->json([
                'success' => true,
                'message' => 'Stage added successfully',
                'stage' => $stage,
                'whatsapp_result' => $whatsappResult,
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add stage: '.$throwable->getMessage(),
            ], 500);
        }
    }

    /**
     * Update claim number (AJAX endpoint).
     */
    public function updateClaimNumber(Request $request, Claim $claim): JsonResponse
    {
        try {
            $request->validate([
                'claim_number' => 'required|string|max:255',
                'send_whatsapp' => 'boolean',
            ]);

            $claim->update([
                'claim_number' => $request->claim_number,
            ]);

            // Send WhatsApp if requested
            $whatsappResult = null;
            if ($request->boolean('send_whatsapp')) {
                $whatsappResult = $claim->sendClaimNumberWhatsApp();
            }

            // Send email notification if enabled
            $claim->sendClaimNumberAssignedNotification();

            return response()->json([
                'success' => true,
                'message' => 'Claim number updated successfully',
                'whatsapp_result' => $whatsappResult,
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update claim number: '.$throwable->getMessage(),
            ], 500);
        }
    }

    /**
     * Update liability details (AJAX endpoint).
     */
    public function updateLiabilityDetails(Request $request, Claim $claim): JsonResponse
    {
        try {
            $request->validate([
                'claim_type' => 'required|in:Cashless,Reimbursement',
                'claim_amount' => 'nullable|numeric|min:0',
                'salvage_amount' => 'nullable|numeric|min:0',
                'less_claim_charge' => 'nullable|numeric|min:0',
                'amount_to_be_paid' => 'nullable|numeric|min:0',
                'less_salvage_amount' => 'nullable|numeric|min:0',
                'less_deductions' => 'nullable|numeric|min:0',
                'claim_amount_received' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $liabilityDetail = $claim->liabilityDetail ?: $claim->liabilityDetail()->create([]);

            $liabilityDetail->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Liability details updated successfully',
                'liability_detail' => $liabilityDetail->fresh(),
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update liability details: '.$throwable->getMessage(),
            ], 500);
        }
    }
}
