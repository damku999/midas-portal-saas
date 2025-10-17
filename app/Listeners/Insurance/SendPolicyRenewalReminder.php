<?php

namespace App\Listeners\Insurance;

use App\Events\Insurance\PolicyExpiringWarning;
use App\Services\CustomerInsuranceService;
use App\Services\EmailService;
use App\Services\Notification\NotificationContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendPolicyRenewalReminder implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private CustomerInsuranceService $customerInsuranceService,
        private EmailService $emailService
    ) {}

    public function handle(PolicyExpiringWarning $event): void
    {
        try {
            $policy = $event->policy;
            $customer = $policy->customer;

            // Send email reminder if customer has email
            if ($event->shouldSendEmail() && ! empty($customer->email)) {
                $this->sendEmailReminder($event);
            }

            // Send WhatsApp reminder if appropriate
            if ($event->shouldSendWhatsApp() && ! empty($customer->mobile_number)) {
                $this->sendWhatsAppReminder($event);
            }

        } catch (\Throwable $e) {
            Log::error('Policy renewal reminder listener failed', [
                'policy_id' => $event->policy->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to allow Laravel's failed job handling
            throw $e;
        }
    }

    private function sendEmailReminder(PolicyExpiringWarning $event): void
    {
        try {
            // Check if email notifications are enabled
            if (! is_email_notification_enabled()) {
                Log::info('Policy renewal email skipped (disabled in settings)', [
                    'policy_id' => $event->policy->id,
                ]);

                return;
            }

            $policy = $event->policy;
            $customer = $policy->customer;

            // Determine notification type based on days until expiry
            $daysUntilExpiry = $event->daysToExpiry;
            $notificationTypeCode = match (true) {
                $daysUntilExpiry <= 0 => 'renewal_expired',
                $daysUntilExpiry <= 7 => 'renewal_7_days',
                $daysUntilExpiry <= 15 => 'renewal_15_days',
                default => 'renewal_30_days'
            };

            // Create notification context with insurance data
            $context = new NotificationContext;
            $context->insurance = $policy;
            $context->customer = $customer;
            $context->customData = [
                'days_to_expiry' => $daysUntilExpiry,
                'warning_type' => $event->warningType,
            ];

            // Send via EmailService (uses template system)
            $sent = $this->emailService->sendTemplatedEmail(
                to: $customer->email,
                notificationTypeCode: $notificationTypeCode,
                context: $context
            );

            if ($sent) {
                Log::info('Policy renewal email sent successfully', [
                    'policy_id' => $policy->id,
                    'customer_id' => $customer->id,
                    'days_to_expiry' => $daysUntilExpiry,
                    'notification_type' => $notificationTypeCode,
                ]);
            } else {
                Log::warning('Policy renewal email sending returned false', [
                    'policy_id' => $policy->id,
                    'customer_id' => $customer->id,
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('Email renewal reminder failed', [
                'policy_id' => $event->policy->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't throw - allow WhatsApp reminder to still send
        }
    }

    private function sendWhatsAppReminder(PolicyExpiringWarning $event): void
    {
        try {
            // Check if WhatsApp notifications are enabled
            if (! is_whatsapp_notification_enabled()) {
                Log::info('Policy renewal WhatsApp skipped (disabled in settings)', [
                    'policy_id' => $event->policy->id,
                ]);

                return;
            }

            // Use CustomerInsuranceService which already has template integration
            // This will use the appropriate renewal template based on days until expiry
            $this->customerInsuranceService->sendRenewalReminderWhatsApp($event->policy);

            Log::info('Policy renewal WhatsApp sent successfully', [
                'policy_id' => $event->policy->id,
                'days_to_expiry' => $event->daysToExpiry,
            ]);

        } catch (\Throwable $e) {
            Log::error('WhatsApp renewal reminder failed', [
                'policy_id' => $event->policy->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function getEmailSubject(PolicyExpiringWarning $event): string
    {
        return match ($event->warningType) {
            'urgent' => "🚨 URGENT: Policy expires in {$event->daysToExpiry} days",
            'important' => '⏰ Important: Policy renewal required',
            'early' => '📋 Policy Renewal Notice',
            default => '📋 Policy Renewal Reminder'
        };
    }

    private function getEmailMessage(PolicyExpiringWarning $event): string
    {
        $policy = $event->policy;
        $customer = $policy->customer;

        return "Dear {$customer->name},\n\n".
               "This is a reminder that your insurance policy is expiring soon:\n\n".
               "Policy Number: {$policy->policy_number}\n".
               'Policy Type: '.($policy->policyType->name ?? 'Insurance Policy')."\n".
               'Insurance Company: '.($policy->insuranceCompany->name ?? 'Insurance Company')."\n".
               'Expiry Date: '.($policy->policy_end_date?->format('d/m/Y') ?? 'N/A')."\n".
               "Days to Expiry: {$event->daysToExpiry}\n\n".
               "Please contact us to renew your policy and avoid any coverage gaps.\n\n".
               "Best regards,\nYour Insurance Team";
    }

    private function getWhatsAppMessage(PolicyExpiringWarning $event): string
    {
        $policy = $event->policy;
        $customer = $policy->customer;
        $emoji = $event->isUrgent() ? '🚨' : '⏰';

        return "{$emoji} Hi {$customer->name}!\n\n".
               "Your insurance policy is expiring soon:\n\n".
               "📋 Policy: {$policy->policy_number}\n".
               "📅 Expires: {$policy->policy_end_date?->format('d/m/Y')}\n".
               "⏳ Days left: {$event->daysToExpiry}\n\n".
               "Renew now to avoid coverage gap:\n".
               route('customer.policies.renew', $policy->id)."\n\n".
               "Need help? Reply to this message.\n\n".
               'Stay protected! 🛡️';
    }

    public function failed(PolicyExpiringWarning $event, \Throwable $exception): void
    {
        \Log::error('Failed to send policy renewal reminder', [
            'policy_id' => $event->policy->id,
            'customer_id' => $event->policy->customer_id,
            'days_to_expiry' => $event->daysToExpiry,
            'warning_type' => $event->warningType,
            'error' => $exception->getMessage(),
        ]);
    }
}
