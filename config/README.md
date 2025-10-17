# Configuration Files

## Dynamic Configuration System

This project uses a **hybrid configuration approach**:

### 📁 Static Config Files (Default Values)
- `config/app.php` - Core Laravel application settings
- `config/whatsapp.php` - WhatsApp API defaults
- `config/notifications.php` - Notification defaults
- `config/mail.php` - Mail configuration defaults

### 🗄️ Database-Driven Settings (Runtime Values)
Many configuration values are **stored in the database** (`app_settings` table) and override static config values at runtime via `DynamicConfigServiceProvider`.

## How It Works

### 1. Service Provider Loads Settings
```php
// app/Providers/DynamicConfigServiceProvider.php
// Runs on every request, loads settings from database into config
```

### 2. Config Files Provide IDE Support
```php
// config/whatsapp.php exists for IDE autocomplete
// But actual values come from app_settings table
```

### 3. Access Settings
```php
// Option 1: Via config() - works because of DynamicConfigServiceProvider
config('whatsapp.sender_id')

// Option 2: Via helper functions (recommended)
company_website()
is_whatsapp_notification_enabled()

// Option 3: Direct AppSettingService (for advanced use)
AppSettingService::get('whatsapp_sender_id', 'whatsapp')
```

## 📊 Database-Managed Settings

### Application Settings
- `app.name` → `app_settings.app_name`
- `app.timezone` → `app_settings.app_timezone`
- `app.currency` → `app_settings.app_currency`
- `app.date_format` → `app_settings.app_date_format`

### Company Settings
- Accessible via helper functions:
  - `company_name()`
  - `company_advisor_name()`
  - `company_website()`
  - `company_phone()`
  - `company_tagline()`

### WhatsApp Settings
- `config('whatsapp.sender_id')` → `app_settings.whatsapp_sender_id`
- `config('whatsapp.base_url')` → `app_settings.whatsapp_base_url`
- `config('whatsapp.auth_token')` → `app_settings.whatsapp_auth_token`

### Notification Settings
- `config('notifications.email_enabled')` → `app_settings.email_notifications_enabled`
- `config('notifications.whatsapp_enabled')` → `app_settings.whatsapp_notifications_enabled`
- `config('notifications.birthday_wishes_enabled')` → `app_settings.birthday_wishes_enabled`

### Mail Settings
- `config('mail.from.address')` → `app_settings.mail_from_address`
- `config('mail.from.name')` → `app_settings.mail_from_name`
- SMTP configuration also loaded from database

## 🔧 Admin Management

All database-driven settings can be managed via:

**Admin Panel**: `/app-settings`

- Filter by category (application, company, whatsapp, mail, notifications)
- Edit values without code changes
- Changes take effect immediately (config is loaded on each request)
- No deployment required

## ⚠️ Important Notes

### For Developers

1. **Config files exist for IDE support** - Your IDE can autocomplete `config('whatsapp.sender_id')` because `config/whatsapp.php` exists

2. **Actual values come from database** - The config files provide defaults, but `DynamicConfigServiceProvider` overrides them with database values

3. **No confusion** - If your IDE shows "config not found", it's wrong. The config IS loaded dynamically.

4. **Helper functions are cleaner** - Instead of `config('notifications.whatsapp_enabled')`, use `is_whatsapp_notification_enabled()`

### For Admins

1. **Edit via UI** - Don't edit config files for dynamic settings, use `/app-settings`

2. **Instant changes** - Database changes take effect immediately, no cache clearing needed

3. **Secure values** - Sensitive values (like auth tokens) can be marked as encrypted

## 📝 Adding New Settings

### 1. Add to AppSettingsSeeder
```php
'new_setting_key' => [
    'value' => 'default value',
    'type' => 'string',
    'category' => 'whatsapp',
    'description' => 'Description of setting',
    'is_encrypted' => false,
]
```

### 2. (Optional) Add Helper Function
```php
// app/Helpers/SettingsHelper.php
function new_setting_key(): string {
    return app(\App\Services\AppSettingService::class)
        ->get('new_setting_key', 'whatsapp', 'default');
}
```

### 3. (Optional) Update Config File for IDE Support
```php
// config/whatsapp.php
'new_setting_key' => env('NEW_SETTING_KEY', 'default'),
```

### 4. (Optional) Load in DynamicConfigServiceProvider
```php
// app/Providers/DynamicConfigServiceProvider.php
config([
    'whatsapp.new_setting_key' => $settings['new_setting_key'] ?? config('whatsapp.new_setting_key'),
]);
```

## 🎯 Best Practices

✅ **DO:**
- Use helper functions when available
- Manage dynamic settings via Admin UI
- Add config files for IDE support
- Document which settings are database-driven

❌ **DON'T:**
- Edit config files expecting changes (for database-driven settings)
- Hardcode values that should be configurable
- Skip DynamicConfigServiceProvider when adding new settings
- Commit sensitive values to git

## 📚 Related Files

- `app/Services/AppSettingService.php` - Setting management service
- `app/Providers/DynamicConfigServiceProvider.php` - Config loading
- `app/Helpers/SettingsHelper.php` - Helper functions
- `database/seeders/AppSettingsSeeder.php` - Default settings
- `app/Http/Controllers/AppSettingController.php` - Admin UI controller
