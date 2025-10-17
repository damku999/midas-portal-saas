<?php

namespace App\Services;

use App\Models\Broker;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\FuelType;
use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use App\Models\PremiumType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    // 1 hour - General caching

    private const LONG_TTL = 7200; // 2 hours - Lookup data (rarely changes)

    private const QUERY_TTL = 1800; // 30 minutes - Query results

    private const REPORT_TTL = 900; // 15 minutes - Report data (frequent updates)

    private const STATISTICS_TTL = 300; // 5 minutes - Real-time statistics

    private const INSURANCE_COMPANIES_KEY = 'insurance_companies_active';

    private const BROKERS_KEY = 'brokers_active';

    private const POLICY_TYPES_KEY = 'policy_types_active';

    private const PREMIUM_TYPES_KEY = 'premium_types_active';

    private const FUEL_TYPES_KEY = 'fuel_types_active';

    private const USERS_KEY = 'users_active';

    public function getInsuranceCompanies(): Collection
    {
        $data = Cache::store('lookups')->remember(self::INSURANCE_COMPANIES_KEY, self::LONG_TTL, fn () => InsuranceCompany::query()->where('status', 1)->get());

        // Ensure we always return a Collection (file cache might return array)
        if (is_array($data)) {
            return collect($data)->map(fn ($item) => is_array($item) ? (object) $item : $item);
        }

        return $data;
    }

    public function getBrokers(): Collection
    {
        $data = Cache::store('lookups')->remember(self::BROKERS_KEY, self::LONG_TTL, fn () => Broker::query()->where('status', 1)->get());

        // Ensure we always return a Collection (file cache might return array)
        if (is_array($data)) {
            return collect($data)->map(fn ($item) => is_array($item) ? (object) $item : $item);
        }

        return $data;
    }

    public function getPolicyTypes(): Collection
    {
        $data = Cache::store('lookups')->remember(self::POLICY_TYPES_KEY, self::LONG_TTL, fn () => PolicyType::query()->where('status', 1)->get());

        // Ensure we always return a Collection (file cache might return array)
        if (is_array($data)) {
            return collect($data)->map(fn ($item) => is_array($item) ? (object) $item : $item);
        }

        return $data;
    }

    public function getPremiumTypes(): Collection
    {
        $data = Cache::store('lookups')->remember(self::PREMIUM_TYPES_KEY, self::LONG_TTL, fn () => PremiumType::query()->where('status', 1)->get());

        // Ensure we always return a Collection (file cache might return array)
        if (is_array($data)) {
            return collect($data)->map(fn ($item) => is_array($item) ? (object) $item : $item);
        }

        return $data;
    }

    public function getFuelTypes(): Collection
    {
        $data = Cache::store('lookups')->remember(self::FUEL_TYPES_KEY, self::LONG_TTL, fn () => FuelType::query()->where('status', 1)->get());

        // Ensure we always return a Collection (file cache might return array)
        if (is_array($data)) {
            return collect($data)->map(fn ($item) => is_array($item) ? (object) $item : $item);
        }

        return $data;
    }

    public function getActiveUsers(): Collection
    {
        return Cache::store('lookups')->remember(self::USERS_KEY, self::LONG_TTL, fn () => User::query()->where('status', 1)->get());
    }

    public function invalidateInsuranceCompanies(): void
    {
        Cache::store('lookups')->forget(self::INSURANCE_COMPANIES_KEY);
    }

    public function invalidateBrokers(): void
    {
        Cache::store('lookups')->forget(self::BROKERS_KEY);
    }

    public function invalidatePolicyTypes(): void
    {
        Cache::store('lookups')->forget(self::POLICY_TYPES_KEY);
    }

    public function invalidatePremiumTypes(): void
    {
        Cache::store('lookups')->forget(self::PREMIUM_TYPES_KEY);
    }

    public function invalidateFuelTypes(): void
    {
        Cache::store('lookups')->forget(self::FUEL_TYPES_KEY);
    }

    public function invalidateUsers(): void
    {
        Cache::store('lookups')->forget(self::USERS_KEY);
    }

    public function invalidateAll(): void
    {
        $this->invalidateInsuranceCompanies();
        $this->invalidateBrokers();
        $this->invalidatePolicyTypes();
        $this->invalidatePremiumTypes();
        $this->invalidateFuelTypes();
        $this->invalidateUsers();
    }

    public function clearApplicationCache(): void
    {
        Cache::flush();
    }

    public function warmupCache(): void
    {
        $this->getInsuranceCompanies();
        $this->getBrokers();
        $this->getPolicyTypes();
        $this->getPremiumTypes();
        $this->getFuelTypes();
        $this->getActiveUsers();
        $this->cacheCustomerStatistics();
    }

    public function cacheCustomerStatistics(): array
    {
        return Cache::remember('customer_statistics', self::STATISTICS_TTL, fn (): array =>
            // 5 minutes - real-time stats
            [
                'total_customers' => Customer::query()->count(),
                'active_customers' => Customer::query()->where('status', 1)->count(),
                'recent_customers' => Customer::query()->where('created_at', '>=', now()->subDays(7))->count(),
                'total_policies' => CustomerInsurance::query()->count(),
                'active_policies' => CustomerInsurance::query()->where('status', 1)->count(),
                'expiring_policies' => CustomerInsurance::query()->where('expired_date', '<=', now()->addDays(30))->count(),
            ]);
    }

    public function invalidateCustomerStatistics(): void
    {
        Cache::forget('customer_statistics');
    }

    /**
     * Advanced Query Result Caching Methods
     */

    /**
     * Cache query results with automatic key generation
     */
    public function cacheQuery(string $method, array $parameters, callable $query, ?int $ttl = null): mixed
    {
        $key = $this->generateQueryKey($method, $parameters);
        $ttl ??= self::QUERY_TTL;

        return Cache::store('queries')->remember($key, $ttl, $query);
    }

    /**
     * Cache report data with specialized store
     */
    public function cacheReport(string $reportType, array $parameters, callable $query, ?int $ttl = null): mixed
    {
        $key = $this->generateReportKey($reportType, $parameters);
        $ttl ??= self::REPORT_TTL;

        return Cache::store('reports')->remember($key, $ttl, $query);
    }

    /**
     * Invalidate query cache by pattern
     */
    public function invalidateQueryPattern(string $pattern): void
    {
        $keys = $this->getKeysByPattern();
        foreach ($keys as $key) {
            Cache::store('queries')->forget(str_replace('queries_', '', $key));
        }
    }

    /**
     * Invalidate report cache by pattern
     */
    public function invalidateReportPattern(string $pattern): void
    {
        $keys = $this->getKeysByPattern();
        foreach ($keys as $key) {
            Cache::store('reports')->forget(str_replace('reports_', '', $key));
        }
    }

    /**
     * Get cache performance statistics
     */
    public function getCacheStatistics(): array
    {
        // File cache statistics (simplified for non-Redis environment)
        return [
            'cache_driver' => config('cache.default'),
            'storage_path' => storage_path('framework/cache'),
            'stores' => [
                'lookups_store' => 'file-based',
                'queries_store' => 'file-based',
                'reports_store' => 'file-based',
                'cache_info' => 'File-based caching active',
            ],
        ];
    }

    /**
     * Warm up critical caches for optimal performance
     */
    public function warmupCriticalCaches(): void
    {
        // Warm up lookup data
        $this->warmupCache();

        // Warm up frequently accessed customer data
        $this->cacheRecentCustomers();
        $this->cacheExpiringPolicies();
    }

    /**
     * Cache recent customers (last 30 days)
     */
    public function cacheRecentCustomers(): Collection
    {
        return $this->cacheQuery('recent_customers', [], fn () => Customer::with(['customerInsurances'])
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->get());
    }

    /**
     * Cache expiring policies for renewal workflows
     */
    public function cacheExpiringPolicies(): Collection
    {
        return $this->cacheQuery('expiring_policies', [], fn () => CustomerInsurance::with(['customer'])
            ->where('expired_date', '<=', now()->addDays(60))
            ->where('status', 1)
            ->orderBy('expired_date')
            ->get());
    }

    /**
     * Generate cache key for queries
     */
    private function generateQueryKey(string $method, array $parameters): string
    {
        return $method.'_'.md5(serialize($parameters));
    }

    /**
     * Generate cache key for reports
     */
    private function generateReportKey(string $reportType, array $parameters): string
    {
        return $reportType.'_'.md5(serialize($parameters));
    }

    /**
     * Get cache keys by pattern (file cache compatible)
     */
    private function getKeysByPattern(): array
    {
        // File cache doesn't support pattern matching
        // Return empty array for compatibility
        return [];
    }

    /**
     * Clear all performance caches
     */
    public function clearPerformanceCaches(): void
    {
        Cache::store('queries')->flush();
        Cache::store('reports')->flush();
        $this->invalidateCustomerStatistics();
    }

    /**
     * Cache invalidation for model updates
     */
    public function invalidateModelCache(string $model, $id = null): void
    {
        $patterns = [
            'Customer' => ['recent_customers', 'customer_statistics'],
            'CustomerInsurance' => ['expiring_policies', 'customer_statistics'],
            'Broker' => ['brokers'],
            'InsuranceCompany' => ['insurance_companies'],
        ];

        $modelName = class_basename($model);

        if (isset($patterns[$modelName])) {
            foreach ($patterns[$modelName] as $pattern) {
                $this->invalidateQueryPattern($pattern);
            }
        }
    }
}
