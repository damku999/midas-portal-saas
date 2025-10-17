# Email Integration - Quick Reference Guide

## Quick Start

### 1. Configure SMTP (.env)

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

### 2. Add Email Settings (Database)

```sql
INSERT INTO app_settings (category, key, value, display_name, is_active) VALUES
('email', 'email_from_address', 'noreply@parthrawal.in', 'Email From Address', 1),
('email', 'email_from_name', 'Parth Rawal Insurance Advisor', 'Email From Name', 1),
('email', 'email_reply_to', 'contact@parthrawal.in', 'Email Reply-To', 1);
```

### 3. Test Email

```bash
php artisan tinker
Mail::raw('Test email', fn($m) => $m->to('test@example.com')->subject('Test'));
```

---

## Usage Examples

### Send Customer Welcome Email

```php
$emailService = app(\App\Services\EmailService::class);
$emailService->sendFromCustomer('customer_welcome', $customer);
```

### Send Policy Email with PDF

```php
$insuranceService = app(\App\Services\CustomerInsuranceService::class);
$insuranceService->sendPolicyDocumentEmail($insurance);
```

### Send Quotation Email

```php
$quotationService = app(\App\Services\QuotationService::class);
$quotationService->sendQuotationViaEmail($quotation);
```

### Send Renewal Reminder

```php
$emailService = app(\App\Services\EmailService::class);
$emailService->sendFromInsurance('renewal_30_days', $insurance);
```

---

## Helper Functions

```php
is_email_notification_enabled()  // Check if emails enabled
email_from_address()             // Get from address
email_from_name()                // Get from name
email_reply_to()                 // Get reply-to address
```

---

## Notification Types

| Code               | Description              | Attachment |
|--------------------|--------------------------|------------|
| customer_welcome   | Welcome email            | No         |
| policy_created     | Policy document          | PDF        |
| quotation_ready    | Quotation comparison     | PDF        |
| renewal_30_days    | 30-day renewal reminder  | No         |
| renewal_15_days    | 15-day renewal reminder  | No         |
| renewal_7_days     | 7-day renewal reminder   | No         |
| renewal_expired    | Policy expired notice    | No         |

---

## File Locations

### Core Files
- `app/Services/EmailService.php` - Main email service
- `app/Mail/TemplatedNotification.php` - Mailable class
- `resources/views/emails/templated-notification.blade.php` - Email template

### Updated Files
- `app/Listeners/Customer/SendOnboardingWhatsApp.php`
- `app/Listeners/Quotation/SendQuotationWhatsApp.php`
- `app/Services/CustomerService.php`
- `app/Services/CustomerInsuranceService.php`
- `app/Services/QuotationService.php`
- `app/Console/Commands/SendRenewalReminders.php`
- `app/Helpers/SettingsHelper.php`

---

## Common Tasks

### Test Email Delivery

```bash
php artisan tinker
$customer = App\Models\Customer::first();
app(\App\Services\EmailService::class)->sendFromCustomer('customer_welcome', $customer);
```

### Check Email Logs

```bash
tail -f storage/logs/laravel.log | grep -i email
```

### Process Email Queue

```bash
php artisan queue:work --queue=default --timeout=300
```

### Retry Failed Emails

```bash
php artisan queue:failed        # List failed
php artisan queue:retry <id>   # Retry specific
php artisan queue:retry all    # Retry all
```

### Clear Config Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan queue:restart
```

---

## Troubleshooting Commands

```bash
# Test SMTP connection
php artisan tinker
Mail::raw('Test', fn($m) => $m->to('test@example.com'));

# Check queue status
php artisan queue:work --once

# View failed jobs
php artisan queue:failed

# Check email settings
php artisan tinker
app(\App\Services\AppSettingService::class)->get('email_from_address', 'email');

# Enable email notifications
DB::table('app_settings')->where('key', 'email_notifications_enabled')->update(['value' => 'true']);
```

---

## Log Patterns

### Successful Email
```
[INFO] Email sent successfully
  to: customer@example.com
  notification_type: customer_welcome
  subject: Welcome to Parth Rawal Insurance Advisor
```

### Skipped Email
```
[INFO] Onboarding email skipped (disabled in settings)
  customer_id: 123
```

### Failed Email
```
[ERROR] Email sending failed
  to: customer@example.com
  error: Connection refused
  trace: ...
```

---

## Quick Diagnostics

### Email Not Sending?

1. Check settings: `is_email_notification_enabled()`
2. Verify SMTP: `php artisan tinker` → Test email
3. Check queue: `php artisan queue:work`
4. Review logs: `storage/logs/laravel.log`

### Attachments Missing?

1. Verify file exists: `Storage::exists('public/...')`
2. Check permissions: `ls -la storage/app/public/`
3. Review log for validation errors

### Template Not Found?

1. Check database: `SELECT * FROM notification_templates WHERE channel = 'email'`
2. Verify template active: `is_active = 1`
3. Fallback will be used automatically

---

## Production Deployment

### Pre-Deploy Checklist

- [ ] Configure SMTP in `.env`
- [ ] Add email settings to database
- [ ] Test email delivery
- [ ] Start queue worker
- [ ] Monitor logs

### Queue Worker (Supervisor)

```ini
[program:laravel-worker]
command=php /path/to/artisan queue:work --sleep=3 --tries=3 --timeout=300
directory=/path/to/project
user=www-data
autostart=true
autorestart=true
```

### Monitor Commands

```bash
# Watch logs
tail -f storage/logs/laravel.log

# Check queue
watch -n 1 'php artisan queue:work --once'

# Monitor failed jobs
watch -n 10 'php artisan queue:failed | head -20'
```

---

## Emergency Disable

```bash
# Disable all email notifications
php artisan tinker
DB::table('app_settings')->where('key', 'email_notifications_enabled')->update(['value' => 'false']);

# Or in database
UPDATE app_settings SET value = 'false' WHERE key = 'email_notifications_enabled';
```

---

## Support Resources

- **Documentation:** `claudedocs/EMAIL_INTEGRATION_COMPLETE_REPORT.md`
- **Laravel Mail Docs:** https://laravel.com/docs/11.x/mail
- **Laravel Queue Docs:** https://laravel.com/docs/11.x/queues
- **Logs:** `storage/logs/laravel.log`
