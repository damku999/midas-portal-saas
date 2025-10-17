<?php

namespace App\Events\Quotation;

use App\Models\Customer;
use App\Models\PolicyType;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuotationRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Customer $customer;

    public PolicyType $policyType;

    public array $quotationData;

    public string $requestChannel;

    public ?int $requestedBy;

    public function __construct(
        Customer $customer,
        PolicyType $policyType,
        array $quotationData,
        string $requestChannel = 'web',
        ?int $requestedBy = null
    ) {
        $this->customer = $customer;
        $this->policyType = $policyType;
        $this->quotationData = $quotationData;
        $this->requestChannel = $requestChannel;
        $this->requestedBy = $requestedBy ?? auth()->id();
    }

    public function getEventData(): array
    {
        return [
            'customer_id' => $this->customer->id,
            'policy_type_id' => $this->policyType->id,
            'policy_type_name' => $this->policyType->name,
            'quotation_data' => $this->quotationData,
            'request_channel' => $this->requestChannel,
            'requested_by' => $this->requestedBy,
            'requested_at' => now()->format('Y-m-d H:i:s'),
            'customer_type' => $this->customer->customer_type,
            'vehicle_number' => $this->quotationData['vehicle_number'] ?? null,
            'sum_assured' => $this->quotationData['sum_assured'] ?? null,
        ];
    }

    public function isHighValue(): bool
    {
        $sumAssured = $this->quotationData['sum_assured'] ?? 0;

        return $sumAssured > 1000000; // 10 lakhs
    }

    public function shouldQueue(): bool
    {
        return true;
    }
}
