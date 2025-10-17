<?php

namespace App\Listeners;

use App\Events\PolicyExpiring;
use App\Mail\PolicyExpiryReminderMail;
use App\Traits\WhatsAppApiTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendPolicyReminderNotification implements ShouldQueue
{
    use WhatsAppApiTrait;

    public function handle(PolicyExpiring $event): void
    {
        $policy = $event->policy;
        $customer = $policy->customer;

        // Send email reminder
        if ($customer->email) {
            Mail::to($customer->email)->send(new PolicyExpiryReminderMail($policy, $event->daysToExpiry));
        }

        // Send WhatsApp reminder
        if ($customer->mobile_number) {
            $message = "Dear {$customer->name}, your insurance policy {$policy->policy_no} will expire in {$event->daysToExpiry} days. Please contact us for renewal.";
            $this->sendWhatsAppMessage($customer->mobile_number, $message);
        }
    }
}
