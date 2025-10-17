# Notification Logging System - Quick Reference Card

## Installation

```bash
# 1. Run migrations
php artisan migrate

# 2. Add permissions
php artisan db:seed --class=UnifiedPermissionsSeeder

# 3. Schedule retry command (app/Console/Kernel.php)
$schedule->command('notifications:retry-failed')->dailyAt('09:00');
```

---

## Basic Usage

### Send WhatsApp with Logging

```php
use App\Traits\WhatsAppApiTrait;
use App\Traits\LogsNotificationsTrait;

class YourService
{
    use WhatsAppApiTrait, LogsNotificationsTrait;

    public function sendNotification($entity)
    {
        $result = $this->logAndSendWhatsApp(
            notifiable: $entity,
            message: "Your message here",
            recipient: $entity->customer->mobile_number,
            options: [
                'notification_type_code' => 'your_type_code',
                'template_id' => 5, // optional
                'variables' => ['name' => 'value'], // optional
            ]
        );

        if ($result['success']) {
            // Success - $result['log'] has NotificationLog
        } else {
            // Failed - $result['error'] has error message
        }
    }
}
```

### Send WhatsApp with Attachment

```php
$result = $this->logAndSendWhatsAppWithAttachment(
    notifiable: $insurance,
    message: "Policy document attached",
    recipient: $customer->mobile_number,
    filePath: storage_path('app/policies/document.pdf'),
    options: ['notification_type_code' => 'policy_issued']
);
```

### Send Email

```php
$result = $this->logAndSendEmail(
    notifiable: $quotation,
    recipient: $customer->email,
    subject: "Your Quotation",
    message: "Email body here",
    options: ['notification_type_code' => 'quotation_ready']
);
```

---

## Artisan Commands

```bash
# Retry failed notifications
php artisan notifications:retry-failed

# Retry with limit
php artisan notifications:retry-failed --limit=50

# Force immediate retry (ignore schedule)
php artisan notifications:retry-failed --force
```

---

## Admin Routes

```
GET  /admin/notification-logs              - List all logs
GET  /admin/notification-logs/{id}         - View details
GET  /admin/notification-logs/analytics    - Analytics dashboard
POST /admin/notification-logs/{id}/resend  - Resend notification
POST /admin/notification-logs/bulk-resend  - Bulk resend
```

---

## Webhook Endpoints

**WhatsApp Delivery Status:**
```
POST /webhooks/whatsapp/delivery-status

Payload:
{
  "log_id": 123,
  "status": "delivered|read|failed",
  "message_id": "wamid.xxx",
  "error": "error message if failed"
}
```

**Email Delivery Status:**
```
POST /webhooks/email/delivery-status

Payload:
{
  "log_id": 123,
  "status": "delivered|opened|bounced|failed",
  "email_id": "xxx",
  "bounce_reason": "reason if bounced"
}
```

---

## Common Queries

### Get notification history for entity

```php
$loggerService = app(\App\Services\NotificationLoggerService::class);
$history = $loggerService->getNotificationHistory($customer, [
    'channel' => 'whatsapp', // optional
    'status' => 'failed',    // optional
    'from_date' => '2025-01-01',
    'to_date' => '2025-12-31',
    'per_page' => 15,
]);
```

### Get failed notifications

```php
$failed = $loggerService->getFailedNotifications(100);
```

### Get statistics

```php
$stats = $loggerService->getStatistics([
    'from_date' => now()->subDays(30),
    'to_date' => now(),
]);
// Returns: total_sent, successful, failed, success_rate, status_counts, channel_counts, top_templates
```

---

## SQL Queries

### Recent notifications

```sql
SELECT id, channel, recipient, status, sent_at
FROM notification_logs
ORDER BY created_at DESC
LIMIT 20;
```

### Success rate by channel

```sql
SELECT
    channel,
    COUNT(*) as total,
    ROUND(SUM(CASE WHEN status IN ('sent','delivered','read') THEN 100 ELSE 0 END) / COUNT(*), 2) as success_rate
FROM notification_logs
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY channel;
```

### Failed notifications ready to retry

```sql
SELECT id, channel, recipient, error_message, retry_count
FROM notification_logs
WHERE status = 'failed'
  AND retry_count < 3
  AND (next_retry_at IS NULL OR next_retry_at <= NOW())
LIMIT 10;
```

---

## Status Flow

```
pending → sent → delivered → read
              ↓
            failed (retry up to 3 times)
```

**Retry Schedule:**
- Attempt 1: Immediate
- Attempt 2: 1 hour later
- Attempt 3: 4 hours later
- Attempt 4: 24 hours later
- After 3 retries: Manual intervention required

---

## Model Relationships

```php
// Get notification logs for a customer
$customer->notificationLogs; // morphMany

// Access related data
$log->notifiable;        // Customer, Insurance, Quotation, Claim
$log->notificationType;  // NotificationType
$log->template;          // NotificationTemplate
$log->sender;            // User who sent
$log->deliveryTracking;  // Status history
```

---

## Scopes

```php
// Failed notifications
NotificationLog::failed()->get();

// Sent notifications
NotificationLog::sent()->get();

// Pending notifications
NotificationLog::pending()->get();

// Delivered notifications
NotificationLog::delivered()->get();

// By channel
NotificationLog::channel('whatsapp')->get();

// Ready to retry
NotificationLog::readyToRetry()->get();
```

---

## Helper Methods

```php
$log = NotificationLog::find($id);

// Check status
$log->isSuccessful();  // true if sent/delivered/read
$log->isFailed();      // true if failed
$log->canRetry();      // true if failed and retry_count < 3

// UI helpers
$log->status_color;    // badge color: success, danger, warning, info
$log->channel_icon;    // fab fa-whatsapp, fas fa-envelope, etc.
```

---

## Testing

### Manual Test

```bash
php artisan tinker

$customer = Customer::first();
$service = new CustomerService();
$result = $service->logAndSendWhatsApp(
    $customer,
    "Test message",
    $customer->mobile_number,
    ['notification_type_code' => 'test']
);

dump($result);
```

### Test Webhook

```bash
curl -X POST http://localhost/webhooks/test \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

---

## Permissions

Add to your role permissions:

```php
'notification-logs' => [
    'view-notification-logs',
    'resend-notifications',
    'delete-notification-logs',
],
```

---

## Troubleshooting

### Notifications not logging
✓ Check trait is added: `use LogsNotificationsTrait;`
✓ Verify migration ran: `php artisan migrate:status`
✓ Check database connection

### Webhooks not updating status
✓ Verify webhook URL is publicly accessible
✓ Check webhook logs in Laravel log file
✓ Test webhook endpoint: `POST /webhooks/test`

### Retries not working
✓ Check cron is running: `php artisan schedule:run`
✓ Verify `next_retry_at` is set
✓ Check `retry_count < 3`

### High failure rate
✓ Check API credentials in app_settings
✓ Verify WhatsApp session is active
✓ Review error messages in failed logs
✓ Check network connectivity

---

## Maintenance

### Daily
```sql
-- Check failed count
SELECT COUNT(*) FROM notification_logs WHERE status = 'failed' AND DATE(created_at) = CURDATE();
```

### Weekly
- Review analytics dashboard
- Check retry queue

### Monthly
```bash
# Archive old logs
php artisan notifications:cleanup --days=90
```

```sql
-- Optimize tables
OPTIMIZE TABLE notification_logs;
OPTIMIZE TABLE notification_delivery_tracking;
```

---

## Key Files

```
Migrations:
  database/migrations/2025_10_08_000050_create_notification_logs_table.php
  database/migrations/2025_10_08_000051_create_notification_delivery_tracking_table.php

Models:
  app/Models/NotificationLog.php
  app/Models/NotificationDeliveryTracking.php

Service:
  app/Services/NotificationLoggerService.php

Controllers:
  app/Http/Controllers/NotificationLogController.php
  app/Http/Controllers/NotificationWebhookController.php

Trait:
  app/Traits/LogsNotificationsTrait.php

Command:
  app/Console/Commands/RetryFailedNotifications.php

Views:
  resources/views/admin/notification_logs/index.blade.php
  resources/views/admin/notification_logs/show.blade.php
  resources/views/admin/notification_logs/analytics.blade.php
```

---

## Documentation

Full documentation available in:
- `claudedocs/NOTIFICATION_LOGGING_SYSTEM.md` - Complete system docs
- `claudedocs/NOTIFICATION_LOGGING_INTEGRATION_EXAMPLES.md` - Code examples
- `claudedocs/NOTIFICATION_LOGGING_IMPLEMENTATION_REPORT.md` - Implementation report
- `database/sql/notification_logging_setup.sql` - SQL queries

---

**Quick Start:**
1. Run migrations
2. Add trait to service
3. Replace `whatsAppSendMessage()` with `logAndSendWhatsApp()`
4. Check admin panel at `/admin/notification-logs`
5. Done!
