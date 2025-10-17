<?php

namespace App\Listeners\Quotation;

use App\Events\Quotation\QuotationGenerated;
use App\Services\QuotationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send quotation notifications via WhatsApp and Email
 *
 * Handles sending quotation notifications through multiple channels.
 * Renamed from SendQuotationWhatsApp to support both WhatsApp and Email.
 */
class SendQuotationWhatsApp implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private QuotationService $quotationService
    ) {}

    public function handle(QuotationGenerated $event): void
    {
        $quotation = $event->quotation;

        // Send WhatsApp notification
        $this->sendWhatsAppNotification($quotation);

        // Send Email notification
        $this->sendEmailNotification($quotation);
    }

    /**
     * Send quotation via WhatsApp.
     */
    protected function sendWhatsAppNotification($quotation): void
    {
        try {
            $customer = $quotation->customer;

            // Only send if customer has mobile number
            if (empty($customer->mobile_number)) {
                Log::info('Quotation WhatsApp skipped - no mobile number', [
                    'quotation_id' => $quotation->id,
                    'customer_id' => $customer->id,
                ]);

                return;
            }

            // Check if WhatsApp notifications are enabled
            if (! is_whatsapp_notification_enabled()) {
                Log::info('Quotation WhatsApp skipped (disabled in settings)', [
                    'quotation_id' => $quotation->id,
                ]);

                return;
            }

            // Send quotation WhatsApp message using QuotationService
            $this->quotationService->sendQuotationViaWhatsApp($quotation);

            Log::info('Quotation WhatsApp sent successfully', [
                'quotation_id' => $quotation->id,
                'customer_id' => $customer->id,
                'mobile_number' => $customer->mobile_number,
            ]);

        } catch (\Throwable $e) {
            Log::error('Quotation WhatsApp listener failed', [
                'quotation_id' => $quotation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't re-throw - email notification should still attempt to send
        }
    }

    /**
     * Send quotation via Email.
     */
    protected function sendEmailNotification($quotation): void
    {
        try {
            $customer = $quotation->customer;

            // Use quotation email if available, otherwise customer email
            $email = $quotation->email ?? $customer->email;

            // Only send if email is available
            if (empty($email)) {
                Log::info('Quotation email skipped - no email address', [
                    'quotation_id' => $quotation->id,
                    'customer_id' => $customer->id,
                ]);

                return;
            }

            // Check if email notifications are enabled
            if (! is_email_notification_enabled()) {
                Log::info('Quotation email skipped (disabled in settings)', [
                    'quotation_id' => $quotation->id,
                ]);

                return;
            }

            // Send quotation email using QuotationService
            $this->quotationService->sendQuotationViaEmail($quotation);

            Log::info('Quotation email sent successfully', [
                'quotation_id' => $quotation->id,
                'customer_id' => $customer->id,
                'email' => $email,
            ]);

        } catch (\Throwable $e) {
            Log::error('Quotation email listener failed', [
                'quotation_id' => $quotation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't re-throw - we still want the job to complete
        }
    }

    public function failed(QuotationGenerated $event, \Throwable $exception): void
    {
        \Log::error('Failed to send quotation notifications', [
            'quotation_id' => $event->quotation->id,
            'customer_id' => $event->quotation->customer_id,
            'customer_mobile' => $event->quotation->customer->mobile_number ?? 'N/A',
            'customer_email' => $event->quotation->customer->email ?? 'N/A',
            'error' => $exception->getMessage(),
        ]);
    }
}
