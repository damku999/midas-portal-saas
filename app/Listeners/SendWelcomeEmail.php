<?php

namespace App\Listeners;

use App\Events\CustomerCreated;
use App\Mail\CustomerWelcomeMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    public function handle(CustomerCreated $event): void
    {
        if ($event->customer->email) {
            Mail::to($event->customer->email)->send(new CustomerWelcomeMail($event->customer));
        }
    }
}
