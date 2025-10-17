<?php

namespace App\Events\Customer;

use App\Models\Customer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Customer $customer;

    public array $metadata;

    public string $registrationChannel;

    public function __construct(Customer $customer, array $metadata = [], string $registrationChannel = 'web')
    {
        $this->customer = $customer;
        $this->metadata = $metadata;
        $this->registrationChannel = $registrationChannel;
    }

    public function getEventData(): array
    {
        return [
            'customer_id' => $this->customer->id,
            'customer_email' => $this->customer->email,
            'customer_name' => $this->customer->name,
            'registration_channel' => $this->registrationChannel,
            'registration_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $this->metadata,
        ];
    }

    public function shouldQueue(): bool
    {
        return true;
    }
}
