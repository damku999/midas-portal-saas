# Routing Architecture - Multi-Tenant System

Complete routing map for the multi-tenant Midas Portal application with database-per-tenant architecture.

## Table of Contents
1. [System Overview](#system-overview)
2. [Three-Portal Architecture](#three-portal-architecture)
3. [Route Loading Order](#route-loading-order)
4. [Authentication & Guards](#authentication--guards)
5. [Complete Route Map](#complete-route-map)
6. [Middleware Stack](#middleware-stack)

---

## System Overview

The Midas Portal uses a **multi-tenant architecture** with three distinct portals:

| Portal | Domain Access | Guard | Purpose |
|--------|--------------|-------|---------|
| **Central Admin** | midastech.testing.in:8085/midas-admin | `central` | Platform administration |
| **Tenant Staff Portal** | {tenant}.midastech.testing.in:8085 | `web` | Business operations |
| **Customer Portal** | {tenant}.midastech.testing.in:8085/customer | `customer` | Customer self-service |

### Key Architectural Principles

1. **Domain-Based Tenant Identification**: Tenant is identified by subdomain (e.g., `acme.midastech.testing.in`)
2. **Database-Per-Tenant**: Each tenant has isolated database using Stancl Tenancy package
3. **Guard-Based Authentication**: Three separate authentication contexts prevent cross-portal access
4. **Middleware-Protected Boundaries**: Enforce domain access rules at middleware level

---

## Three-Portal Architecture

### 1. Central Admin Portal

**Purpose**: Platform management - create/manage tenants, subscriptions, global settings

**Domain Access**:
- ✅ midastech.testing.in:8085
- ✅ midastech.in (production)
- ❌ {tenant}.midastech.testing.in (blocked by design)

**URL Pattern**: `/midas-admin/*`

**Key Routes**:
```
GET  /midas-admin/login          → Central admin login
POST /midas-admin/login          → Process central login
GET  /midas-admin/dashboard      → Central admin dashboard
GET  /midas-admin/tenants        → Manage all tenants
GET  /midas-admin/subscriptions  → Manage subscriptions
```

**Authentication**:
- Guard: `central`
- Middleware: `central.auth`
- User Model: `App\Models\Central\CentralAdmin`
- Database: Central database (not tenant-specific)

**Middleware Stack**:
```
web → NO tenant middleware (operates on central database only)
```

---

### 2. Tenant Staff Portal

**Purpose**: Business operations - manage members, services, billing, appointments, etc.

**Domain Access**:
- ❌ midastech.testing.in:8085 (blocked by 'tenant' middleware)
- ✅ {tenant}.midastech.testing.in:8085
- Example: acme.midastech.testing.in:8085

**URL Pattern**: `/*` (root level, except /customer/* and /midas-admin/*)

**Key Routes**:
```
GET  /                      → Redirect to /home or /login
GET  /login                 → Staff login page
POST /login                 → Process staff login
GET  /home                  → Staff dashboard
GET  /customers             → Customer management
GET  /members               → Member management
GET  /services              → Service management
GET  /billing               → Billing & invoicing
GET  /appointments          → Appointment scheduling
GET  /communication         → WhatsApp/SMS/Email
GET  /reports               → Business reports
```

**Authentication**:
- Guard: `web` (default Laravel guard)
- Middleware: `auth` (uses web guard)
- User Model: `App\Models\User`
- Database: Tenant-specific database

**Middleware Stack**:
```
web → universal (InitializeTenancyByDomain) → tenant (PreventAccessFromCentralDomains)
```

---

### 3. Customer Portal

**Purpose**: Customer self-service - view memberships, make payments, book appointments

**Domain Access**:
- ❌ midastech.testing.in:8085 (blocked by 'tenant' middleware)
- ✅ {tenant}.midastech.testing.in:8085/customer/*
- Example: acme.midastech.testing.in:8085/customer/login

**URL Pattern**: `/customer/*`

**Key Routes**:
```
GET  /customer/login             → Customer login page
POST /customer/login             → Process customer login
GET  /customer/dashboard         → Customer dashboard
GET  /customer/profile           → Customer profile
GET  /customer/memberships       → View memberships
GET  /customer/appointments      → View/book appointments
GET  /customer/payments          → Payment history
GET  /customer/invoices          → View invoices
GET  /customer/family            → Family member management
```

**Authentication**:
- Guard: `customer`
- Middleware: `customer.auth`, `customer.timeout`, `customer.secure`
- User Model: `App\Models\Customer`
- Database: Tenant-specific database

**Middleware Stack**:
```
web → universal (InitializeTenancyByDomain) → tenant (PreventAccessFromCentralDomains)
```

---

## Route Loading Order

Routes are loaded in **specific order** to prevent conflicts. This order is defined in `app/Providers/RouteServiceProvider.php`:

### Order of Precedence (First Match Wins)

```
1. CENTRAL ADMIN ROUTES (/midas-admin/*)
   ↓
2. PUBLIC WEBSITE ROUTES (/, /features, /pricing - central domain only)
   ↓
3. TENANT STAFF PORTAL ROUTES (/* - tenant subdomains only)
   ↓
4. CUSTOMER PORTAL ROUTES (/customer/* - tenant subdomains only)
```

### Why This Order Matters

**Example**: The `/` route

- On central domain (`midastech.testing.in`): Matches **Public Website** (Step 2) → Shows marketing homepage
- On tenant subdomain (`acme.midastech.testing.in`): Public routes blocked by `central.only` middleware, matches **Tenant Staff Portal** (Step 3) → Redirects to staff login/dashboard

**Example**: The `/login` route

- On central domain: Matches **Public Website** (Step 2) → Redirects to `/midas-admin/login`
- On tenant subdomain: Matches **Tenant Staff Portal** (Step 3) → Shows tenant staff login

---

## Authentication & Guards

### Guard Configuration

Defined in `config/auth.php`:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',  // App\Models\User
    ],
    'central' => [
        'driver' => 'session',
        'provider' => 'central_admins',  // App\Models\Central\CentralAdmin
    ],
    'customer' => [
        'driver' => 'session',
        'provider' => 'customers',  // App\Models\Customer
    ],
],
```

### Authentication Flow by Portal

#### Central Admin Login Flow
```
1. User visits midastech.testing.in/midas-admin/login
2. Submits credentials
3. CentralAuthController validates against central database
4. Auth::guard('central')->attempt()
5. Redirect to route('central.dashboard') → /midas-admin/dashboard
```

#### Tenant Staff Login Flow
```
1. User visits acme.midastech.testing.in/login
2. 'universal' middleware identifies tenant from subdomain
3. Tenant context initialized (database switched to acme_db)
4. Submits credentials
5. Auth::guard('web')->attempt() validates against tenant database
6. Redirect to route('home') → /home
```

#### Customer Login Flow
```
1. User visits acme.midastech.testing.in/customer/login
2. 'universal' middleware identifies tenant
3. Tenant context initialized
4. Submits credentials
5. Auth::guard('customer')->attempt() validates against tenant database
6. Redirect to route('customer.dashboard') → /customer/dashboard
```

### Unauthenticated Redirect Logic

Implemented in `app/Http/Middleware/Authenticate.php`:

```php
protected function redirectTo($request)
{
    // Check if on central domain
    if ($isCentralDomain) {
        return route('central.login');  // → /midas-admin/login
    }

    // On tenant subdomain - check route path
    if ($request->is('customer/*')) {
        return route('customer.login');  // → /customer/login
    }

    return route('login');  // → /login (staff portal)
}
```

### Already Authenticated Redirect Logic

Implemented in `app/Http/Middleware/RedirectIfAuthenticated.php`:

```php
if (Auth::guard($guard)->check()) {
    if ($guard === 'central') {
        return redirect()->route('central.dashboard');  // → /midas-admin/dashboard
    }
    if ($guard === 'customer') {
        return redirect()->route('customer.dashboard');  // → /customer/dashboard
    }
    return redirect('/home');  // Staff portal
}
```

---

## Complete Route Map

### Public Website Routes (routes/public.php)
**Domain**: Central only (midastech.testing.in)
**Middleware**: `web`, `central.only`

| Method | Path | Name | Controller | Purpose |
|--------|------|------|------------|---------|
| GET | / | public.home | PublicController@home | Marketing homepage |
| GET | /features | public.features | PublicController@features | Feature showcase |
| GET | /pricing | public.pricing | PublicController@pricing | Pricing plans |
| GET | /about | public.about | PublicController@about | About company |
| GET | /contact | public.contact | PublicController@contact | Contact form |
| POST | /contact | public.contact.submit | PublicController@submitContact | Submit contact |
| GET | /login | public.login.redirect | Closure | Redirect to central.login |

---

### Central Admin Routes (routes/central.php)
**Domain**: Central only (midastech.testing.in/midas-admin)
**Middleware**: `web`, `central.auth`

| Method | Path | Name | Purpose |
|--------|------|------|---------|
| GET | /midas-admin/login | central.login | Central admin login page |
| POST | /midas-admin/login | - | Process central login |
| POST | /midas-admin/logout | central.logout | Central logout |
| GET | /midas-admin/dashboard | central.dashboard | Central dashboard |
| Resource | /midas-admin/tenants | central.tenants.* | Tenant CRUD |
| Resource | /midas-admin/subscriptions | central.subscriptions.* | Subscription management |

---

### Tenant Staff Portal Routes (routes/web.php)
**Domain**: Tenant subdomains only
**Middleware**: `web`, `universal`, `tenant`, `auth`

| Method | Path | Name | Purpose |
|--------|------|------|---------|
| GET | / | tenant.root | Redirect to /home or /login |
| GET | /login | login | Staff login page |
| POST | /login | - | Process staff login |
| POST | /logout | logout | Staff logout |
| GET | /home | home | Staff dashboard |
| Resource | /customers | customers.* | Customer management |
| Resource | /members | members.* | Member management |
| Resource | /services | services.* | Service catalog |
| Resource | /packages | packages.* | Package management |
| Resource | /billing | billing.* | Invoicing & billing |
| Resource | /appointments | appointments.* | Appointment scheduling |
| GET | /communication | communication.index | Communication hub |
| GET | /reports | reports.index | Business reports |

---

### Customer Portal Routes (routes/customer.php)
**Domain**: Tenant subdomains (/customer/*)
**Middleware**: `web`, `universal`, `tenant`, `customer.auth`

| Method | Path | Name | Purpose |
|--------|------|------|---------|
| GET | /customer/login | customer.login | Customer login |
| POST | /customer/login | - | Process customer login |
| POST | /customer/logout | customer.logout | Customer logout |
| GET | /customer/dashboard | customer.dashboard | Customer dashboard |
| GET | /customer/profile | customer.profile | Customer profile |
| GET | /customer/memberships | customer.memberships | View memberships |
| GET | /customer/appointments | customer.appointments | View/book appointments |
| GET | /customer/payments | customer.payments | Payment history |
| GET | /customer/invoices | customer.invoices | View invoices |
| GET | /customer/family | customer.family | Family members |

---

## Middleware Stack

### Available Middleware

| Middleware | Alias | Purpose | Usage |
|------------|-------|---------|-------|
| InitializeTenancyByDomain | `universal` | Identify tenant from subdomain | Tenant portals |
| PreventAccessFromCentralDomains | `tenant` | Block central domain access | Tenant portals |
| PreventAccessFromTenantDomains | `central.only` | Block tenant subdomain access | Central/public routes |
| CentralAuth | `central.auth` | Central admin authentication | Central routes |
| CustomerAuth | `customer.auth` | Customer authentication | Customer routes |
| CustomerSessionTimeout | `customer.timeout` | Customer session expiry | Customer routes |
| SecureSession | `customer.secure` | Customer session security | Customer routes |

### Middleware Application by Portal

#### Central Admin Portal
```php
Route::prefix('midas-admin')
    ->middleware('web')  // Session, CSRF, cookies
    ->group(base_path('routes/central.php'));
```

#### Public Website
```php
Route::middleware(['web', 'central.only'])  // Block tenant subdomains
    ->withoutMiddleware('universal')  // NO tenant identification
    ->group(base_path('routes/public.php'));
```

#### Tenant Staff Portal
```php
Route::middleware(['web', 'universal', 'tenant'])
    ->group(base_path('routes/web.php'));
// 'universal' → Identify tenant from subdomain
// 'tenant' → Block access from central domains
```

#### Customer Portal
```php
Route::middleware(['web', 'universal', 'tenant'])
    ->group(base_path('routes/customer.php'));
// Same middleware stack as staff portal
```

---

## Domain Examples

### Central Domain (midastech.testing.in:8085)

| URL | Route Matched | Result |
|-----|--------------|--------|
| / | public.home | Marketing homepage |
| /login | public.login.redirect | Redirect to /midas-admin/login |
| /features | public.features | Features page |
| /midas-admin/login | central.login | Central admin login |
| /midas-admin/dashboard | central.dashboard | Central dashboard (auth required) |
| /home | N/A | 404 - tenant routes blocked |
| /customer/login | N/A | 404 - tenant routes blocked |

### Tenant Subdomain (acme.midastech.testing.in:8085)

| URL | Route Matched | Result |
|-----|--------------|--------|
| / | tenant.root | Redirect to /home or /login |
| /login | login | Tenant staff login |
| /home | home | Staff dashboard (auth required) |
| /customers | customers.index | Customer list (auth required) |
| /customer/login | customer.login | Customer portal login |
| /customer/dashboard | customer.dashboard | Customer dashboard (auth required) |
| /midas-admin/login | N/A | 404 - central routes blocked |
| /features | N/A | 404 - public routes blocked |

---

## Summary

The routing architecture enforces **strict domain-based separation**:

1. **Central Admin**: Operates on central domain, manages platform
2. **Tenant Staff Portal**: Operates on tenant subdomains, manages business
3. **Customer Portal**: Operates on tenant subdomains under /customer/*, customer self-service

Each portal has:
- **Isolated authentication** (separate guards)
- **Domain-based access control** (middleware enforcement)
- **Proper redirect logic** (domain-aware redirects)
- **Database isolation** (central vs tenant databases)

This architecture ensures:
- No cross-portal authentication leaks
- No accidental tenant data access
- Clear separation of concerns
- Scalable multi-tenant system
