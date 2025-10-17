<?php

namespace App\Events\Insurance;

use App\Models\CustomerInsurance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PolicyCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CustomerInsurance $policy;

    public ?int $createdBy;

    public string $creationSource;

    public array $policyDetails;

    public function __construct(CustomerInsurance $policy, string $creationSource = 'admin', ?int $createdBy = null)
    {
        $this->policy = $policy;
        $this->createdBy = $createdBy ?? auth()->id();
        $this->creationSource = $creationSource;

        $this->policyDetails = [
            'policy_number' => $policy->policy_number,
            'premium_amount' => $policy->premium_amount,
            'sum_assured' => $policy->sum_assured,
            'policy_start_date' => $policy->policy_start_date?->format('Y-m-d'),
            'policy_end_date' => $policy->policy_end_date?->format('Y-m-d'),
            'commission_amount' => $policy->commission_amount,
        ];
    }

    public function getEventData(): array
    {
        return [
            'customer_insurance_id' => $this->policy->id,
            'customer_id' => $this->policy->customer_id,
            'insurance_company_id' => $this->policy->insurance_company_id,
            'policy_type_id' => $this->policy->policy_type_id,
            'policy_details' => $this->policyDetails,
            'created_by' => $this->createdBy,
            'creation_source' => $this->creationSource,
            'created_at' => $this->policy->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function isHighValuePolicy(): bool
    {
        return $this->policy->sum_assured > 1000000;
    }

    public function isPremiumPolicy(): bool
    {
        return $this->policy->premium_amount > 50000;
    }

    public function getDaysToExpiry(): int
    {
        if (! $this->policy->policy_end_date) {
            return 365; // Default assumption
        }

        return now()->diffInDays($this->policy->policy_end_date, false);
    }

    public function shouldQueue(): bool
    {
        return true;
    }
}
