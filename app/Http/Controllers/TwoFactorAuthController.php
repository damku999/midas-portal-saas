<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Services\CustomerTwoFactorAuthService;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class TwoFactorAuthController extends Controller
{
    public function __construct(
        private readonly TwoFactorAuthService $twoFactorAuthService,
        private readonly CustomerTwoFactorAuthService $customerTwoFactorAuthService
    ) {
        // Support both web and customer guards
        $this->middleware(['auth:web,customer'])->except(['showVerification', 'verify']);
    }

    /**
     * Get the appropriate 2FA service based on the authenticated user type
     */
    private function getTwoFactorService()
    {
        // During 2FA challenge, check session guard first
        if (session()->has('2fa_guard')) {
            $guard = session('2fa_guard', 'web');
            if ($guard === 'customer') {
                return $this->customerTwoFactorAuthService;
            }
        }

        // For authenticated requests, check current guard
        if (Auth::guard('customer')->check()) {
            return $this->customerTwoFactorAuthService;
        }

        return $this->twoFactorAuthService;
    }

    /**
     * Get the authenticated user from the appropriate guard
     */
    public function getAuthenticatedUser()
    {
        // Check if customer guard is authenticated (customer portal)
        if (Auth::guard('customer')->check()) {
            return Auth::guard('customer')->user();
        }

        // Default to web guard (admin portal)
        return Auth::guard('web')->user();
    }

    /**
     * Get the appropriate guard name based on current authentication
     */
    public function getGuardName(): string
    {
        return Auth::guard('customer')->check() ? 'customer' : 'web';
    }

    /**
     * Show 2FA settings page
     */
    public function index(): View
    {
        $user = $this->getAuthenticatedUser();
        $service = $this->getTwoFactorService();

        $status = $service->getStatus($user);
        $trustedDevices = $service->getTrustedDevices($user);

        // Use different views based on guard to ensure zero conflicts
        $guardName = $this->getGuardName();
        $viewData = ['status' => $status, 'trustedDevices' => $trustedDevices];

        if ($guardName === 'customer') {
            // Customer portal uses separate view with customer layout
            return view('customer.two-factor', $viewData);
        }

        // Admin portal uses original view with admin layout
        return view('profile.two-factor', $viewData);
    }

    /**
     * Enable 2FA - Start setup process
     */
    public function enable(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();
            $guardName = $this->getGuardName();

            Log::info('🔧 [2FA Enable Controller] Starting 2FA enable process', [
                'user_id' => $user->id,
                'user_type' => $user::class,
                'guard' => $guardName,
                'request_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Log::info('🔧 [2FA Enable Controller] User loaded', [
                'user_id' => $user->id,
                'user_email' => $user->email ?? 'N/A',
                'guard' => $guardName,
                'has_2fa_enabled' => $guardName === 'customer' ? $user->hasCustomerTwoFactorEnabled() : $user->hasTwoFactorEnabled(),
            ]);

            $service = $this->getTwoFactorService();
            $result = $service->enableTwoFactor($user);

            Log::info('✅ [2FA Enable Controller] Service call successful', [
                'user_id' => $user->id,
                'qr_code_present' => isset($result['qr_code_svg']) && ! empty($result['qr_code_svg']),
                'recovery_codes_count' => isset($result['recovery_codes']) ? count($result['recovery_codes']) : 0,
                'setup_url_present' => isset($result['qr_code_url']) && ! empty($result['qr_code_url']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication setup started. Please scan the QR code with your authenticator app.',
                'data' => [
                    'qr_code_svg' => $result['qr_code_svg'],
                    'recovery_codes' => $result['recovery_codes'],
                    'setup_url' => $result['qr_code_url'],
                ],
            ]);
        } catch (\Exception $exception) {
            $user = $this->getAuthenticatedUser();
            Log::error('🚨 [2FA Enable Controller] Exception occurred', [
                'user_id' => $user ? $user->id : null,
                'user_type' => $user ? $user::class : null,
                'guard' => $this->getGuardName(),
                'error_message' => $exception->getMessage(),
                'error_file' => $exception->getFile(),
                'error_line' => $exception->getLine(),
                'stack_trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Confirm 2FA setup with verification code
     */
    public function confirm(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6|regex:/^[0-9]{6}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid 6-digit verification code.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $this->getAuthenticatedUser();
            $service = $this->getTwoFactorService();
            $service->confirmTwoFactor($user, $request->code, $request);

            // Customer-specific service handles security settings internally
            // No need for separate enableTwoFactorInSettings() call

            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication has been successfully enabled for your account.',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'confirmation' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide your current password and confirm the action.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $this->getAuthenticatedUser();
            $service = $this->getTwoFactorService();

            // Check current 2FA status first
            $status = $service->getStatus($user);
            if (! $status['enabled']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Two-factor authentication is already disabled for your account.',
                ], 400);
            }

            $service->disableTwoFactor($user, $request->current_password);

            // Customer-specific service handles security settings internally
            // No need for separate disableTwoFactorInSettings() call

            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication has been disabled for your account.',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Generate new recovery codes
     */
    public function generateRecoveryCodes(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide your current password.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $this->getAuthenticatedUser();

            // Verify current password
            if (! $user->checkPassword($request->current_password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.',
                ], 400);
            }

            $service = $this->getTwoFactorService();
            $codes = $service->generateNewRecoveryCodes($user);

            return response()->json([
                'success' => true,
                'message' => 'New recovery codes have been generated. Please store them safely.',
                'data' => [
                    'recovery_codes' => $codes,
                ],
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Trust current device
     */
    public function trustDevice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid device name.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $this->getAuthenticatedUser();
            $service = $this->getTwoFactorService();
            $result = $service->trustDevice(
                $user,
                $request,
                30 // trust for 30 days
            );

            // Handle different response formats between admin and customer services
            if (isset($result['device'])) {
                // Admin service format
                $device = $result['device'];
                $wasAlreadyTrusted = $result['was_already_trusted'];

                $message = $wasAlreadyTrusted
                    ? 'This device is already trusted and has been updated.'
                    : 'This device has been added to your trusted devices list.';

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'device' => [
                            'id' => $device->id,
                            'name' => $device->device_name,
                            'display_name' => $device->getDisplayName(),
                            'trusted_at' => $device->trusted_at->format('M j, Y g:i A'),
                        ],
                        'was_already_trusted' => $wasAlreadyTrusted,
                    ],
                ]);
            }

            // Customer service format
            return response()->json([
                'success' => true,
                'message' => 'This device has been added to your trusted devices list.',
                'data' => [
                    'device_name' => $result['device_name'],
                    'expires_at' => $result['expires_at'],
                ],
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Revoke device trust
     */
    public function revokeDevice(Request $request, int $deviceId): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUser();
            $service = $this->getTwoFactorService();
            $success = $service->revokeDeviceTrust($user, (string) $deviceId);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found or already revoked.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Device trust has been revoked.',
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Get 2FA status for AJAX requests
     */
    public function status(): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        $service = $this->getTwoFactorService();
        $status = $service->getStatus($user);
        $trustedDevices = $service->getTrustedDevices($user);

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $status,
                'trusted_devices' => $trustedDevices,
                'current_device_trusted' => $service->isDeviceTrusted($user, request()),
            ],
        ]);
    }

    /**
     * Show verification form during login (for 2FA challenge)
     */
    public function showVerification(Request $request): View
    {
        Log::info('🔐 [2FA Challenge] showVerification accessed', [
            'session_id' => session()->getId(),
            'session_2fa_user_id' => session('2fa_user_id'),
            'session_2fa_guard' => session('2fa_guard'),
            'session_2fa_remember' => session('2fa_remember'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // SIMPLIFIED 2FA CHECKS (SAME AS ADMIN)
        $userId = session('2fa_user_id');
        $guard = session('2fa_guard', 'web');

        // Check if 2FA session exists (SIMPLE CHECK LIKE ADMIN)
        if (! $userId) {
            // Redirect based on guard (SIMPLE LIKE ADMIN)
            if ($guard === 'customer') {
                return redirect()->route('customer.login')
                    ->with('error', 'Your 2FA session has expired. Please login again.');
            }

            return redirect()->route('login')
                ->with('error', 'Your 2FA session has expired. Please login again.');
        }

        // Get user from session (SIMPLE LIKE ADMIN)
        $user = $guard === 'customer'
            ? Customer::query()->find($userId)
            : User::query()->find($userId);

        if (! $user) {
            session()->forget(['2fa_user_id', '2fa_guard', '2fa_remember']);
            $loginRoute = $guard === 'customer' ? 'customer.login' : 'login';

            return redirect()->route($loginRoute)->with('error', 'User account not found. Please login again.');
        }

        // Use different views based on guard
        if ($guard === 'customer') {
            return view('customer.auth.two-factor-challenge');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request): RedirectResponse
    {
        // Enhanced validation with additional security checks
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|min:6|max:8',
            'code_type' => 'required|in:totp,recovery',
        ]);

        if ($validator->fails()) {
            // Log failed validation attempt
            Log::warning('2FA verification validation failed', [
                'errors' => $validator->errors(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => session()->getId(),
            ]);

            return back()->withErrors($validator)->withInput();
        }

        try {
            $userId = session('2fa_user_id');
            $guard = session('2fa_guard', 'web');

            // Enhanced session validation
            if (! $userId || ! session()->has('2fa_user_id')) {
                Log::warning('2FA verification attempt without valid session', [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'session_id' => session()->getId(),
                    'has_user_id' => ! is_null($userId),
                    'session_keys' => array_keys(session()->all()),
                ]);

                $loginRoute = $guard === 'customer' ? 'customer.login' : 'login';

                return redirect()->route($loginRoute)->withErrors([
                    'code' => 'Session expired. Please login again.',
                ]);
            }

            // Get user from appropriate guard with additional validation
            $user = $guard === 'customer'
                ? Customer::query()->find($userId)
                : User::query()->find($userId);

            if (! $user) {
                Log::error('2FA verification: User not found', [
                    'user_id' => $userId,
                    'guard' => $guard,
                    'ip_address' => $request->ip(),
                    'session_id' => session()->getId(),
                ]);

                $loginRoute = $guard === 'customer' ? 'customer.login' : 'login';

                return redirect()->route($loginRoute)->withErrors([
                    'code' => 'User not found. Please login again.',
                ]);
            }

            // Additional security check: verify user is still active
            if (method_exists($user, 'isActive') && ! $user->isActive()) {
                Log::warning('2FA verification attempt for inactive user', [
                    'user_id' => $user->id,
                    'guard' => $guard,
                    'ip_address' => $request->ip(),
                ]);

                $loginRoute = $guard === 'customer' ? 'customer.login' : 'login';

                return redirect()->route($loginRoute)->withErrors([
                    'code' => 'Account is no longer active. Please contact support.',
                ]);
            }

            // Verify 2FA code with enhanced logging
            Log::info('2FA verification attempt', [
                'user_id' => $user->id,
                'guard' => $guard,
                'code_type' => $request->code_type,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Use the correct service based on guard
            $service = $this->getTwoFactorService();
            $service->verifyTwoFactorLogin(
                $user,
                $request->code,
                $request->code_type,
                $request
            );

            // Clear 2FA session data (SIMPLE LIKE ADMIN)
            $rememberMe = session('2fa_remember', false);
            session()->forget(['2fa_user_id', '2fa_guard', '2fa_remember']);

            // Complete login with the correct guard (SAME AS ADMIN)
            Auth::guard($guard)->login($user, $rememberMe);

            // Trust device if requested (both admin and customer)
            if ($request->has('trust_device') && $request->trust_device) {
                $service->trustDevice($user, $request);
            }

            // Log successful 2FA completion
            Log::info('2FA verification successful', [
                'user_id' => $user->id,
                'guard' => $guard,
                'trusted_device' => $request->has('trust_device'),
            ]);

            // Redirect to intended location
            $redirectTo = $guard === 'customer' ? route('customer.dashboard') : route('home');

            return redirect()->intended($redirectTo);

        } catch (\Exception $exception) {
            // Enhanced error logging
            Log::error('2FA verification failed', [
                'user_id' => $userId ?? null,
                'guard' => $guard ?? 'unknown',
                'error_message' => $exception->getMessage(),
                'error_file' => $exception->getFile(),
                'error_line' => $exception->getLine(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => session()->getId(),
            ]);

            // For customer errors, use back() to preserve session state
            // For admin errors, redirect to the specific route
            if ($guard === 'customer') {
                return back()->withErrors([
                    'code' => $exception->getMessage(),
                ])->withInput();
            }

            return redirect(route('two-factor.challenge'))->withErrors([
                'code' => $exception->getMessage(),
            ])->withInput();
        }
    }
}
