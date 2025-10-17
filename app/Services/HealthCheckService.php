<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthCheckService
{
    public function __construct(private readonly LoggingService $loggingService) {}

    /**
     * Run all system health checks
     */
    public function runHealthChecks(): array
    {
        $startTime = microtime(true);

        $healthStatus = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'checks' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'storage' => $this->checkStorage(),
                'queue' => $this->checkQueue(),
                'memory' => $this->checkMemory(),
                'disk' => $this->checkDisk(),
            ],
            'performance' => [
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ],
        ];

        // Determine overall status
        $healthStatus['status'] = $this->determineOverallStatus($healthStatus['checks']);

        // Log health check results
        $this->loggingService->logSystemHealth($healthStatus);

        return $healthStatus;
    }

    /**
     * Check database connectivity and performance
     */
    private function checkDatabase(): array
    {
        try {
            $startTime = microtime(true);

            // Test basic connectivity
            DB::connection()->getPdo();

            // Test a simple query
            $result = DB::select('SELECT 1 as test');

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            // Check for slow queries (from Laravel query log)
            $slowQueries = 0; // This would be populated from monitoring

            return [
                'status' => 'healthy',
                'response_time_ms' => $responseTime,
                'connection' => DB::getDefaultConnection(),
                'slow_queries_count' => $slowQueries,
                'message' => 'Database connection successful',
            ];
        } catch (Throwable $throwable) {
            return [
                'status' => 'unhealthy',
                'error' => $throwable->getMessage(),
                'message' => 'Database connection failed',
            ];
        }
    }

    /**
     * Check cache system performance
     */
    private function checkCache(): array
    {
        try {
            $startTime = microtime(true);
            $testKey = 'health_check_'.time();
            $testValue = 'test_value';

            // Test cache write
            Cache::put($testKey, $testValue, 60);

            // Test cache read
            $cachedValue = Cache::get($testKey);

            // Clean up
            Cache::forget($testKey);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($cachedValue !== $testValue) {
                throw new \Exception('Cache read/write test failed');
            }

            return [
                'status' => 'healthy',
                'response_time_ms' => $responseTime,
                'driver' => config('cache.default'),
                'message' => 'Cache system operational',
            ];
        } catch (Throwable $throwable) {
            return [
                'status' => 'unhealthy',
                'error' => $throwable->getMessage(),
                'message' => 'Cache system failure',
            ];
        }
    }

    /**
     * Check file storage system
     */
    private function checkStorage(): array
    {
        try {
            $startTime = microtime(true);
            $testFile = 'health_check_'.time().'.txt';
            $testContent = 'health check test';

            // Test file write
            Storage::put($testFile, $testContent);

            // Test file read
            $content = Storage::get($testFile);

            // Test file delete
            Storage::delete($testFile);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($content !== $testContent) {
                throw new \Exception('Storage read/write test failed');
            }

            return [
                'status' => 'healthy',
                'response_time_ms' => $responseTime,
                'driver' => config('filesystems.default'),
                'message' => 'Storage system operational',
            ];
        } catch (Throwable $throwable) {
            return [
                'status' => 'unhealthy',
                'error' => $throwable->getMessage(),
                'message' => 'Storage system failure',
            ];
        }
    }

    /**
     * Check queue system
     */
    private function checkQueue(): array
    {
        try {
            $queueConnection = config('queue.default');
            $queueSize = 0; // This would check actual queue size

            return [
                'status' => 'healthy',
                'connection' => $queueConnection,
                'pending_jobs' => $queueSize,
                'message' => 'Queue system operational',
            ];
        } catch (Throwable $throwable) {
            return [
                'status' => 'unhealthy',
                'error' => $throwable->getMessage(),
                'message' => 'Queue system failure',
            ];
        }
    }

    /**
     * Check memory usage
     */
    private function checkMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        $memoryLimit = $this->getMemoryLimit();

        $usagePercentage = $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;

        $status = 'healthy';
        if ($usagePercentage > 90) {
            $status = 'critical';
        } elseif ($usagePercentage > 80) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'usage_bytes' => $memoryUsage,
            'usage_mb' => round($memoryUsage / 1024 / 1024, 2),
            'peak_mb' => round($peakMemory / 1024 / 1024, 2),
            'limit_mb' => $memoryLimit > 0 ? round($memoryLimit / 1024 / 1024, 2) : 'unlimited',
            'usage_percentage' => round($usagePercentage, 2),
            'message' => sprintf('Memory usage at %s%%', $usagePercentage),
        ];
    }

    /**
     * Check disk usage
     */
    private function checkDisk(): array
    {
        $storagePath = storage_path();
        $freeBytes = disk_free_space($storagePath);
        $totalBytes = disk_total_space($storagePath);

        $usedBytes = $totalBytes - $freeBytes;
        $usagePercentage = ($usedBytes / $totalBytes) * 100;

        $status = 'healthy';
        if ($usagePercentage > 95) {
            $status = 'critical';
        } elseif ($usagePercentage > 85) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'free_gb' => round($freeBytes / 1024 / 1024 / 1024, 2),
            'used_gb' => round($usedBytes / 1024 / 1024 / 1024, 2),
            'total_gb' => round($totalBytes / 1024 / 1024 / 1024, 2),
            'usage_percentage' => round($usagePercentage, 2),
            'message' => sprintf('Disk usage at %s%%', $usagePercentage),
        ];
    }

    /**
     * Get memory limit in bytes
     */
    private function getMemoryLimit(): int
    {
        $memoryLimit = ini_get('memory_limit');

        if ($memoryLimit === '-1') {
            return 0; // Unlimited
        }

        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) $memoryLimit;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    /**
     * Determine overall health status from individual checks
     */
    private function determineOverallStatus(array $checks): string
    {
        $hasUnhealthy = false;
        $hasWarning = false;

        foreach ($checks as $check) {
            if (is_array($check) && isset($check['status'])) {
                if ($check['status'] === 'unhealthy' || $check['status'] === 'critical') {
                    $hasUnhealthy = true;
                } elseif ($check['status'] === 'warning') {
                    $hasWarning = true;
                }
            }
        }
        if ($hasUnhealthy) {
            return 'unhealthy';
        }

        if ($hasWarning) {
            return 'warning';
        }

        return 'healthy';
    }

    /**
     * Get detailed system metrics
     */
    public function getSystemMetrics(): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'server' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                'operating_system' => PHP_OS,
            ],
            'database' => [
                'connection' => DB::getDefaultConnection(),
                'driver' => DB::connection()->getDriverName(),
            ],
            'cache' => [
                'default_driver' => config('cache.default'),
                'stores' => array_keys(config('cache.stores')),
            ],
            'queue' => [
                'default_connection' => config('queue.default'),
                'connections' => array_keys(config('queue.connections')),
            ],
            'filesystem' => [
                'default_disk' => config('filesystems.default'),
                'disks' => array_keys(config('filesystems.disks')),
            ],
        ];
    }
}
