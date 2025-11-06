# Middleware Reference

**Complete documentation for all 21 middleware classes in the Midas Portal application**

---

## Table of Contents

- [Overview](#overview)
- [Middleware Execution Order](#middleware-execution-order)
- [Global Middleware](#global-middleware)
- [Middleware Groups](#middleware-groups)
- [Route Middleware](#route-middleware)
- [Tenancy Middleware](#tenancy-middleware)
- [Authentication Middleware](#authentication-middleware)
- [Authorization Middleware](#authorization-middleware)
- [Security Middleware](#security-middleware)
- [Rate Limiting Middleware](#rate-limiting-middleware)
- [Session Management Middleware](#session-management-middleware)
- [Subscription & Limits Middleware](#subscription--limits-middleware)
- [Middleware Stack Examples](#middleware-stack-examples)
- [Custom Middleware Configuration](#custom-middleware-configuration)

---

## Overview

The application uses 21 middleware classes organized into global, group, and route-specific layers to handle cross-cutting concerns including security, authentication, tenancy, rate limiting, and subscription management.

### Middleware Statistics

- **Total Middleware**: 21 custom middleware classes
- **Global Middleware**: 8 middleware (run on every request)
- **Web Group**: 6 middleware (session, CSRF, cookies)
- **API Group**: 2 middleware (throttle, bindings)
- **Route Middleware**: 13 custom route middleware
- **Tenancy Middleware**: 3 tenancy-related middleware

### Middleware Location

All middleware located in: `app/Http/Middleware/`

---

## Middleware Execution Order

### Complete Stack for Tenant Staff Portal

```
REQUEST
    ↓
1. GLOBAL MIDDLEWARE (runs first)
    ├── TrustProxies
    ├── HandleCors
    ├── PreventRequestsDuringMaintenance
    ├── ValidatePostSize
    ├── TrimStrings
    ├── ConvertEmptyStringsToNull
    ├── SecurityHeadersMiddleware ⚠️ Security headers & CSP
    └── InitializeTenancyByDomainEarly ⚠️ Tenancy (skips central domains)
    ↓
2. WEB MIDDLEWARE GROUP
    ├── EncryptCookies
    ├── AddQueuedCookiesToResponse
    ├── StartSession
    ├── ShareErrorsFromSession
    ├── VerifyCsrfToken
    └── SubstituteBindings
    ↓
3. ROUTE-SPECIFIC MIDDLEWARE
    ├── subscription.status → CheckSubscriptionStatus ⚠️ Subscription validation
    ├── auth → Authenticate (web guard)
    ├── role → RoleMiddleware (Spatie)
    └── permission → PermissionMiddleware (Spatie)
    ↓
CONTROLLER ACTION
    ↓
RESPONSE
```

### Complete Stack for Customer Portal

```
REQUEST
    ↓
1. GLOBAL MIDDLEWARE
    └── [same as above]
    ↓
2. WEB MIDDLEWARE GROUP
    └── [same as above]
    ↓
3. ROUTE-SPECIFIC MIDDLEWARE
    ├── subscription.status → CheckSubscriptionStatus
    ├── customer.auth → CustomerAuth ⚠️ Customer authentication
    ├── customer.timeout → CustomerSessionTimeout ⚠️ Session timeout
    ├── customer.secure → SecureSession ⚠️ Security checks
    └── customer.family → VerifyFamilyAccess ⚠️ Family group validation
    ↓
CONTROLLER ACTION
    ↓
RESPONSE
```

### Complete Stack for Central Admin Portal

```
REQUEST
    ↓
1. GLOBAL MIDDLEWARE
    └── [InitializeTenancyByDomainEarly SKIPS central domains]
    ↓
2. WEB MIDDLEWARE GROUP
    └── [same as above]
    ↓
3. ROUTE-SPECIFIC MIDDLEWARE
    ├── central.only → PreventAccessFromTenantDomains ⚠️ Blocks tenant domains
    ├── central.auth → CentralAuth ⚠️ Central admin authentication
    └── central.auth:super → CentralAuth with role check
    ↓
CONTROLLER ACTION
    ↓
RESPONSE
```

---

## Global Middleware

Global middleware runs on **every HTTP request** to the application.

### 1. TrustProxies

**File**: `app/Http/Middleware/TrustProxies.php`
**Purpose**: Configure trusted proxy servers for IP and scheme detection
**Order**: 1st (runs first)

**Configuration**:
```php
protected $proxies = '*'; // Trust all proxies
protected $headers = Request::HEADER_X_FORWARDED_ALL;
```

**Usage**: Automatically applied to all requests

### 2. HandleCors (Laravel Built-in)

**Purpose**: Handle Cross-Origin Resource Sharing (CORS)
**Order**: 2nd
**Config**: `config/cors.php`

### 3. PreventRequestsDuringMaintenance

**File**: `app/Http/Middleware/PreventRequestsDuringMaintenance.php`
**Purpose**: Block requests when application is in maintenance mode
**Order**: 3rd

**Exceptions**: Routes excluded from maintenance mode blocking
```php
protected $except = [
    'health', // Health check endpoint
];
```

### 4. ValidatePostSize (Laravel Built-in)

**Purpose**: Validate POST request size doesn't exceed server limits
**Order**: 4th

### 5. TrimStrings

**File**: `app/Http/Middleware/TrimStrings.php`
**Purpose**: Trim whitespace from all request input
**Order**: 5th

**Exceptions**:
```php
protected $except = [
    'password',
    'password_confirmation',
    'current_password',
];
```

### 6. ConvertEmptyStringsToNull (Laravel Built-in)

**Purpose**: Convert empty strings to null in request
**Order**: 6th

### 7. SecurityHeadersMiddleware ⚠️

**File**: `app/Http/Middleware/SecurityHeadersMiddleware.php`
**Purpose**: Add comprehensive security headers and Content Security Policy
**Order**: 7th
**Dependencies**: ContentSecurityPolicyService

#### Features

- **Security Headers**: X-Frame-Options, X-Content-Type-Options, HSTS, etc.
- **Content Security Policy**: Dynamic CSP with nonce support
- **CSP Reporting**: Report-To header for violation reporting
- **View Integration**: Shares CSP nonce with all Blade views

#### Applied Headers

```php
'X-Content-Type-Options' => 'nosniff',
'X-Frame-Options' => 'DENY',
'X-XSS-Protection' => '1; mode=block',
'Referrer-Policy' => 'strict-origin-when-cross-origin',
'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
'Content-Security-Policy' => '[Dynamic CSP with nonce]'
```

#### Skipped Routes

```php
private function shouldSkipSecurityHeaders(Request $request): bool
{
    $skipRoutes = [
        'horizon.*',
        'telescope.*',
        'webmonks-log-viewer.*',
        'api/docs.*',
    ];
    // ...
}
```

#### CSP Configuration

```php
// Nonce usage in views:
<script nonce="{{ $cspNonce }}">
    // Your inline script
</script>

// CSP directives:
default-src 'self';
script-src 'self' 'nonce-{random}' https://cdn.jsdelivr.net;
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
font-src 'self' https://fonts.gstatic.com;
img-src 'self' data: https:;
```

**Configuration**: `config/security.php`
```php
'csp_enabled' => true,
'csp_report_uri' => env('CSP_REPORT_URI'),
```

### 8. InitializeTenancyByDomainEarly ⚠️

**File**: `app/Http/Middleware/InitializeTenancyByDomainEarly.php`
**Purpose**: Initialize tenancy context BEFORE session middleware
**Order**: 8th (last global middleware)
**Extends**: `Stancl\Tenancy\Middleware\InitializeTenancyByDomain`

#### Critical Positioning

**Why it runs BEFORE session middleware:**
- Session needs correct database connection
- Tenant database must be selected before loading authenticated users
- Prevents auth errors from wrong database context

#### Logic

```php
public function handle($request, Closure $next)
{
    $currentDomain = $request->getHost();
    $centralDomains = config('tenancy.central_domains', []);

    // Skip for central domains (midastech.in)
    if (in_array($currentDomain, $centralDomains)) {
        return $next($request);
    }

    // Initialize tenancy for tenant subdomains (acme.midastech.in)
    return parent::handle($request, $next);
}
```

#### Domain Detection

| Domain | Tenancy Initialized | Database Used |
|--------|-------------------|---------------|
| `midastech.in` | ❌ No | Central DB |
| `acme.midastech.in` | ✅ Yes | Tenant DB (acme) |
| `xyz.midastech.in` | ✅ Yes | Tenant DB (xyz) |

**Configuration**: `config/tenancy.php`
```php
'central_domains' => [
    'midastech.in',
    'localhost',
],
```

---

## Middleware Groups

### Web Middleware Group

Applied to all web routes (non-API).

```php
'web' => [
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    ShareErrorsFromSession::class,
    VerifyCsrfToken::class,
    SubstituteBindings::class,
],
```

#### 1. EncryptCookies

**File**: `app/Http/Middleware/EncryptCookies.php`
**Purpose**: Encrypt and decrypt cookies

**Exceptions** (not encrypted):
```php
protected $except = [
    // Add cookies that should NOT be encrypted
];
```

#### 2. AddQueuedCookiesToResponse (Laravel)

**Purpose**: Add queued cookies to response

#### 3. StartSession (Laravel)

**Purpose**: Start session for request

#### 4. ShareErrorsFromSession (Laravel)

**Purpose**: Share validation errors with views

#### 5. VerifyCsrfToken

**File**: `app/Http/Middleware/VerifyCsrfToken.php`
**Purpose**: Verify CSRF token on POST/PUT/DELETE requests

**Exceptions**:
```php
protected $except = [
    'webhooks/*', // Payment gateway webhooks
    'api/*', // API routes
];
```

#### 6. SubstituteBindings (Laravel)

**Purpose**: Resolve route model bindings

### API Middleware Group

Applied to API routes only.

```php
'api' => [
    'throttle:api',
    SubstituteBindings::class,
],
```

**Default Throttle**: 60 requests per minute

---

## Route Middleware

Route middleware are applied individually to specific routes.

### Tenancy Middleware

#### 1. universal (Stancl Package)

**Class**: `Stancl\Tenancy\Middleware\InitializeTenancyByDomain`
**Alias**: `universal`
**Purpose**: Initialize tenancy by domain (used in route groups)

**Note**: We use `InitializeTenancyByDomainEarly` in global middleware instead for most cases.

#### 2. tenant (Stancl Package)

**Class**: `Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains`
**Alias**: `tenant`
**Purpose**: Block access from central domains to tenant-only routes

**Usage**:
```php
Route::middleware(['web', 'tenant'])->group(function () {
    // Tenant-only routes (acme.midastech.in)
    // Blocks access from midastech.in
});
```

#### 3. central.only ⚠️

**File**: `app/Http/Middleware/PreventAccessFromTenantDomains.php`
**Alias**: `central.only`
**Purpose**: Block access from tenant domains to central-only routes

**Logic**:
```php
public function handle(Request $request, Closure $next): Response
{
    $currentDomain = $request->getHost();
    $centralDomains = config('tenancy.central_domains', []);

    // If accessing from tenant domain, block
    if (!in_array($currentDomain, $centralDomains)) {
        abort(403, 'This page is only accessible from the central domain.');
    }

    return $next($request);
}
```

**Usage**:
```php
Route::middleware(['web', 'central.only'])->group(function () {
    // Central-only routes (midastech.in)
    // Blocks access from acme.midastech.in
});
```

**Applied to**:
- `/midas-admin/*` routes
- Public website routes (`/`, `/features`, `/pricing`)

---

## Authentication Middleware

### 1. auth (Laravel Default)

**File**: `app/Http/Middleware/Authenticate.php`
**Alias**: `auth`
**Purpose**: Authenticate users with default guard (web)

**Redirect Logic**:
```php
protected function redirectTo(Request $request): ?string
{
    if (!$request->expectsJson()) {
        return route('login');
    }
    return null;
}
```

**Usage**:
```php
Route::middleware(['auth'])->group(function () {
    // Requires web guard authentication
});
```

### 2. guest (Laravel Default)

**File**: `app/Http/Middleware/RedirectIfAuthenticated.php`
**Alias**: `guest`
**Purpose**: Redirect authenticated users away from guest routes

**Usage**: Applied to login/register routes

### 3. central.auth ⚠️

**File**: `app/Http/Middleware/CentralAuth.php`
**Alias**: `central.auth`
**Purpose**: Authenticate central admin users with role-based access

#### Features

- **Guard**: `central`
- **Active Check**: Verifies user is active
- **Role Check**: Optional role parameter
- **Default Guard**: Sets `central` as default guard for request

#### Usage

```php
// Basic authentication
Route::middleware(['central.auth'])->group(function () {
    // Any central admin
});

// Role-based authentication
Route::middleware(['central.auth:super'])->group(function () {
    // Super admins only
});

Route::middleware(['central.auth:support'])->group(function () {
    // Support admins only
});

Route::middleware(['central.auth:billing'])->group(function () {
    // Billing admins only
});
```

#### Role Checks

```php
public function handle(Request $request, Closure $next, ?string $role = null): Response
{
    // Authenticate
    if (!Auth::guard('central')->check()) {
        return redirect()->route('central.login');
    }

    $user = Auth::guard('central')->user();

    // Active check
    if (!$user->is_active) {
        Auth::guard('central')->logout();
        return redirect()->route('central.login')
            ->with('error', 'Account deactivated');
    }

    // Role check
    if ($role) {
        $hasPermission = match ($role) {
            'super' => $user->isSuperAdmin(),
            'support' => $user->isSupportAdmin(),
            'billing' => $user->isBillingAdmin(),
            default => false,
        };

        if (!$hasPermission) {
            abort(403, 'Unauthorized action.');
        }
    }

    // Set default guard
    Auth::shouldUse('central');

    return $next($request);
}
```

### 4. customer.auth ⚠️

**File**: `app/Http/Middleware/CustomerAuth.php`
**Alias**: `customer.auth`
**Purpose**: Authenticate customer portal users

#### Features

- **Guard**: `customer`
- **Active Check**: Verifies customer is active
- **Password Change**: Forces password change for first login
- **Excluded Routes**: Password change, email verification, 2FA routes

#### Logic

```php
public function handle(Request $request, Closure $next): Response
{
    // Authenticate
    if (!Auth::guard('customer')->check()) {
        return redirect()->route('customer.login')
            ->with('error', 'Please login to access customer portal.');
    }

    $customer = Auth::guard('customer')->user();

    // Active check
    if (!$customer || !$customer->status) {
        Auth::guard('customer')->logout();
        return redirect()->route('customer.login')
            ->with('error', 'Account deactivated.');
    }

    // Force password change (except excluded routes)
    $excludedRoutes = [
        'customer.change-password',
        'customer.change-password.update',
        'customer.verify-email-notice',
        'customer.two-factor.challenge',
        'customer.two-factor.verify',
        'customer.logout',
    ];

    if (!in_array($request->route()->getName(), $excludedRoutes)
        && $customer->needsPasswordChange()) {
        return redirect()->route('customer.change-password')
            ->with('warning', 'You must change your password before continuing.');
    }

    return $next($request);
}
```

**Usage**:
```php
Route::middleware(['customer.auth'])->group(function () {
    // Customer portal routes
});
```

---

## Authorization Middleware

### 1. role (Spatie Package)

**Class**: `Spatie\Permission\Middlewares\RoleMiddleware`
**Alias**: `role`
**Purpose**: Check user has specific role

**Usage**:
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin role required
});

Route::middleware(['auth', 'role:admin|manager'])->group(function () {
    // Admin OR manager role
});
```

### 2. permission (Spatie Package)

**Class**: `Spatie\Permission\Middlewares\PermissionMiddleware`
**Alias**: `permission`
**Purpose**: Check user has specific permission

**Usage**:
```php
Route::middleware(['auth', 'permission:edit articles'])->group(function () {
    // Permission required
});
```

### 3. role_or_permission (Spatie Package)

**Class**: `Spatie\Permission\Middlewares\RoleOrPermissionMiddleware`
**Alias**: `role_or_permission`
**Purpose**: Check user has role OR permission

**Usage**:
```php
Route::middleware(['auth', 'role_or_permission:admin|edit articles'])->group(function () {
    // Admin role OR edit articles permission
});
```

### 4. enhanced.auth

**File**: `app/Http/Middleware/EnhancedAuthorizationMiddleware.php`
**Alias**: `enhanced.auth`
**Purpose**: Enhanced authorization with additional security checks

**Features**: Custom authorization logic, security event logging

---

## Security Middleware

### 1. customer.secure ⚠️

**File**: `app/Http/Middleware/SecureSession.php`
**Alias**: `customer.secure`
**Purpose**: Additional security checks for customer portal

**Features**:
- IP address change detection
- User agent change detection
- Session hijacking prevention
- Suspicious activity logging

**Applied to**: Customer portal routes requiring extra security

### 2. VerifyTwoFactorSession ⚠️

**File**: `app/Http/Middleware/VerifyTwoFactorSession.php`
**Purpose**: Verify 2FA session data exists for 2FA routes

**Logic**:
```php
public function handle(Request $request, Closure $next)
{
    // Only apply to 2FA challenge routes
    if (!$request->routeIs('*.two-factor.challenge', '*.two-factor.verify')) {
        return $next($request);
    }

    $userId = session('2fa_user_id');
    $guard = session('2fa_guard', 'web');

    Log::info('🔐 [2FA Middleware] Session verification', [
        'route' => $request->route()->getName(),
        'has_2fa_user_id' => !empty($userId),
        '2fa_user_id' => $userId,
        '2fa_guard' => $guard,
    ]);

    return $next($request);
}
```

**Usage**: Applied automatically to 2FA challenge/verify routes

---

## Rate Limiting Middleware

### 1. throttle (Laravel Default)

**Purpose**: Rate limit requests using Laravel's built-in throttling

**Usage**:
```php
Route::middleware(['throttle:60,1'])->group(function () {
    // 60 requests per 1 minute
});

Route::middleware(['throttle:api'])->group(function () {
    // Uses 'api' throttle configuration
});
```

**Configuration**: `config/sanctum.php` or route service provider

### 2. rate.limit ⚠️

**File**: `app/Http/Middleware/RateLimit.php`
**Alias**: `rate.limit`
**Purpose**: Advanced rate limiting with analytics and custom identifiers

#### Features

- **Multiple Identifiers**: API key, user ID, IP address
- **Configurable Limits**: Custom max attempts and decay time
- **Rate Limit Headers**: X-RateLimit-* headers
- **Analytics Logging**: Logs to `rate_limit_attempts` table
- **Graceful Responses**: JSON responses with retry information

#### Parameters

```php
public function handle(
    Request $request,
    Closure $next,
    int $maxAttempts = 60,      // Max requests allowed
    int $decayMinutes = 1,       // Time window in minutes
    string $prefix = 'global'    // Identifier prefix for cache key
): Response
```

#### Usage

```php
// Basic usage (60 requests per minute)
Route::middleware(['rate.limit'])->group(function () {
    // Default: 60/min
});

// Custom limits
Route::middleware(['rate.limit:10,1,login'])->group(function () {
    // 10 requests per minute, prefix 'login'
});

Route::middleware(['rate.limit:100,5,api'])->group(function () {
    // 100 requests per 5 minutes, prefix 'api'
});
```

#### Identifier Resolution

```php
protected function resolveRequestIdentifier(Request $request, string $prefix): string
{
    // 1. API key (if available)
    $apiKey = $request->attributes->get('api_key');
    if ($apiKey) {
        return "api_key:{$apiKey->id}";
    }

    // 2. Authenticated user
    if ($request->user()) {
        return "user:{$request->user()->id}";
    }

    // 3. Fallback to IP address
    return "ip:{$request->ip()}";
}
```

#### Response Headers

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1699876543
```

#### Rate Limit Exceeded Response

```json
{
  "error": "Rate Limit Exceeded",
  "message": "Too many requests. Limit of 60 requests per 1 minute(s) exceeded.",
  "status_code": 429,
  "rate_limit": {
    "limit": 60,
    "remaining": 0,
    "reset_in_seconds": 45,
    "reset_at": 1699876543
  }
}
```

#### Analytics Logging

```php
protected function logRateLimitAttempt(Request $request, string $identifier, string $prefix): void
{
    $endpoint = $request->route()?->uri() ?? $request->path();
    $identifierType = explode(':', $identifier)[0];
    $identifierValue = explode(':', $identifier)[1] ?? $identifier;

    // Logs to rate_limit_attempts table
    RateLimitAttempt::updateOrCreate([
        'identifier' => $identifierValue,
        'identifier_type' => $identifierType,
        'endpoint' => $endpoint,
        'window_start' => now()->startOfMinute(),
    ], [
        'attempts' => DB::raw('attempts + 1'),
        'last_attempt' => now(),
    ]);
}
```

**Applied to**:
- Login routes (10 requests/min)
- API endpoints (100 requests/5min)
- Password reset routes (5 requests/15min)

---

## Session Management Middleware

### 1. customer.timeout ⚠️

**File**: `app/Http/Middleware/CustomerSessionTimeout.php`
**Alias**: `customer.timeout`
**Purpose**: Enforce session timeout for customer portal

#### Features

- **Configurable Timeout**: Default 30 minutes
- **Activity Tracking**: Updates last activity timestamp
- **Automatic Logout**: Logs out inactive customers
- **Grace Period**: Warning before logout

#### Configuration

```php
// config/session.php or custom config
'customer_timeout' => 30, // minutes
```

#### Logic

```php
public function handle(Request $request, Closure $next)
{
    $customer = Auth::guard('customer')->user();

    if ($customer) {
        $lastActivity = session('customer_last_activity');
        $timeout = config('session.customer_timeout', 30);

        if ($lastActivity && now()->diffInMinutes($lastActivity) > $timeout) {
            Auth::guard('customer')->logout();
            session()->flush();

            return redirect()->route('customer.login')
                ->with('warning', 'Your session has expired due to inactivity.');
        }

        session(['customer_last_activity' => now()]);
    }

    return $next($request);
}
```

**Applied to**: All customer portal routes

---

## Subscription & Limits Middleware

### 1. subscription.status ⚠️

**File**: `app/Http/Middleware/CheckSubscriptionStatus.php`
**Alias**: `subscription.status`
**Purpose**: Enforce subscription status checks for tenant access

#### Features

- **Multi-Status Checks**: No subscription, suspended, cancelled, expired, trial ended
- **Route Exceptions**: Allows subscription pages and auth routes
- **JSON Support**: Returns JSON for API requests
- **Graceful Redirects**: User-friendly error messages

#### Status Checks

| Status | Condition | Action |
|--------|-----------|--------|
| **No Subscription** | `!$subscription` | Redirect to `subscription.required` |
| **Suspended** | `isSuspended()` | Redirect to `subscription.suspended` |
| **Cancelled** | `isCancelled()` | Redirect to `subscription.cancelled` |
| **Expired** | `hasExpired()` | Redirect to `subscription.plans` |
| **Trial Ended** | `trialEnded() && status != 'active'` | Redirect to `subscription.plans` |

#### Exempted Routes

```php
protected $except = [
    // Subscription management
    'subscription.required',
    'subscription.suspended',
    'subscription.cancelled',
    'subscription.upgrade',
    'subscription.plans',
    'subscription.index',

    // Staff authentication
    'login',
    'logout',
    'password.*',
    'tenant.root',

    // Customer authentication
    'customer.login',
    'customer.logout',
    'customer.password.*',
    'customer.verify-email',
];
```

#### Logic

```php
public function handle(Request $request, Closure $next): Response
{
    $tenant = tenant();

    if (!$tenant) {
        return $next($request);
    }

    // Allow exempted routes
    if ($request->routeIs($this->except)) {
        return $next($request);
    }

    $subscription = $tenant->subscription;

    // No subscription
    if (!$subscription) {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'No active subscription',
                'message' => 'This organization does not have an active subscription.',
            ], 403);
        }

        return redirect()->route('subscription.required')
            ->with('error', 'No active subscription found.');
    }

    // Suspended
    if ($subscription->isSuspended()) {
        return redirect()->route('subscription.suspended')
            ->with('error', 'Your subscription has been suspended.');
    }

    // Cancelled
    if ($subscription->isCancelled()) {
        return redirect()->route('subscription.cancelled')
            ->with('error', 'Your subscription has been cancelled.');
    }

    // Expired
    if ($subscription->hasExpired()) {
        return redirect()->route('subscription.plans')
            ->with('error', 'Your subscription has expired. Please renew.');
    }

    // Trial ended (but check if converted to paid)
    if ($subscription->trialEnded()) {
        if ($subscription->status === 'active') {
            return $next($request); // Converted to paid
        }

        return redirect()->route('subscription.plans')
            ->with('warning', 'Your trial has expired. Please upgrade.');
    }

    return $next($request);
}
```

**Applied to**: All tenant staff and customer portal routes

### 2. tenant.limits ⚠️

**File**: `app/Http/Middleware/CheckTenantLimits.php`
**Alias**: `tenant.limits`
**Purpose**: Enforce plan limits before resource creation
**Dependencies**: UsageTrackingService

#### Parameters

```php
public function handle(
    Request $request,
    Closure $next,
    string $resourceType // 'user', 'customer', 'policy', etc.
): Response
```

#### Features

- **Subscription Check**: Validates active subscription
- **Limit Enforcement**: Checks plan limits via UsageTrackingService
- **User-Friendly Messages**: Specific messages per resource type
- **Upgrade Prompts**: Suggests plan upgrade

#### Logic

```php
public function handle(Request $request, Closure $next, string $resourceType): Response
{
    $tenant = tenant();

    if (!$tenant) {
        return $next($request);
    }

    $subscription = $tenant->subscription;

    // No subscription
    if (!$subscription) {
        return redirect()->route('subscription.required')
            ->with('error', 'No active subscription found.');
    }

    // Inactive subscription
    if (!$subscription->isActive()) {
        $message = $subscription->isOnTrial()
            ? 'Your trial period has expired. Please upgrade your plan.'
            : 'Your subscription is not active.';

        return redirect()->route('subscription.required')
            ->with('error', $message);
    }

    // Check resource limits
    if (!$this->usageService->canCreate($resourceType)) {
        $plan = $subscription->plan;

        $limitMessage = match ($resourceType) {
            'user' => "You have reached the maximum number of users ({$plan->max_users}) allowed in your {$plan->name} plan.",
            'customer' => "You have reached the maximum number of customers ({$plan->max_customers}) allowed in your {$plan->name} plan.",
            default => "You have reached your plan limit for {$resourceType}.",
        };

        return back()
            ->with('error', $limitMessage . ' Please upgrade your plan to add more.')
            ->with('upgrade_required', true);
    }

    return $next($request);
}
```

#### Usage

```php
// Protect user creation
Route::post('/users', [UserController::class, 'store'])
    ->middleware(['auth', 'tenant.limits:user']);

// Protect customer creation
Route::post('/customers', [CustomerController::class, 'store'])
    ->middleware(['auth', 'tenant.limits:customer']);

// Protect policy creation
Route::post('/policies', [PolicyController::class, 'store'])
    ->middleware(['auth', 'tenant.limits:policy']);
```

**Applied to**: All resource creation routes (users, customers, policies)

### 3. customer.family ⚠️

**File**: `app/Http/Middleware/VerifyFamilyAccess.php`
**Alias**: `customer.family`
**Purpose**: Verify customer has family group access for family-restricted features

#### Features

- **Family Group Check**: Validates customer belongs to family group
- **Family Status Check**: Validates family group is active
- **Selective Access**: Allows quotations for individual customers
- **Detailed Logging**: Logs all access attempts

#### Logic

```php
public function handle(Request $request, Closure $next): Response
{
    $customer = Auth::guard('customer')->user();

    if (!$customer) {
        return redirect()->route('customer.login')
            ->with('error', 'Please login to access this page.');
    }

    if (!$customer->status) {
        Auth::guard('customer')->logout();
        return redirect()->route('customer.login')
            ->with('error', 'Your account has been deactivated.');
    }

    // No family group
    if (!$customer->hasFamily()) {
        // Allow quotations access for individual customers
        if (str_starts_with($request->route()->getName(), 'customer.quotations')) {
            return $next($request);
        }

        // Block policies access
        if (str_starts_with($request->route()->getName(), 'customer.policies')) {
            return redirect()->route('customer.dashboard')
                ->with('warning', 'You need to be part of a family group to access policies.');
        }

        // Block other family features
        return redirect()->route('customer.dashboard')
            ->with('warning', 'You need to be part of a family group to access this feature.');
    }

    // Inactive family group
    if (!$customer->familyGroup->status) {
        return redirect()->route('customer.dashboard')
            ->with('error', 'Your family group is currently inactive.');
    }

    // Access granted
    Log::info('VerifyFamilyAccess: Access granted', [
        'customer_id' => $customer->id,
        'family_group_id' => $customer->familyGroup->id,
        'route' => $request->route()->getName(),
    ]);

    return $next($request);
}
```

**Applied to**:
- `customer.policies.*` routes
- `customer.family.*` routes
- Family-restricted customer portal features

---

## Middleware Stack Examples

### Example 1: Creating a New Customer (Tenant Staff)

```
REQUEST: POST /customers (from staff.midastech.in)
    ↓
GLOBAL MIDDLEWARE
    ├── TrustProxies ✓
    ├── HandleCors ✓
    ├── PreventRequestsDuringMaintenance ✓
    ├── ValidatePostSize ✓
    ├── TrimStrings ✓ (trims all input except passwords)
    ├── ConvertEmptyStringsToNull ✓
    ├── SecurityHeadersMiddleware ✓ (adds CSP headers)
    └── InitializeTenancyByDomainEarly ✓ (initializes tenant: staff)
    ↓
WEB MIDDLEWARE GROUP
    ├── EncryptCookies ✓
    ├── AddQueuedCookiesToResponse ✓
    ├── StartSession ✓ (loads staff session)
    ├── ShareErrorsFromSession ✓
    ├── VerifyCsrfToken ✓ (validates CSRF token)
    └── SubstituteBindings ✓
    ↓
ROUTE MIDDLEWARE
    ├── subscription.status ✓ (checks subscription active)
    ├── auth ✓ (validates staff user authenticated)
    ├── tenant.limits:customer ✓ (checks customer limit not reached)
    └── permission:create customers ✓ (validates permission)
    ↓
CONTROLLER: CustomerController@store
    ↓
RESPONSE
```

### Example 2: Customer Viewing Family Policies

```
REQUEST: GET /customer/policies (from staff.midastech.in/customer)
    ↓
GLOBAL MIDDLEWARE
    └── [same as above, tenancy initialized for staff tenant]
    ↓
WEB MIDDLEWARE GROUP
    └── [same as above]
    ↓
ROUTE MIDDLEWARE
    ├── subscription.status ✓ (checks subscription)
    ├── customer.auth ✓ (validates customer authenticated)
    ├── customer.timeout ✓ (checks session not expired)
    ├── customer.secure ✓ (validates IP/user agent)
    └── customer.family ✓ (validates family group membership)
    ↓
CONTROLLER: CustomerPolicyController@index
    ↓
RESPONSE
```

### Example 3: Central Admin Login

```
REQUEST: POST /midas-admin/login (from midastech.in)
    ↓
GLOBAL MIDDLEWARE
    ├── TrustProxies ✓
    ├── HandleCors ✓
    ├── PreventRequestsDuringMaintenance ✓
    ├── ValidatePostSize ✓
    ├── TrimStrings ✓
    ├── ConvertEmptyStringsToNull ✓
    ├── SecurityHeadersMiddleware ✓
    └── InitializeTenancyByDomainEarly ✓ (SKIPS - central domain)
    ↓
WEB MIDDLEWARE GROUP
    ├── EncryptCookies ✓
    ├── AddQueuedCookiesToResponse ✓
    ├── StartSession ✓ (loads central session)
    ├── ShareErrorsFromSession ✓
    ├── VerifyCsrfToken ✓
    └── SubstituteBindings ✓
    ↓
ROUTE MIDDLEWARE
    ├── central.only ✓ (validates central domain)
    ├── guest ✓ (redirects if authenticated)
    └── rate.limit:10,1,login ✓ (10 attempts per minute)
    ↓
CONTROLLER: CentralAuthController@login
    ↓
RESPONSE
```

### Example 4: API Request with Rate Limiting

```
REQUEST: GET /api/customers (API route)
    ↓
GLOBAL MIDDLEWARE
    └── [same as above]
    ↓
API MIDDLEWARE GROUP
    ├── throttle:api ✓ (60 requests/min)
    └── SubstituteBindings ✓
    ↓
ROUTE MIDDLEWARE
    ├── auth:sanctum ✓ (validates API token)
    └── rate.limit:100,5,api ✓ (100 requests per 5 minutes)
    ↓
CONTROLLER: API\CustomerController@index
    ↓
RESPONSE (with rate limit headers)
```

---

## Custom Middleware Configuration

### Registering Custom Middleware

**Location**: `app/Http/Kernel.php`

```php
protected $routeMiddleware = [
    // Custom middleware
    'central.auth' => \App\Http\Middleware\CentralAuth::class,
    'customer.auth' => \App\Http\Middleware\CustomerAuth::class,
    'customer.family' => \App\Http\Middleware\VerifyFamilyAccess::class,
    'customer.secure' => \App\Http\Middleware\SecureSession::class,
    'customer.timeout' => \App\Http\Middleware\CustomerSessionTimeout::class,
    'subscription.status' => \App\Http\Middleware\CheckSubscriptionStatus::class,
    'tenant.limits' => \App\Http\Middleware\CheckTenantLimits::class,
    'rate.limit' => \App\Http\Middleware\RateLimit::class,
    'central.only' => \App\Http\Middleware\PreventAccessFromTenantDomains::class,
];
```

### Usage Patterns

#### Pattern 1: Middleware Stack

```php
Route::middleware([
    'web',
    'subscription.status',
    'auth',
    'permission:view customers',
])->group(function () {
    Route::get('/customers', [CustomerController::class, 'index']);
});
```

#### Pattern 2: Middleware with Parameters

```php
// Central auth with role
Route::middleware(['central.auth:super'])->group(function () {
    // Super admin only
});

// Rate limiting with custom limits
Route::middleware(['rate.limit:10,1,login'])->group(function () {
    // 10 requests per minute
});

// Tenant limits with resource type
Route::post('/users', [UserController::class, 'store'])
    ->middleware(['tenant.limits:user']);
```

#### Pattern 3: Conditional Middleware

```php
Route::middleware(['web'])->group(function () {
    Route::get('/public', [PublicController::class, 'index']);

    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
    });
});
```

### Creating Custom Middleware

```bash
php artisan make:middleware CustomMiddleware
```

**Template**:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Before request
        // ... your logic here

        $response = $next($request);

        // After request
        // ... your logic here

        return $response;
    }
}
```

**Register in Kernel.php**:
```php
protected $routeMiddleware = [
    'custom' => \App\Http\Middleware\CustomMiddleware::class,
];
```

**Use in routes**:
```php
Route::middleware(['custom'])->group(function () {
    // Routes
});
```

---

## Related Documentation

- [Multi-Portal Architecture](MULTI_PORTAL_ARCHITECTURE.md) - Portal routing and guard configuration
- [Service Layer](SERVICE_LAYER.md) - Service classes used by middleware
- [Database Schema](DATABASE_SCHEMA.md) - Database tables used in middleware
- [API Reference](../API_REFERENCE.md) - Route definitions with middleware

---

**Last Updated**: 2025-11-06
**Total Middleware**: 21 custom middleware classes
**Documentation Coverage**: 100% (all middleware documented)
