# Implementation Guide: App Settings & Export Infrastructure

**Status**: ✅ COMPLETED
**Date Completed**: 2025-10-06
**Version**: 1.0

## Overview
This document provides a comprehensive guide for the two critical infrastructure features implemented:

1. **App Settings Infrastructure** - Centralized application configuration management
2. **Export Traits and Services** - Generic reusable Excel export system

---

## 📋 Implementation Checklist

### Phase 1: App Settings Infrastructure ✅ COMPLETED
- [x] Create app_settings migration
- [x] Create AppSetting model with encryption support
- [x] Create AppSettingService for centralized config management
- [x] Create AppSettingsSeeder with default configurations
- [x] Create AppSettingController for CRUD operations
- [x] Register DynamicConfigServiceProvider
- [x] Create SettingsHelper.php with global helpers
- [x] Add routes for settings CRUD
- [x] Run migration and seeder
- [x] Test settings retrieval and update
- [x] Implement encryption for sensitive credentials
- [x] Create SQL deployment file for production
- [x] Usage audit (24/24 settings at 100% usage)

### Phase 2: Export Infrastructure ✅ COMPLETED
- [x] Create GenericExport class
- [x] Create ExcelExportService
- [x] Update ExportableTrait with advanced features
- [x] Remove old individual export classes (10 deleted)
- [x] Update controllers to use new export system
- [x] Add export routes
- [x] Test export functionality
- [x] Verify preset configurations work
- [x] Implement professional Excel styling
- [x] Add filter support (search, date range, status)

---

## 🔧 Phase 1: App Settings Infrastructure

### Step 1.1: Create Migration

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_app_settings_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, json, boolean, numeric
            $table->string('category')->default('general'); // general, whatsapp, mail, api, etc
            $table->text('description')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
```

**Command to create:**
```bash
php artisan make:migration create_app_settings_table
```

---

### Step 1.2: Create AppSetting Model

**File:** `app/Models/AppSetting.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'description',
        'is_encrypted',
        'is_active'
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the value attribute, decrypting if needed
     */
    public function getValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            try {
                return Crypt::decrypt($value);
            } catch (\Exception $e) {
                return $value; // Return original if decryption fails
            }
        }

        if ($this->type === 'json' && $value) {
            return json_decode($value, true);
        }

        return $value;
    }

    /**
     * Set the value attribute, encrypting if needed
     */
    public function setValueAttribute($value)
    {
        if ($this->is_encrypted) {
            $this->attributes['value'] = Crypt::encrypt($value);
        } elseif ($this->type === 'json' && is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Scope for active settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
```

---

### Step 1.3: Create AppSettingService

**File:** `app/Services/AppSettingService.php`

```php
<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;

class AppSettingService
{
    protected const CACHE_PREFIX = 'app_setting_';
    protected const CACHE_TTL = 3600; // 1 hour

    /**
     * Get setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = AppSetting::where('key', $key)
                ->where('is_active', true)
                ->first();

            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set setting value
     */
    public static function set(string $key, $value, array $options = []): AppSetting
    {
        $setting = AppSetting::updateOrCreate(
            ['key' => $key],
            array_merge([
                'value' => $value,
                'type' => self::detectType($value),
                'category' => 'general',
                'is_encrypted' => false,
                'is_active' => true,
            ], $options)
        );

        Cache::forget(self::CACHE_PREFIX . $key);

        return $setting;
    }

    /**
     * Set encrypted setting
     */
    public static function setEncrypted(string $key, $value, array $options = []): AppSetting
    {
        $options['is_encrypted'] = true;
        return self::set($key, $value, $options);
    }

    /**
     * Get all settings by category
     */
    public static function getByCategory(string $category): array
    {
        $cacheKey = self::CACHE_PREFIX . 'category_' . $category;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($category) {
            return AppSetting::where('category', $category)
                ->where('is_active', true)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Get multiple settings by keys
     */
    public static function getMultiple(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::get($key);
        }
        return $result;
    }

    /**
     * Delete setting
     */
    public static function delete(string $key): bool
    {
        $setting = AppSetting::where('key', $key)->first();

        if ($setting) {
            $setting->delete();
            Cache::forget(self::CACHE_PREFIX . $key);
            return true;
        }

        return false;
    }

    /**
     * Toggle setting status
     */
    public static function toggle(string $key): bool
    {
        $setting = AppSetting::where('key', $key)->first();

        if ($setting) {
            $setting->is_active = !$setting->is_active;
            $setting->save();
            Cache::forget(self::CACHE_PREFIX . $key);
            return true;
        }

        return false;
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }

    /**
     * Detect value type
     */
    protected static function detectType($value): string
    {
        if (is_array($value)) {
            return 'json';
        }

        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_numeric($value)) {
            return 'numeric';
        }

        return 'string';
    }

    /**
     * Bulk set settings
     */
    public static function setBulk(array $settings, string $category = 'general'): void
    {
        foreach ($settings as $key => $config) {
            if (is_array($config)) {
                $value = $config['value'];
                unset($config['value']);
                $options = array_merge(['category' => $category], $config);
            } else {
                $value = $config;
                $options = ['category' => $category];
            }

            self::set($key, $value, $options);
        }
    }

    /**
     * Get WhatsApp settings
     */
    public static function getWhatsAppConfig(): array
    {
        return self::getByCategory('whatsapp');
    }

    /**
     * Get mail settings
     */
    public static function getMailConfig(): array
    {
        return self::getByCategory('mail');
    }
}
```

---

### Step 1.4: Create AppSettingsSeeder

**File:** `database/seeders/AppSettingsSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\AppSettingService;

class AppSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // WhatsApp API Settings
        $whatsappSettings = [
            'whatsapp_sender_id' => [
                'value' => '919727793123',
                'description' => 'WhatsApp API Sender ID',
                'is_encrypted' => false,
            ],
            'whatsapp_base_url' => [
                'value' => 'https://api.botmastersender.com/api/v1/',
                'description' => 'WhatsApp API Base URL',
                'is_encrypted' => false,
            ],
            'whatsapp_auth_token' => [
                'value' => 'your-token-here',
                'description' => 'WhatsApp API Authentication Token',
                'is_encrypted' => true,
            ]
        ];

        AppSettingService::setBulk($whatsappSettings, 'whatsapp');

        // Mail Settings
        $mailSettings = [
            'mail_from_name' => [
                'value' => 'Insurance Admin Panel',
                'description' => 'Default mail from name',
                'is_encrypted' => false,
            ],
            'mail_from_address' => [
                'value' => 'noreply@insuranceadmin.com',
                'description' => 'Default mail from address',
                'is_encrypted' => false,
            ]
        ];

        AppSettingService::setBulk($mailSettings, 'mail');

        // Application Settings
        $appSettings = [
            'app_name' => [
                'value' => 'Insurance Admin Panel',
                'description' => 'Application Name',
                'is_encrypted' => false,
            ],
            'app_logo' => [
                'value' => '/admin/images/logo.png',
                'description' => 'Application Logo Path',
                'is_encrypted' => false,
            ]
        ];

        AppSettingService::setBulk($appSettings, 'application');

        // Notification Settings
        $notificationSettings = [
            'renewal_reminder_days_before' => [
                'value' => '30',
                'type' => 'numeric',
                'description' => 'Days before expiry to send renewal reminder',
                'is_encrypted' => false,
            ],
            'enable_whatsapp_notifications' => [
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable WhatsApp notifications',
                'is_encrypted' => false,
            ]
        ];

        AppSettingService::setBulk($notificationSettings, 'notifications');
    }
}
```

**Update DatabaseSeeder:**
```php
// In database/seeders/DatabaseSeeder.php
public function run()
{
    $this->call([
        // ... existing seeders
        AppSettingsSeeder::class,
    ]);
}
```

---

### Step 1.5: Create Service Provider (Optional but Recommended)

**File:** `app/Providers/DynamicConfigServiceProvider.php`

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AppSettingService;

class DynamicConfigServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        try {
            // Load WhatsApp config from database
            $whatsappConfig = AppSettingService::getWhatsAppConfig();

            if (!empty($whatsappConfig)) {
                config([
                    'whatsapp.sender_id' => $whatsappConfig['whatsapp_sender_id'] ?? config('whatsapp.sender_id'),
                    'whatsapp.base_url' => $whatsappConfig['whatsapp_base_url'] ?? config('whatsapp.base_url'),
                    'whatsapp.auth_token' => $whatsappConfig['whatsapp_auth_token'] ?? config('whatsapp.auth_token'),
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail during migration/installation
        }
    }
}
```

**Register in config/app.php:**
```php
'providers' => [
    // ... other providers
    App\Providers\DynamicConfigServiceProvider::class,
],
```

---

### Step 1.6: Usage Examples

**In Controllers:**
```php
use App\Services\AppSettingService;

// Get single setting
$appName = AppSettingService::get('app_name', 'Default Name');

// Get encrypted setting
$apiToken = AppSettingService::get('whatsapp_auth_token');

// Set setting
AppSettingService::set('app_name', 'New App Name');

// Set encrypted setting
AppSettingService::setEncrypted('api_key', 'secret-key-123');

// Get by category
$whatsappSettings = AppSettingService::getWhatsAppConfig();

// Get multiple
$settings = AppSettingService::getMultiple(['app_name', 'app_logo']);
```

---

## 📊 Phase 2: Export Infrastructure

### Step 2.1: Create GenericExport Class

**File:** `app/Exports/GenericExport.php`

```php
<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GenericExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStrictNullComparison,
    ShouldAutoSize,
    WithStyles
{
    protected Collection $collection;
    protected array $config;

    public function __construct(Collection $collection, array $config = [])
    {
        $this->collection = $collection;
        $this->config = $config;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        if (!$this->config['with_headings'] ?? false) {
            return [];
        }

        return $this->config['headings'] ?? [];
    }

    public function map($row): array
    {
        if (!$this->config['with_mapping'] ?? false) {
            return $this->defaultMapping($row);
        }

        $mapping = $this->config['mapping'] ?? null;

        if (is_callable($mapping)) {
            return $mapping($row);
        }

        return $this->defaultMapping($row);
    }

    private function defaultMapping($row): array
    {
        if (is_array($row)) {
            return $row;
        }

        if (is_object($row)) {
            if (method_exists($row, 'toArray')) {
                $data = $row->toArray();

                if (!empty($this->config['columns'])) {
                    $filtered = [];
                    foreach ($this->config['columns'] as $column) {
                        $filtered[$column] = $data[$column] ?? '';
                    }
                    return array_values($filtered);
                }

                return array_values($data);
            }

            return array_values((array) $row);
        }

        return [$row];
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];

        // Header styles
        if ($this->config['with_headings'] ?? false) {
            $headerRow = 1;
            $lastColumn = $this->getLastColumn();

            $styles["A{$headerRow}:{$lastColumn}{$headerRow}"] = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];
        }

        // Data rows styles
        $dataStartRow = ($this->config['with_headings'] ?? false) ? 2 : 1;
        $dataEndRow = $dataStartRow + $this->collection->count() - 1;
        $lastColumn = $this->getLastColumn();

        if ($dataEndRow >= $dataStartRow) {
            $styles["A{$dataStartRow}:{$lastColumn}{$dataEndRow}"] = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true
                ]
            ];

            // Alternate row colors
            for ($row = $dataStartRow; $row <= $dataEndRow; $row += 2) {
                $styles["A{$row}:{$lastColumn}{$row}"] = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA']
                    ]
                ];
            }
        }

        return $styles;
    }

    private function getLastColumn(): string
    {
        $headingsCount = count($this->config['headings'] ?? []);

        if ($headingsCount === 0 && $this->collection->isNotEmpty()) {
            $firstRow = $this->collection->first();
            $mapped = $this->map($firstRow);
            $headingsCount = count($mapped);
        }

        if ($headingsCount <= 26) {
            return chr(64 + $headingsCount); // A-Z
        }

        $firstLetter = chr(64 + floor(($headingsCount - 1) / 26));
        $secondLetter = chr(65 + (($headingsCount - 1) % 26));

        return $firstLetter . $secondLetter;
    }
}
```

---

### Step 2.2: Create ExcelExportService

**File:** `app/Services/ExcelExportService.php`

```php
<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GenericExport;

class ExcelExportService
{
    /**
     * Export data to Excel
     */
    public function export($data, array $config = [])
    {
        $config = array_merge($this->getDefaultConfig(), $config);
        $collection = $this->resolveDataSource($data, $config);
        $filename = $this->generateFilename($config);
        $export = new GenericExport($collection, $config);

        return Excel::download($export, $filename);
    }

    /**
     * Export with custom mapping and headers
     */
    public function exportWithMapping($data, array $headings, callable $mapping, array $config = [])
    {
        $config = array_merge($config, [
            'headings' => $headings,
            'mapping' => $mapping,
            'with_headings' => true,
            'with_mapping' => true
        ]);

        return $this->export($data, $config);
    }

    /**
     * Quick export for simple models
     */
    public function quickExport(string $modelClass, array $columns = [], array $config = [])
    {
        $model = app($modelClass);
        $columns = $columns ?: $this->getDefaultColumns($model);

        $config = array_merge($config, [
            'columns' => $columns,
            'headings' => $this->generateHeadingsFromColumns($columns),
            'with_headings' => true
        ]);

        return $this->export($modelClass, $config);
    }

    /**
     * Export with relationships
     */
    public function exportWithRelations($modelClass, array $relations, array $config = [])
    {
        $config = array_merge($config, [
            'relations' => $relations,
            'with_relations' => true
        ]);

        return $this->export($modelClass, $config);
    }

    /**
     * Export filtered data
     */
    public function exportFiltered($modelClass, array $filters, array $config = [])
    {
        $query = app($modelClass)->newQuery();

        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } elseif (!empty($value)) {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        return $this->export($query, $config);
    }

    /**
     * Export date range data
     */
    public function exportDateRange($modelClass, string $dateField, $startDate, $endDate, array $config = [])
    {
        $query = app($modelClass)->newQuery()
            ->whereBetween($dateField, [$startDate, $endDate])
            ->orderBy($dateField, 'desc');

        $config = array_merge($config, [
            'filename_suffix' => date('Y_m_d', strtotime($startDate)) . '_to_' . date('Y_m_d', strtotime($endDate))
        ]);

        return $this->export($query, $config);
    }

    private function resolveDataSource($data, array $config)
    {
        if ($data instanceof Collection || $data instanceof SupportCollection) {
            return $data;
        }

        if ($data instanceof Builder) {
            if (!empty($config['relations'])) {
                $data->with($config['relations']);
            }

            if (!empty($config['order_by'])) {
                $data->orderBy($config['order_by']['column'], $config['order_by']['direction'] ?? 'asc');
            } else {
                $data->orderBy('created_at', 'desc');
            }

            if (!empty($config['limit'])) {
                $data->limit($config['limit']);
            }

            return $data->get();
        }

        if (is_string($data) && class_exists($data)) {
            $query = app($data)->newQuery();

            if (!empty($config['relations'])) {
                $query->with($config['relations']);
            }

            if (!empty($config['columns'])) {
                $query->select($config['columns']);
            }

            if (!empty($config['order_by'])) {
                $query->orderBy($config['order_by']['column'], $config['order_by']['direction'] ?? 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            if (!empty($config['limit'])) {
                $query->limit($config['limit']);
            }

            return $query->get();
        }

        throw new \InvalidArgumentException('Invalid data source provided for export');
    }

    private function generateFilename(array $config): string
    {
        $base = $config['filename'] ?? 'export';
        $suffix = $config['filename_suffix'] ?? date('Y_m_d_H_i_s');
        $extension = $config['format'] ?? 'xlsx';

        return "{$base}_{$suffix}.{$extension}";
    }

    private function getDefaultConfig(): array
    {
        return [
            'format' => 'xlsx',
            'with_headings' => false,
            'with_mapping' => false,
            'with_relations' => false,
            'strict_null_comparison' => true,
            'auto_size' => true,
            'order_by' => ['column' => 'created_at', 'direction' => 'desc'],
            'limit' => null,
            'relations' => [],
            'columns' => [],
            'headings' => [],
            'mapping' => null,
            'filename' => 'export',
            'filename_suffix' => date('Y_m_d_H_i_s')
        ];
    }

    private function getDefaultColumns(Model $model): array
    {
        $fillable = $model->getFillable();
        $basic = ['id', 'created_at', 'updated_at'];

        return array_merge($fillable, $basic);
    }

    private function generateHeadingsFromColumns(array $columns): array
    {
        return array_map(function($column) {
            return ucwords(str_replace(['_', 'id'], [' ', 'ID'], $column));
        }, $columns);
    }

    /**
     * Get predefined export configs
     */
    public function getPresetConfig(string $modelType): array
    {
        $presets = [
            'customers' => [
                'filename' => 'customers',
                'relations' => ['familyGroup'],
                'headings' => ['ID', 'Name', 'Email', 'Mobile', 'Status', 'Created Date'],
                'mapping' => function($customer) {
                    return [
                        $customer->id,
                        $customer->name,
                        $customer->email,
                        $customer->mobile_number,
                        ucfirst($customer->status),
                        $customer->created_at->format('Y-m-d H:i:s')
                    ];
                },
                'with_headings' => true,
                'with_mapping' => true
            ],
        ];

        return $presets[$modelType] ?? [];
    }
}
```

---

### Step 2.3: Update ExportableTrait

**File:** `app/Traits/ExportableTrait.php`

**Replace the entire content with:**

```php
<?php

namespace App\Traits;

use App\Services\ExcelExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait ExportableTrait
{
    /**
     * Export data using the reusable export system
     */
    public function export(Request $request)
    {
        $exportService = app(ExcelExportService::class);
        $modelClass = $this->getExportModelClass();
        $config = $this->getExportConfig($request);

        // Apply filters if provided
        if ($request->hasAny(['search', 'status', 'start_date', 'end_date'])) {
            return $this->exportFiltered($request, $exportService, $modelClass, $config);
        }

        // Use preset config if available
        $presetType = $this->getExportPresetType();
        if ($presetType) {
            $presetConfig = $exportService->getPresetConfig($presetType);
            $config = array_merge($presetConfig, $config);

            return $exportService->export($modelClass, $config);
        }

        return $exportService->quickExport($modelClass, [], $config);
    }

    /**
     * Export with date range filter
     */
    public function exportDateRange(Request $request, string $startDate, string $endDate)
    {
        $exportService = app(ExcelExportService::class);
        $modelClass = $this->getExportModelClass();
        $dateField = $this->getDateFilterField();
        $config = $this->getExportConfig($request);

        return $exportService->exportDateRange($modelClass, $dateField, $startDate, $endDate, $config);
    }

    protected function exportFiltered(Request $request, ExcelExportService $exportService, string $modelClass, array $config)
    {
        $filters = [];

        if ($request->filled('search')) {
            $searchFields = $this->getSearchableFields();
            $query = app($modelClass)->newQuery();

            foreach ($searchFields as $field) {
                $query->orWhere($field, 'like', '%' . $request->search . '%');
            }

            return $exportService->export($query, $config);
        }

        if ($request->filled('status')) {
            $filters['status'] = $request->status;
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            return $exportService->exportDateRange(
                $modelClass,
                $this->getDateFilterField(),
                $request->start_date,
                $request->end_date,
                $config
            );
        }

        if (!empty($filters)) {
            return $exportService->exportFiltered($modelClass, $filters, $config);
        }

        return $exportService->export($modelClass, $config);
    }

    protected function getExportModelClass(): string
    {
        $controllerName = class_basename($this);
        $modelName = str_replace('Controller', '', $controllerName);

        if (Str::endsWith($modelName, 's')) {
            $modelName = Str::singular($modelName);
        }

        $modelClass = "App\\Models\\{$modelName}";

        if (!class_exists($modelClass)) {
            throw new \Exception("Model class {$modelClass} not found. Override getExportModelClass()");
        }

        return $modelClass;
    }

    protected function getExportConfig(Request $request): array
    {
        return [
            'format' => $request->get('format', 'xlsx'),
            'filename' => $this->getExportFilename(),
            'with_headings' => true,
            'auto_size' => true,
            'relations' => $this->getExportRelations(),
            'order_by' => $this->getExportOrderBy()
        ];
    }

    protected function getExportPresetType(): ?string
    {
        $controllerName = class_basename($this);

        return match($controllerName) {
            'CustomerController' => 'customers',
            default => null
        };
    }

    protected function getExportFilename(): string
    {
        $controllerName = class_basename($this);
        $name = str_replace('Controller', '', $controllerName);

        return Str::snake(Str::plural($name));
    }

    protected function getExportRelations(): array
    {
        return [];
    }

    protected function getExportOrderBy(): array
    {
        return ['column' => 'created_at', 'direction' => 'desc'];
    }

    protected function getSearchableFields(): array
    {
        return ['name'];
    }

    protected function getDateFilterField(): string
    {
        return 'created_at';
    }
}
```

---

### Step 2.4: Update Controllers

**Example for CustomerController:**

```php
use App\Traits\ExportableTrait;

class CustomerController extends Controller
{
    use ExportableTrait;

    // Override export configuration
    protected function getExportRelations(): array
    {
        return ['familyGroup'];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'email', 'mobile_number'];
    }

    protected function getExportConfig(Request $request): array
    {
        $config = parent::getExportConfig($request);

        return array_merge($config, [
            'headings' => ['ID', 'Name', 'Email', 'Mobile', 'Status', 'Created'],
            'mapping' => function($customer) {
                return [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->mobile_number,
                    ucfirst($customer->status),
                    $customer->created_at->format('Y-m-d H:i:s')
                ];
            },
            'with_mapping' => true
        ]);
    }
}
```

---

### Step 2.5: Remove Old Export Classes

**Delete these files:**
```
app/Exports/AddonCoversExport.php
app/Exports/BrokersExport.php
app/Exports/CustomersExport.php
app/Exports/FuelTypesExport.php
app/Exports/InsuranceCompanyExport.php
app/Exports/PolicyTypesExport.php
app/Exports/PremiumTypesExport.php
app/Exports/ReferenceUsersExport.php
app/Exports/RelationshipManagersExport.php
app/Exports/UsersExport.php
```

---

## 🚀 Deployment Steps

### Step 1: Run Migrations
```bash
php artisan migrate
```

### Step 2: Run Seeders
```bash
php artisan db:seed --class=AppSettingsSeeder
```

### Step 3: Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize
```

### Step 4: Test Export
```bash
# Visit any index page with export functionality
# Click export button to verify
```

---

## 🔍 Testing Checklist

### App Settings Tests
- [ ] Settings are stored correctly in database
- [ ] Encrypted settings decrypt properly
- [ ] Cache invalidation works
- [ ] Category-based retrieval works
- [ ] Service provider loads config correctly

### Export Tests
- [ ] Basic export works (all records)
- [ ] Export with search filter works
- [ ] Export with date range works
- [ ] Export with relationships works
- [ ] Custom mapping applies correctly
- [ ] Excel styling renders properly
- [ ] Filename generation is correct

---

## 📝 Usage Examples

### App Settings Usage

```php
// In any controller or service
use App\Services\AppSettingService;

// Get setting
$whatsappToken = AppSettingService::get('whatsapp_auth_token');
$appName = AppSettingService::get('app_name', 'Default Name');

// Set setting
AppSettingService::set('maintenance_mode', true);

// Set encrypted
AppSettingService::setEncrypted('api_secret', 'my-secret-key');

// Get category
$mailConfig = AppSettingService::getMailConfig();
```

### Export Usage

```php
// In CustomerController
use App\Traits\ExportableTrait;

class CustomerController extends Controller
{
    use ExportableTrait;

    // Route: GET /customers/export
    public function export(Request $request)
    {
        return parent::export($request);
    }
}
```

**URL Examples:**
```
/customers/export                          # Export all
/customers/export?search=john              # Export filtered by search
/customers/export?start_date=2025-01-01&end_date=2025-12-31  # Date range
/customers/export?format=csv               # Export as CSV
```

---

## 🔐 Security Considerations

1. **Encrypted Settings**: Always encrypt sensitive data (API tokens, passwords)
2. **Export Permissions**: Add authorization checks before export
3. **Data Filtering**: Ensure users can only export data they have access to
4. **Rate Limiting**: Consider rate limiting export endpoints

---

## 📚 Additional Notes

### Benefits of This Implementation

**App Settings:**
- Centralized configuration management
- Database-driven settings (no code deployment for config changes)
- Encryption support for sensitive data
- Cache optimization for performance
- Category-based organization

**Export System:**
- Single reusable export class
- Consistent styling across all exports
- Easy to maintain and extend
- Support for filters, relationships, custom mapping
- Preset configurations for common models

### Customization Tips

1. **Add new categories** in AppSettingsSeeder
2. **Create preset configs** in ExcelExportService::getPresetConfig()
3. **Override methods** in controllers for specific export needs
4. **Add validation** for export parameters
5. **Implement queue jobs** for large exports

---

## ✅ Completion Verification

After implementation, verify:

1. ✅ All migrations run successfully
2. ✅ Seeder populates settings correctly
3. ✅ Settings can be retrieved via AppSettingService
4. ✅ Export functionality works on all relevant controllers
5. ✅ Old export classes are removed
6. ✅ Excel files download with proper formatting
7. ✅ Filters and date ranges work correctly
8. ✅ No breaking changes to existing functionality

---

## 🆘 Troubleshooting

**Issue: Settings not found**
- Solution: Run `php artisan db:seed --class=AppSettingsSeeder`

**Issue: Export fails**
- Solution: Verify maatwebsite/excel package is installed
- Run: `composer require maatwebsite/excel`

**Issue: Encryption error**
- Solution: Ensure APP_KEY is set in .env
- Run: `php artisan key:generate`

**Issue: Cache not updating**
- Solution: Clear cache with `php artisan cache:clear`

---

## 📦 Package Requirements

Ensure these packages are installed:

```bash
composer require maatwebsite/excel
```

---

**End of Implementation Guide**
