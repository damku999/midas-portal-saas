<?php

namespace App\Http\Controllers;

use App\Models\CustomerDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerDeviceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:customer-device-list')->only(['index']);
        $this->middleware('permission:customer-device-view')->only(['show']);
        $this->middleware('permission:customer-device-deactivate')->only(['deactivate']);
        $this->middleware('permission:customer-device-cleanup')->only(['cleanupInvalid']);
    }

    /**
     * Display listing of all customer devices
     */
    public function index(Request $request)
    {
        $builder = CustomerDevice::with('customer')
            ->orderBy('last_active_at', 'desc');

        // Filters
        if ($request->filled('customer_id')) {
            $builder->where('customer_id', $request->customer_id);
        }

        if ($request->filled('device_type')) {
            $builder->where('device_type', $request->device_type);
        }

        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $builder->where('is_active', true);
            } else {
                $builder->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $builder->where(static function ($q) use ($search): void {
                $q->where('device_name', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('device_token', 'like', sprintf('%%%s%%', $search))
                    ->orWhereHas('customer', static function ($q) use ($search): void {
                        $q->where('name', 'like', sprintf('%%%s%%', $search))
                            ->orWhere('mobile_number', 'like', sprintf('%%%s%%', $search));
                    });
            });
        }

        $devices = $builder->paginate(pagination_per_page());

        // Statistics
        $stats = [
            'total' => CustomerDevice::query()->count(),
            'active' => CustomerDevice::query()->where('is_active', true)->count(),
            'inactive' => CustomerDevice::query()->where('is_active', false)->count(),
            'android' => CustomerDevice::query()->where('device_type', 'android')->where('is_active', true)->count(),
            'ios' => CustomerDevice::query()->where('device_type', 'ios')->where('is_active', true)->count(),
            'web' => CustomerDevice::query()->where('device_type', 'web')->where('is_active', true)->count(),
        ];

        return view('admin.customer_devices.index', ['devices' => $devices, 'stats' => $stats]);
    }

    /**
     * Show device details
     */
    public function show(CustomerDevice $customerDevice)
    {
        $customerDevice->load('customer');

        // Get notification logs for this device
        $notifications = DB::table('notification_logs')
            ->where('channel', 'push')
            ->where('recipient', $customerDevice->device_token)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.customer_devices.show', ['device' => $device, 'notifications' => $notifications]);
    }

    /**
     * Deactivate a device
     */
    public function deactivate(CustomerDevice $customerDevice)
    {
        $customerDevice->update(['is_active' => false]);

        return back()->with('success', 'Device deactivated successfully');
    }

    /**
     * Cleanup invalid device tokens
     */
    public function cleanupInvalid(Request $request)
    {
        // Deactivate devices that haven't been active in 90 days
        $count = CustomerDevice::query()->where('is_active', true)
            ->where('last_active_at', '<', now()->subDays(90))
            ->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => sprintf('Deactivated %s inactive devices', $count),
        ]);
    }
}
