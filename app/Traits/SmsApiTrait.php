<?php

namespace App\Traits;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

trait SmsApiTrait
{
    /**
     * Get SMS provider from config (twilio, nexmo, sns)
     */
    protected function getSmsProvider(): string
    {
        return config('notifications.sms_provider', 'twilio');
    }

    /**
     * Get Twilio Account SID from config
     */
    protected function getTwilioAccountSid(): string
    {
        return config('notifications.twilio_account_sid', env('TWILIO_ACCOUNT_SID', ''));
    }

    /**
     * Get Twilio Auth Token from config
     */
    protected function getTwilioAuthToken(): string
    {
        return config('notifications.twilio_auth_token', env('TWILIO_AUTH_TOKEN', ''));
    }

    /**
     * Get Twilio Phone Number from config
     */
    protected function getTwilioFromNumber(): string
    {
        return config('notifications.twilio_from_number', env('TWILIO_FROM_NUMBER', ''));
    }

    /**
     * Get SMS sender ID from config
     */
    protected function getSmsSenderId(): string
    {
        return config('notifications.sms_sender_id', 'InsureAdv');
    }

    /**
     * Check if SMS notifications are enabled
     */
    protected function isSmsNotificationEnabled(): bool
    {
        return config('notifications.sms_enabled', false);
    }

    /**
     * Get SMS character limit
     */
    protected function getSmsCharacterLimit(): int
    {
        return config('notifications.sms_character_limit', 160);
    }

    /**
     * Send SMS via configured provider
     *
     * @param  string  $messageText  Message content
     * @param  string  $receiverId  Phone number
     * @param  int|null  $customerId  Customer ID for logging
     * @param  string|null  $notificationTypeCode  Notification type code
     * @return array Response with success status and details
     */
    protected function sendSms(
        string $messageText,
        string $receiverId,
        ?int $customerId = null,
        ?string $notificationTypeCode = null
    ): array {
        // Check if SMS notifications are enabled globally
        if (! $this->isSmsNotificationEnabled()) {
            Log::info('SMS notification skipped (disabled in settings)', [
                'receiver' => $receiverId,
            ]);

            return ['success' => false, 'message' => 'SMS notifications disabled'];
        }

        // Validate and format mobile number
        $formattedNumber = $this->validateAndFormatMobileNumber($receiverId);

        if (! $formattedNumber) {
            return ['success' => false, 'message' => "Invalid mobile number format: {$receiverId}"];
        }

        // Truncate message if exceeds character limit
        $characterLimit = $this->getSmsCharacterLimit();
        if (strlen($messageText) > $characterLimit) {
            $messageText = substr($messageText, 0, $characterLimit - 3).'...';
            Log::warning('SMS message truncated to fit character limit', [
                'original_length' => strlen($messageText),
                'character_limit' => $characterLimit,
            ]);
        }

        // Create notification log
        $log = $this->createSmsLog($customerId, $notificationTypeCode, $formattedNumber, $messageText);

        try {
            // Send via configured provider
            $provider = $this->getSmsProvider();

            $response = match ($provider) {
                'twilio' => $this->sendViaTwilio($messageText, $formattedNumber),
                'nexmo' => $this->sendViaNexmo($messageText, $formattedNumber),
                'sns' => $this->sendViaSns($messageText, $formattedNumber),
                default => throw new \Exception("Unsupported SMS provider: {$provider}"),
            };

            // Update log as sent
            if ($response['success']) {
                $log->markAsSent($response['metadata'] ?? []);
            } else {
                $log->markAsFailed($response['message'] ?? 'Unknown error', $response['metadata'] ?? []);
            }

            return $response;

        } catch (\Exception $e) {
            // Mark log as failed
            $log->markAsFailed($e->getMessage());

            Log::error('SMS sending failed', [
                'receiver' => $formattedNumber,
                'provider' => $this->getSmsProvider(),
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send SMS via Twilio
     *
     * @param  string  $messageText  Message content
     * @param  string  $toNumber  Phone number
     * @return array Response
     */
    protected function sendViaTwilio(string $messageText, string $toNumber): array
    {
        $accountSid = $this->getTwilioAccountSid();
        $authToken = $this->getTwilioAuthToken();
        $fromNumber = $this->getTwilioFromNumber();

        if (empty($accountSid) || empty($authToken) || empty($fromNumber)) {
            throw new \Exception('Twilio credentials not configured');
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'From' => $fromNumber,
                'To' => $toNumber,
                'Body' => $messageText,
            ]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic '.base64_encode("{$accountSid}:{$authToken}"),
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError) {
            throw new \Exception("Twilio API connection failed: {$curlError}");
        }

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'metadata' => [
                    'message_sid' => $decodedResponse['sid'] ?? null,
                    'status' => $decodedResponse['status'] ?? null,
                    'provider' => 'twilio',
                ],
            ];
        } else {
            $errorMessage = $decodedResponse['message'] ?? "HTTP {$httpCode}";
            throw new \Exception("Twilio API error: {$errorMessage}");
        }
    }

    /**
     * Send SMS via Nexmo/Vonage
     *
     * @param  string  $messageText  Message content
     * @param  string  $toNumber  Phone number
     * @return array Response
     */
    protected function sendViaNexmo(string $messageText, string $toNumber): array
    {
        // Nexmo implementation placeholder
        throw new \Exception('Nexmo SMS provider not yet implemented');
    }

    /**
     * Send SMS via AWS SNS
     *
     * @param  string  $messageText  Message content
     * @param  string  $toNumber  Phone number
     * @return array Response
     */
    protected function sendViaSns(string $messageText, string $toNumber): array
    {
        // AWS SNS implementation placeholder
        throw new \Exception('AWS SNS SMS provider not yet implemented');
    }

    /**
     * Create SMS notification log
     *
     * @param  int|null  $customerId  Customer ID
     * @param  string|null  $notificationTypeCode  Notification type code
     * @param  string  $recipient  Phone number
     * @param  string  $messageContent  Message content
     */
    protected function createSmsLog(
        ?int $customerId,
        ?string $notificationTypeCode,
        string $recipient,
        string $messageContent
    ): NotificationLog {
        $notificationTypeId = null;

        if ($notificationTypeCode) {
            $notificationType = \App\Models\NotificationType::where('code', $notificationTypeCode)->first();
            $notificationTypeId = $notificationType?->id;
        }

        return NotificationLog::create([
            'customer_id' => $customerId,
            'notification_type_id' => $notificationTypeId,
            'channel' => 'sms',
            'recipient' => $recipient,
            'message_content' => $messageContent,
            'status' => 'pending',
        ]);
    }

    /**
     * Shorten URL for SMS
     *
     * @param  string  $url  Long URL
     * @return string Shortened URL
     */
    protected function shortenUrl(string $url): string
    {
        // Simple URL shortening - returns original URL
        // FUTURE ENHANCEMENT: Integrate with URL shortening service (bit.ly, TinyURL, etc.)
        // This is optional and not critical for current SMS functionality
        // Implementation would require:
        // - API credentials for shortening service
        // - Caching layer for shortened URLs
        // - Fallback handling if service unavailable
        return $url;
    }

    /**
     * Truncate SMS message to fit character limit
     *
     * @param  string  $message  Original message
     * @param  int|null  $limit  Character limit (default from config)
     * @return string Truncated message
     */
    protected function truncateSmsMessage(string $message, ?int $limit = null): string
    {
        $limit = $limit ?? $this->getSmsCharacterLimit();

        if (strlen($message) <= $limit) {
            return $message;
        }

        return substr($message, 0, $limit - 3).'...';
    }
}
