<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyTwoFactorSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only apply to 2FA challenge routes
        if (! $request->routeIs('*.two-factor.challenge', '*.two-factor.verify')) {
            return $next($request);
        }

        $userId = session('2fa_user_id');
        $guard = session('2fa_guard', 'web');

        Log::info('🔐 [2FA Middleware] Session verification', [
            'route' => $request->route()->getName(),
            'session_id' => session()->getId(),
            'has_2fa_user_id' => ! empty($userId),
            '2fa_user_id' => $userId,
            '2fa_guard' => $guard,
            'session_keys' => array_keys(session()->all()),
            'request_method' => $request->method(),
            'ip' => $request->ip(),
        ]);

        return $next($request);
    }
}
