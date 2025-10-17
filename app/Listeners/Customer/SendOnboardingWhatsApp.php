<?php

namespace App\Listeners\Customer;

use App\Events\Customer\CustomerRegistered;
use App\Services\CustomerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send onboarding notifications to newly registered customers
 *
 * This listener sends welcome messages via WhatsApp and Email using the customer_welcome template.
 * It runs asynchronously to avoid blocking the registration process.
 * Renamed from SendOnboardingWhatsApp to SendOnboardingNotifications to support both channels.
 */
class SendOnboardingWhatsApp implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private CustomerService $customerService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(CustomerRegistered $event): void
    {
        $customer = $event->customer;

        // Send WhatsApp notification
        $this->sendWhatsAppNotification($customer);

        // Send Email notification
        $this->sendEmailNotification($customer);
    }

    /**
     * Send WhatsApp onboarding notification.
     */
    protected function sendWhatsAppNotification($customer): void
    {
        try {
            // Check if customer has mobile number
            if (empty($customer->mobile_number)) {
                Log::info('Onboarding WhatsApp skipped - no mobile number', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                ]);

                return;
            }

            // Check if WhatsApp notifications are enabled
            if (! is_whatsapp_notification_enabled()) {
                Log::info('Onboarding WhatsApp skipped (disabled in settings)', [
                    'customer_id' => $customer->id,
                ]);

                return;
            }

            // Send onboarding WhatsApp message (uses customer_welcome template)
            $sent = $this->customerService->sendOnboardingMessage($customer);

            if ($sent) {
                Log::info('Onboarding WhatsApp sent successfully', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'mobile_number' => $customer->mobile_number,
                ]);
            } else {
                Log::warning('Onboarding WhatsApp failed to send', [
                    'customer_id' => $customer->id,
                    'mobile_number' => $customer->mobile_number,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Onboarding WhatsApp listener failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't re-throw - we don't want to fail the whole customer creation
        }
    }

    /**
     * Send Email onboarding notification.
     */
    protected function sendEmailNotification($customer): void
    {
        try {
            // Check if customer has email
            if (empty($customer->email)) {
                Log::info('Onboarding email skipped - no email address', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                ]);

                return;
            }

            // Check if email notifications are enabled
            if (! is_email_notification_enabled()) {
                Log::info('Onboarding email skipped (disabled in settings)', [
                    'customer_id' => $customer->id,
                ]);

                return;
            }

            // Send onboarding email (uses customer_welcome template)
            $sent = $this->customerService->sendOnboardingEmail($customer);

            if ($sent) {
                Log::info('Onboarding email sent successfully', [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'email' => $customer->email,
                ]);
            } else {
                Log::warning('Onboarding email failed to send', [
                    'customer_id' => $customer->id,
                    'email' => $customer->email,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Onboarding email listener failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't re-throw - we don't want to fail the whole customer creation
        }
    }

    /**
     * Determine whether the listener should be queued.
     */
    public function shouldQueue(CustomerRegistered $event): bool
    {
        // Queue if customer has either mobile number or email and respective notifications are enabled
        $hasWhatsApp = ! empty($event->customer->mobile_number) && is_whatsapp_notification_enabled();
        $hasEmail = ! empty($event->customer->email) && is_email_notification_enabled();

        return $hasWhatsApp || $hasEmail;
    }
}
