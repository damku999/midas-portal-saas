<?php

namespace App\Traits;

use App\Models\NotificationLog;
use App\Services\NotificationLoggerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Trait for logging notifications across all channels
 *
 * This trait provides helper methods to log notifications
 * before and after sending through any channel (WhatsApp, Email, SMS)
 */
trait LogsNotificationsTrait
{
    /**
     * Log and send WhatsApp message with automatic tracking
     *
     * @param  Model  $notifiable  The entity being notified
     * @param  string  $message  Message content
     * @param  string  $recipient  Phone number
     * @param  array  $options  Additional options
     * @return array ['success' => bool, 'log' => NotificationLog, 'response' => mixed]
     */
    protected function logAndSendWhatsApp(
        Model $notifiable,
        string $message,
        string $recipient,
        array $options = []
    ): array {
        $loggerService = app(NotificationLoggerService::class);

        // Create the log entry
        $log = $loggerService->logNotification(
            $notifiable,
            'whatsapp',
            $recipient,
            $message,
            $options
        );

        try {
            // Send WhatsApp message using trait method
            $response = $this->whatsAppSendMessage(
                $message,
                $recipient,
                $options['customer_id'] ?? null,
                $options['notification_type_code'] ?? null
            );

            // Decode response
            $decodedResponse = json_decode($response, true);

            // Mark as sent
            $loggerService->markAsSent($log, $decodedResponse ?? []);

            return [
                'success' => true,
                'log' => $log->fresh(),
                'response' => $decodedResponse,
            ];

        } catch (\Exception $e) {
            // Mark as failed
            $loggerService->markAsFailed($log, $e->getMessage(), [
                'exception' => get_class($e),
            ]);

            Log::error('WhatsApp send failed', [
                'log_id' => $log->id,
                'error' => $e->getMessage(),
                'recipient' => $recipient,
            ]);

            return [
                'success' => false,
                'log' => $log->fresh(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Log and send WhatsApp message with attachment
     *
     * @param  Model  $notifiable  The entity being notified
     * @param  string  $message  Message content
     * @param  string  $recipient  Phone number
     * @param  string  $filePath  Path to attachment
     * @param  array  $options  Additional options
     * @return array ['success' => bool, 'log' => NotificationLog, 'response' => mixed]
     */
    protected function logAndSendWhatsAppWithAttachment(
        Model $notifiable,
        string $message,
        string $recipient,
        string $filePath,
        array $options = []
    ): array {
        $loggerService = app(NotificationLoggerService::class);

        // Add attachment info to options
        $options['attachment'] = basename($filePath);

        // Create the log entry
        $log = $loggerService->logNotification(
            $notifiable,
            'whatsapp',
            $recipient,
            $message,
            $options
        );

        try {
            // Send WhatsApp message with attachment using trait method
            $response = $this->whatsAppSendMessageWithAttachment(
                $message,
                $recipient,
                $filePath
            );

            // Decode response
            $decodedResponse = json_decode($response, true);

            // Mark as sent
            $loggerService->markAsSent($log, $decodedResponse ?? []);

            return [
                'success' => true,
                'log' => $log->fresh(),
                'response' => $decodedResponse,
            ];

        } catch (\Exception $e) {
            // Mark as failed
            $loggerService->markAsFailed($log, $e->getMessage(), [
                'exception' => get_class($e),
                'file_path' => $filePath,
            ]);

            Log::error('WhatsApp send with attachment failed', [
                'log_id' => $log->id,
                'error' => $e->getMessage(),
                'recipient' => $recipient,
                'file' => $filePath,
            ]);

            return [
                'success' => false,
                'log' => $log->fresh(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Log and send Email
     *
     * @param  Model  $notifiable  The entity being notified
     * @param  string  $recipient  Email address
     * @param  string  $subject  Email subject
     * @param  string  $message  Email body
     * @param  array  $options  Additional options
     * @return array ['success' => bool, 'log' => NotificationLog, 'response' => mixed]
     */
    protected function logAndSendEmail(
        Model $notifiable,
        string $recipient,
        string $subject,
        string $message,
        array $options = []
    ): array {
        $loggerService = app(NotificationLoggerService::class);

        // Add subject to options
        $options['subject'] = $subject;

        // Create the log entry
        $log = $loggerService->logNotification(
            $notifiable,
            'email',
            $recipient,
            $message,
            $options
        );

        try {
            // Check if email notifications are enabled
            if (! is_email_notification_enabled()) {
                Log::info('Email sending skipped (disabled in settings)', [
                    'recipient' => $recipient,
                ]);

                $loggerService->markAsFailed($log, 'Email notifications disabled', [
                    'reason' => 'settings_disabled',
                ]);

                return [
                    'success' => false,
                    'log' => $log->fresh(),
                    'error' => 'Email notifications are disabled',
                ];
            }

            // Send email using Laravel Mail
            \Illuminate\Support\Facades\Mail::raw($message, function ($mail) use ($recipient, $subject, $options) {
                $mail->to($recipient)
                    ->subject($subject);

                // Support additional recipients if provided
                if (isset($options['cc'])) {
                    $mail->cc($options['cc']);
                }
                if (isset($options['bcc'])) {
                    $mail->bcc($options['bcc']);
                }
            });

            // Mark as sent
            $loggerService->markAsSent($log, [
                'sent_via' => 'laravel_mail',
                'subject' => $subject,
                'sent_at' => now()->toDateTimeString(),
            ]);

            Log::info('Email sent successfully', [
                'log_id' => $log->id,
                'recipient' => $recipient,
                'subject' => $subject,
            ]);

            return [
                'success' => true,
                'log' => $log->fresh(),
                'response' => ['sent' => true],
            ];

        } catch (\Exception $e) {
            // Mark as failed
            $loggerService->markAsFailed($log, $e->getMessage(), [
                'exception' => get_class($e),
            ]);

            Log::error('Email send failed', [
                'log_id' => $log->id,
                'error' => $e->getMessage(),
                'recipient' => $recipient,
            ]);

            return [
                'success' => false,
                'log' => $log->fresh(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get notification history for an entity
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function getNotificationHistory(Model $notifiable, array $filters = [])
    {
        $loggerService = app(NotificationLoggerService::class);

        return $loggerService->getNotificationHistory($notifiable, $filters);
    }
}
