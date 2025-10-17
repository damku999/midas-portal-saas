<?php

namespace App\Services;

use App\Models\Customer;
use App\Services\Notification\NotificationContext;
use App\Traits\SmsApiTrait;
use Illuminate\Support\Facades\Log;

class SmsService
{
    use SmsApiTrait;

    public function __construct(
        protected TemplateService $templateService
    ) {}

    /**
     * Send templated SMS notification with URL shortening.
     *
     * This method orchestrates SMS delivery using notification templates:
     * 1. Renders message content from notification template system
     * 2. Returns false if template not found (no fallback for SMS)
     * 3. Shortens all URLs in message to save SMS characters
     * 4. Sends SMS via provider API (using SmsApiTrait)
     * 5. Logs operations for tracking and debugging
     *
     * Accepts both NotificationContext (new) and array (legacy) for backward
     * compatibility during template system migration.
     *
     * Returns false on failures to prevent disrupting main workflows,
     * allowing graceful degradation when SMS delivery fails.
     *
     * @param  string  $to  Recipient phone number (with country code)
     * @param  string  $notificationTypeCode  Notification type code (e.g., 'renewal_7_days')
     * @param  NotificationContext|array  $context  Context for template variables (new) or legacy array
     * @param  int|null  $customerId  Optional customer ID for logging and tracking
     * @return bool True on successful send, false on template missing or sending error
     */
    public function sendTemplatedSms(
        string $to,
        string $notificationTypeCode,
        NotificationContext|array $context,
        ?int $customerId = null
    ): bool {
        try {
            // Render template
            $message = $this->templateService->render($notificationTypeCode, 'sms', $context);

            if (in_array($message, [null, '', '0'], true)) {
                Log::warning('SMS template not found', [
                    'notification_type' => $notificationTypeCode,
                    'channel' => 'sms',
                ]);

                return false;
            }

            // Shorten URLs in message if any
            $message = $this->shortenUrlsInMessage($message);

            // Send SMS
            $response = $this->sendSms($message, $to, $customerId, $notificationTypeCode);

            return $response['success'] ?? false;

        } catch (\Exception $exception) {
            Log::error('Templated SMS sending failed', [
                'notification_type' => $notificationTypeCode,
                'to' => $to,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send SMS to customer using their primary mobile number with preference checking.
     *
     * Convenience method that validates customer mobile number exists, checks
     * customer notification preferences, and sends templated SMS. Respects:
     * - Channel preferences: Customer can disable SMS channel entirely
     * - Opt-out lists: Customer can opt out of specific notification types
     * - Quiet hours: SMS blocked during customer-defined quiet hours
     *
     * Returns false if customer has no mobile number or has opted out,
     * preventing unwanted SMS and respecting customer communication preferences.
     *
     * Critical for compliance: honors customer preferences to avoid spam complaints.
     *
     * @param  Customer  $customer  Customer with mobile number and preferences
     * @param  string  $notificationTypeCode  Notification type code
     * @param  NotificationContext|array  $context  Context for template variables
     * @return bool True on successful send, false if no mobile or preferences block sending
     */
    public function sendToCustomer(
        Customer $customer,
        string $notificationTypeCode,
        NotificationContext|array $context
    ): bool {
        if (empty($customer->mobile)) {
            Log::warning('Customer has no mobile number', [
                'customer_id' => $customer->id,
            ]);

            return false;
        }

        // Check customer SMS preferences
        if (! $this->canSendSmsToCustomer($customer, $notificationTypeCode)) {
            Log::info('SMS skipped due to customer preferences', [
                'customer_id' => $customer->id,
                'notification_type' => $notificationTypeCode,
            ]);

            return false;
        }

        return $this->sendTemplatedSms(
            $customer->mobile,
            $notificationTypeCode,
            $context,
            $customer->id
        );
    }

    /**
     * Check if SMS can be sent to customer based on notification preferences.
     *
     * Evaluates customer notification_preferences JSON field for SMS permission:
     *
     * 1. Channel check: If 'sms' not in preferences.channels array, return false
     * 2. Opt-out check: If notification type in preferences.opt_out_types, return false
     * 3. Quiet hours check: If current time within preferences.quiet_hours range, return false
     *
     * Default behavior: Returns true if preferences not set (opt-in by default).
     *
     * Example preferences structure:
     * {
     *   "channels": ["sms", "email"],
     *   "opt_out_types": ["renewal_30_days"],
     *   "quiet_hours": {"start": "22:00", "end": "08:00"}
     * }
     *
     * @param  Customer  $customer  Customer with notification_preferences JSON field
     * @param  string  $notificationTypeCode  Notification type to check
     * @return bool True if SMS allowed, false if blocked by preferences
     */
    protected function canSendSmsToCustomer(Customer $customer, string $notificationTypeCode): bool
    {
        $preferences = $customer->notification_preferences ?? [];

        // Check if SMS channel is enabled for customer
        if (isset($preferences['channels']) && is_array($preferences['channels']) && ! in_array('sms', $preferences['channels'])) {
            return false;
        }

        // Check if customer opted out of this notification type
        if (isset($preferences['opt_out_types']) && is_array($preferences['opt_out_types']) && in_array($notificationTypeCode, $preferences['opt_out_types'])) {
            return false;
        }

        // Check quiet hours
        if (isset($preferences['quiet_hours'])) {
            $quietHours = $preferences['quiet_hours'];
            $currentHour = now()->format('H:i');

            if (
                isset($quietHours['start']) &&
                isset($quietHours['end']) &&
                $currentHour >= $quietHours['start'] &&
                $currentHour <= $quietHours['end']
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Shorten all URLs in SMS message to save character count.
     *
     * Uses regex to find all URLs (http:// or https://) in message and
     * passes each to shortenUrl() method from SmsApiTrait. This reduces
     * SMS character usage, potentially reducing message cost.
     *
     * Currently shortenUrl() returns original URL (no external service),
     * but method structure supports future integration with URL shortening
     * services like bit.ly or TinyURL.
     *
     * @param  string  $message  Message potentially containing URLs
     * @return string Message with URLs replaced by shortened versions
     */
    protected function shortenUrlsInMessage(string $message): string
    {
        // Find URLs in message
        $pattern = '/(https?:\/\/[^\s]+)/i';

        return preg_replace_callback($pattern, fn ($matches): string => $this->shortenUrl($matches[0]), $message);
    }

    /**
     * Send plain SMS without template rendering.
     *
     * Direct SMS sending method that bypasses template system. Useful for:
     * - One-off notifications not needing templates
     * - Custom messages generated programmatically
     * - Testing SMS delivery
     * - Admin-initiated manual messages
     *
     * Message sent as-is without URL shortening or template variable resolution.
     * Uses SmsApiTrait sendSms() method for actual delivery.
     *
     * @param  string  $to  Recipient phone number (with country code)
     * @param  string  $message  Complete message content (no template processing)
     * @param  int|null  $customerId  Optional customer ID for logging
     * @return bool True on successful send, false on sending error
     */
    public function sendPlainSms(string $to, string $message, ?int $customerId = null): bool
    {
        try {
            $response = $this->sendSms($message, $to, $customerId);

            return $response['success'] ?? false;
        } catch (\Exception $exception) {
            Log::error('Plain SMS sending failed', [
                'to' => $to,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get SMS delivery status from provider API.
     *
     * Placeholder method for checking SMS delivery status via provider API
     * (e.g., Twilio, Nexmo). Currently returns 'unknown' status.
     *
     * Future implementation would:
     * 1. Query SMS provider API with message SID
     * 2. Parse delivery status (delivered, failed, pending)
     * 3. Return structured status information
     * 4. Update notification logs with delivery confirmation
     *
     * Implementation requires:
     * - Provider API credentials
     * - API client library
     * - Message SID tracking in notification logs
     *
     * @param  string  $messageSid  Provider message identifier (e.g., Twilio SID)
     * @return array Status information array (currently returns ['status' => 'unknown'])
     */
    public function getDeliveryStatus(string $messageSid): array
    {
        // Implementation for checking delivery status via Twilio API
        // This is optional and can be implemented if needed
        return ['status' => 'unknown'];
    }
}
