<?php

namespace App\Events\Insurance;

use App\Models\CustomerInsurance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PolicyRenewed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CustomerInsurance $originalPolicy;

    public CustomerInsurance $renewedPolicy;

    public array $renewalChanges;

    public ?int $renewedBy;

    public string $renewalType;

    public function __construct(
        CustomerInsurance $originalPolicy,
        CustomerInsurance $renewedPolicy,
        array $renewalChanges = [],
        string $renewalType = 'manual',
        ?int $renewedBy = null
    ) {
        $this->originalPolicy = $originalPolicy;
        $this->renewedPolicy = $renewedPolicy;
        $this->renewalChanges = $renewalChanges;
        $this->renewalType = $renewalType; // manual, automatic, customer_initiated
        $this->renewedBy = $renewedBy ?? auth()->id();
    }

    public function getEventData(): array
    {
        return [
            'original_policy_id' => $this->originalPolicy->id,
            'renewed_policy_id' => $this->renewedPolicy->id,
            'customer_id' => $this->renewedPolicy->customer_id,
            'renewal_type' => $this->renewalType,
            'renewal_changes' => $this->renewalChanges,
            'premium_change' => [
                'old' => $this->originalPolicy->premium_amount,
                'new' => $this->renewedPolicy->premium_amount,
                'difference' => $this->renewedPolicy->premium_amount - $this->originalPolicy->premium_amount,
            ],
            'coverage_change' => [
                'old' => $this->originalPolicy->sum_assured,
                'new' => $this->renewedPolicy->sum_assured,
            ],
            'renewed_by' => $this->renewedBy,
            'renewed_at' => $this->renewedPolicy->created_at->format('Y-m-d H:i:s'),
            'days_before_expiry' => $this->getDaysBeforeExpiry(),
        ];
    }

    public function isPremiumIncrease(): bool
    {
        return $this->renewedPolicy->premium_amount > $this->originalPolicy->premium_amount;
    }

    public function getCoverageIncrease(): float
    {
        return $this->renewedPolicy->sum_assured - $this->originalPolicy->sum_assured;
    }

    public function getDaysBeforeExpiry(): int
    {
        if (! $this->originalPolicy->policy_end_date) {
            return 0;
        }

        return $this->originalPolicy->policy_end_date->diffInDays($this->renewedPolicy->created_at, false);
    }

    public function shouldQueue(): bool
    {
        return true;
    }
}
