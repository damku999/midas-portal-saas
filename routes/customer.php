<?php

use App\Http\Controllers\Auth\CustomerAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Portal Routes
|--------------------------------------------------------------------------
|
| This file contains all routes for the customer portal functionality
| including authentication, dashboard, policies, quotations, and profile
| management for insurance customers and their families.
|
*/

// Customer Authentication Routes (defined with priority)
Route::prefix('customer')->name('customer.')->group(function () {

    // ==========================================
    // PUBLIC ROUTES (Unauthenticated Access)
    // ==========================================

    // Login Routes with Rate Limiting
    Route::middleware(['throttle:10,1'])->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login']);
    });

    // Password Reset Routes with Enhanced Rate Limiting
    Route::middleware(['throttle:5,1'])->group(function () {
        Route::get('/password/reset', [CustomerAuthController::class, 'showPasswordResetForm'])->name('password.request');
        Route::post('/password/email', [CustomerAuthController::class, 'sendPasswordResetLink'])->name('password.email');
        Route::get('/password/reset/{token}', [CustomerAuthController::class, 'showPasswordResetFormWithToken'])->name('password.reset');
        Route::post('/password/reset', [CustomerAuthController::class, 'resetPassword'])->name('password.update');
    });

    // Email Verification Routes with Strict Rate Limiting
    Route::middleware(['throttle:3,1'])->group(function () {
        Route::get('/email/verify/{token}', [CustomerAuthController::class, 'verifyEmail'])->name('verify-email');
    });

    // ==========================================
    // AUTHENTICATED ROUTES (Customer Login Required)
    // ==========================================

    // Logout Route (Authenticated Customers Only)
    Route::post('/logout', [CustomerAuthController::class, 'logout'])
        ->middleware(['customer.auth'])
        ->name('logout');

    // Core Dashboard Routes (Protected with Session Timeout)
    // Increased rate limit since individual routes have their own more specific limits
    Route::middleware(['customer.auth', 'customer.timeout', 'throttle:200,1'])->group(function () {

        // Main Dashboard
        Route::get('/dashboard', [CustomerAuthController::class, 'dashboard'])->name('dashboard');

        // Profile Management
        Route::get('/profile', [CustomerAuthController::class, 'showProfile'])->name('profile');

        // Password Change Functionality
        Route::get('/change-password', [CustomerAuthController::class, 'showChangePasswordForm'])->name('change-password-form');
        Route::post('/change-password', [CustomerAuthController::class, 'changePassword'])
            ->middleware(['throttle:10,1'])
            ->name('change-password');

        // Email Verification Management
        Route::get('/email/verify-notice', [CustomerAuthController::class, 'showEmailVerificationNotice'])->name('verify-email-notice');
        Route::post('/email/resend', [CustomerAuthController::class, 'resendVerification'])
            ->middleware(['throttle:10,1'])
            ->name('resend-verification');

        // Alternative route name for compatibility (same endpoint, different name)
        Route::post('/email/verification/send', [CustomerAuthController::class, 'resendVerification'])
            ->middleware(['throttle:10,1'])
            ->name('verification.send');

        // Two-Factor Authentication Routes with appropriate rate limits
        Route::prefix('two-factor')->name('two-factor.')->group(function () {
            // 2FA Management Page (can be accessed frequently)
            Route::get('/', [\App\Http\Controllers\TwoFactorAuthController::class, 'index'])
                ->middleware(['throttle:120,1']) // 120 requests per minute for page loads
                ->name('index');

            // Status endpoint (used by AJAX, needs higher limit)
            Route::get('/status', [\App\Http\Controllers\TwoFactorAuthController::class, 'status'])
                ->middleware(['throttle:120,1']) // 120 requests per minute for status checks
                ->name('status');

            // Setup and management actions (moderate rate limit)
            Route::post('/enable', [\App\Http\Controllers\TwoFactorAuthController::class, 'enable'])
                ->middleware(['throttle:10,1'])
                ->name('enable');

            Route::post('/confirm', [\App\Http\Controllers\TwoFactorAuthController::class, 'confirm'])
                ->middleware(['throttle:10,1'])
                ->name('confirm');

            Route::post('/disable', [\App\Http\Controllers\TwoFactorAuthController::class, 'disable'])
                ->middleware(['throttle:15,1']) // Reasonable limit for disable attempts
                ->name('disable');

            Route::post('/recovery-codes', [\App\Http\Controllers\TwoFactorAuthController::class, 'generateRecoveryCodes'])
                ->middleware(['throttle:10,1']) // Increased from 5 to 10 per minute
                ->name('recovery-codes');

            // Device management (moderate rate limit)
            Route::post('/trust-device', [\App\Http\Controllers\TwoFactorAuthController::class, 'trustDevice'])
                ->middleware(['throttle:20,1'])
                ->name('trust-device');

            Route::delete('/trusted-devices/{deviceId}', [\App\Http\Controllers\TwoFactorAuthController::class, 'revokeDevice'])
                ->middleware(['throttle:20,1'])
                ->name('revoke-device');
        });

        // ==========================================
        // 2FA CHALLENGE ROUTES (Outside auth middleware)
        // ==========================================

        // 2FA Challenge Routes (for customer - keep separate route names)
        Route::get('/two-factor-challenge', [\App\Http\Controllers\TwoFactorAuthController::class, 'showVerification'])
            ->middleware(['throttle:30,1'])
            ->name('customer.two-factor.challenge');

        Route::post('/two-factor-challenge', [\App\Http\Controllers\TwoFactorAuthController::class, 'verify'])
            ->middleware(['throttle:6,1'])
            ->name('customer.two-factor.verify');

        // ==========================================
        // FAMILY MEMBER MANAGEMENT (Family Heads Only)
        // ==========================================

        // Family Member Profile Access
        Route::get('/family-member/{member}/profile', [CustomerAuthController::class, 'showFamilyMemberProfile'])
            ->name('family-member.profile');

        // Family Member Password Management
        Route::get('/family-member/{member}/change-password', [CustomerAuthController::class, 'showFamilyMemberPasswordForm'])
            ->name('family-member.change-password');
        Route::put('/family-member/{member}/password', [CustomerAuthController::class, 'updateFamilyMemberPassword'])
            ->middleware(['throttle:10,1'])
            ->name('family-member.password');

        // Family Member 2FA Management (only for family head)
        Route::post('/family-member/{member}/disable-2fa', [CustomerAuthController::class, 'disableFamilyMember2FA'])
            ->middleware(['throttle:5,1'])
            ->name('family-member.disable-2fa');
    });

    // ==========================================
    // FAMILY GROUP ROUTES (Family Membership Required)
    // ==========================================

    // Family-Specific Routes (Require Family Group Membership)
    Route::middleware(['customer.auth', 'customer.timeout', 'customer.family', 'throttle:60,1'])->group(function () {

        // ==========================================
        // INSURANCE POLICIES MANAGEMENT
        // ==========================================

        // Policy Listing and Details
        Route::get('/policies', [CustomerAuthController::class, 'showPolicies'])->name('policies');
        Route::get('/policies/{policy}', [CustomerAuthController::class, 'showPolicyDetail'])->name('policies.detail');

        // Policy Document Downloads (Rate Limited)
        Route::get('/policies/{policy}/download', [CustomerAuthController::class, 'downloadPolicy'])
            ->middleware(['throttle:10,1'])
            ->name('policies.download');

        // ==========================================
        // QUOTATIONS MANAGEMENT
        // ==========================================

        // Quotation Listing and Details
        Route::get('/quotations', [CustomerAuthController::class, 'showQuotations'])->name('quotations');
        Route::get('/quotations/{quotation}', [CustomerAuthController::class, 'showQuotationDetail'])->name('quotations.detail');

        // Quotation Document Downloads (Rate Limited)
        Route::get('/quotations/{quotation}/download', [CustomerAuthController::class, 'downloadQuotation'])
            ->middleware(['throttle:10,1'])
            ->name('quotations.download');

        // ==========================================
        // CLAIMS MANAGEMENT (Read-Only)
        // ==========================================

        // Claims Listing and Details
        Route::get('/view-claims', [CustomerAuthController::class, 'showClaims'])->name('claims');
        Route::get('/view-claims/{claim}', [CustomerAuthController::class, 'showClaimDetail'])->name('claims.detail');
    });
});

/*
|--------------------------------------------------------------------------
| Route Security Notes
|--------------------------------------------------------------------------
|
| 1. Rate Limiting:
|    - Login attempts: 10 per minute
|    - Password reset: 5 per minute
|    - Email verification: 3 per minute
|    - General routes: 60 per minute
|    - Downloads: 10 per minute
|
| 2. Middleware Stack:
|    - customer.auth: Validates customer authentication
|    - customer.timeout: Enforces session timeout
|    - customer.family: Ensures family group membership
|    - throttle: Rate limiting protection
|
| 3. Security Features:
|    - Session timeout enforcement
|    - Rate limiting on sensitive operations
|    - Family group access control
|    - Download throttling
|
*/
