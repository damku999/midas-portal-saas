# Multi-Portal Architecture - Deep Dive

**Project**: Midas Insurance Portal
**Architecture Pattern**: Domain-Based Multi-Portal SaaS
**Laravel Version**: 10.49.1
**Last Updated**: 2025-11-06

---

## Table of Contents

1. [Overview](#overview)
2. [Four-Portal System](#four-portal-system)
3. [Route Loading Architecture](#route-loading-architecture)
4. [Domain-Based Access Control](#domain-based-access-control)
5. [Authentication Flows](#authentication-flows)
6. [Middleware Execution](#middleware-execution)
7. [Session Management](#session-management)
8. [Security Architecture](#security-architecture)
9. [Request Flow Diagrams](#request-flow-diagrams)
10. [Troubleshooting Common Issues](#troubleshooting-common-issues)

---

## Overview

The Midas Portal implements a **4-portal architecture** with strict domain-based separation. This design ensures complete isolation between:
- Public-facing marketing content
- Platform administration
- Tenant business operations
- Customer self-service portals

### Architecture Goals

1. **Complete Isolation**: No cross-portal data leakage or authentication bypass
2. **Domain-Based Security**: Middleware enforces strict access boundaries
3. **Scalable Multi-Tenancy**: Support 100+ tenants on single infrastructure
4. **Clear Separation of Concerns**: Each portal has distinct purpose and access patterns
5. **Maintainable Codebase**: Route organization prevents conflicts and confusion

---

## Four-Portal System

### Portal Comparison Matrix

| Feature | Public Website | Central Admin | Tenant Staff | Customer Portal |
|---------|---------------|---------------|--------------|-----------------|
| **Domain** | midastech.in | midastech.in/midas-admin | {tenant}.midastech.in | {tenant}.midastech.in/customer |
| **Authentication** | None | `central` guard | `web` guard | `customer` guard |
| **Database** | Central (read-only queries) | Central (full access) | Tenant-specific | Tenant-specific |
| **User Model** | N/A | `Central\CentralAdmin` | `User` | `Customer` |
| **Primary Middleware** | `web`, `central.only` | `web`, `central.auth` | `web`, `tenant`, `subscription.status`, `auth` | `web`, `tenant`, `subscription.status`, `customer.auth` |
| **Route File** | `routes/public.php` | `routes/central.php` | `routes/web.php` | `routes/customer.php` |
| **View Directory** | `resources/views/public/` | `resources/views/central/` | `resources/views/` (main) | `resources/views/customer/` |
| **Purpose** | Marketing, lead gen | Platform management | Business operations | Self-service portal |
| **User Access** | Anonymous visitors | Super admins | Tenant staff | Tenant customers |

---

## Route Loading Architecture

### Critical Loading Order

Routes are loaded in **specific order** to prevent conflicts (defined in `RouteServiceProvider.php`):

```
1. Central Admin Routes (/midas-admin/*)
   ↓
2. Public Website Routes (/, /features, /pricing, /contact)
   ↓
3. Tenant Staff Portal Routes (/* - all business routes)
   ↓
4. Customer Portal Routes (/customer/*)
   ↓
5. Tenant Storage Routes (/storage/tenant-files/*)
```

### Why This Order Matters

**Problem**: The `/` route is needed by both Public Website and Tenant Staff Portal
**Solution**: Load Public Website routes with domain restrictions BEFORE tenant routes

```php
// RouteServiceProvider.php (lines 80-85)
// Load Public Website ONLY for central domains
foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)
        ->middleware('web')
        ->group(base_path('routes/public.php'));
}

// Then load Tenant routes (lines 98-100)
// These will NOT match central domains due to domain() restriction above
Route::middleware(['web', 'tenant', 'subscription.status'])
    ->group(base_path('routes/web.php'));
```

**Result**:
- `midastech.in/` → Public Website homepage
- `acme.midastech.in/` → Tenant Staff Portal redirect to login/dashboard

### Route Registration Details

#### 1. Central Admin Routes

```php
Route::prefix('midas-admin')
    ->middleware('web')
    ->group(base_path('routes/central.php'));
```

- **Prefix**: All routes prefixed with `/midas-admin`
- **Middleware**: Only `web` (session, CSRF, cookies)
- **No Tenant Middleware**: Operates exclusively on central database
- **Access**: Restricted by `central.auth` middleware inside route file

#### 2. Public Website Routes

```php
foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)
        ->middleware('web')
        ->group(base_path('routes/public.php'));
}
```

- **Domain Restriction**: Only registered on central domains
- **Middleware**: `web` + `central.only` (defined in route file)
- **Key Feature**: Domain-level registration prevents tenant middleware execution
- **Routes**: `/`, `/features`, `/pricing`, `/about`, `/contact`

**Central Domains Configuration** (`config/tenancy.php`):
```php
'central_domains' => [
    'midastech.in',                    // Production
    'midastech.testing.in',            // Testing
    'midastech.testing.in:8085',       // Local development with port
    'localhost',                       // Local development
],
```

#### 3. Tenant Staff Portal Routes

```php
Route::middleware(['web', 'tenant', 'subscription.status'])
    ->group(base_path('routes/web.php'));
```

- **Global Middleware**: `InitializeTenancyByDomainEarly` runs BEFORE route matching
- **Middleware Stack**: `web` → `tenant` → `subscription.status`
- **Protected Routes**: Add `auth` middleware inside route file
- **Database**: Automatically switched to tenant-specific database

#### 4. Customer Portal Routes

```php
Route::middleware(['web', 'tenant', 'subscription.status'])
    ->group(base_path('routes/customer.php'));
```

- **Same Base Middleware**: As tenant staff portal
- **Different Guard**: Uses `customer` guard for authentication
- **Path Prefix**: All routes start with `/customer/`
- **Access Control**: `customer.auth` + `customer.timeout` middleware

#### 5. Tenant Storage Routes

```php
Route::middleware(['web', 'tenant'])
    ->group(base_path('routes/tenant-storage.php'));
```

- **NO Subscription Check**: Allows file access even for suspended tenants
- **Purpose**: Serve uploaded files from tenant-specific storage
- **Security**: Validates file ownership before serving

---

## Domain-Based Access Control

### Access Matrix

| URL | Domain Type | Portal Accessed | Auth Required | Database Used |
|-----|-------------|-----------------|---------------|---------------|
| `midastech.in/` | Central | Public Website | ❌ | Central (read-only) |
| `midastech.in/features` | Central | Public Website | ❌ | Central (read-only) |
| `midastech.in/midas-admin` | Central | Central Admin | ✅ (central guard) | Central |
| `midastech.in/midas-admin/tenants` | Central | Central Admin | ✅ (central guard) | Central |
| `midastech.in/login` | Central | Redirect → `/midas-admin/login` | ❌ | N/A |
| `midastech.in/customers` | Central | **404 Error** | N/A | N/A |
| `acme.midastech.in/` | Tenant | Tenant Staff (redirect) | ❌ (redirects to /login) | Tenant (acme_db) |
| `acme.midastech.in/login` | Tenant | Tenant Staff | ❌ (login page) | Tenant (acme_db) |
| `acme.midastech.in/home` | Tenant | Tenant Staff | ✅ (web guard) | Tenant (acme_db) |
| `acme.midastech.in/customers` | Tenant | Tenant Staff | ✅ (web guard) | Tenant (acme_db) |
| `acme.midastech.in/customer/login` | Tenant | Customer Portal | ❌ (login page) | Tenant (acme_db) |
| `acme.midastech.in/customer/dashboard` | Tenant | Customer Portal | ✅ (customer guard) | Tenant (acme_db) |
| `acme.midastech.in/features` | Tenant | **404 Error** | N/A | N/A |
| `acme.midastech.in/midas-admin` | Tenant | **404 Error** | N/A | N/A |

### Middleware Enforcement

#### `central.only` - PreventAccessFromTenantDomains

**File**: `app/Http/Middleware/PreventAccessFromTenantDomains.php`

**Purpose**: Block tenant subdomains from accessing public website routes

**Logic**:
```php
public function handle($request, Closure $next)
{
    $currentDomain = $request->getHost();
    $centralDomains = config('tenancy.central_domains');

    // Check if current domain is a central domain
    if (!in_array($currentDomain, $centralDomains)) {
        abort(404); // Tenant subdomain trying to access central route
    }

    return $next($request);
}
```

**Used On**: Public website routes only

#### `tenant` - PreventAccessFromCentralDomains

**Package**: `Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains`

**Purpose**: Block central domains from accessing tenant routes

**Logic**:
```php
public function handle($request, Closure $next)
{
    // Check if tenancy was initialized by InitializeTenancyByDomain
    if (!tenancy()->initialized) {
        abort(404); // Central domain trying to access tenant route
    }

    return $next($request);
}
```

**Used On**: All tenant staff portal and customer portal routes

---

## Authentication Flows

### 1. Public Website (No Authentication)

```
User Request: midastech.in/features
    ↓
Route matched: GET /features (routes/public.php)
    ↓
Middleware: web → central.only
    ↓
central.only check:
    - Current domain: midastech.in
    - Is in central_domains? YES
    - Allow access ✅
    ↓
Controller: PublicController@features
    ↓
Response: Render features.blade.php
```

### 2. Central Admin Login Flow

```
User Request: midastech.in/midas-admin/login
    ↓
Route matched: GET /midas-admin/login (routes/central.php)
    ↓
Middleware: web
    ↓
Check guest:central → User not authenticated, show login form
    ↓
User submits credentials
    ↓
POST /midas-admin/login
    ↓
Controller: Central\AuthController@login
    ↓
Validate credentials against central database:
    - Query: central_admins table
    - Guard: Auth::guard('central')->attempt($credentials)
    ↓
Authentication Success:
    - Session created with 'central' guard
    - Redirect: route('central.dashboard') → /midas-admin/dashboard
    ↓
Subsequent requests include central.auth middleware:
    - Checks: Auth::guard('central')->check()
    - Allows access if authenticated
```

### 3. Tenant Staff Login Flow

```
User Request: acme.midastech.in/login
    ↓
GLOBAL Middleware: InitializeTenancyByDomainEarly
    - Extracts subdomain: "acme"
    - Looks up tenant in central database
    - Found: tenant_id = 5, database = acme_db
    - Switches default connection to acme_db
    - Sets tenancy()->tenant = Tenant object
    ↓
Route matched: GET /login (routes/web.php)
    ↓
Middleware: web → tenant → guest:web
    ↓
tenant middleware check:
    - tenancy()->initialized? YES
    - Allow access ✅
    ↓
guest:web check:
    - Auth::guard('web')->check()? NO
    - Show login form
    ↓
User submits credentials
    ↓
POST /login
    ↓
Controller: Auth\LoginController@login
    ↓
Validate credentials against TENANT database:
    - Query: users table in acme_db
    - Guard: Auth::guard('web')->attempt($credentials)
    ↓
Authentication Success:
    - Session created with 'web' guard
    - Redirect: /home
    ↓
Subsequent requests:
    - InitializeTenancyByDomainEarly: Switches to acme_db
    - auth middleware: Checks Auth::guard('web')->check()
    - subscription.status: Checks tenant subscription status
```

### 4. Customer Portal Login Flow

```
User Request: acme.midastech.in/customer/login
    ↓
GLOBAL Middleware: InitializeTenancyByDomainEarly
    - Same tenant identification as staff portal
    - Switches to acme_db
    ↓
Route matched: GET /customer/login (routes/customer.php)
    ↓
Middleware: web → tenant → guest:customer
    ↓
guest:customer check:
    - Auth::guard('customer')->check()? NO
    - Show customer login form
    ↓
User submits credentials
    ↓
POST /customer/login
    ↓
Controller: Auth\CustomerAuthController@login
    ↓
Validate credentials against TENANT database:
    - Query: customers table in acme_db
    - Guard: Auth::guard('customer')->attempt($credentials)
    - Additional checks: email_verified_at, status = active
    ↓
Authentication Success:
    - Session created with 'customer' guard
    - Session extended data: device fingerprint, login timestamp
    - Redirect: route('customer.dashboard') → /customer/dashboard
    ↓
Subsequent requests:
    - customer.auth middleware: Checks Auth::guard('customer')->check()
    - customer.timeout: Enforces 30-minute session timeout
    - customer.family: (optional) Validates family group access
```

---

## Middleware Execution

### Complete Middleware Stack by Portal

#### Public Website Request

```
Request: GET midastech.in/features
    ↓
[GLOBAL] StartSession → Initialize session
[GLOBAL] ShareErrorsFromSession → Share validation errors
[GLOBAL] VerifyCsrfToken → CSRF protection (GET exempt)
    ↓
[Route Group] web middleware:
    1. EncryptCookies
    2. AddQueuedCookiesToResponse
    3. StartSession (already ran globally)
    4. ShareErrorsFromSession (already ran globally)
    5. VerifyCsrfToken (already ran globally)
    6. SubstituteBindings
    ↓
[Route Group] central.only middleware:
    - PreventAccessFromTenantDomains
    - Checks domain is in central_domains list
    ↓
Controller: PublicController@features
```

#### Central Admin Request

```
Request: GET midastech.in/midas-admin/tenants
    ↓
[GLOBAL] Standard Laravel middleware
    ↓
[Route Group] web middleware (same as public website)
    ↓
[Route Specific] central.auth middleware:
    - CentralAuth
    - Checks Auth::guard('central')->check()
    - Redirects to /midas-admin/login if not authenticated
    ↓
Controller: Central\TenantController@index
```

#### Tenant Staff Request

```
Request: GET acme.midastech.in/customers
    ↓
[GLOBAL - BEFORE ROUTING] InitializeTenancyByDomainEarly:
    - Extracts subdomain: "acme"
    - Switches database to acme_db
    - Sets tenancy context
    ↓
[GLOBAL] Standard Laravel middleware
    ↓
[Route Group] web middleware
    ↓
[Route Group] tenant middleware:
    - PreventAccessFromCentralDomains
    - Checks tenancy()->initialized === true
    ↓
[Route Group] subscription.status middleware:
    - CheckSubscriptionStatus
    - Checks tenant subscription status
    - Aborts if status === 'suspended', 'cancelled', 'expired'
    - Allows trial, active, grace_period
    ↓
[Route Specific] auth middleware:
    - Authenticate
    - Checks Auth::guard('web')->check()
    - Redirects to /login if not authenticated
    ↓
Controller: CustomerController@index
```

#### Customer Portal Request

```
Request: GET acme.midastech.in/customer/dashboard
    ↓
[GLOBAL - BEFORE ROUTING] InitializeTenancyByDomainEarly
    (same as tenant staff)
    ↓
[GLOBAL] Standard Laravel middleware
    ↓
[Route Group] web middleware
    ↓
[Route Group] tenant middleware
    (same as tenant staff)
    ↓
[Route Group] subscription.status middleware
    (same as tenant staff)
    ↓
[Route Specific] customer.auth middleware:
    - CustomerAuth
    - Checks Auth::guard('customer')->check()
    - Redirects to /customer/login if not authenticated
    ↓
[Route Specific] customer.timeout middleware:
    - CustomerSessionTimeout
    - Checks last activity timestamp
    - Logs out if idle > 30 minutes
    ↓
Controller: Auth\CustomerAuthController@dashboard
```

### Middleware Execution Order Rules

**Critical Order for Tenant Routes**:
```
1. InitializeTenancyByDomainEarly (GLOBAL - before routing)
2. web (session, CSRF, cookies)
3. tenant (verify tenancy initialized)
4. subscription.status (check tenant subscription)
5. auth / customer.auth (verify user authentication)
6. customer.timeout (session timeout - customer portal only)
```

**Why This Order?**:
1. Tenancy MUST initialize before session (session stored in tenant context)
2. Session MUST exist before tenant check (tenant middleware checks session-stored data)
3. Subscription check AFTER tenancy (requires tenant object)
4. Auth check AFTER subscription (no point authenticating suspended tenants)
5. Timeout check AFTER auth (requires authenticated user)

---

## Session Management

### Guard-Specific Sessions

Laravel maintains **separate session data** for each guard:

```php
// Session structure for multi-guard authentication
session()->all() = [
    '_token' => 'csrf_token_value',

    // Web guard (tenant staff)
    'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => user_id,

    // Customer guard (customer portal)
    'login_customer_59ba36addc2b2f9401580f014c7f58ea4e30989d' => customer_id,

    // Central guard (central admin)
    'login_central_59ba36addc2b2f9401580f014c7f58ea4e30989d' => admin_id,

    // Tenancy context
    'tenancy_tenant_id' => 5,

    // Other session data
    'url' => ['intended' => '/customers'],
]
```

### Session Storage by Portal

| Portal | Session Driver | Storage Location | Scope |
|--------|---------------|------------------|-------|
| Public Website | file/redis | `storage/framework/sessions/` | No user-specific data |
| Central Admin | file/redis | `storage/framework/sessions/` | Central admin user |
| Tenant Staff | file/redis | `storage/framework/sessions/` | Tenant staff user |
| Customer Portal | file/redis | `storage/framework/sessions/` | Customer user |

**Important**: All sessions stored in same location but isolated by guard-specific keys.

### Tenant Context in Session

When tenancy is initialized:
```php
// Stored in session by Stancl Tenancy
session()->put('tenancy_tenant_id', $tenant->id);

// Retrieved on subsequent requests
$tenantId = session('tenancy_tenant_id');
$tenant = Tenant::find($tenantId);
tenancy()->initialize($tenant);
```

---

## Security Architecture

### Cross-Portal Security Measures

#### 1. Domain-Based Isolation

**Threat**: User tries to access tenant routes from central domain
**Protection**: `tenant` middleware checks `tenancy()->initialized`
**Result**: 404 error if accessed from central domain

**Threat**: User tries to access central routes from tenant subdomain
**Protection**: `central.only` middleware checks domain against whitelist
**Result**: 404 error if accessed from tenant subdomain

#### 2. Guard-Based Isolation

**Threat**: Central admin tries to access tenant portal without proper authentication
**Protection**: Separate guard (`central` vs `web`)
**Result**: Central admin session not recognized by tenant auth middleware

**Threat**: Tenant staff tries to access customer portal
**Protection**: Separate guard (`web` vs `customer`)
**Result**: Staff session not recognized by customer auth middleware

#### 3. Database-Level Isolation

**Threat**: Query accidentally runs on wrong database
**Protection**: Tenancy middleware switches database connection early
**Result**: All queries automatically scoped to tenant database

**Example**:
```php
// On acme.midastech.in/customers
Customer::all(); // Queries acme_db.customers (automatic)

// On central domain /midas-admin/tenants
Tenant::all(); // Queries central.tenants (automatic)
```

#### 4. Subscription-Based Access Control

**Threat**: Suspended tenant continues using system
**Protection**: `subscription.status` middleware on all tenant routes
**Result**: Suspended tenants see suspension notice, cannot access features

**Allowed Statuses**:
- `trial` - Full access during trial period
- `active` - Full access with active subscription
- `grace_period` - Limited access after payment failure

**Blocked Statuses**:
- `suspended` - Blocks all access, shows suspension notice
- `cancelled` - Blocks all access, shows cancellation notice
- `expired` - Blocks all access, shows renewal prompt

#### 5. CSRF Protection

All POST/PUT/DELETE requests require CSRF token:
```html
<form method="POST" action="/customers/store">
    @csrf
    <!-- form fields -->
</form>
```

**Verified By**: `VerifyCsrfToken` middleware (in `web` middleware group)

---

## Request Flow Diagrams

### Public Website Request Flow

```
┌─────────────────────────────────────────────────┐
│ User visits: midastech.in/pricing              │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ DNS Resolution: midastech.in → Server IP       │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ Laravel receives request                        │
│ Host: midastech.in                              │
│ Path: /pricing                                  │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ Route Registration Check:                       │
│ • routes/central.php - NO (prefix /midas-admin)│
│ • routes/public.php - YES (domain matched)      │
│ • routes/web.php - NO (domain not matched)      │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ Middleware Execution:                           │
│ 1. web → Session, CSRF, cookies                 │
│ 2. central.only → Check domain whitelist        │
│    ✓ midastech.in in central_domains            │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ Controller: PublicController@pricing            │
│ Database: central (read-only)                   │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ View: resources/views/public/pricing.blade.php  │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ Response: 200 OK with HTML                      │
└─────────────────────────────────────────────────┘
```

### Tenant Staff Request Flow

```
┌─────────────────────────────────────────────────┐
│ User visits: acme.midastech.in/customers       │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ DNS Resolution: *.midastech.in → Server IP      │
│ Subdomain extracted: "acme"                     │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ GLOBAL Middleware (BEFORE ROUTING):             │
│ InitializeTenancyByDomainEarly                  │
│ • Extract subdomain: "acme"                     │
│ • Query: SELECT * FROM central.tenants          │
│         WHERE subdomain = 'acme'                │
│ • Found: tenant_id = 5, database = acme_db      │
│ • Switch: DB::setDefaultConnection('acme_db')   │
│ • Set: tenancy()->initialize($tenant)           │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ Route Registration Check:                       │
│ • routes/central.php - NO                       │
│ • routes/public.php - NO (domain not matched)   │
│ • routes/web.php - YES (tenant middleware)      │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ Middleware Execution:                           │
│ 1. web → Session, CSRF, cookies                 │
│ 2. tenant → Check tenancy()->initialized ✓      │
│ 3. subscription.status → Check tenant status ✓  │
│ 4. auth → Check Auth::guard('web')->check() ✓   │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ Controller: CustomerController@index             │
│ Database: acme_db (automatically scoped)        │
│ Query: SELECT * FROM acme_db.customers          │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ View: resources/views/customers/index.blade.php │
└─────────────────────────────────────────────────┘
                     ↓
┌─────────────────────────────────────────────────┐
│ Response: 200 OK with HTML (tenant-scoped data) │
└─────────────────────────────────────────────────┘
```

---

## Troubleshooting Common Issues

### Issue 1: 404 Error on Central Domain

**Symptom**: Accessing `midastech.in/customers` returns 404

**Cause**: Tenant routes (`/customers`) only registered for tenant subdomains

**Solution**: This is expected behavior. Staff routes only accessible on tenant subdomains.

**Correct URL**: `acme.midastech.in/customers`

---

### Issue 2: 404 Error on Tenant Subdomain

**Symptom**: Accessing `acme.midastech.in/features` returns 404

**Cause**: Public website routes only registered for central domains

**Solution**: This is expected behavior. Public routes only accessible on central domain.

**Correct URL**: `midastech.in/features`

---

### Issue 3: Infinite Redirect Loop

**Symptom**: Browser shows "Too many redirects" error

**Possible Causes**:
1. Middleware order incorrect (auth before tenant initialization)
2. Guest middleware checking wrong guard
3. Session not persisting (cookie issues)

**Debugging**:
```php
// Add to middleware to debug
\Log::info('Middleware Check', [
    'middleware' => 'auth',
    'guard' => 'web',
    'authenticated' => Auth::guard('web')->check(),
    'user_id' => Auth::guard('web')->id(),
]);
```

**Common Fix**: Verify middleware order in `RouteServiceProvider.php`

---

### Issue 4: Wrong Database Queried

**Symptom**: Tenant queries returning data from wrong tenant or central database

**Cause**: Tenancy middleware not executing or running in wrong order

**Debug**:
```php
// Add to controller
dd([
    'default_connection' => config('database.default'),
    'current_database' => DB::connection()->getDatabaseName(),
    'tenant_initialized' => tenancy()->initialized,
    'tenant_id' => optional(tenancy()->tenant)->id,
]);
```

**Fix**: Ensure `InitializeTenancyByDomainEarly` is registered as global middleware

---

### Issue 5: CSRF Token Mismatch

**Symptom**: POST requests fail with "419 Page Expired"

**Cause**: CSRF token not included or session lost

**Solutions**:
1. Include `@csrf` in all forms
2. Add CSRF token to AJAX requests:
```javascript
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

3. Check session driver configuration (file/redis)

---

### Issue 6: Subscription Check Blocking Access

**Symptom**: Active tenant cannot access portal

**Debug**:
```php
// In CheckSubscriptionStatus middleware
\Log::info('Subscription Check', [
    'tenant_id' => $tenant->id,
    'has_subscription' => $tenant->subscription !== null,
    'status' => optional($tenant->subscription)->status,
    'trial_ends_at' => optional($tenant->subscription)->trial_ends_at,
    'is_trial_active' => optional($tenant->subscription)->isOnTrial(),
]);
```

**Fix**: Verify subscription record exists and has valid status

---

### Issue 7: Customer Cannot Login to Portal

**Symptom**: Customer credentials correct but login fails

**Possible Causes**:
1. Email not verified (`email_verified_at` is NULL)
2. Customer status inactive
3. Wrong guard being checked

**Debug**:
```php
// In CustomerAuthController
$customer = Customer::where('email', $request->email)->first();
dd([
    'customer_found' => $customer !== null,
    'email_verified' => $customer->email_verified_at !== null,
    'status' => $customer->status,
    'password_match' => Hash::check($request->password, $customer->password),
]);
```

---

### Issue 8: Session Timeout Too Aggressive

**Symptom**: Customer logged out after 5 minutes

**Cause**: `customer.timeout` middleware enforcing 30-minute timeout incorrectly

**Fix**: Check last activity timestamp update:
```php
// Should update on every request
session(['last_activity' => now()->timestamp]);
```

---

## Best Practices

### 1. Always Use Named Routes

❌ **Bad**: `return redirect('/customers');`
✅ **Good**: `return redirect()->route('customers.index');`

**Why**: Named routes prevent hardcoded URLs and work correctly across portals

### 2. Use Guard-Specific Auth Checks

❌ **Bad**: `Auth::check()`
✅ **Good**: `Auth::guard('web')->check()` or `Auth::guard('customer')->check()`

**Why**: Default guard may not be the one you expect

### 3. Always Specify Guard in Controllers

```php
// Tenant Staff Controller
public function __construct()
{
    $this->middleware('auth:web');
}

// Customer Portal Controller
public function __construct()
{
    $this->middleware('auth:customer');
}

// Central Admin Controller
public function __construct()
{
    $this->middleware('auth:central');
}
```

### 4. Test Cross-Portal Isolation

```php
// Test that tenant routes blocked from central domain
$this->get('http://midastech.in/customers')
    ->assertStatus(404);

// Test that public routes blocked from tenant domain
$this->get('http://acme.midastech.in/features')
    ->assertStatus(404);
```

### 5. Use Domain-Aware Redirects

```php
// In Authenticate middleware
protected function redirectTo($request)
{
    // Check domain type FIRST
    if ($this->isCentralDomain($request)) {
        return route('central.login');
    }

    // Then check path
    if ($request->is('customer/*')) {
        return route('customer.login');
    }

    return route('login'); // Tenant staff
}
```

---

## Summary

The Midas Portal's **4-portal architecture** provides:

✅ **Complete Isolation**: No cross-portal data leaks or authentication bypasses
✅ **Domain-Based Security**: Middleware enforces strict boundaries
✅ **Clear Separation**: Each portal has distinct purpose and access patterns
✅ **Scalable Design**: Supports 100+ tenants with consistent architecture
✅ **Maintainable Codebase**: Route organization prevents conflicts

**Key Architectural Decisions**:
1. **Domain-Level Route Registration**: Prevents middleware execution on wrong domains
2. **Guard-Based Authentication**: Complete session isolation between portals
3. **Global Tenancy Initialization**: Tenant context set before routing
4. **Subscription-Based Access**: Automatic enforcement across all tenant routes
5. **Strict Loading Order**: Prevents route conflicts and ensures predictable behavior

---

**Document Version**: 1.0
**Last Updated**: 2025-11-06
**Related Documentation**: [ARCHITECTURE.md](../ARCHITECTURE.md), [MIDDLEWARE_REFERENCE.md](MIDDLEWARE_REFERENCE.md)
