<?php

namespace App\Modules\Quotation\Contracts;

use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Models\Quotation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface QuotationServiceInterface
{
    public function getQuotations(Request $request): LengthAwarePaginator;

    public function createQuotation(StoreQuotationRequest $request): Quotation;

    public function updateQuotation(UpdateQuotationRequest $request, Quotation $quotation): bool;

    public function deleteQuotation(Quotation $quotation): bool;

    public function generateCompanyQuotes(Quotation $quotation): array;

    public function getQuotationStatistics(): array;

    public function findById(int $id): ?Quotation;

    public function getActiveQuotations(): \Illuminate\Database\Eloquent\Collection;
}
