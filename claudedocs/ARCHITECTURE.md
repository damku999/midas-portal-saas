# Midas Portal - Multi-Tenant Architecture

**Project**: Midas Insurance Portal - Multi-Tenant SaaS Platform
**Laravel Version**: 10.49.1
**PHP Version**: 8.2.12
**Multi-Tenancy Package**: stancl/tenancy ^3.8

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Multi-Tenancy Architecture](#multi-tenancy-architecture)
3. [Database Architecture](#database-architecture)
4. [Domain-Based Routing](#domain-based-routing)
5. [Middleware Stack](#middleware-stack)
6. [Authentication System](#authentication-system)
7. [Portal Structure](#portal-structure)
8. [Infrastructure Requirements](#infrastructure-requirements)

---

## System Overview

The Midas Portal is a **multi-tenant SaaS platform** using database-per-tenant architecture with subdomain-based routing. Each tenant operates in complete isolation with their own database, users, and data.

### Core Architecture Principles

1. **Database-Per-Tenant**: Complete data isolation with separate databases
2. **Subdomain-Based Routing**: Tenant identification via subdomain (e.g., `acme.midastech.in`)
3. **Four-Portal System**: Public website, central admin, tenant staff portal, customer portal
4. **Guard-Based Authentication**: Separate authentication contexts prevent cross-portal access
5. **Domain Access Control**: Middleware enforces strict domain-based boundaries

### Four-Portal Architecture

| Portal | Domain Access | Guard | Purpose |
|--------|--------------|-------|---------|
| **Public Website** | midastech.in | none | Marketing & lead generation |
| **Central Admin** | midastech.in/midas-admin | `central` | Platform administration |
| **Tenant Staff Portal** | {tenant}.midastech.in | `web` | Business operations |
| **Customer Portal** | {tenant}.midastech.in/customer | `customer` | Customer self-service |

---

## Multi-Tenancy Architecture

### Tenant Structure

```
Central Domain (midastech.in)
├── Public Website (/, /pricing, /features, /contact)
├── Central Admin Panel (/midas-admin/*)
├── Central Database (tenant metadata, billing, subscriptions)
└── Platform Management

Tenant Domains (*.midastech.in)
├── Tenant Database 1 (tenant_1) → tenant1.midastech.in
├── Tenant Database 2 (tenant_2) → tenant2.midastech.in
└── Tenant Database N (tenant_N) → tenantN.midastech.in
    ├── Users (isolated)
    ├── Customers (isolated)
    ├── Leads (isolated)
    ├── Claims (isolated)
    └── All operational data (isolated)
```

### Key Features

**Complete Data Isolation**
- Separate database per tenant
- No shared data between tenants
- Independent file storage per tenant
- Tenant-scoped sessions and cache

**Automated Tenant Provisioning**
1. Create tenant record in central database
2. Generate unique subdomain and database name
3. Create MySQL database for tenant
4. Run all tenant migrations
5. Seed default data (roles, statuses, settings)
6. Generate tenant admin credentials
7. Send welcome email
8. Duration: < 2 minutes per tenant

**Billing & Subscription System**

Pricing Tiers:
- **Starter**: 5 users, 1,000 customers, 500 leads/month
- **Professional**: 20 users, 10,000 customers, 5,000 leads/month
- **Enterprise**: Unlimited users, unlimited data, custom features

Features:
- Trial periods (14/30 days)
- Usage limit enforcement
- Auto-suspend on payment failure
- Payment gateway integration (Stripe/Razorpay)
- Invoice generation

---

## Database Architecture

### Central Database (`central`)

**Purpose**: Manage tenant metadata and billing

**Tables**:
- `tenants` - Tenant information (id, name, subdomain, database_name, plan_id, status)
- `plans` - Pricing tiers (Starter, Professional, Enterprise)
- `subscriptions` - Billing records per tenant
- `central_admins` - Super admin accounts (cross-tenant access)
- `migrations` - Central migration tracking

### Tenant Databases (`tenant_{id}`)

**Purpose**: Isolated operational data per tenant

**Tables** (60+ tables):
- All current application tables
- Complete data isolation
- Independent user management
- Separate roles and permissions
- Tenant-specific settings

### Database Configuration

```php
// config/database.php
'central' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => 'central',
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
],

'tenant' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => null,  // Set dynamically per tenant
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
],
```

---

## Domain-Based Routing

### Domain Structure

#### Production Domains

| Domain Type | URL Pattern | Purpose |
|-------------|-------------|---------|
| **Central Domain** | midastech.in | Platform website & admin |
| **Tenant Subdomain** | {tenant}.midastech.in | Tenant business portal |

#### Development/Testing Domains

| Domain Type | URL Pattern | Purpose |
|-------------|-------------|---------|
| **Central Domain** | midastech.testing.in:8085 | Platform website & admin |
| **Tenant Subdomain** | {tenant}.midastech.testing.in:8085 | Tenant business portal |

### Route Loading Order

Routes are loaded in specific order to prevent conflicts (defined in `app/Providers/RouteServiceProvider.php`):

```
1. CENTRAL ADMIN ROUTES (/midas-admin/*)
   ↓
2. PUBLIC WEBSITE ROUTES (/, /features, /pricing - central domain only)
   ↓
3. TENANT STAFF PORTAL ROUTES (/* - tenant subdomains only)
   ↓
4. CUSTOMER PORTAL ROUTES (/customer/* - tenant subdomains only)
```

### Domain Access Rules

#### Central Domain Access (midastech.in)

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

#### Tenant Subdomain Access ({tenant}.midastech.in)

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

### Complete Route Map

#### Public Website Routes (routes/public.php)
**Domain**: Central only
**Middleware**: `web`, `central.only`

| Method | Path | Name | Purpose |
|--------|------|------|---------|
| GET | / | public.home | Marketing homepage |
| GET | /features | public.features | Feature showcase |
| GET | /pricing | public.pricing | Pricing plans |
| GET | /about | public.about | About company |
| GET | /contact | public.contact | Contact form |

#### Central Admin Routes (routes/central.php)
**Domain**: Central only (/midas-admin)
**Middleware**: `web`, `central.auth`

| Method | Path | Name | Purpose |
|--------|------|------|---------|
| GET | /midas-admin/login | central.login | Central admin login page |
| POST | /midas-admin/login | - | Process central login |
| POST | /midas-admin/logout | central.logout | Central logout |
| GET | /midas-admin/dashboard | central.dashboard | Central dashboard |
| Resource | /midas-admin/tenants | central.tenants.* | Tenant CRUD |
| Resource | /midas-admin/subscriptions | central.subscriptions.* | Subscription management |

#### Tenant Staff Portal Routes (routes/web.php)
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
| Resource | /billing | billing.* | Invoicing & billing |
| Resource | /appointments | appointments.* | Appointment scheduling |

#### Customer Portal Routes (routes/customer.php)
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

---

## Middleware Stack

### Core Middleware

#### Tenancy Middleware

**`universal` - InitializeTenancyByDomain**
- **Purpose**: Identify tenant from subdomain and initialize tenant context
- **Class**: `Stancl\Tenancy\Middleware\InitializeTenancyByDomain`
- **Usage**: All tenant subdomain routes
- **Process**:
  1. Extracts subdomain from request
  2. Looks up tenant in `tenants` table
  3. Switches database connection to tenant-specific database
  4. Sets tenant context for entire request lifecycle

**`tenant` - PreventAccessFromCentralDomains**
- **Purpose**: Block access to tenant routes from central domains
- **Class**: `Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains`
- **Usage**: All tenant routes
- **Process**:
  1. Checks if tenant context was initialized
  2. If no tenant context, aborts with 404
  3. Ensures routes only accessible from tenant subdomains

**`central.only` - PreventAccessFromTenantDomains**
- **Purpose**: Block access to central routes from tenant subdomains
- **Class**: `App\Http\Middleware\PreventAccessFromTenantDomains`
- **Usage**: Public website routes
- **Process**:
  1. Gets current domain from request
  2. Checks if domain is in `config('tenancy.central_domains')`
  3. If NOT a central domain, aborts with 404

#### Authentication Middleware

**`auth` - Authenticate**
- **Purpose**: Verify user is authenticated, redirect if not
- **Class**: `App\Http\Middleware\Authenticate`
- **Domain-Aware Redirect Logic**:
  - Central domain → `route('central.login')`
  - Tenant subdomain + `/customer/*` → `route('customer.login')`
  - Tenant subdomain + other → `route('login')`

**`guest` - RedirectIfAuthenticated**
- **Purpose**: Redirect authenticated users away from login pages
- **Class**: `App\Http\Middleware\RedirectIfAuthenticated`
- **Redirect Logic**:
  - `central` guard → `route('central.dashboard')`
  - `customer` guard → `route('customer.dashboard')`
  - `web` guard → `/home`

**`central.auth` - CentralAuth**
- **Purpose**: Verify user is authenticated as central admin
- **Class**: `App\Http\Middleware\CentralAuth`

**`customer.auth` - CustomerAuth**
- **Purpose**: Verify user is authenticated as customer
- **Class**: `App\Http\Middleware\CustomerAuth`

### Middleware Execution Order

**CRITICAL**: Middleware must execute in correct order!

#### Correct Order for Tenant Routes
```php
Route::middleware(['web', 'universal', 'tenant', 'auth'])
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

### Middleware Stack by Portal

#### Central Admin Portal
```php
Route::prefix('midas-admin')
    ->middleware('web')
    ->group(base_path('routes/central.php'));
```

**Stack**: `web` → NO tenant middleware (central database only)

#### Public Website
```php
Route::middleware(['web', 'central.only'])
    ->withoutMiddleware('universal')
    ->group(base_path('routes/public.php'));
```

**Stack**: `web` → `central.only` → NO `universal` (no tenant identification)

#### Tenant Staff Portal
```php
Route::middleware(['web', 'universal', 'tenant'])
    ->group(base_path('routes/web.php'));
```

**Stack**: `web` → `universal` → `tenant` → (plus `auth` on protected routes)

#### Customer Portal
```php
Route::middleware(['web', 'universal', 'tenant'])
    ->group(base_path('routes/customer.php'));
```

**Stack**: `web` → `universal` → `tenant` → (plus `customer.auth` on protected routes)

---

## Authentication System

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

### Authentication Flows

#### Central Admin Login Flow
```
1. User visits midastech.in/midas-admin/login
2. Submits credentials
3. CentralAuthController validates against central database
4. Auth::guard('central')->attempt()
5. Redirect to route('central.dashboard') → /midas-admin/dashboard
```

#### Tenant Staff Login Flow
```
1. User visits acme.midastech.in/login
2. 'universal' middleware identifies tenant from subdomain
3. Tenant context initialized (database switched to acme_db)
4. Submits credentials
5. Auth::guard('web')->attempt() validates against tenant database
6. Redirect to route('home') → /home
```

#### Customer Login Flow
```
1. User visits acme.midastech.in/customer/login
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

---

## Portal Structure

### 1. Public Website Portal

**Domain**: midastech.in (central domain only)
**Purpose**: Marketing website, lead generation, tenant signup, pricing information

**Key Features**:
- Marketing homepage with product showcase
- Feature listing and benefits
- Pricing plans display
- Contact form for lead capture
- Tenant signup/registration
- Responsive design for all devices
- SEO optimized pages

**Key Routes**:
- `/` - Homepage with hero section, features overview
- `/features` - Detailed feature showcase
- `/pricing` - Pricing tiers (Starter/Professional/Enterprise)
- `/about` - Company information
- `/contact` - Contact form submission

**Technologies**:
- Bootstrap 5.3.2 for responsive layout
- jQuery 3.7.1 for interactions
- Blade templates for server-side rendering
- No authentication required (public access)

**Access Control**:
- **Accessible from**: Central domain only (midastech.in)
- **Blocked from**: Tenant subdomains (404 error)
- **Middleware**: `web`, `central.only`

**Contact Form Handling**:
- Form submissions stored in `contact_submissions` table (central database)
- Email notification sent to admin
- Admin can view/manage submissions from central admin panel
- Anti-spam protection with rate limiting (5 submissions/minute)

**Navigation Behavior**:
- `/login` → Redirects to `/midas-admin/login` (central admin)
- `/register` → Redirects to homepage with info message
- Header links to central admin for authenticated users

**Controllers**:
- `PublicController@home` - Homepage
- `PublicController@features` - Features page
- `PublicController@pricing` - Pricing page
- `PublicController@about` - About page
- `PublicController@contact` - Contact form (GET/POST)
- `PublicController@submitContact` - Process form submission

**Templates Location**: `resources/views/public/`

### 2. Central Admin Portal

**Domain**: midastech.in/midas-admin
**Purpose**: Platform management - create/manage tenants, subscriptions, global settings

**Key Features**:
- Tenant Management (create, edit, suspend, delete)
- Subscription & Billing Management
- Revenue Dashboard & Analytics
- System Health Monitoring
- Tenant User Impersonation
- Global System Configuration
- Audit Logs

**Dashboard Metrics**:
- Total Tenants (active, trial, suspended, cancelled)
- Monthly Recurring Revenue (MRR)
- New Tenants This Month
- Churn Rate
- Total Users Across All Tenants
- System Health Status

**Tenant Actions**:
- **Create Tenant**: Automated provisioning with database creation
- **Suspend Tenant**: Disable tenant access with notification
- **Activate Tenant**: Re-enable suspended tenant
- **Delete Tenant**: Soft delete with 30-day recovery window
- **Impersonate User**: Login as tenant user for support

### 3. Tenant Staff Portal

**Domain**: {tenant}.midastech.in
**Purpose**: Business operations - manage members, services, billing, appointments

**Key Routes**:
- `/home` - Staff dashboard
- `/customers` - Customer management
- `/members` - Member management
- `/services` - Service catalog
- `/packages` - Package management
- `/billing` - Invoicing & billing
- `/appointments` - Appointment scheduling
- `/communication` - WhatsApp/SMS/Email
- `/reports` - Business reports

**Authentication**: Guard `web`, User Model `App\Models\User`

### 4. Customer Portal

**Domain**: {tenant}.midastech.in/customer
**Purpose**: Customer self-service - view memberships, make payments, book appointments

**Key Routes**:
- `/customer/dashboard` - Customer dashboard
- `/customer/profile` - Customer profile
- `/customer/memberships` - View memberships
- `/customer/appointments` - View/book appointments
- `/customer/payments` - Payment history
- `/customer/invoices` - View invoices
- `/customer/family` - Family member management

**Authentication**: Guard `customer`, User Model `App\Models\Customer`

**Security Features**:
- Session timeout enforcement
- Secure session handling
- Customer-scoped data access

---

## Infrastructure Requirements

### Required Packages
```json
{
  "stancl/tenancy": "^3.8",
  "stripe/stripe-php": "^13.0",
  "razorpay/razorpay": "^2.9"
}
```

### Server Requirements
1. **DNS**: Wildcard subdomain (`*.midastech.in`)
2. **SSL**: Wildcard SSL certificate (Let's Encrypt)
3. **Database**: MySQL 8.0+ with 20+ connections per tenant
4. **Server**: 4GB+ RAM, multi-core CPU
5. **Storage**: SSD with sufficient space for tenant isolation

### Environment Variables
```env
# Multi-Tenancy Configuration
TENANCY_DATABASE=central
TENANT_DATABASE_PREFIX=tenant_
TENANT_SUBDOMAIN_ENABLED=true
APP_DOMAIN=midastech.in
CENTRAL_DOMAIN=midastech.in
CENTRAL_ADMIN_PATH=/admin

# Billing
STRIPE_KEY=
STRIPE_SECRET=
RAZORPAY_KEY_ID=
RAZORPAY_KEY_SECRET=
```

### Central Domains Configuration

```php
// config/tenancy.php
'central_domains' => [
    'midastech.in',
    'midastech.testing.in',
    'midastech.testing.in:8085',  // Development with port
],
```

### Performance Considerations

- **Database connection pooling**
- **Redis cache per tenant** (optional)
- **Query optimization** for tenant context
- **Indexed subdomain lookups**
- **Lazy loading** tenant data
- **Support for 100+ tenants** on single server
- **Horizontal scaling** via multiple app servers

### Security Measures

**Data Security**:
- Complete isolation between tenants
- No cross-tenant queries possible
- Separate file storage per tenant
- Tenant-scoped sessions and cache
- Encrypted sensitive data

**Central Admin Protection**:
- IP Whitelist (optional)
- 2FA Required for super admins
- Session Timeout (30 minutes)
- Audit Logging (all actions)
- Rate Limiting (strict limits)
- HTTPS Only

### Backup & Recovery
- Automated daily backups per tenant database
- Point-in-time recovery capability
- Central database backup
- File storage backups
- Disaster recovery plan
- Soft delete with 30-day recovery

---

## Summary

The Midas Portal uses a **strict domain-based separation** architecture with **4 distinct portals**:

1. **Public Website**: Operates on central domain, marketing and lead generation
2. **Central Admin**: Operates on central domain (/midas-admin), manages platform
3. **Tenant Staff Portal**: Operates on tenant subdomains, manages business operations
4. **Customer Portal**: Operates on tenant subdomains under /customer/*, customer self-service

Each portal has:
- **Isolated authentication** (separate guards where applicable)
- **Domain-based access control** (middleware enforcement)
- **Proper redirect logic** (domain-aware redirects)
- **Database isolation** (central vs tenant databases)
- **Clear routing boundaries** (no cross-portal route conflicts)

This architecture ensures:
- ✅ No cross-portal authentication leaks
- ✅ No accidental tenant data access
- ✅ Clear separation of concerns
- ✅ Scalable multi-tenant system
- ✅ Complete data isolation per tenant
- ✅ Consistent URL patterns across all tenants
- ✅ Public marketing site isolated from tenant operations
- ✅ Central administration separate from public website

---

**Document Version**: 1.0
**Last Updated**: 2025-11-03
**Status**: Production-Ready
