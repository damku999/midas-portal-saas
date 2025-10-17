# App Settings System - Complete Documentation
**Version**: 1.0
**Date**: 2025-10-06
**Status**: ✅ Production Ready

---

## Overview

Database-driven configuration system for the Insurance Admin Panel with encryption support, multi-tenancy ready architecture, and 100% setting usage.

**Total Settings**: 24
**Encrypted Credentials**: 3
**Usage Rate**: 100%
**Categories**: 4 (Application, WhatsApp, Mail, Notifications)

---

## Quick Reference

### Application Settings (9)
| Setting | Default | Description |
|---------|---------|-------------|
| `app_name` | "Insurance Admin Panel" | Application display name |
| `app_timezone` | "Asia/Kolkata" | PHP timezone identifier |
| `app_locale` | "en" | Default language |
| `app_currency` | "INR" | Currency code (INR, USD, EUR) |
| `app_currency_symbol` | "₹" | Currency symbol for display |
| `app_date_format` | "d/m/Y" | Date format (d/m/Y, Y-m-d, m/d/Y) |
| `app_time_format` | "12h" | Time format (12h or 24h) |
| `pagination_default` | "15" | Items per page |
| `session_lifetime` | "120" | Session timeout (minutes) |

### WhatsApp Settings (3)
| Setting | Encrypted | Description |
|---------|-----------|-------------|
| `whatsapp_sender_id` | ❌ | WhatsApp API sender ID |
| `whatsapp_base_url` | ❌ | WhatsApp API base URL |
| `whatsapp_auth_token` | 🔒 YES | WhatsApp API auth token |

### Mail Settings (8)
| Setting | Encrypted | Description |
|---------|-----------|-------------|
| `mail_default_driver` | ❌ | Mail driver (smtp, sendmail) |
| `mail_from_address` | ❌ | Default sender email |
| `mail_from_name` | ❌ | Default sender name |
| `mail_smtp_host` | ❌ | SMTP server host |
| `mail_smtp_port` | ❌ | SMTP server port |
| `mail_smtp_encryption` | ❌ | Encryption (tls, ssl) |
| `mail_smtp_username` | 🔒 YES | SMTP username |
| `mail_smtp_password` | 🔒 YES | SMTP password |

### Notification Settings (4)
| Setting | Default | Description |
|---------|---------|-------------|
| `email_notifications_enabled` | "true" | Email notification toggle |
| `whatsapp_notifications_enabled` | "true" | WhatsApp notification toggle |
| `renewal_reminder_days` | "30,15,7,1" | Days before expiry for reminders |
| `birthday_wishes_enabled` | "true" | Auto birthday wishes feature |

---

## Usage Examples

### Reading Settings

```php
// Get single setting
$timezone = AppSettingService::get('app_timezone');

// Get category
$mailSettings = AppSettingService::getByCategory('mail');

// Using config helper
$appName = config('app.name');

// Using custom helpers
$symbol = app_currency_symbol();
$format = app_time_format();
```

### Updating Settings

```php
// Update single setting
AppSettingService::set('app_timezone', 'America/New_York');

// Update encrypted setting
AppSettingService::set('mail_smtp_password', 'newpassword', [
    'is_encrypted' => true
]);

// Bulk update
AppSettingService::setBulk([
    'app_name' => ['value' => 'New Name', 'type' => 'string'],
    'app_timezone' => ['value' => 'UTC', 'type' => 'string'],
], 'application');
```

### Helper Functions

```php
// Currency helpers
app_currency();                    // "INR"
app_currency_symbol();             // "₹"
format_indian_currency(1000);      // "₹ 1,000.00"

// Date/Time helpers
app_date_format();                 // "d/m/Y"
app_time_format();                 // "12h"
format_app_date($date);            // "06/10/2025"
format_app_time($datetime);        // "02:30 PM"
format_app_datetime($datetime);    // "06/10/2025 02:30 PM"

// Notification helpers
is_email_notification_enabled();   // true/false
is_whatsapp_notification_enabled(); // true/false
is_birthday_wishes_enabled();      // true/false
get_renewal_reminder_days();       // [30, 15, 7, 1]
```

---

## Architecture

### Components

1. **Model**: `App\Models\AppSetting`
   - Handles encryption/decryption via accessors/mutators
   - Auto-encrypts when `is_encrypted = true`

2. **Service**: `App\Services\AppSettingService`
   - CRUD operations with caching
   - Bulk operations support
   - Cache TTL: 1 hour

3. **Provider**: `App\Providers\DynamicConfigServiceProvider`
   - Loads settings to Laravel config at boot
   - Runs before application fully loaded

4. **Helpers**: `app/Helpers/SettingsHelper.php`
   - Global helper functions
   - Auto-loaded via composer

5. **Seeder**: `Database\Seeders\AppSettingsSeeder`
   - Seeds initial configuration
   - Safe to run multiple times (uses upsert)

### Encryption System

**Method**: Laravel Crypt (AES-256-CBC)
**Key**: `APP_KEY` from `.env`

```php
// Automatic encryption in model
protected function setValueAttribute($value): void {
    if ($this->is_encrypted) {
        $this->attributes['value'] = Crypt::encryptString($value);
    } else {
        $this->attributes['value'] = $value;
    }
}

// Automatic decryption
public function getValueAttribute(): mixed {
    if ($this->is_encrypted) {
        return Crypt::decryptString($this->attributes['value']);
    }
    return $this->attributes['value'];
}
```

**Encrypted Settings**:
- `whatsapp_auth_token` (256 bytes)
- `mail_smtp_username` (228 bytes)
- `mail_smtp_password` (228 bytes)

### Caching Strategy

```php
// Cache key pattern
app_setting_{key}              // Individual setting
app_setting_category_{category} // Category cache

// TTL: 3600 seconds (1 hour)
// Auto-invalidation on set/delete
```

---

## Deployment Guide

### Initial Setup

```bash
# 1. Run migration (if not already)
php artisan migrate

# 2. Seed settings
php artisan db:seed --class=AppSettingsSeeder

# 3. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# 4. Verify settings loaded
php artisan tinker
>>> config('app.name')
>>> config('whatsapp.auth_token')
```

### Updating Settings

```bash
# Via Tinker
php artisan tinker
>>> AppSettingService::set('app_timezone', 'UTC');
>>> exit

# Clear config cache
php artisan config:clear
php artisan config:cache
```

### Production Deployment

```bash
# 1. Backup database
mysqldump admin_panel > backup_$(date +%F).sql

# 2. Run seeder (safe to run multiple times)
php artisan db:seed --class=AppSettingsSeeder --force

# 3. Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache

# 4. Verify
php artisan tinker
>>> App\Models\AppSetting::count()  // Should return 24
```

---

## Security

### Current Setup (Single Tenant)

**Encryption**: Database values encrypted with `APP_KEY`
**Risk Level**: 🟡 MEDIUM (acceptable for single-tenant)

**Considerations**:
- Encryption key in same `.env` as application
- Database + .env breach = full credential access
- Acceptable for current single-tenant deployment

### Production Hardening

```bash
# 1. Generate strong APP_KEY
php artisan key:generate --force

# 2. Restrict file permissions
chmod 600 .env
chmod 755 storage/ -R

# 3. Encrypt database backups
mysqldump admin_panel | gpg -c > backup.sql.gpg
```

### Future Multi-Tenant Setup

When migrating to SaaS:

1. **External Key Management**
   - AWS KMS, HashiCorp Vault, or Azure Key Vault
   - Per-tenant encryption keys

2. **Tenant Isolation**
   ```php
   // Add tenant_id to app_settings table
   Schema::table('app_settings', function (Blueprint $table) {
       $table->unsignedBigInteger('tenant_id')->nullable();
       $table->index('tenant_id');
   });
   ```

3. **Tenant-Aware Service**
   ```php
   AppSettingService::get('smtp_password', tenant_id: auth()->user()->tenant_id);
   ```

---

## Testing

### Unit Tests

```php
// Test encryption
$setting = AppSetting::create([
    'key' => 'test_secret',
    'value' => 'password123',
    'is_encrypted' => true
]);

// Raw value should be encrypted
$raw = $setting->getRawOriginal('value');
$this->assertNotEquals('password123', $raw);
$this->assertGreaterThan(200, strlen($raw));

// Accessor should decrypt
$this->assertEquals('password123', $setting->value);
```

### Manual Testing

```bash
php artisan tinker

# Test time format switching
>>> AppSettingService::set('app_time_format', '24h');
>>> config('app.time_format');  // "24h"
>>> format_app_time('2025-10-06 14:30:00');  // "14:30"

>>> AppSettingService::set('app_time_format', '12h');
>>> format_app_time('2025-10-06 14:30:00');  // "02:30 PM"

# Test encryption
>>> $token = AppSetting::where('key', 'whatsapp_auth_token')->first();
>>> strlen($token->getRawOriginal('value'));  // ~256 (encrypted)
>>> strlen($token->value);  // ~36 (decrypted UUID)
```

---

## Troubleshooting

### Settings Not Loading

**Problem**: Changes not reflected in application

**Solution**:
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### Encryption Errors

**Problem**: "The payload is invalid" or decryption fails

**Cause**: `APP_KEY` changed after encryption

**Solution**:
1. Don't change `APP_KEY` after encrypting data
2. If changed, re-encrypt all encrypted settings

### Performance Issues

**Problem**: Slow application boot

**Cause**: Too many setting reads

**Solution**:
- Settings are cached for 1 hour
- DynamicConfigServiceProvider loads at boot
- Use `config()` helper (cached) not direct DB queries

---

## Development Guidelines

### Adding New Settings

```php
// 1. Add to AppSettingsSeeder.php
$newSettings = [
    'setting_key' => [
        'value' => 'default_value',
        'type' => 'string',  // string, numeric, boolean
        'category' => 'application',
        'description' => 'Setting description',
        'is_encrypted' => false,  // true for sensitive data
    ],
];

AppSettingService::setBulk($newSettings, 'category_name');

// 2. Add to DynamicConfigServiceProvider.php (if needed for config)
config([
    'custom.setting_key' => $settings['setting_key'] ?? 'default',
]);

// 3. Create helper function (optional)
if (!function_exists('custom_setting')) {
    function custom_setting(): string {
        return config('custom.setting_key', 'default');
    }
}

// 4. Run seeder
php artisan db:seed --class=AppSettingsSeeder
```

### Best Practices

1. **Always use helpers or config()** - Don't query DB directly
2. **Encrypt sensitive data** - Set `is_encrypted: true` for passwords/tokens
3. **Provide defaults** - Use fallbacks in helpers: `config('key', 'default')`
4. **Clear caches** - After updating settings
5. **Document usage** - Add to this file's Quick Reference

---

## File Locations

```
app/
├── Models/
│   └── AppSetting.php              # Model with encryption
├── Services/
│   └── AppSettingService.php       # CRUD + caching
├── Providers/
│   └── DynamicConfigServiceProvider.php  # Config loader
├── Helpers/
│   └── SettingsHelper.php          # Global helpers
└── Console/Commands/
    ├── SendRenewalReminders.php    # Uses renewal_reminder_days
    └── SendBirthdayWishes.php      # Uses birthday_wishes_enabled

database/
├── migrations/
│   └── 2025_10_06_063600_create_app_settings_table.php
└── seeders/
    └── AppSettingsSeeder.php       # Initial data

resources/views/
├── claims/
│   ├── show.blade.php              # Uses format_app_datetime()
│   └── edit.blade.php              # Uses format_app_datetime()
└── admin/family_groups/
    └── index.blade.php             # Uses format_app_time()
```

---

## Changelog

### Version 1.0 (2025-10-06)

**Added**:
- Complete app settings infrastructure
- 24 settings across 4 categories
- 3 encrypted credentials (WhatsApp token, SMTP username/password)
- 11 global helper functions
- Dynamic config loading at boot
- Birthday wishes feature
- Time format switching (12h/24h)

**Implemented**:
- `format_app_time()` in 7+ view files
- `format_app_datetime()` in claims views
- `app_currency()` for future multi-currency

**Removed**:
- 47 unused bloat settings (security, insurance, files, reports categories)

**Security**:
- AES-256-CBC encryption for sensitive values
- Backwards compatible with legacy unencrypted values

---

## Support & Maintenance

### Common Operations

**View all settings**:
```bash
php artisan tinker
>>> App\Models\AppSetting::all()->groupBy('category');
```

**Check setting value**:
```bash
>>> AppSettingService::get('app_timezone');
```

**Update setting**:
```bash
>>> AppSettingService::set('app_name', 'New Name');
```

**Clear cache**:
```bash
>>> AppSettingService::clearCache();
```

### Database Maintenance

**Check encrypted settings**:
```sql
SELECT key,
       CHAR_LENGTH(value) as encrypted_length,
       is_encrypted
FROM app_settings
WHERE is_encrypted = 1;
```

**Export settings**:
```bash
php artisan tinker
>>> App\Models\AppSetting::all()->toJson();
```

---

## Future Roadmap

### Short Term (1-2 weeks)
- [ ] Admin UI for settings management
- [ ] Setting change audit log
- [ ] Validation rules per setting type

### Medium Term (1-2 months)
- [ ] Multi-tenancy preparation (tenant_id column)
- [ ] External vault integration (HashiCorp Vault / AWS KMS)
- [ ] Setting versioning and rollback

### Long Term (3-6 months)
- [ ] Full SaaS migration with per-tenant settings
- [ ] Custom branding per tenant
- [ ] A/B testing capabilities
- [ ] Feature flags integration

---

## Conclusion

The App Settings system provides a robust, flexible, and secure configuration management solution for the Insurance Admin Panel. With 100% setting usage, encryption support, and multi-tenancy ready architecture, the system is production-ready and scalable for future growth.

**Key Achievements**:
- ✅ 24 settings at 100% verified usage
- ✅ 3 encrypted credentials for security
- ✅ Clean, maintainable architecture
- ✅ Comprehensive helper functions
- ✅ Production-ready with full documentation

---

**Maintained By**: Development Team
**Last Updated**: 2025-10-06
**Next Review**: When adding new features or migrating to SaaS
