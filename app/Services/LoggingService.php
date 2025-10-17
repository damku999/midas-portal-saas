<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class LoggingService
{
    private array $contextData = [];

    public function __construct()
    {
        $this->contextData = [
            'timestamp' => now()->toISOString(),
            'session_id' => session()->getId(),
            'environment' => app()->environment(),
            'server' => request()?->server('SERVER_NAME', 'unknown'),
        ];
    }

    /**
     * Log structured application events
     */
    public function logEvent(string $event, array $data = [], string $level = 'info'): void
    {
        $logData = array_merge($this->contextData, [
            'event' => $event,
            'data' => $data,
            'trace_id' => $this->generateTraceId(),
        ]);

        Log::log($level, 'Event: '.$event, $logData);
    }

    /**
     * Log user activity with comprehensive context
     */
    public function logUserActivity(string $action, ?int $userId = null, array $metadata = []): void
    {
        $userId ??= auth()->id();

        $logData = array_merge($this->contextData, [
            'activity' => $action,
            'user_id' => $userId,
            'user_email' => auth()->user()?->email,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->header('User-Agent'),
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
            'metadata' => $metadata,
        ]);

        Log::info('User Activity: '.$action, $logData);
    }

    /**
     * Log business events with customer context
     */
    public function logBusinessEvent(string $event, array $businessData = [], ?string $entityType = null, ?int $entityId = null): void
    {
        $logData = array_merge($this->contextData, [
            'business_event' => $event,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'business_data' => $businessData,
            'user_id' => auth()->id(),
            'correlation_id' => $this->generateCorrelationId(),
        ]);

        Log::info('Business Event: '.$event, $logData);
    }

    /**
     * Log performance metrics with detailed context
     */
    public function logPerformanceMetric(string $operation, float $duration, array $metadata = []): void
    {
        $logData = array_merge($this->contextData, [
            'performance_metric' => $operation,
            'duration_ms' => round($duration, 2),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'metadata' => $metadata,
        ]);

        $level = $duration > 1000 ? 'warning' : 'info';
        Log::log($level, 'Performance: '.$operation, $logData);
    }

    /**
     * Log database query performance
     */
    public function logDatabaseQuery(string $query, float $duration, array $bindings = []): void
    {
        // Only log slow queries or in debug mode
        if ($duration > 100 || config('app.debug')) {
            $logData = array_merge($this->contextData, [
                'database_query' => true,
                'query' => $query,
                'duration_ms' => round($duration, 2),
                'bindings' => $bindings,
                'connection' => DB::getDefaultConnection(),
            ]);

            $level = $duration > 1000 ? 'warning' : ($duration > 500 ? 'notice' : 'debug');
            Log::log($level, 'Database Query', $logData);
        }
    }

    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, array $securityData = [], string $severity = 'warning'): void
    {
        $logData = array_merge($this->contextData, [
            'security_event' => $event,
            'severity' => $severity,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->header('User-Agent'),
            'user_id' => auth()->id(),
            'url' => request()?->fullUrl(),
            'headers' => request()?->headers->all(),
            'security_data' => $securityData,
        ]);

        Log::log($severity, 'Security Event: '.$event, $logData);
    }

    /**
     * Log errors with comprehensive context
     */
    public function logError(Throwable $throwable, array $context = []): void
    {
        $logData = array_merge($this->contextData, [
            'exception' => $throwable::class,
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTraceAsString(),
            'user_id' => auth()->id(),
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
            'input' => request()?->except(['password', 'password_confirmation', '_token']),
            'context' => $context,
        ]);

        Log::error('Exception: '.$throwable->getMessage(), $logData);
    }

    /**
     * Log API requests with detailed information
     */
    public function logApiRequest(Request $request, $response = null, float $duration = 0): void
    {
        $responseData = null;
        $statusCode = null;

        if ($response) {
            $statusCode = $response->getStatusCode();
            if ($response->getStatusCode() >= 400) {
                $responseData = $response->getContent();
            }
        }

        $logData = array_merge($this->contextData, [
            'api_request' => true,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'query_params' => $request->query(),
            'headers' => $request->headers->all(),
            'user_id' => auth('sanctum')->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'duration_ms' => round($duration, 2),
            'status_code' => $statusCode,
            'response_data' => $responseData,
        ]);

        $level = $statusCode >= 500 ? 'error' : ($statusCode >= 400 ? 'warning' : 'info');
        Log::log($level, 'API Request', $logData);
    }

    /**
     * Log cache operations
     */
    public function logCacheOperation(string $operation, string $key, $value = null, ?bool $hit = null): void
    {
        $logData = array_merge($this->contextData, [
            'cache_operation' => $operation,
            'cache_key' => $key,
            'cache_hit' => $hit,
            'value_size' => $value ? strlen(serialize($value)) : null,
        ]);

        Log::debug('Cache: '.$operation, $logData);
    }

    /**
     * Generate unique trace ID for request tracking
     */
    private function generateTraceId(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Generate correlation ID for business process tracking
     */
    private function generateCorrelationId(): string
    {
        return 'corr_'.Str::random(16);
    }

    /**
     * Add context data to all subsequent logs
     */
    public function addContext(string $key, $value): self
    {
        $this->contextData[$key] = $value;

        return $this;
    }

    /**
     * Create a child logger with additional context
     */
    public function withContext(array $context): self
    {
        $child = new self;
        $child->contextData = array_merge($this->contextData, $context);

        return $child;
    }

    /**
     * Log system health metrics
     */
    public function logSystemHealth(array $healthData): void
    {
        $logData = array_merge($this->contextData, [
            'system_health' => true,
            'metrics' => $healthData,
            'timestamp' => now()->toISOString(),
        ]);

        Log::info('System Health Check', $logData);
    }
}
