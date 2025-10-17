<?php

namespace App\Http\Controllers;

use App\Contracts\Services\QuotationServiceInterface;
use App\Http\Requests\CreateQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Models\Quotation;
use App\Traits\ExportableTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Quotation Controller
 *
 * Handles Quotation CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class QuotationController extends AbstractBaseCrudController
{
    use ExportableTrait;

    public function __construct(private QuotationServiceInterface $quotationService)
    {
        $this->setupCustomPermissionMiddleware([
            ['permission' => 'quotation-list|quotation-create|quotation-edit|quotation-delete', 'only' => ['index']],
            ['permission' => 'quotation-create', 'only' => ['create', 'store']],
            ['permission' => 'quotation-edit', 'only' => ['show', 'edit', 'update']],
            ['permission' => 'quotation-generate', 'only' => ['generateQuotes']],
            ['permission' => 'quotation-send-whatsapp', 'only' => ['sendToWhatsApp']],
            ['permission' => 'quotation-download-pdf', 'only' => ['downloadPdf']],
            ['permission' => 'quotation-delete', 'only' => ['delete']],
        ]);
    }

    public function index(Request $request)
    {
        // Handle AJAX requests for Select2 autocomplete
        if ($request->ajax() || $request->has('ajax')) {
            $search = $request->input('q', $request->input('search', ''));

            $query = Quotation::with('customer:id,name')
                ->select('id', 'customer_id', 'registration_no', 'vehicle_make', 'vehicle_model', 'created_at');

            if ($search) {
                $query->where(static function ($q) use ($search): void {
                    $q->where('registration_no', 'LIKE', sprintf('%%%s%%', $search))
                        ->orWhere('vehicle_make', 'LIKE', sprintf('%%%s%%', $search))
                        ->orWhere('vehicle_model', 'LIKE', sprintf('%%%s%%', $search))
                        ->orWhereHas('customer', static function ($cq) use ($search): void {
                            $cq->where('name', 'LIKE', sprintf('%%%s%%', $search));
                        });
                });
            }

            $quotations = $query->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            // Format for Select2
            return response()->json([
                'results' => $quotations->map(static function ($quotation): array {
                    $label = 'ID:'.$quotation->id;
                    if ($quotation->registration_no) {
                        $label .= ' | '.$quotation->registration_no;
                    }

                    if ($quotation->vehicle_make || $quotation->vehicle_model) {
                        $label .= ' | '.trim($quotation->vehicle_make.' '.$quotation->vehicle_model);
                    }

                    $label .= ' - '.($quotation->customer?->name ?? 'Unknown');

                    return [
                        'id' => $quotation->id,
                        'text' => $label,
                    ];
                }),
            ]);
        }

        $quotations = $this->quotationService->getQuotations($request);

        return view('quotations.index', ['quotations' => $quotations]);
    }

    public function create(): View
    {
        $formData = $this->quotationService->getQuotationFormData();

        return view('quotations.create', $formData);
    }

    public function store(CreateQuotationRequest $createQuotationRequest): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $quotation = $this->quotationService->createQuotation($createQuotationRequest->validated());

            DB::commit();

            return $this->redirectWithSuccess('quotations.show', 'Quotation created successfully. Generating quotes from multiple companies...', ['quotation' => $quotation]);
        } catch (\Throwable $throwable) {
            DB::rollBack();

            return $this->redirectWithError('Failed to create quotation: '.$throwable->getMessage())
                ->withInput();
        }
    }

    public function show(Quotation $quotation): View
    {
        $quotation->load(['customer', 'quotationCompanies.insuranceCompany']);

        return view('quotations.show', ['quotation' => $quotation]);
    }

    public function edit(Quotation $quotation): View
    {
        $formData = $this->quotationService->getQuotationFormData();
        $formData['quotation'] = $quotation;

        return view('quotations.edit', $formData);
    }

    public function update(UpdateQuotationRequest $updateQuotationRequest, Quotation $quotation): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $data = $updateQuotationRequest->validated();

            // Update quotation with manual company data
            $this->quotationService->updateQuotationWithCompanies($quotation, $data);

            DB::commit();

            return $this->redirectWithSuccess('quotations.show', 'Quotation updated successfully!', ['quotation' => $quotation]);
        } catch (\Throwable $throwable) {
            DB::rollBack();

            return $this->redirectWithError('Failed to update quotation: '.$throwable->getMessage())
                ->withInput();
        }
    }

    public function generateQuotes(Quotation $quotation): RedirectResponse
    {
        try {
            $this->quotationService->generateCompanyQuotes($quotation);

            return $this->redirectWithSuccess(null, 'Quotes generated successfully from all companies!');
        } catch (\Throwable $throwable) {
            return $this->redirectWithError('Failed to generate quotes: '.$throwable->getMessage());
        }
    }

    public function sendToWhatsApp(Quotation $quotation): RedirectResponse
    {
        try {
            $this->quotationService->sendQuotationViaWhatsApp($quotation);

            return $this->redirectWithSuccess(null, 'Quotation sent via WhatsApp successfully!');
        } catch (\Throwable $throwable) {
            return $this->redirectWithError('Failed to send quotation: '.$throwable->getMessage());
        }
    }

    public function downloadPdf(Quotation $quotation)
    {
        try {
            return $this->quotationService->generatePdf($quotation);
        } catch (\Throwable $throwable) {
            return $this->redirectWithError('Failed to generate PDF: '.$throwable->getMessage());
        }
    }

    public function getQuoteFormHtml(Request $request)
    {
        $formData = $this->quotationService->getQuotationFormData();
        $formData['currentIndex'] = $request->input('index', 0);

        return view('quotations.partials.quote-form', $formData)->render();
    }

    public function delete(Quotation $quotation): RedirectResponse
    {
        try {
            $quoteReference = $quotation->getQuoteReference();
            $companiesCount = $quotation->quotationCompanies()->count();

            $deleted = $this->quotationService->deleteQuotation($quotation);

            if ($deleted) {
                $message = sprintf('Quotation %s deleted successfully!', $quoteReference);
                if ($companiesCount > 0) {
                    $message .= sprintf(' (%s company quote(s) also removed)', $companiesCount);
                }

                return $this->redirectWithSuccess('quotations.index', $message);
            }

            return $this->redirectWithError('Failed to delete quotation.');
        } catch (\Throwable $throwable) {
            return $this->redirectWithError('Failed to delete quotation: '.$throwable->getMessage());
        }
    }

    protected function getExportRelations(): array
    {
        return ['customer'];
    }

    protected function getSearchableFields(): array
    {
        return ['customer.name', 'insurance_type'];
    }

    protected function getExportConfig(Request $request): array
    {
        return [
            'format' => $request->get('format', 'xlsx'),
            'filename' => 'quotations',
            'with_headings' => true,
            'auto_size' => true,
            'relations' => $this->getExportRelations(),
            'order_by' => ['column' => 'created_at', 'direction' => 'desc'],
            'headings' => ['ID', 'Customer', 'Insurance Type', 'Status', 'Companies Count', 'Created Date'],
            'mapping' => fn ($quotation): array => [
                $quotation->id,
                $quotation->customer->name ?? 'N/A',
                $quotation->insurance_type ?? 'N/A',
                $quotation->status ? 'Active' : 'Inactive',
                $quotation->quotationCompanies()->count(),
                $quotation->created_at->format('Y-m-d H:i:s'),
            ],
            'with_mapping' => true,
        ];
    }
}
