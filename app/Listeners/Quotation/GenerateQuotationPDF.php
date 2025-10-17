<?php

namespace App\Listeners\Quotation;

use App\Events\Document\PDFGenerationRequested;
use App\Events\Quotation\QuotationGenerated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GenerateQuotationPDF implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(QuotationGenerated $event): void
    {
        $quotation = $event->quotation;

        // Generate PDF filename
        $fileName = "quotation_{$quotation->quotation_number}_".date('YmdHis').'.pdf';

        // Request PDF generation
        PDFGenerationRequested::dispatch(
            'quotation',
            'pdfs.quotation',
            [
                'quotation' => $quotation->load(['customer', 'quotationCompanies.insuranceCompany']),
                'generated_date' => now()->format('d/m/Y'),
                'best_premium' => $event->bestPremium,
                'company_count' => $event->companyCount,
                'generated_by' => $event->generatedBy,
            ],
            $fileName,
            'pdfs/quotations',
            [
                'format' => 'A4',
                'orientation' => 'portrait',
                'margin_top' => 15,
                'margin_bottom' => 15,
            ],
            $event->isHighValueQuotation() ? 3 : 5,
            "quotation_{$quotation->id}",
            $quotation->customer_id,
            'QuotationPDFGenerated'
        );
    }

    public function failed(QuotationGenerated $event, \Throwable $exception): void
    {
        \Log::error('Failed to generate quotation PDF', [
            'quotation_id' => $event->quotation->id,
            'quotation_number' => $event->quotation->quotation_number,
            'error' => $exception->getMessage(),
        ]);
    }
}
