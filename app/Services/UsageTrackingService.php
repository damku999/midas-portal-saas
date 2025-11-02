<?php

namespace App\Services;

use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UsageTrackingService
{
    /**
     * Get current usage statistics for a tenant.
     */
    public function getTenantUsage(Tenant $tenant): array
    {
        $cacheKey = "tenant_usage_{$tenant->id}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($tenant) {
            return $tenant->run(function () {
                return [
                    'users' => User::count(),
                    'customers' => Customer::count(),
                    'policies' => CustomerInsurance::count(),
                    'active_policies' => CustomerInsurance::where('status', 'active')->count(),
                    'leads' => Lead::count(),
                    'storage_mb' => $this->calculateStorageUsage(),
                ];
            });
        });
    }

    /**
     * Check if tenant is within plan limits.
     */
    public function isWithinLimits(Tenant $tenant, ?string $limitType = null): bool
    {
        $subscription = $tenant->subscription;

        if (! $subscription || ! $subscription->plan) {
            return false;
        }

        $plan = $subscription->plan;
        $usage = $this->getTenantUsage($tenant);

        $checks = [
            'users' => $plan->max_users === -1 || $usage['users'] < $plan->max_users,
            'customers' => $plan->max_customers === -1 || $usage['customers'] < $plan->max_customers,
            'storage' => $plan->max_storage_gb === -1 || ($usage['storage_mb'] / 1024) < $plan->max_storage_gb,
        ];

        if ($limitType) {
            return $checks[$limitType] ?? false;
        }

        return ! in_array(false, $checks, true);
    }

    /**
     * Check if specific resource can be created.
     */
    public function canCreate(string $resourceType): bool
    {
        $tenant = tenant();

        if (! $tenant) {
            return false;
        }

        $subscription = Subscription::where('tenant_id', $tenant->id)->first();

        if (! $subscription || ! $subscription->isActive()) {
            return false;
        }

        $plan = $subscription->plan;
        $usage = $this->getTenantUsage($tenant);

        switch ($resourceType) {
            case 'user':
                return $plan->max_users === -1 || $usage['users'] < $plan->max_users;
            case 'customer':
                return $plan->max_customers === -1 || $usage['customers'] < $plan->max_customers;
            case 'policy':
                return true; // Policies are unlimited in current plan structure
            case 'lead':
                return true; // Leads are unlimited
            default:
                return true;
        }
    }

    /**
     * Get usage percentage for a specific limit.
     */
    public function getUsagePercentage(Tenant $tenant, string $limitType): float
    {
        $subscription = $tenant->subscription;

        if (! $subscription || ! $subscription->plan) {
            return 0;
        }

        $plan = $subscription->plan;
        $usage = $this->getTenantUsage($tenant);

        switch ($limitType) {
            case 'users':
                return $plan->max_users === -1 ? 0 : ($usage['users'] / $plan->max_users) * 100;
            case 'customers':
                return $plan->max_customers === -1 ? 0 : ($usage['customers'] / $plan->max_customers) * 100;
            case 'storage':
                $storageMB = $usage['storage_mb'];
                $limitMB = $plan->max_storage_gb * 1024;

                return $plan->max_storage_gb === -1 ? 0 : ($storageMB / $limitMB) * 100;
            default:
                return 0;
        }
    }

    /**
     * Get remaining capacity for a resource.
     */
    public function getRemainingCapacity(Tenant $tenant, string $limitType): int
    {
        $subscription = $tenant->subscription;

        if (! $subscription || ! $subscription->plan) {
            return 0;
        }

        $plan = $subscription->plan;
        $usage = $this->getTenantUsage($tenant);

        switch ($limitType) {
            case 'users':
                return $plan->max_users === -1 ? -1 : max(0, $plan->max_users - $usage['users']);
            case 'customers':
                return $plan->max_customers === -1 ? -1 : max(0, $plan->max_customers - $usage['customers']);
            case 'storage':
                if ($plan->max_storage_gb === -1) {
                    return -1;
                }
                $usedGB = $usage['storage_mb'] / 1024;

                return max(0, $plan->max_storage_gb - $usedGB);
            default:
                return 0;
        }
    }

    /**
     * Clear usage cache for a tenant.
     */
    public function clearUsageCache(Tenant $tenant): void
    {
        Cache::forget("tenant_usage_{$tenant->id}");
    }

    /**
     * Calculate storage usage in MB.
     */
    private function calculateStorageUsage(): float
    {
        // Calculate database size
        $tables = DB::select('SHOW TABLES');
        $totalSize = 0;

        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            $size = DB::select('
                SELECT
                    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
            ', [$tableName]);

            $totalSize += $size[0]->size_mb ?? 0;
        }

        // TODO: Add file storage calculation when filesystem is implemented
        // $fileStorageMB = $this->calculateFileStorageUsage();

        return round($totalSize, 2);
    }

    /**
     * Track a resource creation event.
     */
    public function trackResourceCreated(string $resourceType): void
    {
        $tenant = tenant();

        if ($tenant) {
            $this->clearUsageCache($tenant);
        }
    }

    /**
     * Track a resource deletion event.
     */
    public function trackResourceDeleted(string $resourceType): void
    {
        $tenant = tenant();

        if ($tenant) {
            $this->clearUsageCache($tenant);
        }
    }

    /**
     * Get usage summary with warnings.
     */
    public function getUsageSummary(Tenant $tenant): array
    {
        $usage = $this->getTenantUsage($tenant);
        $subscription = $tenant->subscription;
        $plan = $subscription?->plan;

        if (! $plan) {
            return ['usage' => $usage, 'limits' => [], 'warnings' => []];
        }

        $limits = [
            'users' => [
                'current' => $usage['users'],
                'max' => $plan->max_users,
                'percentage' => $this->getUsagePercentage($tenant, 'users'),
                'remaining' => $this->getRemainingCapacity($tenant, 'users'),
            ],
            'customers' => [
                'current' => $usage['customers'],
                'max' => $plan->max_customers,
                'percentage' => $this->getUsagePercentage($tenant, 'customers'),
                'remaining' => $this->getRemainingCapacity($tenant, 'customers'),
            ],
            'storage' => [
                'current' => round($usage['storage_mb'] / 1024, 2),
                'max' => $plan->max_storage_gb,
                'percentage' => $this->getUsagePercentage($tenant, 'storage'),
                'remaining' => $this->getRemainingCapacity($tenant, 'storage'),
                'unit' => 'GB',
            ],
        ];

        $warnings = [];

        foreach ($limits as $type => $limit) {
            if ($limit['max'] !== -1 && $limit['percentage'] >= 80) {
                $warnings[] = [
                    'type' => $type,
                    'message' => ucfirst($type).' usage at '.round($limit['percentage']).'%',
                    'severity' => $limit['percentage'] >= 95 ? 'critical' : 'warning',
                ];
            }
        }

        return [
            'usage' => $usage,
            'limits' => $limits,
            'warnings' => $warnings,
            'plan' => $plan->name,
        ];
    }
}
