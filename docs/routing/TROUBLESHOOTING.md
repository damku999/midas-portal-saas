# Routing Troubleshooting Guide

Common routing issues, their causes, and solutions for the multi-tenant Midas Portal application.

**Last Updated:** 2025-11-02 - See [Recent Fixes](#recent-fixes-2025-11-02)

## Table of Contents
1. [Recent Fixes (2025-11-02)](#recent-fixes-2025-11-02) ⭐ NEW
2. [404 Errors](#404-errors)
3. [Authentication Issues](#authentication-issues)
4. [Redirect Problems](#redirect-problems)
5. [Tenant Identification](#tenant-identification)
6. [Performance Issues](#performance-issues)
7. [Cache Problems](#cache-problems)

---

## Recent Fixes (2025-11-02)

### ✅ FIXED: Public Routes Not Appearing in Route List

**Issue**: Public website routes (/, /features, /pricing, etc.) were not properly registered in the route cache.

**Root Cause**: Incorrect use of `withoutMiddleware('universal')` in `RouteServiceProvider.php` line 79.

**Solution Applied**: Removed unnecessary `withoutMiddleware()` call:

```php
// BEFORE (WRONG)
Route::middleware(['web', 'central.only'])
    ->withoutMiddleware('universal')  // ❌ Problematic
    ->namespace($this->namespace)
    ->group(base_path('routes/public.php'));

// AFTER (FIXED)
Route::middleware(['web', 'central.only'])
    ->namespace($this->namespace)
    ->group(base_path('routes/public.php'));
```

**Impact**: Public routes now load correctly on central domain.

**Related File**: `app/Providers/RouteServiceProvider.php:79`

---

### ✅ FIXED: Customer 2FA Routes Duplicate Prefix

**Issue**: Customer portal 2FA routes had duplicate route names:
- `customer.customer.two-factor.challenge` (WRONG)
- `customer.customer.two-factor.verify` (WRONG)

**Root Cause**: Routes inside `Route::prefix('customer')->name('customer.')` group manually added `customer.` prefix again.

**Solution Applied**: Removed duplicate prefix from route names:

```php
// BEFORE (WRONG)
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('/two-factor-challenge', ...)
        ->name('customer.two-factor.challenge');  // Results in: customer.customer.two-factor.challenge
});

// AFTER (FIXED)
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('/two-factor-challenge', ...)
        ->name('two-factor.challenge');  // Results in: customer.two-factor.challenge
});
```

**Impact**: Customer 2FA authentication flow now works correctly.

**Related File**: `routes/customer.php:123-129`

---

### 📝 Quick Fix Verification

After recent fixes, verify routes are working:

```bash
# 1. Clear and rebuild route cache
php artisan route:clear
php artisan route:cache

# 2. Verify public routes exist
php artisan route:list --name=public
# Should show: public.about, public.features, public.pricing, public.contact

# 3. Verify customer 2FA routes
php artisan route:list --path=customer --name=two-factor
# Should show: customer.two-factor.challenge, customer.two-factor.verify (NOT customer.customer.*)

# 4. Test access
# Public: http://midastech.testing.in:8085/
# Customer 2FA: http://demo.midastech.testing.in:8085/customer/two-factor-challenge
```

**Full Details**: See [ROUTING_FIXES_2025-11-02.md](ROUTING_FIXES_2025-11-02.md)

---

## 404 Errors

### Issue 1: 404 on Tenant Routes from Central Domain

**Symptom**:
```
URL: https://midastech.testing.in:8085/home
Response: 404 Not Found
```

**Cause**: The `tenant` middleware (PreventAccessFromCentralDomains) blocks tenant routes when accessed from central domain.

**Solution**: This is **correct behavior** by design. Use tenant subdomain:
```
✅ Correct: https://acme.midastech.testing.in:8085/home
❌ Wrong: https://midastech.testing.in:8085/home
```

**Verification**:
```php
// Check RouteServiceProvider.php
Route::middleware(['web', 'universal', 'tenant'])  // 'tenant' blocks central
    ->group(base_path('routes/web.php'));
```

---

### Issue 2: 404 on Public Routes from Tenant Subdomain

**Symptom**:
```
URL: https://acme.midastech.testing.in:8085/features
Response: 404 Not Found
```

**Cause**: The `central.only` middleware blocks public website routes when accessed from tenant subdomain.

**Solution**: This is **correct behavior** by design. Use central domain:
```
✅ Correct: https://midastech.testing.in:8085/features
❌ Wrong: https://acme.midastech.testing.in:8085/features
```

**Verification**:
```php
// Check RouteServiceProvider.php
Route::middleware(['web', 'central.only'])  // Blocks tenant subdomains
    ->group(base_path('routes/public.php'));
```

---

### Issue 3: 404 on All Routes After Changes

**Symptom**:
```
All routes return 404 after making routing changes
```

**Cause**: Route cache is outdated.

**Solution**: Clear route cache:
```bash
php artisan route:clear
php artisan route:cache  # Optional: rebuild cache
```

**Verification**:
```bash
# List all registered routes
php artisan route:list
```

---

### Issue 4: Route Not Found After Creating New Route

**Symptom**:
```
New route added to routes/web.php but returns 404
```

**Cause 1**: Route cache not cleared

**Solution**:
```bash
php artisan route:clear
```

**Cause 2**: Route conflicts with existing route (earlier route matches first)

**Solution**: Check route order in `php artisan route:list`, move specific routes before generic ones:

❌ **Wrong Order**:
```php
Route::get('/users/{id}', ...);     // Matches first
Route::get('/users/create', ...);   // Never reached (matched by above)
```

✅ **Right Order**:
```php
Route::get('/users/create', ...);   // Specific route first
Route::get('/users/{id}', ...);     // Generic route last
```

---

### Issue 5: Central Admin Routes 404 from Central Domain

**Symptom**:
```
URL: https://midastech.testing.in:8085/midas-admin/login
Response: 404 Not Found
```

**Cause 1**: Central routes not loaded properly in RouteServiceProvider

**Solution**: Check `app/Providers/RouteServiceProvider.php`:
```php
Route::prefix('midas-admin')
    ->middleware('web')
    ->group(base_path('routes/central.php'));
```

**Cause 2**: Route file doesn't exist or has syntax errors

**Solution**: Check file exists and syntax:
```bash
# Check file exists
ls routes/central.php

# Check syntax
php artisan route:list --name=central
```

---

## Authentication Issues

### Issue 6: Authentication Always Fails on Tenant Routes

**Symptom**:
```
Login credentials correct but always redirected back to login
```

**Cause**: `auth` middleware runs before `universal` middleware, so authentication checks central database instead of tenant database.

**Solution**: Ensure correct middleware order in `RouteServiceProvider.php`:

❌ **Wrong**:
```php
Route::middleware(['web', 'auth', 'universal', 'tenant'])
```

✅ **Right**:
```php
Route::middleware(['web', 'universal', 'tenant', 'auth'])
```

**Verification**:
```bash
# Check middleware order
php artisan route:list --path=home
```

---

### Issue 7: Wrong Guard Being Used

**Symptom**:
```
Customer login not working on /customer/login
```

**Cause**: Route uses wrong guard or no guard specified.

**Solution**: Explicitly specify guard in route middleware:

❌ **Wrong**:
```php
// routes/customer.php
Route::middleware('auth')->group(function () {
    // Uses default 'web' guard, not 'customer'
});
```

✅ **Right**:
```php
// routes/customer.php
Route::middleware('auth:customer')->group(function () {
    // Explicitly uses 'customer' guard
});
```

Or use custom middleware:
```php
Route::middleware('customer.auth')->group(function () {
    // Custom middleware enforces customer guard
});
```

**Verification**:
```bash
# Check what middleware is applied
php artisan route:list --path=customer
```

---

### Issue 8: Session Lost After Redirect

**Symptom**:
```
User logs in successfully but session lost after redirect
```

**Cause 1**: Session domain configuration incorrect

**Solution**: Check `.env` and `config/session.php`:
```env
# .env
SESSION_DOMAIN=.midastech.testing.in
# Note the leading dot for subdomain support
```

```php
// config/session.php
'domain' => env('SESSION_DOMAIN', '.midastech.testing.in'),
```

**Cause 2**: HTTPS/HTTP mismatch

**Solution**: Ensure consistent protocol:
```env
# .env
SESSION_SECURE_COOKIE=false  # for HTTP (development)
SESSION_SECURE_COOKIE=true   # for HTTPS (production)
```

---

## Redirect Problems

### Issue 9: Redirected to Wrong Login Page

**Symptom**:
```
Accessing /customer/dashboard (unauthenticated)
Redirected to /login instead of /customer/login
```

**Cause**: `Authenticate` middleware doesn't detect `/customer/*` routes properly.

**Solution**: Check `app/Http/Middleware/Authenticate.php`:

```php
protected function redirectTo($request)
{
    if (! $request->expectsJson()) {
        // ... central domain check ...

        // Check customer routes
        if ($request->is('customer/*')) {  // THIS LINE MUST EXIST
            return route('customer.login');
        }

        return route('login');
    }
}
```

**Verification**:
```bash
# Test unauthenticated access
curl -I https://acme.midastech.testing.in:8085/customer/dashboard
# Should redirect to /customer/login
```

---

### Issue 10: Infinite Redirect Loop

**Symptom**:
```
Browser shows "Too many redirects" error
```

**Cause 1**: Login route has `auth` middleware

**Solution**: Login routes should use `guest` middleware, not `auth`:

❌ **Wrong**:
```php
Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('auth');  // WRONG - creates loop
```

✅ **Right**:
```php
Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest');
```

**Cause 2**: `RedirectIfAuthenticated` middleware redirects to login

**Solution**: Check `app/Http/Middleware/RedirectIfAuthenticated.php` redirects to dashboard, not login:

❌ **Wrong**:
```php
if (Auth::guard($guard)->check()) {
    return redirect()->route('login');  // WRONG - creates loop
}
```

✅ **Right**:
```php
if (Auth::guard($guard)->check()) {
    return redirect()->route('home');  // or customer.dashboard
}
```

---

### Issue 11: Redirected to Central Login from Tenant Subdomain

**Symptom**:
```
Accessing https://acme.midastech.testing.in:8085/home (unauthenticated)
Redirected to https://midastech.testing.in:8085/midas-admin/login
```

**Cause**: `Authenticate` middleware incorrectly identifies domain as central.

**Solution**: Check domain detection logic in `app/Http/Middleware/Authenticate.php`:

```php
protected function redirectTo($request)
{
    $currentDomain = $request->getHost();
    $currentDomainWithPort = $request->getHttpHost();
    $centralDomains = config('tenancy.central_domains', []);

    // Check all domain formats
    $isCentralDomain = in_array($currentDomain, $centralDomains)
                    || in_array($currentDomainWithPort, $centralDomains);

    // Debug: Log what's being checked
    \Log::debug('Domain check', [
        'currentDomain' => $currentDomain,
        'currentDomainWithPort' => $currentDomainWithPort,
        'centralDomains' => $centralDomains,
        'isCentral' => $isCentralDomain
    ]);

    if ($isCentralDomain) {
        return route('central.login');
    }

    // ... rest of logic
}
```

**Verification**:
```bash
# Check config
php artisan tinker
>>> config('tenancy.central_domains')
```

---

### Issue 12: Already Authenticated User Not Redirected from Login

**Symptom**:
```
User already logged in but can still access /login page
```

**Cause**: `guest` middleware not applied to login routes.

**Solution**: Add `guest` middleware:

```php
Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest');  // Add this

Route::get('/customer/login', [CustomerLoginController::class, 'showLoginForm'])
    ->middleware('guest:customer');  // Add this with guard
```

**Verification**: Visit login page while authenticated - should redirect to dashboard.

---

## Tenant Identification

### Issue 13: "Tenant could not be identified"

**Symptom**:
```
Request: https://acme.midastech.testing.in:8085/home
Error: Tenant could not be identified on domain acme.midastech.testing.in:8085
```

**Cause 1**: Tenant doesn't exist in `tenants` table

**Solution**: Create tenant via central admin:
```
1. Login to https://midastech.testing.in:8085/midas-admin/login
2. Navigate to Tenants management
3. Create tenant with id/subdomain "acme"
```

Or via tinker:
```bash
php artisan tinker
>>> $tenant = \App\Models\Central\Tenant::create(['id' => 'acme']);
>>> $tenant->domains()->create(['domain' => 'acme.midastech.testing.in:8085']);
```

**Cause 2**: Domain not registered for tenant

**Solution**: Check tenant domains:
```bash
php artisan tinker
>>> $tenant = \App\Models\Central\Tenant::find('acme');
>>> $tenant->domains;  // Check if domain exists
>>> $tenant->domains()->create(['domain' => 'acme.midastech.testing.in:8085']);
```

**Verification**:
```bash
php artisan tenants:list
```

---

### Issue 14: Wrong Tenant Database Being Used

**Symptom**:
```
Logged into tenant "acme" but seeing data from tenant "xyz"
```

**Cause**: Tenant context not being initialized properly or cached incorrectly.

**Solution 1**: Clear all caches:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

**Solution 2**: Check `universal` middleware is applied:
```php
// RouteServiceProvider.php
Route::middleware(['web', 'universal', 'tenant'])  // 'universal' MUST be here
    ->group(base_path('routes/web.php'));
```

**Solution 3**: Debug tenant identification:
```php
// Add to controller
public function index()
{
    \Log::debug('Tenant context', [
        'tenant_id' => tenant('id'),
        'database' => config('database.connections.tenant.database'),
    ]);

    // ... rest of logic
}
```

---

## Performance Issues

### Issue 15: Slow Route Resolution

**Symptom**:
```
Routes take several seconds to load
```

**Cause**: Route cache not being used in production.

**Solution**: Cache routes:
```bash
php artisan route:cache
```

**Note**: After caching, any route changes require re-caching:
```bash
php artisan route:clear
php artisan route:cache
```

**Verification**:
```bash
# Check if cache exists
ls bootstrap/cache/routes-v7.php
```

---

### Issue 16: Memory Issues with Large Route Files

**Symptom**:
```
PHP memory exhausted when loading routes
```

**Cause**: Too many routes defined in single file or inefficient route definitions.

**Solution 1**: Split routes into logical groups:
```
routes/
  web.php           → Main tenant routes
  customer.php      → Customer portal
  central.php       → Central admin
  api.php           → API routes
  webhooks.php      → Webhook handlers
```

**Solution 2**: Use route caching:
```bash
php artisan route:cache
```

**Solution 3**: Increase PHP memory limit:
```ini
; php.ini
memory_limit = 256M
```

---

## Cache Problems

### Issue 17: Changes Not Reflecting

**Symptom**:
```
Changed route in routes/web.php but still hitting old route
```

**Cause**: Route cache not cleared.

**Solution**: Clear all caches:
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Restart services
php artisan serve  # or restart web server
```

**Verification**:
```bash
php artisan route:list | grep "your-route-name"
```

---

### Issue 18: Middleware Changes Not Applied

**Symptom**:
```
Added middleware to route but not executing
```

**Cause 1**: Config cache not cleared

**Solution**:
```bash
php artisan config:clear
php artisan route:clear
```

**Cause 2**: Middleware not registered in Kernel

**Solution**: Check `app/Http/Kernel.php`:
```php
protected $routeMiddleware = [
    'my.middleware' => \App\Http\Middleware\MyMiddleware::class,
];
```

**Verification**:
```bash
# Check middleware on route
php artisan route:list --name=your-route --columns=name,middleware
```

---

## Diagnostic Commands

### Check Route Registration
```bash
# List all routes
php artisan route:list

# Filter by name
php artisan route:list --name=customer

# Filter by path
php artisan route:list --path=customer

# Show specific columns
php artisan route:list --columns=method,uri,name,middleware
```

### Check Tenant Configuration
```bash
# List all tenants
php artisan tenants:list

# Run command in tenant context
php artisan tenants:run "php artisan db:seed"

# Check tenant database
php artisan tinker
>>> tenant('id')
>>> config('database.connections.tenant.database')
```

### Check Middleware Registration
```bash
php artisan route:list --name=home --columns=name,middleware
```

### Debug Request Flow
Add to route:
```php
Route::get('/debug', function (\Illuminate\Http\Request $request) {
    return [
        'domain' => $request->getHost(),
        'domain_with_port' => $request->getHttpHost(),
        'url' => $request->url(),
        'tenant_id' => tenant('id'),
        'auth_web' => auth()->check(),
        'auth_customer' => auth()->guard('customer')->check(),
        'auth_central' => auth()->guard('central')->check(),
    ];
});
```

---

## Emergency Recovery

### Complete Cache Clear
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
composer dump-autoload
```

### Reset All Routes
```bash
# Backup current routes
cp routes/web.php routes/web.php.backup

# Clear and recache
php artisan route:clear
php artisan route:cache

# Restart services
# For Apache/Nginx
sudo systemctl restart apache2
# or
sudo systemctl restart nginx

# For php artisan serve
# Ctrl+C and restart
php artisan serve
```

### Check for Syntax Errors
```bash
# Check route files
php -l routes/web.php
php -l routes/central.php
php -l routes/customer.php
php -l routes/public.php

# Check provider
php -l app/Providers/RouteServiceProvider.php

# Check middleware
php -l app/Http/Middleware/Authenticate.php
php -l app/Http/Middleware/RedirectIfAuthenticated.php
```

---

## Quick Diagnostics Checklist

When facing routing issues, run through this checklist:

- [ ] **Clear all caches** (`route:clear`, `config:clear`, `cache:clear`)
- [ ] **Check route exists** (`php artisan route:list --name=...`)
- [ ] **Verify middleware order** (universal before tenant, universal before auth)
- [ ] **Check domain configuration** (`config('tenancy.central_domains')`)
- [ ] **Verify tenant exists** (`php artisan tenants:list`)
- [ ] **Check guard usage** (auth:customer for customer routes, etc.)
- [ ] **Verify redirect logic** (Authenticate.php, RedirectIfAuthenticated.php)
- [ ] **Check for route conflicts** (specific routes before generic ones)
- [ ] **Verify session configuration** (SESSION_DOMAIN in .env)
- [ ] **Restart web server** (after config changes)

---

## Getting Help

If issues persist after trying solutions above:

1. **Check Laravel logs**:
   ```
   storage/logs/laravel.log
   ```

2. **Enable debug mode** (development only):
   ```env
   APP_DEBUG=true
   ```

3. **Check error details** in browser developer console (Network tab)

4. **Run diagnostics**:
   ```bash
   php artisan route:list
   php artisan tenants:list
   php artisan config:show tenancy
   ```

5. **Review documentation**:
   - `docs/routing/ROUTING_ARCHITECTURE.md`
   - `docs/routing/DOMAIN_ROUTING_GUIDE.md`
   - `docs/routing/MIDDLEWARE_GUIDE.md`
