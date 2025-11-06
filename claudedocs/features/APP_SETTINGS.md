# Application Settings

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Overview

Centralized application settings management with support for different data types, encryption, and categorization.

### Key Features

- **Dynamic Settings**: Runtime-configurable application settings
- **Type Support**: string, integer, boolean, json, text
- **Encryption**: Sensitive values automatically encrypted
- **Categorization**: Group settings by category
- **Active Status**: Enable/disable settings without deletion
- **Tenant-Scoped**: Each tenant has independent settings

## AppSetting Model

**File**: `app/Models/AppSetting.php`

### Attributes

- `key` (string) - Unique setting identifier (e.g., 'whatsapp_api_key')
- `value` (string, nullable) - Setting value (auto-encrypted if is_encrypted=true)
- `type` (string) - Data type: string, integer, boolean, json, text
- `category` (string) - Group: general, api, notification, payment, etc.
- `description` (string, nullable) - Human-readable description
- `is_encrypted` (boolean) - Whether value should be encrypted
- `is_active` (boolean) - Enable/disable setting

### Methods

**Value Accessors** (automatic):
```php
// Decrypts if encrypted
$setting->value; // Returns decrypted value

// JSON auto-decoded if type='json'
$jsonSetting->value; // Returns array
```

**Value Mutators** (automatic):
```php
// Encrypts if is_encrypted=true
$setting->value = 'secret_key'; // Stored encrypted

// JSON auto-encoded if type='json'
$jsonSetting->value = ['key' => 'value']; // Stored as JSON string
```

**Query Scopes**:
```php
AppSetting::active()->get(); // Only active settings
AppSetting::category('api')->get(); // By category
AppSetting::active()->category('payment')->get(); // Combined
```

## Database Schema

```sql
CREATE TABLE app_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'string',
    category VARCHAR(100) NOT NULL DEFAULT 'general',
    description TEXT NULL,
    is_encrypted BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_category (category),
    INDEX idx_active (is_active)
);
```

## Setting Categories

| Category | Purpose | Examples |
|----------|---------|----------|
| **general** | General app settings | app_name, app_logo, timezone |
| **api** | API credentials | whatsapp_api_key, payment_gateway_key |
| **notification** | Notification config | email_from, sms_provider |
| **payment** | Payment settings | razorpay_key_id, razorpay_key_secret |
| **insurance** | Insurance-specific | default_idv_percentage, ncb_rates |
| **commission** | Commission config | default_commission_rate |
| **security** | Security settings | session_timeout, max_login_attempts |
| **feature_flags** | Feature toggles | enable_whatsapp, enable_2fa |

## Usage Examples

### Example 1: Create Setting

```php
use App\Models\AppSetting;

// Create API key setting (encrypted)
AppSetting::create([
    'key' => 'whatsapp_api_key',
    'value' => 'your_secret_api_key',
    'type' => 'string',
    'category' => 'api',
    'description' => 'WhatsApp Business API Key',
    'is_encrypted' => true,
    'is_active' => true
]);

// Create JSON setting
AppSetting::create([
    'key' => 'ncb_rates',
    'value' => [
        'first_year' => 20,
        'second_year' => 25,
        'third_year' => 35,
        'fourth_year' => 45,
        'fifth_year' => 50
    ],
    'type' => 'json',
    'category' => 'insurance',
    'description' => 'NCB (No Claim Bonus) percentage rates',
    'is_encrypted' => false,
    'is_active' => true
]);

// Create boolean feature flag
AppSetting::create([
    'key' => 'enable_whatsapp',
    'value' => 'true',
    'type' => 'boolean',
    'category' => 'feature_flags',
    'description' => 'Enable WhatsApp notifications',
    'is_encrypted' => false,
    'is_active' => true
]);
```

### Example 2: Retrieve Settings

```php
// Get specific setting
$apiKey = AppSetting::where('key', 'whatsapp_api_key')
    ->where('is_active', true)
    ->first();

if ($apiKey) {
    $decryptedKey = $apiKey->value; // Automatically decrypted
}

// Get all API settings
$apiSettings = AppSetting::active()
    ->category('api')
    ->get();

foreach ($apiSettings as $setting) {
    echo "{$setting->key}: {$setting->value}\n";
}

// Get JSON setting
$ncbRates = AppSetting::where('key', 'ncb_rates')->first();
$rates = $ncbRates->value; // Returns array ['first_year' => 20, ...]
```

### Example 3: Helper Function

```php
// Create helper function (app/Helpers/SettingHelper.php)
function getSetting(string $key, $default = null)
{
    $setting = AppSetting::active()
        ->where('key', $key)
        ->first();

    return $setting ? $setting->value : $default;
}

// Usage
$whatsappEnabled = getSetting('enable_whatsapp', false);
$ncbRates = getSetting('ncb_rates', []);
$apiKey = getSetting('whatsapp_api_key');
```

### Example 4: Update Setting

```php
$setting = AppSetting::where('key', 'whatsapp_api_key')->first();

// Value automatically encrypted on save
$setting->update(['value' => 'new_api_key']);

// Or use firstOrCreate
AppSetting::updateOrCreate(
    ['key' => 'default_commission_rate'],
    [
        'value' => '2.5',
        'type' => 'string',
        'category' => 'commission',
        'is_active' => true
    ]
);
```

### Example 5: Settings Management Interface

```php
// Controller
public function index()
{
    $settings = AppSetting::orderBy('category')
        ->orderBy('key')
        ->paginate(50);

    $categories = AppSetting::distinct('category')
        ->pluck('category');

    return view('admin.settings.index', compact('settings', 'categories'));
}

public function update(Request $request, AppSetting $setting)
{
    $validated = $request->validate([
        'value' => 'required',
        'is_active' => 'sometimes|boolean'
    ]);

    // Note: Encryption handled automatically by model
    $setting->update($validated);

    return back()->with('success', 'Setting updated successfully');
}
```

### Example 6: Feature Flag Check

```php
// Middleware or helper
class FeatureMiddleware
{
    public function handle($request, Closure $next, $feature)
    {
        $enabled = getSetting("enable_{$feature}", false);

        if (!$enabled || $enabled === 'false') {
            abort(403, "Feature '{$feature}' is not enabled");
        }

        return $next($request);
    }
}

// Usage in routes
Route::post('/quotation/{quotation}/send-whatsapp', [QuotationController::class, 'sendWhatsApp'])
    ->middleware('feature:whatsapp');
```

### Example 7: Cached Settings

```php
use Illuminate\Support\Facades\Cache;

class SettingService
{
    public function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = AppSetting::active()->where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public function set(string $key, $value): void
    {
        $setting = AppSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting.{$key}");
    }

    public function flush(): void
    {
        Cache::tags(['settings'])->flush();
    }
}

// Usage
$settingService = app(SettingService::class);
$apiKey = $settingService->get('whatsapp_api_key');
$settingService->set('enable_sms', 'true');
```

## Security Considerations

1. **Always Encrypt Sensitive Data**: API keys, passwords, tokens
2. **Validate Setting Types**: Ensure type matches expected value
3. **Restrict Access**: Settings management should be admin-only
4. **Audit Changes**: Log all setting modifications
5. **Backup Settings**: Include in database backups
6. **Environment Priority**: .env values override DB settings where applicable

## Best Practices

1. **Use Descriptive Keys**: `whatsapp_api_key` not `wa_key`
2. **Categorize Properly**: Makes management easier
3. **Provide Descriptions**: Help future developers understand purpose
4. **Use Type System**: Leverage type casting for data integrity
5. **Cache Frequently Used Settings**: Reduce database queries
6. **Version Control Seeder**: Track default settings in seeders
7. **Document Settings**: Maintain list of available settings

## Example Seeder

```php
// database/seeders/AppSettingSeeder.php
public function run()
{
    $settings = [
        [
            'key' => 'whatsapp_api_key',
            'value' => env('WHATSAPP_API_KEY'),
            'type' => 'string',
            'category' => 'api',
            'description' => 'WhatsApp Business API Key',
            'is_encrypted' => true,
            'is_active' => true
        ],
        [
            'key' => 'default_commission_rate',
            'value' => '2.5',
            'type' => 'string',
            'category' => 'commission',
            'description' => 'Default commission rate percentage',
            'is_encrypted' => false,
            'is_active' => true
        ],
        // ... more settings
    ];

    foreach ($settings as $setting) {
        AppSetting::updateOrCreate(
            ['key' => $setting['key']],
            $setting
        );
    }
}
```

## Related Documentation

- **[ENVIRONMENT_CONFIGURATION.md](../setup/ENVIRONMENT_CONFIGURATION.md)** - .env configuration
- **[SECURITY_SETTINGS.md](SECURITY_SETTINGS.md)** - Security configuration
- **[DATABASE_SCHEMA.md](../core/DATABASE_SCHEMA.md)** - Schema details

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
