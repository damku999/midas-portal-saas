<?php

namespace App\Listeners;

use App\Events\LeadAssigned;
use App\Events\LeadConverted;
use App\Events\LeadCreated;
use App\Events\LeadStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLeadNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle LeadCreated event
     */
    public function handleLeadCreated(LeadCreated $event): void
    {
        $lead = $event->lead;

        Log::info("New lead created: {$lead->lead_number} - {$lead->name}");

        // Send notification to assigned user if exists
        if ($lead->assignedUser) {
            $this->notifyUser(
                $lead->assignedUser->email,
                'New Lead Assigned',
                "A new lead '{$lead->name}' ({$lead->lead_number}) has been assigned to you."
            );
        }

        // Notify managers (optional - implement based on your requirements)
        $this->notifyManagers($lead, 'New lead created');
    }

    /**
     * Handle LeadStatusChanged event
     */
    public function handleLeadStatusChanged(LeadStatusChanged $event): void
    {
        $lead = $event->lead;
        $oldStatus = $event->oldStatus;
        $newStatus = $event->newStatus;

        Log::info("Lead status changed: {$lead->lead_number} from {$oldStatus->name} to {$newStatus->name}");

        // Notify assigned user
        if ($lead->assignedUser) {
            $this->notifyUser(
                $lead->assignedUser->email,
                'Lead Status Changed',
                "Lead '{$lead->name}' ({$lead->lead_number}) status changed from {$oldStatus->name} to {$newStatus->name}."
            );
        }

        // Special handling for converted or lost status
        if ($newStatus->is_converted) {
            $this->handleConversionNotification($lead);
        } elseif ($newStatus->is_lost) {
            $this->handleLostNotification($lead);
        }
    }

    /**
     * Handle LeadConverted event
     */
    public function handleLeadConverted(LeadConverted $event): void
    {
        $lead = $event->lead;
        $customer = $event->customer;
        $isNewCustomer = $event->isNewCustomer;

        Log::info("Lead converted: {$lead->lead_number} to customer {$customer->id}");

        // Notify assigned user
        if ($lead->assignedUser) {
            $message = $isNewCustomer
                ? "Lead '{$lead->name}' ({$lead->lead_number}) has been successfully converted to a new customer."
                : "Lead '{$lead->name}' ({$lead->lead_number}) has been linked to existing customer {$customer->name}.";

            $this->notifyUser(
                $lead->assignedUser->email,
                'Lead Converted Successfully',
                $message
            );
        }

        // Send welcome email to customer if new and has email
        if ($isNewCustomer && $customer->email) {
            $this->sendCustomerWelcomeEmail($customer);
        }
    }

    /**
     * Handle LeadAssigned event
     */
    public function handleLeadAssigned(LeadAssigned $event): void
    {
        $lead = $event->lead;
        $newUser = $event->newUser;
        $oldUser = $event->oldUser;

        Log::info("Lead assigned: {$lead->lead_number} to {$newUser->name}");

        // Notify new assigned user
        $this->notifyUser(
            $newUser->email,
            'New Lead Assigned',
            "Lead '{$lead->name}' ({$lead->lead_number}) has been assigned to you."
        );

        // Notify old user if existed
        if ($oldUser) {
            $this->notifyUser(
                $oldUser->email,
                'Lead Re-assigned',
                "Lead '{$lead->name}' ({$lead->lead_number}) has been re-assigned to {$newUser->name}."
            );
        }
    }

    /**
     * Send notification to user
     */
    protected function notifyUser(string $email, string $subject, string $message): void
    {
        try {
            // Placeholder - implement based on your notification system
            // Could use Mail, WhatsApp, SMS, or your notification service

            Log::info("Notification sent to {$email}: {$subject}");

            // Example email notification (uncomment when mail is configured):
            /*
            Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)->subject($subject);
            });
            */
        } catch (\Exception $e) {
            Log::error('Failed to send notification: '.$e->getMessage());
        }
    }

    /**
     * Notify managers about lead events
     */
    protected function notifyManagers($lead, string $event): void
    {
        // Placeholder - implement based on your requirements
        // Query manager users and send notifications

        Log::info("Manager notification: {$event} for lead {$lead->lead_number}");
    }

    /**
     * Handle conversion notification
     */
    protected function handleConversionNotification($lead): void
    {
        Log::info("Conversion notification for lead {$lead->lead_number}");

        // Send success metrics to managers
        // Trigger any post-conversion workflows
    }

    /**
     * Handle lost lead notification
     */
    protected function handleLostNotification($lead): void
    {
        Log::info("Lost lead notification for {$lead->lead_number}: {$lead->lost_reason}");

        // Notify managers for analysis
        // Track lost reasons for reporting
    }

    /**
     * Send welcome email to new customer
     */
    protected function sendCustomerWelcomeEmail($customer): void
    {
        try {
            Log::info("Welcome email sent to customer: {$customer->email}");

            // Implement customer welcome email
            /*
            Mail::send('emails.customer-welcome', ['customer' => $customer], function ($mail) use ($customer) {
                $mail->to($customer->email)->subject('Welcome to Our Service');
            });
            */
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email: '.$e->getMessage());
        }
    }
}
