<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        $subscription = $tenant->subscription;

        // No subscription found
        if (! $subscription) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'No active subscription',
                    'message' => 'This organization does not have an active subscription.',
                ], 403);
            }

            return redirect()->route('subscription.required')
                ->with('error', 'No active subscription found. Please contact your administrator.');
        }

        // Subscription suspended
        if ($subscription->isSuspended()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Subscription suspended',
                    'message' => 'This organization\'s subscription has been suspended.',
                ], 403);
            }

            return redirect()->route('subscription.suspended')
                ->with('error', 'Your subscription has been suspended. Please contact support.');
        }

        // Subscription cancelled
        if ($subscription->isCancelled()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Subscription cancelled',
                    'message' => 'This organization\'s subscription has been cancelled.',
                ], 403);
            }

            return redirect()->route('subscription.cancelled')
                ->with('error', 'Your subscription has been cancelled. Please contact support to reactivate.');
        }

        // Trial expired
        if ($subscription->trialEnded()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Trial expired',
                    'message' => 'Your trial period has expired. Please upgrade to continue.',
                ], 403);
            }

            return redirect()->route('subscription.upgrade')
                ->with('warning', 'Your trial period has expired. Please upgrade your plan to continue using all features.');
        }

        return $next($request);
    }
}
