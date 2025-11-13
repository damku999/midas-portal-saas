<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Tenant Storage Routes
|--------------------------------------------------------------------------
|
| These routes handle serving tenant-specific files from tenant storage.
| Files are stored in storage/tenant{id}/app/public/ and must be served
| through this route to maintain tenant isolation.
|
*/

// Serve tenant-specific public storage files
// Using /tenant-assets instead of /storage to avoid conflict with public/storage symlink
Route::get('/tenant-assets/{path}', function ($path) {
    $tenant = tenant();

    if (!$tenant) {
        abort(404, 'Tenant not found');
    }

    // Use Storage facade to get file from tenant-specific storage
    // The FilesystemTenancyBootstrapper automatically scopes this to tenant storage
    if (!Storage::disk('public')->exists($path)) {
        abort(404, 'File not found');
    }

    $file = Storage::disk('public')->get($path);
    $mimeType = Storage::disk('public')->mimeType($path);

    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
})->where('path', '.*')->name('tenant.storage');
