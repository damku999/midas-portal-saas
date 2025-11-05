<?php

use App\Http\Controllers\Central\AuthController;
use App\Http\Controllers\Central\ContactSubmissionController;
use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\PlanController;
use App\Http\Controllers\Central\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central Admin Routes
|--------------------------------------------------------------------------
|
| These routes are for the central administration panel accessed at
| midastech.in/admin. These routes use the 'central' auth guard and
| are completely separate from tenant routes.
|
*/

// Authentication Routes
Route::middleware('guest:central')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('central.login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Central Admin Routes
Route::middleware(['central.auth'])->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('central.logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('central.dashboard');

    // Tenant Management
    Route::prefix('tenants')->name('central.tenants.')->group(function () {
        Route::get('/', [TenantController::class, 'index'])->name('index');
        Route::get('/create', [TenantController::class, 'create'])->name('create');
        Route::post('/', [TenantController::class, 'store'])->name('store');
        Route::post('/store-with-progress', [TenantController::class, 'storeWithProgress'])->name('store-with-progress');
        Route::post('/progress', [TenantController::class, 'getProgress'])->name('progress');
        Route::get('/{tenant}', [TenantController::class, 'show'])->name('show');
        Route::get('/{tenant}/edit', [TenantController::class, 'edit'])->name('edit');
        Route::put('/{tenant}', [TenantController::class, 'update'])->name('update');
        Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('destroy');

        // Tenant Actions
        Route::post('/{tenant}/suspend', [TenantController::class, 'suspend'])->name('suspend');
        Route::post('/{tenant}/activate', [TenantController::class, 'activate'])->name('activate');
        Route::post('/{tenant}/end-trial', [TenantController::class, 'endTrial'])->name('end-trial');
    });

    // Plans Management
    Route::prefix('plans')->name('central.plans.')->group(function () {
        Route::get('/', [PlanController::class, 'index'])->name('index');
        Route::get('/create', [PlanController::class, 'create'])->name('create');
        Route::post('/', [PlanController::class, 'store'])->name('store');
        Route::get('/{plan}', [PlanController::class, 'show'])->name('show');
        Route::get('/{plan}/edit', [PlanController::class, 'edit'])->name('edit');
        Route::put('/{plan}', [PlanController::class, 'update'])->name('update');
        Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');

        // Plan Actions
        Route::post('/{plan}/toggle-status', [PlanController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Contact Submissions
    Route::prefix('contact-submissions')->name('central.contact-submissions.')->group(function () {
        Route::get('/', [ContactSubmissionController::class, 'index'])->name('index');
        Route::get('/{contactSubmission}', [ContactSubmissionController::class, 'show'])->name('show');
        Route::post('/{contactSubmission}/status', [ContactSubmissionController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{contactSubmission}', [ContactSubmissionController::class, 'destroy'])->name('destroy');
    });
});
