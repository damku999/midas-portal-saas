<?php

namespace App\Http\Middleware;

use App\Services\UsageAlertService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class InjectUsageAlerts
{
    public function __construct(
        protected UsageAlertService $usageAlertService
    ) {}

    /**
     * Handle an incoming request.
     *
     * Inject usage alert data into all tenant staff views for display in banner.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only inject for tenant context (staff portal)
        // Using 'web' guard - this is the guard for tenant staff users
        if (auth()->check() && tenant()) {
            try {
                $tenant = tenant();

                // Get alert summary for this tenant
                $summary = $this->usageAlertService->getAlertSummary($tenant);

                // Share with all views
                View::share('usageAlertSummary', $summary);
                View::share('hasUsageAlerts', $summary['total_active'] > 0);
                View::share('hasCriticalAlerts', $summary['has_critical']);

            } catch (\Exception $e) {
                // Silently fail - don't break the page if usage check fails
                \Log::warning('Failed to inject usage alerts', [
                    'error' => $e->getMessage(),
                    'tenant_id' => tenant()?->id,
                ]);
            }
        }

        return $next($request);
    }
}
