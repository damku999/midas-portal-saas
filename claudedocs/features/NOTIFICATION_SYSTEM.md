# Notification System

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Overview

Multi-channel notification system supporting Email, WhatsApp, SMS, and Push notifications with template management, delivery tracking, retry logic, and comprehensive logging.

### Key Features

- **Multi-Channel Support**: Email, WhatsApp, SMS, Push notifications
- **Template Management**: Reusable templates with variable substitution
- **Delivery Tracking**: Real-time status tracking (pending → sent → delivered → read)
- **Retry Logic**: Exponential backoff (1h, 4h, 24h)
- **Notification Types**: Categorized notification types (policy_expiry, claim_status, etc.)
- **Logging & Analytics**: Complete audit trail with statistics
- **Webhook Integration**: Real-time status updates from providers

## System Architecture

```
┌──────────────────────────────────────────────────────┐
│           Notification System                         │
├──────────────────────────────────────────────────────┤
│                                                        │
│  NotificationType → NotificationTemplate → Render    │
│         ↓                                             │
│  NotificationLoggerService                            │
│         ↓                                             │
│  Channel Services (Email/WhatsApp/SMS/Push)          │
│         ↓                                             │
│  NotificationLog + DeliveryTracking                  │
│                                                        │
└──────────────────────────────────────────────────────┘
```

## Core Models

### NotificationType

**Attributes**:
- `name` - Display name
- `code` - Unique identifier (e.g., 'policy_expiry_reminder')
- `category` - Group (insurance, claims, authentication, etc.)
- `channels` - Array of supported channels
- `is_active` - Enable/disable

### NotificationTemplate

**Attributes**:
- `notification_type_id` - Foreign key
- `channel` - email/whatsapp/sms/push
- `subject` - Email subject (nullable for non-email)
- `template_content` - Message template with `{variables}`
- `available_variables` - JSON array of variable names
- `sample_output` - Example rendered message
- `is_active` - Enable/disable

**Methods**:
```php
$template->render(['customer_name' => 'John', 'policy_no' => 'POL123']);
```

### NotificationLog

**Attributes**:
- `notifiable_type/id` - Polymorphic relation
- `notification_type_id` - Notification type
- `template_id` - Template used
- `channel` - Delivery channel
- `recipient` - Email/phone/device token
- `subject` - Email subject
- `message_content` - Rendered message
- `variables_used` - JSON data used
- `status` - pending/sent/delivered/read/failed
- `sent_at/delivered_at/read_at` - Timestamps
- `error_message` - Failure reason
- `api_response` - Provider response
- `sent_by` - User ID
- `retry_count` - Attempts (max 3)
- `next_retry_at` - Next retry time

**Scopes**:
```php
NotificationLog::pending()->channel('whatsapp')->get();
NotificationLog::failed()->readyToRetry()->get();
NotificationLog::delivered()->where('created_at', '>=', now()->subDay())->get();
```

### NotificationDeliveryTracking

Tracks status changes for audit trail:
- `notification_log_id` - Foreign key
- `status` - Status at tracking time
- `tracked_at` - Timestamp
- `provider_status` - Provider-specific data
- `metadata` - Additional context

## NotificationLoggerService

**File**: `app/Services/NotificationLoggerService.php`

### Core Methods

**Log Notification**:
```php
$log = $notificationLoggerService->logNotification(
    $customer,                    // Model (Customer, Policy, Claim, etc.)
    'whatsapp',                   // Channel
    '+919876543210',              // Recipient
    'Your policy expires in 30 days', // Message
    [
        'notification_type_code' => 'policy_expiry',
        'template_id' => 5,
        'subject' => 'Policy Expiry Alert',
        'variables' => ['days_remaining' => 30, 'policy_no' => 'POL123']
    ]
);
```

**Update Status**:
```php
$notificationLoggerService->markAsSent($log, ['message_id' => 'wamid.abc123']);
$notificationLoggerService->markAsDelivered($log, ['delivered_at' => '2025-11-06T10:30:00Z']);
$notificationLoggerService->markAsRead($log);
$notificationLoggerService->markAsFailed($log, 'Invalid phone number', ['error_code' => 1006]);
```

**Webhook Updates**:
```php
$notificationLoggerService->updateStatusFromWebhook($logId, 'delivered', $webhookData);
```

**Query & Analytics**:
```php
// Get history for entity
$history = $notificationLoggerService->getNotificationHistory($customer, [
    'channel' => 'email',
    'status' => 'delivered',
    'from_date' => now()->subDays(30)
]);

// Get statistics
$stats = $notificationLoggerService->getStatistics([
    'from_date' => now()->subMonth(),
    'to_date' => now()
]);
// Returns: total_sent, successful, failed, success_rate, status_counts, channel_counts, top_templates

// Get failed notifications for retry
$failed = $notificationLoggerService->getFailedNotifications(100);

// Retry notification
$notificationLoggerService->retryNotification($log);

// Archive old logs (90+ days)
$notificationLoggerService->archiveOldLogs(90);
```

## Retry Logic

**Exponential Backoff**:
- **Retry 1**: 1 hour after failure
- **Retry 2**: 4 hours after 2nd failure
- **Retry 3**: 24 hours after 3rd failure
- **After 3 attempts**: No more retries

**Implementation**:
```php
protected function calculateNextRetryTime(int $retryCount): ?Carbon
{
    return match ($retryCount) {
        1 => now()->addHour(),
        2 => now()->addHours(4),
        3 => now()->addHours(24),
        default => null, // No more retries
    };
}
```

## Notification Channels

### Email
- **Provider**: SMTP/SendGrid/Mailgun
- **Config**: `config/mail.php`
- **Mailable Classes**: `app/Mail/*.php`
- **Features**: HTML templates, attachments, CC/BCC

### WhatsApp
- **Provider**: WhatsApp Business API / Twilio
- **Service**: `PushNotificationService` or dedicated WhatsApp service
- **Features**: Text messages, media, templates

### SMS
- **Provider**: Twilio/MSG91
- **Features**: Text-only, delivery reports

### Push Notifications
- **Provider**: FCM (Firebase Cloud Messaging) / APNS
- **Service**: `PushNotificationService`
- **Features**: Mobile app notifications, badge counts
- **Device Management**: `CustomerDevice` model

## Database Schema

### notification_types Table
```sql
CREATE TABLE notification_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) UNIQUE NOT NULL,
    category VARCHAR(255) NOT NULL,
    channels JSON NOT NULL, -- ['email', 'whatsapp', 'sms']
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### notification_templates Table
```sql
CREATE TABLE notification_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    notification_type_id BIGINT UNSIGNED NOT NULL,
    channel VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NULL,
    template_content TEXT NOT NULL,
    available_variables JSON NULL,
    sample_output TEXT NULL,
    is_active BOOLEAN DEFAULT 1,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (notification_type_id) REFERENCES notification_types(id)
);
```

### notification_logs Table
```sql
CREATE TABLE notification_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT UNSIGNED NOT NULL,
    notification_type_id BIGINT UNSIGNED NULL,
    template_id BIGINT UNSIGNED NULL,
    channel VARCHAR(50) NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NULL,
    message_content TEXT NOT NULL,
    variables_used JSON NULL,
    status VARCHAR(50) DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    error_message TEXT NULL,
    api_response JSON NULL,
    sent_by BIGINT UNSIGNED NULL,
    retry_count INT DEFAULT 0,
    next_retry_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_notifiable (notifiable_type, notifiable_id),
    INDEX idx_status (status),
    INDEX idx_channel (channel),
    INDEX idx_retry (status, next_retry_at)
);
```

### notification_delivery_tracking Table
```sql
CREATE TABLE notification_delivery_tracking (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    notification_log_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(50) NOT NULL,
    tracked_at TIMESTAMP NOT NULL,
    provider_status JSON NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (notification_log_id) REFERENCES notification_logs(id) ON DELETE CASCADE
);
```

## Usage Examples

### Example 1: Send Policy Expiry Notification

```php
use App\Services\NotificationLoggerService;
use App\Models\NotificationTemplate;

// Get template
$template = NotificationTemplate::where('notification_type_id', $policyExpiryType->id)
    ->where('channel', 'whatsapp')
    ->where('is_active', true)
    ->first();

// Render message
$message = $template->render([
    'customer_name' => $customer->name,
    'policy_no' => $policy->policy_no,
    'expiry_date' => $policy->expiry_date->format('d-m-Y'),
    'days_remaining' => $policy->expiry_date->diffInDays(now())
]);

// Log notification
$logger = app(NotificationLoggerService::class);
$log = $logger->logNotification(
    $policy,
    'whatsapp',
    $customer->mobile_number,
    $message,
    [
        'notification_type_code' => 'policy_expiry',
        'template_id' => $template->id,
        'variables' => [
            'customer_name' => $customer->name,
            'policy_no' => $policy->policy_no,
            'expiry_date' => $policy->expiry_date->format('d-m-Y'),
            'days_remaining' => $policy->expiry_date->diffInDays(now())
        ]
    ]
);

// Send via WhatsApp service
try {
    $whatsappService->sendMessage($customer->mobile_number, $message);
    $logger->markAsSent($log, ['sent_via' => 'whatsapp_api']);
} catch (\Exception $e) {
    $logger->markAsFailed($log, $e->getMessage());
}
```

### Example 2: Handle Webhook Status Update

```php
// Webhook controller
public function handleWhatsAppWebhook(Request $request)
{
    $data = $request->all();

    // Extract notification log ID from message metadata
    $logId = $data['metadata']['log_id'] ?? null;

    if (!$logId) {
        return response()->json(['error' => 'Log ID not found'], 400);
    }

    $status = match ($data['status']) {
        'delivered' => 'delivered',
        'read' => 'read',
        'failed' => 'failed',
        default => null
    };

    if ($status) {
        app(NotificationLoggerService::class)->updateStatusFromWebhook(
            $logId,
            $status,
            $data
        );
    }

    return response()->json(['success' => true]);
}
```

### Example 3: Retry Failed Notifications (Scheduled Command)

```php
// app/Console/Commands/RetryFailedNotifications.php

public function handle()
{
    $logger = app(NotificationLoggerService::class);
    $failed = $logger->getFailedNotifications(100);

    $this->info("Found {$failed->count()} notifications ready for retry");

    foreach ($failed as $log) {
        try {
            // Re-send based on channel
            $sent = match ($log->channel) {
                'email' => $this->resendEmail($log),
                'whatsapp' => $this->resendWhatsApp($log),
                'sms' => $this->resendSMS($log),
                'push' => $this->resendPush($log),
                default => false
            };

            if ($sent) {
                $logger->markAsSent($log);
                $this->info("Retry successful: Log #{$log->id}");
            } else {
                $logger->markAsFailed($log, 'Retry failed - service unavailable');
            }
        } catch (\Exception $e) {
            $logger->markAsFailed($log, "Retry error: {$e->getMessage()}");
            $this->error("Retry failed for Log #{$log->id}: {$e->getMessage()}");
        }
    }
}
```

### Example 4: Notification Statistics Dashboard

```php
public function dashboardStats()
{
    $logger = app(NotificationLoggerService::class);

    $stats = $logger->getStatistics([
        'from_date' => now()->subDays(30),
        'to_date' => now()
    ]);

    return view('admin.notifications.dashboard', [
        'total_sent' => $stats['total_sent'],
        'success_rate' => $stats['success_rate'],
        'failed_count' => $stats['failed'],
        'status_breakdown' => $stats['status_counts'],
        'channel_breakdown' => $stats['channel_counts'],
        'top_templates' => $stats['top_templates']
    ]);
}
```

## Best Practices

1. **Always Log Before Sending**: Create log entry before attempting delivery
2. **Use Templates**: Centralize message content in templates
3. **Handle Failures Gracefully**: Don't block user actions on notification failures
4. **Monitor Delivery Rates**: Set up alerts for success rate drops
5. **Clean Old Logs**: Archive logs > 90 days to maintain performance
6. **Test Templates**: Use `sample_output` to preview before activating
7. **Respect Rate Limits**: Implement queuing for bulk notifications

## Related Documentation

- **[DEVICE_TRACKING.md](DEVICE_TRACKING.md)** - Push notification device management
- **[AUDIT_LOGGING.md](AUDIT_LOGGING.md)** - Notification logging integration
- **[SERVICE_LAYER.md](../core/SERVICE_LAYER.md)** - NotificationLoggerService details

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
**System Version**: Multi-Tenancy SaaS v2.0
