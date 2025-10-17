<?php

namespace App\Services;

use App\Models\NotificationDeliveryTracking;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\NotificationType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationLoggerService
{
    /**
     * Log a notification before sending
     *
     * @param  Model  $model  The entity being notified (Customer, Insurance, Quotation, Claim)
     * @param  string  $channel  Channel: whatsapp, email, sms
     * @param  string  $recipient  Phone number or email address
     * @param  string  $message  Message content
     * @param  array  $options  Additional options
     */
    public function logNotification(
        Model $model,
        string $channel,
        string $recipient,
        string $message,
        array $options = []
    ): NotificationLog {
        try {
            $notificationType = null;
            $template = null;

            // Get notification type if code provided
            if (isset($options['notification_type_code'])) {
                $notificationType = NotificationType::query()->where('code', $options['notification_type_code'])
                    ->where('is_active', true)
                    ->first();
            }

            // Get template if ID provided
            if (isset($options['template_id'])) {
                $template = NotificationTemplate::query()->find($options['template_id']);
            }

            // Create the log entry
            $log = NotificationLog::query()->create([
                'notifiable_type' => $model::class,
                'notifiable_id' => $model->id,
                'notification_type_id' => $notificationType?->id,
                'template_id' => $template?->id,
                'channel' => $channel,
                'recipient' => $recipient,
                'subject' => $options['subject'] ?? null,
                'message_content' => $message,
                'variables_used' => $options['variables'] ?? null,
                'status' => 'pending',
                'sent_by' => auth()->id(),
                'retry_count' => 0,
            ]);

            Log::info('Notification logged', [
                'log_id' => $log->id,
                'channel' => $channel,
                'recipient' => $recipient,
                'notifiable' => $model::class.'#'.$model->id,
            ]);

            return $log;

        } catch (\Exception $exception) {
            Log::error('Failed to log notification', [
                'error' => $exception->getMessage(),
                'channel' => $channel,
                'recipient' => $recipient,
            ]);
            throw $exception;
        }
    }

    /**
     * Update notification status to sent
     *
     * @param  array  $apiResponse  API provider response
     */
    public function markAsSent(NotificationLog $notificationLog, array $apiResponse = []): NotificationLog
    {
        DB::transaction(function () use ($notificationLog, $apiResponse): void {
            $notificationLog->update([
                'status' => 'sent',
                'sent_at' => now(),
                'api_response' => $apiResponse,
            ]);

            // Create tracking record
            $this->addDeliveryTracking($notificationLog, 'sent', $apiResponse);
        });

        return $notificationLog->fresh();
    }

    /**
     * Update notification status to delivered
     *
     * @param  array  $providerStatus  Provider status data
     */
    public function markAsDelivered(NotificationLog $notificationLog, array $providerStatus = []): NotificationLog
    {
        DB::transaction(function () use ($notificationLog, $providerStatus): void {
            $notificationLog->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

            // Create tracking record
            $this->addDeliveryTracking($notificationLog, 'delivered', $providerStatus);
        });

        return $notificationLog->fresh();
    }

    /**
     * Update notification status to read
     *
     * @param  array  $providerStatus  Provider status data
     */
    public function markAsRead(NotificationLog $notificationLog, array $providerStatus = []): NotificationLog
    {
        DB::transaction(function () use ($notificationLog, $providerStatus): void {
            $notificationLog->update([
                'status' => 'read',
                'read_at' => now(),
            ]);

            // Create tracking record
            $this->addDeliveryTracking($notificationLog, 'read', $providerStatus);
        });

        return $notificationLog->fresh();
    }

    /**
     * Mark notification as failed
     *
     * @param  string  $errorMessage  Error message
     * @param  array  $apiResponse  API response if available
     */
    public function markAsFailed(NotificationLog $notificationLog, string $errorMessage, array $apiResponse = []): NotificationLog
    {
        DB::transaction(function () use ($notificationLog, $errorMessage, $apiResponse): void {
            $retryCount = $notificationLog->retry_count + 1;
            $nextRetryAt = $this->calculateNextRetryTime($retryCount);

            $notificationLog->update([
                'status' => 'failed',
                'error_message' => $errorMessage,
                'api_response' => $apiResponse,
                'retry_count' => $retryCount,
                'next_retry_at' => $nextRetryAt,
            ]);

            // Create tracking record
            $this->addDeliveryTracking($notificationLog, 'failed', $apiResponse);

            Log::warning('Notification marked as failed', [
                'log_id' => $notificationLog->id,
                'error' => $errorMessage,
                'retry_count' => $retryCount,
                'next_retry_at' => $nextRetryAt?->format('Y-m-d H:i:s'),
            ]);
        });

        return $notificationLog->fresh();
    }

    /**
     * Update notification status from webhook
     *
     * @param  int  $logId  Notification log ID
     * @param  string  $status  New status
     * @param  array  $providerData  Provider data
     */
    public function updateStatusFromWebhook(int $logId, string $status, array $providerData = []): ?NotificationLog
    {
        $log = NotificationLog::query()->find($logId);

        if (! $log) {
            Log::warning('Notification log not found for webhook update', ['log_id' => $logId]);

            return null;
        }

        switch ($status) {
            case 'delivered':
                return $this->markAsDelivered($log, $providerData);
            case 'read':
                return $this->markAsRead($log, $providerData);
            case 'failed':
                $errorMessage = $providerData['error'] ?? 'Delivery failed (webhook)';

                return $this->markAsFailed($log, $errorMessage, $providerData);
            default:
                Log::warning('Unknown status in webhook', ['status' => $status, 'log_id' => $logId]);

                return $log;
        }
    }

    /**
     * Get notification history for an entity
     *
     * @param  array  $filters  Optional filters
     * @return Collection
     */
    public function getNotificationHistory(Model $model, array $filters = [])
    {
        $query = NotificationLog::query()->where('notifiable_type', $model::class)
            ->where('notifiable_id', $model->id)
            ->with(['notificationType', 'template', 'sender', 'deliveryTracking'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (isset($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get failed notifications ready for retry
     *
     * @param  int  $limit  Maximum notifications to retrieve
     * @return Collection
     */
    public function getFailedNotifications(int $limit = 100)
    {
        return NotificationLog::readyToRetry()
            ->with(['notificationType', 'template'])
            ->limit($limit)
            ->get();
    }

    /**
     * Get notification statistics
     *
     * @param  array  $filters  Date range and other filters
     */
    public function getStatistics(array $filters = []): array
    {
        $query = NotificationLog::query();

        // Apply date filters
        if (isset($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        // Get counts by status
        $statusCounts = (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Get counts by channel
        $channelCounts = (clone $query)
            ->select('channel', DB::raw('count(*) as count'))
            ->groupBy('channel')
            ->pluck('count', 'channel')
            ->toArray();

        // Calculate success rate
        $totalSent = $query->count();
        $successful = $query->whereIn('status', ['sent', 'delivered', 'read'])->count();
        $successRate = $totalSent > 0 ? round(($successful / $totalSent) * 100, 2) : 0;

        // Get failed notifications count
        $failedCount = NotificationLog::failed()->count();

        // Get most used templates
        $topTemplates = NotificationLog::query()->select('template_id', DB::raw('count(*) as count'))
            ->whereNotNull('template_id')
            ->groupBy('template_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->with('template.notificationType')
            ->get();

        return [
            'total_sent' => $totalSent,
            'successful' => $successful,
            'failed' => $failedCount,
            'success_rate' => $successRate,
            'status_counts' => $statusCounts,
            'channel_counts' => $channelCounts,
            'top_templates' => $topTemplates,
        ];
    }

    /**
     * Add delivery tracking record
     */
    protected function addDeliveryTracking(NotificationLog $notificationLog, string $status, array $providerStatus = []): NotificationDeliveryTracking
    {
        return NotificationDeliveryTracking::query()->create([
            'notification_log_id' => $notificationLog->id,
            'status' => $status,
            'tracked_at' => now(),
            'provider_status' => $providerStatus,
            'metadata' => [
                'previous_status' => $notificationLog->status,
                'updated_by_webhook' => request()->ip() ?? 'system',
            ],
        ]);
    }

    /**
     * Calculate next retry time based on retry count
     */
    protected function calculateNextRetryTime(int $retryCount): ?Carbon
    {
        // Exponential backoff: 1h, 4h, 24h
        return match ($retryCount) {
            1 => now()->addHour(),
            2 => now()->addHours(4),
            3 => now()->addHours(24),
            default => null, // No more retries after 3 attempts
        };
    }

    /**
     * Retry a failed notification
     *
     * @return bool Success status
     */
    public function retryNotification(NotificationLog $notificationLog): bool
    {
        if (! $notificationLog->canRetry()) {
            Log::warning('Notification cannot be retried', [
                'log_id' => $notificationLog->id,
                'retry_count' => $notificationLog->retry_count,
                'status' => $notificationLog->status,
            ]);

            return false;
        }

        try {
            // Reset to pending for retry
            $notificationLog->update([
                'status' => 'pending',
                'error_message' => null,
            ]);

            // Dispatch the notification again based on channel
            // This will be handled by the respective services (WhatsApp, Email, etc.)

            Log::info('Notification queued for retry', [
                'log_id' => $notificationLog->id,
                'retry_count' => $notificationLog->retry_count,
            ]);

            return true;

        } catch (\Exception $exception) {
            Log::error('Failed to retry notification', [
                'log_id' => $notificationLog->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Archive old notification logs
     *
     * @param  int  $daysOld  Number of days to keep
     * @return int Number of archived logs
     */
    public function archiveOldLogs(int $daysOld = 90): int
    {
        $cutoffDate = now()->subDays($daysOld);

        $count = NotificationLog::query()->where('created_at', '<', $cutoffDate)
            ->whereIn('status', ['sent', 'delivered', 'read'])
            ->delete();

        Log::info('Archived old notification logs', [
            'cutoff_date' => $cutoffDate->format('Y-m-d'),
            'count' => $count,
        ]);

        return $count;
    }
}
