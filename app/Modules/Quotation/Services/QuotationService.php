<?php

namespace App\Modules\Quotation\Services;

use App\Contracts\Repositories\QuotationRepositoryInterface;
use App\Events\Quotation\QuotationGenerated;
use App\Events\Quotation\QuotationRequested;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use App\Models\Quotation;
use App\Models\QuotationCompany;
use App\Modules\Quotation\Contracts\QuotationServiceInterface;
use App\Services\PdfGenerationService;
use App\Traits\WhatsAppApiTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationService implements QuotationServiceInterface
{
    use WhatsAppApiTrait;

    public function __construct(
        private PdfGenerationService $pdfService,
        private QuotationRepositoryInterface $quotationRepository
    ) {}

    public function getQuotations(Request $request): LengthAwarePaginator
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'customer_id' => $request->input('customer_id'),
            'sort_field' => $request->input('sort_field', 'created_at'),
            'sort_order' => $request->input('sort_order', 'desc'),
        ];

        return $this->quotationRepository->getPaginated($filters, 15);
    }

    public function createQuotation(StoreQuotationRequest $request): Quotation
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Calculate total IDV
            $data['total_idv'] = $this->calculateTotalIdv($data);

            // Fire QuotationRequested event
            $customer = Customer::find($data['customer_id']);
            $policyType = PolicyType::find($data['policy_type_id']);

            if ($customer && $policyType) {
                QuotationRequested::dispatch(
                    $customer,
                    $policyType,
                    $data,
                    auth()->user()->name ?? 'system'
                );
            }

            // Extract company data before creating quotation
            $companies = $data['companies'] ?? [];
            unset($data['companies']);

            $quotation = $this->quotationRepository->create($data);

            // Create company quotes if provided
            if (! empty($companies)) {
                $this->createManualCompanyQuotes($quotation, $companies);
            }

            DB::commit();

            // Fire QuotationGenerated event after successful creation
            QuotationGenerated::dispatch($quotation);

            return $quotation;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateQuotation(UpdateQuotationRequest $request, Quotation $quotation): bool
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $data['total_idv'] = $this->calculateTotalIdv($data);

            // Extract company data
            $companies = $data['companies'] ?? [];
            unset($data['companies']);

            $updated = $this->quotationRepository->update($quotation->id, $data);

            if ($updated) {
                // Update company quotes
                $quotation->quotationCompanies()->delete();
                if (! empty($companies)) {
                    $this->createManualCompanyQuotes($quotation, $companies);
                }

                DB::commit();

                return true;
            }

            DB::rollBack();

            return false;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function deleteQuotation(Quotation $quotation): bool
    {
        DB::beginTransaction();

        try {
            $deleted = $this->quotationRepository->delete($quotation->id);

            if ($deleted) {
                DB::commit();

                return true;
            }

            DB::rollBack();

            return false;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function generateCompanyQuotes(Quotation $quotation): array
    {
        $companies = InsuranceCompany::where('status', 1)
            ->limit(5)
            ->get();

        $quotes = [];
        foreach ($companies as $company) {
            $quotes[] = $this->generateCompanyQuote($quotation, $company);
        }

        $this->setRecommendations($quotation);

        return $quotes;
    }

    public function getQuotationStatistics(): array
    {
        return [
            'total' => $this->quotationRepository->count(),
            'pending' => $this->quotationRepository->countByStatus('Pending'),
            'sent' => $this->quotationRepository->countByStatus('Sent'),
            'converted' => $this->quotationRepository->countByStatus('Converted'),
        ];
    }

    public function findById(int $id): ?Quotation
    {
        return $this->quotationRepository->findById($id);
    }

    public function getActiveQuotations(): Collection
    {
        return $this->quotationRepository->getActive();
    }

    // Private helper methods from original service
    private function calculateTotalIdv(array $data): float
    {
        return ($data['idv_vehicle'] ?? 0) +
               ($data['idv_trailer'] ?? 0) +
               ($data['idv_cng_lpg_kit'] ?? 0) +
               ($data['idv_electrical_accessories'] ?? 0) +
               ($data['idv_non_electrical_accessories'] ?? 0);
    }

    private function generateCompanyQuote(Quotation $quotation, InsuranceCompany $company): QuotationCompany
    {
        $baseData = $this->calculateBasePremium($quotation, $company);
        $addonData = $this->calculateAddonPremiums($quotation, $company);

        $netPremium = $baseData['total_od_premium'] + $addonData['total_addon_premium'];
        $sgstAmount = $netPremium * 0.09;
        $cgstAmount = $netPremium * 0.09;
        $totalPremium = $netPremium + $sgstAmount + $cgstAmount;
        $roadsideAssistance = $this->calculateRoadsideAssistance($company);
        $finalPremium = $totalPremium + $roadsideAssistance;

        return QuotationCompany::create([
            'quotation_id' => $quotation->id,
            'insurance_company_id' => $company->id,
            'quote_number' => $this->generateQuoteNumber($quotation, $company),
            'basic_od_premium' => $baseData['basic_od_premium'],
            'cng_lpg_premium' => $baseData['cng_lpg_premium'],
            'total_od_premium' => $baseData['total_od_premium'],
            'addon_covers_breakdown' => $addonData['breakdown'],
            'total_addon_premium' => $addonData['total_addon_premium'],
            'net_premium' => $netPremium,
            'sgst_amount' => $sgstAmount,
            'cgst_amount' => $cgstAmount,
            'total_premium' => $totalPremium,
            'roadside_assistance' => $roadsideAssistance,
            'final_premium' => $finalPremium,
            'benefits' => $this->getCompanyBenefits($company),
            'exclusions' => $this->getCompanyExclusions($company),
        ]);
    }

    private function calculateBasePremium(Quotation $quotation, InsuranceCompany $company): array
    {
        $idv = $quotation->total_idv;
        $companyFactor = $this->getCompanyRatingFactor($company);
        $basicRate = $this->getBasicOdRate($quotation);
        $basicOdPremium = ($idv * $basicRate / 100) * $companyFactor;

        $cngLpgPremium = 0;
        if (in_array($quotation->fuel_type, ['CNG', 'Hybrid']) && $quotation->idv_cng_lpg_kit > 0) {
            $cngLpgPremium = ($quotation->idv_cng_lpg_kit * 0.05) * $companyFactor;
        }

        return [
            'basic_od_premium' => round($basicOdPremium, 2),
            'cng_lpg_premium' => round($cngLpgPremium, 2),
            'total_od_premium' => round($basicOdPremium + $cngLpgPremium, 2),
        ];
    }

    private function calculateAddonPremiums(Quotation $quotation, InsuranceCompany $company): array
    {
        $addons = $quotation->addon_covers ?? [];
        $breakdown = [];
        $totalAddonPremium = 0;
        $companyFactor = $this->getCompanyRatingFactor($company);
        $addonRates = $this->getAddonRates($company);

        foreach ($addons as $addon) {
            $premium = $this->calculateAddonPremium($addon, $quotation, $addonRates, $companyFactor);
            if ($premium > 0) {
                $breakdown[$addon] = $premium;
                $totalAddonPremium += $premium;
            }
        }

        return [
            'breakdown' => $breakdown,
            'total_addon_premium' => round($totalAddonPremium, 2),
        ];
    }

    private function calculateAddonPremium(string $addon, Quotation $quotation, array $rates, float $companyFactor): float
    {
        $idv = $quotation->total_idv;

        return match ($addon) {
            'Zero Depreciation' => ($idv * ($rates['depreciation'] ?? 0.4) / 100) * $companyFactor,
            'Engine Protection' => ($idv * ($rates['engine_secure'] ?? 0.1) / 100) * $companyFactor,
            'Road Side Assistance' => 180 * $companyFactor,
            'NCB Protection' => ($idv * 0.05 / 100) * $companyFactor,
            'Invoice Protection' => ($idv * ($rates['return_to_invoice'] ?? 0.23) / 100) * $companyFactor,
            'Key Replacement' => 425 * $companyFactor,
            'Personal Accident' => 450 * $companyFactor,
            'Tyre Protection' => ($idv * ($rates['tyre_secure'] ?? 0.18) / 100) * $companyFactor,
            'Consumables' => ($idv * ($rates['consumables'] ?? 0.06) / 100) * $companyFactor,
            default => 0,
        };
    }

    private function setRecommendations(Quotation $quotation): void
    {
        $quotes = $quotation->quotationCompanies()->orderBy('final_premium')->get();

        foreach ($quotes as $index => $quote) {
            $quote->update(['ranking' => $index + 1]);
        }

        $recommended = $quotes->first();
        if ($recommended) {
            $recommended->update(['is_recommended' => true]);
        }
    }

    private function createManualCompanyQuotes(Quotation $quotation, array $companies): void
    {
        foreach ($companies as $index => $companyData) {
            if (empty($companyData['insurance_company_id'])) {
                continue;
            }

            $this->createManualCompanyQuote($quotation, $companyData);
        }

        $this->setRankings($quotation);
    }

    private function createManualCompanyQuote(Quotation $quotation, array $data): QuotationCompany
    {
        return QuotationCompany::create([
            'quotation_id' => $quotation->id,
            'insurance_company_id' => $data['insurance_company_id'],
            'quote_number' => $data['quote_number'] ?? $this->generateQuoteNumber($quotation, $data['insurance_company_id']),
            'basic_od_premium' => $data['basic_od_premium'] ?? 0,
            'total_od_premium' => $data['total_od_premium'] ?? $data['basic_od_premium'] ?? 0,
            'addon_covers_breakdown' => $data['addon_covers_breakdown'] ?? [],
            'total_addon_premium' => $data['total_addon_premium'] ?? 0,
            'net_premium' => $data['net_premium'] ?? 0,
            'sgst_amount' => $data['sgst_amount'] ?? 0,
            'cgst_amount' => $data['cgst_amount'] ?? 0,
            'total_premium' => $data['total_premium'] ?? 0,
            'roadside_assistance' => $data['roadside_assistance'] ?? 0,
            'final_premium' => $data['final_premium'] ?? 0,
            'is_recommended' => $data['is_recommended'] ?? false,
            'ranking' => $data['ranking'] ?? 1,
        ]);
    }

    private function setRankings(Quotation $quotation): void
    {
        $quotes = $quotation->quotationCompanies()->orderBy('final_premium')->get();

        foreach ($quotes as $index => $quote) {
            $quote->update(['ranking' => $index + 1]);
        }
    }

    private function generateQuoteNumber(Quotation $quotation, $companyId): string
    {
        $microtime = (string) microtime(true);
        $uniqueId = str_replace('.', '', $microtime);
        $uniqueId = substr($uniqueId, -8);

        return 'QT/'.date('y').'/'.str_pad($quotation->id, 4, '0', STR_PAD_LEFT).
               str_pad($companyId, 2, '0', STR_PAD_LEFT).
               $uniqueId;
    }

    private function getCompanyRatingFactor(InsuranceCompany $company): float
    {
        return match ($company->name) {
            'TATA AIG' => 1.0,
            'HDFC ERGO' => 0.95,
            'ICICI Lombard' => 1.05,
            'Bajaj Allianz' => 0.98,
            'Reliance General' => 0.92,
            default => 1.0,
        };
    }

    private function getBasicOdRate(Quotation $quotation): float
    {
        $vehicleAge = date('Y') - $quotation->manufacturing_year;

        if ($vehicleAge <= 1) {
            return 1.2;
        }
        if ($vehicleAge <= 3) {
            return 1.8;
        }
        if ($vehicleAge <= 5) {
            return 2.4;
        }

        return 3.0;
    }

    private function getAddonRates(InsuranceCompany $company): array
    {
        return [
            'depreciation' => 0.4,
            'consumables' => 0.06,
            'tyre_secure' => 0.18,
            'engine_secure' => 0.1,
            'return_to_invoice' => 0.23,
        ];
    }

    private function calculateRoadsideAssistance(InsuranceCompany $company): float
    {
        return 136.88;
    }

    private function getCompanyBenefits(InsuranceCompany $company): string
    {
        return 'Comprehensive coverage with add-on benefits, 24/7 customer support, quick claim settlement, nationwide network of garages.';
    }

    private function getCompanyExclusions(InsuranceCompany $company): string
    {
        return 'Pre-existing damages, wear and tear, consequential damages, driving under influence, use for commercial purposes.';
    }
}
