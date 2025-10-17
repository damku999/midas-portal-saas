<?php

namespace App\Events;

use App\Models\CustomerInsurance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PolicyExpiring
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public CustomerInsurance $policy,
        public int $daysToExpiry
    ) {}
}
