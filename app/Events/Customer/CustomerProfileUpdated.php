<?php

namespace App\Events\Customer;

use App\Models\Customer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerProfileUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Customer $customer;

    public array $changedFields;

    public array $originalValues;

    public ?int $updatedBy;

    public function __construct(Customer $customer, array $changedFields, array $originalValues, ?int $updatedBy = null)
    {
        $this->customer = $customer;
        $this->changedFields = $changedFields;
        $this->originalValues = $originalValues;
        $this->updatedBy = $updatedBy ?? auth()->id();
    }

    public function getEventData(): array
    {
        return [
            'customer_id' => $this->customer->id,
            'changed_fields' => $this->changedFields,
            'original_values' => $this->originalValues,
            'updated_by' => $this->updatedBy,
            'updated_at' => now()->format('Y-m-d H:i:s'),
            'ip_address' => request()->ip(),
        ];
    }

    public function hasSignificantChanges(): bool
    {
        $significantFields = ['email', 'mobile', 'name', 'family_group_id'];

        return ! empty(array_intersect($this->changedFields, $significantFields));
    }

    public function shouldQueue(): bool
    {
        return true;
    }
}
