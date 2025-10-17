<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnhancedAuthorizationMiddleware
{
    /**
     * Handle an incoming request with enhanced authorization checks.
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        // Basic authentication check
        if (! Auth::check()) {
            $this->logSecurityEvent('unauthorized_access_attempt', $request);

            return redirect()->route('login')->with('error', 'Authentication required.');
        }

        $user = Auth::user();

        // Check if user account is active
        if (! $user->status) {
            $this->logSecurityEvent('inactive_user_access_attempt', $request, $user);
            Auth::logout();

            return redirect()->route('login')->with('error', 'Your account has been deactivated.');
        }

        // Check for suspicious activity patterns
        if ($this->detectSuspiciousActivity($request, $user)) {
            $this->logSecurityEvent('suspicious_activity_detected', $request, $user);

            // Optional: Lock account or require additional verification
            if (config('security.lock_suspicious_accounts', false)) {
                $user->update(['status' => false]);
                Auth::logout();

                return redirect()->route('login')->with('error', 'Account locked due to suspicious activity.');
            }
        }

        // Permission-based authorization
        if ($permission && ! $user->can($permission)) {
            $this->logSecurityEvent('permission_denied', $request, $user, ['permission' => $permission]);

            return response()->view('errors.403', [
                'message' => 'You do not have permission to access this resource.',
            ], 403);
        }

        // Resource-based authorization (for edit/delete operations)
        if ($this->requiresResourceAuthorization($request)) {
            if (! $this->authorizeResourceAccess($request, $user)) {
                $this->logSecurityEvent('resource_access_denied', $request, $user);

                return response()->view('errors.403', [
                    'message' => 'You do not have permission to access this specific resource.',
                ], 403);
            }
        }

        // IP address validation
        if (config('security.validate_ip_consistency', false)) {
            if (! $this->validateIpConsistency($request, $user)) {
                $this->logSecurityEvent('ip_mismatch_detected', $request, $user);

                // Optional: Force re-authentication
                if (config('security.force_reauth_on_ip_change', false)) {
                    Auth::logout();

                    return redirect()->route('login')->with('warning', 'Please re-authenticate due to IP address change.');
                }
            }
        }

        // Session fingerprint validation
        if (config('security.session_security.fingerprint_validation', true)) {
            if (! $this->validateSessionFingerprint($request)) {
                $this->logSecurityEvent('session_fingerprint_mismatch', $request, $user);
                Auth::logout();

                return redirect()->route('login')->with('error', 'Session security validation failed.');
            }
        }

        // Rate limiting check
        if ($this->isRateLimited($request, $user)) {
            $this->logSecurityEvent('rate_limit_exceeded', $request, $user);

            return response()->view('errors.429', [
                'message' => 'Too many requests. Please slow down.',
            ], 429);
        }

        // Update last activity
        $this->updateUserActivity($user, $request);

        return $next($request);
    }

    /**
     * Detect suspicious activity patterns
     */
    private function detectSuspiciousActivity(Request $request, $user): bool
    {
        // Check for rapid successive requests
        $recentRequests = cache()->get("user_requests_{$user->id}", []);
        $currentTime = now()->timestamp;

        // Filter requests from last minute
        $recentRequests = array_filter($recentRequests, function ($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < 60;
        });

        // Add current request
        $recentRequests[] = $currentTime;
        cache()->put("user_requests_{$user->id}", $recentRequests, 300); // 5 minutes

        // Check if too many requests in short time
        if (count($recentRequests) > config('security.rate_limiting.suspicious_threshold', 100)) {
            return true;
        }

        // Check for unusual access patterns
        $userAgent = $request->header('User-Agent');
        $lastUserAgent = cache()->get("user_agent_{$user->id}");

        if ($lastUserAgent && $lastUserAgent !== $userAgent) {
            // Different user agent - could be suspicious
            cache()->put("user_agent_{$user->id}", $userAgent, 86400); // 24 hours

            return true;
        }

        cache()->put("user_agent_{$user->id}", $userAgent, 86400);

        return false;
    }

    /**
     * Check if request requires resource-level authorization
     */
    private function requiresResourceAuthorization(Request $request): bool
    {
        $route = $request->route();
        if (! $route) {
            return false;
        }

        $routeName = $route->getName();
        $resourceRoutes = ['edit', 'update', 'destroy', 'show'];

        return collect($resourceRoutes)->contains(function ($action) use ($routeName) {
            return str_contains($routeName, $action);
        });
    }

    /**
     * Authorize access to specific resource
     */
    private function authorizeResourceAccess(Request $request, $user): bool
    {
        $route = $request->route();
        $parameters = $route->parameters();

        // For customer resources, check if user can access the specific customer
        if (array_key_exists('customer', $parameters)) {
            $customer = $parameters['customer'];

            // Super admin can access all customers
            if ($user->hasRole('super-admin')) {
                return true;
            }

            // Branch managers can only access customers in their branch
            if ($user->hasRole('branch-manager')) {
                return $customer->branch_id === $user->branch_id;
            }

            // Agents can only access customers assigned to them
            if ($user->hasRole('agent')) {
                return $customer->assigned_agent_id === $user->id;
            }
        }

        // Similar logic for other resources (claims, policies, etc.)
        if (array_key_exists('claim', $parameters)) {
            $claim = $parameters['claim'];

            if ($user->hasRole('super-admin')) {
                return true;
            }

            if ($user->hasRole('branch-manager')) {
                return $claim->customer->branch_id === $user->branch_id;
            }

            if ($user->hasRole('agent')) {
                return $claim->customer->assigned_agent_id === $user->id;
            }
        }

        return true; // Default allow for non-restricted resources
    }

    /**
     * Validate IP address consistency
     */
    private function validateIpConsistency(Request $request, $user): bool
    {
        $currentIp = $request->ip();
        $lastIp = cache()->get("user_ip_{$user->id}");

        if ($lastIp && $lastIp !== $currentIp) {
            // IP changed - potentially suspicious
            cache()->put("user_ip_{$user->id}", $currentIp, 86400);

            return false;
        }

        cache()->put("user_ip_{$user->id}", $currentIp, 86400);

        return true;
    }

    /**
     * Validate session fingerprint
     */
    private function validateSessionFingerprint(Request $request): bool
    {
        $currentFingerprint = $this->generateFingerprint($request);
        $sessionFingerprint = session('security_fingerprint');

        if ($sessionFingerprint && $sessionFingerprint !== $currentFingerprint) {
            return false;
        }

        if (! $sessionFingerprint) {
            session(['security_fingerprint' => $currentFingerprint]);
        }

        return true;
    }

    /**
     * Generate session fingerprint
     */
    private function generateFingerprint(Request $request): string
    {
        $components = [
            $request->header('User-Agent'),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
        ];

        return hash('sha256', implode('|', $components));
    }

    /**
     * Check if user is rate limited
     */
    private function isRateLimited(Request $request, $user): bool
    {
        $key = "rate_limit_{$user->id}";
        $attempts = cache()->get($key, 0);
        $maxAttempts = config('security.rate_limiting.max_requests', 1000);
        $window = config('security.rate_limiting.window', 3600); // 1 hour

        if ($attempts >= $maxAttempts) {
            return true;
        }

        cache()->put($key, $attempts + 1, $window);

        return false;
    }

    /**
     * Update user last activity
     */
    private function updateUserActivity($user, Request $request): void
    {
        $user->update([
            'last_activity_at' => now(),
            'last_ip_address' => $request->ip(),
        ]);
    }

    /**
     * Log security events
     */
    private function logSecurityEvent(string $event, Request $request, $user = null, array $context = []): void
    {
        $logData = [
            'event' => $event,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $user?->id,
            'session_id' => session()->getId(),
            'timestamp' => now()->toISOString(),
        ];

        Log::channel('security')->warning("Security Event: {$event}", array_merge($logData, $context));

        // Store in database for monitoring
        if (config('security.monitoring.log_to_database', true)) {
            \DB::table('security_events')->insert([
                'event_type' => $event,
                'user_id' => $user?->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'url' => $request->fullUrl(),
                'context' => json_encode($context),
                'created_at' => now(),
            ]);
        }
    }
}
