<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * Implements guard-specific redirect logic for multi-tenant architecture:
     * - Central guard → Central admin dashboard (/midas-admin/dashboard)
     * - Customer guard → Customer portal dashboard (/customer/dashboard)
     * - Web guard (default/staff) → Staff portal home (/home)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // ================================================================
                // GUARD-SPECIFIC REDIRECTS
                // ================================================================

                // Central Admin Guard - redirect to central admin dashboard
                if ($guard === 'central') {
                    return redirect()->route('central.dashboard');
                }

                // Customer Portal Guard - redirect to customer dashboard
                if ($guard === 'customer') {
                    return redirect()->route('customer.dashboard');
                }

                // Staff Portal Guard (web/default) - redirect to staff home
                // Also handles null guard which defaults to 'web'
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
