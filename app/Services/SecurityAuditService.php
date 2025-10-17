<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SecurityAuditService
{
    /**
     * Log user authentication events
     */
    public function logAuthenticationEvent(string $event, ?int $userId = null, array $context = []): void
    {
        $baseContext = [
            'event_type' => $event,
            'user_id' => $userId,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->header('User-Agent'),
            'session_id' => session()?->getId(),
            'timestamp' => now()->toISOString(),
        ];

        $fullContext = array_merge($baseContext, $context);

        // Log to security channel
        Log::channel('security')->info('Authentication Event: '.$event, $fullContext);

        // Store in database
        $this->storeSecurityEvent($event, $userId, $fullContext, $this->getEventSeverity($event));
    }

    /**
     * Log authorization events
     */
    public function logAuthorizationEvent(string $event, ?int $userId = null, array $context = []): void
    {
        $baseContext = [
            'event_type' => $event,
            'user_id' => $userId,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->header('User-Agent'),
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
            'timestamp' => now()->toISOString(),
        ];

        $fullContext = array_merge($baseContext, $context);

        // Log to security channel
        Log::channel('security')->warning('Authorization Event: '.$event, $fullContext);

        // Store in database
        $this->storeSecurityEvent($event, $userId, $fullContext, 'medium');
    }

    /**
     * Log data access events
     */
    public function logDataAccessEvent(string $resourceType, string $action, int $resourceId, ?int $userId = null): void
    {
        $context = [
            'resource_type' => $resourceType,
            'action' => $action,
            'resource_id' => $resourceId,
            'user_id' => $userId,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->header('User-Agent'),
            'url' => request()?->fullUrl(),
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('security')->info(sprintf('Data Access: %s %s ID:%d', $action, $resourceType, $resourceId), $context);

        // Store sensitive data access events
        if ($this->isSensitiveResource($resourceType)) {
            $this->storeSecurityEvent('data_access_'.$action, $userId, $context, 'low');
        }
    }

    /**
     * Log security violations
     */
    public function logSecurityViolation(string $violationType, array $context = []): void
    {
        $baseContext = [
            'violation_type' => $violationType,
            'user_id' => auth()?->id(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->header('User-Agent'),
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
            'session_id' => session()?->getId(),
            'timestamp' => now()->toISOString(),
        ];

        $fullContext = array_merge($baseContext, $context);

        Log::channel('security')->error('Security Violation: '.$violationType, $fullContext);

        // Store high severity events
        $this->storeSecurityEvent($violationType, auth()?->id(), $fullContext, 'high');

        // Check for patterns that require immediate action
        $this->checkForSecurityPatterns($fullContext);
    }

    /**
     * Log file operations
     */
    public function logFileOperation(string $operation, string $filename, ?int $userId = null, array $context = []): void
    {
        $baseContext = [
            'operation' => $operation,
            'filename' => $filename,
            'user_id' => $userId,
            'ip_address' => request()?->ip(),
            'timestamp' => now()->toISOString(),
        ];

        $fullContext = array_merge($baseContext, $context);

        Log::channel('security')->info('File Operation: '.$operation, $fullContext);

        // Store file upload/download events
        if (in_array($operation, ['upload', 'download', 'delete'])) {
            $this->storeSecurityEvent('file_'.$operation, $userId, $fullContext, 'low');
        }
    }

    /**
     * Generate security reports
     */
    public function generateSecurityReport(Carbon $startDate, Carbon $endDate): array
    {
        $events = DB::table('security_events')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $report = [
            'period' => [
                'start' => $startDate->toISOString(),
                'end' => $endDate->toISOString(),
            ],
            'summary' => [
                'total_events' => $events->count(),
                'by_severity' => $events->groupBy('severity')->map->count(),
                'by_event_type' => $events->groupBy('event_type')->map->count(),
                'unique_users' => $events->whereNotNull('user_id')->pluck('user_id')->unique()->count(),
                'unique_ips' => $events->pluck('ip_address')->unique()->count(),
            ],
            'top_events' => $events->groupBy('event_type')->map->count()->sortDesc()->take(10),
            'failed_logins' => $events->where('event_type', 'login_failed')->count(),
            'csrf_violations' => $events->where('event_type', 'csrf_token_mismatch')->count(),
            'authorization_failures' => $events->where('event_type', 'permission_denied')->count(),
            'suspicious_activities' => $events->where('severity', 'high')->count(),
        ];

        // Identify trends and patterns
        $report['trends'] = $this->analyzeTrends($events);
        $report['recommendations'] = $this->generateRecommendations($report);

        return $report;
    }

    /**
     * Store security event in database
     */
    private function storeSecurityEvent(string $eventType, ?int $userId, array $context, string $severity): void
    {
        try {
            DB::table('security_events')->insert([
                'event_type' => $eventType,
                'user_id' => $userId,
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
                'url' => $context['url'] ?? null,
                'method' => $context['method'] ?? null,
                'context' => json_encode($context),
                'severity' => $severity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $exception) {
            Log::error('Failed to store security event', [
                'error' => $exception->getMessage(),
                'event_type' => $eventType,
                'context' => $context,
            ]);
        }
    }

    /**
     * Get event severity based on event type
     */
    private function getEventSeverity(string $eventType): string
    {
        $severityMap = [
            'login_success' => 'low',
            'login_failed' => 'medium',
            'logout' => 'low',
            'password_changed' => 'medium',
            'email_changed' => 'medium',
            'account_locked' => 'high',
            'multiple_failed_logins' => 'high',
            'suspicious_login_pattern' => 'high',
        ];

        return $severityMap[$eventType] ?? 'medium';
    }

    /**
     * Check if resource is sensitive
     */
    private function isSensitiveResource(string $resourceType): bool
    {
        $sensitiveResources = [
            'customers',
            'customer_insurances',
            'claims',
            'users',
            'financial_data',
            'payment_information',
        ];

        return in_array($resourceType, $sensitiveResources);
    }

    /**
     * Check for security patterns requiring action
     */
    private function checkForSecurityPatterns(array $context): void
    {
        $userId = $context['user_id'] ?? null;
        $ipAddress = $context['ip_address'] ?? null;

        // Check for repeated violations from same user
        if ($userId) {
            $recentViolations = DB::table('security_events')
                ->where('user_id', $userId)
                ->where('severity', 'high')
                ->where('created_at', '>', now()->subHours(1))
                ->count();

            if ($recentViolations >= 5) {
                $this->triggerSecurityAlert('multiple_violations_user', [
                    'user_id' => $userId,
                    'violation_count' => $recentViolations,
                ]);
            }
        }

        // Check for repeated violations from same IP
        if ($ipAddress) {
            $recentIpViolations = DB::table('security_events')
                ->where('ip_address', $ipAddress)
                ->where('severity', 'high')
                ->where('created_at', '>', now()->subHours(1))
                ->count();

            if ($recentIpViolations >= 10) {
                $this->triggerSecurityAlert('multiple_violations_ip', [
                    'ip_address' => $ipAddress,
                    'violation_count' => $recentIpViolations,
                ]);
            }
        }
    }

    /**
     * Trigger security alerts
     */
    private function triggerSecurityAlert(string $alertType, array $context): void
    {
        Log::channel('security')->critical('Security Alert: '.$alertType, $context);

        // Store alert
        $this->storeSecurityEvent('alert_'.$alertType, null, $context, 'critical');

        // Send notification if configured
        if ($notificationEmail = config('security.monitoring.notification_email')) {
            // Queue notification email
            // Mail::to($notificationEmail)->queue(new SecurityAlertMail($alertType, $context));
        }
    }

    /**
     * Analyze trends in security events
     */
    private function analyzeTrends($events): array
    {
        $trends = [];

        // Analyze by hour of day
        $hourlyDistribution = $events->groupBy(fn ($event) => Carbon::parse($event->created_at)->hour)->map->count();

        $trends['hourly_distribution'] = $hourlyDistribution;

        // Analyze daily patterns
        $dailyEvents = $events->groupBy(fn ($event) => Carbon::parse($event->created_at)->format('Y-m-d'))->map->count();

        $trends['daily_patterns'] = $dailyEvents;

        // Top problematic IPs
        $problematicIps = $events
            ->where('severity', 'high')
            ->groupBy('ip_address')
            ->map->count()
            ->sortDesc()
            ->take(10);

        $trends['problematic_ips'] = $problematicIps;

        return $trends;
    }

    /**
     * Generate security recommendations
     */
    private function generateRecommendations(array $report): array
    {
        $recommendations = [];

        // Check for high failure rates
        if ($report['failed_logins'] > 100) {
            $recommendations[] = 'Consider implementing stronger rate limiting for login attempts';
        }

        if ($report['csrf_violations'] > 10) {
            $recommendations[] = 'Review CSRF protection implementation and user training';
        }

        if ($report['authorization_failures'] > 50) {
            $recommendations[] = 'Review user permissions and access control policies';
        }

        if ($report['suspicious_activities'] > 20) {
            $recommendations[] = 'Increase monitoring and consider additional security measures';
        }

        return $recommendations;
    }
}
