<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecureSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Apply secure session settings for customer routes
        if (Auth::guard('customer')->check()) {
            $this->enforceSecureSession($request);
            // $this->checkSessionTimeout($request); // Handled by CustomerSessionTimeout middleware
            $integrityResult = $this->validateSessionIntegrity($request);
            if ($integrityResult) {
                return $integrityResult;
            }
        }

        $response = $next($request);

        // Set secure headers for customer pages
        if (Auth::guard('customer')->check()) {
            $this->setSecureHeaders($response);
            $this->updateLastActivity($request);
        }

        return $response;
    }

    /**
     * Enforce secure session configuration.
     */
    protected function enforceSecureSession(Request $request): void
    {
        // Regenerate session ID periodically for security (but NOT during 2FA challenge)
        if (! $request->session()->has('last_regenerated') ||
            now()->diffInMinutes($request->session()->get('last_regenerated')) > 30) {

            // Don't regenerate session if we're in 2FA challenge state
            if (! $request->session()->has('2fa_user_id')) {
                $request->session()->regenerate();
                $request->session()->put('last_regenerated', now());

                Log::info('Customer session regenerated for security', [
                    'customer_id' => Auth::guard('customer')->id(),
                    'session_id' => $request->session()->getId(),
                ]);
            } else {
                Log::info('Skipping session regeneration during 2FA challenge', [
                    '2fa_user_id' => $request->session()->get('2fa_user_id'),
                    'session_id' => $request->session()->getId(),
                ]);
            }
        }
    }

    /**
     * Check if session has timed out.
     */
    protected function checkSessionTimeout(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        $lastActivity = $request->session()->get('customer_last_activity');

        // Customer session timeout (60 minutes)
        $timeoutMinutes = config('session.customer_timeout', 60);

        if ($lastActivity && now()->diffInMinutes($lastActivity) > $timeoutMinutes) {
            Log::warning('Customer session timed out', [
                'customer_id' => $customer->id,
                'last_activity' => $lastActivity,
                'timeout_minutes' => $timeoutMinutes,
            ]);

            Auth::guard('customer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirect with timeout message
            return redirect()->route('customer.login')
                ->with('error', 'Your session has timed out due to inactivity. Please log in again.');
        }

        return null;
    }

    /**
     * Validate session integrity.
     */
    protected function validateSessionIntegrity(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        // Check if customer status changed
        if (! $customer->status) {
            Log::warning('Inactive customer session terminated', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
            ]);

            Auth::guard('customer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('customer.login')
                ->with('error', 'Your account has been deactivated.');
        }

        // Check if family group became inactive
        if ($customer->familyGroup && ! $customer->familyGroup->status) {
            Log::warning('Customer session terminated due to inactive family group', [
                'customer_id' => $customer->id,
                'family_group_id' => $customer->familyGroup->id,
            ]);

            Auth::guard('customer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('customer.login')
                ->with('error', 'Your family group has been deactivated.');
        }

        return null;
    }

    /**
     * Set secure headers for customer responses.
     */
    protected function setSecureHeaders(Response $response): void
    {
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Cache control for customer pages
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
    }

    /**
     * Update last activity timestamp.
     */
    protected function updateLastActivity(Request $request): void
    {
        $request->session()->put('customer_last_activity', now());
    }
}
