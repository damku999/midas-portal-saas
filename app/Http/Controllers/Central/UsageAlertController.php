<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Central\UsageAlert;
use App\Services\UsageAlertService;
use App\Services\UsageAnalyticsService;
use Illuminate\Http\Request;

class UsageAlertController extends Controller
{
    public function __construct(
        protected UsageAlertService $usageAlertService,
        protected UsageAnalyticsService $usageAnalyticsService
    ) {}

    /**
     * Display usage alerts dashboard.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $status = $request->get('status', 'active'); // active, all, resolved
        $thresholdLevel = $request->get('threshold');
        $resourceType = $request->get('resource');

        // Build query
        $query = UsageAlert::with('tenant')->orderBy('created_at', 'desc');

        // Apply filters
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'resolved') {
            $query->where('alert_status', 'resolved');
        }

        if ($thresholdLevel) {
            $query->where('threshold_level', $thresholdLevel);
        }

        if ($resourceType) {
            $query->where('resource_type', $resourceType);
        }

        $alerts = $query->paginate(20);

        // Get global statistics
        $stats = $this->usageAlertService->getGlobalAlertStatistics();

        // Get usage analytics data
        $analyticsData = $this->usageAnalyticsService->getGlobalUsageTrends(30);

        return view('central.usage-alerts.index', compact('alerts', 'stats', 'analyticsData'));
    }

    /**
     * Show detailed view of specific alert.
     */
    public function show(UsageAlert $alert)
    {
        $alert->load('tenant');

        // Get tenant's current usage
        $tenant = $alert->tenant;
        $usageTrackingService = app(\App\Services\UsageTrackingService::class);
        $currentUsage = $usageTrackingService->getUsageSummary($tenant);

        // Get alert history for this resource
        $alertHistory = UsageAlert::where('tenant_id', $alert->tenant_id)
            ->where('resource_type', $alert->resource_type)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('central.usage-alerts.show', compact('alert', 'currentUsage', 'alertHistory'));
    }

    /**
     * Acknowledge an alert.
     */
    public function acknowledge(Request $request, UsageAlert $alert)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->usageAlertService->acknowledgeAlert($alert, $validated['notes'] ?? null);

        return redirect()
            ->back()
            ->with('success', 'Alert acknowledged successfully.');
    }

    /**
     * Manually resolve an alert.
     */
    public function resolve(Request $request, UsageAlert $alert)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $alert->resolve($validated['notes'] ?? 'Manually resolved by admin');

        return redirect()
            ->back()
            ->with('success', 'Alert resolved successfully.');
    }

    /**
     * Get usage analytics data (AJAX endpoint).
     */
    public function analytics(Request $request)
    {
        $days = $request->get('days', 30);
        $tenantId = $request->get('tenant_id');

        if ($tenantId) {
            $tenant = Tenant::findOrFail($tenantId);
            $data = $this->usageAnalyticsService->getTenantUsageTrends($tenant, $days);
        } else {
            $data = $this->usageAnalyticsService->getGlobalUsageTrends($days);
        }

        return response()->json($data);
    }

    /**
     * Get tenant-specific usage data (AJAX endpoint).
     */
    public function tenantUsage(Tenant $tenant)
    {
        $usageTrackingService = app(\App\Services\UsageTrackingService::class);
        $usage = $usageTrackingService->getUsageSummary($tenant);
        $alerts = $this->usageAlertService->getActiveAlerts($tenant);

        return response()->json([
            'usage' => $usage,
            'active_alerts' => $alerts,
            'alert_summary' => $this->usageAlertService->getAlertSummary($tenant),
        ]);
    }

    /**
     * Update custom thresholds for a tenant.
     */
    public function updateThresholds(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'warning_threshold' => 'nullable|integer|min:50|max:95',
            'critical_threshold' => 'nullable|integer|min:60|max:99',
            'enable_custom_thresholds' => 'boolean',
        ]);

        // Store custom thresholds in tenant metadata
        $currentData = $tenant->data ?? [];
        $currentData['custom_thresholds'] = [
            'enabled' => $validated['enable_custom_thresholds'] ?? false,
            'warning' => $validated['warning_threshold'] ?? 80,
            'critical' => $validated['critical_threshold'] ?? 90,
            'updated_at' => now()->toDateTimeString(),
        ];

        $tenant->update(['data' => $currentData]);

        return redirect()
            ->back()
            ->with('success', 'Custom thresholds updated successfully.');
    }
}
