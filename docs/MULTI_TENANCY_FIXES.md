# Multi-Tenancy Fixes - Complete Guide

## Overview

This document covers all the multi-tenancy issues identified and fixed during the transition from single-tenant to multi-tenant architecture using Stancl Tenancy package.

**Date**: 2025-11-03
**Status**: ✅ All Critical Issues Resolved

---

## Issues Fixed

### 1. ✅ Suspended Tenant Access Control (CRITICAL)

**Problem**: Suspended tenants could still access the entire portal because subscription.status middleware was not applied globally.

**Impact**: High - Security issue allowing suspended accounts to operate normally

**Root Cause**:
- `subscription.status` middleware only applied to `/subscription/*` routes
- All tenant routes (staff portal + customer portal) lacked subscription checks
- Middleware was not in the global route group

**Solution**:
```php
// app/Providers/RouteServiceProvider.php
Route::middleware(['web', 'tenant', 'subscription.status'])  // Added subscription.status
    ->namespace($this->namespace)
    ->group(base_path('routes/web.php'));
```

**Files Modified**:
- `app/Providers/RouteServiceProvider.php` - Added subscription.status to route groups
- `app/Http/Middleware/CheckSubscriptionStatus.php` - Added exception routes to prevent redirect loops

**Files Created**:
- `resources/views/subscription/suspended.blade.php` - Subscription suspended status page
- `resources/views/subscription/cancelled.blade.php` - Subscription cancelled status page
- `resources/views/subscription/required.blade.php` - No subscription found page

**Middleware Exceptions** (routes accessible even when suspended):
```php
protected $except = [
    // Subscription pages
    'subscription.required', 'subscription.suspended', 'subscription.cancelled',
    'subscription.upgrade', 'subscription.plans', 'subscription.index',

    // Authentication (staff)
    'login', 'logout', 'password.request', 'password.email',
    'password.reset', 'password.update', 'tenant.root',

    // Authentication (customer)
    'customer.login', 'customer.logout', 'customer.password.*',
    'customer.verify-email', 'customer.verify-email-notice',
    'customer.resend-verification', 'customer.verification.send',
];
```

**Testing**:
```sql
-- Suspend a tenant
UPDATE subscriptions SET status = 'suspended'
WHERE tenant_id = '{tenant-id}';

-- Try accessing tenant portal - should redirect to subscription.suspended
```

---

### 2. ✅ Customer Email Verification Infinite Redirect

**Problem**: After clicking "resend verification email", users were stuck in infinite redirect loop between `/customer/change-password` and email verification pages.

**Impact**: High - Blocked new customers from verifying emails and completing onboarding

**Root Cause**:
```php
// Old code in CustomerAuthController.php:474
return back()->with('success', 'Verification link sent to your email.');
```

The `back()` function returned to the previous page (change-password), which required email verification, creating a loop.

**Solution**:
```php
// app/Http/Controllers/Auth/CustomerAuthController.php:474-475
return redirect()->route('customer.verify-email-notice')
    ->with('success', 'Verification link sent to your email.');

// Line 484 - Error case also fixed
return redirect()->route('customer.verify-email-notice')
    ->withErrors(['email' => 'Failed to send verification email...']);
```

**Files Modified**:
- `app/Http/Controllers/Auth/CustomerAuthController.php` - Lines 474 and 484
- `app/Http/Middleware/CheckSubscriptionStatus.php` - Added customer verification routes to exceptions

**Testing**:
1. Login with unverified customer account
2. Click "Resend Verification Email"
3. Should stay on verify-email-notice page (no loop)

---

### 3. ✅ File Storage URL Generation for Tenant Files

**Problem**: Files stored in tenant-specific storage (`storage/tenant{id}/app/public/`) were inaccessible via URL (`/storage/...` pointed to central storage).

**Impact**: High - Document downloads, policy PDFs, customer uploads all broken for tenants

**Root Cause**:
- Laravel's default `public/storage` symlink points to `storage/app/public/` (central storage)
- Tenant files are in `storage/tenant{id}/app/public/`
- FilesystemTenancyBootstrapper handles Storage facade but not HTTP file serving
- No route to serve tenant-specific files

**Solution**:

Created dedicated tenant storage serving route:

```php
// routes/tenant-storage.php (NEW FILE)
Route::get('/storage/{path}', function ($path) {
    $tenant = tenant();

    if (!$tenant) {
        abort(404, 'Tenant not found');
    }

    // FilesystemTenancyBootstrapper automatically scopes Storage::disk('public')
    if (!Storage::disk('public')->exists($path)) {
        abort(404, 'File not found');
    }

    $file = Storage::disk('public')->get($path);
    $mimeType = Storage::disk('public')->mimeType($path);

    return response($file, 200)->header('Content-Type', $mimeType);
})->where('path', '.*')->name('tenant.storage');
```

**Files Created**:
- `routes/tenant-storage.php` - New file serving route

**Files Modified**:
- `app/Providers/RouteServiceProvider.php` - Registered tenant-storage route group

**Route Registration**:
```php
// app/Providers/RouteServiceProvider.php
Route::middleware(['web', 'tenant'])  // NO subscription.status - allow file viewing
    ->namespace($this->namespace)
    ->group(base_path('routes/tenant-storage.php'));
```

**How It Works**:
1. Request: `GET https://webmonks.midastech.testing.in:8085/storage/customer_insurances/1/policy.pdf`
2. `tenant-storage` route catches `/storage/*`
3. Middleware identifies tenant from subdomain
4. FilesystemTenancyBootstrapper scopes `Storage::disk('public')` to `storage/tenant{id}/app/public/`
5. File served with correct MIME type

**Testing**:
```bash
# Store test file
php artisan tinker
Storage::disk('public')->put('test.txt', 'Hello from tenant storage!');

# Access via browser
https://{tenant}.midastech.testing.in:8085/storage/test.txt
```

**Important Notes**:
- Files ARE tenant-isolated (automatic via FilesystemTenancyBootstrapper)
- NO symlink confusion (handled dynamically)
- Works for all file types (PDFs, images, documents)
- No changes needed in existing upload code
- Database paths remain relative: `customer_insurances/1/policy.pdf`

---

### 4. ✅ Missing Subscription Status Views

**Problem**: When suspended/cancelled tenants were redirected to status pages, they encountered "View not found" errors because the views didn't exist.

**Impact**: Medium - Middleware worked but resulted in error pages instead of user-friendly status pages

**Root Cause**:
- `SubscriptionController` methods `suspended()`, `cancelled()`, and `required()` expected views that were never created
- Routes existed but returned 500 errors due to missing views
- Only `index.blade.php` and `plans.blade.php` existed in `resources/views/subscription/`

**Solution**:

Created three subscription status view files with user-friendly designs:

**Files Created**:
- `resources/views/subscription/suspended.blade.php`
  - Explains suspension reason
  - Shows common causes (payment issues, billing updates needed)
  - Contact support button

- `resources/views/subscription/cancelled.blade.php`
  - Explains cancellation status
  - Data retention policy (30 days)
  - Reactivation option

- `resources/views/subscription/required.blade.php`
  - No subscription found message
  - Setup instructions
  - Administrator contact information

**Design Features**:
- Standalone layouts (no auth required)
- Gradient backgrounds with centered cards
- Font Awesome icons for visual clarity
- Responsive Bootstrap 5 styling
- Clear call-to-action buttons

**Testing**:
```bash
# Verify views exist
ls resources/views/subscription/

# Test by suspending tenant and accessing portal
# Should see professional suspension page, not error
```

---

## Migration Checklist

### For Existing Single-Tenant Application

- [x] Apply subscription.status middleware globally
- [x] Add middleware exception routes
- [x] Fix customer verification redirects
- [x] Create tenant storage serving route
- [x] Clear all caches (`php artisan route:clear config:clear cache:clear`)
- [x] Create subscription status views (suspended, cancelled, required)
- [x] Test with suspended tenant
- [x] Test file downloads
- [ ] Test email verification flow
- [ ] Verify all existing uploads are accessible

### Verification Commands

```bash
# 1. Clear caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# 2. Verify routes
php artisan route:list | grep storage
php artisan route:list | grep subscription

# 3. Check middleware stack
php artisan route:list | grep "web,tenant,subscription.status"

# 4. Test tenant storage
php artisan tinker
>> Storage::disk('public')->put('test.txt', 'test');
>> Storage::disk('public')->url('test.txt');
```

---

## Remaining Issues (Lower Priority)

### 5. 🔄 Asset URL References (CSS/JS)

**Status**: Pending investigation

**Description**: Ensure CSS, JS, and image assets load correctly on tenant subdomains

**Likely Solution**:
- Use `asset()` helper (not `url()`)
- Verify `asset_helper_tenancy => false` in config (we want global assets)

**Testing**:
```bash
# Check if assets load on tenant subdomain
https://tenant.midastech.testing.in:8085/css/app.css
```

---

### 6. 🔄 Email Links Use Tenant Domains

**Status**: Pending investigation

**Description**: Emails sent to customers should use tenant subdomain URLs, not central domain

**Example Issue**:
```php
// Wrong - uses central domain
$resetUrl = url('/password/reset/' . $token);

// Right - uses tenant domain
$resetUrl = route('customer.password.reset', ['token' => $token]);
```

**Files to Check**:
- `app/Mail/*.php` - All email classes
- Email templates in `resources/views/emails/`

---

## Best Practices Going Forward

### 1. File Storage
✅ **Always use Storage facade**:
```php
// Good
Storage::disk('public')->put($path, $contents);
$url = Storage::disk('public')->url($path);

// Bad
file_put_contents(storage_path('app/public/' . $path), $contents);
```

### 2. URLs and Routes
✅ **Always use route() or url() helpers**:
```php
// Good - tenant-aware
$url = route('customer.dashboard');
$url = url('/policies');

// Bad - hardcoded domain
$url = 'https://midastech.testing.in/policies';
```

### 3. Database Connections
✅ **Always specify connection for central models**:
```php
// Central models
protected $connection = 'central';

// Tenant models (default connection)
// No need to specify - handled by tenancy
```

### 4. Middleware Application
✅ **Route middleware order**:
```php
Route::middleware(['web', 'tenant', 'subscription.status', 'auth'])
    ->group(function () {
        // Tenant routes requiring active subscription
    });

Route::middleware(['web', 'tenant'])
    ->group(function () {
        // Public tenant routes (login, storage)
    });
```

---

## Troubleshooting

### Files Not Accessible
**Symptom**: 404 errors on `/storage/*` URLs

**Check**:
1. Is tenancy middleware applied to storage route?
   ```bash
   php artisan route:list | grep storage
   ```
2. Does file exist in tenant storage?
   ```bash
   ls storage/tenant{TENANT-ID}/app/public/
   ```
3. Is FilesystemTenancyBootstrapper enabled?
   ```bash
   grep -A5 "filesystem" config/tenancy.php
   ```

### Subscription Middleware Not Working
**Symptom**: Suspended tenants can still access portal

**Check**:
1. Is middleware in route group?
   ```bash
   php artisan route:list | grep "subscription.status"
   ```
2. Are routes cached?
   ```bash
   php artisan route:clear
   ```
3. Is subscription actually suspended?
   ```sql
   SELECT status FROM subscriptions WHERE tenant_id = '...';
   ```

### Email Verification Loop
**Symptom**: Clicking resend keeps redirecting to same page

**Check**:
1. Does resendVerification redirect to verify-email-notice?
   ```bash
   grep "verify-email-notice" app/Http/Controllers/Auth/CustomerAuthController.php
   ```
2. Are verification routes in middleware exceptions?
   ```bash
   grep "verify-email" app/Http/Middleware/CheckSubscriptionStatus.php
   ```

### View Not Found Errors
**Symptom**: "View [subscription.suspended] not found" or similar errors

**Check**:
1. Do all subscription status views exist?
   ```bash
   ls resources/views/subscription/
   # Should show: suspended.blade.php, cancelled.blade.php, required.blade.php
   ```
2. Clear view cache:
   ```bash
   php artisan view:clear
   ```
3. Verify controller methods:
   ```bash
   grep -A2 "function suspended\|function cancelled\|function required" app/Http/Controllers/SubscriptionController.php
   ```

---

## Summary

**Critical Fixes Applied**:
1. ✅ Global subscription status middleware prevents suspended tenant access
2. ✅ Customer email verification flow fixed (no infinite redirects)
3. ✅ Tenant file storage serving route enables document downloads
4. ✅ Subscription status views created (suspended, cancelled, required)

**Security Improvements**:
- Suspended tenants immediately blocked from all routes
- File access properly isolated per tenant
- Email verification can't be bypassed

**Performance Impact**:
- Minimal - one additional middleware check per request
- File serving is on-demand (no pre-caching needed)

**Next Steps**:
1. Test all functionality on suspended tenant
2. Verify file downloads work for all file types
3. Complete email verification testing
4. Investigate remaining asset URL and email link issues

---

---

## WebMonks Branding Updates

### Subscription Status Views

All three subscription status views have been updated with WebMonks branding:

**Files Updated:**
- `resources/views/subscription/suspended.blade.php`
- `resources/views/subscription/cancelled.blade.php`
- `resources/views/subscription/required.blade.php`

**Branding Elements:**
- **Logo**: `images/logo.png` (WebMonks full logo)
- **Favicon**: `images/logo-icon@2000x.png` (WebMonks monk icon)
- **Primary Color**: #17a2b8 (Teal)
- **Accent Color**: #5fd0e3 (Light Teal)
- **Typography**: Uses `theme_primary_font()` (Inter)

**Features:**
- Dynamic theme integration using `theme_styles()` helper
- CDN assets using `cdn_url()` helper
- Responsive Bootstrap 5 design
- Professional gradient backgrounds
- Smooth hover animations
- Clear call-to-action buttons

### App Settings Seeder Updates

**File Modified:**
- `database/seeders/Tenant/AppSettingsSeeder.php`

**Default Values Updated:**

**Branding:**
- Logo: `images/logo.png` → WebMonks full logo
- Logo Alt: "WebMonks Technologies"
- Favicon: `images/logo-icon@2000x.png` → WebMonks monk icon

**Theme Colors (WebMonks Teal):**
- Primary: #17a2b8 (Teal)
- Info: #5fd0e3 (Light Teal)
- Sidebar BG: #17a2b8 (Teal)
- Link Color: #17a2b8 (Teal)
- Link Hover: #138496 (Darker Teal)
- Chart Primary: rgba(23, 162, 184, 0.8) (Teal with transparency)

**CDN:**
- Added `cdn_bootstrap_css` for Bootstrap 5 CSS

**Benefits:**
- New tenants get WebMonks branding by default
- Consistent teal color scheme across all interfaces
- Professional brand identity from first login
- Easy to customize per tenant if needed

### Testing Branding

```bash
# 1. Re-seed a test tenant (if needed)
php artisan db:seed --class=Database\\Seeders\\Tenant\\AppSettingsSeeder

# 2. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 3. Test subscription status pages
# - Suspend a tenant
# - Try accessing portal
# - Should see WebMonks-branded suspension page
```

---

**Last Updated**: 2025-11-03
**Author**: Multi-Tenancy Migration Team & WebMonks Branding Update
