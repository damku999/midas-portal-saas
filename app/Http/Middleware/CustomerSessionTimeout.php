<?php

namespace App\Http\Middleware;

use App\Models\CustomerAuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerSessionTimeout
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to authenticated customer sessions
        if (! Auth::guard('customer')->check()) {
            return $next($request);
        }

        $customer = Auth::guard('customer')->user();
        $sessionTimeoutMinutes = config('session.customer_timeout', 60);
        $lastActivity = session('customer_last_activity');

        // Skip timeout check for critical operations to prevent interruption
        if ($this->shouldSkipTimeoutCheck($request)) {
            // Update activity timestamp and continue
            session(['customer_last_activity' => now()->format('Y-m-d H:i:s')]);

            return $next($request);
        }

        // If no last activity recorded, set it now
        if (! $lastActivity) {
            session(['customer_last_activity' => now()->format('Y-m-d H:i:s')]);

            return $next($request);
        }

        // Handle both timestamp and datetime string formats
        try {
            if (is_numeric($lastActivity)) {
                // Handle Unix timestamp format
                if ($lastActivity < 0 || $lastActivity > now()->timestamp + 3600) {
                    throw new \Exception('Invalid timestamp range');
                }
                $lastActivityTime = \Carbon\Carbon::createFromTimestamp($lastActivity);
            } else {
                // Handle datetime string format
                $lastActivityTime = \Carbon\Carbon::parse($lastActivity);
            }
        } catch (\Exception $e) {
            \Log::warning('Invalid session activity timestamp detected, resetting', [
                'customer_id' => $customer->id,
                'invalid_timestamp' => $lastActivity,
                'route' => $request->route()?->getName(),
                'error' => $e->getMessage(),
            ]);

            // Reset to current time and continue
            session(['customer_last_activity' => now()->format('Y-m-d H:i:s')]);

            return $next($request);
        }
        $timeoutThreshold = now()->subMinutes($sessionTimeoutMinutes);

        // Debug logging for timeout check
        \Log::info('Session timeout check', [
            'customer_id' => $customer->id,
            'route' => $request->route()?->getName(),
            'last_activity' => $lastActivityTime->toDateTimeString(),
            'timeout_threshold' => $timeoutThreshold->toDateTimeString(),
            'minutes_since_activity' => $lastActivityTime->diffInMinutes(now()),
            'timeout_minutes' => $sessionTimeoutMinutes,
            'will_timeout' => $lastActivityTime->isBefore($timeoutThreshold),
        ]);

        // Check if session has timed out
        if ($lastActivityTime->isBefore($timeoutThreshold)) {
            // Log session timeout
            CustomerAuditLog::create([
                'customer_id' => $customer->id,
                'action' => 'session_timeout',
                'description' => 'Session expired due to inactivity',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => session()->getId(),
                'success' => false,
                'failure_reason' => 'Session timeout due to inactivity',
                'metadata' => [
                    'last_activity' => $lastActivityTime->toDateTimeString(),
                    'timeout_minutes' => $sessionTimeoutMinutes,
                    'inactive_duration_minutes' => $lastActivityTime->diffInMinutes(now()),
                    'security_action' => 'forced_logout',
                ],
            ]);

            // Force logout and clear all session data
            Auth::guard('customer')->logout();

            // Clear session data before invalidating
            $request->session()->flush();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Handle AJAX requests differently
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Session expired',
                    'message' => 'Your session has expired. Please refresh and log in again.',
                    'redirect' => route('customer.login'),
                ], 401);
            }

            // Redirect to login with timeout message
            return redirect()->route('customer.login')
                ->with('warning', 'Your session has expired due to inactivity. Please log in again.');
        }

        // Update last activity timestamp for active sessions
        session(['customer_last_activity' => now()->format('Y-m-d H:i:s')]);

        // Add session metadata to response headers for debugging (in development only)
        $response = $next($request);

        // Debug: Log successful middleware completion
        if ($request->route()?->getName() === 'customer.change-password') {
            \Log::info('CustomerSessionTimeout middleware completed successfully', [
                'customer_id' => $customer->id,
                'route' => $request->route()?->getName(),
                'response_status' => $response->getStatusCode(),
                'is_redirect' => $response->isRedirection(),
                'redirect_location' => $response->headers->get('Location'),
            ]);
        }

        if (config('app.debug')) {
            $response->headers->set('X-Session-Timeout', $sessionTimeoutMinutes);
            $response->headers->set('X-Last-Activity', $lastActivityTime->toDateTimeString());
            $response->headers->set('X-Time-Remaining', $timeoutThreshold->addMinutes($sessionTimeoutMinutes)->diffInMinutes(now()));
        }

        return $response;
    }

    /**
     * Determine if timeout check should be skipped for critical operations.
     */
    protected function shouldSkipTimeoutCheck(Request $request): bool
    {
        // Skip timeout for critical security operations that should not be interrupted
        $criticalRoutes = [
            'customer.change-password.update',
            'customer.password.update',
            'customer.logout',
            'customer.verification.send',
        ];

        $routeName = $request->route()?->getName();

        // Skip for critical routes
        if (in_array($routeName, $criticalRoutes)) {
            return true;
        }

        // Skip for POST requests to password/security related endpoints
        if ($request->isMethod('POST')) {
            $path = $request->path();
            $securityPaths = [
                'customer/change-password',
                'customer/password/reset',
                'customer/password/email',
                'customer/logout',
            ];

            foreach ($securityPaths as $securityPath) {
                if (str_contains($path, $securityPath)) {
                    return true;
                }
            }
        }

        // Skip if this is an AJAX request (likely form validation or immediate action)
        if ($request->ajax() || $request->wantsJson()) {
            return true;
        }

        return false;
    }
}
