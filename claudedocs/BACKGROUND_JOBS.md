# Background Jobs & Scheduled Tasks Documentation

**Project**: Admin Panel - Insurance Management System
**Version**: 1.0
**Last Updated**: 2025-10-06

---

## Table of Contents

1. [Overview](#overview)
2. [Scheduled Commands (Cron Jobs)](#scheduled-commands-cron-jobs)
3. [Queue System](#queue-system)
4. [Event-Driven Listeners](#event-driven-listeners)
5. [Notification System Architecture](#notification-system-architecture)
6. [WhatsApp Integration](#whatsapp-integration)
7. [Email Notification System](#email-notification-system)
8. [Setup & Configuration](#setup--configuration)
9. [Monitoring & Logging](#monitoring--logging)
10. [Troubleshooting](#troubleshooting)

---

## Overview

The application uses Laravel's task scheduling and event-driven architecture to automate background processes. The system includes:

- **2 Scheduled Commands**: Renewal reminders and birthday wishes
- **1 Setup Command**: Security setup
- **8 Queued Event Listeners**: For async processing
- **WhatsApp Integration**: Via BotMasterSender API
- **Email System**: Via SMTP (Mailtrap configured)
- **App Settings Integration**: Dynamic configuration from database

**Current Queue Driver**: `sync` (synchronous - no queue worker required)
**Note**: Change to `database` or `redis` for production async processing

---

## Scheduled Commands (Cron Jobs)

### Command Schedule Configuration

**File**: `app/Console/Kernel.php`

Currently, NO commands are scheduled in the `schedule()` method. The file contains only commented example code.

```php
protected function schedule(Schedule $schedule)
{
    // $schedule->command('inspire')->hourly();
}
```

**Action Required**: Add the following to enable automated scheduling:

```php
protected function schedule(Schedule $schedule)
{
    // Send renewal reminders daily at 9:00 AM
    $schedule->command('send:renewal-reminders')
             ->dailyAt('09:00')
             ->withoutOverlapping()
             ->onOneServer();

    // Send birthday wishes daily at 8:00 AM
    $schedule->command('send:birthday-wishes')
             ->dailyAt('08:00')
             ->withoutOverlapping()
             ->onOneServer();
}
```

### 1. Send Renewal Reminders

**Command**: `php artisan send:renewal-reminders`
**File**: `app/Console/Commands/SendRenewalReminders.php`
**Signature**: `send:renewal-reminders`

#### Purpose
Automatically sends WhatsApp reminders to customers whose insurance policies are expiring soon based on configurable reminder days.

#### Schedule Recommendation
- **Frequency**: Daily at 9:00 AM
- **Execution Time**: ~5-30 seconds (depending on volume)
- **Peak Load**: First day of month (30-day reminders)

#### Data Processing

**Query Logic**:
```php
CustomerInsurance::where(function ($query) use ($currentDate, $reminderDays) {
    foreach ($reminderDays as $days) {
        $targetDate = $currentDate->copy()->addDays($days)->startOfDay();
        $query->orWhereDate('expired_date', $targetDate);
    }
})
->where('is_renewed', 0)
->where('status', 1)
->get();
```

**Filters**:
- Expired date matches configured reminder days (default: 30, 15, 7, 1 days before expiry)
- Policy not already renewed (`is_renewed = 0`)
- Active policies only (`status = 1`)

#### Notifications Sent

**Via**: WhatsApp (using `WhatsAppApiTrait`)

**Message Templates**:

1. **Vehicle Insurance**:
```
Dear *{Customer Name}*

Your *{Policy Type}* Under Policy No *{Policy Number}* of *{Company}*
for Vehicle Number *{Registration No}* is due for renewal on *{Expiry Date}*.

To ensure continuous coverage, please renew by the due date.
For assistance, contact us at +919727793123.

Best regards,
Parth Rawal
https://parthrawal.in
Your Trusted Insurance Advisor
"Think of Insurance, Think of Us."
```

2. **Non-Vehicle Insurance**:
```
Dear *{Customer Name}*

Your *{Policy Type}* Under Policy No *{Policy Number}* of *{Company}*
is due for renewal on *{Expiry Date}*.

To ensure continuous coverage, please renew by the due date.
For assistance, contact us at +919727793123.

Best regards,
Parth Rawal
https://parthrawal.in
Your Trusted Insurance Advisor
"Think of Insurance, Think of Us."
```

#### App Settings Dependencies

**Setting**: `notifications.renewal_reminder_days`
**Default**: `30,15,7,1` (days before expiry)
**Helper Function**: `get_renewal_reminder_days()`
**Location**: `app/Helpers/SettingsHelper.php`

**Configuration Path**: DynamicConfigServiceProvider → Notifications category

#### Batch Processing
- Processes insurances in chunks of 100
- Prevents memory issues with large datasets

#### Error Handling

```php
try {
    $this->whatsAppSendMessage($messageText, $receiverId);
    $sentCount++;
} catch (\Exception $e) {
    $this->error("Failed to send reminder for insurance #{$insurance->id}: " . $e->getMessage());
    $skippedCount++;
}
```

- Individual failures don't stop entire process
- Errors logged with insurance ID
- Summary report at end: Total, Sent, Skipped

#### Output Example
```
Checking for renewals expiring in: 30, 15, 7, 1 days
Renewal reminders sent successfully!
Total found: 45, Sent: 42, Skipped: 3
```

---

### 2. Send Birthday Wishes

**Command**: `php artisan send:birthday-wishes`
**File**: `app/Console/Commands/SendBirthdayWishes.php`
**Signature**: `send:birthday-wishes`

#### Purpose
Sends automated birthday wishes via WhatsApp to customers whose birthday is today.

#### Schedule Recommendation
- **Frequency**: Daily at 8:00 AM
- **Execution Time**: ~2-10 seconds
- **Peak Load**: Any day (typically 0-5 birthdays per day)

#### Data Processing

**Query Logic**:
```php
Customer::whereMonth('date_of_birth', $today->month)
    ->whereDay('date_of_birth', $today->day)
    ->where('status', 1)
    ->whereNotNull('mobile_number')
    ->whereNotNull('date_of_birth')
    ->get();
```

**Filters**:
- Birthday matches today's month and day (year-independent)
- Active customers only (`status = 1`)
- Has mobile number
- Has date of birth on record

#### Feature Toggle

**Check**: Feature can be disabled via App Settings

```php
if (!is_birthday_wishes_enabled()) {
    $this->info('Birthday wishes feature is disabled in settings.');
    return;
}
```

**Setting**: `notifications.birthday_wishes_enabled`
**Default**: `true`
**Helper Function**: `is_birthday_wishes_enabled()`

#### Notification Content

**Via**: WhatsApp (using `WhatsAppApiTrait`)

**Message Template**:
```
🎉 *Happy Birthday, {Customer Name}!* 🎂

Wishing you a wonderful day filled with joy, happiness, and blessings.
May this year bring you good health, prosperity, and all the success you deserve.

Thank you for trusting us with your insurance needs.
We're honored to be part of your journey!

Warm wishes,
Parth Rawal
https://parthrawal.in
Your Trusted Insurance Advisor
"Think of Insurance, Think of Us."
```

#### Error Handling

```php
try {
    $message = $this->getBirthdayMessage($customer);
    $this->whatsAppSendMessage($message, $customer->mobile_number);
    $this->info("✓ Sent birthday wish to {$customer->name} ({$customer->mobile_number})");
    $sentCount++;
} catch (\Exception $e) {
    $this->error("✗ Failed for {$customer->name}: " . $e->getMessage());
    $skippedCount++;
}
```

- Individual failures don't stop process
- Real-time console feedback for each customer
- Summary report at end

#### Output Example
```
Found 3 birthday(s) today!
✓ Sent birthday wish to John Doe (919727793123)
✓ Sent birthday wish to Jane Smith (919876543210)
✗ Failed for Bob Wilson: WhatsApp session is offline
🎉 Birthday wishes completed!
Total: 3, Sent: 2, Skipped: 1
```

---

### 3. Security Setup Command

**Command**: `php artisan security:setup`
**File**: `app/Console/Commands/SecuritySetupCommand.php`
**Signature**: `security:setup {--force : Force setup even if already configured}`

#### Purpose
One-time setup command for comprehensive security features. NOT a scheduled task.

#### What It Does

1. **Prerequisites Check**
   - PHP version >= 8.1
   - Laravel framework loaded
   - Database connection active
   - Storage directory writable
   - Required extensions (openssl, mbstring)

2. **Database Setup**
   - Creates `security_events` table
   - Validates table structure

3. **Logging Configuration**
   - Creates `storage/logs/security.log`
   - Sets file permissions (0644)
   - Validates logging channel configuration

4. **Security Keys**
   - Verifies APP_KEY exists
   - Generates if missing

5. **File Permissions**
   - Sets storage directory permissions (0755)
   - Secures .env file (0600)

6. **Configuration Validation**
   - Checks APP_DEBUG in production
   - Validates SESSION_SECURE
   - Verifies security config loaded

7. **Security Tests**
   - Runs `php artisan security:test`

#### Usage

**Initial Setup**:
```bash
php artisan security:setup
```

**Force Re-setup**:
```bash
php artisan security:setup --force
```

#### Not Scheduled
This is a manual/deployment command, NOT scheduled via cron.

---

## Queue System

### Current Configuration

**Driver**: `sync` (Synchronous)
**File**: `config/queue.php`
**Queue Table**: `jobs` (for database driver when switched)

```php
'default' => env('QUEUE_CONNECTION', 'sync'),
```

### Queue Behavior

**With `sync` driver**:
- All queued jobs execute immediately in same process
- No queue worker required
- Blocks HTTP request until job completes
- Suitable for development/low-traffic

**When switching to `database` or `redis`**:
- Jobs queued for background processing
- Requires queue worker: `php artisan queue:work`
- Non-blocking HTTP requests
- Recommended for production

### Queued Listeners

All listeners implementing `ShouldQueue` interface:

1. `SendPolicyReminderNotification` (Legacy)
2. `SendWelcomeEmail` (Legacy)
3. `SendQuotationWhatsApp`
4. `GenerateQuotationPDF`
5. `SendPolicyRenewalReminder`
6. `Customer\SendWelcomeEmail`
7. `Customer\NotifyAdminOfRegistration`
8. `Customer\CreateCustomerAuditLog`

**Current Behavior**: Execute synchronously (queue driver is `sync`)

### Queue Worker Setup (For Production)

**1. Change Queue Driver**:
```env
QUEUE_CONNECTION=database
```

**2. Run Migrations**:
```bash
php artisan queue:table
php artisan migrate
```

**3. Start Queue Worker**:
```bash
php artisan queue:work --tries=3 --timeout=90
```

**4. Supervisor Configuration** (Recommended):
```ini
[program:insurance-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/queue-worker.log
stopwaitsecs=3600
```

---

## Event-Driven Listeners

### Event System Architecture

**File**: `app/Providers/EventServiceProvider.php`

### Customer Events

#### 1. CustomerRegistered Event

**Event**: `App\Events\Customer\CustomerRegistered`

**Listeners**:
- `Customer\CreateCustomerAuditLog` - Logs registration event
- `Customer\NotifyAdminOfRegistration` - Notifies admin

**Note**: Welcome email now handled synchronously in `CustomerService`, not via event

#### 2. CustomerEmailVerified Event

**Event**: `App\Events\Customer\CustomerEmailVerified`

**Listeners**:
- `Customer\CreateCustomerAuditLog` - Logs verification

#### 3. CustomerProfileUpdated Event

**Event**: `App\Events\Customer\CustomerProfileUpdated`

**Listeners**:
- `Customer\CreateCustomerAuditLog` - Logs profile changes

### Quotation Events

#### 1. QuotationRequested Event

**Event**: `App\Events\Quotation\QuotationRequested`

**Listeners**:
- `Customer\CreateCustomerAuditLog` - Logs quotation request

#### 2. QuotationGenerated Event

**Event**: `App\Events\Quotation\QuotationGenerated`

**Listeners**:
- `Quotation\GenerateQuotationPDF` - Generates PDF (queued)
- `Quotation\SendQuotationWhatsApp` - Sends WhatsApp notification (queued)

**Listener Details - SendQuotationWhatsApp**:

**File**: `app/Listeners/Quotation/SendQuotationWhatsApp.php`
**Status**: Queued (`implements ShouldQueue`)
**Current State**: Placeholder - WhatsApp integration not fully implemented

```php
// WhatsApp service integration placeholder
// WhatsAppService::sendMessage($customer->mobile, $message);

// For development: just skip sending
```

**Message Template**:
```
Hi {Customer Name}! 🎉

Your quotation #{Quotation Number} is ready!

📋 Policy: {Policy Type}
💰 Best Premium: ₹{Amount}
🏢 Companies: {Count}

View detailed quotation: {URL}

Need help? Reply to this message or call us.

Thank you for choosing us! 🙏
```

**Error Handling**: Failed jobs logged with quotation and customer details

### Insurance Policy Events

#### 1. PolicyCreated Event

**Event**: `App\Events\Insurance\PolicyCreated`

**Listeners**:
- `Customer\CreateCustomerAuditLog` - Logs policy creation

#### 2. PolicyRenewed Event

**Event**: `App\Events\Insurance\PolicyRenewed`

**Listeners**:
- `Customer\CreateCustomerAuditLog` - Logs renewal

#### 3. PolicyExpiringWarning Event

**Event**: `App\Events\Insurance\PolicyExpiringWarning`

**Listeners**:
- `Insurance\SendPolicyRenewalReminder` - Sends email and WhatsApp reminders (queued)

**Listener Details - SendPolicyRenewalReminder**:

**File**: `app/Listeners/Insurance/SendPolicyRenewalReminder.php`
**Status**: Queued (`implements ShouldQueue`)

**Functionality**:
- Conditional email sending via `shouldSendEmail()`
- Conditional WhatsApp sending via `shouldSendWhatsApp()`
- Warning type-based message formatting

**Warning Types**:
- `urgent` - 🚨 URGENT: Policy expires in {days} days
- `important` - ⏰ Important: Policy renewal required
- `early` - 📋 Policy Renewal Notice
- `default` - 📋 Policy Renewal Reminder

**Email Template**:
```
Dear {Customer Name},

This is a reminder that your insurance policy is expiring soon:

Policy Number: {Policy Number}
Policy Type: {Type}
Insurance Company: {Company}
Expiry Date: {Date}
Days to Expiry: {Days}

Please contact us to renew your policy and avoid any coverage gaps.

Best regards,
Your Insurance Team
```

**WhatsApp Template**:
```
{Emoji} Hi {Customer Name}!

Your insurance policy is expiring soon:

📋 Policy: {Policy Number}
📅 Expires: {Date}
⏳ Days left: {Days}

Renew now to avoid coverage gap:
{URL}

Need help? Reply to this message.

Stay protected! 🛡️
```

**Error Handling**: Failed jobs logged with policy, customer, and warning details

### Legacy Events (To Be Phased Out)

#### CustomerCreated Event (Legacy)

**Event**: `App\Events\CustomerCreated`

**Listeners**:
- `SendWelcomeEmail` - Legacy welcome email (queued)

**Note**: Being replaced by `Customer\CustomerRegistered` event

#### PolicyExpiring Event (Legacy)

**Event**: `App\Events\PolicyExpiring`

**Listeners**:
- `SendPolicyReminderNotification` - Legacy reminder (queued)

**Note**: Being replaced by `Insurance\PolicyExpiringWarning` event

---

## Notification System Architecture

### Configuration Files

**Dynamic Configuration**: `app/Providers/DynamicConfigServiceProvider.php`

Loads notification settings from database on application boot.

### Notification Categories

#### 1. Email Notifications

**Setting**: `notifications.email_enabled`
**Type**: Boolean
**Default**: `true`
**Helper**: `is_email_notification_enabled()`

**Database Source**: App Settings → `notifications` category → `email_notifications_enabled`

**Current Mailer**: SMTP (Mailtrap)
```
Host: sandbox.smtp.mailtrap.io
Port: 2525
Encryption: TLS
Username: d10d175f9a5b32
Password: 59a1245bfbc9ef
```

**Mail Configuration Override**:
```php
'mail.default' => $settings['mail_default_driver'] ?? 'smtp',
'mail.from.address' => $settings['mail_from_address'] ?? config('mail.from.address'),
'mail.from.name' => $settings['mail_from_name'] ?? config('mail.from.name'),
'mail.mailers.smtp.host' => $settings['mail_smtp_host'],
'mail.mailers.smtp.port' => (int)$settings['mail_smtp_port'],
'mail.mailers.smtp.encryption' => $settings['mail_smtp_encryption'],
'mail.mailers.smtp.username' => $settings['mail_smtp_username'],
'mail.mailers.smtp.password' => $settings['mail_smtp_password'],
```

#### 2. WhatsApp Notifications

**Setting**: `notifications.whatsapp_enabled`
**Type**: Boolean
**Default**: `true`
**Helper**: `is_whatsapp_notification_enabled()`

**Database Source**: App Settings → `notifications` category → `whatsapp_notifications_enabled`

**WhatsApp Configuration Override**:
```php
'whatsapp.sender_id' => $settings['whatsapp_sender_id'] ?? '919727793123',
'whatsapp.base_url' => $settings['whatsapp_base_url'] ?? 'https://api.botmastersender.com/api/v1/',
'whatsapp.auth_token' => $settings['whatsapp_auth_token'] ?? '53eb1f03-90be-49ce-9dbe-b23fe982b31f',
```

#### 3. Birthday Wishes

**Setting**: `notifications.birthday_wishes_enabled`
**Type**: Boolean
**Default**: `true`
**Helper**: `is_birthday_wishes_enabled()`

**Database Source**: App Settings → `notifications` category → `birthday_wishes_enabled`

**Effect**: Controls whether `send:birthday-wishes` command sends messages

#### 4. Renewal Reminder Days

**Setting**: `notifications.renewal_reminder_days`
**Type**: String (comma-separated integers)
**Default**: `30,15,7,1`
**Helper**: `get_renewal_reminder_days()` (returns array)

**Database Source**: App Settings → `notifications` category → `renewal_reminder_days`

**Effect**: Controls which days before expiry to send renewal reminders

**Example Values**:
- `30,15,7,1` - Standard (30, 15, 7, 1 days before)
- `45,30,15,7,3,1` - Extended reminders
- `7,1` - Last-minute only
- `30` - Single reminder 30 days before

---

## WhatsApp Integration

### Provider

**Service**: BotMasterSender
**API**: REST API via cURL
**Documentation**: https://api.botmastersender.com/

### Configuration

**Trait**: `app/Traits/WhatsAppApiTrait.php`

**Settings**:
```php
'sender_id' => '919727793123',
'base_url' => 'https://api.botmastersender.com/api/v1/',
'auth_token' => '53eb1f03-90be-49ce-9dbe-b23fe982b31f',
```

**Configuration Source**: Database App Settings → WhatsApp category

### API Methods

#### 1. Send Text Message

**Method**: `whatsAppSendMessage($messageText, $receiverId)`

**Process**:
1. Check if WhatsApp notifications enabled
2. Validate and format mobile number (adds 91 prefix if missing)
3. Send POST request to BotMasterSender API
4. Handle response and errors

**cURL Request**:
```php
CURLOPT_URL => 'https://api.botmastersender.com/api/v1/?action=send'
CURLOPT_POSTFIELDS => [
    'senderId' => '919727793123',
    'authToken' => '53eb1f03-90be-49ce-9dbe-b23fe982b31f',
    'messageText' => $messageText,
    'receiverId' => $formattedNumber,
]
```

**Timeout**: 30 seconds

#### 2. Send Message with Attachment

**Method**: `whatsAppSendMessageWithAttachment($messageText, $receiverId, $filePath)`

**Additional Validations**:
- File exists check
- File readable check
- File uploaded via `curl_file_create()`

**Timeout**: 60 seconds (longer for file uploads)

**Use Cases**:
- Policy document delivery
- Quotation PDF sending
- Certificate attachments

### Mobile Number Validation

**Method**: `validateAndFormatMobileNumber($mobileNumber)`

**Process**:
1. Remove all non-numeric characters
2. Add '91' prefix if not present
3. Validate format: `^91[0-9]{10}$` (Indian mobile format)
4. Return formatted number or `false`

**Examples**:
- Input: `9727793123` → Output: `919727793123` ✓
- Input: `+91-972-779-3123` → Output: `919727793123` ✓
- Input: `8888888888` → Output: `918888888888` ✓
- Input: `12345` → Output: `false` ✗

### Error Handling

**Common Errors**:

1. **Invalid Mobile Number**:
```
Exception: Invalid mobile number format: {number}
```

2. **Connection Failed**:
```
Exception: WhatsApp API connection failed: {curl_error}
```

3. **HTTP Error**:
```
Exception: WhatsApp API returned HTTP {code}
```

4. **Session Offline**:
```
Exception: WhatsApp session is offline. Please reconnect your WhatsApp
session in BotMasterSender dashboard.
```

5. **API Error**:
```
Exception: WhatsApp sending failed: {error_message}
```

### Notification Control

**Global Toggle**: Checked before every send

```php
if (!$this->isWhatsAppNotificationEnabled()) {
    \Log::info('WhatsApp notification skipped (disabled in settings)', [
        'receiver' => $receiverId,
    ]);
    return json_encode(['success' => false, 'message' => 'WhatsApp notifications disabled']);
}
```

**Setting**: `notifications.whatsapp_enabled` from App Settings

### Message Templates

All message templates are hardcoded in:
- `WhatsAppApiTrait` - Customer registration, insurance added
- `SendRenewalReminders` - Renewal reminders (vehicle and non-vehicle)
- `SendBirthdayWishes` - Birthday wishes
- Event listeners - Quotations, policy reminders

**Branding Elements** (consistent across all messages):
```
Best regards, / Warm wishes,
Parth Rawal
https://parthrawal.in
Your Trusted Insurance Advisor
"Think of Insurance, Think of Us."
```

---

## Email Notification System

### Mail Configuration

**Current Setup**: SMTP via Mailtrap (Development)

**Driver**: `smtp`
**Host**: `sandbox.smtp.mailtrap.io`
**Port**: `2525`
**Encryption**: `tls`
**From Address**: Configured in App Settings
**From Name**: Configured in App Settings

### Dynamic Configuration

**Provider**: `DynamicConfigServiceProvider`
**Category**: `mail`

**Settings Loaded**:
- `mail_default_driver` → `mail.default`
- `mail_from_address` → `mail.from.address`
- `mail_from_name` → `mail.from.name`
- `mail_smtp_host` → `mail.mailers.smtp.host`
- `mail_smtp_port` → `mail.mailers.smtp.port`
- `mail_smtp_encryption` → `mail.mailers.smtp.encryption`
- `mail_smtp_username` → `mail.mailers.smtp.username`
- `mail_smtp_password` → `mail.mailers.smtp.password`

### Mailable Classes

**Location**: `app/Mail/`

1. `CustomerPasswordResetMail` - Password reset emails
2. `FamilyLoginCredentialsMail` - Family member credentials
3. `ClaimNotificationMail` - Claim status notifications
4. `CustomerEmailVerificationMail` - Email verification

**Usage**: Standard Laravel Mail facade

```php
Mail::to($customer->email)->send(new CustomerPasswordResetMail($data));
```

### Email Sending in Listeners

**Example**: `Insurance\SendPolicyRenewalReminder`

```php
Mail::raw($message, function($mail) use ($customer, $subject) {
    $mail->to($customer->email)
         ->subject($subject);
});
```

**Conditional Sending**: Based on event method `shouldSendEmail()`

### Email Notification Toggle

**Setting**: `notifications.email_enabled`
**Default**: `true`
**Helper**: `is_email_notification_enabled()`

**Usage**:
```php
if (is_email_notification_enabled()) {
    Mail::to($customer->email)->send($mailable);
}
```

---

## Setup & Configuration

### Cron Job Setup

**Requirement**: Laravel Task Scheduler must run every minute

#### Linux/Unix Cron Entry

```bash
* * * * * cd /path/to/admin-panel && php artisan schedule:run >> /dev/null 2>&1
```

#### Windows Task Scheduler

**Action**: Start a program
**Program**: `C:\php\php.exe`
**Arguments**: `C:\wamp64\www\test\admin-panel\artisan schedule:run`
**Start in**: `C:\wamp64\www\test\admin-panel`
**Trigger**: Every 1 minute, indefinitely

### Required Schedule Configuration

**Edit**: `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    // Renewal reminders - Daily at 9:00 AM
    $schedule->command('send:renewal-reminders')
             ->dailyAt('09:00')
             ->withoutOverlapping()
             ->onOneServer();

    // Birthday wishes - Daily at 8:00 AM
    $schedule->command('send:birthday-wishes')
             ->dailyAt('08:00')
             ->withoutOverlapping()
             ->onOneServer();
}
```

**Scheduling Options**:

- `dailyAt('09:00')` - Runs at specific time (9:00 AM)
- `withoutOverlapping()` - Prevents concurrent execution
- `onOneServer()` - Runs on one server only (multi-server environments)
- `timezone('Asia/Kolkata')` - Optional: Specify timezone

**Alternative Schedules**:

```php
// Run every hour
$schedule->command('send:renewal-reminders')->hourly();

// Run twice daily (9 AM and 5 PM)
$schedule->command('send:renewal-reminders')->twiceDaily(9, 17);

// Run on weekdays only
$schedule->command('send:renewal-reminders')->weekdays()->at('09:00');

// Run with custom cron expression
$schedule->command('send:renewal-reminders')->cron('0 9 * * *');
```

### Queue Worker Setup (Production)

**1. Install Supervisor** (Linux):
```bash
sudo apt-get install supervisor
```

**2. Create Supervisor Config**: `/etc/supervisor/conf.d/insurance-queue.conf`

```ini
[program:insurance-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/admin-panel/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/admin-panel/storage/logs/queue-worker.log
stopwaitsecs=3600
```

**3. Start Supervisor**:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start insurance-queue-worker:*
```

**4. Check Status**:
```bash
sudo supervisorctl status insurance-queue-worker:*
```

### App Settings Configuration

**Access**: Admin Panel → Settings → App Settings

**Categories to Configure**:

1. **Notifications**
   - Email Notifications Enabled: `true/false`
   - WhatsApp Notifications Enabled: `true/false`
   - Birthday Wishes Enabled: `true/false`
   - Renewal Reminder Days: `30,15,7,1`

2. **WhatsApp**
   - Sender ID: `919727793123`
   - Base URL: `https://api.botmastersender.com/api/v1/`
   - Auth Token: `{your-token}`

3. **Mail**
   - Default Driver: `smtp`
   - From Address: `noreply@yourdomain.com`
   - From Name: `Parth Rawal Insurance`
   - SMTP Host: `smtp.yourdomain.com`
   - SMTP Port: `587`
   - SMTP Encryption: `tls`
   - SMTP Username: `{username}`
   - SMTP Password: `{password}`

### WhatsApp Session Setup

**Provider**: BotMasterSender

**Steps**:
1. Login to BotMasterSender dashboard
2. Connect WhatsApp account (QR code scan)
3. Keep session active
4. Note sender ID (phone number)
5. Copy API auth token
6. Update App Settings

**Common Issue**: Session offline

```
WhatsApp session is offline. Please reconnect your WhatsApp session
in BotMasterSender dashboard.
```

**Solution**: Re-scan QR code in BotMasterSender dashboard

---

## Monitoring & Logging

### Application Logs

**Location**: `storage/logs/laravel.log`

**Daily Rotation**: Enabled (Laravel default)

**What's Logged**:
- WhatsApp API errors
- Queue job failures
- Event listener failures
- Mail sending errors
- DynamicConfigServiceProvider issues

### Command Output Logs

**Renewal Reminders**:
```bash
php artisan send:renewal-reminders >> storage/logs/renewal-reminders.log 2>&1
```

**Birthday Wishes**:
```bash
php artisan send:birthday-wishes >> storage/logs/birthday-wishes.log 2>&1
```

**Add to Kernel.php Schedule**:
```php
$schedule->command('send:renewal-reminders')
         ->dailyAt('09:00')
         ->appendOutputTo(storage_path('logs/renewal-reminders.log'));
```

### Queue Job Monitoring

**Failed Jobs Table**: `failed_jobs`

**View Failed Jobs**:
```bash
php artisan queue:failed
```

**Retry Failed Job**:
```bash
php artisan queue:retry {job-id}
```

**Retry All Failed Jobs**:
```bash
php artisan queue:retry all
```

**Forget Failed Job**:
```bash
php artisan queue:forget {job-id}
```

**Flush All Failed Jobs**:
```bash
php artisan queue:flush
```

### Event Listener Error Logging

**Location**: Event listener classes (in `failed()` method)

**Example**: `SendQuotationWhatsApp`

```php
public function failed(QuotationGenerated $event, \Throwable $exception): void
{
    \Log::error('Failed to send quotation WhatsApp', [
        'quotation_id' => $event->quotation->id,
        'customer_id' => $event->quotation->customer_id,
        'customer_mobile' => $event->quotation->customer->mobile,
        'error' => $exception->getMessage(),
    ]);
}
```

**Log Format**: Structured with context (quotation ID, customer ID, error details)

### WhatsApp API Logging

**Trait**: `WhatsAppApiTrait`

**Logs**:
1. Notifications disabled:
```php
\Log::info('WhatsApp notification skipped (disabled in settings)', [
    'receiver' => $receiverId,
]);
```

2. All API errors thrown as exceptions (caught by callers)

### Health Monitoring Recommendations

**1. Check Cron is Running**:
```bash
# Add to schedule with output
$schedule->command('inspire')
         ->everyMinute()
         ->appendOutputTo(storage_path('logs/cron-heartbeat.log'));
```

**2. Monitor Queue Size** (if using database queue):
```bash
php artisan queue:monitor database --max=100
```

**3. Alert on Failed Jobs**:
```php
# In AppServiceProvider or custom command
if (DB::table('failed_jobs')->count() > 10) {
    // Send alert to admin
}
```

**4. Log Aggregation**: Use tools like:
- Laravel Telescope (development)
- Sentry (production errors)
- Papertrail (log aggregation)
- CloudWatch (AWS deployments)

---

## Troubleshooting

### Common Issues

#### 1. Scheduled Commands Not Running

**Symptom**: Renewal reminders or birthday wishes not being sent

**Diagnosis**:
```bash
# Check if cron is configured
crontab -l

# Manually run scheduler (should show output every minute)
php artisan schedule:run

# Check schedule list
php artisan schedule:list
```

**Solutions**:

A. **Kernel.php not configured**:
- Verify `schedule()` method has command definitions
- See [Schedule Configuration](#required-schedule-configuration)

B. **Cron not running**:
- Add cron entry: `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1`
- Restart cron: `sudo service cron restart`

C. **Wrong timezone**:
- Set timezone in schedule: `->timezone('Asia/Kolkata')`
- Or change `config/app.php`: `'timezone' => 'Asia/Kolkata'`

D. **Test manually**:
```bash
php artisan send:renewal-reminders
php artisan send:birthday-wishes
```

#### 2. WhatsApp Messages Not Sending

**Symptom**: Commands run but no messages received

**Diagnosis**:
```bash
# Check logs
tail -f storage/logs/laravel.log

# Test with one customer
php artisan send:birthday-wishes

# Check App Settings
php artisan config:show notifications
php artisan config:show whatsapp
```

**Solutions**:

A. **WhatsApp notifications disabled**:
- Check: `notifications.whatsapp_enabled` = `true`
- Update via App Settings admin panel

B. **WhatsApp session offline**:
```
Error: WhatsApp session is offline. Please reconnect your WhatsApp
session in BotMasterSender dashboard.
```
- Login to BotMasterSender
- Re-scan QR code
- Verify session is active

C. **Invalid API credentials**:
```
Error: WhatsApp API returned HTTP 401
```
- Verify `whatsapp.auth_token` in App Settings
- Check sender ID matches registered number

D. **Invalid mobile numbers**:
```
Error: Invalid mobile number format: {number}
```
- Check customer mobile numbers in database
- Format must be 10 digits (or 12 with 91 prefix)
- Validation regex: `^91[0-9]{10}$`

E. **API connectivity issues**:
```
Error: WhatsApp API connection failed: {curl_error}
```
- Check internet connectivity
- Verify firewall allows outbound HTTPS
- Test API URL: `https://api.botmastersender.com/api/v1/`

#### 3. Email Notifications Not Working

**Symptom**: Email notifications not being sent

**Diagnosis**:
```bash
# Check mail config
php artisan config:show mail

# Test email sending
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

**Solutions**:

A. **Email notifications disabled**:
- Check: `notifications.email_enabled` = `true`
- Update via App Settings admin panel

B. **SMTP configuration incorrect**:
- Verify all mail settings in App Settings
- Test with Mailtrap in development
- Check SMTP credentials with provider

C. **Firewall blocking SMTP**:
- Verify port 587 or 2525 is open
- Check with hosting provider

D. **From address issues**:
- Verify `mail.from.address` is valid
- Some SMTP providers require authenticated from address

#### 4. Queue Jobs Not Processing

**Symptom**: Queued jobs stuck or not executing

**Diagnosis**:
```bash
# Check queue connection
php artisan config:show queue

# Check jobs table (if using database driver)
php artisan db:table jobs --select=id,queue,payload,attempts

# Check failed jobs
php artisan queue:failed
```

**Solutions**:

A. **Using sync driver** (current setup):
- Jobs execute immediately, no worker needed
- If slow, consider switching to `database` or `redis`

B. **Queue worker not running** (if switched to database):
```bash
# Start worker
php artisan queue:work database --tries=3

# Check worker is running
ps aux | grep "queue:work"

# Use Supervisor for production (see Setup section)
```

C. **Jobs failing silently**:
```bash
# Check failed jobs table
php artisan queue:failed

# View job details
php artisan queue:failed {job-id}

# Retry
php artisan queue:retry all
```

D. **Memory issues**:
```bash
# Limit worker memory
php artisan queue:work --memory=512 --timeout=60

# Restart worker after processing N jobs
php artisan queue:work --max-jobs=1000
```

#### 5. Birthday Wishes Not Sent for Some Customers

**Symptom**: Some birthdays missed

**Diagnosis**:
```bash
# Check birthday data
php artisan tinker
>>> Customer::whereMonth('date_of_birth', now()->month)
    ->whereDay('date_of_birth', now()->day)
    ->get(['name', 'date_of_birth', 'mobile_number', 'status']);
```

**Common Causes**:

A. **Feature disabled**:
- Check: `notifications.birthday_wishes_enabled` = `true`

B. **Missing data**:
- `date_of_birth` is NULL
- `mobile_number` is NULL
- Customer `status` = 0 (inactive)

C. **Invalid mobile number**:
- Number doesn't pass validation
- Check logs for "Invalid mobile number format"

D. **Date format issues**:
- Ensure `date_of_birth` stored as DATE type in MySQL
- Check year is reasonable (not 0000 or invalid)

#### 6. Renewal Reminders Sent on Wrong Days

**Symptom**: Reminders sent at incorrect intervals

**Diagnosis**:
```bash
# Check reminder days setting
php artisan config:show notifications

# Check what would be sent today
php artisan tinker
>>> $days = get_renewal_reminder_days();
>>> foreach ($days as $d) {
...     echo "Checking " . now()->addDays($d)->toDateString() . "\n";
... }
```

**Solutions**:

A. **Incorrect reminder days**:
- Default: `30,15,7,1`
- Update via App Settings: `renewal_reminder_days`
- Format: Comma-separated integers, no spaces

B. **Timezone issues**:
- Commands use server time
- Set correct timezone in `config/app.php`
- Or in schedule: `->timezone('Asia/Kolkata')`

C. **Already renewed policies included**:
- Query filters `is_renewed = 0`
- Ensure `is_renewed` flag updated on renewal

D. **Testing with manual run**:
```bash
# Manual run uses current date
php artisan send:renewal-reminders

# To simulate different date, modify command temporarily
# Or use Carbon::setTestNow() in tinker
```

### Debug Mode

**Enable detailed logging for commands**:

```php
// In SendRenewalReminders.php
public function handle()
{
    $this->info('Debug: Current date = ' . Carbon::now());
    $this->info('Debug: Reminder days = ' . implode(',', $reminderDays));

    // ... existing code ...

    foreach ($insurances as $insurance) {
        $this->info("Debug: Processing insurance #{$insurance->id} for {$insurance->customer->name}");
        // ... send message ...
    }
}
```

**Run with verbose output**:
```bash
php artisan send:renewal-reminders -v
php artisan send:birthday-wishes -vvv
```

### Testing Commands Locally

**1. Create test customer with birthday today**:
```sql
INSERT INTO customers (name, date_of_birth, mobile_number, status)
VALUES ('Test User', '1990-10-06', '9999999999', 1);
```

**2. Create test insurance expiring in 30 days**:
```sql
-- Assuming CustomerInsurance table structure
INSERT INTO customer_insurances (customer_id, policy_no, expired_date, is_renewed, status)
VALUES (1, 'TEST123', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 0, 1);
```

**3. Run commands**:
```bash
php artisan send:birthday-wishes
php artisan send:renewal-reminders
```

**4. Check logs**:
```bash
tail -f storage/logs/laravel.log
```

### Performance Issues

**Symptom**: Commands taking too long

**Solutions**:

A. **Too many records**:
- Current batch size: 100
- Increase if needed: `->chunk(500)`
- Or add database indexes on query columns

B. **API timeouts**:
- WhatsApp API timeout: 30s (text), 60s (attachments)
- Consider queue for large batches
- Add retry logic for failed messages

C. **Database query optimization**:
```php
// Add eager loading if needed
$insurances = CustomerInsurance::with(['customer', 'premiumType', 'insuranceCompany'])
    ->where(...)
    ->get();
```

D. **Monitor execution time**:
```bash
# Add timing to commands
time php artisan send:renewal-reminders

# Or in code
$startTime = microtime(true);
// ... command logic ...
$this->info("Execution time: " . round(microtime(true) - $startTime, 2) . "s");
```

---

## Appendix

### File References

**Commands**:
- `app/Console/Kernel.php` - Task scheduler
- `app/Console/Commands/SendRenewalReminders.php` - Renewal reminder command
- `app/Console/Commands/SendBirthdayWishes.php` - Birthday wishes command
- `app/Console/Commands/SecuritySetupCommand.php` - Security setup command

**Traits**:
- `app/Traits/WhatsAppApiTrait.php` - WhatsApp integration methods

**Helpers**:
- `app/Helpers/SettingsHelper.php` - App Settings helper functions

**Providers**:
- `app/Providers/DynamicConfigServiceProvider.php` - Dynamic configuration loader
- `app/Providers/EventServiceProvider.php` - Event-listener mappings

**Listeners** (Queued):
- `app/Listeners/Quotation/SendQuotationWhatsApp.php`
- `app/Listeners/Quotation/GenerateQuotationPDF.php`
- `app/Listeners/Insurance/SendPolicyRenewalReminder.php`
- `app/Listeners/Customer/SendWelcomeEmail.php`
- `app/Listeners/Customer/NotifyAdminOfRegistration.php`
- `app/Listeners/Customer/CreateCustomerAuditLog.php`
- `app/Listeners/SendWelcomeEmail.php` (Legacy)
- `app/Listeners/SendPolicyReminderNotification.php` (Legacy)

**Mail**:
- `app/Mail/CustomerPasswordResetMail.php`
- `app/Mail/FamilyLoginCredentialsMail.php`
- `app/Mail/ClaimNotificationMail.php`
- `app/Mail/CustomerEmailVerificationMail.php`

**Configuration**:
- `config/queue.php` - Queue configuration
- `config/mail.php` - Mail configuration (dynamically overridden)

### Database Tables

**Relevant Tables**:
- `customers` - Customer records (birthday wishes, contact info)
- `customer_insurances` - Insurance policies (renewal reminders)
- `app_settings` - Dynamic configuration
- `jobs` - Queued jobs (when using database driver)
- `failed_jobs` - Failed queue jobs
- `security_events` - Security audit events

**Key Columns**:

**customers**:
- `date_of_birth` - For birthday filtering
- `mobile_number` - WhatsApp recipient
- `email` - Email recipient
- `status` - Active/inactive flag

**customer_insurances**:
- `expired_date` - Expiry date for renewal reminders
- `is_renewed` - Renewal status flag
- `status` - Active/inactive flag
- `registration_no` - Vehicle registration (for vehicle policies)
- `policy_no` - Policy number

**app_settings**:
- `category` - Setting category (notifications, whatsapp, mail, etc.)
- `key` - Setting key
- `value` - Setting value
- `type` - Data type (string, boolean, integer, text)

### Environment Variables

**Queue**:
- `QUEUE_CONNECTION=sync` - Change to `database` or `redis` for async

**WhatsApp** (fallback values if not in App Settings):
- `WHATSAPP_SENDER_ID=919727793123`
- `WHATSAPP_BASE_URL=https://api.botmastersender.com/api/v1/`
- `WHATSAPP_AUTH_TOKEN={token}`

**Mail** (fallback values if not in App Settings):
- `MAIL_MAILER=smtp`
- `MAIL_HOST=sandbox.smtp.mailtrap.io`
- `MAIL_PORT=2525`
- `MAIL_USERNAME={username}`
- `MAIL_PASSWORD={password}`
- `MAIL_ENCRYPTION=tls`
- `MAIL_FROM_ADDRESS=noreply@example.com`
- `MAIL_FROM_NAME="${APP_NAME}"`

### Useful Commands

**Schedule Management**:
```bash
php artisan schedule:list                    # List all scheduled commands
php artisan schedule:run                     # Run scheduled commands (called by cron)
php artisan schedule:work                    # Run scheduler in foreground (dev only)
php artisan schedule:test --name="send:renewal-reminders"  # Test specific command
```

**Queue Management**:
```bash
php artisan queue:work                       # Start queue worker
php artisan queue:work --once                # Process one job
php artisan queue:listen                     # Listen for new jobs
php artisan queue:restart                    # Restart queue workers
php artisan queue:failed                     # List failed jobs
php artisan queue:retry all                  # Retry all failed jobs
php artisan queue:flush                      # Flush all failed jobs
php artisan queue:monitor database --max=100 # Alert if queue exceeds 100 jobs
```

**Configuration**:
```bash
php artisan config:cache                     # Cache configuration
php artisan config:clear                     # Clear config cache
php artisan config:show notifications        # Show notifications config
php artisan config:show whatsapp            # Show WhatsApp config
php artisan config:show mail                # Show mail config
```

**Testing**:
```bash
php artisan send:renewal-reminders          # Manual renewal reminder run
php artisan send:birthday-wishes            # Manual birthday wishes run
php artisan tinker                          # Laravel REPL for testing
```

**Database**:
```bash
php artisan db:table customers              # View customers table
php artisan db:table customer_insurances    # View insurances table
php artisan db:table jobs                   # View queued jobs (database driver)
php artisan db:table failed_jobs            # View failed jobs
```

### Quick Reference

**Schedule Frequencies**:
- `->everyMinute()` - Every minute
- `->everyFiveMinutes()` - Every 5 minutes
- `->hourly()` - Every hour
- `->dailyAt('09:00')` - Daily at 9:00 AM
- `->daily()` - Daily at midnight
- `->twiceDaily(9, 17)` - 9 AM and 5 PM
- `->weekdays()` - Monday through Friday
- `->weekends()` - Saturday and Sunday
- `->mondays()` - Every Monday
- `->monthly()` - First day of month
- `->quarterly()` - First day of quarter
- `->yearly()` - First day of year
- `->cron('0 9 * * *')` - Custom cron expression

**Schedule Constraints**:
- `->timezone('Asia/Kolkata')` - Specific timezone
- `->environments(['production'])` - Only in production
- `->when(fn() => condition)` - Conditional execution
- `->skip(fn() => condition)` - Skip if condition true
- `->withoutOverlapping()` - Prevent concurrent runs
- `->onOneServer()` - Run on one server only
- `->runInBackground()` - Run in background

**Schedule Callbacks**:
- `->before(fn() => ...)` - Before command runs
- `->after(fn() => ...)` - After command completes
- `->onSuccess(fn() => ...)` - On successful completion
- `->onFailure(fn() => ...)` - On failure
- `->sendOutputTo($path)` - Redirect output
- `->appendOutputTo($path)` - Append output
- `->emailOutputTo($email)` - Email output
- `->pingBefore($url)` - HTTP ping before
- `->pingAfter($url)` - HTTP ping after

---

**End of Documentation**
