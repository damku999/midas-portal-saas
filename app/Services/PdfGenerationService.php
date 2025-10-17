<?php

namespace App\Services;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfGenerationService
{
    public function generateQuotationPdf(Quotation $quotation): Response
    {
        $quotation->load(['customer', 'quotationCompanies.insuranceCompany']);

        $data = [
            'quotation' => $quotation,
        ];

        $pdf = Pdf::loadView('pdfs.quotation', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Quotation_'.$this->sanitizeFilename($quotation->getQuoteReference()).'_'.
                   $this->sanitizeFilename($quotation->customer->name).'.pdf';

        return $pdf->download($filename);
    }

    public function generateQuotationPdfForWhatsApp(Quotation $quotation): string
    {
        $quotation->load(['customer', 'quotationCompanies.insuranceCompany']);

        $data = [
            'quotation' => $quotation,
        ];

        $pdf = Pdf::loadView('pdfs.quotation', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'Quotation_'.$this->sanitizeFilename($quotation->getQuoteReference()).'_'.
                   $this->sanitizeFilename($quotation->customer->name).'.pdf';

        // Create temp directory if it doesn't exist
        $tempDir = storage_path('app/temp/quotations');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filePath = $tempDir.'/'.$filename;

        // Save PDF to file
        file_put_contents($filePath, $pdf->output());

        return $filePath;
    }

    public function generateComparisonPdf(Quotation $quotation): Response
    {
        $quotation->load(['customer', 'quotationCompanies.insuranceCompany']);

        $data = [
            'quotation' => $quotation,
            'customer' => $quotation->customer,
            'companies' => $quotation->quotationCompanies->sortBy('final_premium'),
            'savings' => $this->calculateSavings($quotation),
            'generatedDate' => now()->format('d/m/Y'),
        ];

        $pdf = Pdf::loadView('pdfs.comparison', $data);
        $pdf->setPaper('A4', 'landscape');

        $filename = 'Comparison_'.$this->sanitizeFilename($quotation->getQuoteReference()).'.pdf';

        return $pdf->download($filename);
    }

    private function calculateSavings(Quotation $quotation): array
    {
        $quotes = $quotation->quotationCompanies->sortBy('final_premium');
        $cheapest = $quotes->first();
        $most_expensive = $quotes->last();

        return [
            'max_savings' => $most_expensive ? $most_expensive->final_premium - $cheapest->final_premium : 0,
            'cheapest_company' => $cheapest?->insuranceCompany->name,
            'most_expensive_company' => $most_expensive?->insuranceCompany->name,
        ];
    }

    /**
     * Sanitize filename to remove invalid characters
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove or replace invalid characters for filenames
        $invalid_chars = ['/', '\\', ':', '*', '?', '"', '<', '>', '|', '\0'];
        $sanitized = str_replace($invalid_chars, '_', $filename);

        // Replace multiple spaces with single underscore
        $sanitized = preg_replace('/\s+/', '_', $sanitized);

        // Remove leading/trailing dots and spaces
        $sanitized = trim((string) $sanitized, '. ');

        // Limit length to avoid filesystem issues
        return substr($sanitized, 0, 100);
    }
}
