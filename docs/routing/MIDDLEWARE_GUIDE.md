# Middleware Guide - Multi-Tenant System

Complete guide to middleware usage, order, and best practices for the Midas Portal multi-tenant application.

## Table of Contents
1. [Middleware Overview](#middleware-overview)
2. [Core Middleware](#core-middleware)
3. [Middleware Execution Order](#middleware-execution-order)
4. [Usage Patterns](#usage-patterns)
5. [Custom Middleware](#custom-middleware)
6. [Best Practices](#best-practices)

---

## Middleware Overview

Middleware in the multi-tenant system serves **three critical purposes**:

1. **Tenant Identification**: Determine which tenant's data to access based on subdomain
2. **Domain Access Control**: Enforce which domains can access which routes
3. **Authentication & Authorization**: Verify user identity and permissions

### Middleware Types

| Type | Purpose | Examples |
|------|---------|----------|
| **Global** | Run on every request | TrustProxies, SecurityHeaders |
| **Group** | Run on route groups | `web`, `api` |
| **Route** | Run on specific routes | `auth`, `guest`, `tenant` |

---

## Core Middleware

### 1. Tenancy Middleware

#### `universal` - InitializeTenancyByDomain

**Purpose**: Identify tenant from subdomain and initialize tenant context (database switch).

**Alias**: `universal`
**Class**: `Stancl\Tenancy\Middleware\InitializeTenancyByDomain`
**Registration**: `app/Http/Kernel.php` → `$routeMiddleware`

**How It Works**:
1. Extracts subdomain from request (e.g., "acme" from "acme.midastech.testing.in")
2. Looks up tenant in `tenants` table in central database
3. Switches database connection to tenant-specific database
4. Sets tenant context for entire request lifecycle

**Usage**:
```php
// RouteServiceProvider.php
Route::middleware(['web', 'universal', 'tenant'])
    ->group(base_path('routes/web.php'));
```

**When to Use**:
- ✅ All tenant subdomain routes (staff portal, customer portal)
- ❌ Central domain routes (public website, central admin)

**Example**:
```
Request: https://acme.midastech.testing.in:8085/customers
         ↓
universal middleware:
  - Extract "acme" from subdomain
  - Find tenant in central DB: SELECT * FROM tenants WHERE id = 'acme'
  - Switch DB connection: config(['database.default' => 'tenant'])
  - Set tenant context: tenancy()->initialize($tenant)
         ↓
All subsequent queries use acme's database
```

---

#### `tenant` - PreventAccessFromCentralDomains

**Purpose**: Block access to tenant routes from central domains.

**Alias**: `tenant`
**Class**: `Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains`
**Registration**: `app/Http/Kernel.php` → `$routeMiddleware`

**How It Works**:
1. Checks if tenant context was initialized by `universal` middleware
2. If no tenant context exists, aborts with 404
3. Ensures routes are ONLY accessible from tenant subdomains

**Usage**:
```php
// RouteServiceProvider.php
Route::middleware(['web', 'universal', 'tenant'])
    ->group(base_path('routes/web.php'));
```

**When to Use**:
- ✅ All tenant routes (staff portal, customer portal)
- ❌ Central domain routes

**Example**:
```
Request from central domain: https://midastech.testing.in:8085/customers
         ↓
universal middleware: No tenant found (not a subdomain)
         ↓
tenant middleware: tenancy()->initialized == false
         ↓
Response: abort(404) - Route blocked
```

---

#### `central.only` - PreventAccessFromTenantDomains

**Purpose**: Block access to central routes from tenant subdomains.

**Alias**: `central.only`
**Class**: `App\Http\Middleware\PreventAccessFromTenantDomains`
**File**: `app/Http/Middleware/PreventAccessFromTenantDomains.php`

**How It Works**:
1. Gets current domain from request
2. Checks if domain is in `config('tenancy.central_domains')`
3. If NOT a central domain, aborts with 404
4. Ensures routes are ONLY accessible from central domains

**Usage**:
```php
// RouteServiceProvider.php
Route::middleware(['web', 'central.only'])
    ->withoutMiddleware('universal')
    ->group(base_path('routes/public.php'));
```

**When to Use**:
- ✅ Public website routes (marketing pages)
- ❌ Tenant routes
- ❌ Central admin routes (they don't need it - no tenant middleware blocks them)

**Example**:
```
Request from tenant subdomain: https://acme.midastech.testing.in:8085/features
         ↓
central.only middleware:
  - Current domain: "acme.midastech.testing.in:8085"
  - Central domains: ["midastech.testing.in:8085"]
  - Is central? false
         ↓
Response: abort(404, 'This route is only accessible from central domains')
```

---

### 2. Authentication Middleware

#### `auth` - Authenticate

**Purpose**: Verify user is authenticated, redirect if not.

**Alias**: `auth`
**Class**: `App\Http\Middleware\Authenticate`
**File**: `app/Http/Middleware/Authenticate.php`

**How It Works**:
1. Checks if user is authenticated with specified guard
2. If not authenticated, calls `redirectTo()` method
3. `redirectTo()` implements **domain-aware redirect logic**:
   - Central domain → `route('central.login')`
   - Tenant subdomain + `/customer/*` → `route('customer.login')`
   - Tenant subdomain + other → `route('login')`

**Usage**:
```php
// In routes
Route::get('/home', [HomeController::class, 'index'])
    ->middleware('auth');  // Uses default 'web' guard

// With specific guard
Route::get('/customer/dashboard', [CustomerController::class, 'dashboard'])
    ->middleware('auth:customer');  // Uses 'customer' guard
```

**When to Use**:
- ✅ All protected routes requiring authentication
- ✅ Specify guard with `auth:guard_name` syntax

**Example**:
```
Request: https://acme.midastech.testing.in:8085/customers (unauthenticated)
         ↓
auth middleware:
  - Check Auth::guard('web')->check() = false
  - Call redirectTo($request)
  - Not central domain + not /customer/* → route('login')
         ↓
Response: Redirect to /login
```

---

#### `guest` - RedirectIfAuthenticated

**Purpose**: Redirect authenticated users away from login/register pages.

**Alias**: `guest`
**Class**: `App\Http\Middleware\RedirectIfAuthenticated`
**File**: `app/Http/Middleware/RedirectIfAuthenticated.php`

**How It Works**:
1. Checks if user is authenticated with specified guard(s)
2. If authenticated, redirects to appropriate dashboard:
   - `central` guard → `route('central.dashboard')`
   - `customer` guard → `route('customer.dashboard')`
   - `web` guard → `RouteServiceProvider::HOME` (/home)

**Usage**:
```php
// In routes
Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest');  // Default 'web' guard

Route::get('/customer/login', [CustomerLoginController::class, 'showLoginForm'])
    ->middleware('guest:customer');  // 'customer' guard
```

**When to Use**:
- ✅ Login pages
- ✅ Registration pages
- ✅ Password reset request pages

**Example**:
```
Request: https://acme.midastech.testing.in:8085/login (authenticated staff)
         ↓
guest middleware:
  - Check Auth::guard('web')->check() = true
  - Guard is 'web' → redirect(RouteServiceProvider::HOME)
         ↓
Response: Redirect to /home
```

---

### 3. Authorization Middleware

#### `central.auth` - CentralAuth

**Purpose**: Verify user is authenticated as central admin.

**Alias**: `central.auth`
**Class**: `App\Http\Middleware\CentralAuth`

**Usage**:
```php
Route::middleware('central.auth')
    ->group(function () {
        // Central admin protected routes
    });
```

**When to Use**:
- ✅ All central admin protected routes
- ❌ Tenant routes

---

#### `customer.auth` - CustomerAuth

**Purpose**: Verify user is authenticated as customer.

**Alias**: `customer.auth`
**Class**: `App\Http\Middleware\CustomerAuth`

**Usage**:
```php
Route::middleware('customer.auth')
    ->group(function () {
        // Customer portal protected routes
    });
```

**When to Use**:
- ✅ All customer portal protected routes
- ❌ Staff or central admin routes

---

#### `customer.timeout` - CustomerSessionTimeout

**Purpose**: Enforce session timeout for customer security.

**Alias**: `customer.timeout`
**Class**: `App\Http\Middleware\CustomerSessionTimeout`

**Usage**:
```php
Route::middleware(['customer.auth', 'customer.timeout'])
    ->group(function () {
        // Customer routes with timeout
    });
```

---

#### `customer.secure` - SecureSession

**Purpose**: Enhanced security checks for customer sessions.

**Alias**: `customer.secure`
**Class**: `App\Http\Middleware\SecureSession`

**Usage**:
```php
Route::middleware(['customer.auth', 'customer.secure'])
    ->group(function () {
        // Customer routes with security
    });
```

---

### 4. Other Middleware

#### `role` - RoleMiddleware

**Purpose**: Check if user has specific role (Spatie Permission package).

**Alias**: `role`
**Class**: `Spatie\Permission\Middlewares\RoleMiddleware`

**Usage**:
```php
Route::middleware('role:admin')
    ->group(function () {
        // Admin-only routes
    });
```

---

#### `permission` - PermissionMiddleware

**Purpose**: Check if user has specific permission (Spatie Permission package).

**Alias**: `permission`
**Class**: `Spatie\Permission\Middlewares\PermissionMiddleware`

**Usage**:
```php
Route::middleware('permission:edit customers')
    ->group(function () {
        // Routes requiring 'edit customers' permission
    });
```

---

## Middleware Execution Order

### Order Matters!

Middleware executes in the order specified. **Incorrect order causes bugs.**

### Correct Order for Tenant Routes

```php
Route::middleware(['web', 'universal', 'tenant', 'auth'])
    ->group(function () {
        // Tenant staff routes
    });
```

**Execution Flow**:
```
1. web          → Session, CSRF, cookies
2. universal    → Identify tenant, switch database
3. tenant       → Verify tenant context exists (block central domains)
4. auth         → Verify user authentication in tenant database
```

**Why This Order?**:
- `web` must be first to establish session
- `universal` must come before `tenant` (tenant checks if universal succeeded)
- `universal` must come before `auth` (auth queries tenant database)
- `tenant` should come before `auth` (block non-tenant access early)

---

### Incorrect Order Examples

#### ❌ Wrong: `auth` before `universal`
```php
Route::middleware(['web', 'auth', 'universal', 'tenant'])
    ->group(function () {
        // BROKEN - auth runs before tenant database switch
    });
```

**Problem**: `auth` middleware queries database before `universal` switches to tenant database. Authentication always fails.

---

#### ❌ Wrong: `tenant` before `universal`
```php
Route::middleware(['web', 'tenant', 'universal'])
    ->group(function () {
        // BROKEN - tenant checks before universal identifies tenant
    });
```

**Problem**: `tenant` middleware checks if tenant context exists, but `universal` hasn't run yet to create it. Always returns 404.

---

### Correct Order for Different Route Types

#### Public Website Routes
```php
Route::middleware(['web', 'central.only'])
    ->withoutMiddleware('universal')
    ->group(base_path('routes/public.php'));
```

**Order**:
1. `web` → Session, CSRF
2. `central.only` → Block tenant subdomains
3. NO `universal` → No tenant identification needed

---

#### Central Admin Routes
```php
Route::prefix('midas-admin')
    ->middleware('web')
    ->group(base_path('routes/central.php'));

// Protected routes within central.php
Route::middleware('central.auth')
    ->group(function () {
        // Central admin routes
    });
```

**Order**:
1. `web` → Session, CSRF
2. `central.auth` (on protected routes) → Verify central admin authentication
3. NO tenant middleware → Central database only

---

#### Tenant Staff Routes
```php
Route::middleware(['web', 'universal', 'tenant'])
    ->group(base_path('routes/web.php'));

// Protected routes within web.php
Route::middleware('auth')
    ->group(function () {
        // Staff routes
    });
```

**Order**:
1. `web` → Session, CSRF
2. `universal` → Identify tenant, switch database
3. `tenant` → Block central domains
4. `auth` (on protected routes) → Verify staff authentication

---

#### Customer Portal Routes
```php
Route::middleware(['web', 'universal', 'tenant'])
    ->group(base_path('routes/customer.php'));

// Protected routes within customer.php
Route::middleware(['customer.auth', 'customer.timeout', 'customer.secure'])
    ->group(function () {
        // Customer routes
    });
```

**Order**:
1. `web` → Session, CSRF
2. `universal` → Identify tenant, switch database
3. `tenant` → Block central domains
4. `customer.auth` → Verify customer authentication
5. `customer.timeout` → Check session timeout
6. `customer.secure` → Security checks

---

## Usage Patterns

### Pattern 1: Public Routes (No Auth, Central Domain Only)

```php
// RouteServiceProvider.php
Route::middleware(['web', 'central.only'])
    ->withoutMiddleware('universal')
    ->group(base_path('routes/public.php'));
```

**Use For**:
- Marketing homepage
- Feature pages
- Pricing page
- Contact form

---

### Pattern 2: Central Admin (Auth Required, Central Domain)

```php
// routes/central.php
Route::prefix('midas-admin')->group(function () {
    // Guest routes (login)
    Route::middleware('guest:central')->group(function () {
        Route::get('/login', [CentralAuthController::class, 'showLoginForm'])
            ->name('central.login');
    });

    // Protected routes
    Route::middleware('central.auth')->group(function () {
        Route::get('/dashboard', [CentralDashboardController::class, 'index'])
            ->name('central.dashboard');
    });
});
```

**Middleware Stack**:
- Guest routes: `web` → `guest:central`
- Protected routes: `web` → `central.auth`

---

### Pattern 3: Tenant Staff (Auth Required, Tenant Subdomain)

```php
// routes/web.php (loaded with 'web', 'universal', 'tenant' from RouteServiceProvider)

// Guest routes (login)
Auth::routes(['register' => false]);  // Includes 'guest' middleware

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::resource('customers', CustomerController::class);
});
```

**Middleware Stack**:
- Guest routes: `web` → `universal` → `tenant` → `guest`
- Protected routes: `web` → `universal` → `tenant` → `auth`

---

### Pattern 4: Customer Portal (Auth Required, Tenant Subdomain)

```php
// routes/customer.php (loaded with 'web', 'universal', 'tenant' from RouteServiceProvider)

Route::prefix('customer')->group(function () {
    // Guest routes (login)
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])
            ->name('customer.login');
    });

    // Protected routes
    Route::middleware(['customer.auth', 'customer.timeout', 'customer.secure'])->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])
            ->name('customer.dashboard');
    });
});
```

**Middleware Stack**:
- Guest routes: `web` → `universal` → `tenant` → `guest:customer`
- Protected routes: `web` → `universal` → `tenant` → `customer.auth` → `customer.timeout` → `customer.secure`

---

## Custom Middleware

### Creating Custom Middleware

```bash
php artisan make:middleware MyCustomMiddleware
```

### Registration

Add to `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ...
    'my.middleware' => \App\Http\Middleware\MyCustomMiddleware::class,
];
```

### Usage

```php
Route::middleware('my.middleware')->group(function () {
    // Routes using custom middleware
});
```

---

## Best Practices

### 1. Always Use Correct Middleware Order

✅ **Right**:
```php
Route::middleware(['web', 'universal', 'tenant', 'auth'])
```

❌ **Wrong**:
```php
Route::middleware(['web', 'auth', 'universal', 'tenant'])
```

---

### 2. Use Specific Guards

✅ **Right**:
```php
Route::middleware('auth:customer')  // Explicit guard
Route::middleware('guest:central')  // Explicit guard
```

❌ **Wrong**:
```php
Route::middleware('auth')  // Ambiguous in multi-guard system
```

**Exception**: Default `web` guard for staff routes is acceptable since it's the default.

---

### 3. Don't Mix Tenancy Middleware

✅ **Right**:
```php
// Public routes - NO tenant middleware
Route::middleware(['web', 'central.only'])
    ->withoutMiddleware('universal')

// Tenant routes - FULL tenant middleware
Route::middleware(['web', 'universal', 'tenant'])
```

❌ **Wrong**:
```php
// Inconsistent - has universal but not tenant
Route::middleware(['web', 'universal'])

// Inconsistent - has tenant but not universal
Route::middleware(['web', 'tenant'])
```

---

### 4. Group Related Routes

✅ **Right**:
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('settings', SettingController::class);
    // All admin routes together
});
```

❌ **Wrong**:
```php
Route::get('/users', [UserController::class, 'index'])->middleware(['auth', 'role:admin']);
Route::get('/settings', [SettingController::class, 'index'])->middleware(['auth', 'role:admin']);
// Repeated middleware declarations
```

---

### 5. Use RouteServiceProvider for Top-Level Middleware

✅ **Right** - Apply tenant middleware in RouteServiceProvider:
```php
// RouteServiceProvider.php
Route::middleware(['web', 'universal', 'tenant'])
    ->group(base_path('routes/web.php'));
```

Then in `routes/web.php`:
```php
Route::middleware('auth')->group(function () {
    // Just add route-specific middleware
});
```

❌ **Wrong** - Repeat top-level middleware in every route file:
```php
// routes/web.php
Route::middleware(['web', 'universal', 'tenant', 'auth'])->group(function () {
    // Duplicated middleware from RouteServiceProvider
});
```

---

## Troubleshooting

### Problem: 404 on All Tenant Routes

**Check**: Middleware order

```php
// Should be:
Route::middleware(['web', 'universal', 'tenant'])

// NOT:
Route::middleware(['web', 'tenant', 'universal'])
```

---

### Problem: Authentication Always Fails

**Check**: `universal` middleware runs before `auth`

```php
// Should be:
Route::middleware(['web', 'universal', 'tenant', 'auth'])

// NOT:
Route::middleware(['web', 'auth', 'universal', 'tenant'])
```

---

### Problem: Wrong Login Redirect

**Check**: `Authenticate.php` middleware `redirectTo()` method has correct logic

**Check**: Route uses correct guard
```php
// Staff routes
Route::middleware('auth')  // or auth:web

// Customer routes
Route::middleware('auth:customer')

// Central routes
Route::middleware('auth:central')
```

---

### Problem: Middleware Not Running

**Check**: Middleware is registered in `app/Http/Kernel.php`

**Clear cache**:
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

---

## Quick Reference

### Middleware by Portal

| Portal | Middleware Stack | Purpose |
|--------|-----------------|----------|
| Public Website | `web`, `central.only` | Public access, central only |
| Central Admin | `web`, `central.auth` | Platform admin |
| Tenant Staff | `web`, `universal`, `tenant`, `auth` | Business operations |
| Customer Portal | `web`, `universal`, `tenant`, `customer.auth` | Customer self-service |

### Middleware Execution Order

```
1. web                → Session, CSRF (ALWAYS FIRST)
2. central.only       → Block tenant access (public routes)
   OR universal       → Identify tenant (tenant routes)
3. tenant             → Block central access (tenant routes)
4. auth/central.auth/ → Verify authentication
   customer.auth
5. role/permission    → Verify authorization
```

### Common Patterns

```php
// Public (no auth, central only)
['web', 'central.only']

// Central admin (with auth)
['web', 'central.auth']

// Tenant staff (with auth)
['web', 'universal', 'tenant', 'auth']

// Customer portal (with auth)
['web', 'universal', 'tenant', 'customer.auth']
```
