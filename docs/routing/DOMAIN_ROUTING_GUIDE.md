# Domain Routing Guide - Multi-Tenant System

Complete guide to domain-based routing rules and URL patterns for the Midas Portal multi-tenant application.

## Table of Contents
1. [Domain Structure](#domain-structure)
2. [Domain Access Rules](#domain-access-rules)
3. [URL Pattern Matrix](#url-pattern-matrix)
4. [Middleware Protection](#middleware-protection)
5. [Common Scenarios](#common-scenarios)
6. [Troubleshooting](#troubleshooting)

---

## Domain Structure

### Production Domains

| Domain Type | URL Pattern | Purpose |
|-------------|-------------|---------|
| **Central Domain** | midastech.in | Platform website & admin |
| **Tenant Subdomain** | {tenant}.midastech.in | Tenant business portal |

### Development/Testing Domains

| Domain Type | URL Pattern | Purpose |
|-------------|-------------|---------|
| **Central Domain** | midastech.testing.in:8085 | Platform website & admin |
| **Tenant Subdomain** | {tenant}.midastech.testing.in:8085 | Tenant business portal |

### Configuration

Central domains are configured in `config/tenancy.php`:

```php
'central_domains' => [
    'midastech.in',
    'midastech.testing.in',
    'midastech.testing.in:8085',  // Development with port
],
```

---

## Domain Access Rules

### Rule 1: Central Domain Access

**Accessible Routes**:
- ✅ `/` - Public website homepage
- ✅ `/features`, `/pricing`, `/about`, `/contact` - Marketing pages
- ✅ `/midas-admin/*` - Central admin panel
- ❌ `/home`, `/customers`, `/members` - Tenant staff routes (404)
- ❌ `/customer/*` - Customer portal routes (404)

**How It Works**:
1. Public website routes have `central.only` middleware → blocks tenant subdomains
2. Tenant routes have `tenant` middleware → blocks central domains
3. Central admin routes are always accessible on central domain

**Example URLs**:
```
✅ https://midastech.testing.in:8085/
✅ https://midastech.testing.in:8085/features
✅ https://midastech.testing.in:8085/midas-admin/login
✅ https://midastech.testing.in:8085/midas-admin/tenants
❌ https://midastech.testing.in:8085/home (404)
❌ https://midastech.testing.in:8085/customer/login (404)
```

---

### Rule 2: Tenant Subdomain Access

**Accessible Routes**:
- ✅ `/` - Redirects to staff login or dashboard
- ✅ `/login`, `/home` - Staff portal
- ✅ `/customers`, `/members`, `/services`, etc. - Business routes
- ✅ `/customer/*` - Customer portal
- ❌ `/features`, `/pricing` - Public website (404)
- ❌ `/midas-admin/*` - Central admin (404)

**How It Works**:
1. `universal` middleware identifies tenant from subdomain
2. Tenant database context initialized
3. `tenant` middleware blocks access from central domains
4. `central.only` middleware blocks public routes

**Example URLs**:
```
✅ https://acme.midastech.testing.in:8085/
✅ https://acme.midastech.testing.in:8085/login
✅ https://acme.midastech.testing.in:8085/home
✅ https://acme.midastech.testing.in:8085/customers
✅ https://acme.midastech.testing.in:8085/customer/login
❌ https://acme.midastech.testing.in:8085/features (404)
❌ https://acme.midastech.testing.in:8085/midas-admin/login (404)
```

---

## URL Pattern Matrix

### Central Domain (midastech.testing.in:8085)

| URL | Portal | Route Name | Auth Required | Redirect If Unauthenticated |
|-----|--------|-----------|---------------|------------------------------|
| / | Public | public.home | No | N/A |
| /features | Public | public.features | No | N/A |
| /pricing | Public | public.pricing | No | N/A |
| /about | Public | public.about | No | N/A |
| /contact | Public | public.contact | No | N/A |
| /login | Public | public.login.redirect | No | Redirects to /midas-admin/login |
| /midas-admin/login | Central Admin | central.login | No | N/A |
| /midas-admin/dashboard | Central Admin | central.dashboard | Yes (central) | /midas-admin/login |
| /midas-admin/tenants | Central Admin | central.tenants.index | Yes (central) | /midas-admin/login |
| /home | N/A | 404 | N/A | N/A |
| /customer/login | N/A | 404 | N/A | N/A |

### Tenant Subdomain ({tenant}.midastech.testing.in:8085)

| URL | Portal | Route Name | Auth Required | Redirect If Unauthenticated |
|-----|--------|-----------|---------------|------------------------------|
| / | Staff | tenant.root | No | Redirects to /login or /home |
| /login | Staff | login | No | N/A |
| /home | Staff | home | Yes (web) | /login |
| /customers | Staff | customers.index | Yes (web) | /login |
| /members | Staff | members.index | Yes (web) | /login |
| /services | Staff | services.index | Yes (web) | /login |
| /billing | Staff | billing.index | Yes (web) | /login |
| /customer/login | Customer | customer.login | No | N/A |
| /customer/dashboard | Customer | customer.dashboard | Yes (customer) | /customer/login |
| /customer/profile | Customer | customer.profile | Yes (customer) | /customer/login |
| /customer/memberships | Customer | customer.memberships | Yes (customer) | /customer/login |
| /features | N/A | 404 | N/A | N/A |
| /midas-admin/login | N/A | 404 | N/A | N/A |

---

## Middleware Protection

### Middleware Stack by Domain

#### Central Domain - Public Routes
```php
Route::middleware(['web', 'central.only'])
    ->withoutMiddleware('universal')
    ->group(base_path('routes/public.php'));
```

**Middleware Execution Order**:
1. `web` → Session, CSRF, cookies
2. `central.only` → Block tenant subdomains
3. NO `universal` → No tenant identification

**Result**: Routes only accessible on central domain

---

#### Central Domain - Admin Routes
```php
Route::prefix('midas-admin')
    ->middleware('web')
    ->group(base_path('routes/central.php'));
```

**Middleware Execution Order**:
1. `web` → Session, CSRF, cookies
2. NO tenant middleware → Central database only

**Result**: Admin routes accessible on central domain, no tenant context

---

#### Tenant Subdomain - Staff Routes
```php
Route::middleware(['web', 'universal', 'tenant'])
    ->group(base_path('routes/web.php'));
```

**Middleware Execution Order**:
1. `web` → Session, CSRF, cookies
2. `universal` (InitializeTenancyByDomain) → Identify tenant from subdomain
3. `tenant` (PreventAccessFromCentralDomains) → Block central domain access

**Result**: Routes only accessible on tenant subdomains with tenant context

---

#### Tenant Subdomain - Customer Routes
```php
Route::middleware(['web', 'universal', 'tenant'])
    ->group(base_path('routes/customer.php'));
```

**Middleware Execution Order**:
1. `web` → Session, CSRF, cookies
2. `universal` → Identify tenant from subdomain
3. `tenant` → Block central domain access

**Result**: Same as staff routes - tenant subdomains only

---

## Common Scenarios

### Scenario 1: User Visits Root URL

#### On Central Domain (midastech.testing.in:8085/)
```
Request: GET https://midastech.testing.in:8085/
         ↓
Middleware: web → central.only
         ↓
Route: public.home (PublicController@home)
         ↓
Response: Marketing homepage
```

#### On Tenant Subdomain (acme.midastech.testing.in:8085/)
```
Request: GET https://acme.midastech.testing.in:8085/
         ↓
Middleware: web → universal → tenant
         ↓
Tenant identified: "acme"
Database switched: acme_db
         ↓
Route: tenant.root (Closure)
         ↓
Check auth: if (auth()->check()) redirect /home else redirect /login
         ↓
Response: Redirect to /login or /home
```

---

### Scenario 2: Unauthenticated User Accesses Protected Route

#### Staff Route (/customers)
```
Request: GET https://acme.midastech.testing.in:8085/customers
         ↓
Middleware: web → universal → tenant → auth
         ↓
Authenticate middleware: User not authenticated
         ↓
redirectTo() method:
  - Not on central domain
  - Not /customer/* route
  → return route('login')
         ↓
Response: Redirect to /login
```

#### Customer Route (/customer/dashboard)
```
Request: GET https://acme.midastech.testing.in:8085/customer/dashboard
         ↓
Middleware: web → universal → tenant → customer.auth
         ↓
Authenticate middleware: Customer not authenticated
         ↓
redirectTo() method:
  - Not on central domain
  - Is /customer/* route
  → return route('customer.login')
         ↓
Response: Redirect to /customer/login
```

#### Central Admin Route (/midas-admin/tenants)
```
Request: GET https://midastech.testing.in:8085/midas-admin/tenants
         ↓
Middleware: web → central.auth
         ↓
Authenticate middleware: Central admin not authenticated
         ↓
redirectTo() method:
  - Is on central domain
  → return route('central.login')
         ↓
Response: Redirect to /midas-admin/login
```

---

### Scenario 3: Authenticated User Visits Login Page

#### Staff User Visits /login
```
Request: GET https://acme.midastech.testing.in:8085/login
         ↓
Route has 'guest' middleware
         ↓
RedirectIfAuthenticated:
  Auth::guard('web')->check() = true
  Guard is 'web'
  → return redirect('/home')
         ↓
Response: Redirect to /home
```

#### Customer Visits /customer/login
```
Request: GET https://acme.midastech.testing.in:8085/customer/login
         ↓
Route has 'guest:customer' middleware
         ↓
RedirectIfAuthenticated:
  Auth::guard('customer')->check() = true
  Guard is 'customer'
  → return redirect()->route('customer.dashboard')
         ↓
Response: Redirect to /customer/dashboard
```

#### Central Admin Visits /midas-admin/login
```
Request: GET https://midastech.testing.in:8085/midas-admin/login
         ↓
Route has 'guest:central' middleware
         ↓
RedirectIfAuthenticated:
  Auth::guard('central')->check() = true
  Guard is 'central'
  → return redirect()->route('central.dashboard')
         ↓
Response: Redirect to /midas-admin/dashboard
```

---

## Troubleshooting

### Problem: 404 on Tenant Routes from Central Domain

**Symptom**:
```
Request: https://midastech.testing.in:8085/home
Response: 404 Not Found
```

**Cause**: The `tenant` middleware (PreventAccessFromCentralDomains) blocks tenant routes on central domain.

**Solution**: This is correct behavior. Tenant routes should only work on tenant subdomains.
```
Correct URL: https://acme.midastech.testing.in:8085/home
```

---

### Problem: 404 on Public Routes from Tenant Subdomain

**Symptom**:
```
Request: https://acme.midastech.testing.in:8085/features
Response: 404 Not Found
```

**Cause**: The `central.only` middleware blocks public routes on tenant subdomains.

**Solution**: This is correct behavior. Public routes should only work on central domain.
```
Correct URL: https://midastech.testing.in:8085/features
```

---

### Problem: Tenant Not Identified

**Symptom**:
```
Request: https://acme.midastech.testing.in:8085/home
Error: Tenant could not be identified on domain acme.midastech.testing.in:8085
```

**Cause**: Tenant "acme" doesn't exist in the `tenants` table in central database.

**Solution**: Create tenant via central admin panel:
```
1. Login to https://midastech.testing.in:8085/midas-admin/login
2. Navigate to Tenants management
3. Create tenant with subdomain "acme"
```

---

### Problem: Wrong Login Redirect

**Symptom**:
```
Request: https://acme.midastech.testing.in:8085/customer/dashboard (unauthenticated)
Response: Redirected to /login instead of /customer/login
```

**Cause**: Middleware or route configuration issue.

**Solution**: Check that:
1. Route uses `customer.auth` middleware (not `auth`)
2. `Authenticate.php` middleware has correct `is('customer/*')` check
3. Clear route cache: `php artisan route:clear`

---

### Problem: Can Access Central Admin from Tenant Subdomain

**Symptom**:
```
Request: https://acme.midastech.testing.in:8085/midas-admin/login
Response: 200 OK (Should be 404)
```

**Cause**: Central admin routes missing domain protection.

**Solution**: Check `RouteServiceProvider.php` - central routes should NOT have `tenant` middleware (they don't by design), but they should only respond to central domains.

Central admin routes are accessible from any domain by design (no middleware blocks them). To restrict, add domain check in central routes:

```php
Route::domain(config('tenancy.central_domains')[0])
    ->prefix('midas-admin')
    ->middleware('web')
    ->group(base_path('routes/central.php'));
```

**Note**: Current implementation allows /midas-admin/* on any domain but only central admins can login.

---

### Problem: Cache Issues

**Symptom**: Changes to routes not reflecting.

**Solution**: Clear all caches:
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## Quick Reference

### Domain → Portal Mapping

| Domain | / | /login | /home | /customer/login | /midas-admin/login |
|--------|---|--------|-------|----------------|-------------------|
| midastech.testing.in:8085 | Public homepage | Redirect to central | 404 | 404 | Central admin |
| acme.midastech.testing.in:8085 | Redirect to staff | Staff login | Staff dashboard | Customer login | 404 |

### Guard → Dashboard Mapping

| Guard | Login Route | Dashboard Route | Domain |
|-------|------------|----------------|---------|
| `central` | /midas-admin/login | /midas-admin/dashboard | Central |
| `web` | /login | /home | Tenant |
| `customer` | /customer/login | /customer/dashboard | Tenant |

### Middleware → Domain Mapping

| Middleware | Allows Central | Allows Tenant | Purpose |
|------------|---------------|---------------|---------|
| `web` | ✅ | ✅ | Session, CSRF |
| `central.only` | ✅ | ❌ | Block tenant access |
| `universal` | N/A | ✅ | Identify tenant |
| `tenant` | ❌ | ✅ | Block central access |

---

## Summary

**Key Principles**:

1. **Central domain** = Public website + Central admin (NO tenant context)
2. **Tenant subdomain** = Staff portal + Customer portal (WITH tenant context)
3. **Middleware enforces boundaries** - no manual domain checks in controllers
4. **Three separate authentication contexts** - no guard mixing
5. **Route loading order prevents conflicts** - first match wins

This domain-based routing ensures:
- ✅ Complete tenant isolation
- ✅ No accidental cross-tenant data access
- ✅ Clear separation between platform admin and tenant operations
- ✅ Consistent URL patterns across all tenants
