<?php

use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website Routes (Central Domain Only)
|--------------------------------------------------------------------------
|
| These routes are for the public-facing marketing website accessible ONLY
| on central domains (e.g., midastech.in, midastech.testing.in).
|
| These routes are loaded with 'central.only' middleware which blocks
| access from tenant subdomains.
|
| Domain Access:
| ✅ midastech.testing.in:8085
| ✅ midastech.in
| ❌ tenant.midastech.testing.in (blocked by central.only middleware)
|
*/

// Public Website Home
Route::get('/', [PublicController::class, 'home'])->name('public.home');

// Feature Pages
Route::get('/features', [PublicController::class, 'features'])->name('public.features');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('public.pricing');
Route::get('/about', [PublicController::class, 'about'])->name('public.about');

// Feature Detail Pages
Route::get('/features/customer-management', [PublicController::class, 'customerManagement'])->name('public.features.customer-management');
Route::get('/features/family-management', [PublicController::class, 'familyManagement'])->name('public.features.family-management');
Route::get('/features/customer-portal', [PublicController::class, 'customerPortal'])->name('public.features.customer-portal');
Route::get('/features/lead-management', [PublicController::class, 'leadManagement'])->name('public.features.lead-management');
Route::get('/features/policy-management', [PublicController::class, 'policyManagement'])->name('public.features.policy-management');
Route::get('/features/claims-management', [PublicController::class, 'claimsManagement'])->name('public.features.claims-management');
Route::get('/features/whatsapp-integration', [PublicController::class, 'whatsappIntegration'])->name('public.features.whatsapp-integration');
Route::get('/features/quotation-system', [PublicController::class, 'quotationSystem'])->name('public.features.quotation-system');
Route::get('/features/analytics-reports', [PublicController::class, 'analyticsReports'])->name('public.features.analytics-reports');
Route::get('/features/commission-tracking', [PublicController::class, 'commissionTracking'])->name('public.features.commission-tracking');
Route::get('/features/document-management', [PublicController::class, 'documentManagement'])->name('public.features.document-management');
Route::get('/features/staff-management', [PublicController::class, 'staffManagement'])->name('public.features.staff-management');
Route::get('/features/master-data-management', [PublicController::class, 'masterDataManagement'])->name('public.features.master-data-management');
Route::get('/features/notifications-alerts', [PublicController::class, 'notificationsAlerts'])->name('public.features.notifications-alerts');

// Contact Form
Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');
Route::post('/contact', [PublicController::class, 'submitContact'])->name('public.contact.submit');

// Newsletter Subscription
Route::post('/newsletter/subscribe', [PublicController::class, 'subscribeNewsletter'])->name('public.newsletter.subscribe');

// Blog Routes
Route::get('/blog', [PublicController::class, 'blog'])->name('public.blog');
Route::get('/blog/{post:slug}', [PublicController::class, 'blogShow'])->name('public.blog.show');
Route::get('/help-center', [PublicController::class, 'helpCenter'])->name('public.help-center');
Route::get('/documentation', [PublicController::class, 'documentation'])->name('public.documentation');
Route::get('/api', [PublicController::class, 'api'])->name('public.api');
Route::get('/privacy', [PublicController::class, 'privacy'])->name('public.privacy');
Route::get('/terms', [PublicController::class, 'terms'])->name('public.terms');
Route::get('/security', [PublicController::class, 'security'])->name('public.security');

// Dynamic Sitemap
Route::get('/sitemap.xml', [PublicController::class, 'sitemap'])->name('public.sitemap');

// Razorpay Test Page
Route::prefix('razorpay-test')->name('razorpay-test.')->group(function () {
    Route::get('/', [App\Http\Controllers\RazorpayTestController::class, 'index'])->name('index');
    Route::post('/create-order', [App\Http\Controllers\RazorpayTestController::class, 'createOrder'])->name('create-order');
    Route::post('/verify-payment', [App\Http\Controllers\RazorpayTestController::class, 'verifyPayment'])->name('verify-payment');
    Route::get('/payment-status/{paymentId}', [App\Http\Controllers\RazorpayTestController::class, 'paymentStatus'])->name('payment-status');
    Route::get('/recent-payments', [App\Http\Controllers\RazorpayTestController::class, 'recentPayments'])->name('recent-payments');
});

/*
|--------------------------------------------------------------------------
| Public Domain Redirect Handler
|--------------------------------------------------------------------------
|
| Redirect common authentication routes to appropriate locations
| when accessed from central domain
|
*/

// Redirect /login on central domain to central admin login
Route::get('/login', function () {
    return redirect('/midas-admin/login');
});

// Redirect /register on central domain (if someone tries to access it)
Route::get('/register', function () {
    return redirect('/')->with('info', 'Please contact us to create a tenant account.');
});
