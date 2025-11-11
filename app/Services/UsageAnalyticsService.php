<?php

namespace App\Services;

use App\Models\Central\Tenant;
use App\Models\Central\UsageAlert;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UsageAnalyticsService
{
    public function __construct(
        protected UsageTrackingService $usageTrackingService
    ) {}

    /**
     * Get global usage trends across all tenants.
     *
     * @param int $days Number of days to analyze
     * @return array
     */
    public function getGlobalUsageTrends(int $days = 30): array
    {
        $cacheKey = "global_usage_trends_{$days}d";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($days) {
            $startDate = now()->subDays($days);

            // Get all active tenants
            $tenants = Tenant::whereHas('subscription', function ($query) {
                $query->where('status', 'active')->orWhere('status', 'trial');
            })->get();

            $dailyData = [];
            $labels = [];

            // Generate daily data points
            for ($i = $days; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateKey = $date->format('Y-m-d');
                $labels[] = $date->format('M d');

                $dailyData[$dateKey] = [
                    'users' => 0,
                    'customers' => 0,
                    'storage' => 0,
                    'tenants_at_warning' => 0,
                    'tenants_at_critical' => 0,
                ];
            }

            // Aggregate current usage (simplified - real implementation would track historical data)
            foreach ($tenants as $tenant) {
                try {
                    $usage = $this->usageTrackingService->getTenantUsage($tenant);
                    $todayKey = now()->format('Y-m-d');

                    if (isset($dailyData[$todayKey])) {
                        $dailyData[$todayKey]['users'] += $usage['users'];
                        $dailyData[$todayKey]['customers'] += $usage['customers'];
                        $dailyData[$todayKey]['storage'] += round(($usage['storage_mb'] ?? 0) / 1024, 2);
                    }
                } catch (\Exception $e) {
                    // Skip tenant if error
                    continue;
                }
            }

            // Get alert counts by date
            $alerts = UsageAlert::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, threshold_level, COUNT(*) as count')
                ->groupBy('date', 'threshold_level')
                ->get();

            foreach ($alerts as $alert) {
                $dateKey = $alert->date;
                if (isset($dailyData[$dateKey])) {
                    if ($alert->threshold_level === 'warning') {
                        $dailyData[$dateKey]['tenants_at_warning'] += $alert->count;
                    } elseif (in_array($alert->threshold_level, ['critical', 'exceeded'])) {
                        $dailyData[$dateKey]['tenants_at_critical'] += $alert->count;
                    }
                }
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    'users' => array_column($dailyData, 'users'),
                    'customers' => array_column($dailyData, 'customers'),
                    'storage' => array_column($dailyData, 'storage'),
                    'warnings' => array_column($dailyData, 'tenants_at_warning'),
                    'critical' => array_column($dailyData, 'tenants_at_critical'),
                ],
                'summary' => $this->calculateTrendSummary($dailyData),
            ];
        });
    }

    /**
     * Get usage trends for a specific tenant.
     *
     * @param Tenant $tenant
     * @param int $days
     * @return array
     */
    public function getTenantUsageTrends(Tenant $tenant, int $days = 30): array
    {
        $cacheKey = "tenant_usage_trends_{$tenant->id}_{$days}d";

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($tenant, $days) {
            $labels = [];
            $usersData = [];
            $customersData = [];
            $storageData = [];

            // Get current usage and project historical data
            // Note: In a real implementation, you'd store historical usage data
            $currentUsage = $this->usageTrackingService->getTenantUsage($tenant);
            $plan = $tenant->subscription->plan;

            for ($i = $days; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $labels[] = $date->format('M d');

                // Simplified: Use current usage (real implementation would fetch historical data)
                $usersData[] = $currentUsage['users'];
                $customersData[] = $currentUsage['customers'];
                $storageData[] = round(($currentUsage['storage_mb'] ?? 0) / 1024, 2);
            }

            return [
                'labels' => $labels,
                'datasets' => [
                    'users' => $usersData,
                    'customers' => $customersData,
                    'storage' => $storageData,
                ],
                'limits' => [
                    'users' => $plan->max_users,
                    'customers' => $plan->max_customers,
                    'storage' => $plan->max_storage_gb,
                ],
                'predictions' => $this->predictUsage($tenant),
            ];
        });
    }

    /**
     * Predict when tenant will hit limits based on current growth.
     *
     * @param Tenant $tenant
     * @return array
     */
    public function predictUsage(Tenant $tenant): array
    {
        try {
            $currentUsage = $this->usageTrackingService->getTenantUsage($tenant);
            $plan = $tenant->subscription->plan;

            $predictions = [];

            // Simple linear prediction (real implementation would use more sophisticated algorithms)
            foreach (['users', 'customers', 'storage'] as $resource) {
                $current = $resource === 'storage'
                    ? round(($currentUsage['storage_mb'] ?? 0) / 1024, 2)
                    : $currentUsage[$resource];

                $limit = match ($resource) {
                    'users' => $plan->max_users,
                    'customers' => $plan->max_customers,
                    'storage' => $plan->max_storage_gb,
                };

                if ($limit === -1 || $limit === 0) {
                    $predictions[$resource] = [
                        'days_until_limit' => null,
                        'growth_rate' => 0,
                        'status' => 'unlimited',
                    ];
                    continue;
                }

                $percentage = ($current / $limit) * 100;

                // Assume 5% monthly growth (simplified)
                $monthlyGrowthRate = 0.05;
                $daysUntilLimit = null;

                if ($percentage < 100 && $current > 0) {
                    $remaining = $limit - $current;
                    $dailyGrowth = ($current * $monthlyGrowthRate) / 30;

                    if ($dailyGrowth > 0) {
                        $daysUntilLimit = (int) ceil($remaining / $dailyGrowth);
                    }
                }

                $predictions[$resource] = [
                    'days_until_limit' => $daysUntilLimit,
                    'growth_rate' => $monthlyGrowthRate * 100, // Convert to percentage
                    'status' => $this->getPredictionStatus($percentage, $daysUntilLimit),
                    'current_percentage' => round($percentage, 1),
                ];
            }

            return $predictions;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get prediction status based on percentage and days until limit.
     *
     * @param float $percentage
     * @param int|null $daysUntilLimit
     * @return string
     */
    protected function getPredictionStatus(float $percentage, ?int $daysUntilLimit): string
    {
        if ($percentage >= 90) {
            return 'critical';
        }

        if ($daysUntilLimit !== null && $daysUntilLimit <= 30) {
            return 'warning';
        }

        if ($percentage >= 70) {
            return 'caution';
        }

        return 'healthy';
    }

    /**
     * Calculate trend summary (growth rates, averages, etc.).
     *
     * @param array $dailyData
     * @return array
     */
    protected function calculateTrendSummary(array $dailyData): array
    {
        if (empty($dailyData)) {
            return [];
        }

        $values = array_values($dailyData);
        $firstWeek = array_slice($values, 0, 7);
        $lastWeek = array_slice($values, -7);

        $calculateAverage = function ($data, $key) {
            $values = array_column($data, $key);
            return count($values) > 0 ? array_sum($values) / count($values) : 0;
        };

        return [
            'users' => [
                'first_week_avg' => round($calculateAverage($firstWeek, 'users'), 2),
                'last_week_avg' => round($calculateAverage($lastWeek, 'users'), 2),
                'trend' => $this->calculateTrend($firstWeek, $lastWeek, 'users'),
            ],
            'customers' => [
                'first_week_avg' => round($calculateAverage($firstWeek, 'customers'), 2),
                'last_week_avg' => round($calculateAverage($lastWeek, 'customers'), 2),
                'trend' => $this->calculateTrend($firstWeek, $lastWeek, 'customers'),
            ],
            'storage' => [
                'first_week_avg' => round($calculateAverage($firstWeek, 'storage'), 2),
                'last_week_avg' => round($calculateAverage($lastWeek, 'storage'), 2),
                'trend' => $this->calculateTrend($firstWeek, $lastWeek, 'storage'),
            ],
        ];
    }

    /**
     * Calculate trend direction (up, down, stable).
     *
     * @param array $firstWeek
     * @param array $lastWeek
     * @param string $key
     * @return string
     */
    protected function calculateTrend(array $firstWeek, array $lastWeek, string $key): string
    {
        $firstAvg = array_sum(array_column($firstWeek, $key)) / count($firstWeek);
        $lastAvg = array_sum(array_column($lastWeek, $key)) / count($lastWeek);

        $change = (($lastAvg - $firstAvg) / max($firstAvg, 1)) * 100;

        if ($change > 10) {
            return 'up';
        } elseif ($change < -10) {
            return 'down';
        }

        return 'stable';
    }

    /**
     * Clear analytics cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('global_usage_trends_30d');
        Cache::forget('global_usage_trends_7d');
        Cache::forget('global_usage_trends_90d');
    }
}
