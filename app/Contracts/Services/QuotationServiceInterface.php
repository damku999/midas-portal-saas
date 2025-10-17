<?php

namespace App\Contracts\Services;

use App\Models\Quotation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface QuotationServiceInterface
{
    /**
     * Get paginated list of quotations with filters and search.
     */
    public function getQuotations(Request $request): LengthAwarePaginator;

    /**
     * Create a new quotation with company quotes.
     */
    public function createQuotation(array $data): Quotation;

    /**
     * Update quotation with company data.
     */
    public function updateQuotationWithCompanies(Quotation $quotation, array $data): void;

    /**
     * Generate automatic quotes from insurance companies.
     */
    public function generateCompanyQuotes(Quotation $quotation): void;

    /**
     * Generate quotes for specific companies.
     */
    public function generateQuotesForSelectedCompanies(Quotation $quotation, array $companyIds): void;

    /**
     * Send quotation via WhatsApp.
     */
    public function sendQuotationViaWhatsApp(Quotation $quotation): void;

    /**
     * Generate PDF for quotation.
     */
    public function generatePdf(Quotation $quotation);

    /**
     * Delete quotation with related data cleanup.
     */
    public function deleteQuotation(Quotation $quotation): bool;

    /**
     * Calculate premium for given data.
     */
    public function calculatePremium(array $data): float;

    /**
     * Get quotation data for form rendering.
     */
    public function getQuotationFormData(): array;
}
