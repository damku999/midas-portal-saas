<?php

use App\Http\Controllers\Api\CustomerDeviceApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
|--------------------------------------------------------------------------
| Customer API Routes (Mobile App)
|--------------------------------------------------------------------------
|
| Authentication: Laravel Sanctum
| Base URL: /api/customer
|
| These routes are for the mobile app (Android/iOS/Web) to interact
| with the backend for customer-specific operations including device
| registration for push notifications.
|
| SECURITY FIX #9: Added rate limiting to prevent API abuse
|
*/

Route::prefix('customer')->name('api.customer.')->group(function () {

    // ==========================================
    // AUTHENTICATED CUSTOMER ROUTES
    // ==========================================

    Route::middleware(['auth:sanctum'])->group(function () {

        // ==========================================
        // DEVICE MANAGEMENT (Push Notifications)
        // ==========================================

        // SECURITY: Strict rate limiting for device registration (10 requests per minute)
        // Register device for push notifications
        Route::post('/device/register', [CustomerDeviceApiController::class, 'register'])
            ->middleware('throttle:10,1')
            ->name('device.register');

        // SECURITY: Standard rate limiting for device operations (60 requests per minute)
        // Unregister device from push notifications
        Route::post('/device/unregister', [CustomerDeviceApiController::class, 'unregister'])
            ->middleware('throttle:60,1')
            ->name('device.unregister');

        // Update device information
        Route::put('/device/update', [CustomerDeviceApiController::class, 'update'])
            ->middleware('throttle:60,1')
            ->name('device.update');

        // Get all registered devices for customer
        Route::get('/devices', [CustomerDeviceApiController::class, 'index'])
            ->middleware('throttle:60,1')
            ->name('devices.index');

        // Deactivate specific device (SECURITY FIX #12: Changed parameter name for explicit IDOR protection)
        Route::post('/device/{deviceId}/deactivate', [CustomerDeviceApiController::class, 'deactivate'])
            ->middleware('throttle:60,1')
            ->name('device.deactivate');

        // SECURITY: Higher rate limit for heartbeat (120 requests per minute)
        // Send heartbeat to keep device active
        Route::post('/device/heartbeat', [CustomerDeviceApiController::class, 'heartbeat'])
            ->middleware('throttle:120,1')
            ->name('device.heartbeat');

        // ==========================================
        // FUTURE: Customer Profile, Policies, etc.
        // ==========================================

        // Additional customer API endpoints can be added here with appropriate rate limiting
        // Examples:
        // Route::get('/profile', [CustomerApiController::class, 'profile'])
        //     ->middleware('throttle:60,1');
        // Route::get('/policies', [CustomerPolicyApiController::class, 'index'])
        //     ->middleware('throttle:60,1');
        // Route::get('/quotations', [CustomerQuotationApiController::class, 'index'])
        //     ->middleware('throttle:60,1');
        // Route::get('/claims', [CustomerClaimApiController::class, 'index'])
        //     ->middleware('throttle:60,1');

    });

});

/*
|--------------------------------------------------------------------------
| API Documentation
|--------------------------------------------------------------------------
|
| Device Registration Flow:
|
| 1. Customer logs in via mobile app
| 2. App receives authentication token (Sanctum)
| 3. App requests FCM token from device
| 4. App calls POST /api/customer/device/register with:
|    - device_token (FCM token)
|    - device_type (android/ios/web)
|    - device_name, device_model, os_version, app_version
| 5. Backend stores device in customer_devices table
| 6. Customer can now receive push notifications
|
| Push Notification Flow:
|
| 1. Backend triggers notification (e.g., policy renewal reminder)
| 2. PushNotificationService.sendToCustomer() called
| 3. Service fetches all active devices for customer
| 4. Sends push to each device via FCM
| 5. Creates notification_logs entry (channel='push')
| 6. Mobile app receives push notification
| 7. User taps notification → app deep links to relevant screen
|
| Rate Limiting:
|
| All routes are protected by Laravel's default rate limiting.
| Adjust in app/Http/Kernel.php or RouteServiceProvider if needed.
|
| Authentication:
|
| All routes require Laravel Sanctum authentication.
| Mobile app must include Bearer token in Authorization header:
|
| Authorization: Bearer {sanctum_token}
|
*/
