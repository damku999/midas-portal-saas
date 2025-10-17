# Notification System Documentation

**Laravel Insurance Admin Panel**
**Version:** 2.0
**Last Updated:** 2025-10-10
**Status:** Production Ready

---

## Table of Contents

1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Email Notifications](#email-notifications)
4. [SMS & Push Notifications](#sms--push-notifications)
5. [Notification Templates](#notification-templates)
6. [Notification Logging & Monitoring](#notification-logging--monitoring)
7. [Testing](#testing)
8. [Deployment](#deployment)
9. [Quick Reference](#quick-reference)
10. [Troubleshooting](#troubleshooting)

---

## Overview

The notification system provides multi-channel communication capabilities for the insurance admin panel. It supports WhatsApp, Email, SMS, and Push notifications with a unified template system, comprehensive logging, and automatic retry mechanisms.

### Key Features

- **Multi-Channel Support**: WhatsApp, Email, SMS, and Push notifications
- **Template System**: Database-driven templates with 70+ dynamic variables
- **Comprehensive Logging**: Track every notification with delivery status
- **Automatic Retry**: Exponential backoff for failed notifications
- **Customer Preferences**: Respect customer channel preferences and quiet hours
- **Rich Notifications**: Support for attachments (PDF), images, and action buttons
- **Analytics Dashboard**: Monitor success rates and performance
- **Version Control**: Template version history with restore capability

### Supported Channels

| Channel | Status | Provider | Features |
|---------|--------|----------|----------|
| WhatsApp | ✅ Active | BotMasterSender | Text, PDF attachments |
| Email | ✅ Active | SMTP (Laravel Mail) | HTML, PDF attachments |
| SMS | ⚠️ Optional | Twilio/Nexmo | Plain text only |
| Push | ✅ Active | Firebase (FCM) | Rich notifications, images |

---

## System Architecture

### Notification Flow

```
Event/Trigger
    ↓
Event Listener (Queue)
    ↓
Service Layer (CustomerService, PolicyService, etc.)
    ↓
ChannelManager (Multi-channel orchestration)
    ↓
├── PushNotificationService → Firebase
├── WhatsAppApiTrait → BotMasterSender
├── EmailService → Laravel Mail
└── SmsService → Twilio
    ↓
TemplateService (Render with 70+ variables)
    ↓
NotificationLoggerService (Track everything)
    ↓
API Provider
    ↓
Webhook (Delivery status updates)
```

### Database Schema

**Core Tables:**
- `notification_types` - Notification categories (19 types)
- `notification_templates` - Template content by channel
- `notification_logs` - All sent notifications
- `notification_delivery_tracking` - Delivery status timeline
- `customer_devices` - FCM push notification tokens
- `notification_template_versions` - Template version history

**Key Models:**
- `NotificationType` - Notification categories
- `NotificationTemplate` - Template definitions
- `NotificationLog` - Sent notification tracking
- `CustomerDevice` - Push notification devices
- `NotificationContext` - Variable resolution context

---

## Email Notifications

### Setup

#### 1. Configure SMTP (.env)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@parthrawal.in"
MAIL_FROM_NAME="Parth Rawal Insurance Advisor"
```

#### 2. Add Email Settings (Database)

```sql
INSERT INTO app_settings (category, key, value, display_name, is_active) VALUES
('email', 'email_from_address', 'noreply@parthrawal.in', 'Email From Address', 1),
('email', 'email_from_name', 'Parth Rawal Insurance Advisor', 'Email From Name', 1),
('email', 'email_reply_to', 'contact@parthrawal.in', 'Email Reply-To', 1),
('notifications', 'email_notifications_enabled', 'true', 'Enable Email Notifications', 1);
```

### Usage Examples

#### Send Customer Welcome Email

```php
use App\Services\EmailService;

$emailService = app(EmailService::class);
$emailService->sendFromCustomer('customer_welcome', $customer);
```

#### Send Policy Email with PDF

```php
use App\Services\CustomerInsuranceService;

$insuranceService = app(CustomerInsuranceService::class);
$insuranceService->sendPolicyDocumentEmail($insurance);
```

#### Send Quotation Email

```php
use App\Services\QuotationService;

$quotationService = app(QuotationService::class);
$quotationService->sendQuotationViaEmail($quotation);
```

### Email Features

- **Template Support**: Uses notification_templates table with channel='email'
- **Variable Resolution**: All 70+ variables supported
- **HTML Formatting**: Professional responsive email design
- **PDF Attachments**: Policy documents, quotations
- **Fallback Support**: Uses hardcoded messages if no template found
- **Queue-Based**: Async sending for performance

### Core Files

- `app/Services/EmailService.php` - Main email service
- `app/Mail/TemplatedNotification.php` - Mailable class
- `resources/views/emails/templated-notification.blade.php` - Email template
- `app/Helpers/SettingsHelper.php` - Email settings helpers

---

## SMS & Push Notifications

### SMS Notifications

#### Setup

```env
SMS_NOTIFICATIONS_ENABLED=true
SMS_PROVIDER=twilio
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890
```

#### Usage

```php
use App\Services\SmsService;
use App\Services\Notification\NotificationContext;

$smsService = app(SmsService::class);
$context = NotificationContext::fromCustomerId($customerId);

// With template
$smsService->sendTemplatedSms(
    to: '+919876543210',
    notificationTypeCode: 'policy_renewal_reminder',
    context: $context,
    customerId: $customerId
);

// Plain SMS
$smsService->sendPlainSms(
    to: '+919876543210',
    message: 'Your policy expires soon',
    customerId: $customerId
);
```

### Push Notifications

#### Setup

```env
PUSH_NOTIFICATIONS_ENABLED=true
FCM_SERVER_KEY=your_fcm_server_key
FCM_SENDER_ID=your_sender_id
```

#### Register Device

```php
use App\Services\PushNotificationService;

$pushService = app(PushNotificationService::class);

$pushService->registerDevice(
    customerId: $customer->id,
    deviceToken: 'fcm_token_from_mobile_app',
    deviceType: 'android', // or 'ios', 'web'
    deviceInfo: [
        'device_name' => 'Samsung Galaxy S21',
        'os_version' => 'Android 13',
        'app_version' => '2.1.0'
    ]
);
```

#### Send Push Notification

```php
// To all customer devices
$pushService->sendToCustomer(
    customer: $customer,
    notificationTypeCode: 'policy_renewal_reminder',
    context: $context
);

// To specific device
$pushService->sendTemplatedPush(
    deviceToken: 'fcm_device_token',
    notificationTypeCode: 'policy_renewal_reminder',
    context: $context,
    customerId: $customer->id
);
```

### Multi-Channel Manager

```php
use App\Services\Notification\ChannelManager;

$channelManager = app(ChannelManager::class);

// Send to all channels
$channelManager->sendToAllChannels(
    notificationTypeCode: 'policy_renewal_reminder',
    context: $context,
    channels: ['push', 'whatsapp', 'email'],
    customer: $customer
);

// Send with fallback (Push → WhatsApp → Email)
$channelManager->sendWithFallback(
    notificationTypeCode: 'policy_renewal_reminder',
    context: $context,
    customer: $customer
);
```

### Customer Preferences

Customers can set their notification preferences:

```php
$customer->update([
    'notification_preferences' => [
        'channels' => ['whatsapp', 'email', 'push'],
        'quiet_hours' => [
            'start' => '22:00',
            'end' => '08:00'
        ],
        'opt_out_types' => ['birthday_wish']
    ]
]);
```

---

## Notification Templates

### Template System Overview

Templates are stored in the `notification_templates` table with dynamic variable replacement. The system supports 70+ variables across customer, policy, quotation, and system data.

### Available Channels

- `whatsapp` - WhatsApp messages
- `email` - Email body content
- `sms` - SMS text (160 char limit)
- `push_title` - Push notification title
- `push` - Push notification body

### Variable Categories

#### Customer Variables (12)
```
{{customer_name}}           - Customer full name
{{customer_email}}          - Customer email address
{{customer_mobile}}         - Customer mobile number
{{customer_whatsapp}}       - WhatsApp number
{{date_of_birth}}          - Date of birth (formatted)
{{wedding_anniversary}}    - Wedding anniversary date
{{engagement_anniversary}} - Engagement anniversary
```

#### Insurance/Policy Variables (25)
```
{{policy_no}}              - Policy number
{{registration_no}}        - Vehicle registration
{{vehicle_make_model}}     - Vehicle make and model
{{start_date}}            - Policy start date
{{expired_date}}          - Policy expiry date
{{premium_amount}}        - Premium amount (₹ formatted)
{{net_premium}}           - Net premium
{{ncb_percentage}}        - NCB percentage
{{idv_amount}}            - IDV/Sum insured
{{insurance_company}}     - Insurance company name
{{policy_type}}           - Policy type name
{{days_remaining}}        - Days until expiry (computed)
```

#### Quotation Variables (8)
```
{{quotes_count}}          - Number of quotes
{{best_company_name}}     - Company with lowest premium
{{best_premium}}          - Lowest premium amount
{{comparison_list}}       - HTML table of all quotes
{{quotation_id}}          - Quotation reference ID
```

#### Claim Variables (6)
```
{{claim_number}}          - Claim reference number
{{claim_status}}          - Current claim status
{{claim_amount}}          - Claim amount
{{stage_name}}            - Current stage name
{{pending_documents_list}} - List of pending documents
```

#### Company Settings (8)
```
{{company_name}}          - Company name
{{company_phone}}         - Company phone
{{company_email}}         - Company email
{{company_address}}       - Company address
{{company_website}}       - Company website
{{whatsapp_number}}       - WhatsApp support number
{{portal_url}}            - Customer portal URL
```

#### System Variables (5)
```
{{current_date}}          - Current date (formatted)
{{current_year}}          - Current year
{{advisor_name}}          - Advisor name
```

### Creating Templates

#### Via Admin Panel

1. Navigate to `/notification-templates/create`
2. Select notification type
3. Select channel (whatsapp, email, push, etc.)
4. Write template content with variables
5. Use variable browser to insert variables
6. Preview with real customer data
7. Test send before activating

#### Via SQL

```sql
INSERT INTO notification_templates (
    notification_type_id,
    channel,
    template_name,
    template_content,
    is_active
) VALUES (
    (SELECT id FROM notification_types WHERE code = 'policy_created'),
    'email',
    'Policy Created Email',
    'Dear {{customer_name}},

Your {{policy_type}} policy has been issued successfully!

Policy Number: {{policy_no}}
Insurance Company: {{insurance_company}}
Coverage Period: {{start_date}} to {{expired_date}}
Premium Amount: {{premium_amount}}

Thank you for choosing {{company_name}}!',
    1
);
```

### Template Best Practices

1. **Use Descriptive Names**: "Policy Renewal - 30 Days Notice"
2. **Include Fallback Text**: Always have default values
3. **Test Before Activating**: Use test send feature
4. **Version Control**: System auto-saves versions on each edit
5. **Mobile-Friendly**: Keep WhatsApp/SMS messages concise
6. **Professional Tone**: Maintain brand voice

---

## Notification Logging & Monitoring

### Overview

Every notification sent through the system is automatically logged with complete tracking information, delivery status, and retry capability.

### Notification Log Features

- **Polymorphic Relations**: Link to Customer, Insurance, Quotation, or Claim
- **Status Tracking**: pending → sent → delivered → read
- **Automatic Retry**: Up to 3 retries with exponential backoff (1h, 4h, 24h)
- **Error Logging**: Capture API errors and failure reasons
- **Variable Tracking**: Store resolved variables for debugging
- **Webhook Integration**: Real-time delivery status updates

### Using Notification Logging

#### Basic Integration

Add the trait to any service:

```php
use App\Traits\LogsNotificationsTrait;

class YourService
{
    use LogsNotificationsTrait;

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
            // Success - $result['log'] contains NotificationLog instance
        } else {
            // Failed - $result['error'] contains error message
        }
    }
}
```

#### Available Methods

```php
// WhatsApp
$this->logAndSendWhatsApp($notifiable, $message, $recipient, $options);
$this->logAndSendWhatsAppWithAttachment($notifiable, $message, $recipient, $filePath, $options);

// Email
$this->logAndSendEmail($notifiable, $recipient, $subject, $message, $options);

// Get history
$this->getNotificationHistory($notifiable, $filters);
```

### Viewing Logs

#### Admin Interface

Navigate to `/admin/notification-logs` for:
- Filterable list of all notifications
- Search by recipient or content
- Filter by channel, status, date range
- Detailed view of each notification
- Resend failed notifications
- Bulk operations

#### Analytics Dashboard

Navigate to `/admin/notification-logs/analytics` for:
- Success rate metrics
- Channel distribution charts
- Status breakdown
- Volume trends over time
- Top templates used
- Failed notifications requiring attention

### Automatic Retry System

Failed notifications are automatically retried:

```bash
# Manual retry
php artisan notifications:retry-failed

# With limit
php artisan notifications:retry-failed --limit=50

# Force immediate retry (ignore schedule)
php artisan notifications:retry-failed --force
```

**Retry Schedule:**
- Attempt 1: Immediate
- Attempt 2: 1 hour later
- Attempt 3: 4 hours later
- Attempt 4: 24 hours later
- After 3 retries: Manual intervention required

### Webhooks

Configure webhook endpoints in your providers:

**WhatsApp Delivery Status:**
```
POST /webhooks/whatsapp/delivery-status

Payload:
{
  "log_id": 123,
  "status": "delivered|read|failed",
  "timestamp": "2025-10-08 12:00:00",
  "message_id": "wamid.xxxxx",
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
  "timestamp": "2025-10-08 12:00:00",
  "email_id": "xxx",
  "bounce_reason": "reason if bounced"
}
```

---

## Testing

### Running Tests

```bash
# All notification tests
php artisan test tests/Unit/Notification tests/Feature/Notification

# Specific test file
php artisan test tests/Unit/Notification/VariableResolverServiceTest.php

# With coverage
php artisan test --coverage tests/Unit/Notification

# Windows batch script
run-tests.bat
```

### Test Coverage

The system includes 210+ comprehensive tests:

**Unit Tests (145+ tests):**
- `VariableResolverServiceTest.php` - 50+ tests for all 70+ variables
- `VariableRegistryServiceTest.php` - 30+ tests for variable registry
- `NotificationContextTest.php` - 35+ tests for context building
- `TemplateServiceTest.php` - 30+ tests for template rendering

**Feature Tests (65+ tests):**
- `CustomerNotificationTest.php` - 15+ tests for customer workflows
- `PolicyNotificationTest.php` - 20+ tests for policy notifications
- `QuotationNotificationTest.php` - 15+ tests for quotation flows
- `ClaimNotificationTest.php` - 15+ tests for claim notifications

### Manual Testing

#### Test Email Delivery

```bash
php artisan test:email welcome --email=test@example.com
php artisan test:email policy --email=test@example.com --insurance-id=1
php artisan test:email quotation --email=test@example.com --quotation-id=1
```

#### Test Push Notification

```bash
php artisan tinker

$customer = App\Models\Customer::first();
$pushService = app(\App\Services\PushNotificationService::class);

// Register test device
$device = $pushService->registerDevice(
    customerId: $customer->id,
    deviceToken: 'test_fcm_token',
    deviceType: 'android'
);

// Send test push
$context = \App\Services\Notification\NotificationContext::fromCustomerId($customer->id);
$result = $pushService->sendToCustomer($customer, 'test_notification', $context);
var_dump($result);
```

---

## Deployment

### Prerequisites

- Laravel 11.x installed
- Queue worker configured
- SMTP server access
- Firebase project created (for Push)
- Database backup completed

### Step 1: Run Migrations

```bash
php artisan migrate
```

**Migrations Included:**
- `create_notification_types_table.php`
- `create_notification_templates_table.php`
- `create_notification_logs_table.php`
- `create_notification_delivery_tracking_table.php`
- `create_customer_devices_table.php`
- `create_notification_template_versions_table.php`
- `add_notification_preferences_to_customers.php`

### Step 2: Seed Data

```bash
# Seed notification types
php artisan db:seed --class=NotificationTypesSeeder

# Seed notification templates
php artisan db:seed --class=NotificationTemplatesSeeder

# Seed permissions
php artisan db:seed --class=UnifiedPermissionsSeeder
```

### Step 3: Configure Environment

Update `.env` file:

```env
# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@parthrawal.in"
MAIL_FROM_NAME="Parth Rawal Insurance Advisor"

# Push Notifications (FCM)
PUSH_NOTIFICATIONS_ENABLED=true
FCM_SERVER_KEY=your_fcm_server_key
FCM_SENDER_ID=your_sender_id

# Queue Configuration
QUEUE_CONNECTION=database
```

### Step 4: Update App Settings

```sql
-- Email Settings
INSERT INTO app_settings (category, key, value, is_active) VALUES
('email', 'email_from_address', 'noreply@parthrawal.in', 1),
('email', 'email_from_name', 'Parth Rawal Insurance Advisor', 1),
('email', 'email_reply_to', 'contact@parthrawal.in', 1),
('notifications', 'email_notifications_enabled', 'true', 1);

-- Push Settings
INSERT INTO app_settings (category, key, value, is_active) VALUES
('push', 'push_notifications_enabled', 'true', 1),
('push', 'push_fcm_server_key', 'AAAA...', 1),
('push', 'push_fcm_sender_id', '123456789', 1);
```

### Step 5: Schedule Commands

In `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Retry failed notifications daily
    $schedule->command('notifications:retry-failed')
             ->dailyAt('09:00')
             ->withoutOverlapping();

    // Send birthday wishes daily
    $schedule->command('send:birthday-wishes')
             ->dailyAt('09:00');

    // Send renewal reminders
    $schedule->command('send:renewal-reminders 30')
             ->dailyAt('10:00');
}
```

### Step 6: Start Queue Worker

```bash
# Development
php artisan queue:work --tries=3 --timeout=60

# Production (with Supervisor)
[program:laravel-worker]
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --timeout=300
directory=/path/to/project
user=www-data
autostart=true
autorestart=true
```

### Step 7: Configure Webhooks

Add webhook URLs to your notification providers:

- **WhatsApp**: `https://yourdomain.com/webhooks/whatsapp/delivery-status`
- **Email**: `https://yourdomain.com/webhooks/email/delivery-status`

### Step 8: Verify Installation

```bash
# Run tests
php artisan test tests/Unit/Notification tests/Feature/Notification

# Test email sending
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));

# Check notification types
>>> App\Models\NotificationType::count(); // Should be 19

# Check templates
>>> App\Models\NotificationTemplate::count(); // Should be 13+
```

---

## Quick Reference

### Common Operations

#### Send Notifications

```php
// WhatsApp
$result = $this->logAndSendWhatsApp($customer, $message, $recipient, [
    'notification_type_code' => 'customer_welcome'
]);

// Email
$emailService->sendFromCustomer('customer_welcome', $customer);

// Push
$pushService->sendToCustomer($customer, 'policy_renewal_reminder', $context);

// Multi-channel
$channelManager->sendWithFallback('claim_update', $context, $customer);
```

#### Query Logs

```php
// Get recent notifications
$logs = NotificationLog::where('customer_id', $customerId)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// Get failed notifications
$failed = NotificationLog::failed()
    ->where('channel', 'email')
    ->get();

// Success rate
$stats = app(\App\Services\NotificationLoggerService::class)
    ->getStatistics(['from_date' => now()->subDays(30)]);
```

#### Helper Functions

```php
// Email settings
is_email_notification_enabled()
email_from_address()
email_from_name()

// Push settings
is_push_notification_enabled()
```

### Artisan Commands

```bash
# Retry failed notifications
php artisan notifications:retry-failed

# Send birthday wishes
php artisan send:birthday-wishes

# Send renewal reminders
php artisan send:renewal-reminders 30

# Test email notification
php artisan test:email welcome --email=test@example.com

# Clean up old logs (>90 days)
php artisan notifications:cleanup --days=90
```

### Admin Routes

```
/notification-templates              - Template management
/notification-templates/create       - Create new template
/notification-templates/edit/{id}    - Edit template
/notification-logs                   - Notification log list
/notification-logs/{id}              - Log details
/notification-logs/analytics         - Analytics dashboard
```

---

## Troubleshooting

### Email Not Sending

**Check SMTP Configuration:**
```bash
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));
```

**Check Queue:**
```bash
php artisan queue:work --once
```

**Check Logs:**
```bash
tail -f storage/logs/laravel.log | grep -i email
```

**Common Issues:**
- SMTP credentials incorrect
- Email notifications disabled in settings
- Queue worker not running
- Firewall blocking SMTP port

### WhatsApp Not Sending

**Check API Credentials:**
```bash
php artisan tinker
>>> config('whatsapp.api_key')
```

**Check Logs:**
```sql
SELECT * FROM notification_logs
WHERE channel = 'whatsapp'
ORDER BY created_at DESC
LIMIT 10;
```

**Common Issues:**
- API key not configured
- WhatsApp session expired
- Invalid phone number format
- SSL certificate issues (development)

### Push Notifications Not Delivering

**Check FCM Configuration:**
```bash
php artisan tinker
>>> config('notifications.fcm_server_key')
```

**Check Device Tokens:**
```bash
php artisan tinker
>>> App\Models\CustomerDevice::where('customer_id', 1)->active()->get();
```

**Common Issues:**
- FCM server key incorrect
- Device token expired
- App not configured with Firebase
- No active devices registered

### Template Variables Not Resolving

**Test Variable Resolution:**
```bash
php artisan tinker
>>> $context = App\Services\Notification\NotificationContext::fromCustomerId(1);
>>> $resolver = app(\App\Services\Notification\VariableResolverService::class);
>>> $resolver->resolveVariable('customer_name', $context);
```

**Common Issues:**
- Variable name misspelled
- Missing data in context
- Template syntax error
- Relationship not loaded

### Notifications Not Logged

**Check Integration:**
- Verify `LogsNotificationsTrait` is added to service
- Confirm using `logAndSendWhatsApp()` not direct API call
- Check migrations ran successfully

**Check Database:**
```sql
SELECT COUNT(*) FROM notification_logs;
```

---

## Additional Resources

### Related Documentation

- **Variable System**: `NOTIFICATION_VARIABLE_SYSTEM_ARCHITECTURE.md`
- **Template Integration**: `NOTIFICATION_TEMPLATES_INTEGRATION.md`
- **Email Integration**: `EMAIL_INTEGRATION_COMPLETE_REPORT.md`
- **SMS/Push Implementation**: `SMS_PUSH_NOTIFICATION_IMPLEMENTATION.md`
- **Testing Guide**: `NOTIFICATION_TESTING_SUITE_SUMMARY.md`
- **Logging System**: `NOTIFICATION_LOGGING_SYSTEM.md`

### Laravel Documentation

- [Laravel Mail](https://laravel.com/docs/11.x/mail)
- [Laravel Queues](https://laravel.com/docs/11.x/queues)
- [Laravel Notifications](https://laravel.com/docs/11.x/notifications)

### API Documentation

- [Firebase Cloud Messaging](https://firebase.google.com/docs/cloud-messaging)
- [Twilio API](https://www.twilio.com/docs/sms)

---

## Support

For issues or questions:

1. Check logs: `storage/logs/laravel.log`
2. Review `notification_logs` table
3. Verify API credentials in app settings
4. Test with provider console (Firebase, Twilio)
5. Check customer notification preferences

---

**Version:** 2.0
**Last Updated:** 2025-10-10
**Maintained By:** Development Team
**Status:** Production Ready
