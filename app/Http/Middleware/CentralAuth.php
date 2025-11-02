<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CentralAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role = null): Response
    {
        // Check if user is authenticated with central guard
        if (!Auth::guard('central')->check()) {
            return redirect()->route('central.login');
        }

        $user = Auth::guard('central')->user();

        // Check if user is active
        if (!$user->is_active) {
            Auth::guard('central')->logout();
            return redirect()->route('central.login')
                ->with('error', 'Your account has been deactivated.');
        }

        // Check role-based permissions
        if ($role) {
            $hasPermission = match($role) {
                'super' => $user->isSuperAdmin(),
                'support' => $user->isSupportAdmin(),
                'billing' => $user->isBillingAdmin(),
                default => false,
            };

            if (!$hasPermission) {
                abort(403, 'Unauthorized action.');
            }
        }

        // Set default guard for this request
        Auth::shouldUse('central');

        return $next($request);
    }
}
