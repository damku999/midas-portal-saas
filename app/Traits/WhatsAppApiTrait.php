<?php

namespace App\Traits;

trait WhatsAppApiTrait
{
    /**
     * Get WhatsApp sender ID from config
     *
     * Note: This config value is dynamically loaded from app_settings table.
     * Managed via Admin Panel: /app-settings (whatsapp_sender_id)
     */
    protected function getSenderId(): string
    {
        return config('whatsapp.sender_id', '919727793123');
    }

    /**
     * Get WhatsApp API base URL from config
     *
     * Note: This config value is dynamically loaded from app_settings table.
     * Managed via Admin Panel: /app-settings (whatsapp_base_url)
     */
    protected function getBaseUrl(): string
    {
        return config('whatsapp.base_url', 'https://api.botmastersender.com/api/v1/');
    }

    /**
     * Get WhatsApp auth token from config
     *
     * Note: This config value is dynamically loaded from app_settings table.
     * Managed via Admin Panel: /app-settings (whatsapp_auth_token)
     */
    protected function getAuthToken(): string
    {
        return config('whatsapp.auth_token', '53eb1f03-90be-49ce-9dbe-b23fe982b31f');
    }

    /**
     * Check if WhatsApp notifications are enabled
     *
     * Note: This config value is dynamically loaded from app_settings table.
     * Managed via Admin Panel: /app-settings (whatsapp_notifications_enabled)
     */
    protected function isWhatsAppNotificationEnabled(): bool
    {
        return config('notifications.whatsapp_enabled', true);
    }

    // mediaurl
    protected function whatsAppSendMessage($messageText, $receiverId, $customerId = null, $notificationTypeCode = null)
    {
        // Check if WhatsApp notifications are enabled globally
        if (! $this->isWhatsAppNotificationEnabled()) {
            \Log::info('WhatsApp notification skipped (disabled in settings)', [
                'receiver' => $receiverId,
            ]);

            return json_encode(['success' => false, 'message' => 'WhatsApp notifications disabled']);
        }

        $formattedNumber = $this->validateAndFormatMobileNumber($receiverId);

        if (! $formattedNumber) {
            throw new \Exception("Invalid mobile number format: {$receiverId}");
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->getBaseUrl().'?action=send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYPEER => false, // Disable SSL verification for local development
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POSTFIELDS => [
                'senderId' => $this->getSenderId(),
                'authToken' => $this->getAuthToken(),
                'messageText' => $messageText,
                'receiverId' => $formattedNumber,
            ],
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError) {
            throw new \Exception("WhatsApp API connection failed: {$curlError}");
        }

        if ($httpCode !== 200) {
            // Log the actual response body for debugging
            \Log::error('WhatsApp API error response', [
                'http_code' => $httpCode,
                'response_body' => $response,
                'receiver' => $formattedNumber,
                'sender_id' => $this->getSenderId(),
            ]);
            throw new \Exception("WhatsApp API returned HTTP {$httpCode}: {$response}");
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON response from WhatsApp API: {$response}");
        }

        // Check if response indicates failure
        if (is_array($decodedResponse)) {
            foreach ($decodedResponse as $result) {
                if (isset($result['success']) && $result['success'] === false) {
                    $errorMsg = $result['message'] ?? 'Unknown error';

                    // Check for specific error conditions
                    if (isset($result['error']['status']) && $result['error']['status'] === 'session offline') {
                        throw new \Exception('WhatsApp session is offline. Please reconnect your WhatsApp session in BotMasterSender dashboard.');
                    }

                    if (isset($result['error']['error'])) {
                        $specificError = $result['error']['error'];
                        throw new \Exception("WhatsApp sending failed: {$specificError}");
                    }

                    throw new \Exception("WhatsApp sending failed: {$errorMsg}");
                }
            }
        }

        return $response;
    }

    protected function whatsAppSendMessageWithAttachment($messageText, $receiverId, $filePath)
    {
        // Check if WhatsApp notifications are enabled
        if (! $this->isWhatsAppNotificationEnabled()) {
            \Log::info('WhatsApp notification with attachment skipped (disabled in settings)', [
                'receiver' => $receiverId,
                'file' => $filePath,
            ]);

            return json_encode(['success' => false, 'message' => 'WhatsApp notifications disabled']);
        }

        $formattedNumber = $this->validateAndFormatMobileNumber($receiverId);

        if (! $formattedNumber) {
            throw new \Exception("Invalid mobile number format: {$receiverId}");
        }

        if (! file_exists($filePath)) {
            throw new \Exception("Attachment file not found: {$filePath}");
        }

        if (! is_readable($filePath)) {
            throw new \Exception("Attachment file is not readable: {$filePath}");
        }

        try {
            $curl = curl_init();
            $fileHandle = fopen($filePath, 'r');

            curl_setopt_array($curl, [
                CURLOPT_URL => $this->getBaseUrl().'?action=send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60, // Longer timeout for file uploads
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_SSL_VERIFYPEER => false, // Disable SSL verification for local development
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_POSTFIELDS => [
                    'senderId' => $this->getSenderId(),
                    'authToken' => $this->getAuthToken(),
                    'messageText' => $messageText,
                    'receiverId' => $formattedNumber,
                    'uploadFile' => curl_file_create($filePath, mime_content_type($filePath), basename($filePath)),
                ],
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);
            fclose($fileHandle);

            if ($curlError) {
                throw new \Exception("WhatsApp API connection failed: {$curlError}");
            }

            if ($httpCode !== 200) {
                // Log the actual response body for debugging
                \Log::error('WhatsApp API error response (with attachment)', [
                    'http_code' => $httpCode,
                    'response_body' => $response,
                    'receiver' => $formattedNumber,
                    'sender_id' => $this->getSenderId(),
                    'file_path' => $filePath,
                ]);
                throw new \Exception("WhatsApp API returned HTTP {$httpCode}: {$response}");
            }

            $decodedResponse = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response from WhatsApp API: {$response}");
            }

            // Check if response indicates failure
            if (is_array($decodedResponse)) {
                foreach ($decodedResponse as $result) {
                    if (isset($result['success']) && $result['success'] === false) {
                        $errorMsg = $result['message'] ?? 'Unknown error';

                        // Check for specific error conditions
                        if (isset($result['error']['status']) && $result['error']['status'] === 'session offline') {
                            throw new \Exception('WhatsApp session is offline. Please reconnect your WhatsApp session in BotMasterSender dashboard.');
                        }

                        if (isset($result['error']['error'])) {
                            $specificError = $result['error']['error'];
                            throw new \Exception("WhatsApp sending failed: {$specificError}");
                        }

                        throw new \Exception("WhatsApp sending failed: {$errorMsg}");
                    }
                }
            }

            return $response;

        } catch (\Throwable $th) {
            // Re-throw as a more descriptive exception
            throw new \Exception('WhatsApp message with attachment failed: '.$th->getMessage());
        }
    }

    public function validateAndFormatMobileNumber($mobileNumber)
    {
        // Remove any non-numeric characters
        $mobileNumber = preg_replace('/\D/', '', $mobileNumber);

        // Check if the number starts with '91'
        if (substr($mobileNumber, 0, 2) !== '91') {
            // If not, prepend '91'
            $mobileNumber = '91'.$mobileNumber;
        }

        // Check if the number is a valid Indian mobile number (10 digits starting with 91)
        if (preg_match('/^91[0-9]{10}$/', $mobileNumber)) {
            return $mobileNumber; // Return the formatted number
        } else {
            return false; // Return false if the number is invalid
        }
    }

    public function newCustomerAdd($customer)
    {
        return "Dear {$customer->name}

Welcome to the world of insurance solutions! I'm ".company_advisor_name().", your dedicated insurance advisor here to guide you through every step of your insurance journey. Whether you're seeking protection for your loved ones, securing your assets, or planning for the future, I'm committed to providing personalized advice and finding the perfect insurance solutions tailored to your needs. Feel free to reach out anytime with questions or concerns. Let's work together to safeguard what matters most to you!

Best regards,
".company_advisor_name().'
'.company_website().'
'.company_title().'
"'.company_tagline().'"';
    }

    public function insuranceAdded($customer_insurance)
    {
        $expired_date = date('d-m-Y', strtotime($customer_insurance->expired_date));
        $policy_detail = trim($customer_insurance->premiumType->name.' '.$customer_insurance->registration_no);

        return "Dear {$customer_insurance->customer->name}

Thank you for entrusting me with your insurance needs. Attached, you'll find the policy document with *Policy No. {$customer_insurance->policy_no}* of your *{$policy_detail}* which expire on *{$expired_date}*. If you have any questions or need further assistance, please don't hesitate to reach out.

Best regards,
".company_advisor_name().'
'.company_website().'
'.company_title().'
"'.company_tagline().'"';
    }

    public function renewalReminder($customer_insurance)
    {
        $expired_date = date('d-m-Y', strtotime($customer_insurance->expired_date));

        return "Dear *{$customer_insurance->customer->name}*

Your *{$customer_insurance->premiumType->name}*  Under Policy No *{$customer_insurance->policy_no}* of *{$customer_insurance->insuranceCompany->name}* is due for renewal on *{$expired_date}*. To ensure continuous coverage, please renew by the due date. For assistance, contact us at ".company_phone().'.

Best regards,
'.company_advisor_name().'
'.company_website().'
'.company_title().'
"'.company_tagline().'"';
    }

    public function renewalReminderVehicle($customer_insurance)
    {
        $expired_date = date('d-m-Y', strtotime($customer_insurance->expired_date));

        return "Dear *{$customer_insurance->customer->name}*

Your *{$customer_insurance->premiumType->name}* Under Policy No *{$customer_insurance->policy_no}* of *{$customer_insurance->insuranceCompany->name}* for Vehicle Number *{$customer_insurance->registration_no}* is due for renewal on *{$expired_date}*. To ensure continuous coverage, please renew by the due date. For assistance, contact us at ".company_phone().'.

Best regards,
'.company_advisor_name().'
'.company_website().'
'.company_title().'
"'.company_tagline().'"';
    }

    /**
     * Get message from template database or fallback to hardcoded.
     */
    protected function getMessageFromTemplate(string $notificationTypeCode, array $data): ?string
    {
        $templateService = app(\App\Services\TemplateService::class);

        return $templateService->render($notificationTypeCode, 'whatsapp', $data);
    }
}
