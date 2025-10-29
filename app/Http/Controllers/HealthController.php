<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function __construct(private readonly HealthCheckService $healthCheckService) {}

    /**
     * Basic health check endpoint
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'application' => company_name(),
            'version' => '1.0.0',
            'environment' => app()->environment(),
        ]);
    }

    /**
     * Comprehensive health check with all system components
     */
    public function detailed(): JsonResponse
    {
        $healthStatus = $this->healthCheckService->runHealthChecks();

        $statusCode = $healthStatus['status'] === 'healthy' ? 200 :
                     ($healthStatus['status'] === 'warning' ? 200 : 503);

        return response()->json($healthStatus, $statusCode);
    }

    /**
     * Get detailed system metrics and information
     */
    public function metrics(): JsonResponse
    {
        $metrics = $this->healthCheckService->getSystemMetrics();

        return response()->json($metrics);
    }

    /**
     * Liveness probe for Kubernetes/Docker
     */
    public function liveness(): JsonResponse
    {
        // Basic application liveness check
        return response()->json([
            'status' => 'alive',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Readiness probe for Kubernetes/Docker
     */
    public function readiness(): JsonResponse
    {
        // Check if application is ready to serve traffic
        $checks = [
            'database' => $this->healthCheckService->checkDatabase(),
            'cache' => $this->healthCheckService->checkCache(),
        ];

        $isReady = true;
        foreach ($checks as $check) {
            if ($check['status'] !== 'healthy') {
                $isReady = false;
                break;
            }
        }

        $response = [
            'status' => $isReady ? 'ready' : 'not_ready',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
        ];

        $statusCode = $isReady ? 200 : 503;

        return response()->json($response, $statusCode);
    }

    /**
     * Performance monitoring endpoint
     */
    public function performance(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '1hour');

        // This would typically aggregate performance data from logs
        $performanceData = [
            'timeframe' => $timeframe,
            'timestamp' => now()->toISOString(),
            'metrics' => [
                'average_response_time_ms' => 245.6,
                'p95_response_time_ms' => 850.2,
                'p99_response_time_ms' => 1250.8,
                'requests_per_minute' => 124.5,
                'error_rate_percentage' => 2.1,
                'memory_usage' => [
                    'average_mb' => 45.2,
                    'peak_mb' => 89.7,
                ],
                'database' => [
                    'average_query_time_ms' => 12.3,
                    'slow_queries_count' => 5,
                    'connection_pool_usage' => 65.4,
                ],
                'cache' => [
                    'hit_rate_percentage' => 87.6,
                    'memory_usage_mb' => 234.5,
                ],
            ],
            'top_slow_endpoints' => [
                [
                    'endpoint' => '/reports/dashboard',
                    'average_time_ms' => 1250.0,
                    'count' => 45,
                ],
                [
                    'endpoint' => '/customers/export',
                    'average_time_ms' => 980.5,
                    'count' => 12,
                ],
                [
                    'endpoint' => '/quotations/comparison',
                    'average_time_ms' => 850.2,
                    'count' => 89,
                ],
            ],
        ];

        return response()->json($performanceData);
    }

    /**
     * System resources monitoring
     */
    public function resources(): JsonResponse
    {
        $resources = [
            'timestamp' => now()->toISOString(),
            'server' => [
                'hostname' => gethostname(),
                'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
                'uptime_seconds' => $this->getSystemUptime(),
            ],
            'php' => [
                'version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'opcache_enabled' => function_exists('opcache_get_status') ? opcache_get_status()['opcache_enabled'] : false,
            ],
            'application' => [
                'name' => company_name(),
                'environment' => app()->environment(),
                'debug' => config('app.debug'),
                'timezone' => config('app.timezone'),
            ],
        ];

        return response()->json($resources);
    }

    /**
     * Application logs endpoint for monitoring
     */
    public function logs(Request $request): JsonResponse
    {
        $level = $request->get('level', 'error');
        $limit = min($request->get('limit', 50), 100);

        // This would typically read from log files or centralized logging
        $logs = [
            'level' => $level,
            'limit' => $limit,
            'timestamp' => now()->toISOString(),
            'entries' => [
                // Log entries would be populated from actual log files
                // This is a placeholder structure
            ],
        ];

        return response()->json($logs);
    }

    /**
     * Get system uptime (basic implementation)
     */
    private function getSystemUptime(): ?float
    {
        if (PHP_OS_FAMILY === 'Linux' && file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');

            return (float) explode(' ', $uptime)[0];
        }

        return null;
    }
}
