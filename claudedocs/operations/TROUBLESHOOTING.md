# Troubleshooting & Fixes

Common issues, solutions, and critical fixes for Midas Portal multi-tenant system.

---

## Critical Fixes Applied

### 1. Domain Routing Fix (Nov 2, 2025) 🔥
**Issue**: TenantCouldNotBeIdentifiedOnDomainException on central domain
**Solution**: Changed to domain-based route registration
**File**: app/Providers/RouteServiceProvider.php

### 2. Tenant Creation Schema Fix (Nov 3, 2025)
**Issue**: Unknown column 'name' - users table has first_name/last_name
**Solution**: Updated TenantController user creation
**File**: app/Http/Controllers/Central/TenantController.php

### 3. Cache Tagging Fix (Nov 2, 2025)
**Issue**: File cache doesn't support tagging
**Solution**: Changed CACHE_DRIVER=database
**File**: .env

### 4. Double Modal Fix (Nov 3, 2025)
**Issue**: Delete button opening two popups
**Solution**: Skip data-confirm if onclick exists
**File**: resources/views/central/layout.blade.php

---

## Common Issues

### Routing Issues

**404 on Central Domain**:
- Check middleware configuration
- Clear route cache: php artisan route:clear
- Verify central.only middleware exists

**Tenant Routes on Central Domain**:
- Ensure tenant middleware applied
- Check route loading order in RouteServiceProvider

### Tenant Creation

**Database Not Created**:
- Check TenancyServiceProvider events
- Verify MySQL CREATE DATABASE permission
- Manually run: php artisan tenants:migrate --tenants={id}

**Migrations Not Running**:
- Verify migrations in database/migrations/tenant/
- Check for errors in migration files

**Seeders Failing**:
- Check schema matches (status vs is_active, first_name vs name)
- Manually run: php artisan tenants:seed --tenants={id}

### Authentication

**Wrong Login Redirect**:
- Check Authenticate middleware domain detection
- Verify CENTRAL_DOMAINS in config/tenancy.php

**Cross-Guard Login**:
- Check session cookie names are unique
- Verify guards use different providers

### Database

**Cross-Tenant Data**:
- Ensure universal middleware applied
- Never use DB::connection('mysql') in tenant context
- Check tenancy()->tenant is not null

**Outdated Schema**:
- Run: php artisan tenants:migrate --tenants={id}
- Or recreate tenant for fresh schema

### Cache

**Cache Leaking**:
- Use database or redis cache (not file)
- Clear: php artisan cache:clear

**Stale Routes**:
- Clear ALL: php artisan optimize:clear
- Rebuild: php artisan route:cache

---

## Subscription Issues

### Trial Not Starting

**Symptoms**: New tenant created but trial_ends_at is null

**Causes**:
1. DEFAULT_TRIAL_DAYS not set in .env
2. Subscription not created during tenant creation
3. TenantCreationService not executing subscription logic

**Solutions**:
```bash
# Check tenant subscription
php artisan tinker
>>> $tenant = \App\Models\Central\Tenant::find('{id}');
>>> $tenant->subscription;

# Manually set trial
>>> $subscription = $tenant->subscription;
>>> $subscription->trial_ends_at = now()->addDays(14);
>>> $subscription->save();

# Or use service
>>> $service = app(\App\Services\TenantCreationService::class);
>>> $service->createTrialSubscription($tenant, 14);
```

**Prevention**: Verify DEFAULT_TRIAL_DAYS in .env is set

### Trial Not Expiring

**Symptoms**: Expired trials still allowing access

**Causes**:
1. Scheduler not running (cron job missing)
2. ProcessTrialSubscriptions command not executing
3. Middleware not checking trial status

**Solutions**:
```bash
# Manually run trial processing
php artisan subscriptions:process-trials

# Check scheduler status
php artisan schedule:list

# Verify cron is set up
crontab -l | grep schedule:run

# Add cron if missing
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

**Check Trial Status**:
```bash
php artisan tinker
>>> $expired = \App\Models\Central\Subscription::where('status', 'trialing')
...     ->where('trial_ends_at', '<', now())
...     ->with('tenant')
...     ->get();
>>> $expired->pluck('tenant.company_name', 'trial_ends_at');
```

### Subscription Status Not Updating

**Symptoms**: Payments succeed but subscription stays in 'trialing' or 'suspended'

**Causes**:
1. Webhooks not configured or failing
2. PaymentService not updating subscription
3. Webhook signature verification failing

**Solutions**:
```bash
# Check recent webhooks
php artisan tinker
>>> \App\Models\Central\Payment::latest()->take(10)->get(['id', 'status', 'gateway', 'created_at']);

# Check webhook logs
tail -f storage/logs/laravel.log | grep webhook

# Test webhook endpoint
curl -I https://yourdomain.com/webhooks/razorpay

# Manually update subscription status
>>> $subscription = \App\Models\Central\Subscription::find('{id}');
>>> $subscription->status = 'active';
>>> $subscription->trial_ends_at = null;
>>> $subscription->save();
```

**Verify Webhook Configuration**:
- Check webhook URL in gateway dashboard matches deployment URL
- Verify RAZORPAY_WEBHOOK_SECRET or STRIPE_WEBHOOK_SECRET in .env
- Check webhook endpoint returns 200 OK

### Usage Limits Not Enforcing

**Symptoms**: Tenants exceed plan limits without restriction

**Causes**:
1. UsageTrackingService not recording usage
2. Middleware not checking limits
3. Plan limits not defined correctly

**Solutions**:
```bash
# Check usage records
php artisan tinker
>>> $tenant = tenancy()->tenant;
>>> $usage = \App\Models\Central\Subscription::find($tenant->subscription_id)
...     ->usageRecords()
...     ->latest()
...     ->first();
>>> $usage;

# Check plan limits
>>> $subscription = \App\Models\Central\Subscription::find($tenant->subscription_id);
>>> $subscription->plan;

# Manually reset usage
>>> $service = app(\App\Services\UsageTrackingService::class);
>>> $service->resetMonthlyUsage($subscription);
```

**Verify Limits Configuration**:
```sql
-- Check plan limits
SELECT id, name, max_customers, max_policies, max_users FROM plans;

-- Check current usage
SELECT
    t.company_name,
    s.current_customers,
    s.current_policies,
    s.current_users,
    p.max_customers,
    p.max_policies,
    p.max_users
FROM subscriptions s
JOIN tenants t ON s.tenant_id = t.id
JOIN plans p ON s.plan_id = p.id;
```

---

## Payment Gateway Issues

### Payment Failed - Gateway Error

**Symptoms**: Payment shows as 'failed' with gateway error message

**Common Causes**:
1. **Invalid API Keys**: Test/Live key mismatch
2. **Insufficient Funds**: Customer card declined
3. **Gateway Down**: Razorpay/Stripe service outage
4. **3D Secure Failed**: Authentication challenge not completed

**Solutions**:
```bash
# Verify API keys
php artisan tinker
>>> config('services.razorpay.key');
>>> config('services.razorpay.secret');

# Check last error
>>> $payment = \App\Models\Central\Payment::latest()->first();
>>> $payment->error_message;
>>> $payment->gateway_response;

# Test gateway connectivity
php artisan payment:test-gateway razorpay
```

**Gateway-Specific Debugging**:

**Razorpay**:
```bash
# Check Razorpay logs (dashboard → Logs → Payment Logs)
# Common errors:
# - BAD_REQUEST_ERROR: Invalid parameters
# - GATEWAY_ERROR: Bank declined
# - SERVER_ERROR: Razorpay downtime
```

**Stripe**:
```bash
# Check Stripe logs (dashboard → Developers → Logs)
# Common errors:
# - card_declined: Insufficient funds
# - incorrect_cvc: Wrong CVV
# - expired_card: Card expired
```

### Webhook Not Receiving Events

**Symptoms**: Payments complete but subscription not updated

**Diagnostic Steps**:
```bash
# 1. Check webhook endpoint accessibility
curl -I https://yourdomain.com/webhooks/razorpay

# 2. Check webhook logs in gateway dashboard
# Razorpay: Settings → Webhooks → View Logs
# Stripe: Developers → Webhooks → [endpoint] → Attempts

# 3. Check application logs
tail -f storage/logs/laravel.log | grep "Webhook received"

# 4. Verify webhook secret
php artisan tinker
>>> config('services.razorpay.webhook_secret');
```

**Common Issues**:

**1. Signature Verification Failed**:
```bash
# Error: "Webhook signature verification failed"
# Solution: Update webhook secret in .env
RAZORPAY_WEBHOOK_SECRET=whsec_CORRECT_SECRET

# Clear config cache
php artisan config:clear
```

**2. Webhook URL Wrong**:
```bash
# Local development: Use ngrok
ngrok http 8000
# Update webhook URL to: https://YOUR_SUBDOMAIN.ngrok.io/webhooks/razorpay

# Production: Ensure correct domain
# Correct: https://midastech.in/webhooks/razorpay
# Wrong: http://midastech.in/webhooks/razorpay (no HTTPS)
```

**3. Firewall Blocking Webhooks**:
```bash
# Add gateway IPs to whitelist
# Razorpay IPs: 13.232.146.66, 13.234.74.26, 13.234.74.27
# Stripe IPs: 3.18.12.63, 3.130.192.231, 13.235.14.237

# Check server firewall (UFW example)
sudo ufw status
sudo ufw allow from 13.232.146.66
```

### Double Charging

**Symptoms**: Customer charged twice for same transaction

**Causes**:
1. Payment form submitted multiple times
2. Webhook processed duplicate events
3. Race condition in payment processing

**Investigation**:
```bash
php artisan tinker
>>> $payments = \App\Models\Central\Payment::where('tenant_id', '{id}')
...     ->where('created_at', '>', now()->subDay())
...     ->get(['id', 'amount', 'gateway_payment_id', 'status', 'created_at']);
>>> $payments;

# Check for duplicate gateway_payment_ids
>>> $duplicates = \App\Models\Central\Payment::select('gateway_payment_id')
...     ->groupBy('gateway_payment_id')
...     ->havingRaw('COUNT(*) > 1')
...     ->get();
```

**Solutions**:
```bash
# Refund duplicate charge
>>> $payment = \App\Models\Central\Payment::find('{duplicate_id}');
>>> $service = app(\App\Services\PaymentService::class);
>>> $service->refund($payment->id, $payment->amount, 'Duplicate charge');

# Add idempotency check (already implemented in PaymentService)
# Verify gateway_payment_id uniqueness in database
```

### Payment Gateway Timeout

**Symptoms**: "Gateway timeout" or "No response from payment gateway"

**Solutions**:
```bash
# Increase timeout in config/payment.php
'timeout' => env('PAYMENT_GATEWAY_TIMEOUT', 30), // Increase to 60

# Check gateway status pages
# Razorpay: https://status.razorpay.com
# Stripe: https://status.stripe.com

# Implement retry logic (check PaymentService)
>>> $service = app(\App\Services\PaymentService::class);
>>> $service->retryFailedPayment('{payment_id}');
```

---

## Multi-Portal Issues

### Wrong Portal Loading

**Symptoms**: Accessing tenant1.midastech.in shows central admin or wrong tenant

**Causes**:
1. Domain detection failing
2. Middleware order incorrect
3. Tenant identification middleware not working

**Solutions**:
```bash
# Check current domain detection
php artisan tinker
>>> request()->getHost();
>>> tenancy()->tenant;

# Verify middleware order in app/Http/Kernel.php
# InitializeTenancyByDomain should be early in middleware stack

# Check tenant by domain
>>> $tenant = \App\Models\Central\Tenant::where('domain', 'tenant1.midastech.in')->first();
>>> $tenant;
```

**Fix Domain Configuration**:
```bash
# Update tenant domain
php artisan tinker
>>> $tenant = \App\Models\Central\Tenant::find('{id}');
>>> $tenant->domain = 'tenant1.midastech.in';
>>> $tenant->save();

# Verify CENTRAL_DOMAINS in .env
CENTRAL_DOMAINS=midastech.in,www.midastech.in

# Clear route cache
php artisan route:clear
php artisan config:clear
```

### Customer Portal Shows Staff Data

**Symptoms**: Customer can access staff routes or see staff dashboard

**Causes**:
1. Middleware not checking customer guard
2. Routes not properly separated
3. Session guard confusion

**Solutions**:
```bash
# Verify route middleware
php artisan route:list | grep customer

# Expected output should show:
# GET /customer/login ... web,guest:customer
# GET /customer/dashboard ... web,auth:customer

# Check authentication guard
php artisan tinker
>>> auth()->guard('customer')->check();
>>> auth()->guard('web')->check();
```

**Fix Guard Configuration**:
```php
// routes/web.php - ensure customer routes use correct middleware
Route::prefix('customer')->middleware(['web'])->group(function () {
    Route::middleware('guest:customer')->group(function () {
        // Login routes
    });
    Route::middleware('auth:customer')->group(function () {
        // Protected customer routes
    });
});
```

### Portal Authentication Loop

**Symptoms**: Redirects between login pages endlessly

**Causes**:
1. Session cookie domain mismatch
2. Redirector detecting wrong portal
3. Authenticate middleware redirecting incorrectly

**Solutions**:
```bash
# Check session configuration
php artisan tinker
>>> config('session.domain');

# Should be: .midastech.in (with leading dot for subdomains)

# Update .env
SESSION_DOMAIN=.midastech.in

# Clear sessions
php artisan cache:clear
rm storage/framework/sessions/*

# Restart browser to clear cookies
```

**Verify Redirector Logic** (app/Http/Middleware/Authenticate.php):
```php
protected function redirectTo($request)
{
    if (str_contains($request->getHost(), 'customer')) {
        return route('customer.login');
    }
    if (in_array($request->getHost(), config('tenancy.central_domains'))) {
        return route('admin.login');
    }
    return route('login'); // Staff portal
}
```

### Public Website Shows Login Required

**Symptoms**: www.midastech.in redirects to login instead of showing public pages

**Causes**:
1. Public routes wrapped in auth middleware
2. Domain not recognized as public
3. Route registration order incorrect

**Solutions**:
```bash
# Verify public routes don't have auth middleware
php artisan route:list | grep -E "^(GET|POST)\s+/"

# Expected routes without auth middleware:
# GET / ... web
# GET /features ... web
# GET /pricing ... web
# POST /contact ... web

# Check PUBLIC_WEBSITE_DOMAIN
php artisan tinker
>>> config('app.public_website_domain');
```

**Fix Route Registration** (routes/web.php):
```php
// Public website routes (NO auth middleware)
Route::domain(config('app.public_website_domain'))->group(function () {
    Route::get('/', [PublicWebsiteController::class, 'home'])->name('public.home');
    Route::get('/features', [PublicWebsiteController::class, 'features'])->name('public.features');
    // ... other public routes
});
```

### Session Sharing Between Portals

**Symptoms**: Logging into one portal logs you into another

**Causes**:
1. Session cookie name same for all portals
2. SESSION_DOMAIN too broad
3. Guard configuration sharing sessions

**Solutions**:
```env
# Update session configuration
SESSION_DRIVER=database
SESSION_DOMAIN=.midastech.in
SESSION_COOKIE=midas_session

# Each guard should have unique session
# Verify in config/auth.php guards use different providers
```

**Verify Session Isolation**:
```bash
php artisan tinker
>>> session()->getId(); # Should be different for each portal
>>> auth('web')->check();
>>> auth('customer')->check();
>>> auth('admin')->check();
# Only one should be true
```

---

## Quick Commands

```bash
# === Tenant Management ===
# Check all tenants
php artisan tinker → \App\Models\Central\Tenant::all()

# Check specific tenant
php artisan tinker → \App\Models\Central\Tenant::find('{id}')

# Check tenant databases
SHOW DATABASES LIKE 'tenant_%';

# === Subscription Management ===
# Check subscription status
php artisan tinker → \App\Models\Central\Subscription::with('tenant', 'plan')->get()

# Process expired trials
php artisan subscriptions:process-trials

# Check usage tracking
php artisan tinker → \App\Models\Central\UsageRecord::latest()->take(10)->get()

# === Payment Debugging ===
# Check recent payments
php artisan tinker → \App\Models\Central\Payment::latest()->take(10)->get()

# Test payment gateway
php artisan payment:test-gateway razorpay

# View webhook logs
tail -f storage/logs/laravel.log | grep webhook

# === Route Management ===
# Check route list
php artisan route:list | grep central
php artisan route:list | grep customer
php artisan route:list | grep admin

# === Cache Management ===
# Clear everything
php artisan optimize:clear

# Clear specific caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# === Scheduler & Queue ===
# Check scheduled commands
php artisan schedule:list

# Run scheduler manually
php artisan schedule:run

# Check queue workers
php artisan queue:work --once

# === Database ===
# Check migrations status
php artisan migrate:status

# Run tenant migrations
php artisan tenants:migrate --tenants={id}

# Seed tenant data
php artisan tenants:seed --tenants={id}
```

---

## Emergency Procedures

### Tenant Locked Out (Trial Expired)

```bash
# Extend trial by 7 days
php artisan tinker
>>> $subscription = \App\Models\Central\Subscription::where('tenant_id', '{id}')->first();
>>> $subscription->trial_ends_at = now()->addDays(7);
>>> $subscription->status = 'trialing';
>>> $subscription->save();
```

### Payment Gateway Down

```bash
# Switch to backup gateway
# Update .env
RAZORPAY_ENABLED=false
STRIPE_ENABLED=true

php artisan config:clear
```

### All Portals Down

```bash
# Check services
sudo systemctl status nginx php8.2-fpm mysql redis-server

# Restart services
sudo systemctl restart nginx php8.2-fpm

# Check logs
tail -f /var/log/nginx/error.log
tail -f storage/logs/laravel.log
```

### Database Connection Failed

```bash
# Check MySQL
sudo systemctl status mysql

# Test connection
mysql -u midas_user -p

# Check .env database credentials
grep DB_ .env

# Test from PHP
php artisan tinker
>>> DB::connection('central')->getPdo();
```

---

## Related Documentation

- [DEPLOYMENT.md](./DEPLOYMENT.md) - Deployment guide with payment gateway setup
- [MULTI_PORTAL_ARCHITECTURE.md](../core/MULTI_PORTAL_ARCHITECTURE.md) - Multi-portal system architecture
- [SUBSCRIPTION_MANAGEMENT.md](../features/SUBSCRIPTION_MANAGEMENT.md) - Subscription system details
- [PAYMENT_GATEWAY_INTEGRATION.md](../features/PAYMENT_GATEWAY_INTEGRATION.md) - Payment integration guide
- [ENVIRONMENT_CONFIGURATION.md](../setup/ENVIRONMENT_CONFIGURATION.md) - Environment setup

---

**Last Updated**: 2025-01-07
