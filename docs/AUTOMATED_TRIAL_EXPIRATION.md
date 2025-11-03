# Automated Trial Expiration System

## Overview

This system automatically monitors trial subscriptions, sends warning notifications, and suspends tenants when trials expire. It runs via Laravel's task scheduler with no manual intervention required.

## Components

### 1. Console Command: `CheckTrialExpiration`

**Location**: `app/Console/Commands/CheckTrialExpiration.php`

**Command**: `php artisan tenants:check-trial-expiration`

**Purpose**:
- Identifies expired trial subscriptions
- Automatically suspends tenants with expired trials
- Sends expiration notification emails
- Logs all actions to audit logs

**Options**:
- `--notify-upcoming`: Sends warning emails for trials expiring in 3 days

**Usage Examples**:
```bash
# Check and suspend expired trials
php artisan tenants:check-trial-expiration

# Send warning emails for trials expiring in 3 days
php artisan tenants:check-trial-expiration --notify-upcoming

# Both operations
php artisan tenants:check-trial-expiration --notify-upcoming
```

### 2. Scheduled Tasks

**Location**: `app/Console/Kernel.php`

**Schedule**:
```php
// Daily at 2:00 AM - Check expired trials and auto-suspend
$schedule->command('tenants:check-trial-expiration')
    ->dailyAt('02:00')
    ->withoutOverlapping();

// Daily at 9:00 AM - Send 3-day warning emails
$schedule->command('tenants:check-trial-expiration --notify-upcoming')
    ->dailyAt('09:00')
    ->withoutOverlapping();
```

**Why These Times?**
- **2:00 AM**: Low-traffic period, suspensions won't impact active users
- **9:00 AM**: Business hours start, users can read warnings and take action

**Running the Scheduler**:

For **production** (Linux/Unix servers):
```bash
# Add to crontab (run every minute)
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

For **local development** (Windows XAMPP):
```bash
# Keep this running in a terminal window
php artisan schedule:work

# OR run manually for testing
php artisan schedule:run
```

### 3. Email Templates

#### Trial Expiring Warning (`trial-expiring-warning.blade.php`)

**Location**: `resources/views/emails/trial-expiring-warning.blade.php`

**Sent**: 3 days before trial expiration

**Content**:
- Warning notice with countdown (3 days remaining)
- Trial expiration date
- What happens after expiration
- Call-to-action button to upgrade
- Company/tenant details

**Variables**:
- `$companyName`: Tenant company name
- `$trialEndsAt`: Carbon date object
- `$domain`: Tenant domain

#### Trial Expired (`trial-expired.blade.php`)

**Location**: `resources/views/emails/trial-expired.blade.php`

**Sent**: When trial expires and account is suspended

**Content**:
- Alert that account has been suspended
- Expiration details
- Data safety assurance
- Upgrade benefits list
- Call-to-action button to restore access

**Variables**:
- `$companyName`: Tenant company name
- `$trialEndsAt`: Carbon date object
- `$domain`: Tenant domain

## How It Works

### 1. Trial Expiration Detection

**Query**: Finds subscriptions where:
```php
Subscription::where('is_trial', true)
    ->where('status', 'trial')
    ->where('trial_ends_at', '<', now())
    ->get();
```

**Criteria**:
- `is_trial = true`
- `status = 'trial'` (not already suspended/cancelled)
- `trial_ends_at` is in the past

### 2. Auto-Suspension Process

For each expired trial:

1. **Update subscription status**:
   ```php
   $subscription->update(['status' => 'suspended']);
   ```

2. **Send expiration email** (if email exists):
   - Uses `trial-expired.blade.php` template
   - Subject: "Trial Expired - Action Required - {Company Name}"

3. **Log to audit trail**:
   ```php
   AuditLog::log(
       'trial.expired_auto_suspend',
       "Trial expired and tenant auto-suspended: {$companyName}",
       null,
       $tenant->id,
       ['expired_at' => $trial_ends_at, 'auto_suspended' => true]
   );
   ```

### 3. Warning Notification Process

For trials expiring in exactly 3 days:

**Query**:
```php
Subscription::where('is_trial', true)
    ->where('status', 'trial')
    ->whereDate('trial_ends_at', now()->addDays(3)->toDateString())
    ->get();
```

1. **Send warning email**:
   - Uses `trial-expiring-warning.blade.php` template
   - Subject: "Trial Expiring Soon - {Company Name}"

2. **Log notification**:
   ```php
   AuditLog::log(
       'trial.warning_sent',
       "Trial expiration warning sent for: {$companyName}",
       null,
       $tenant->id,
       ['email' => $email, 'expires_in_days' => 3]
   );
   ```

## Subscription Status Flow

```
Trial Created
    ↓
[status: trial]
[is_trial: true]
[trial_ends_at: future date]
    ↓
    ↓ (Trial active - 3 days before expiration)
    ↓
🔔 WARNING EMAIL SENT
    ↓
    ↓ (Trial expiration date reached)
    ↓
[status: suspended]  ← AUTO-SUSPENDED
[is_trial: true]
[trial_ends_at: past date]
    ↓
📧 EXPIRATION EMAIL SENT
    ↓
    ↓ (User upgrades)
    ↓
[status: active]
[is_trial: false]
```

## Middleware Integration

**Middleware**: `CheckSubscriptionStatus`

**Location**: `app/Http/Middleware/CheckSubscriptionStatus.php`

**Behavior**: Blocks access for suspended subscriptions

```php
if ($subscription->isSuspended()) {
    return redirect()->route('subscription.suspended')
        ->with('error', 'Your subscription has been suspended...');
}
```

**Result**: Users with suspended trials cannot access the tenant portal until they upgrade.

## Audit Log Events

### 1. Warning Notification Sent
```php
[
    'action' => 'trial.warning_sent',
    'description' => 'Trial expiration warning sent for: {Company Name}',
    'metadata' => [
        'email' => 'admin@company.com',
        'expires_in_days' => 3
    ]
]
```

### 2. Auto-Suspension
```php
[
    'action' => 'trial.expired_auto_suspend',
    'description' => 'Trial expired and tenant auto-suspended: {Company Name}',
    'metadata' => [
        'expired_at' => '2025-11-06 23:59:59',
        'auto_suspended' => true
    ]
]
```

## Testing

### Manual Testing

#### 1. Test Command Execution
```bash
# Test without any trials (should show "No expired trials")
php artisan tenants:check-trial-expiration

# Test warning notifications
php artisan tenants:check-trial-expiration --notify-upcoming
```

#### 2. Test with Real Data

**Create test tenant with near-expiration trial**:
```sql
-- Set trial to expire in 3 days for warning test
UPDATE subscriptions
SET trial_ends_at = DATE_ADD(NOW(), INTERVAL 3 DAY)
WHERE tenant_id = '{test-tenant-id}' AND is_trial = 1;

-- Run warning command
php artisan tenants:check-trial-expiration --notify-upcoming
```

**Create test tenant with expired trial**:
```sql
-- Set trial to yesterday for suspension test
UPDATE subscriptions
SET trial_ends_at = DATE_SUB(NOW(), INTERVAL 1 DAY)
WHERE tenant_id = '{test-tenant-id}' AND is_trial = 1;

-- Run expiration command
php artisan tenants:check-trial-expiration
```

#### 3. Verify Results

**Check subscription status**:
```sql
SELECT tenant_id, status, is_trial, trial_ends_at
FROM subscriptions
WHERE tenant_id = '{test-tenant-id}';
```

**Check audit logs**:
```sql
SELECT * FROM audit_logs
WHERE tenant_id = '{test-tenant-id}'
AND action LIKE 'trial.%'
ORDER BY created_at DESC;
```

**Check email logs** (if using log driver):
```bash
tail -f storage/logs/laravel.log | grep "trial"
```

### Automated Testing

Create feature test in `tests/Feature/TrialExpirationTest.php`:

```php
public function test_expired_trials_are_suspended()
{
    $tenant = Tenant::factory()->create();
    $subscription = Subscription::factory()->create([
        'tenant_id' => $tenant->id,
        'is_trial' => true,
        'status' => 'trial',
        'trial_ends_at' => now()->subDay(), // Yesterday
    ]);

    $this->artisan('tenants:check-trial-expiration')
        ->assertSuccessful();

    $this->assertEquals('suspended', $subscription->fresh()->status);
}
```

## Troubleshooting

### Scheduler Not Running

**Symptoms**: No trials getting suspended, no emails sent

**Check**:
```bash
# Verify scheduler is configured
php artisan schedule:list

# Run manually to test
php artisan schedule:run

# Check logs
tail -f storage/logs/laravel.log
```

**Fix**:
- For production: Add cron job (see "Running the Scheduler" above)
- For local dev: Run `php artisan schedule:work` in separate terminal

### Emails Not Sending

**Symptoms**: Command runs but no emails received

**Check mail configuration**:
```bash
# .env file
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

**Test email**:
```bash
php artisan tinker
Mail::raw('Test email', function($msg) {
    $msg->to('test@example.com')->subject('Test');
});
```

### Command Errors

**Symptoms**: Command fails with errors

**Check logs**:
```bash
tail -f storage/logs/laravel.log
```

**Common issues**:
1. Missing tenant email: Command skips and logs warning
2. Invalid email address: Exception caught and logged
3. Mail server connection failed: Exception caught and logged

**All errors are caught and logged** - the command continues processing other tenants.

## Configuration

### Customizing Warning Period

Change "3 days" to different period:

**In command** (`CheckTrialExpiration.php:55`):
```php
// Change addDays(3) to desired days
->whereDate('trial_ends_at', now()->addDays(7)->toDateString())
```

**In email template** (`trial-expiring-warning.blade.php:48`):
```blade
<td><strong style="color: #ff9800;">7 Days</strong></td>
```

### Customizing Schedule Times

**In scheduler** (`app/Console/Kernel.php`):
```php
// Change times as needed
$schedule->command('tenants:check-trial-expiration')
    ->dailyAt('03:00'); // Run at 3 AM instead

$schedule->command('tenants:check-trial-expiration --notify-upcoming')
    ->dailyAt('10:00'); // Run at 10 AM instead
```

### Email Customization

Edit templates:
- `resources/views/emails/trial-expiring-warning.blade.php`
- `resources/views/emails/trial-expired.blade.php`

Available variables:
- `$companyName`: Tenant company name
- `$trialEndsAt`: Carbon datetime object
- `$domain`: Tenant domain (e.g., `company.midastech.in`)

## Security & Best Practices

### 1. Graceful Degradation
- All email failures are caught and logged
- Command continues processing other tenants
- Uses `withoutOverlapping()` to prevent concurrent executions

### 2. Audit Trail
- Every action logged to `audit_logs` table
- Includes tenant ID, timestamp, and metadata
- Audit logs preserved even if tenant is deleted (optional)

### 3. Data Safety
- Suspension does NOT delete any data
- Users can upgrade anytime to restore access
- Database and files remain intact

### 4. Idempotency
- Safe to run multiple times
- Only processes trials with `status = 'trial'`
- Already-suspended tenants are skipped

## Summary

✅ **Fully Automated**: Runs daily without manual intervention
✅ **Proactive Warnings**: 3-day advance notice via email
✅ **Auto-Suspension**: Immediate suspension on expiration
✅ **Audit Trail**: Complete logging of all actions
✅ **Error Handling**: Graceful failure with detailed logging
✅ **Data Safety**: No data loss during suspension
✅ **Production Ready**: Tested and ready for deployment

---

**Last Updated**: 2025-11-03
**Author**: Multi-Tenancy Trial Expiration Implementation Team
