<?php

namespace App\Modules\Quotation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Models\AddonCover;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Quotation;
use App\Modules\Quotation\Contracts\QuotationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuotationApiController extends Controller
{
    public function __construct(
        private QuotationServiceInterface $quotationService
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of quotations with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $quotations = $this->quotationService->getQuotations($request);

            return response()->json([
                'success' => true,
                'data' => $quotations->items(),
                'pagination' => [
                    'current_page' => $quotations->currentPage(),
                    'last_page' => $quotations->lastPage(),
                    'per_page' => $quotations->perPage(),
                    'total' => $quotations->total(),
                    'from' => $quotations->firstItem(),
                    'to' => $quotations->lastItem(),
                ],
                'filters' => [
                    'search' => $request->input('search'),
                    'status' => $request->input('status'),
                    'customer_id' => $request->input('customer_id'),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quotations',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created quotation.
     */
    public function store(StoreQuotationRequest $request): JsonResponse
    {
        try {
            $quotation = $this->quotationService->createQuotation($request);

            return response()->json([
                'success' => true,
                'message' => 'Quotation created successfully',
                'data' => $quotation->load(['customer', 'quotationCompanies.insuranceCompany']),
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create quotation',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified quotation.
     */
    public function show(Quotation $quotation): JsonResponse
    {
        try {
            $quotation->load([
                'customer',
                'quotationCompanies' => function ($query) {
                    $query->orderBy('ranking');
                },
                'quotationCompanies.insuranceCompany',
            ]);

            return response()->json([
                'success' => true,
                'data' => $quotation,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quotation',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified quotation.
     */
    public function update(UpdateQuotationRequest $request, Quotation $quotation): JsonResponse
    {
        try {
            $updated = $this->quotationService->updateQuotation($request, $quotation);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Quotation updated successfully',
                    'data' => $quotation->fresh()->load(['customer', 'quotationCompanies.insuranceCompany']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update quotation',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quotation',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified quotation.
     */
    public function destroy(Quotation $quotation): JsonResponse
    {
        try {
            $deleted = $this->quotationService->deleteQuotation($quotation);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Quotation deleted successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quotation',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quotation',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate company quotes for a quotation.
     */
    public function generateCompanyQuotes(Quotation $quotation): JsonResponse
    {
        try {
            $quotes = $this->quotationService->generateCompanyQuotes($quotation);

            return response()->json([
                'success' => true,
                'message' => 'Company quotes generated successfully',
                'data' => [
                    'quotation_id' => $quotation->id,
                    'quotes_generated' => count($quotes),
                    'quotes' => $quotes,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate company quotes',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get company quotes for a quotation.
     */
    public function getCompanyQuotes(Quotation $quotation): JsonResponse
    {
        try {
            $quotes = $quotation->quotationCompanies()
                ->with('insuranceCompany')
                ->orderBy('ranking')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'quotation_id' => $quotation->id,
                    'quotes_count' => $quotes->count(),
                    'quotes' => $quotes,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve company quotes',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update company quotes for a quotation.
     */
    public function updateCompanyQuotes(Request $request, Quotation $quotation): JsonResponse
    {
        $request->validate([
            'companies' => 'required|array|min:1',
            'companies.*.insurance_company_id' => 'required|exists:insurance_companies,id',
            'companies.*.basic_od_premium' => 'required|numeric|min:0',
            'companies.*.final_premium' => 'required|numeric|min:0',
        ]);

        try {
            // Delete existing company quotes
            $quotation->quotationCompanies()->delete();

            // Create new company quotes
            $companies = $request->input('companies', []);
            foreach ($companies as $companyData) {
                $quotation->quotationCompanies()->create($companyData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Company quotes updated successfully',
                'data' => [
                    'quotation_id' => $quotation->id,
                    'quotes_updated' => count($companies),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update company quotes',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate PDF for quotation.
     */
    public function generatePdf(Quotation $quotation)
    {
        try {
            $quotation->load(['customer', 'quotationCompanies.insuranceCompany']);

            if ($quotation->quotationCompanies->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot generate PDF - no company quotes available',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $pdf = app(\App\Services\PdfGenerationService::class)->generateQuotationPdf($quotation);

            return $pdf->download("quotation-{$quotation->id}-{$quotation->customer->name}.pdf");
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send quotation via WhatsApp.
     */
    public function sendViaWhatsApp(Quotation $quotation): JsonResponse
    {
        try {
            $quotation->load(['customer', 'quotationCompanies.insuranceCompany']);

            if ($quotation->quotationCompanies->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot send quotation - no company quotes available',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (! $quotation->whatsapp_number && ! $quotation->customer->mobile_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot send quotation - no WhatsApp number available',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Use the original service method for WhatsApp sending
            $originalService = app(\App\Services\QuotationService::class);
            $originalService->sendQuotationViaWhatsApp($quotation);

            return response()->json([
                'success' => true,
                'message' => 'Quotation sent via WhatsApp successfully',
                'data' => [
                    'quotation_id' => $quotation->id,
                    'customer_name' => $quotation->customer->name,
                    'whatsapp_number' => $quotation->whatsapp_number ?: $quotation->customer->mobile_number,
                    'sent_at' => now()->toISOString(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send quotation via WhatsApp',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Calculate premium for given data.
     */
    public function calculatePremium(Request $request): JsonResponse
    {
        $request->validate([
            'idv_vehicle' => 'required|numeric|min:0',
            'idv_trailer' => 'nullable|numeric|min:0',
            'idv_cng_lpg_kit' => 'nullable|numeric|min:0',
            'idv_electrical_accessories' => 'nullable|numeric|min:0',
            'idv_non_electrical_accessories' => 'nullable|numeric|min:0',
        ]);

        try {
            $totalIdv = $this->quotationService->calculatePremium($request->all());

            return response()->json([
                'success' => true,
                'data' => [
                    'total_idv' => $totalIdv,
                    'breakdown' => [
                        'vehicle' => $request->input('idv_vehicle', 0),
                        'trailer' => $request->input('idv_trailer', 0),
                        'cng_lpg_kit' => $request->input('idv_cng_lpg_kit', 0),
                        'electrical_accessories' => $request->input('idv_electrical_accessories', 0),
                        'non_electrical_accessories' => $request->input('idv_non_electrical_accessories', 0),
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate premium',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get quotation statistics.
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->quotationService->getQuotationStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'generated_at' => now()->toISOString(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quotation statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get active quotations.
     */
    public function getActiveQuotations(): JsonResponse
    {
        try {
            $quotations = $this->quotationService->getActiveQuotations();

            return response()->json([
                'success' => true,
                'data' => $quotations->load(['customer']),
                'count' => $quotations->count(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active quotations',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get form data for quotation creation.
     */
    public function getFormData(): JsonResponse
    {
        try {
            $data = [
                'customers' => Customer::where('status', 1)->orderBy('name')->get(['id', 'name', 'email', 'mobile_number', 'type']),
                'insurance_companies' => InsuranceCompany::where('status', 1)->orderBy('name')->get(['id', 'name', 'status']),
                'addon_covers' => AddonCover::getOrdered(1),
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'counts' => [
                    'customers' => $data['customers']->count(),
                    'insurance_companies' => $data['insurance_companies']->count(),
                    'addon_covers' => $data['addon_covers']->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve form data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
