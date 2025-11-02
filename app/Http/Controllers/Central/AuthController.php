<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the central admin login form.
     */
    public function showLogin()
    {
        return view('central.auth.login');
    }

    /**
     * Handle central admin login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting
        $key = 'central-login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        // Attempt authentication
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::guard('central')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::guard('central')->user();

            // Check if user is active
            if (!$user->is_active) {
                Auth::guard('central')->logout();
                throw ValidationException::withMessages([
                    'email' => ['Your account has been deactivated.'],
                ]);
            }

            // Check if user has admin permissions
            if (!$user->isSuperAdmin() && !$user->isSupportAdmin() && !$user->isBillingAdmin()) {
                Auth::guard('central')->logout();
                throw ValidationException::withMessages([
                    'email' => ['You do not have permission to access the admin panel.'],
                ]);
            }

            // Update last login
            $user->updateLastLogin($request->ip());

            // Log successful login
            AuditLog::log(
                'auth.login',
                "User logged in: {$user->email}",
                $user
            );

            RateLimiter::clear($key);

            return redirect()->intended(route('central.dashboard'));
        }

        // Failed login attempt
        RateLimiter::hit($key, 300); // 5 minutes

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Handle central admin logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('central')->user();

        if ($user) {
            AuditLog::log(
                'auth.logout',
                "User logged out: {$user->email}",
                $user
            );
        }

        Auth::guard('central')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('central.login');
    }
}
