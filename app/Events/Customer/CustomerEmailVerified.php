<?php

namespace App\Events\Customer;

use App\Models\Customer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerEmailVerified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Customer $customer;

    public \DateTime $verifiedAt;

    public string $verificationMethod;

    public function __construct(Customer $customer, string $verificationMethod = 'email_link')
    {
        $this->customer = $customer;
        $this->verifiedAt = now();
        $this->verificationMethod = $verificationMethod;
    }

    public function getEventData(): array
    {
        return [
            'customer_id' => $this->customer->id,
            'customer_email' => $this->customer->email,
            'verified_at' => $this->verifiedAt->format('Y-m-d H:i:s'),
            'verification_method' => $this->verificationMethod,
            'ip_address' => request()->ip(),
        ];
    }

    public function shouldQueue(): bool
    {
        return true;
    }
}
