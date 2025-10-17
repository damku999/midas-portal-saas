<?php

namespace App\Traits;

use App\Models\CustomerDevice;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

trait PushNotificationTrait
{
    /**
     * Get FCM Server Key from config
     */
    protected function getFcmServerKey(): string
    {
        return config('notifications.fcm_server_key', env('FCM_SERVER_KEY', ''));
    }

    /**
     * Get FCM Sender ID from config
     */
    protected function getFcmSenderId(): string
    {
        return config('notifications.fcm_sender_id', env('FCM_SENDER_ID', ''));
    }

    /**
     * Get FCM API URL
     */
    protected function getFcmApiUrl(): string
    {
        return 'https://fcm.googleapis.com/fcm/send';
    }

    /**
     * Check if push notifications are enabled
     */
    protected function isPushNotificationEnabled(): bool
    {
        return config('notifications.push_enabled', false);
    }

    /**
     * Get default push icon URL
     */
    protected function getDefaultPushIcon(): string
    {
        return config('notifications.push_default_icon', asset('images/logo.png'));
    }

    /**
     * Get default push sound
     */
    protected function getDefaultPushSound(): string
    {
        return config('notifications.push_default_sound', 'default');
    }

    /**
     * Send push notification to single device
     *
     * @param  string  $deviceToken  FCM device token
     * @param  string  $title  Notification title
     * @param  string  $body  Notification body
     * @param  array  $data  Additional data payload
     * @param  int|null  $customerId  Customer ID for logging
     * @param  string|null  $notificationTypeCode  Notification type code
     * @return array Response with success status
     */
    protected function sendPushToDevice(
        string $deviceToken,
        string $title,
        string $body,
        array $data = [],
        ?int $customerId = null,
        ?string $notificationTypeCode = null
    ): array {
        // Check if push notifications are enabled
        if (! $this->isPushNotificationEnabled()) {
            Log::info('Push notification skipped (disabled in settings)', [
                'device_token' => substr($deviceToken, 0, 20).'...',
            ]);

            return ['success' => false, 'message' => 'Push notifications disabled'];
        }

        // Validate FCM credentials
        $serverKey = $this->getFcmServerKey();
        if (empty($serverKey)) {
            return ['success' => false, 'message' => 'FCM server key not configured'];
        }

        // Create notification log
        $log = $this->createPushLog($customerId, $notificationTypeCode, $deviceToken, $title, $body);

        try {
            // Build FCM payload
            $payload = [
                'to' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => $this->getDefaultPushSound(),
                    'icon' => $this->getDefaultPushIcon(),
                ],
                'data' => array_merge($data, [
                    'notification_type' => $notificationTypeCode,
                    'timestamp' => now()->toIso8601String(),
                ]),
                'priority' => 'high',
            ];

            // Send to FCM
            $response = $this->sendToFcm($payload);

            // Update log
            if ($response['success']) {
                $log->markAsSent($response['metadata'] ?? []);

                // Update device last active
                $this->updateDeviceLastActive($deviceToken);
            } else {
                $log->markAsFailed($response['message'] ?? 'Unknown error', $response['metadata'] ?? []);

                // Check if token is invalid
                if ($this->isTokenInvalid($response)) {
                    $this->deactivateDevice($deviceToken);
                }
            }

            return $response;

        } catch (\Exception $e) {
            $log->markAsFailed($e->getMessage());

            Log::error('Push notification failed', [
                'device_token' => substr($deviceToken, 0, 20).'...',
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send push notification to multiple devices
     *
     * @param  array  $deviceTokens  Array of FCM device tokens
     * @param  string  $title  Notification title
     * @param  string  $body  Notification body
     * @param  array  $data  Additional data payload
     * @param  int|null  $customerId  Customer ID for logging
     * @param  string|null  $notificationTypeCode  Notification type code
     * @return array Response with success/failure counts
     */
    protected function sendPushToMultipleDevices(
        array $deviceTokens,
        string $title,
        string $body,
        array $data = [],
        ?int $customerId = null,
        ?string $notificationTypeCode = null
    ): array {
        $results = [
            'total' => count($deviceTokens),
            'success' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($deviceTokens as $token) {
            $response = $this->sendPushToDevice($token, $title, $body, $data, $customerId, $notificationTypeCode);

            if ($response['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'token' => substr($token, 0, 20).'...',
                'success' => $response['success'],
                'message' => $response['message'] ?? '',
            ];
        }

        return $results;
    }

    /**
     * Send push to all customer's active devices
     *
     * @param  int  $customerId  Customer ID
     * @param  string  $title  Notification title
     * @param  string  $body  Notification body
     * @param  array  $data  Additional data payload
     * @param  string|null  $notificationTypeCode  Notification type code
     * @return array Response with success/failure counts
     */
    protected function sendPushToCustomerDevices(
        int $customerId,
        string $title,
        string $body,
        array $data = [],
        ?string $notificationTypeCode = null
    ): array {
        // Get all active devices for customer
        $devices = CustomerDevice::where('customer_id', $customerId)
            ->where('is_active', true)
            ->get();

        if ($devices->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No active devices found for customer',
                'total' => 0,
                'sent' => 0,
                'failed' => 0,
            ];
        }

        $deviceTokens = $devices->pluck('device_token')->toArray();

        return $this->sendPushToMultipleDevices(
            $deviceTokens,
            $title,
            $body,
            $data,
            $customerId,
            $notificationTypeCode
        );
    }

    /**
     * Send to FCM API
     *
     * @param  array  $payload  FCM payload
     * @return array Response
     */
    protected function sendToFcm(array $payload): array
    {
        $serverKey = $this->getFcmServerKey();
        $url = $this->getFcmApiUrl();

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: key={$serverKey}",
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError) {
            throw new \Exception("FCM API connection failed: {$curlError}");
        }

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            // Check FCM response for success
            if (isset($decodedResponse['success']) && $decodedResponse['success'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Push notification sent successfully',
                    'metadata' => [
                        'message_id' => $decodedResponse['results'][0]['message_id'] ?? null,
                        'provider' => 'fcm',
                        'response' => $decodedResponse,
                    ],
                ];
            } elseif (isset($decodedResponse['failure']) && $decodedResponse['failure'] > 0) {
                $error = $decodedResponse['results'][0]['error'] ?? 'Unknown FCM error';
                throw new \Exception("FCM error: {$error}");
            }
        }

        $errorMessage = $decodedResponse['error'] ?? "HTTP {$httpCode}";
        throw new \Exception("FCM API error: {$errorMessage}");
    }

    /**
     * Create push notification log
     *
     * @param  int|null  $customerId  Customer ID
     * @param  string|null  $notificationTypeCode  Notification type code
     * @param  string  $deviceToken  Device token
     * @param  string  $title  Notification title
     * @param  string  $body  Notification body
     */
    protected function createPushLog(
        ?int $customerId,
        ?string $notificationTypeCode,
        string $deviceToken,
        string $title,
        string $body
    ): NotificationLog {
        $notificationTypeId = null;

        if ($notificationTypeCode) {
            $notificationType = \App\Models\NotificationType::where('code', $notificationTypeCode)->first();
            $notificationTypeId = $notificationType?->id;
        }

        return NotificationLog::create([
            'customer_id' => $customerId,
            'notification_type_id' => $notificationTypeId,
            'channel' => 'push',
            'recipient' => substr($deviceToken, 0, 50), // Truncate for storage
            'message_content' => json_encode(['title' => $title, 'body' => $body]),
            'status' => 'pending',
        ]);
    }

    /**
     * Check if FCM token is invalid
     *
     * @param  array  $response  FCM response
     * @return bool Token is invalid
     */
    protected function isTokenInvalid(array $response): bool
    {
        if (! isset($response['metadata']['response']['results'])) {
            return false;
        }

        $results = $response['metadata']['response']['results'];

        foreach ($results as $result) {
            if (isset($result['error'])) {
                $error = $result['error'];
                if (in_array($error, ['InvalidRegistration', 'NotRegistered', 'MismatchSenderId'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Update device last active timestamp
     *
     * @param  string  $deviceToken  Device token
     */
    protected function updateDeviceLastActive(string $deviceToken): void
    {
        CustomerDevice::where('device_token', $deviceToken)->update([
            'last_active_at' => now(),
        ]);
    }

    /**
     * Deactivate device
     *
     * @param  string  $deviceToken  Device token
     */
    protected function deactivateDevice(string $deviceToken): void
    {
        CustomerDevice::where('device_token', $deviceToken)->update([
            'is_active' => false,
        ]);

        Log::info('Device deactivated due to invalid token', [
            'device_token' => substr($deviceToken, 0, 20).'...',
        ]);
    }

    /**
     * Register or update device token
     *
     * @param  int  $customerId  Customer ID
     * @param  string  $deviceToken  FCM device token
     * @param  string  $deviceType  Device type (ios, android, web)
     * @param  array  $deviceInfo  Additional device information
     */
    protected function registerDeviceToken(
        int $customerId,
        string $deviceToken,
        string $deviceType = 'android',
        array $deviceInfo = []
    ): CustomerDevice {
        // Check if device token already exists
        $device = CustomerDevice::where('device_token', $deviceToken)->first();

        if ($device) {
            // Update existing device
            $device->update([
                'customer_id' => $customerId,
                'device_type' => $deviceType,
                'device_name' => $deviceInfo['device_name'] ?? $device->device_name,
                'device_model' => $deviceInfo['device_model'] ?? $device->device_model,
                'os_version' => $deviceInfo['os_version'] ?? $device->os_version,
                'app_version' => $deviceInfo['app_version'] ?? $device->app_version,
                'is_active' => true,
                'last_active_at' => now(),
            ]);
        } else {
            // Create new device
            $device = CustomerDevice::create([
                'customer_id' => $customerId,
                'device_token' => $deviceToken,
                'device_type' => $deviceType,
                'device_name' => $deviceInfo['device_name'] ?? null,
                'device_model' => $deviceInfo['device_model'] ?? null,
                'os_version' => $deviceInfo['os_version'] ?? null,
                'app_version' => $deviceInfo['app_version'] ?? null,
                'is_active' => true,
                'last_active_at' => now(),
            ]);
        }

        return $device;
    }
}
