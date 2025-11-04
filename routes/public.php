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

// Contact Form
Route::get('/contact', [PublicController::class, 'contact'])->name('public.contact');
Route::post('/contact', [PublicController::class, 'submitContact'])->name('public.contact.submit');

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
