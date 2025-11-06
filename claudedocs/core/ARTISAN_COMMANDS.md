# Artisan Commands Reference

**Complete documentation for all 11 custom Artisan commands in the Midas Portal application**

---

## Table of Contents

- [Overview](#overview)
- [Command Scheduling](#command-scheduling)
- [Subscription Management Commands](#subscription-management-commands)
- [Notification Commands](#notification-commands)
- [Lead Management Commands](#lead-management-commands)
- [Security Commands](#security-commands)
- [Testing & Validation Commands](#testing--validation-commands)
- [Running Commands](#running-commands)
- [Best Practices](#best-practices)

---

## Overview

The application includes 11 custom Artisan commands for automated tasks including subscription management, notifications, lead follow-ups, and security setup.

### Command Statistics

- **Total Custom Commands**: 11 commands
- **Scheduled Commands**: 4 commands (run automatically via cron)
- **Manual Commands**: 7 commands (run on-demand)
- **Location**: `app/Console/Commands/`
- **Scheduler**: `app/Console/Kernel.php`

###Command Categories

| Category | Commands | Purpose |
|----------|----------|---------|
| **Subscription Management** | 2 | Trial processing, expiration checking |
| **Notifications** | 4 | Renewal reminders, birthday wishes, failed notification retry, testing |
| **Lead Management** | 1 | Follow-up reminders |
| **Security** | 1 | Security setup and configuration |
| **Testing** | 3 | Template testing and validation |

---

## Command Scheduling

### Cron Configuration

Add to server crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Commands

**Location**: `app/Console/Kernel.php` → `schedule()` method

```php
// Daily at 2 AM - Check expired trials and suspend
$schedule->command('tenants:check-trial-expiration')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Log::error('Trial expiration check failed');
    });

// Daily at 9 AM - Send 3-day expiration warnings
$schedule->command('tenants:check-trial-expiration --notify-upcoming')
    ->dailyAt('09:00')
    ->withoutOverlapping();

// Daily at 8 AM - Send trial reminders (7,3,1 days before expiration)
$schedule->command('subscriptions:process-trials --send-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping();

// Hourly - Auto-convert expired trials with payment method
$schedule->command('subscriptions:process-trials --auto-convert')
    ->hourly()
    ->withoutOverlapping();
```

### Schedule Overview

| Time | Command | Purpose |
|------|---------|---------|
| **02:00 Daily** | `tenants:check-trial-expiration` | Suspend expired trials |
| **08:00 Daily** | `subscriptions:process-trials --send-reminders` | Send 7/3/1-day reminders |
| **09:00 Daily** | `tenants:check-trial-expiration --notify-upcoming` | Send 3-day warnings |
| **Every Hour** | `subscriptions:process-trials --auto-convert` | Auto-convert trials |

---

## Subscription Management Commands

### 1. subscriptions:process-trials

**File**: `ProcessTrialSubscriptions.php`
**Purpose**: Process trial subscriptions - send reminders and auto-convert to paid
**Schedule**: Daily at 8AM (reminders), Hourly (auto-conversion)

#### Signature

```bash
php artisan subscriptions:process-trials [options]
```

#### Options

| Option | Description | Default |
|--------|-------------|---------|
| `--send-reminders` | Send trial expiration reminders | ✓ (if no options) |
| `--auto-convert` | Auto-convert expired trials with payment method | ✓ (if no options) |

#### Features

**1. Trial Expiration Reminders**

- Sends email reminders at 7, 3, and 1 days before trial expiration
- Uses `TrialExpiringMail` mailable
- Targets subscriptions with `is_trial=true` and `status='trial'`
- Sends to admin email from tenant data

**2. Auto-Conversion**

- Converts expired trials with `auto_renew=true` and payment method on file
- Updates subscription status from `trial` to `active`
- Calculates subscription end date based on billing interval
- Logs all conversions for audit trail

#### Usage Examples

```bash
# Run both reminders and auto-conversion (default)
php artisan subscriptions:process-trials

# Send only reminders
php artisan subscriptions:process-trials --send-reminders

# Auto-convert only
php artisan subscriptions:process-trials --auto-convert
```

#### Output Example

```
Starting trial subscription processing...
Sending trial expiration reminders...
✓ Sent 7-day reminder to: admin@acme.com (Tenant: acme-insurance)
✓ Sent 3-day reminder to: admin@xyz.com (Tenant: xyz-brokers)
Processed 2 subscription(s) for 7-day reminders
Processed 1 subscription(s) for 3-day reminders

Auto-converting expired trials with payment method...
✓ Auto-converted: Tenant abc-insurance to Professional (expires: 2025-12-06)
Auto-conversion completed: 1 successful, 0 failed

Trial subscription processing completed!
```

#### Reminder Schedule Logic

```php
$reminderDays = [7, 3, 1];

foreach ($reminderDays as $days) {
    $subscriptions = Subscription::with(['tenant.domains', 'plan'])
        ->where('is_trial', true)
        ->where('status', 'trial')
        ->whereDate('trial_ends_at', '=', now()->addDays($days)->toDateString())
        ->get();

    // Send reminder emails...
}
```

#### Auto-Conversion Criteria

```php
$subscriptions = Subscription::with(['tenant', 'plan'])
    ->where('is_trial', true)
    ->where('status', 'trial')
    ->where('auto_renew', true)               // Auto-renew enabled
    ->whereNotNull('payment_method')          // Payment method saved
    ->whereNotNull('payment_gateway')         // Gateway configured
    ->where('trial_ends_at', '<=', now())     // Trial expired
    ->get();
```

#### Billing Interval Calculation

```php
private function calculateSubscriptionEndDate(string $billingInterval): \Carbon\Carbon
{
    return match ($billingInterval) {
        'week' => now()->addWeek(),
        'month' => now()->addMonth(),
        'two_month' => now()->addMonths(2),
        'quarter' => now()->addMonths(3),
        'six_month' => now()->addMonths(6),
        'year' => now()->addYear(),
        default => now()->addMonth(),
    };
}
```

### 2. tenants:check-trial-expiration

**File**: `CheckTrialExpiration.php`
**Purpose**: Check expired trials, suspend tenants, send expiration warnings
**Schedule**: Daily at 2AM (suspension), 9AM (warnings)

#### Signature

```bash
php artisan tenants:check-trial-expiration [options]
```

#### Options

| Option | Description |
|--------|-------------|
| `--notify-upcoming` | Send notifications for trials expiring in 3 days |

#### Features

**1. Expired Trial Suspension**

- Finds trials where `trial_ends_at < now()`
- Updates subscription status to `suspended`
- Sends expiration notification email
- Logs suspension event in audit log

**2. Upcoming Expiration Warnings**

- Finds trials expiring in exactly 3 days
- Sends warning email to tenant admin
- Logs warning event

#### Usage Examples

```bash
# Suspend expired trials (default)
php artisan tenants:check-trial-expiration

# Send 3-day expiration warnings
php artisan tenants:check-trial-expiration --notify-upcoming
```

#### Output Example

```
Starting trial expiration check...
Checking for trials expiring in 3 days...
Found 2 trial(s) expiring in 3 days.
Sent warning email to: admin@company1.com (Company 1)
Sent warning email to: admin@company2.com (Company 2)

Checking for expired trials...
Found 1 expired trial(s). Suspending...
Suspended trial for: Old Company
Sent expiration email to: admin@oldcompany.com
Successfully suspended: 1 tenant(s)

Trial expiration check completed!
```

#### Suspension Logic

```php
$expiredTrials = Subscription::where('is_trial', true)
    ->where('status', 'trial')
    ->where('trial_ends_at', '<', now())
    ->with('tenant.domains')
    ->get();

foreach ($expiredTrials as $subscription) {
    // Suspend subscription
    $subscription->update(['status' => 'suspended']);

    // Send email notification
    Mail::send('emails.trial-expired', [
        'companyName' => $companyName,
        'trialEndsAt' => $subscription->trial_ends_at,
        'domain' => $tenant->domains->first()->domain,
    ], ...);

    // Log audit event
    AuditLog::log(
        'trial.expired_auto_suspend',
        "Trial expired and tenant auto-suspended: {$companyName}",
        null,
        $tenant->id,
        ['expired_at' => $subscription->trial_ends_at, 'auto_suspended' => true]
    );
}
```

---

## Notification Commands

### 3. send:renewal-reminders

**File**: `SendRenewalReminders.php`
**Purpose**: Send insurance policy renewal reminders via WhatsApp and Email
**Schedule**: Not scheduled (manual/custom cron)

#### Signature

```bash
php artisan send:renewal-reminders
```

#### Features

- Sends renewal reminders for policies expiring in configured days
- Default reminder days: 30, 15, 7, 1 (configurable via App Settings)
- Multi-channel: WhatsApp + Email notifications
- Uses notification template system with fallback
- Chunks processing for performance (100 policies per batch)

#### Configuration

```php
// Get reminder days from app settings
$reminderDays = get_renewal_reminder_days(); // Returns [30, 15, 7, 1]
```

#### Policy Selection Logic

```php
$insurances = CustomerInsurance::where(function ($query) use ($currentDate, $reminderDays) {
    foreach ($reminderDays as $days) {
        $targetDate = $currentDate->copy()->addDays($days)->startOfDay();
        $query->orWhereDate('expired_date', $targetDate);
    }
})
    ->where('is_renewed', 0)  // Not yet renewed
    ->where('status', 1)      // Active policies
    ->get();
```

#### Notification Type Selection

```php
private function getNotificationTypeCode(int $daysUntilExpiry): string
{
    if ($daysUntilExpiry >= 25 && $daysUntilExpiry <= 35) {
        return 'renewal_30_days';
    } elseif ($daysUntilExpiry >= 12 && $daysUntilExpiry <= 18) {
        return 'renewal_15_days';
    } elseif ($daysUntilExpiry >= 5 && $daysUntilExpiry <= 9) {
        return 'renewal_7_days';
    } elseif ($daysUntilExpiry >= 0 && $daysUntilExpiry <= 2) {
        return 'renewal_expired';
    }
    return 'renewal_30_days'; // Default
}
```

#### Output Example

```
Checking for renewals expiring in: 30,15,7,1 days
Renewal reminders sent successfully!
Total found: 45, Sent: 43, Skipped: 2
```

### 4. send:birthday-wishes

**File**: `SendBirthdayWishes.php`
**Purpose**: Send birthday wishes to customers via WhatsApp
**Schedule**: Not scheduled (recommended: daily at 9AM)

#### Signature

```bash
php artisan send:birthday-wishes
```

#### Features

- Finds customers with birthday today (month and day match)
- Sends personalized birthday wishes via WhatsApp
- Uses template system with fallback message
- Checks if birthday wishes feature is enabled via settings
- Only sends to active customers with mobile numbers

#### Customer Selection

```php
$customers = Customer::whereMonth('date_of_birth', $today->month)
    ->whereDay('date_of_birth', $today->day)
    ->where('status', 1)                     // Active customers only
    ->whereNotNull('mobile_number')          // Has mobile number
    ->whereNotNull('date_of_birth')          // Has DOB
    ->get();
```

#### Feature Flag Check

```php
if (!is_birthday_wishes_enabled()) {
    $this->info('Birthday wishes feature is disabled in settings.');
    return;
}
```

#### Fallback Message

```php
private function getBirthdayMessage(Customer $customer): string
{
    return "🎉 *Happy Birthday, {$customer->name}!* 🎂

Wishing you a wonderful day filled with joy, happiness, and blessings. May this year bring you good health, prosperity, and all the success you deserve.

Thank you for trusting us with your insurance needs. We're honored to be part of your journey!

Warm wishes,
" . company_advisor_name() . "
" . company_website() . "
" . company_title() . "
\"" . company_tagline() . "\"";
}
```

#### Output Example

```
Found 3 birthday(s) today!
✓ Sent birthday wish to John Doe (9876543210)
✓ Sent birthday wish to Jane Smith (9876543211)
✗ Failed for Bob Johnson: WhatsApp API error

🎉 Birthday wishes completed!
Total: 3, Sent: 2, Skipped: 1
```

#### Recommended Schedule

```php
// Add to app/Console/Kernel.php
$schedule->command('send:birthday-wishes')
    ->dailyAt('09:00')
    ->withoutOverlapping();
```

### 5. notifications:retry-failed

**File**: `RetryFailedNotifications.php`
**Purpose**: Retry failed notifications with exponential backoff
**Schedule**: Not scheduled (manual or custom cron for retries)
**Dependencies**: NotificationLoggerService

#### Signature

```bash
php artisan notifications:retry-failed [options]
```

#### Options

| Option | Description | Default |
|--------|-------------|---------|
| `--limit=N` | Maximum number of notifications to retry | 100 |
| `--force` | Force retry even if not due yet | false |

#### Features

- Exponential backoff for retry scheduling
- Respects `next_retry_at` timestamp (unless `--force`)
- Comprehensive retry reporting
- Logs all retry attempts
- Returns failure code if any retries fail

#### Usage Examples

```bash
# Retry up to 100 failed notifications (default)
php artisan notifications:retry-failed

# Retry up to 50 notifications
php artisan notifications:retry-failed --limit=50

# Force immediate retry regardless of schedule
php artisan notifications:retry-failed --force

# Retry 200 notifications with force
php artisan notifications:retry-failed --limit=200 --force
```

#### Output Example

```
Starting failed notifications retry process...
Found 15 notification(s) to retry.
Retrying Log #123 (Attempt #2)...
  ✓ Successfully queued Log #123
Retrying Log #124 (Attempt #1)...
  ✗ Failed to queue Log #124
Skipping Log #125 - retry scheduled for 2025-11-06 14:30:00

+------------------+-------+
| Status           | Count |
+------------------+-------+
| Retried          | 12    |
| Skipped          | 2     |
| Failed           | 1     |
| Total Processed  | 15    |
+------------------+-------+

⚠ Some notifications failed to retry. Check logs for details.
```

#### Return Codes

| Code | Meaning |
|------|---------|
| `0` (SUCCESS) | All retries succeeded or skipped |
| `1` (FAILURE) | One or more retries failed |

---

## Lead Management Commands

### 6. leads:send-follow-up-reminders

**File**: `SendFollowUpReminders.php`
**Purpose**: Send follow-up reminders for leads with upcoming or overdue follow-ups
**Schedule**: Not scheduled (recommended: daily at 8AM)

#### Signature

```bash
php artisan leads:send-follow-up-reminders [options]
```

#### Options

| Option | Description | Default |
|--------|-------------|---------|
| `--days-ahead=N` | Number of days ahead to check | 1 |

#### Features

**1. Overdue Follow-ups**
- Finds leads with `next_follow_up_date < now()`
- Sends urgent reminder to assigned user
- Logs reminder as lead activity
- Calculates days overdue

**2. Upcoming Follow-ups**
- Finds leads with follow-up date within N days
- Sends advance reminder to assigned user
- Helps prevent missed follow-ups

**3. Scheduled Activities**
- Finds activities scheduled for today
- Sends activity reminders to assigned users
- Includes activity details and lead context

#### Usage Examples

```bash
# Check for follow-ups due today or tomorrow (default)
php artisan leads:send-follow-up-reminders

# Check for follow-ups due within next 3 days
php artisan leads:send-follow-up-reminders --days-ahead=3

# Check for immediate follow-ups only (today)
php artisan leads:send-follow-up-reminders --days-ahead=0
```

#### Lead Selection Queries

```php
// Overdue follow-ups
$overdueLeads = Lead::with(['assignedUser', 'status', 'source'])
    ->followUpOverdue()  // Scope: next_follow_up_date < now()
    ->active()           // Scope: active leads only
    ->get();

// Upcoming follow-ups
$upcomingLeads = Lead::with(['assignedUser', 'status', 'source'])
    ->whereNotNull('next_follow_up_date')
    ->whereDate('next_follow_up_date', '>', now())
    ->whereDate('next_follow_up_date', '<=', now()->addDays($daysAhead))
    ->active()
    ->get();

// Today's activities
$todayActivities = LeadActivity::with(['lead.assignedUser', 'creator'])
    ->today()            // Scope: scheduled_at = today
    ->pending()          // Scope: status = pending
    ->get();
```

#### Reminder Message Format

**Overdue Follow-up**:
```
🚨 OVERDUE FOLLOW-UP

Lead: John Doe (LEAD-2025-001)
Status: Qualified
Source: Website Inquiry
Follow-up Date: 2025-11-03
Days Overdue: 3
Mobile: 9876543210
Email: john@example.com
```

**Upcoming Follow-up**:
```
📅 UPCOMING FOLLOW-UP

Lead: Jane Smith (LEAD-2025-002)
Status: Contacted
Source: Referral
Follow-up Date: 2025-11-07
Days Until: 1
Mobile: 9876543211
Email: jane@example.com
```

**Activity Reminder**:
```
📋 ACTIVITY SCHEDULED TODAY

Activity: Call to discuss policy options
Type: call
Lead: Bob Johnson (LEAD-2025-003)
Scheduled Time: 14:30
Description: Follow-up call regarding health insurance quote
```

#### Activity Logging

```php
LeadActivity::create([
    'lead_id' => $lead->id,
    'activity_type' => LeadActivity::TYPE_NOTE,
    'subject' => 'Follow-up Reminder Sent',
    'description' => "Overdue follow-up reminder sent to {$lead->assignedUser->name} ({$daysOverdue} days overdue)",
    'created_by' => 1, // System user
]);
```

#### Output Example

```
Checking for follow-ups due within 1 days...
Found 5 overdue follow-ups
Found 3 upcoming follow-ups
Found 2 activities scheduled for today

Sent overdue reminder for lead LEAD-2025-001 to Alice Admin
Sent overdue reminder for lead LEAD-2025-005 to Bob Manager
Sent upcoming reminder for lead LEAD-2025-010 to Alice Admin
Sent activity reminder for Call to discuss options to Bob Manager

Follow-up reminders sent successfully!
```

#### Recommended Schedule

```php
// Add to app/Console/Kernel.php
$schedule->command('leads:send-follow-up-reminders --days-ahead=1')
    ->dailyAt('08:00')
    ->withoutOverlapping();
```

---

## Security Commands

### 7. security:setup

**File**: `SecuritySetupCommand.php`
**Purpose**: Set up comprehensive security features for the application
**Schedule**: Manual only (one-time setup)

#### Signature

```bash
php artisan security:setup [options]
```

#### Options

| Option | Description |
|--------|-------------|
| `--force` | Force setup even if already configured |

#### Setup Steps

The command performs 7 comprehensive setup steps:

**1. Prerequisites Check**
- PHP version >= 8.1
- Laravel framework presence
- Database connection
- Storage directory writable
- Required PHP extensions (openssl, mbstring)

**2. Security Database Setup**
- Creates `security_events` table
- Verifies table structure
- Validates required columns

**3. Security Logging Configuration**
- Creates security log file (`storage/logs/security.log`)
- Sets proper file permissions (0644)
- Validates logging configuration in `config/logging.php`

**4. Security Key Generation**
- Verifies `APP_KEY` is set
- Generates application key if missing
- Validates CSP nonce generation

**5. File Permissions**
- Sets proper permissions for storage directories
- Secures `.env` file (chmod 0600)
- Ensures framework directories are writable

**6. Configuration Validation**
- Checks `APP_DEBUG` setting (should be false in production)
- Validates `SESSION_SECURE` setting (should be true in production)
- Verifies security configuration loaded

**7. Security Tests**
- Runs `security:test` command
- Reports test results
- Logs any failures

#### Usage Example

```bash
# First-time setup
php artisan security:setup

# Force re-setup
php artisan security:setup --force
```

#### Output Example

```
🛡️ Setting up Security Features...

1. Checking prerequisites...
  ✅ PHP version >= 8.1
  ✅ Laravel framework
  ✅ Database connection
  ✅ Storage directory writable
  ✅ Required extensions

2. Setting up security database tables...
  ✅ Security events table created
  ✅ Security events table structure valid

3. Configuring security logging...
  ✅ Security log file created
  ✅ Log file permissions set
  ✅ Security logging channel configured

4. Generating security keys...
  ✅ Application key configured
  ✅ Security keys validated

5. Setting up secure file permissions...
  ✅ Set permissions for /path/storage
  ✅ Set permissions for /path/storage/logs
  ✅ Set permissions for /path/storage/framework
  ✅ Set permissions for /path/storage/app
  ✅ Secured .env file permissions

6. Validating security configuration...
  ✅ Configuration validation passed

7. Running security tests...
  ✅ Security tests passed

✅ Security setup completed successfully!

🛡️ Security Setup Summary:

✅ Enhanced Input Validation: SecureFormRequest classes implemented
✅ Advanced Authorization: Resource-level access control enabled
✅ SQL Injection Prevention: Secure query patterns implemented
✅ CSRF Protection: Enhanced CSRF validation active
✅ Secure File Uploads: Multi-layer file validation enabled
✅ Audit Logging: Comprehensive security event tracking
✅ Security Headers: CSP and security headers configured
✅ Monitoring & Alerts: Real-time security monitoring active

📚 Next Steps:
1. Copy settings from .env.security to your .env file
2. Review SECURITY.md for detailed configuration options
3. Run periodic security tests: php artisan security:test --comprehensive
4. Monitor security logs in storage/logs/security.log
5. Set up alerting for critical security events

🔗 Documentation:
• SECURITY.md - Complete security documentation
• .env.security - Security configuration template
• config/security.php - Security settings

Your Laravel application is now secured with enterprise-grade security features! 🚀
```

#### Security Features Enabled

| Feature | Description |
|---------|-------------|
| **Enhanced Input Validation** | SecureFormRequest classes |
| **Advanced Authorization** | Resource-level access control |
| **SQL Injection Prevention** | Secure query patterns |
| **CSRF Protection** | Enhanced validation |
| **Secure File Uploads** | Multi-layer validation |
| **Audit Logging** | Security event tracking |
| **Security Headers** | CSP and headers configured |
| **Monitoring & Alerts** | Real-time monitoring |

---

## Testing & Validation Commands

### 8. notifications:test-email

**File**: `TestEmailNotification.php`
**Purpose**: Test email notification system
**Schedule**: Manual only

Basic test command for email notification delivery.

### 9. notifications:test-logging

**File**: `TestNotificationLogging.php`
**Purpose**: Test notification logging system
**Schedule**: Manual only

Tests the notification logging infrastructure and database connections.

### 10. notifications:test-templates

**File**: `TestNotificationTemplates.php`
**Purpose**: Test notification template rendering
**Schedule**: Manual only

Tests template rendering with sample data for all notification types.

### 11. notifications:validate-templates

**File**: `ValidateNotificationTemplates.php`
**Purpose**: Validate all notification templates for syntax and variable errors
**Schedule**: Manual only

#### Signature

```bash
php artisan notifications:validate-templates
```

#### Features

- Validates all templates in `notification_templates` table
- Checks for syntax errors
- Validates variable placeholders
- Reports missing or invalid templates
- Provides detailed error reporting

---

## Running Commands

### Manual Execution

```bash
# Run any command manually
php artisan command:name

# With options
php artisan command:name --option-name

# With parameters
php artisan command:name --option=value

# Get help for a command
php artisan help command:name
```

### Scheduled Execution

```bash
# View scheduled commands
php artisan schedule:list

# Run scheduler (called by cron)
php artisan schedule:run

# Test scheduler without running
php artisan schedule:test
```

### Background Execution

```bash
# Run command in background (Linux/Mac)
php artisan command:name > /dev/null 2>&1 &

# Run command with output to log file
php artisan command:name >> storage/logs/command.log 2>&1 &
```

### Queue Commands

```bash
# Some commands dispatch jobs to queue
# Make sure queue worker is running
php artisan queue:work

# Or use supervisor for production
supervisor> start laravel-worker:*
```

---

## Best Practices

### 1. Scheduling

**DO**: Use Laravel's scheduler for recurring commands
```php
// app/Console/Kernel.php
$schedule->command('send:renewal-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Log::error('Renewal reminders failed');
    });
```

**DON'T**: Create multiple cron entries for Laravel commands
```bash
# ❌ Wrong: Direct cron for each command
0 8 * * * php artisan send:renewal-reminders
0 9 * * * php artisan send:birthday-wishes
```

### 2. Error Handling

**DO**: Use try-catch and log errors
```php
public function handle()
{
    try {
        $this->processData();
        $this->info('Command completed successfully');
        return Command::SUCCESS;
    } catch (\Exception $e) {
        $this->error('Command failed: ' . $e->getMessage());
        \Log::error('Command error', ['error' => $e->getMessage()]);
        return Command::FAILURE;
    }
}
```

**DON'T**: Let exceptions crash silently
```php
// ❌ Wrong: No error handling
public function handle()
{
    $this->processData(); // Could throw unhandled exception
}
```

### 3. Output Feedback

**DO**: Provide clear progress indicators
```php
$customers = Customer::all();
$this->info("Found {$customers->count()} customers to process");

$bar = $this->output->createProgressBar($customers->count());
foreach ($customers as $customer) {
    $this->processCustomer($customer);
    $bar->advance();
}
$bar->finish();

$this->newLine();
$this->info('Processing complete!');
```

**DON'T**: Run silently without feedback
```php
// ❌ Wrong: No progress indication
foreach ($customers as $customer) {
    $this->processCustomer($customer);
}
```

### 4. Overlapping Prevention

**DO**: Use `withoutOverlapping()` for long-running commands
```php
$schedule->command('long-running-command')
    ->hourly()
    ->withoutOverlapping(10); // Timeout after 10 minutes
```

**DON'T**: Allow command instances to stack
```php
// ❌ Wrong: Could create multiple instances
$schedule->command('long-running-command')
    ->everyMinute(); // No overlap protection
```

### 5. Chunk Processing

**DO**: Process large datasets in chunks
```php
Customer::chunk(100, function ($customers) {
    foreach ($customers as $customer) {
        $this->sendNotification($customer);
    }
});
```

**DON'T**: Load all records into memory
```php
// ❌ Wrong: Memory issues with large datasets
$customers = Customer::all(); // Loads all customers
foreach ($customers as $customer) {
    $this->sendNotification($customer);
}
```

### 6. Database Connections

**DO**: Specify connection for multi-database operations
```php
// Central database
$subscription = Subscription::on('central')
    ->where('tenant_id', $tenantId)
    ->first();

// Tenant database (after tenancy initialized)
tenancy()->initialize($tenant);
$customers = Customer::all();
```

**DON'T**: Assume default connection
```php
// ❌ Wrong: May use wrong database
$customers = Customer::all(); // Which database?
```

### 7. Return Codes

**DO**: Use appropriate return codes
```php
public function handle(): int
{
    if ($this->validateData()) {
        $this->processData();
        return Command::SUCCESS; // 0
    }
    return Command::FAILURE; // 1
}
```

**DON'T**: Always return success
```php
// ❌ Wrong: No failure indication
public function handle()
{
    try {
        $this->processData();
    } catch (\Exception $e) {
        // Caught exception but still returns success
    }
}
```

### 8. Logging

**DO**: Log important events and errors
```php
\Log::info('Command started', ['command' => 'send:renewal-reminders']);

try {
    $this->sendReminders();
    \Log::info('Reminders sent', ['count' => $count]);
} catch (\Exception $e) {
    \Log::error('Failed to send reminders', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
}
```

**DON'T**: Skip logging
```php
// ❌ Wrong: No audit trail
$this->sendReminders();
```

---

## Related Documentation

- [Service Layer](SERVICE_LAYER.md) - Services used by commands
- [Database Schema](DATABASE_SCHEMA.md) - Tables accessed by commands
- [Middleware Reference](MIDDLEWARE_REFERENCE.md) - HTTP context
- [Features Documentation](../FEATURES.md) - Feature specifications

---

**Last Updated**: 2025-11-06
**Total Commands**: 11 custom Artisan commands
**Documentation Coverage**: 100% (all commands documented)
