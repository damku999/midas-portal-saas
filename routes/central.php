<?php

use App\Http\Controllers\Central\AuthController;
use App\Http\Controllers\Central\BlogPostController;
use App\Http\Controllers\Central\ContactSubmissionController;
use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\InvoiceController;
use App\Http\Controllers\Central\NewsletterSubscriberController;
use App\Http\Controllers\Central\PlanController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\TestimonialController;
use App\Http\Controllers\Central\UsageAlertController;
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

        // Subscription & Payment Management
        Route::post('/{tenant}/change-plan', [TenantController::class, 'changePlan'])->name('change-plan');
        Route::post('/{tenant}/record-payment', [TenantController::class, 'recordPayment'])->name('record-payment');
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

    // Newsletter Subscribers
    Route::prefix('newsletter-subscribers')->name('central.newsletter-subscribers.')->group(function () {
        Route::get('/', [NewsletterSubscriberController::class, 'index'])->name('index');
        Route::get('/export', [NewsletterSubscriberController::class, 'export'])->name('export');
        Route::get('/{subscriber}', [NewsletterSubscriberController::class, 'show'])->name('show');
        Route::post('/{subscriber}/status', [NewsletterSubscriberController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{subscriber}', [NewsletterSubscriberController::class, 'destroy'])->name('destroy');
    });

    // Testimonials
    Route::prefix('testimonials')->name('central.testimonials.')->group(function () {
        Route::get('/', [TestimonialController::class, 'index'])->name('index');
        Route::get('/create', [TestimonialController::class, 'create'])->name('create');
        Route::post('/', [TestimonialController::class, 'store'])->name('store');
        Route::get('/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('edit');
        Route::put('/{testimonial}', [TestimonialController::class, 'update'])->name('update');
        Route::delete('/{testimonial}', [TestimonialController::class, 'destroy'])->name('destroy');
        Route::post('/{testimonial}/toggle-status', [TestimonialController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Blog Posts
    Route::prefix('blog-posts')->name('central.blog-posts.')->group(function () {
        Route::get('/', [BlogPostController::class, 'index'])->name('index');
        Route::get('/create', [BlogPostController::class, 'create'])->name('create');
        Route::post('/', [BlogPostController::class, 'store'])->name('store');
        Route::get('/{blogPost}/edit', [BlogPostController::class, 'edit'])->name('edit');
        Route::put('/{blogPost}', [BlogPostController::class, 'update'])->name('update');
        Route::delete('/{blogPost}', [BlogPostController::class, 'destroy'])->name('destroy');
        Route::post('/{blogPost}/toggle-status', [BlogPostController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Usage Alerts Management
    Route::prefix('usage-alerts')->name('central.usage-alerts.')->group(function () {
        Route::get('/', [UsageAlertController::class, 'index'])->name('index');
        Route::get('/analytics', [UsageAlertController::class, 'analytics'])->name('analytics');
        Route::get('/{alert}', [UsageAlertController::class, 'show'])->name('show');
        Route::post('/{alert}/acknowledge', [UsageAlertController::class, 'acknowledge'])->name('acknowledge');
        Route::post('/{alert}/resolve', [UsageAlertController::class, 'resolve'])->name('resolve');
    });

    // Tenant Usage Management
    Route::get('/tenants/{tenant}/usage', [UsageAlertController::class, 'tenantUsage'])->name('central.tenants.usage');
    Route::post('/tenants/{tenant}/thresholds', [UsageAlertController::class, 'updateThresholds'])->name('central.tenants.thresholds');

    // Invoice Management
    Route::prefix('invoices')->name('central.invoices.')->group(function () {
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/download', [InvoiceController::class, 'download'])->name('download');
        Route::get('/{invoice}/stream', [InvoiceController::class, 'stream'])->name('stream');
        Route::post('/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('send-email');
    });
});
