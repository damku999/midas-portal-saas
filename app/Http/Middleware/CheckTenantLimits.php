<?php

namespace App\Http\Middleware;

use App\Services\UsageTrackingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantLimits
{
    protected UsageTrackingService $usageService;

    public function __construct(UsageTrackingService $usageService)
    {
        $this->usageService = $usageService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $resourceType): Response
    {
        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        // Check if subscription is active
        $subscription = $tenant->subscription;

        if (! $subscription) {
            return redirect()->route('subscription.required')
                ->with('error', 'No active subscription found. Please contact your administrator.');
        }

        if (! $subscription->isActive()) {
            $message = $subscription->isOnTrial()
                ? 'Your trial period has expired. Please upgrade your plan.'
                : 'Your subscription is not active. Please contact your administrator.';

            return redirect()->route('subscription.required')
                ->with('error', $message);
        }

        // Check resource limits
        if (! $this->usageService->canCreate($resourceType)) {
            $plan = $subscription->plan;
            $usage = $this->usageService->getTenantUsage($tenant);

            $limitMessage = match ($resourceType) {
                'user' => "You have reached the maximum number of users ({$plan->max_users}) allowed in your {$plan->name} plan.",
                'customer' => "You have reached the maximum number of customers ({$plan->max_customers}) allowed in your {$plan->name} plan.",
                default => "You have reached your plan limit for {$resourceType}.",
            };

            return back()
                ->with('error', $limitMessage.' Please upgrade your plan to add more.')
                ->with('upgrade_required', true);
        }

        return $next($request);
    }
}
