<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorTrackingService
{
    private array $criticalErrors = [];

    private const ALERT_THRESHOLD = 5; // errors in 5 minutes

    private const ALERT_WINDOW = 300; // 5 minutes in seconds

    public function __construct(private readonly LoggingService $loggingService) {}

    /**
     * Track and categorize errors with intelligent alerting
     */
    public function trackError(Throwable $throwable, array $context = []): void
    {
        $errorData = $this->analyzeError($throwable);

        // Log the error with structured data
        $this->loggingService->logError($throwable, array_merge($context, [
            'error_category' => $errorData['category'],
            'severity' => $errorData['severity'],
            'fingerprint' => $errorData['fingerprint'],
            'first_occurrence' => $errorData['first_occurrence'],
            'occurrence_count' => $errorData['occurrence_count'],
        ]));

        // Handle critical errors
        if ($errorData['severity'] === 'critical') {
            $this->handleCriticalError($throwable, $errorData, $context);
        }

        // Check for error rate spikes
        $this->checkErrorRateSpike();

        // Update error statistics
        $this->updateErrorStatistics($errorData);
    }

    /**
     * Analyze error to determine category, severity, and patterns
     */
    private function analyzeError(Throwable $throwable): array
    {
        $errorClass = $throwable::class;
        $message = $throwable->getMessage();
        $file = $throwable->getFile();
        $line = $throwable->getLine();

        // Generate unique fingerprint for error grouping
        $fingerprint = $this->generateErrorFingerprint($errorClass, $file, $line, $message);

        // Determine error category
        $category = $this->categorizeError($throwable);

        // Determine severity
        $severity = $this->determineSeverity($throwable, $category);

        // Check if this is a new error or recurring
        $cacheKey = 'error_tracking:'.$fingerprint;
        $errorHistory = Cache::get($cacheKey, [
            'first_occurrence' => now()->toISOString(),
            'count' => 0,
            'last_occurrence' => null,
        ]);

        $errorHistory['count']++;
        $errorHistory['last_occurrence'] = now()->toISOString();

        // Cache for 24 hours
        Cache::put($cacheKey, $errorHistory, now()->addDay());

        return [
            'fingerprint' => $fingerprint,
            'category' => $category,
            'severity' => $severity,
            'first_occurrence' => $errorHistory['first_occurrence'],
            'occurrence_count' => $errorHistory['count'],
            'is_new' => $errorHistory['count'] === 1,
        ];
    }

    /**
     * Categorize errors based on type and context
     */
    private function categorizeError(Throwable $throwable): string
    {
        $errorClass = $throwable::class;
        $message = $throwable->getMessage();

        // Database-related errors
        if (str_contains($errorClass, 'QueryException') ||
            str_contains($errorClass, 'PDOException') ||
            str_contains($message, 'database') ||
            str_contains($message, 'connection')) {
            return 'database';
        }

        // Authentication/Authorization errors
        if (str_contains($errorClass, 'AuthenticationException') ||
            str_contains($errorClass, 'AuthorizationException') ||
            str_contains($message, 'unauthorized') ||
            str_contains($message, 'permission')) {
            return 'auth';
        }

        // Validation errors
        if (str_contains($errorClass, 'ValidationException') ||
            str_contains($message, 'validation')) {
            return 'validation';
        }

        // HTTP/API errors
        if (str_contains($errorClass, 'HttpException') ||
            str_contains($errorClass, 'ClientException') ||
            str_contains($errorClass, 'ServerException')) {
            return 'http';
        }

        // File system errors
        if (str_contains($message, 'file') ||
            str_contains($message, 'directory') ||
            str_contains($message, 'permission denied')) {
            return 'filesystem';
        }

        // Cache/Redis errors
        if (str_contains($message, 'redis') ||
            str_contains($message, 'cache')) {
            return 'cache';
        }

        // Third-party service errors
        if (str_contains($message, 'curl') ||
            str_contains($message, 'timeout') ||
            str_contains($message, 'connection refused')) {
            return 'external_service';
        }

        // Business logic errors
        if (str_contains($throwable->getFile(), 'Services/') ||
            str_contains($throwable->getFile(), 'Jobs/')) {
            return 'business_logic';
        }

        return 'general';
    }

    /**
     * Determine error severity based on multiple factors
     */
    private function determineSeverity(Throwable $throwable, string $category): string
    {
        $errorClass = $throwable::class;
        $message = $throwable->getMessage();

        // Critical errors that require immediate attention
        if (str_contains($errorClass, 'FatalError') ||
            str_contains($errorClass, 'OutOfMemoryError') ||
            str_contains($message, 'out of memory') ||
            $category === 'database' && str_contains($message, 'connection') ||
            str_contains($message, 'segmentation fault')) {
            return 'critical';
        }

        // High severity errors that affect functionality
        if (str_contains($errorClass, 'Error') ||
            str_contains($errorClass, 'QueryException') ||
            $category === 'auth' ||
            $category === 'business_logic') {
            return 'high';
        }

        // Medium severity errors
        if (str_contains($errorClass, 'Exception') &&
            ! str_contains($errorClass, 'ValidationException')) {
            return 'medium';
        }

        // Low severity errors (warnings, notices)
        return 'low';
    }

    /**
     * Generate unique fingerprint for error grouping
     */
    private function generateErrorFingerprint(string $class, string $file, int $line, string $message): string
    {
        // Remove dynamic parts from message for better grouping
        $cleanMessage = preg_replace('/\d+/', 'N', $message);
        $cleanMessage = preg_replace('/[\'"][^\'\"]*[\'"]/', 'STRING', (string) $cleanMessage);

        return md5($class.$file.$line.$cleanMessage);
    }

    /**
     * Handle critical errors with immediate alerts
     */
    private function handleCriticalError(Throwable $throwable, array $errorData, array $context): void
    {
        $this->criticalErrors[] = $errorData;

        // Log critical error
        $this->loggingService->logEvent('critical_error', [
            'exception_class' => $throwable::class,
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'fingerprint' => $errorData['fingerprint'],
            'category' => $errorData['category'],
            'is_new' => $errorData['is_new'],
            'context' => $context,
        ], 'critical');

        // Send immediate alert (in production, this would send to Slack, email, PagerDuty, etc.)
        $this->sendCriticalAlert($throwable, $errorData, $context);
    }

    /**
     * Check for error rate spikes
     */
    private function checkErrorRateSpike(): void
    {
        $cacheKey = 'error_rate_'.now()->format('Y-m-d-H-i');
        $currentCount = Cache::get($cacheKey, 0);
        $newCount = $currentCount + 1;
        Cache::put($cacheKey, $newCount, now()->addMinutes(10));
        // Alert if error rate exceeds threshold
        if ($newCount >= self::ALERT_THRESHOLD) {
            $this->loggingService->logEvent('error_rate_spike', [
                'error_count' => $newCount,
                'threshold' => self::ALERT_THRESHOLD,
                'window_minutes' => self::ALERT_WINDOW / 60,
                'timestamp' => now()->toISOString(),
            ], 'warning');

            $this->sendErrorRateAlert($newCount);
        }
    }

    /**
     * Update error statistics for monitoring
     */
    private function updateErrorStatistics(array $errorData): void
    {
        $today = now()->format('Y-m-d');
        $statsKey = 'error_stats:'.$today;

        $stats = Cache::get($statsKey, [
            'total_errors' => 0,
            'by_category' => [],
            'by_severity' => [],
            'unique_errors' => 0,
        ]);

        $stats['total_errors']++;
        $stats['by_category'][$errorData['category']] = ($stats['by_category'][$errorData['category']] ?? 0) + 1;
        $stats['by_severity'][$errorData['severity']] = ($stats['by_severity'][$errorData['severity']] ?? 0) + 1;

        if ($errorData['is_new']) {
            $stats['unique_errors']++;
        }

        Cache::put($statsKey, $stats, now()->addDays(7));
    }

    /**
     * Send critical error alert
     */
    private function sendCriticalAlert(Throwable $throwable, array $errorData, array $context): void
    {
        // In production, this would integrate with:
        // - Slack webhooks
        // - Email alerts
        // - PagerDuty
        // - SMS notifications
        // - Discord webhooks

        Log::critical('CRITICAL ERROR ALERT', [
            'exception' => $throwable::class,
            'message' => $throwable->getMessage(),
            'fingerprint' => $errorData['fingerprint'],
            'category' => $errorData['category'],
            'environment' => app()->environment(),
            'server' => request()?->server('SERVER_NAME'),
            'url' => request()?->fullUrl(),
            'user_id' => auth()->id(),
            'context' => $context,
        ]);
    }

    /**
     * Send error rate spike alert
     */
    private function sendErrorRateAlert(int $errorCount): void
    {
        Log::warning('ERROR RATE SPIKE DETECTED', [
            'error_count' => $errorCount,
            'threshold' => self::ALERT_THRESHOLD,
            'window_seconds' => self::ALERT_WINDOW,
            'timestamp' => now()->toISOString(),
            'environment' => app()->environment(),
            'server' => request()?->server('SERVER_NAME'),
        ]);
    }

    /**
     * Get error statistics for monitoring dashboard
     */
    public function getErrorStatistics(int $days = 7): array
    {
        $stats = [];

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $statsKey = 'error_stats:'.$date;
            $dayStats = Cache::get($statsKey, [
                'total_errors' => 0,
                'by_category' => [],
                'by_severity' => [],
                'unique_errors' => 0,
            ]);

            $stats[$date] = $dayStats;
        }

        return $stats;
    }

    /**
     * Clear error statistics (for testing or maintenance)
     */
    public function clearStatistics(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            Cache::forget('error_stats:'.$date);
        }
    }
}
