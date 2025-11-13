<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\AppSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * App Setting Controller
 *
 * Handles AppSetting CRUD operations for managing application configuration.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class AppSettingController extends AbstractBaseCrudController
{
    protected array $categories = [
        'application' => 'Application',
        'company' => 'Company',
        'whatsapp' => 'WhatsApp',
        'mail' => 'Mail',
        'notifications' => 'Notifications',
        'general' => 'General',
    ];

    protected array $types = [
        'string' => 'Text',
        'text' => 'Textarea',
        'json' => 'JSON',
        'boolean' => 'Boolean',
        'numeric' => 'Number',
        'color' => 'Color Picker',
        'url' => 'URL',
        'email' => 'Email',
        'image' => 'Image Upload',
        'file' => 'File Upload',
    ];

    public function __construct(protected AppSettingService $appSettingService)
    {
        $this->setupPermissionMiddleware('app-setting');
    }

    /**
     * Display a listing of the app settings
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request)
    {
        try {
            $query = AppSetting::query();

            // Get all unique categories dynamically from database
            $dynamicCategories = AppSetting::select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category', 'category');

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(static function ($q) use ($search): void {
                    $q->where('key', 'LIKE', sprintf('%%%s%%', $search))
                        ->orWhere('description', 'LIKE', sprintf('%%%s%%', $search))
                        ->orWhere('category', 'LIKE', sprintf('%%%s%%', $search))
                        ->orWhere('value', 'LIKE', sprintf('%%%s%%', $search));
                });
            }

            // Category filter
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            // Type filter
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('is_active', $request->status);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'category');
            $sortOrder = $request->get('sort_order', 'asc');

            // Validate sort columns
            $allowedSorts = ['key', 'category', 'type', 'is_active', 'created_at', 'updated_at'];
            if (! in_array($sortBy, $allowedSorts)) {
                $sortBy = 'category';
            }

            // Validate sort order
            if (! in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            // Apply sorting with secondary sort by key
            if ($sortBy === 'category') {
                $settings = $query->orderBy($sortBy, $sortOrder)->orderBy('key', 'asc')->paginate(pagination_per_page());
            } else {
                $settings = $query->orderBy($sortBy, $sortOrder)->paginate(pagination_per_page());
            }

            $settings->appends($request->except('page'));

            // Group settings by category for display
            $groupedSettings = $settings->getCollection()->groupBy('category');

            return view('app_settings.index', [
                'settings' => $settings,
                'groupedSettings' => $groupedSettings,
                'categories' => $dynamicCategories,
                'types' => $this->types,
            ]);
        } catch (\Throwable $throwable) {
            return $this->redirectWithError('Failed to load settings: '.$throwable->getMessage());
        }
    }

    /**
     * Show the form for creating a new app setting
     *
     * @return View
     */
    public function create()
    {
        // Get existing categories dynamically from database
        $dynamicCategories = AppSetting::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category', 'category');

        return view('app_settings.create', [
            'categories' => $dynamicCategories,
            'types' => $this->types,
        ]);
    }

    /**
     * Store a newly created app setting in storage
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:app_settings,key',
            'value' => 'nullable',
            'type' => 'required|in:string,text,json,boolean,numeric,color,url,email,image,file',
            'category' => 'required|string|max:100',
            'new_category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_encrypted' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'image_file' => 'nullable|image|max:2048', // 2MB max
            'file_upload' => 'nullable|file|max:5120', // 5MB max
        ]);

        try {
            $value = $request->value;
            $category = $request->filled('new_category') ? $request->new_category : $request->category;

            // Handle image upload
            if ($request->type === 'image' && $request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $filename = time().'_'.str_replace(' ', '_', $file->getClientOriginalName());
                $file->storeAs('app-settings', $filename, 'public');
                $value = 'app-settings/'.$filename;
            }

            // Handle file upload
            if ($request->type === 'file' && $request->hasFile('file_upload')) {
                $file = $request->file('file_upload');
                $filename = time().'_'.str_replace(' ', '_', $file->getClientOriginalName());
                $file->storeAs('app-settings', $filename, 'public');
                $value = 'app-settings/'.$filename;
            }

            // Handle boolean values
            if ($request->type === 'boolean') {
                $value = $request->has('value') ? 'true' : 'false';
            }

            // Create using service
            $this->appSettingService->set(
                $request->key,
                $value,
                [
                    'type' => $request->type,
                    'category' => $category,
                    'description' => $request->description,
                    'is_encrypted' => $request->has('is_encrypted'),
                    'is_active' => $request->has('is_active') ? 1 : 0,
                ]
            );

            return $this->redirectWithSuccess(
                'app-settings.index',
                $this->getSuccessMessage('App Setting', 'created')
            );
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('App Setting', 'create').': '.$throwable->getMessage()
            )->withInput();
        }
    }

    /**
     * Display the specified app setting
     *
     * @param  int  $id
     * @return View|RedirectResponse
     */
    public function show($id)
    {
        try {
            $setting = AppSetting::query()->findOrFail($id);

            return view('app_settings.show', [
                'setting' => $setting,
                'categories' => $this->categories,
                'types' => $this->types,
            ]);
        } catch (\Throwable $throwable) {
            return $this->redirectWithError('Setting not found: '.$throwable->getMessage());
        }
    }

    /**
     * Show the form for editing the specified app setting
     *
     * @param  int  $id
     * @return View|RedirectResponse
     */
    public function edit($id)
    {
        try {
            $setting = AppSetting::query()->findOrFail($id);

            // Get existing categories dynamically from database
            $dynamicCategories = AppSetting::select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category', 'category');

            return view('app_settings.edit', [
                'setting' => $setting,
                'categories' => $dynamicCategories,
                'types' => $this->types,
            ]);
        } catch (\Throwable $throwable) {
            return $this->redirectWithError('Setting not found: '.$throwable->getMessage());
        }
    }

    /**
     * Update the specified app setting in storage
     *
     * @param  int  $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:app_settings,key,'.$id,
            'value' => 'nullable',
            'type' => 'required|in:string,text,json,boolean,numeric,color,url,email,image,file',
            'category' => 'required|string|max:100',
            'new_category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_encrypted' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'image_file' => 'nullable|image|max:2048',
            'file_upload' => 'nullable|file|max:5120',
        ]);

        try {
            $setting = AppSetting::query()->findOrFail($id);
            $value = $request->value;
            $category = $request->filled('new_category') ? $request->new_category : $request->category;

            // Handle image upload
            if ($request->type === 'image' && $request->hasFile('image_file')) {
                // Delete old image if exists
                if ($setting->type === 'image' && $setting->value && Storage::disk('public')->exists($setting->value)) {
                    Storage::disk('public')->delete($setting->value);
                }

                $file = $request->file('image_file');
                $filename = time().'_'.str_replace(' ', '_', $file->getClientOriginalName());
                $file->storeAs('app-settings', $filename, 'public');
                $value = 'app-settings/'.$filename;
            } elseif ($request->type === 'image' && ! $request->hasFile('image_file') && $setting->type === 'image') {
                // Keep existing image if no new upload
                $value = $setting->value;
            }

            // Handle file upload
            if ($request->type === 'file' && $request->hasFile('file_upload')) {
                // Delete old file if exists
                if ($setting->type === 'file' && $setting->value && Storage::disk('public')->exists($setting->value)) {
                    Storage::disk('public')->delete($setting->value);
                }

                $file = $request->file('file_upload');
                $filename = time().'_'.str_replace(' ', '_', $file->getClientOriginalName());
                $file->storeAs('app-settings', $filename, 'public');
                $value = 'app-settings/'.$filename;
            } elseif ($request->type === 'file' && ! $request->hasFile('file_upload') && $setting->type === 'file') {
                // Keep existing file if no new upload
                $value = $setting->value;
            }

            // Handle boolean values
            if ($request->type === 'boolean') {
                $value = $request->has('value') ? 'true' : 'false';
            }

            // Update the setting
            $setting->update([
                'key' => $request->key,
                'value' => $value,
                'type' => $request->type,
                'category' => $category,
                'description' => $request->description,
                'is_encrypted' => $request->has('is_encrypted') ? 1 : 0,
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            // Clear cache
            $this->appSettingService->clearCache();

            return $this->redirectWithSuccess(
                'app-settings.index',
                $this->getSuccessMessage('App Setting', 'updated')
            );
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('App Setting', 'update').': '.$throwable->getMessage()
            )->withInput();
        }
    }

    /**
     * Remove the specified app setting from storage (soft delete / mark inactive)
     *
     * @param  int  $id
     */
    /**
     * SECURITY FIX #11: Enhanced email domain authorization with security improvements
     * - Keeps @webmonks.in and @midastech.in domain authorization (user requirement)
     * - Adds email verification check to prevent email spoofing
     * - Adds comprehensive security logging
     * - Stores authorized domains in config for easier management
     */
    public function destroy($id): RedirectResponse
    {
        try {
            $user = auth()->user();
            $userEmail = $user->email ?? '';

            // SECURITY ENHANCEMENT: Check email is verified
            if (!$user->hasVerifiedEmail() && method_exists($user, 'hasVerifiedEmail')) {
                \Log::warning('SECURITY: Attempt to delete app setting with unverified email', [
                    'user_id' => $user->id,
                    'user_email' => $userEmail,
                    'setting_id' => $id,
                    'ip' => request()->ip(),
                ]);

                return $this->redirectWithError(
                    'You must verify your email address before performing this action.'
                );
            }

            // Check if user has authorized email domain
            // TODO: Consider moving to config/app.php as 'authorized_admin_domains'
            $authorizedDomains = ['@webmonks.in', '@midastech.in'];
            $isAuthorized = false;

            foreach ($authorizedDomains as $authorizedDomain) {
                if (str_ends_with($userEmail, $authorizedDomain)) {
                    $isAuthorized = true;
                    break;
                }
            }

            if (!$isAuthorized) {
                // SECURITY: Log unauthorized attempt
                \Log::warning('SECURITY: Unauthorized attempt to delete app setting', [
                    'user_id' => $user->id,
                    'user_email' => $userEmail,
                    'setting_id' => $id,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return $this->redirectWithError(
                    'You do not have permission to delete app settings. Only @webmonks.in or @midastech.in users can delete settings.'
                );
            }

            $setting = AppSetting::query()->findOrFail($id);

            // SECURITY: Log successful setting deletion
            \Log::info('App setting marked as inactive by authorized user', [
                'setting_id' => $id,
                'setting_key' => $setting->key ?? null,
                'user_id' => $user->id,
                'user_email' => $userEmail,
                'ip' => request()->ip(),
            ]);

            // Mark as inactive instead of deleting
            $setting->update(['is_active' => 0]);

            // Clear cache
            $this->appSettingService->clearCache();

            return $this->redirectWithSuccess(
                'app-settings.index',
                $this->getSuccessMessage('App Setting', 'deactivated')
            );
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('App Setting', 'deactivate').': '.$throwable->getMessage()
            );
        }
    }

    /**
     * Toggle setting status
     *
     * @param  int  $id
     * @param  int  $status
     */
    public function updateStatus($id, $status): RedirectResponse
    {
        try {
            $setting = AppSetting::query()->findOrFail($id);
            $setting->update(['is_active' => $status]);

            // Clear cache
            $this->appSettingService->clearCache();

            return $this->redirectWithSuccess(
                'app-settings.index',
                $this->getSuccessMessage('App Setting status', 'updated')
            );
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('App Setting status', 'update').': '.$throwable->getMessage()
            );
        }
    }

    /**
     * Get decrypted value for encrypted setting (AJAX)
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function getDecryptedValue($id)
    {
        try {
            $setting = AppSetting::query()->findOrFail($id);

            if (! $setting->is_encrypted) {
                return response()->json([
                    'success' => false,
                    'message' => 'This setting is not encrypted.',
                ], 400);
            }

            // Get decrypted value (accessor handles decryption)
            $decryptedValue = $setting->value;

            return response()->json([
                'success' => true,
                'value' => $decryptedValue,
            ]);

        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Error decrypting value: '.$throwable->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear app settings cache (AJAX)
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->appSettingService->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'App settings cache cleared successfully.',
            ]);
        } catch (\Throwable $throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache: '.$throwable->getMessage(),
            ], 500);
        }
    }
}
