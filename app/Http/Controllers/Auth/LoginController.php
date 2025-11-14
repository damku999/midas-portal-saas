<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Validate the user login request.
     */
    protected function validateLogin(Request $request)
    {
        $rules = [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ];

        // Only require CAPTCHA in production environment
        if (app()->environment('production')) {
            $rules['cf-turnstile-response'] = ['required', Rule::turnstile()];
        }

        $request->validate($rules);
    }

    /**
     * Send the response after the user was authenticated.
     */
    protected function sendLoginResponse(Request $request)
    {
        $user = Auth::user();

        // Clear login attempts first
        $this->clearLoginAttempts($request);

        // Check if user has 2FA enabled and confirmed
        // Check if device is already trusted
        if ($user && method_exists($user, 'hasTwoFactorEnabled') && $user->hasTwoFactorEnabled() && (method_exists($user, 'isDeviceTrusted') && ! $user->isDeviceTrusted($request))) {
            // Store user info in session for 2FA challenge
            $request->session()->put([
                '2fa_user_id' => $user->id,
                '2fa_guard' => 'web',
                '2fa_remember' => $request->boolean('remember'),
            ]);
            // Save session immediately
            $request->session()->save();
            // Logout the user temporarily (they'll be logged back in after 2FA)
            Auth::logout();

            // Redirect to 2FA challenge
            return redirect()->route('two-factor.challenge')
                ->with('info', 'Please enter your two-factor authentication code.');
        }

        // Default behavior - proceed with normal login (regenerate session for normal logins)
        $request->session()->regenerate();

        return $this->authenticated($request, $user)
                ?: redirect()->intended($this->redirectPath());
    }
}
