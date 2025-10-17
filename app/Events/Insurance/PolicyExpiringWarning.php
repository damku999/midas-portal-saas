<?php

namespace App\Events\Insurance;

use App\Models\CustomerInsurance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PolicyExpiringWarning
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CustomerInsurance $policy;

    public int $daysToExpiry;

    public string $warningType;

    public bool $isFirstWarning;

    public function __construct(CustomerInsurance $policy, int $daysToExpiry, bool $isFirstWarning = false)
    {
        $this->policy = $policy;
        $this->daysToExpiry = $daysToExpiry;
        $this->isFirstWarning = $isFirstWarning;

        $this->warningType = match (true) {
            $daysToExpiry <= 7 => 'urgent',
            $daysToExpiry <= 30 => 'important',
            $daysToExpiry <= 60 => 'early',
            default => 'advance'
        };
    }

    public function getEventData(): array
    {
        return [
            'customer_insurance_id' => $this->policy->id,
            'customer_id' => $this->policy->customer_id,
            'policy_number' => $this->policy->policy_number,
            'insurance_company_id' => $this->policy->insurance_company_id,
            'policy_type_id' => $this->policy->policy_type_id,
            'expiry_date' => $this->policy->policy_end_date?->format('Y-m-d'),
            'days_to_expiry' => $this->daysToExpiry,
            'warning_type' => $this->warningType,
            'is_first_warning' => $this->isFirstWarning,
            'premium_amount' => $this->policy->premium_amount,
            'sum_assured' => $this->policy->sum_assured,
            'customer_email' => $this->policy->customer->email ?? null,
            'customer_mobile' => $this->policy->customer->mobile ?? null,
        ];
    }

    public function isUrgent(): bool
    {
        return $this->warningType === 'urgent';
    }

    public function isHighValue(): bool
    {
        return $this->policy->sum_assured > 1000000;
    }

    public function shouldSendWhatsApp(): bool
    {
        return $this->daysToExpiry <= 15 && ! empty($this->policy->customer->mobile);
    }

    public function shouldSendEmail(): bool
    {
        return ! empty($this->policy->customer->email);
    }

    public function shouldQueue(): bool
    {
        return true;
    }
}
