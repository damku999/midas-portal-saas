<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('customer')->check()) {
            return redirect()->route('customer.login')->with('error', 'Please login to access customer portal.');
        }

        // Check if customer is active
        $customer = Auth::guard('customer')->user();
        if (! $customer || ! $customer->status) {
            Auth::guard('customer')->logout();

            return redirect()->route('customer.login')->with('error', 'Your account has been deactivated. Please contact support.');
        }

        // Check if customer needs to change password (skip for password/email change routes, 2FA routes, and logout)
        $excludedRoutes = [
            'customer.change-password',
            'customer.change-password.update',
            'customer.family-member.change-password',
            'customer.family-member.password',
            'customer.verify-email-notice',
            'customer.verify-email',
            'customer.resend-verification',
            'customer.logout',
            // 2FA routes should not require password change check
            'customer.two-factor.challenge',
            'customer.two-factor.verify',
        ];

        if (! in_array($request->route()->getName(), $excludedRoutes) && $customer->needsPasswordChange()) {
            return redirect()->route('customer.change-password')
                ->with('warning', 'You must change your password before continuing.');
        }

        return $next($request);
    }
}
