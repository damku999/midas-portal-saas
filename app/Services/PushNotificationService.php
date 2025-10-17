<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\NotificationType;
use App\Services\Notification\NotificationContext;
use App\Traits\PushNotificationTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    use PushNotificationTrait;

    public function __construct(
        protected TemplateService $templateService
    ) {}

    /**
     * Send templated push notification to one or multiple devices.
     *
     * This method orchestrates push notification delivery with template rendering:
     * 1. Renders title from 'push_title' template channel
     * 2. Renders body from 'push' template channel (fallback to 'sms' if not found)
     * 3. Uses default title from NotificationType if template not found
     * 4. Builds data payload with deep links (insurance/quotation/claim)
     * 5. Routes to single or multiple device sending based on token parameter type
     *
     * Accepts both NotificationContext (new) and array (legacy) for backward
     * compatibility. Returns structured response with success status and details.
     *
     * Data payload includes:
     * - notification_type: Code for identifying notification in app
     * - deep_link: App navigation URI (e.g., app://insurance/123)
     * - insurance_id/quotation_id/claim_id: Related entity IDs
     *
     * @param  string|array  $deviceToken  FCM device token or array of tokens
     * @param  string  $notificationTypeCode  Notification type code
     * @param  NotificationContext|array  $context  Context for template variables
     * @param  int|null  $customerId  Optional customer ID for logging
     * @return array Response with ['success' => bool, 'message' => string, 'sent' => int, 'failed' => int]
     */
    public function sendTemplatedPush(
        string|array $deviceToken,
        string $notificationTypeCode,
        NotificationContext|array $context,
        ?int $customerId = null
    ): array {
        try {
            // Render title template
            $title = $this->templateService->render($notificationTypeCode, 'push_title', $context);

            // Render body template (use push or fallback to SMS/WhatsApp)
            $body = $this->templateService->render($notificationTypeCode, 'push', $context);

            if (in_array($body, [null, '', '0'], true)) {
                // Fallback to SMS template if push template not found
                $body = $this->templateService->render($notificationTypeCode, 'sms', $context);
            }

            if (in_array($body, [null, '', '0'], true)) {
                Log::warning('Push template not found', [
                    'notification_type' => $notificationTypeCode,
                    'channel' => 'push',
                ]);

                return ['success' => false, 'message' => 'Template not found'];
            }

            // Use default title if not specified
            if (in_array($title, [null, '', '0'], true)) {
                $title = $this->getDefaultTitle($notificationTypeCode);
            }

            // Get additional data from context
            $data = $this->buildDataPayload($context, $notificationTypeCode);

            // Send to single or multiple devices
            if (is_array($deviceToken)) {
                return $this->sendPushToMultipleDevices(
                    $deviceToken,
                    $title,
                    $body,
                    $data,
                    $customerId,
                    $notificationTypeCode
                );
            }

            return $this->sendPushToDevice(
                $deviceToken,
                $title,
                $body,
                $data,
                $customerId,
                $notificationTypeCode
            );

        } catch (\Exception $exception) {
            Log::error('Templated push notification failed', [
                'notification_type' => $notificationTypeCode,
                'error' => $exception->getMessage(),
            ]);

            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    /**
     * Send push notification to all customer's active devices with preference checking.
     *
     * Convenience method that:
     * 1. Checks customer push notification preferences (channel/opt-out/quiet hours)
     * 2. Fetches all active devices registered for customer
     * 3. Renders templates for title and body
     * 4. Sends push to all devices simultaneously
     *
     * Returns early if customer opted out or has no active devices.
     * Supports multiple devices per customer (iOS + Android, multiple phones/tablets).
     *
     * Response includes success/failure counts per device for monitoring delivery rates.
     *
     * @param  Customer  $customer  Customer with devices and preferences
     * @param  string  $notificationTypeCode  Notification type code
     * @param  NotificationContext|array  $context  Context for template variables
     * @return array Response with total/sent/failed counts and success status
     */
    public function sendToCustomer(
        Customer $customer,
        string $notificationTypeCode,
        NotificationContext|array $context
    ): array {
        // Check customer push preferences
        if (! $this->canSendPushToCustomer($customer, $notificationTypeCode)) {
            Log::info('Push notification skipped due to customer preferences', [
                'customer_id' => $customer->id,
                'notification_type' => $notificationTypeCode,
            ]);

            return [
                'success' => false,
                'message' => 'Customer opted out or preferences prevent sending',
                'total' => 0,
                'sent' => 0,
                'failed' => 0,
            ];
        }

        // Get all active devices
        $devices = CustomerDevice::query()->where('customer_id', $customer->id)
            ->where('is_active', true)
            ->get();

        if ($devices->isEmpty()) {
            Log::info('No active devices found for customer', [
                'customer_id' => $customer->id,
            ]);

            return [
                'success' => false,
                'message' => 'No active devices found',
                'total' => 0,
                'sent' => 0,
                'failed' => 0,
            ];
        }

        $deviceTokens = $devices->pluck('device_token')->toArray();

        // Render templates
        $title = $this->templateService->render($notificationTypeCode, 'push_title', $context);
        $body = $this->templateService->render($notificationTypeCode, 'push', $context);

        if (in_array($body, [null, '', '0'], true)) {
            $body = $this->templateService->render($notificationTypeCode, 'sms', $context);
        }

        if (in_array($title, [null, '', '0'], true)) {
            $title = $this->getDefaultTitle($notificationTypeCode);
        }

        $data = $this->buildDataPayload($context, $notificationTypeCode);

        return $this->sendPushToMultipleDevices(
            $deviceTokens,
            $title,
            $body,
            $data,
            $customer->id,
            $notificationTypeCode
        );
    }

    /**
     * Send rich push notification with image attachment.
     *
     * Sends enhanced push notification with image displayed in notification:
     * - Uses standard title and body parameters
     * - Adds image URL to data payload for client rendering
     * - Supports single device or multiple devices
     *
     * Rich notifications provide higher engagement rates by showing visual
     * content directly in notification drawer/center.
     *
     * Image requirements (client-side):
     * - HTTPS URL required
     * - Recommended: JPG/PNG, < 1MB size
     * - Aspect ratio: 2:1 for best display
     *
     * @param  string|array  $deviceToken  FCM device token(s)
     * @param  string  $title  Notification title
     * @param  string  $body  Notification body text
     * @param  string  $imageUrl  HTTPS image URL to display
     * @param  array  $data  Additional custom data payload
     * @param  int|null  $customerId  Optional customer ID for logging
     * @return array Response with success status and delivery counts
     */
    public function sendRichPush(
        string|array $deviceToken,
        string $title,
        string $body,
        string $imageUrl,
        array $data = [],
        ?int $customerId = null
    ): array {
        // Add image to data payload
        $data['image'] = $imageUrl;

        if (is_array($deviceToken)) {
            return $this->sendPushToMultipleDevices($deviceToken, $title, $body, $data, $customerId);
        }

        return $this->sendPushToDevice($deviceToken, $title, $body, $data, $customerId);
    }

    /**
     * Send push notification with interactive action buttons.
     *
     * Sends notification with action buttons enabling direct user response:
     * - Standard title and body text
     * - Action buttons added to data payload for client rendering
     * - Enables quick actions without opening app
     *
     * Example actions structure:
     * [
     *   ['action' => 'view', 'title' => 'View Policy'],
     *   ['action' => 'renew', 'title' => 'Renew Now'],
     *   ['action' => 'dismiss', 'title' => 'Later']
     * ]
     *
     * Client app handles action callbacks based on 'action' field.
     * Improves engagement by reducing friction for common workflows.
     *
     * @param  string|array  $deviceToken  FCM device token(s)
     * @param  string  $title  Notification title
     * @param  string  $body  Notification body text
     * @param  array  $actions  Action button definitions (action + title pairs)
     * @param  array  $data  Additional custom data payload
     * @param  int|null  $customerId  Optional customer ID for logging
     * @return array Response with success status and delivery counts
     */
    public function sendPushWithActions(
        string|array $deviceToken,
        string $title,
        string $body,
        array $actions,
        array $data = [],
        ?int $customerId = null
    ): array {
        // Add actions to data payload
        $data['actions'] = $actions;

        if (is_array($deviceToken)) {
            return $this->sendPushToMultipleDevices($deviceToken, $title, $body, $data, $customerId);
        }

        return $this->sendPushToDevice($deviceToken, $title, $body, $data, $customerId);
    }

    /**
     * Check if push notification can be sent to customer based on preferences.
     *
     * Evaluates customer notification_preferences JSON field for push permission.
     * Same logic as SMS/Email preference checking for consistency:
     *
     * 1. Channel check: If 'push' not in preferences.channels array, return false
     * 2. Opt-out check: If notification type in preferences.opt_out_types, return false
     * 3. Quiet hours check: If current time within preferences.quiet_hours range, return false
     *
     * Default behavior: Returns true if preferences not set (opt-in by default).
     *
     * Example preferences structure:
     * {
     *   "channels": ["push", "email"],
     *   "opt_out_types": ["renewal_30_days"],
     *   "quiet_hours": {"start": "22:00", "end": "08:00"}
     * }
     *
     * @param  Customer  $customer  Customer with notification_preferences JSON field
     * @param  string  $notificationTypeCode  Notification type to check
     * @return bool True if push allowed, false if blocked by preferences
     */
    protected function canSendPushToCustomer(Customer $customer, string $notificationTypeCode): bool
    {
        $preferences = $customer->notification_preferences ?? [];

        // Check if push channel is enabled
        if (isset($preferences['channels']) && is_array($preferences['channels']) && ! in_array('push', $preferences['channels'])) {
            return false;
        }

        // Check opt-out types
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
     * Build data payload from notification context with deep linking.
     *
     * Constructs JSON data payload sent with push notification for:
     * - Client-side notification routing
     * - Deep linking to specific app screens
     * - Contextual data for notification handling
     *
     * Always includes:
     * - notification_type: Code for client-side identification
     *
     * Conditionally includes based on context:
     * - deep_link: App navigation URI (app://insurance/{id})
     * - insurance_id/quotation_id/claim_id: Related entity ID
     *
     * Deep links enable tapping notification to navigate directly to
     * relevant insurance policy, quotation, or claim screen.
     *
     * @param  NotificationContext|array  $context  Context with insurance/quotation/claim
     * @param  string  $notificationTypeCode  Notification type code
     * @return array Data payload with notification type and deep links
     */
    protected function buildDataPayload(
        NotificationContext|array $context,
        string $notificationTypeCode
    ): array {
        $data = [
            'notification_type' => $notificationTypeCode,
        ];

        // Add deep link based on notification type
        if ($context instanceof NotificationContext) {
            if ($context->hasInsurance()) {
                $data['deep_link'] = 'app://insurance/'.$context->insurance->id;
                $data['insurance_id'] = (string) $context->insurance->id;
            } elseif ($context->hasQuotation()) {
                $data['deep_link'] = 'app://quotation/'.$context->quotation->id;
                $data['quotation_id'] = (string) $context->quotation->id;
            } elseif ($context->hasClaim()) {
                $data['deep_link'] = 'app://claim/'.$context->claim->id;
                $data['claim_id'] = (string) $context->claim->id;
            }
        }

        return $data;
    }

    /**
     * Get default push notification title from notification type.
     *
     * Fetches human-readable title from NotificationType model by code.
     * Falls back to formatted code (underscores to spaces, title case)
     * if notification type not found in database.
     *
     * Examples:
     * - 'renewal_7_days' NotificationType.name → "7 Day Renewal Reminder"
     * - 'unknown_code' (not in DB) → "Unknown Code"
     *
     * Used when push_title template channel is empty or not found.
     *
     * @param  string  $notificationTypeCode  Notification type code
     * @return string Human-readable notification title
     */
    protected function getDefaultTitle(string $notificationTypeCode): string
    {
        // Get from NotificationType model
        $notificationType = NotificationType::query()->where('code', $notificationTypeCode)->first();

        if ($notificationType) {
            return $notificationType->name;
        }

        // Fallback to formatted code
        return ucwords(str_replace('_', ' ', $notificationTypeCode));
    }

    /**
     * Register customer device for push notifications.
     *
     * Creates or updates CustomerDevice record with FCM device token.
     * Delegates to registerDeviceToken() method from PushNotificationTrait
     * which handles token uniqueness and device updates.
     *
     * Device types:
     * - 'android': Android devices using FCM
     * - 'ios': iOS devices using APNS via FCM
     * - 'web': Web push notifications
     *
     * Device info may include:
     * - app_version: Client app version
     * - os_version: Operating system version
     * - device_model: Hardware model name
     *
     * @param  int  $customerId  Customer ID to associate device with
     * @param  string  $deviceToken  FCM device token from client
     * @param  string  $deviceType  Device platform (android/ios/web)
     * @param  array  $deviceInfo  Optional device metadata
     * @return CustomerDevice Created or updated device record
     */
    public function registerDevice(
        int $customerId,
        string $deviceToken,
        string $deviceType = 'android',
        array $deviceInfo = []
    ): CustomerDevice {
        return $this->registerDeviceToken($customerId, $deviceToken, $deviceType, $deviceInfo);
    }

    /**
     * Unregister device from push notifications.
     *
     * Deletes CustomerDevice record by FCM token, stopping all future
     * push notifications to this device. Used when:
     * - User logs out of app
     * - User uninstalls app (client should call on logout)
     * - Device token becomes invalid
     *
     * Hard delete (not soft delete) as device tokens are regenerated
     * and no historical value in keeping old tokens.
     *
     * @param  string  $deviceToken  FCM device token to unregister
     * @return bool True if device found and deleted, false if not found
     */
    public function unregisterDevice(string $deviceToken): bool
    {
        return CustomerDevice::query()->where('device_token', $deviceToken)->delete();
    }

    /**
     * Get all active devices registered for customer.
     *
     * Fetches CustomerDevice records where is_active = true.
     * Useful for:
     * - Device management UI (show registered devices)
     * - Testing push notifications
     * - Auditing device registrations
     * - Manual device cleanup
     *
     * Active devices receive push notifications when sendToCustomer() is called.
     *
     * @param  int  $customerId  Customer ID to fetch devices for
     * @return Collection Active device records
     */
    public function getCustomerDevices(int $customerId)
    {
        return CustomerDevice::query()->where('customer_id', $customerId)
            ->where('is_active', true)
            ->get();
    }
}
