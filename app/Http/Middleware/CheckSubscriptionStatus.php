<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Routes that should be accessible even with subscription issues
     */
    protected $except = [
        // Subscription status pages
        'subscription.required',
        'subscription.suspended',
        'subscription.cancelled',
        'subscription.upgrade',
        'subscription.plans',
        'subscription.index',

        // Authentication routes (staff)
        'login',
        'logout',
        'password.request',
        'password.email',
        'password.reset',
        'password.update',
        'tenant.root',

        // Customer authentication routes
        'customer.login',
        'customer.logout',
        'customer.password.request',
        'customer.password.email',
        'customer.password.reset',
        'customer.password.update',
        'customer.verify-email',
        'customer.verify-email-notice',
        'customer.resend-verification',
        'customer.verification.send',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        // Allow access to subscription status pages and logout to prevent redirect loops
        if ($request->routeIs($this->except)) {
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

        // Subscription expired (ends_at date has passed)
        if ($subscription->hasExpired()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Subscription expired',
                    'message' => 'Your subscription has expired. Please renew to continue.',
                ], 403);
            }

            return redirect()->route('subscription.plans')
                ->with('error', 'Your subscription has expired. Please renew your plan to continue.');
        }

        // Trial expired - but check if they have an active paid subscription
        if ($subscription->trialEnded()) {
            // If status is 'active', they have converted to paid - allow access
            if ($subscription->status === 'active') {
                return $next($request);
            }

            // Trial expired and no paid subscription - block access
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Trial expired',
                    'message' => 'Your trial period has expired. Please upgrade to continue.',
                ], 403);
            }

            return redirect()->route('subscription.plans')
                ->with('warning', 'Your trial period has expired. Please upgrade your plan to continue using all features.');
        }

        return $next($request);
    }
}
