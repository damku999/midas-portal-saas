# Insurance Admin Panel - Complete Project Documentation

**Version**: 1.0.0
**Last Updated**: 2025-10-06
**Status**: ✅ Production Ready

---

## 📑 Table of Contents

1. [Project Overview](#project-overview)
2. [System Architecture](#system-architecture)
3. [Core Modules](#core-modules)
4. [Infrastructure Features](#infrastructure-features)
5. [Security Implementation](#security-implementation)
6. [Database Schema](#database-schema)
7. [API & Integration](#api--integration)
8. [Deployment Guide](#deployment-guide)
9. [Maintenance & Operations](#maintenance--operations)
10. [Troubleshooting](#troubleshooting)

---

## 🎯 Project Overview

### Purpose
Full-featured insurance management system for agencies to manage customers, policies, claims, quotations, brokers, and automated notifications.

### Target Users
- **Admin Portal**: Insurance agency staff, managers, administrators
- **Customer Portal**: Individual customers and family groups

### Technology Stack
- **Backend**: Laravel 10.49 (PHP 8.2.12)
- **Database**: MySQL 8.0+
- **Frontend**: Bootstrap 5 + Vanilla JS
- **Authentication**: Multi-guard (Admin + Customer)
- **Security**: 2FA, Encryption, Activity Logging

---

## 🏗️ System Architecture

### Multi-Guard Authentication

```
┌─────────────────────────────────────────────────┐
│           Application Entry Point               │
└─────────────┬───────────────────────────────────┘
              │
              ├─► Admin Guard (/admin/*)
              │   ├─ AdminController
              │   ├─ 2FA Middleware
              │   ├─ Role-based Access
              │   └─ Activity Logging
              │
              └─► Customer Guard (/customer/*)
                  ├─ CustomerAuthController
                  ├─ 2FA Optional
                  ├─ Policy Viewing
                  └─ Claim Submission
```

### App Settings Architecture

```
┌──────────────────┐
│   Application    │
│   Boot Process   │
└────────┬─────────┘
         │
         ▼
┌─────────────────────────────────┐
│ DynamicConfigServiceProvider     │
│ Loads settings from database     │
│ Sets Laravel config at runtime   │
└────────┬────────────────────────┘
         │
         ▼
┌──────────────────────┐     ┌────────────────┐
│  AppSettingService   │────►│  Redis Cache   │
│  - get()             │     │  TTL: 1 hour   │
│  - set()             │     └────────────────┘
│  - getByCategory()   │
└──────────┬───────────┘
           │
           ▼
┌───────────────────────┐
│   app_settings Table  │
│   - key (unique)      │
│   - value (encrypted) │
│   - category          │
│   - is_encrypted      │
└───────────────────────┘
```

### Export System Flow

```
Controller with ExportableTrait
         │
         ▼
┌─────────────────────────┐
│  ExcelExportService     │
│  - Builds query         │
│  - Applies filters      │
│  - Eager loads relations│
└──────────┬──────────────┘
           │
           ▼
┌─────────────────────┐
│  GenericExport      │
│  - Maps data        │
│  - Applies styling  │
│  - Auto-sizes cols  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Excel Download     │
│  Professional style │
└─────────────────────┘
```

---

## 📦 Core Modules

### 1. Customer Management

**Models**: `Customer`, `FamilyGroup`
**Controllers**: `CustomerController`, `FamilyGroupController`

**Features**:
- Individual customer records
- Family group linking
- Reference user tracking
- Activity logging
- Export functionality

**Key Relationships**:
- Customer → FamilyGroup (belongsTo)
- Customer → CustomerInsurance (hasMany)
- Customer → Claims (hasMany)
- Customer → Quotations (hasMany)

### 2. Policy Management

**Models**: `CustomerInsurance`, `PolicyType`, `PremiumType`, `AddonCover`
**Controllers**: `CustomerInsuranceController`, `PolicyTypeController`, `PremiumTypeController`

**Features**:
- Multi-type insurance (Motor, Health, Life, Fire, Marine)
- Premium calculation
- Addon cover management
- Renewal tracking
- Document storage

**Key Fields**:
- Policy number
- Start/end dates
- Premium amount
- Status (active, expired, cancelled)
- Insurance company

### 3. Claims Processing

**Model**: `Claim`
**Controller**: `ClaimController`

**Features**:
- Claim submission
- Status tracking (pending, approved, rejected, paid)
- Document uploads
- Email notifications
- Activity logging

**Workflow**:
1. Customer submits claim
2. Admin reviews documents
3. Status updates trigger notifications
4. Payment processing
5. Claim closure

### 4. Quotation System

**Model**: `Quotation`
**Controller**: `QuotationController`

**Features**:
- Professional quotation generation
- PDF export with branding
- Email to customer
- Conversion tracking
- Multiple insurance companies

**PDF Generation**:
- Company logo
- Customer details
- Policy breakdown
- Premium calculation
- Terms & conditions

### 5. Broker & RM Management

**Models**: `Broker`, `RelationshipManager`, `ReferenceUser`
**Controllers**: `BrokerController`, `RelationshipManagerController`, `ReferenceUsersController`

**Features**:
- Broker relationship management
- Commission tracking
- RM assignment
- Customer referral system
- Performance metrics

---

## 🔧 Infrastructure Features

### App Settings System

**Status**: ✅ 24/24 settings at 100% usage
**Location**: `app/Services/AppSettingService.php`

#### Categories

**1. Application Settings (9)**
```php
app_name                    // "Insurance Admin Panel"
app_timezone                // "Asia/Kolkata"
app_locale                  // "en"
app_currency                // "INR"
app_currency_symbol         // "₹"
app_date_format             // "d/m/Y"
app_time_format             // "12h" or "24h"
pagination_default          // "15"
session_lifetime            // "120" minutes
```

**2. WhatsApp Settings (3)**
```php
whatsapp_sender_id          // API sender ID
whatsapp_base_url           // API endpoint
whatsapp_auth_token         // 🔒 Encrypted
```

**3. Mail Settings (8)**
```php
mail_default_driver         // "smtp"
mail_from_address           // Default sender
mail_from_name              // Sender name
mail_smtp_host              // SMTP server
mail_smtp_port              // "587"
mail_smtp_encryption        // "tls"
mail_smtp_username          // 🔒 Encrypted
mail_smtp_password          // 🔒 Encrypted
```

**4. Notification Settings (4)**
```php
email_notifications_enabled     // "true"
whatsapp_notifications_enabled  // "true"
renewal_reminder_days           // "30,15,7,1"
birthday_wishes_enabled         // "true"
```

#### Usage in Code

```php
// Get setting
$timezone = AppSettingService::get('app_timezone');

// Get via config (loaded at boot)
$appName = config('app.name');

// Helper functions
$currency = app_currency();              // "INR"
$symbol = app_currency_symbol();         // "₹"
$formatted = format_indian_currency(1000); // "₹ 1,000.00"

// Time formatting
$time = format_app_time('2025-10-06 14:30:00');
// 12h format: "02:30 PM"
// 24h format: "14:30"

// Date formatting
$date = format_app_date('2025-10-06'); // "06/10/2025"
$datetime = format_app_datetime('2025-10-06 14:30:00');
// "06/10/2025 02:30 PM"
```

#### Multi-Portal Usage

**Admin Portal**:
- All settings available
- Can modify via AppSettingController
- Full encryption support

**Customer Portal**:
- Read-only access via config()
- Uses time/date formatting helpers
- Currency display in policies
- Example: `CustomerAuthController:1306` uses `config('app.pagination_default')`

### Export System

**Status**: ✅ Fully Implemented
**Location**: `app/Services/ExcelExportService.php`, `app/Exports/GenericExport.php`

#### Features

1. **Professional Styling**
   - Blue header (#4472C4) with white text
   - Alternating row colors (#F8F9FA)
   - Auto-sized columns
   - Border styling

2. **Advanced Filtering**
   ```php
   /export?search=john
   /export?status=active
   /export?start_date=2025-01-01&end_date=2025-12-31
   /export?format=csv
   ```

3. **Relationship Mapping**
   ```php
   // Maps relationship names, not IDs
   $customer->familyGroup ? $customer->familyGroup->name : 'Individual'
   // Not: $customer->family_group_id (5)
   ```

4. **Controller Implementation**
   ```php
   use App\Traits\ExportableTrait;

   class CustomerController extends Controller {
       use ExportableTrait;

       protected function getExportRelations(): array {
           return ['familyGroup'];
       }

       protected function getSearchableFields(): array {
           return ['name', 'email', 'mobile_number'];
       }

       protected function getExportConfig(Request $request): array {
           return [
               'headings' => ['ID', 'Name', 'Email', 'Mobile', 'Status'],
               'mapping' => function($customer) {
                   return [
                       $customer->id,
                       $customer->name,
                       $customer->email,
                       $customer->mobile_number,
                       ucfirst($customer->status),
                   ];
               },
               'with_mapping' => true
           ];
       }
   }
   ```

---

## 🔐 Security Implementation

### Two-Factor Authentication (2FA)

**Package**: `pragmarx/google2fa-laravel`
**Implementation**: Multi-guard support (Admin + Customer)

#### Features

1. **QR Code Setup**
   - Generated via `simplesoftwareio/simple-qrcode`
   - Google Authenticator compatible
   - Secret stored encrypted

2. **Recovery Codes**
   - 10 one-time recovery codes
   - Encrypted in database
   - Regeneration on demand

3. **Trusted Devices**
   - Remember device for 30 days
   - Device-specific trust tokens
   - Revoke by ID or hash

4. **Session-Based Guard Detection**
   ```php
   // Automatically detects guard context
   $guard = session('2fa_guard', 'admin');
   $service = $guard === 'admin'
       ? app(TwoFactorService::class)
       : app(CustomerTwoFactorService::class);
   ```

5. **Error Handling**
   - Guard-aware redirects
   - Detailed logging
   - User-friendly messages

### Encryption

**Method**: Laravel Crypt (AES-256-CBC)
**Key**: `APP_KEY` from `.env`

**Encrypted Fields**:
- `app_settings.value` (when `is_encrypted = true`)
- `users.google2fa_secret`
- `users.google2fa_recovery_codes`
- `customers.google2fa_secret` (if customer 2FA enabled)

**Backwards Compatibility**:
```php
// Graceful fallback for legacy unencrypted data
try {
    return Crypt::decryptString($value);
} catch (\Exception $e) {
    return $value; // Return original if decryption fails
}
```

### Activity Logging

**Package**: `spatie/laravel-activitylog`

**Logged Actions**:
- User login/logout
- Policy updates
- Claim status changes
- Settings modifications
- Export downloads
- 2FA events

**Usage**:
```php
activity()
    ->causedBy(auth()->user())
    ->performedOn($customer)
    ->log('Customer profile updated');
```

### Role-Based Access Control

**Package**: `spatie/laravel-permission`

**Roles**:
- Super Admin
- Admin
- Manager
- Staff
- Customer

**Permissions**:
- `view_policies`
- `create_policies`
- `edit_policies`
- `delete_policies`
- `approve_claims`
- etc.

---

## 💾 Database Schema

### Core Tables

**customers**
- id
- name, email, mobile_number
- family_group_id (nullable)
- status (active, inactive)
- google2fa_secret (encrypted, nullable)
- created_at, updated_at

**customer_insurances**
- id
- customer_id
- insurance_company_id
- policy_type_id
- premium_type_id
- policy_number
- start_date, end_date
- premium_amount
- status
- created_at, updated_at

**claims**
- id
- customer_id
- customer_insurance_id
- claim_number
- claim_amount
- status
- description
- created_at, updated_at

**quotations**
- id
- customer_id
- insurance_company_id
- quotation_number
- total_amount
- status
- created_at, updated_at

### Infrastructure Tables

**app_settings** (24 records)
- id
- key (unique)
- value (encrypted if is_encrypted = true)
- type (string, numeric, boolean, json)
- category (application, whatsapp, mail, notifications)
- description
- is_encrypted (boolean)
- is_active (boolean)
- created_at, updated_at

**activity_log** (Spatie package)
- id
- log_name
- description
- subject_type, subject_id
- causer_type, causer_id
- properties (json)
- created_at, updated_at

**users** (Admin)
- id
- name, email, password
- google2fa_enabled (boolean)
- google2fa_secret (encrypted, nullable)
- google2fa_recovery_codes (encrypted, nullable)
- trusted_devices (json, nullable)
- created_at, updated_at

---

## 🔌 API & Integration

### WhatsApp API Integration

**Provider**: Botmaster Sender
**API Base URL**: Configurable via `whatsapp_base_url` setting
**Authentication**: Token-based (stored encrypted)

**Implementation**: `app/Traits/WhatsAppApiTrait.php`

**Endpoints Used**:
- `POST /send` - Send single message
- `POST /send/bulk` - Send bulk messages

**Usage**:
```php
use App\Traits\WhatsAppApiTrait;

class NotificationService {
    use WhatsAppApiTrait;

    public function sendRenewalReminder($customer, $policy) {
        $message = "Your policy {$policy->policy_number} expires on {$policy->end_date}";
        $this->sendWhatsAppMessage($customer->mobile_number, $message);
    }
}
```

**Notifications Sent**:
- Renewal reminders (30, 15, 7, 1 days before)
- Birthday wishes
- Claim status updates
- Policy expiry alerts
- Welcome messages

### Email Integration

**Mailer**: Configurable SMTP
**Configuration**: Via App Settings (database) or .env

**Mail Classes**:
- `RenewalReminderMail`
- `BirthdayWishesMail`
- `ClaimStatusMail`
- `QuotationMail`
- `PolicyExpiryMail`

**Usage**:
```php
Mail::to($customer->email)->send(
    new RenewalReminderMail($customer, $policy)
);
```

### PDF Generation

**Package**: `barryvdh/laravel-dompdf`

**Generated Documents**:
- Policy quotations
- Policy certificates
- Claim forms
- Reports

**Example**:
```php
$pdf = PDF::loadView('pdfs.quotation', compact('quotation'));
return $pdf->download("quotation-{$quotation->quotation_number}.pdf");
```

---

## 🚀 Deployment Guide

### Production Checklist

#### 1. Environment Setup
```bash
# Set environment
APP_ENV=production
APP_DEBUG=false

# Generate application key
php artisan key:generate
```

#### 2. Database Migration
```bash
# Run migrations
php artisan migrate --force

# Seed app settings
php artisan db:seed --class=AppSettingsSeeder --force
```

#### 3. Update Encrypted Settings
```bash
php artisan tinker

>>> use App\Services\AppSettingService;
>>> AppSettingService::setEncrypted('whatsapp_auth_token', 'production-token');
>>> AppSettingService::setEncrypted('mail_smtp_username', 'smtp-user');
>>> AppSettingService::setEncrypted('mail_smtp_password', 'smtp-pass');
>>> exit
```

#### 4. Optimize Application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

#### 5. Set Permissions
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 6. Cron Jobs
Add to `/etc/crontab`:
```bash
# Laravel scheduler
* * * * * cd /path/to/admin-panel && php artisan schedule:run >> /dev/null 2>&1
```

**Scheduled Commands**:
- `php artisan reminders:renewal` - Daily at 9:00 AM
- `php artisan wishes:birthday` - Daily at 8:00 AM

---

## 🔧 Maintenance & Operations

### Regular Tasks

#### Daily
- Monitor error logs (`storage/logs/laravel.log`)
- Check scheduled task execution
- Review activity logs for anomalies

#### Weekly
- Database backup
- Review and clear old logs
- Check disk space
- Monitor API usage (WhatsApp)

#### Monthly
- Update Laravel and packages
- Security audit
- Performance review
- Backup retention cleanup

### Backup Strategy

**Database Backup**:
```bash
#!/bin/bash
DATE=$(date +%Y-%m-%d)
mysqldump -u user -p database_name > backup_$DATE.sql
# Encrypt backup
gpg -c backup_$DATE.sql
# Upload to S3 or cloud storage
```

**File Backup**:
- `storage/app/public/` - Uploaded documents
- `.env` - Encrypted separately
- Database backups

**Retention Policy**:
- Daily backups: Keep 7 days
- Weekly backups: Keep 4 weeks
- Monthly backups: Keep 12 months

### Monitoring

**Key Metrics**:
- Response time
- Database query performance
- Cache hit rate
- Failed jobs queue
- Disk usage
- API rate limits

**Logging**:
- Application logs: `storage/logs/laravel.log`
- Web server logs: Apache/Nginx access and error logs
- Database slow query log
- Cron job logs

---

## 🆘 Troubleshooting

### Common Issues

#### 1. Settings Not Loading

**Problem**: Changes to app settings not reflected

**Solution**:
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

#### 2. Encryption Errors

**Problem**: "The payload is invalid" or decryption fails

**Cause**: `APP_KEY` changed after data was encrypted

**Solution**:
- Never change `APP_KEY` in production
- If changed, re-encrypt all encrypted settings:
```bash
php artisan tinker
>>> AppSettingService::setEncrypted('key', 'value');
```

#### 3. Export Fails

**Problem**: Export returns 500 error

**Diagnostics**:
```bash
# Check logs
tail -f storage/logs/laravel.log

# Verify package installed
composer show maatwebsite/excel

# Clear cache
php artisan config:clear
```

#### 4. WhatsApp API Fails

**Problem**: Messages not sending

**Diagnostics**:
```bash
# Test API credentials
php artisan tinker
>>> AppSettingService::get('whatsapp_auth_token');
>>> AppSettingService::get('whatsapp_base_url');

# Test API call
>>> $client = new \GuzzleHttp\Client();
>>> $response = $client->post(config('whatsapp.base_url') . 'send', [...]);
```

#### 5. 2FA Issues

**Problem**: User locked out of 2FA

**Solution**:
```bash
# Disable 2FA for specific user
php artisan tinker
>>> $user = User::find(1);
>>> $user->google2fa_enabled = false;
>>> $user->save();
```

---

## 📞 Support & Resources

### Documentation Files

All documentation located in `/claudedocs/`:

| File | Purpose |
|------|---------|
| PROJECT_DOCUMENTATION.md | This file - Complete project reference |
| APP_SETTINGS_DOCUMENTATION.md | App Settings system details |
| IMPLEMENTATION_GUIDE.md | Step-by-step implementation guide |
| EXPORT_IMPLEMENTATION_STATUS.md | Export system implementation tracker |
| DEPLOYMENT_SUMMARY.md | Deployment checklist and summary |
| NOTIFICATION_SETTINGS_IMPLEMENTATION.md | Notification system documentation |
| APP_SETTINGS_USAGE_AUDIT.md | Settings usage audit report |

### Quick Links

- Main README: `/README.md`
- Log Viewer: `/log-viewer` (admin only)
- API Documentation: Contact development team
- Issue Tracking: GitHub Issues

---

## 📝 Changelog

### Version 1.0.0 (2025-10-06)

**Infrastructure**:
- ✅ App Settings system (24 settings, 100% usage)
- ✅ Generic Export system with professional styling
- ✅ Multi-guard authentication (Admin + Customer)
- ✅ Two-factor authentication with multi-guard support

**Features**:
- ✅ Customer & family group management
- ✅ Policy management (5 types)
- ✅ Claims processing
- ✅ Quotation generation with PDF
- ✅ Broker & RM management
- ✅ WhatsApp & Email notifications
- ✅ Birthday wishes automation
- ✅ Renewal reminder system (30/15/7/1 days)

**Security**:
- ✅ AES-256-CBC encryption for sensitive data
- ✅ 2FA with recovery codes
- ✅ Trusted device management
- ✅ Activity logging
- ✅ Role-based access control

**Optimizations**:
- ✅ Removed 47 bloat settings (100% usage rate achieved)
- ✅ Deleted 10 old export classes (replaced with generic system)
- ✅ Session-based 2FA guard detection
- ✅ Cache optimization (1-hour TTL)

---

**End of Complete Project Documentation**

*For updates or questions, refer to individual documentation files in `/claudedocs/` or contact the development team.*
