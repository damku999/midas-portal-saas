<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerDevice;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Customer Device API Controller
 *
 * Handles device registration and management for push notifications.
 * Used by mobile apps (Android/iOS/Web) to register FCM tokens.
 *
 * Authentication: Requires customer authentication via Sanctum
 */
class CustomerDeviceApiController extends Controller
{
    public function __construct(
        protected PushNotificationService $pushService
    ) {}

    /**
     * Register device for push notifications
     *
     * POST /api/customer/device/register
     *
     * Registers a new device or updates existing device token.
     * If device token already exists, updates the device info.
     *
     * @bodyParam device_token string required FCM device token (max 500 chars)
     * @bodyParam device_type string required Device platform: android, ios, or web
     * @bodyParam device_name string optional Human-readable device name (e.g., "John's iPhone")
     * @bodyParam device_model string optional Device model (e.g., "iPhone 13 Pro", "Samsung Galaxy S21")
     * @bodyParam os_version string optional OS version (e.g., "iOS 16.2", "Android 13")
     * @bodyParam app_version string optional App version (e.g., "1.0.0")
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Device registered successfully",
     *   "device": {
     *     "id": 1,
     *     "customer_id": 123,
     *     "device_type": "android",
     *     "device_token": "FCM_TOKEN...",
     *     "device_name": "John's Phone",
     *     "device_model": "Samsung Galaxy S21",
     *     "os_version": "Android 13",
     *     "app_version": "1.0.0",
     *     "is_active": true,
     *     "last_active_at": "2025-10-31 10:00:00",
     *     "created_at": "2025-10-31 10:00:00"
     *   }
     * }
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_token' => 'required|string|max:500',
                'device_type' => ['required', Rule::in(['android', 'ios', 'web'])],
                'device_name' => 'nullable|string|max:125',
                'device_model' => 'nullable|string|max:125',
                'os_version' => 'nullable|string|max:125',
                'app_version' => 'nullable|string|max:125',
            ]);

            $customerId = auth('sanctum')->id();

            // Check device limit per customer
            $maxDevices = config('push.device_management.max_devices_per_customer', 5);
            $existingDevices = CustomerDevice::where('customer_id', $customerId)
                ->where('is_active', true)
                ->count();

            // Check if this token already exists for this customer
            $existingDevice = CustomerDevice::where('device_token', $validated['device_token'])
                ->where('customer_id', $customerId)
                ->first();

            if (! $existingDevice && $existingDevices >= $maxDevices) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf('Maximum %d devices allowed per customer', $maxDevices),
                    'error' => 'device_limit_reached',
                ], 422);
            }

            $device = $this->pushService->registerDevice(
                $customerId,
                $validated['device_token'],
                $validated['device_type'],
                [
                    'device_name' => $validated['device_name'] ?? null,
                    'device_model' => $validated['device_model'] ?? null,
                    'os_version' => $validated['os_version'] ?? null,
                    'app_version' => $validated['app_version'] ?? null,
                ]
            );

            Log::info('Device registered successfully', [
                'customer_id' => $customerId,
                'device_id' => $device->id,
                'device_type' => $validated['device_type'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device registered successfully',
                'device' => [
                    'id' => $device->id,
                    'customer_id' => $device->customer_id,
                    'device_type' => $device->device_type,
                    'device_token' => $device->device_token,
                    'device_name' => $device->device_name,
                    'device_model' => $device->device_model,
                    'os_version' => $device->os_version,
                    'app_version' => $device->app_version,
                    'is_active' => (bool) $device->is_active,
                    'last_active_at' => $device->last_active_at?->toIso8601String(),
                    'created_at' => $device->created_at?->toIso8601String(),
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Device registration failed', [
                'customer_id' => auth('sanctum')->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register device',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unregister device from push notifications
     *
     * POST /api/customer/device/unregister
     *
     * Removes device token from database. Call this when:
     * - User logs out of app
     * - User uninstalls app (if logout is triggered)
     * - Device token becomes invalid
     *
     * @bodyParam device_token string required FCM device token to unregister
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Device unregistered successfully"
     * }
     */
    public function unregister(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_token' => 'required|string',
            ]);

            $customerId = auth('sanctum')->id();

            // Only allow unregistering own devices
            $device = CustomerDevice::where('device_token', $validated['device_token'])
                ->where('customer_id', $customerId)
                ->first();

            if (! $device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found or does not belong to you',
                ], 404);
            }

            $deleted = $this->pushService->unregisterDevice($validated['device_token']);

            Log::info('Device unregistered', [
                'customer_id' => $customerId,
                'device_id' => $device->id,
                'device_type' => $device->device_type,
            ]);

            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'Device unregistered successfully' : 'Device not found',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Device unregistration failed', [
                'customer_id' => auth('sanctum')->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unregister device',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update device information
     *
     * PUT /api/customer/device/update
     *
     * Updates device metadata (name, model, OS version, app version).
     * Also marks device as active and updates last_active_at timestamp.
     *
     * @bodyParam device_token string required FCM device token to update
     * @bodyParam device_name string optional New device name
     * @bodyParam device_model string optional Device model
     * @bodyParam os_version string optional OS version
     * @bodyParam app_version string optional App version
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Device updated successfully",
     *   "device": {...}
     * }
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_token' => 'required|string',
                'device_name' => 'nullable|string|max:125',
                'device_model' => 'nullable|string|max:125',
                'os_version' => 'nullable|string|max:125',
                'app_version' => 'nullable|string|max:125',
            ]);

            $customerId = auth('sanctum')->id();

            $device = CustomerDevice::where('device_token', $validated['device_token'])
                ->where('customer_id', $customerId)
                ->first();

            if (! $device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found or does not belong to you',
                ], 404);
            }

            $updateData = array_filter([
                'device_name' => $validated['device_name'] ?? null,
                'device_model' => $validated['device_model'] ?? null,
                'os_version' => $validated['os_version'] ?? null,
                'app_version' => $validated['app_version'] ?? null,
                'last_active_at' => now(),
            ], fn ($value) => $value !== null);

            $device->update($updateData);

            Log::info('Device updated', [
                'customer_id' => $customerId,
                'device_id' => $device->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device updated successfully',
                'device' => [
                    'id' => $device->id,
                    'customer_id' => $device->customer_id,
                    'device_type' => $device->device_type,
                    'device_token' => $device->device_token,
                    'device_name' => $device->device_name,
                    'device_model' => $device->device_model,
                    'os_version' => $device->os_version,
                    'app_version' => $device->app_version,
                    'is_active' => (bool) $device->is_active,
                    'last_active_at' => $device->last_active_at?->toIso8601String(),
                    'created_at' => $device->created_at?->toIso8601String(),
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Device update failed', [
                'customer_id' => auth('sanctum')->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update device',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer's registered devices
     *
     * GET /api/customer/devices
     *
     * Returns all active devices registered for the authenticated customer.
     * Useful for:
     * - Showing registered devices in app settings
     * - Device management UI
     * - Debugging push notification delivery
     *
     * @response 200 {
     *   "success": true,
     *   "devices": [
     *     {
     *       "id": 1,
     *       "device_type": "android",
     *       "device_name": "John's Phone",
     *       "device_model": "Samsung Galaxy S21",
     *       "os_version": "Android 13",
     *       "app_version": "1.0.0",
     *       "is_active": true,
     *       "last_active_at": "2025-10-31 10:00:00",
     *       "created_at": "2025-10-31 10:00:00"
     *     }
     *   ],
     *   "total": 1
     * }
     */
    public function index(): JsonResponse
    {
        try {
            $customerId = auth('sanctum')->id();

            $devices = $this->pushService->getCustomerDevices($customerId);

            return response()->json([
                'success' => true,
                'devices' => $devices->map(fn ($device) => [
                    'id' => $device->id,
                    'device_type' => $device->device_type,
                    'device_name' => $device->device_name,
                    'device_model' => $device->device_model,
                    'os_version' => $device->os_version,
                    'app_version' => $device->app_version,
                    'is_active' => (bool) $device->is_active,
                    'last_active_at' => $device->last_active_at?->toIso8601String(),
                    'created_at' => $device->created_at?->toIso8601String(),
                ]),
                'total' => $devices->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch devices', [
                'customer_id' => auth('sanctum')->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch devices',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deactivate a specific device
     *
     * POST /api/customer/device/{deviceId}/deactivate
     *
     * SECURITY FIX #12: IDOR protection with explicit ownership check in query
     * - Changed from route model binding to explicit ID parameter
     * - Uses ->where('customer_id', $customerId) in query to prevent IDOR
     * - Never fetches devices that don't belong to authenticated customer
     *
     * Marks a device as inactive. Useful for:
     * - User wanting to stop notifications on specific device
     * - Device management UI in app settings
     *
     * @urlParam deviceId required Device ID
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Device deactivated successfully"
     * }
     */
    public function deactivate(int $deviceId): JsonResponse
    {
        try {
            $customerId = auth('sanctum')->id();

            // SECURITY FIX: Explicit ownership check in query itself
            // Only fetch devices that belong to the authenticated customer
            $device = CustomerDevice::where('id', $deviceId)
                ->where('customer_id', $customerId)
                ->first();

            if (!$device) {
                // SECURITY: Log potential IDOR attempt
                Log::warning('SECURITY: Attempted to deactivate device belonging to another customer', [
                    'attempted_device_id' => $deviceId,
                    'customer_id' => $customerId,
                    'ip' => request()->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Device not found',
                ], 404);
            }

            $device->update(['is_active' => false]);

            Log::info('Device deactivated', [
                'customer_id' => $customerId,
                'device_id' => $device->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device deactivated successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Device deactivation failed', [
                'device_id' => $deviceId ?? null,
                'customer_id' => auth('sanctum')->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate device',
            ], 500);
        }
    }

    /**
     * Update device heartbeat (mark as active)
     *
     * POST /api/customer/device/heartbeat
     *
     * Updates last_active_at timestamp. Call this periodically from mobile app
     * to prevent device from being auto-cleaned up as inactive.
     *
     * Recommended: Call every time app opens or every 24 hours while in use.
     *
     * @bodyParam device_token string required FCM device token
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Heartbeat recorded"
     * }
     */
    public function heartbeat(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_token' => 'required|string',
            ]);

            $customerId = auth('sanctum')->id();

            $device = CustomerDevice::where('device_token', $validated['device_token'])
                ->where('customer_id', $customerId)
                ->first();

            if (! $device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found',
                ], 404);
            }

            $device->markActive();

            return response()->json([
                'success' => true,
                'message' => 'Heartbeat recorded',
                'last_active_at' => $device->last_active_at?->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Heartbeat failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
