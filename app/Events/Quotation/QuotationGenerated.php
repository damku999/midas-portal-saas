<?php

namespace App\Events\Quotation;

use App\Models\Quotation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuotationGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Quotation $quotation;

    public int $companyCount;

    public array $companyPremiums;

    public ?float $bestPremium;

    public ?int $generatedBy;

    public function __construct(Quotation $quotation, ?int $generatedBy = null)
    {
        $this->quotation = $quotation;
        $this->generatedBy = $generatedBy ?? auth()->id();

        // Calculate quotation metrics
        $this->companyCount = $quotation->quotationCompanies()->count();
        $this->companyPremiums = $quotation->quotationCompanies()
            ->pluck('final_premium', 'insurance_company_id')
            ->toArray();
        $this->bestPremium = $quotation->quotationCompanies()->min('final_premium');
    }

    public function getEventData(): array
    {
        return [
            'quotation_id' => $this->quotation->id,
            'quotation_number' => $this->quotation->quotation_number,
            'customer_id' => $this->quotation->customer_id,
            'policy_type_id' => $this->quotation->policy_type_id,
            'company_count' => $this->companyCount,
            'best_premium' => $this->bestPremium,
            'total_premium_range' => [
                'min' => $this->bestPremium,
                'max' => ! empty($this->companyPremiums) ? max($this->companyPremiums) : 0,
            ],
            'generated_by' => $this->generatedBy,
            'generated_at' => $this->quotation->created_at->format('Y-m-d H:i:s'),
            'vehicle_number' => $this->quotation->vehicle_number,
            'sum_assured' => $this->quotation->sum_assured,
        ];
    }

    public function isHighValueQuotation(): bool
    {
        return $this->quotation->sum_assured > 1000000;
    }

    public function hasMultipleOptions(): bool
    {
        return $this->companyCount > 1;
    }

    public function shouldQueue(): bool
    {
        return true;
    }
}
