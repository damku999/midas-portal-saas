# Subscription Management System

**Complete documentation for the multi-tenant subscription and billing system**

---

## Table of Contents

- [Overview](#overview)
- [Subscription Plans](#subscription-plans)
- [Subscription Lifecycle](#subscription-lifecycle)
- [Trial Management](#trial-management)
- [Billing & Payments](#billing--payments)
- [Usage Tracking](#usage-tracking)
- [Plan Limits Enforcement](#plan-limits-enforcement)
- [Subscription States](#subscription-states)
- [Auto-Renewal & Conversion](#auto-renewal--conversion)
- [Upgrade & Downgrade](#upgrade--downgrade)
- [Cancellation & Suspension](#cancellation--suspension)
- [MRR Tracking](#mrr-tracking)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [User Interface](#user-interface)

---

## Overview

The Midas Portal subscription management system provides complete SaaS billing functionality including trial periods, multiple billing intervals, usage-based limits, automated renewals, and payment gateway integration.

### Key Features

- **Flexible Plans**: Multiple subscription tiers with customizable limits
- **Trial Periods**: Configurable trial duration with automated expiration
- **Multi-Interval Billing**: Weekly, monthly, quarterly, half-yearly, yearly
- **Usage Tracking**: Real-time monitoring of users, customers, policies, leads, storage
- **Automated Reminders**: Email notifications at 7/3/1 days before expiration
- **Auto-Conversion**: Automatic trial-to-paid conversion with payment on file
- **Payment Gateways**: Razorpay (active), Stripe (ready)
- **Plan Limits**: Hard limits enforcement for users, customers, storage
- **MRR Tracking**: Monthly Recurring Revenue calculation
- **Grace Periods**: Configurable grace period for payment failures

### System Architecture

```
┌─────────────────┐
│  Tenant Signup  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐      ┌──────────────┐
│  Select Plan    │─────▶│  Plans Table │
└────────┬────────┘      └──────────────┘
         │
         ▼
┌─────────────────┐      ┌────────────────────┐
│  Create Tenant  │─────▶│  Subscriptions     │
└────────┬────────┘      │  Table (Central)   │
         │               └────────────────────┘
         ▼                        │
┌─────────────────┐              │
│  Start Trial    │◀─────────────┘
│  (14 days)      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐      ┌──────────────────┐
│  Usage Tracking │◀────▶│  UsageTracking   │
│  (Real-time)    │      │  Service         │
└────────┬────────┘      └──────────────────┘
         │
         ▼
┌─────────────────┐      ┌──────────────────┐
│  Limit Checks   │◀────▶│  CheckTenantLimits│
│  (Middleware)   │      │  Middleware       │
└────────┬────────┘      └──────────────────┘
         │
         ▼
┌─────────────────┐      ┌──────────────────┐
│  Trial Expires  │─────▶│  Auto-Convert /  │
│  (Day 14)       │      │  Suspend         │
└─────────────────┘      └──────────────────┘
```

---

## Subscription Plans

### Plan Model

**Location**: `app/Models/Central/Plan.php`
**Database**: Central database
**Table**: `plans`

#### Plan Attributes

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string | Plan name (Starter, Professional, Enterprise) |
| `slug` | string | URL-friendly identifier |
| `description` | text | Plan description |
| `price` | decimal(10,2) | Base price |
| `billing_interval` | enum | Billing frequency |
| `features` | json | Feature list array |
| `max_users` | integer | Maximum staff users (-1 = unlimited) |
| `max_customers` | integer | Maximum customers (-1 = unlimited) |
| `max_leads_per_month` | integer | Monthly lead limit (-1 = unlimited) |
| `storage_limit_gb` | integer | Storage quota in GB |
| `is_active` | boolean | Plan availability |
| `sort_order` | integer | Display order |
| `metadata` | json | Additional configuration |

#### Billing Intervals

```php
'week'       => Weekly (price × 52 = annual)
'month'      => Monthly (price × 12 = annual)
'two_month'  => Every 2 Months (price × 6 = annual)
'quarter'    => Quarterly (price × 4 = annual)
'six_month'  => Half-Yearly (price × 2 = annual)
'year'       => Yearly (price × 1 = annual)
```

#### Example Plans

**Starter Plan**:
```php
[
    'name' => 'Starter',
    'slug' => 'starter',
    'price' => 49.00,
    'billing_interval' => 'month',
    'max_users' => 3,
    'max_customers' => 100,
    'max_leads_per_month' => 50,
    'storage_limit_gb' => 1,
    'features' => [
        'Basic CRM',
        'Email Support',
        'Policy Management',
        'Customer Portal',
    ],
]
```

**Professional Plan**:
```php
[
    'name' => 'Professional',
    'slug' => 'professional',
    'price' => 149.00,
    'billing_interval' => 'month',
    'max_users' => 10,
    'max_customers' => 500,
    'max_leads_per_month' => 200,
    'storage_limit_gb' => 5,
    'features' => [
        'Advanced CRM',
        'WhatsApp Integration',
        'Priority Support',
        'Custom Reports',
        'API Access',
    ],
]
```

**Enterprise Plan**:
```php
[
    'name' => 'Enterprise',
    'slug' => 'enterprise',
    'price' => 499.00,
    'billing_interval' => 'month',
    'max_users' => -1,          // Unlimited
    'max_customers' => -1,       // Unlimited
    'max_leads_per_month' => -1, // Unlimited
    'storage_limit_gb' => 50,
    'features' => [
        'Full Platform Access',
        'Dedicated Support',
        'Custom Integrations',
        'Advanced Analytics',
        'White Label Options',
    ],
]
```

#### Plan Methods

```php
// Check unlimited limits
$plan->hasUnlimitedUsers()     // Returns bool
$plan->hasUnlimitedCustomers()  // Returns bool
$plan->hasUnlimitedLeads()      // Returns bool

// Pricing calculations
$plan->formatted_price          // "$149.00"
$plan->billing_interval_label   // "Monthly"
$plan->annual_price             // Annual cost based on billing interval

// Scopes
Plan::active()->ordered()->get()  // Active plans, ordered by sort_order and price
```

---

## Subscription Lifecycle

### Subscription Model

**Location**: `app/Models/Central/Subscription.php`
**Database**: Central database
**Table**: `subscriptions`

#### Subscription Attributes

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `tenant_id` | string | Foreign key to tenants table |
| `plan_id` | bigint | Foreign key to plans table |
| `status` | enum | Subscription status |
| `is_trial` | boolean | Whether currently on trial |
| `trial_ends_at` | timestamp | Trial expiration date |
| `starts_at` | timestamp | Subscription start date |
| `ends_at` | timestamp | Subscription end date |
| `next_billing_date` | timestamp | Next billing attempt |
| `mrr` | decimal(10,2) | Monthly Recurring Revenue |
| `payment_gateway` | string | Gateway name (razorpay/stripe) |
| `gateway_subscription_id` | string | Gateway subscription ID |
| `gateway_customer_id` | string | Gateway customer ID |
| `payment_method` | json | Saved payment method details |
| `auto_renew` | boolean | Auto-renewal enabled |
| `cancelled_at` | timestamp | Cancellation date |
| `cancellation_reason` | text | Reason for cancellation |
| `metadata` | json | Additional data |

#### Status Values

| Status | Description | Can Access System |
|--------|-------------|-------------------|
| `trial` | Active trial period | ✅ Yes |
| `active` | Paid, active subscription | ✅ Yes |
| `past_due` | Payment failed, grace period | ⚠️ Limited (warning shown) |
| `cancelled` | Cancelled by user | ❌ No |
| `suspended` | Suspended by system/admin | ❌ No |
| `expired` | Subscription end date passed | ❌ No |

### Lifecycle States

```
NEW SIGNUP
    │
    ▼
┌────────────┐  Trial Start
│   TRIAL    │◀───────────────┐
│ (14 days)  │                │
└─────┬──────┘                │
      │                       │
      ├─ Payment Method Added │
      │  + Auto-Renew ON      │
      │                       │
      ▼                       │
Trial Expires                 │
      │                       │
      ├─ Auto-Convert ─────── ┘
      │  (if payment method)
      │
      ▼
┌────────────┐
│   ACTIVE   │  Paid Subscription
│ (Recurring)│
└─────┬──────┘
      │
      ├─ Payment Success ──▶ Continue Active
      │
      ├─ Payment Failure ──▶ PAST_DUE (grace period)
      │                           │
      │                           ├─ Payment Retry Success ──▶ ACTIVE
      │                           └─ Grace Period Ends ──▶ SUSPENDED
      │
      ├─ User Cancels ──────▶ CANCELLED
      │
      ├─ Admin Suspends ────▶ SUSPENDED
      │
      └─ Subscription Ends ─▶ EXPIRED
```

---

## Trial Management

### Trial Configuration

**Default Trial Period**: 14 days
**Configurable**: Yes (per subscription)
**Location**: Subscription creation

#### Trial Creation

```php
// During tenant creation
$subscription = Subscription::create([
    'tenant_id' => $tenant->id,
    'plan_id' => $plan->id,
    'status' => 'trial',
    'is_trial' => true,
    'trial_ends_at' => now()->addDays(14),
    'starts_at' => now(),
    'ends_at' => null,
    'mrr' => 0, // No MRR during trial
    'auto_renew' => false,
]);
```

#### Trial Checks

```php
$subscription->onTrial()           // Returns true if trial active
$subscription->trialEnded()        // Returns true if trial expired
$subscription->trialDaysRemaining() // Returns days left in trial
```

### Trial Expiration Process

**Scheduled Command**: `tenants:check-trial-expiration`
**Schedule**: Daily at 2:00 AM

**Process**:
1. Find trials where `trial_ends_at < now()`
2. Check for payment method on file
3. If payment method exists and `auto_renew = true`:
   - Auto-convert to paid subscription
   - Charge payment method
   - Update status to `active`
4. If no payment method or `auto_renew = false`:
   - Update status to `suspended`
   - Send expiration email
5. Log all actions in audit log

```php
// Auto-conversion logic
if ($subscription->is_trial
    && $subscription->trial_ends_at->isPast()
    && $subscription->auto_renew
    && $subscription->payment_method
    && $subscription->payment_gateway) {

    // Calculate new end date
    $endsAt = $this->calculateEndDate($subscription->plan->billing_interval);

    // Update subscription
    $subscription->update([
        'status' => 'active',
        'is_trial' => false,
        'trial_ends_at' => null,
        'ends_at' => $endsAt,
        'next_billing_date' => $endsAt,
        'mrr' => $this->calculateMRR($subscription->plan),
    ]);

    // Process payment
    $this->processPayment($subscription);
}
```

### Trial Reminders

**Scheduled Command**: `subscriptions:process-trials --send-reminders`
**Schedule**: Daily at 8:00 AM

**Reminder Schedule**:
- **7 days before expiration**: First reminder
- **3 days before expiration**: Second reminder
- **1 day before expiration**: Final reminder

```php
$reminderDays = [7, 3, 1];

foreach ($reminderDays as $days) {
    $subscriptions = Subscription::where('is_trial', true)
        ->where('status', 'trial')
        ->whereDate('trial_ends_at', '=', now()->addDays($days))
        ->get();

    foreach ($subscriptions as $subscription) {
        // Send reminder email
        Mail::to($subscription->tenant->data['email'])
            ->send(new TrialExpiringMail($subscription, $days));
    }
}
```

---

## Billing & Payments

### Payment Gateway Integration

**Supported Gateways**:
- **Razorpay**: Active, production-ready
- **Stripe**: Configuration ready, not yet implemented
- **Bank Transfer**: Manual payment option

**Service**: `App\Services\PaymentService`

#### Payment Flow

```
User Selects Plan
    │
    ▼
Create Payment Record
    │
    ▼
Generate Gateway Order
    │ (Razorpay Order ID)
    ▼
Redirect to Payment Page
    │
    ▼
User Completes Payment
    │
    ▼
Gateway Webhook Callback
    │
    ▼
Verify Payment Signature
    │
    ▼
Update Subscription
    │ (status = active)
    ▼
Activate Tenant Access
```

#### Creating Payment Order

```php
$paymentService = app(PaymentService::class);

$result = $paymentService->createOrder(
    subscription: $subscription,
    amount: 149.00,
    gateway: 'razorpay',
    type: 'subscription'
);

// Returns:
[
    'success' => true,
    'payment_id' => 123,
    'order_data' => [
        'order_id' => 'order_xyz123',
        'amount' => 14900, // in paise
        'currency' => 'INR',
        'key' => 'rzp_test_key',
        'gateway' => 'razorpay',
    ],
    'payment' => Payment // model instance
]
```

#### Verifying Payment

```php
$result = $paymentService->verifyPayment($paymentId, [
    'razorpay_order_id' => 'order_xyz123',
    'razorpay_payment_id' => 'pay_abc456',
    'razorpay_signature' => 'signature_hash',
]);

// Returns:
[
    'success' => true,
    'payment_id' => 'pay_abc456',
    'response' => [...], // Gateway response
    'payment_method' => [
        'type' => 'card',
        'card_last4' => '4242',
        'card_network' => 'Visa',
    ]
]
```

#### Webhook Handling

```php
// Route: POST /webhooks/razorpay
$result = $paymentService->handleWebhook('razorpay', $request->all());

// Handles events:
// - payment.success
// - payment.failed
// - subscription.charged
// - subscription.cancelled
```

### Payment Types

| Type | Description | When Used |
|------|-------------|-----------|
| `subscription` | New subscription purchase | Trial conversion, new signup |
| `renewal` | Subscription renewal | Auto-renewal billing |
| `upgrade` | Plan upgrade payment | Upgrading to higher tier |
| `addon` | Additional feature purchase | Extra users, storage |

---

## Usage Tracking

### UsageTrackingService

**Location**: `app/Services/UsageTrackingService.php`
**Purpose**: Real-time monitoring of tenant resource usage

#### Tracked Resources

| Resource | Source | Limit Field | Calculation |
|----------|--------|-------------|-------------|
| **Users** | `users` table | `plan.max_users` | `User::count()` |
| **Customers** | `customers` table | `plan.max_customers` | `Customer::count()` |
| **Policies** | `customer_insurances` table | N/A | `CustomerInsurance::count()` |
| **Leads** | `leads` table | `plan.max_leads_per_month` | Current month count |
| **Storage** | Database + Files | `plan.storage_limit_gb` | DB size + file sizes |

#### Core Methods

```php
// Get complete usage data
$usage = $usageTrackingService->getTenantUsage($tenant);
// Returns: [
//     'users' => 5,
//     'customers' => 150,
//     'policies' => 89,
//     'active_policies' => 75,
//     'leads' => 45,
//     'storage_mb' => 256.45
// ]

// Check if within limits
$usageTrackingService->isWithinLimits($tenant); // Returns bool
$usageTrackingService->isWithinLimits($tenant, 'users'); // Check specific limit

// Check if can create new resource
$usageTrackingService->canCreate('customer'); // Returns bool

// Get usage percentage
$percentage = $usageTrackingService->getUsagePercentage($tenant, 'customers');
// Returns: 75.0 (if 150/200 customers)

// Get remaining capacity
$remaining = $usageTrackingService->getRemainingCapacity($tenant, 'users');
// Returns: 5 (if 5/10 users used)

// Get complete summary
$summary = $usageTrackingService->getUsageSummary($tenant);
```

#### Usage Summary Structure

```php
[
    'usage' => [
        'users' => 5,
        'customers' => 150,
        'policies' => 89,
        'active_policies' => 75,
        'leads' => 45,
        'storage_mb' => 256.45,
    ],
    'limits' => [
        'users' => [
            'current' => 5,
            'max' => 10,
            'percentage' => 50.0,
            'remaining' => 5,
        ],
        'customers' => [
            'current' => 150,
            'max' => 200,
            'percentage' => 75.0,
            'remaining' => 50,
        ],
        'storage' => [
            'current' => 0.25,   // GB
            'max' => 1,          // GB
            'percentage' => 25.0,
            'remaining' => 0.75, // GB
            'unit' => 'GB',
        ],
    ],
    'warnings' => [
        ['type' => 'customers', 'message' => 'Customers usage at 75%', 'severity' => 'warning'],
    ],
    'plan' => 'Professional',
]
```

#### Storage Calculation

**Database Size**:
```sql
SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE();
```

**File Storage**:
- Path: `storage/tenant{id}/app/public/`
- Recursive directory traversal
- Sum of all file sizes in bytes → MB

#### Caching Strategy

```php
// Cache key
$cacheKey = "tenant_usage_{$tenant->id}";

// Cache TTL
$ttl = 5 minutes;

// Invalidation
$usageTrackingService->clearUsageCache($tenant);
$usageTrackingService->trackResourceCreated('customer'); // Auto-clears cache
$usageTrackingService->trackResourceDeleted('user');     // Auto-clears cache
```

---

## Plan Limits Enforcement

### CheckTenantLimits Middleware

**Location**: `app/Http/Middleware/CheckTenantLimits.php`
**Alias**: `tenant.limits`
**Purpose**: Enforce plan limits before resource creation

#### Usage

```php
// Protect user creation route
Route::post('/users', [UserController::class, 'store'])
    ->middleware(['auth', 'tenant.limits:user']);

// Protect customer creation route
Route::post('/customers', [CustomerController::class, 'store'])
    ->middleware(['auth', 'tenant.limits:customer']);

// Protect policy creation route
Route::post('/policies', [PolicyController::class, 'store'])
    ->middleware(['auth', 'tenant.limits:policy']);
```

#### Enforcement Logic

```php
public function handle(Request $request, Closure $next, string $resourceType): Response
{
    $tenant = tenant();
    $subscription = $tenant->subscription;

    // 1. Check subscription exists and active
    if (!$subscription || !$subscription->isActive()) {
        return redirect()->route('subscription.required')
            ->with('error', 'Your subscription is not active.');
    }

    // 2. Check resource limit
    if (!$this->usageService->canCreate($resourceType)) {
        $plan = $subscription->plan;

        $limitMessage = match ($resourceType) {
            'user' => "You have reached the maximum number of users ({$plan->max_users}).",
            'customer' => "You have reached the maximum number of customers ({$plan->max_customers}).",
            default => "You have reached your plan limit for {$resourceType}.",
        };

        return back()
            ->with('error', $limitMessage . ' Please upgrade your plan.')
            ->with('upgrade_required', true);
    }

    return $next($request);
}
```

#### Limit Check Algorithm

```php
public function canCreate(string $resourceType): bool
{
    $tenant = tenant();
    $usage = $this->getTenantUsage($tenant);
    $plan = $tenant->subscription->plan;

    return match ($resourceType) {
        'user' => $plan->hasUnlimitedUsers()
                  || $usage['users'] < $plan->max_users,

        'customer' => $plan->hasUnlimitedCustomers()
                      || $usage['customers'] < $plan->max_customers,

        'lead' => $plan->hasUnlimitedLeads()
                  || $this->getMonthlyLeadCount() < $plan->max_leads_per_month,

        default => true, // No limit
    };
}
```

### Soft Limits vs Hard Limits

**Hard Limits** (Enforced via middleware):
- Users
- Customers
- Leads per month

**Soft Limits** (Warning only):
- Storage (shows warning at 80%, 90%, 95%)
- Policies (no limit, tracked for metrics)

---

## Subscription States

### State Management

```php
// Check subscription state
$subscription->isActive()     // Active or on valid trial
$subscription->onTrial()      // Currently on trial
$subscription->trialEnded()   // Trial has expired
$subscription->hasExpired()   // Subscription end date passed
$subscription->isCancelled()  // User cancelled
$subscription->isSuspended()  // Admin/system suspended
$subscription->isPastDue()    // Payment failed, grace period

// State transitions
$subscription->cancel($reason);    // → cancelled
$subscription->suspend();          // → suspended
$subscription->resume();           // → active (if not cancelled)
```

### Middleware Protection

**CheckSubscriptionStatus Middleware**:
```php
Route::middleware(['subscription.status'])->group(function () {
    // All tenant routes protected
});
```

**Status Checks**:
1. No subscription → Redirect to `subscription.required`
2. Suspended → Redirect to `subscription.suspended`
3. Cancelled → Redirect to `subscription.cancelled`
4. Expired → Redirect to `subscription.plans`
5. Trial ended (no paid) → Redirect to `subscription.plans`

**Exempted Routes**:
- Subscription pages
- Authentication routes
- Logout

---

## Auto-Renewal & Conversion

### Auto-Renewal Configuration

```php
$subscription->update(['auto_renew' => true]);
```

**Requirements for Auto-Renewal**:
1. `auto_renew = true`
2. `payment_method` set (card/UPI details)
3. `payment_gateway` set (razorpay/stripe)
4. `gateway_customer_id` set

### Auto-Conversion Process

**Command**: `subscriptions:process-trials --auto-convert`
**Schedule**: Hourly

**Criteria**:
```php
Subscription::where('is_trial', true)
    ->where('status', 'trial')
    ->where('auto_renew', true)
    ->whereNotNull('payment_method')
    ->whereNotNull('payment_gateway')
    ->where('trial_ends_at', '<=', now())
    ->get();
```

**Conversion Steps**:
1. Calculate new `ends_at` based on billing interval
2. Update subscription:
   ```php
   [
       'status' => 'active',
       'is_trial' => false,
       'trial_ends_at' => null,
       'ends_at' => $calculated_date,
       'next_billing_date' => $calculated_date,
       'mrr' => $calculated_mrr,
   ]
   ```
3. Process payment via gateway
4. Send confirmation email
5. Log conversion event

---

## Upgrade & Downgrade

### Plan Changes

**Upgrade**: Moving to higher-tier plan
**Downgrade**: Moving to lower-tier plan

#### Upgrade Process

```php
// 1. Calculate prorated amount
$currentPlan = $subscription->plan;
$newPlan = Plan::find($newPlanId);

$daysRemaining = now()->diffInDays($subscription->next_billing_date);
$totalDays = now()->diffInDays($subscription->starts_at, $subscription->next_billing_date);

$unusedAmount = ($currentPlan->price / $totalDays) * $daysRemaining;
$proratedAmount = $newPlan->price - $unusedAmount;

// 2. Process payment for prorated amount
$payment = $paymentService->createOrder(
    subscription: $subscription,
    amount: max(0, $proratedAmount),
    gateway: 'razorpay',
    type: 'upgrade'
);

// 3. Update subscription
$subscription->update([
    'plan_id' => $newPlan->id,
    'mrr' => $this->calculateMRR($newPlan),
]);

// 4. Log upgrade event
AuditLog::log('subscription.upgraded', ...);
```

#### Downgrade Process

```php
// Apply at next billing cycle (no immediate change)
$subscription->update([
    'metadata->pending_plan_id' => $downgradePlanId,
    'metadata->downgrade_at_renewal' => true,
]);

// On next renewal, apply downgrade
if ($subscription->metadata['downgrade_at_renewal'] ?? false) {
    $subscription->update([
        'plan_id' => $subscription->metadata['pending_plan_id'],
        'mrr' => $this->calculateMRR($newPlan),
    ]);
}
```

---

## Cancellation & Suspension

### Cancellation

**User-Initiated**:
```php
$subscription->cancel('No longer needed');

// Updates:
[
    'status' => 'cancelled',
    'cancelled_at' => now(),
    'cancellation_reason' => 'No longer needed',
]
```

**Immediate Effect**: Yes, access blocked immediately

**Data Retention**: 30 days (configurable)

### Suspension

**System/Admin-Initiated**:
```php
$subscription->suspend();

// Updates:
['status' => 'suspended']
```

**Reasons for Suspension**:
- Trial expired without payment
- Payment failure after grace period
- Terms of Service violation
- Admin action

**Resume**:
```php
if (!$subscription->isCancelled()) {
    $subscription->resume(); // Returns to 'active'
}
```

---

## MRR Tracking

### Monthly Recurring Revenue Calculation

```php
private function calculateMRR(Plan $plan): float
{
    return match ($plan->billing_interval) {
        'week' => ($plan->price * 52) / 12,      // Weekly to monthly
        'month' => $plan->price,                 // Already monthly
        'two_month' => ($plan->price * 6) / 12,  // Bi-monthly to monthly
        'quarter' => ($plan->price * 4) / 12,    // Quarterly to monthly
        'six_month' => ($plan->price * 2) / 12,  // Half-yearly to monthly
        'year' => $plan->price / 12,             // Yearly to monthly
        default => $plan->price,
    };
}
```

### MRR Reporting

```php
// Total MRR across all active subscriptions
$totalMRR = Subscription::active()->sum('mrr');

// MRR by plan
$mrrByPlan = Subscription::active()
    ->selectRaw('plan_id, SUM(mrr) as total_mrr')
    ->groupBy('plan_id')
    ->with('plan')
    ->get();

// MRR growth (month-over-month)
$currentMonthMRR = Subscription::active()->sum('mrr');
$lastMonthMRR = Subscription::active()
    ->where('created_at', '<', now()->startOfMonth())
    ->sum('mrr');
$growth = (($currentMonthMRR - $lastMonthMRR) / $lastMonthMRR) * 100;
```

---

## Database Schema

### Plans Table

```sql
CREATE TABLE plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    billing_interval ENUM('week', 'month', 'two_month', 'quarter', 'six_month', 'year') DEFAULT 'month',
    features JSON,
    max_users INT DEFAULT 1,                    -- -1 = unlimited
    max_customers INT DEFAULT 100,              -- -1 = unlimited
    max_leads_per_month INT DEFAULT 50,         -- -1 = unlimited
    storage_limit_gb INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    metadata JSON,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);
```

### Subscriptions Table

```sql
CREATE TABLE subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(255) NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    status ENUM('trial', 'active', 'past_due', 'cancelled', 'suspended', 'expired') DEFAULT 'trial',
    is_trial BOOLEAN DEFAULT TRUE,
    trial_ends_at TIMESTAMP NULL,
    starts_at TIMESTAMP NULL,
    ends_at TIMESTAMP NULL,
    next_billing_date TIMESTAMP NULL,
    mrr DECIMAL(10,2) DEFAULT 0.00,
    payment_gateway VARCHAR(255) NULL,
    gateway_subscription_id VARCHAR(255) NULL,
    gateway_customer_id VARCHAR(255) NULL,
    payment_method JSON NULL,
    auto_renew BOOLEAN DEFAULT FALSE,
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT NULL,
    metadata JSON,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE RESTRICT,

    INDEX idx_tenant_id (tenant_id),
    INDEX idx_plan_id (plan_id),
    INDEX idx_status (status),
    INDEX idx_trial_ends_at (trial_ends_at),
    INDEX idx_next_billing_date (next_billing_date)
);
```

---

## API Endpoints

### Public Routes (Central Domain)

```php
GET  /pricing               // View available plans
POST /contact               // Contact sales
```

### Subscription Routes (Tenant Domain)

```php
GET  /subscription          // View current subscription
GET  /subscription/plans    // Available upgrade plans
POST /subscription/upgrade  // Upgrade to new plan
POST /subscription/cancel   // Cancel subscription
POST /subscription/resume   // Resume cancelled subscription

// Payment
POST /subscription/payment/create   // Create payment order
POST /subscription/payment/verify   // Verify payment completion
```

### Central Admin Routes

```php
GET    /midas-admin/plans              // List all plans
POST   /midas-admin/plans              // Create plan
PUT    /midas-admin/plans/{id}         // Update plan
DELETE /midas-admin/plans/{id}         // Delete plan

GET    /midas-admin/subscriptions      // List all subscriptions
GET    /midas-admin/subscriptions/{id} // View subscription
PUT    /midas-admin/subscriptions/{id} // Update subscription
POST   /midas-admin/subscriptions/{id}/suspend   // Suspend
POST   /midas-admin/subscriptions/{id}/resume    // Resume
```

---

## User Interface

### Plan Selection Page

**Location**: `resources/views/subscription/plans.blade.php`
**Route**: `/subscription/plans`

**Features**:
- Side-by-side plan comparison
- Feature checklist per plan
- Monthly/Annual toggle (if multiple intervals)
- Upgrade/Downgrade indicators
- Trial period highlighted

### Current Subscription Dashboard

**Location**: `resources/views/subscription/index.blade.php`
**Route**: `/subscription`

**Displays**:
- Current plan name and price
- Billing interval
- Next billing date
- Usage metrics with progress bars
- Upgrade/Cancel buttons
- Payment method on file

### Usage Widget

**Component**: Displayed on tenant dashboard

**Shows**:
```
Users: ████████░░ 8/10 (80%)
Customers: ███████░░░ 150/200 (75%)
Storage: ██░░░░░░░░ 250MB/1GB (24%)
```

### Upgrade Required Modal

**Triggers**: When limit reached
**Shows**:
- Current usage vs limit
- Recommended plan
- "Upgrade Now" CTA

---

## Related Documentation

- [Payment Gateway Integration](PAYMENT_GATEWAY_INTEGRATION.md) - Razorpay/Stripe setup
- [Trial Conversion System](TRIAL_CONVERSION_SYSTEM.md) - Trial automation details
- [Service Layer](../core/SERVICE_LAYER.md) - PaymentService, UsageTrackingService
- [Middleware Reference](../core/MIDDLEWARE_REFERENCE.md) - subscription.status, tenant.limits
- [Artisan Commands](../core/ARTISAN_COMMANDS.md) - Trial processing commands
- [Database Schema](../core/DATABASE_SCHEMA.md) - Full schema documentation

---

**Last Updated**: 2025-11-06
**Status**: Production-ready with Razorpay integration
**Coverage**: 100% of subscription management features
