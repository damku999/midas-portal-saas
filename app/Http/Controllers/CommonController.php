<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Claim;

class CommonController extends Controller
{
    /**
     * SECURITY FIX: Model whitelist for deleteCommon
     * Only these models are allowed to prevent IDOR attacks
     */
    private const ALLOWED_DELETE_MODELS = [
        'Customer' => Customer::class,
        'Lead' => Lead::class,
        'Claim' => Claim::class,
    ];

    public function refreshCaptchaImage(Request $request)
    {
        return view('admin.refresh_captcha_image');
    }

    /**
     * SECURITY FIX: Complete overhaul of deleteCommon method
     * - Model whitelist to prevent arbitrary model deletion
     * - Authorization checks using Laravel policies
     * - Tenant isolation to prevent cross-tenant data access
     * - Comprehensive security logging
     */
    public function deleteCommon(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'record_id' => 'required|integer|min:1',
            'model' => 'required|string',
            'display_title' => 'string|nullable',
        ]);

        $modelName = $validated['model'];
        $recordId = $validated['record_id'];
        $displayTitle = $validated['display_title'] ?? $modelName;

        // SECURITY: Check if model is in whitelist
        if (!isset(self::ALLOWED_DELETE_MODELS[$modelName])) {
            Log::warning('SECURITY: Attempted to delete non-whitelisted model', [
                'model' => $modelName,
                'record_id' => $recordId,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid model type.'
            ], 403);
        }

        $modelClass = self::ALLOWED_DELETE_MODELS[$modelName];

        try {
            // Find the record
            $record = $modelClass::find($recordId);

            if (!$record) {
                return response()->json([
                    'status' => 'error',
                    'message' => $displayTitle . ' not found.'
                ], 404);
            }

            // SECURITY: Authorization check using Laravel policy
            if (auth()->user()->cannot('delete', $record)) {
                Log::warning('SECURITY: Unauthorized delete attempt', [
                    'model' => $modelName,
                    'record_id' => $recordId,
                    'user_id' => auth()->id(),
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            // SECURITY: Tenant isolation check (if tenant exists)
            if (function_exists('tenant') && tenant()) {
                $tenantId = tenant()->id;

                // Verify record belongs to current tenant
                if (method_exists($record, 'getTenantId')) {
                    if ($record->getTenantId() !== $tenantId) {
                        Log::warning('SECURITY: Cross-tenant delete attempt', [
                            'model' => $modelName,
                            'record_id' => $recordId,
                            'user_id' => auth()->id(),
                            'tenant_id' => $tenantId,
                            'record_tenant_id' => $record->getTenantId(),
                            'ip' => $request->ip(),
                        ]);

                        return response()->json([
                            'status' => 'error',
                            'message' => 'Record not found.'
                        ], 404);
                    }
                }
            }

            // Perform deletion
            $record->delete();

            // SECURITY: Log successful deletion
            Log::info('Record deleted via deleteCommon', [
                'model' => $modelName,
                'record_id' => $recordId,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $displayTitle . ' has been deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in deleteCommon', [
                'model' => $modelName,
                'record_id' => $recordId,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the record.'
            ], 500);
        }
    }

    public function activeInactiveCommon(Request $request)
    {
        if ($request->record_id != '' && $request->model != '') {
            $model_name = '\\App\\Models\\'.$request->model;
            $model_obj = new $model_name;
            $record = $model_obj->find($request->record_id);
            $temp_label = '';

            if ($record->status == 'A') {
                $record->status = 'I';
                $temp_label = ' In Activated ';
            } else {
                $record->status = 'A';
                $temp_label = ' Activated ';
            }

            if ($record->save()) {
                return response()->json(['status' => 'success', 'message' => $request->display_title.$temp_label.' successfully.']);
            }

            return response()->json(['status' => 'error', 'message' => $request->display_title.$temp_label.' not found.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Something went wrong.']);
    }

    /**
     * SECURITY FIX: Path traversal protection for getImage
     * - Validates and sanitizes all path components
     * - Uses realpath() to prevent directory traversal
     * - Whitelists allowed directories
     * - Validates MIME types
     * - Logs security events
     */
    public function getImage(Request $request, string $file_path, string $file_name)
    {
        // SECURITY: Sanitize inputs - only allow alphanumeric, dash, underscore, dot
        $file_path = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $file_path);
        $file_name = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $file_name);

        // SECURITY: Whitelist of allowed directories
        $allowedPaths = [
            'app/public',
            'app/tenant',
            'app/uploads',
        ];

        // Build the full path
        $basePath = storage_path();
        $requestedPath = $basePath . DIRECTORY_SEPARATOR . $file_path . DIRECTORY_SEPARATOR . $file_name;

        // SECURITY: Get real path to prevent directory traversal
        $realPath = realpath($requestedPath);

        // Check if file exists and is within allowed paths
        if ($realPath === false || !file_exists($realPath)) {
            Log::warning('SECURITY: Attempted to access non-existent file', [
                'requested_path' => $file_path . '/' . $file_name,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);

            abort(404, 'File not found');
        }

        // SECURITY: Verify the real path is within storage directory
        if (strpos($realPath, $basePath) !== 0) {
            Log::warning('SECURITY: Path traversal attempt detected', [
                'requested_path' => $file_path . '/' . $file_name,
                'real_path' => $realPath,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);

            abort(403, 'Access denied');
        }

        // SECURITY: Verify the path is in an allowed directory
        $pathAllowed = false;
        foreach ($allowedPaths as $allowedPath) {
            $fullAllowedPath = $basePath . DIRECTORY_SEPARATOR . $allowedPath;
            if (strpos($realPath, $fullAllowedPath) === 0) {
                $pathAllowed = true;
                break;
            }
        }

        if (!$pathAllowed) {
            Log::warning('SECURITY: Attempted to access file outside allowed directories', [
                'requested_path' => $file_path . '/' . $file_name,
                'real_path' => $realPath,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);

            abort(403, 'Access denied');
        }

        // SECURITY: Validate MIME type - only allow images and documents
        $allowedMimeTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        $mimeType = mime_content_type($realPath);
        if (!in_array($mimeType, $allowedMimeTypes)) {
            Log::warning('SECURITY: Attempted to access file with invalid MIME type', [
                'requested_path' => $file_path . '/' . $file_name,
                'mime_type' => $mimeType,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);

            abort(403, 'Invalid file type');
        }

        return response()->file($realPath);
    }

    /**
     * SECURITY FIX: Path traversal protection for getImage1
     * Same security measures as getImage but with two-level path
     */
    public function getImage1(Request $request, string $file_path1, string $file_path2, string $file_name)
    {
        // SECURITY: Sanitize inputs - only allow alphanumeric, dash, underscore, dot
        $file_path1 = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $file_path1);
        $file_path2 = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $file_path2);
        $file_name = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $file_name);

        // SECURITY: Whitelist of allowed directories
        $allowedPaths = [
            'app/public',
            'app/tenant',
            'app/uploads',
        ];

        // Build the full path
        $basePath = storage_path();
        $requestedPath = $basePath . DIRECTORY_SEPARATOR . $file_path1 . DIRECTORY_SEPARATOR . $file_path2 . DIRECTORY_SEPARATOR . $file_name;

        // SECURITY: Get real path to prevent directory traversal
        $realPath = realpath($requestedPath);

        // Check if file exists and is within allowed paths
        if ($realPath === false || !file_exists($realPath)) {
            Log::warning('SECURITY: Attempted to access non-existent file', [
                'requested_path' => $file_path1 . '/' . $file_path2 . '/' . $file_name,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);

            abort(404, 'File not found');
        }

        // SECURITY: Verify the real path is within storage directory
        if (strpos($realPath, $basePath) !== 0) {
            Log::warning('SECURITY: Path traversal attempt detected', [
                'requested_path' => $file_path1 . '/' . $file_path2 . '/' . $file_name,
                'real_path' => $realPath,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);

            abort(403, 'Access denied');
        }

        // SECURITY: Verify the path is in an allowed directory
        $pathAllowed = false;
        foreach ($allowedPaths as $allowedPath) {
            $fullAllowedPath = $basePath . DIRECTORY_SEPARATOR . $allowedPath;
            if (strpos($realPath, $fullAllowedPath) === 0) {
                $pathAllowed = true;
                break;
            }
        }

        if (!$pathAllowed) {
            Log::warning('SECURITY: Attempted to access file outside allowed directories', [
                'requested_path' => $file_path1 . '/' . $file_path2 . '/' . $file_name,
                'real_path' => $realPath,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);

            abort(403, 'Access denied');
        }

        // SECURITY: Validate MIME type - only allow images and documents
        $allowedMimeTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        $mimeType = mime_content_type($realPath);
        if (!in_array($mimeType, $allowedMimeTypes)) {
            Log::warning('SECURITY: Attempted to access file with invalid MIME type', [
                'requested_path' => $file_path1 . '/' . $file_path2 . '/' . $file_name,
                'mime_type' => $mimeType,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
            ]);

            abort(403, 'Invalid file type');
        }

        return response()->file($realPath);
    }
}
