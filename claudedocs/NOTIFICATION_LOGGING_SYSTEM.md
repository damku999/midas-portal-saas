# Notification Logging & Monitoring System

## Overview

Comprehensive notification tracking system for WhatsApp, Email, and SMS channels with delivery status monitoring, retry mechanism, and analytics dashboard.

## System Architecture

### Database Schema

#### `notification_logs` Table
```sql
- id (bigint, PK)
- notifiable_type (string) - Polymorphic type (Customer, CustomerInsurance, Quotation, Claim)
- notifiable_id (bigint) - Polymorphic ID
- notification_type_id (FK to notification_types, nullable)
- template_id (FK to notification_templates, nullable)
- channel (string) - whatsapp, email, sms
- recipient (string) - Phone number or email
- subject (string, nullable) - For emails
- message_content (text) - Full message content
- variables_used (json, nullable) - Resolved template variables
- status (enum) - pending, sent, failed, delivered, read
- sent_at (timestamp, nullable)
- delivered_at (timestamp, nullable)
- read_at (timestamp, nullable)
- error_message (text, nullable)
- api_response (json, nullable) - Provider API response
- sent_by (FK to users, nullable) - User who triggered send
- retry_count (tinyint) - Number of retry attempts (max 3)
- next_retry_at (timestamp, nullable) - Scheduled retry time
- created_at, updated_at, deleted_at
```

**Indexes:**
- `idx_notifiable` - (notifiable_type, notifiable_id) for polymorphic queries
- `channel`, `status`, `sent_at`, `created_at` - Individual indexes
- `idx_retry_queue` - (status, retry_count, next_retry_at) for retry processing

#### `notification_delivery_tracking` Table
```sql
- id (bigint, PK)
- notification_log_id (FK to notification_logs, cascade delete)
- status (enum) - sent, delivered, read, failed
- tracked_at (timestamp) - When status was recorded
- provider_status (json, nullable) - Raw provider status data
- metadata (json, nullable) - Additional tracking data
- created_at, updated_at
```

**Indexes:**
- `notification_log_id`, `tracked_at`

---

## Core Components

### 1. Models

#### `NotificationLog` Model
**Location:** `app/Models/NotificationLog.php`

**Key Methods:**
- `notifiable()` - Polymorphic relation to Customer, Insurance, Quotation, Claim
- `notificationType()` - BelongsTo NotificationType
- `template()` - BelongsTo NotificationTemplate
- `sender()` - BelongsTo User
- `deliveryTracking()` - HasMany tracking records
- `isSuccessful()` - Check if sent/delivered/read
- `isFailed()` - Check if failed
- `canRetry()` - Check if retry allowed (failed + retry_count < 3)

**Scopes:**
- `pending()`, `sent()`, `failed()`, `delivered()`
- `channel($channel)` - Filter by channel
- `readyToRetry()` - Failed notifications ready for retry

**Accessors:**
- `status_color` - Badge color for UI (warning, info, success, danger)
- `channel_icon` - Font Awesome icon class

#### `NotificationDeliveryTracking` Model
**Location:** `app/Models/NotificationDeliveryTracking.php`

Tracks each status change throughout notification lifecycle.

---

### 2. Services

#### `NotificationLoggerService`
**Location:** `app/Services/NotificationLoggerService.php`

**Core Methods:**

```php
// Create log entry before sending
logNotification(Model $notifiable, string $channel, string $recipient,
                string $message, array $options): NotificationLog

// Update status
markAsSent(NotificationLog $log, array $apiResponse): NotificationLog
markAsDelivered(NotificationLog $log, array $providerStatus): NotificationLog
markAsRead(NotificationLog $log, array $providerStatus): NotificationLog
markAsFailed(NotificationLog $log, string $errorMessage, array $apiResponse): NotificationLog

// Webhook integration
updateStatusFromWebhook(int $logId, string $status, array $providerData): ?NotificationLog

// History and analytics
getNotificationHistory(Model $notifiable, array $filters)
getFailedNotifications(int $limit = 100)
getStatistics(array $filters): array

// Retry and maintenance
retryNotification(NotificationLog $log): bool
archiveOldLogs(int $daysOld = 90): int
```

**Retry Logic:**
- Exponential backoff: 1h, 4h, 24h
- Maximum 3 retry attempts
- Automatic scheduling via `next_retry_at`

---

### 3. Integration Trait

#### `LogsNotificationsTrait`
**Location:** `app/Traits/LogsNotificationsTrait.php`

**Purpose:** Wrap existing notification methods with automatic logging

**Usage Example:**
```php
class CustomerService {
    use WhatsAppApiTrait, LogsNotificationsTrait;

    public function sendWelcomeMessage(Customer $customer) {
        $result = $this->logAndSendWhatsApp(
            notifiable: $customer,
            message: "Welcome {$customer->name}!",
            recipient: $customer->mobile_number,
            options: [
                'notification_type_code' => 'customer_onboarding',
                'template_id' => 5,
                'variables' => ['name' => $customer->name],
            ]
        );

        if ($result['success']) {
            // Message sent successfully
            $log = $result['log']; // NotificationLog instance
            $response = $result['response']; // API response
        } else {
            // Handle failure
            $error = $result['error'];
        }
    }
}
```

**Available Methods:**
- `logAndSendWhatsApp()` - Send WhatsApp with logging
- `logAndSendWhatsAppWithAttachment()` - Send WhatsApp with file
- `logAndSendEmail()` - Send Email with logging
- `getNotificationHistory()` - Get entity notification history

---

### 4. Controllers

#### `NotificationLogController`
**Location:** `app/Http/Controllers/NotificationLogController.php`

**Routes:**
```
GET  /admin/notification-logs              - Index page with filters
GET  /admin/notification-logs/{log}        - Detail page
GET  /admin/notification-logs/analytics    - Analytics dashboard
POST /admin/notification-logs/{log}/resend - Resend single notification
POST /admin/notification-logs/bulk-resend  - Bulk resend
POST /admin/notification-logs/cleanup      - Archive old logs
```

#### `NotificationWebhookController`
**Location:** `app/Http/Controllers/NotificationWebhookController.php`

**Webhook Endpoints:**
```
POST /webhooks/whatsapp/delivery-status - WhatsApp delivery updates
POST /webhooks/email/delivery-status    - Email delivery updates
ANY  /webhooks/test                      - Test webhook endpoint
```

**WhatsApp Webhook Payload:**
```json
{
  "log_id": 123,
  "status": "delivered|read|failed",
  "timestamp": "2025-10-08 12:00:00",
  "message_id": "wamid.xxxxx",
  "error": "error message if failed"
}
```

**Email Webhook Payload:**
```json
{
  "log_id": 123,
  "status": "delivered|opened|bounced|failed",
  "timestamp": "2025-10-08 12:00:00",
  "email_id": "xxx",
  "bounce_reason": "reason if bounced"
}
```

---

### 5. Artisan Command

#### `notifications:retry-failed`
**Location:** `app/Console/Commands/RetryFailedNotifications.php`

**Usage:**
```bash
# Retry failed notifications (respects retry schedule)
php artisan notifications:retry-failed

# Retry with custom limit
php artisan notifications:retry-failed --limit=50

# Force immediate retry (ignore next_retry_at)
php artisan notifications:retry-failed --force
```

**Schedule in `app/Console/Kernel.php`:**
```php
protected function schedule(Schedule $schedule)
{
    // Retry failed notifications daily at 9 AM
    $schedule->command('notifications:retry-failed')
             ->dailyAt('09:00')
             ->withoutOverlapping();
}
```

---

## Admin UI

### Index Page
**Route:** `/admin/notification-logs`
**View:** `resources/views/admin/notification_logs/index.blade.php`

**Features:**
- Filterable DataTable (channel, status, date range, search)
- Bulk selection and resend
- Status badges (color-coded)
- Quick view and resend actions
- Pagination

**Filters:**
- Channel: WhatsApp, Email, SMS
- Status: Pending, Sent, Delivered, Read, Failed
- Date Range: From/To
- Search: Recipient or message content

### Detail Page
**Route:** `/admin/notification-logs/{log}`
**View:** `resources/views/admin/notification_logs/show.blade.php`

**Displays:**
- Basic information (channel, status, recipient, type, template)
- Timestamps (created, sent, delivered, read)
- Full message content
- Resolved variables (debugging)
- Error details (if failed)
- API response (raw JSON)
- Delivery timeline (all status changes)
- Related entity info
- Resend button (if failed)

### Analytics Dashboard
**Route:** `/admin/notification-logs/analytics`
**View:** `resources/views/admin/notification_logs/analytics.blade.php`

**Metrics:**
- Summary Cards: Total Sent, Successful, Failed, Success Rate
- Channel Distribution (Pie Chart)
- Status Distribution (Doughnut Chart)
- Volume Over Time (Line Chart)
- Channel Performance Table (with success rates)
- Top 5 Most Used Templates
- Failed Notifications Requiring Attention

**Date Range Filter:** Default last 30 days, customizable

---

## Integration Guide

### 1. WhatsApp Integration

**Update Existing Service:**
```php
use App\Traits\WhatsAppApiTrait;
use App\Traits\LogsNotificationsTrait;

class PolicyService
{
    use WhatsAppApiTrait, LogsNotificationsTrait;

    public function sendPolicyDocument(CustomerInsurance $insurance)
    {
        $message = $this->insuranceAdded($insurance);
        $filePath = storage_path("app/policies/{$insurance->policy_document}");

        // OLD WAY (no logging):
        // $response = $this->whatsAppSendMessageWithAttachment($message, $customer->mobile, $filePath);

        // NEW WAY (with logging):
        $result = $this->logAndSendWhatsAppWithAttachment(
            notifiable: $insurance,
            message: $message,
            recipient: $insurance->customer->mobile_number,
            filePath: $filePath,
            options: [
                'notification_type_code' => 'policy_issued',
                'customer_id' => $insurance->customer_id,
            ]
        );

        if (!$result['success']) {
            Log::warning('Failed to send policy document', [
                'log_id' => $result['log']->id,
                'error' => $result['error'],
            ]);
        }
    }
}
```

### 2. Email Integration

**Placeholder for Email Service:**
```php
use App\Traits\LogsNotificationsTrait;

class EmailNotificationService
{
    use LogsNotificationsTrait;

    public function sendQuotationEmail(Quotation $quotation)
    {
        $result = $this->logAndSendEmail(
            notifiable: $quotation,
            recipient: $quotation->customer->email,
            subject: 'Your Insurance Quotation',
            message: view('emails.quotation', compact('quotation'))->render(),
            options: [
                'notification_type_code' => 'quotation_ready',
                'template_id' => 8,
            ]
        );

        return $result['success'];
    }
}
```

### 3. Listener Integration

**Update Event Listeners:**
```php
namespace App\Listeners\Quotation;

use App\Events\Quotation\QuotationGenerated;
use App\Services\QuotationService;
use App\Traits\LogsNotificationsTrait;

class SendQuotationWhatsApp implements ShouldQueue
{
    use LogsNotificationsTrait;

    public function handle(QuotationGenerated $event): void
    {
        $quotation = $event->quotation;

        // Use logged version
        $result = $this->logAndSendWhatsApp(
            notifiable: $quotation,
            message: $this->getQuotationMessage($quotation),
            recipient: $quotation->customer->mobile_number,
            options: [
                'notification_type_code' => 'quotation_ready',
            ]
        );

        if (!$result['success']) {
            // Failed job will auto-retry via queue
            throw new \Exception($result['error']);
        }
    }
}
```

---

## Webhook Configuration

### BotMasterSender WhatsApp Setup

**Webhook URL:**
```
POST https://yourdomain.com/webhooks/whatsapp/delivery-status
```

**Configure in BotMasterSender Dashboard:**
1. Login to BotMasterSender
2. Go to Settings > Webhooks
3. Add Delivery Status Webhook URL
4. Select events: `sent`, `delivered`, `read`, `failed`
5. Save configuration

**Expected Callback:**
BotMasterSender should POST to your webhook when status changes with payload:
```json
{
  "log_id": 123,
  "status": "delivered",
  "message_id": "wamid.HBgNOTE5NzI3NzkzMTIzFQIAERgSODhGMDUxNDY1RTdBMEJGOTFDAA==",
  "timestamp": "2025-10-08 14:30:00"
}
```

**Note:** You'll need to modify your WhatsApp send method to include `log_id` in the API request so BotMasterSender can send it back in webhooks.

### Email Service Webhook

Configure your email provider (SendGrid, Mailgun, etc.) to POST to:
```
https://yourdomain.com/webhooks/email/delivery-status
```

---

## Customer Notification History

### Add to Customer Profile Page

**Controller Method:**
```php
public function show(Customer $customer)
{
    $notificationHistory = app(NotificationLoggerService::class)
        ->getNotificationHistory($customer, [
            'per_page' => 10,
        ]);

    return view('customers.show', compact('customer', 'notificationHistory'));
}
```

**View (customer profile):**
```blade
<div class="card mt-4">
    <div class="card-header">
        <h5>Notification History</h5>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Channel</th>
                    <th>Type</th>
                    <th>Sent At</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notificationHistory as $log)
                <tr>
                    <td><i class="{{ $log->channel_icon }}"></i> {{ ucfirst($log->channel) }}</td>
                    <td>{{ $log->notificationType?->name }}</td>
                    <td>{{ $log->sent_at?->format('Y-m-d H:i') }}</td>
                    <td><span class="badge badge-{{ $log->status_color }}">{{ $log->status }}</span></td>
                    <td>
                        <a href="{{ route('admin.notification-logs.show', $log) }}" class="btn btn-sm btn-info">
                            View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $notificationHistory->links() }}
    </div>
</div>
```

---

## Permissions

**Add to `database/seeders/UnifiedPermissionsSeeder.php`:**
```php
'notification-logs' => [
    'view-notification-logs',
    'resend-notifications',
    'delete-notification-logs',
],
```

**Assign to Roles:**
- Super Admin: All permissions
- Admin: view-notification-logs, resend-notifications
- Agent: view-notification-logs (limited to their customers)

---

## Testing

### Manual Testing

**1. Send a notification:**
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

# Check result
dump($result);
```

**2. Test webhook:**
```bash
curl -X POST http://localhost/webhooks/test \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

**3. Test failed notification retry:**
```bash
php artisan notifications:retry-failed --limit=5
```

### Database Verification

```sql
-- Check recent logs
SELECT id, channel, recipient, status, sent_at, created_at
FROM notification_logs
ORDER BY created_at DESC
LIMIT 10;

-- Check failed notifications
SELECT id, channel, recipient, error_message, retry_count, next_retry_at
FROM notification_logs
WHERE status = 'failed'
ORDER BY created_at DESC;

-- Check delivery tracking
SELECT nl.id, nl.status as current_status,
       ndt.status as tracking_status, ndt.tracked_at
FROM notification_logs nl
JOIN notification_delivery_tracking ndt ON ndt.notification_log_id = nl.id
WHERE nl.id = 123
ORDER BY ndt.tracked_at;
```

---

## Monitoring & Alerts

### Daily Monitoring Tasks

**Check Failed Notifications:**
```sql
SELECT COUNT(*) as failed_count
FROM notification_logs
WHERE status = 'failed'
AND created_at >= CURDATE();
```

**Success Rate:**
```sql
SELECT
    channel,
    COUNT(*) as total,
    SUM(CASE WHEN status IN ('sent', 'delivered', 'read') THEN 1 ELSE 0 END) as successful,
    ROUND(SUM(CASE WHEN status IN ('sent', 'delivered', 'read') THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as success_rate
FROM notification_logs
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY channel;
```

### Alert Thresholds

- Failed notifications > 50/day: Investigate provider issues
- Success rate < 90%: Check API credentials, network
- Retry count = 3: Manual intervention required
- Delivery time > 5 minutes: Provider delay

---

## Maintenance

### Archive Old Logs

**Manual:**
```bash
# Archive logs older than 90 days
php artisan notifications:cleanup --days=90
```

**Automated (add to Kernel.php):**
```php
$schedule->command('notifications:cleanup --days=90')
         ->monthly()
         ->onFailure(function () {
             Log::error('Failed to cleanup notification logs');
         });
```

### Database Optimization

```sql
-- Monthly maintenance
OPTIMIZE TABLE notification_logs;
OPTIMIZE TABLE notification_delivery_tracking;

-- Remove soft-deleted logs permanently after 1 year
DELETE FROM notification_logs
WHERE deleted_at IS NOT NULL
AND deleted_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

---

## Troubleshooting

### Common Issues

**1. Webhooks not updating status**
- Check webhook URL is publicly accessible
- Verify log_id is included in API requests
- Check webhook controller logs
- Test webhook endpoint manually

**2. Retries not working**
- Check `next_retry_at` is set correctly
- Verify cron job is running
- Check retry_count < 3
- Review command logs

**3. High memory usage**
- Paginate large queries
- Archive old logs regularly
- Add indexes for slow queries
- Use queue for bulk operations

**4. Missing logs**
- Ensure trait is used: `use LogsNotificationsTrait;`
- Check method is called: `logAndSendWhatsApp()` not `whatsAppSendMessage()`
- Verify database connection
- Check for exceptions in logs

---

## Future Enhancements

1. **SMS Channel Integration** - Add SMS logging support
2. **Real-time Dashboard** - WebSocket for live updates
3. **Advanced Analytics** - Delivery time metrics, geographic distribution
4. **A/B Testing** - Template performance comparison
5. **Rate Limiting** - Prevent spam, throttle sends
6. **Priority Queue** - High-priority notifications first
7. **Custom Retry Schedules** - Per notification type
8. **Export Features** - CSV/PDF reports
9. **Notification Preferences** - Customer opt-in/out management
10. **Multi-language Support** - Template translations

---

## Security Considerations

1. **Webhook Authentication**
   - Add webhook secret verification
   - IP whitelist for provider IPs
   - Rate limiting on webhook endpoints

2. **Data Privacy**
   - Encrypt sensitive message content
   - GDPR compliance: Allow data deletion
   - Audit log access

3. **Access Control**
   - Role-based permissions
   - Customer-specific log filtering
   - Activity logging

---

## Summary

**Files Created:**
- `database/migrations/2025_10_08_000050_create_notification_logs_table.php`
- `database/migrations/2025_10_08_000051_create_notification_delivery_tracking_table.php`
- `app/Models/NotificationLog.php`
- `app/Models/NotificationDeliveryTracking.php`
- `app/Services/NotificationLoggerService.php`
- `app/Http/Controllers/NotificationLogController.php`
- `app/Http/Controllers/NotificationWebhookController.php`
- `app/Console/Commands/RetryFailedNotifications.php`
- `app/Traits/LogsNotificationsTrait.php`
- `resources/views/admin/notification_logs/index.blade.php`
- `resources/views/admin/notification_logs/show.blade.php`
- `resources/views/admin/notification_logs/analytics.blade.php`

**Routes Added:**
- `/admin/notification-logs` - Index
- `/admin/notification-logs/{log}` - Detail
- `/admin/notification-logs/analytics` - Analytics
- `/admin/notification-logs/{log}/resend` - Resend
- `/admin/notification-logs/bulk-resend` - Bulk resend
- `/webhooks/whatsapp/delivery-status` - WhatsApp webhook
- `/webhooks/email/delivery-status` - Email webhook

**Next Steps:**
1. Run migrations
2. Add permissions to seeder
3. Update existing services to use `LogsNotificationsTrait`
4. Configure webhooks in providers
5. Schedule retry command
6. Test thoroughly
