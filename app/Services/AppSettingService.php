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
        // Return default if not in tenant context (e.g., central admin)
        if (!tenancy()->initialized) {
            return $default;
        }

        $cacheKey = self::CACHE_PREFIX.$key;

        return Cache::remember($cacheKey, self::CACHE_TTL, static function () use ($key, $default) {
            $setting = AppSetting::query()->where('key', $key)
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
        $isEncrypted = $options['is_encrypted'] ?? false;

        // Find or create the setting
        $setting = AppSetting::query()->firstOrNew(['key' => $key]);

        // Set metadata first (before value, so mutator can check is_encrypted)
        $setting->type = $options['type'] ?? self::detectType($value);
        $setting->category = $options['category'] ?? 'general';
        $setting->description = $options['description'] ?? null;
        $setting->is_encrypted = $isEncrypted;
        $setting->is_active = $options['is_active'] ?? true;

        // Now set value - mutator will encrypt if is_encrypted is true
        $setting->value = $value;

        // Save
        $setting->save();

        Cache::forget(self::CACHE_PREFIX.$key);

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
        // Return empty array if not in tenant context (e.g., central admin)
        if (!tenancy()->initialized) {
            return [];
        }

        $cacheKey = self::CACHE_PREFIX.'category_'.$category;

        return Cache::remember($cacheKey, self::CACHE_TTL, static function () use ($category): array {
            $settings = AppSetting::query()->where('category', $category)
                ->where('is_active', true)
                ->get();

            $result = [];
            foreach ($settings as $setting) {
                // Access via model attribute to trigger getValueAttribute() for decryption
                $result[$setting->key] = $setting->value;
            }

            return $result;
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
        $setting = AppSetting::query()->where('key', $key)->first();

        if ($setting) {
            $setting->delete();
            Cache::forget(self::CACHE_PREFIX.$key);

            return true;
        }

        return false;
    }

    /**
     * Toggle setting status
     */
    public static function toggle(string $key): bool
    {
        $setting = AppSetting::query()->where('key', $key)->first();

        if ($setting) {
            $setting->is_active = ! $setting->is_active;
            $setting->save();
            Cache::forget(self::CACHE_PREFIX.$key);

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
