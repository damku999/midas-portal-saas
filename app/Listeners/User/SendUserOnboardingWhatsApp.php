<?php

namespace App\Listeners\User;

use App\Events\User\UserRegistered;
use App\Services\NotificationLoggerService;
use App\Traits\WhatsAppApiTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Send onboarding WhatsApp notification to newly registered users
 *
 * This listener sends welcome messages via WhatsApp when an admin user is registered.
 * It runs asynchronously to avoid blocking the registration process.
 */
class SendUserOnboardingWhatsApp implements ShouldQueue
{
    use WhatsAppApiTrait;

    /**
     * Create the event listener.
     */
    public function __construct(
        private NotificationLoggerService $notificationLogger
    ) {}

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        // Check if user has mobile number
        if (empty($user->mobile_number)) {
            Log::info('User onboarding WhatsApp skipped - no mobile number', [
                'user_id' => $user->id,
                'user_name' => $user->full_name,
            ]);

            return;
        }

        // Check if WhatsApp notifications are enabled
        if (! is_whatsapp_notification_enabled()) {
            Log::info('User onboarding WhatsApp skipped (disabled in settings)', [
                'user_id' => $user->id,
            ]);

            return;
        }

        // Send WhatsApp notification
        $this->sendWhatsAppNotification($user);
    }

    /**
     * Send WhatsApp onboarding notification.
     */
    protected function sendWhatsAppNotification($user): void
    {
        $notificationLog = null;

        try {
            // Generate welcome message
            $message = $this->generateWelcomeMessage($user);

            // Log the notification before sending
            $notificationLog = $this->notificationLogger->logNotification(
                $user,
                'whatsapp',
                $user->mobile_number,
                $message,
                [
                    'notification_type_code' => 'user_welcome',
                    'user_role' => $user->role_id,
                ]
            );

            // Send the message
            $result = $this->whatsAppSendMessage($message, $user->mobile_number);

            // Update log status based on result
            if ($result) {
                $this->notificationLogger->markAsSent($notificationLog, ['channel' => 'whatsapp']);
                Log::info('User onboarding WhatsApp sent successfully', [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'mobile_number' => $user->mobile_number,
                ]);
            } else {
                $this->notificationLogger->markAsFailed($notificationLog, 'WhatsApp API returned false');
                Log::warning('User onboarding WhatsApp failed to send', [
                    'user_id' => $user->id,
                    'mobile_number' => $user->mobile_number,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('User onboarding WhatsApp listener failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update notification log if it was created
            if ($notificationLog) {
                $this->notificationLogger->markAsFailed($notificationLog, $e->getMessage());
            }

            // Don't re-throw - we don't want to fail the whole user creation
        }
    }

    /**
     * Generate welcome message for user.
     */
    protected function generateWelcomeMessage($user): string
    {
        $companyName = company_name();
        $userName = $user->full_name ?: $user->first_name;
        $roleText = $user->role_id === 1 ? 'Admin' : 'User';

        return <<<MESSAGE
🎉 *Welcome to {$companyName}!*

Hello {$userName},

Your {$roleText} account has been successfully created!

📧 *Login Email:* {$user->email}

You can now access the portal and manage your tasks.

If you have any questions, please contact our support team.

Best regards,
{$companyName} Team
MESSAGE;
    }

    /**
     * Determine whether the listener should be queued.
     */
    public function shouldQueue(UserRegistered $event): bool
    {
        // Queue if user has mobile number and WhatsApp notifications are enabled
        return ! empty($event->user->mobile_number) && is_whatsapp_notification_enabled();
    }
}
