<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:security.monitor')->except(['dashboard']);
    }

    /**
     * Security monitoring dashboard
     */
    public function dashboard(): View
    {
        $metrics = $this->auditService->getSecurityMetrics(7); // Last 7 days
        $recentActivity = $this->auditService->getRecentActivity(24, 20);
        $suspiciousActivity = $this->auditService->getSuspiciousActivity(7);

        return view('security.dashboard', ['metrics' => $metrics, 'recentActivity' => $recentActivity, 'suspiciousActivity' => $suspiciousActivity]);
    }

    /**
     * Audit logs listing and search
     */
    public function auditLogs(Request $request): View
    {
        $filters = $request->only([
            'event', 'event_category', 'risk_level', 'is_suspicious',
            'ip_address', 'actor_id', 'actor_type', 'date_from', 'date_to', 'search',
        ]);

        $this->auditService->searchLogs($filters, 25);

        return view('security.audit-logs', ['logs' => $logs, 'filters' => $filters]);
    }

    /**
     * Security analytics API endpoint
     */
    public function analytics(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $metrics = $this->auditService->getSecurityMetrics($days);

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Recent suspicious activity API
     */
    public function suspiciousActivity(Request $request): JsonResponse
    {
        $days = $request->input('days', 7);
        $limit = $request->input('limit', 50);

        $activity = $this->auditService->getSuspiciousActivity($days)->take($limit);

        return response()->json([
            'success' => true,
            'data' => $activity,
        ]);
    }

    /**
     * High risk activity API
     */
    public function highRiskActivity(Request $request): JsonResponse
    {
        $days = $request->input('days', 7);
        $limit = $request->input('limit', 50);

        $activity = $this->auditService->getHighRiskActivity($days)->take($limit);

        return response()->json([
            'success' => true,
            'data' => $activity,
        ]);
    }

    /**
     * User activity report
     */
    public function userActivity(Request $request, $userId): JsonResponse
    {
        $userType = $request->input('user_type', User::class);
        $days = $request->input('days', 30);

        $activity = $this->auditService->getActivityByUser($userId, $userType, $days);

        return response()->json([
            'success' => true,
            'data' => $activity,
        ]);
    }

    /**
     * Entity activity report
     */
    public function entityActivity(Request $request, $entityId): JsonResponse
    {
        $entityType = $request->input('entity_type');
        $days = $request->input('days', 30);

        if (! $entityType) {
            return response()->json([
                'success' => false,
                'message' => 'Entity type is required',
            ], 400);
        }

        $activity = $this->auditService->getActivityByEntity($entityId, $entityType, $days);

        return response()->json([
            'success' => true,
            'data' => $activity,
        ]);
    }

    /**
     * Generate security report
     */
    public function generateReport(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $report = $this->auditService->generateSecurityReport($days);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Export audit logs
     */
    public function exportLogs(Request $request): Response
    {
        $filters = $request->only([
            'event', 'event_category', 'risk_level', 'is_suspicious',
            'ip_address', 'actor_id', 'actor_type', 'date_from', 'date_to', 'search',
        ]);

        $format = $request->input('format', 'csv');
        $data = $this->auditService->exportLogs($filters, $format);

        $filename = 'audit-logs-'.now()->format('Y-m-d-H-i-s').'.'.$format;
        $contentType = $format === 'json' ? 'application/json' : 'text/csv';

        return response($data, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    /**
     * Real-time security alerts
     */
    public function alerts(Request $request): JsonResponse
    {
        $since = $request->input('since');
        $query = $this->auditService->getSuspiciousActivity(1); // Last 24 hours

        if ($since) {
            $query = $query->where('occurred_at', '>', $since);
        }

        $alerts = $query->take(20);

        return response()->json([
            'success' => true,
            'data' => $alerts,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Security metrics widget data
     */
    public function metricsWidget(Request $request): JsonResponse
    {
        $period = $request->input('period', '24h');

        $days = match ($period) {
            '1h' => 1 / 24,
            '24h' => 1,
            '7d' => 7,
            '30d' => 30,
            default => 1,
        };

        $metrics = $this->auditService->getSecurityMetrics($days);

        return response()->json([
            'success' => true,
            'data' => [
                'total_events' => $metrics['total_events'],
                'suspicious_events' => $metrics['suspicious_events'],
                'high_risk_events' => $metrics['high_risk_events'],
                'failed_logins' => $metrics['failed_logins'],
                'unique_ips' => $metrics['unique_ips'],
                'suspicious_percentage' => $metrics['total_events'] > 0
                    ? round(($metrics['suspicious_events'] / $metrics['total_events']) * 100, 2)
                    : 0,
            ],
            'period' => $period,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
