# ACID Implementation Guide - MIDAS Portal

## Overview

This document outlines ACID (Atomicity, Consistency, Isolation, Durability) transaction implementation across the MIDAS insurance management platform.

---

## ACID Properties Explained

### **Atomicity** - All or Nothing
Transactions either complete fully or rollback completely. No partial updates.

### **Consistency** - Valid State Transitions
Database moves from one valid state to another. All constraints maintained.

### **Isolation** - Concurrent Transaction Safety
Multiple transactions don't interfere. Prevents dirty reads, phantom reads.

### **Durability** - Permanent Once Committed
Committed data survives crashes and power failures.

---

## Laravel Transaction Patterns

### Pattern 1: Automatic Transactions (Recommended)
```php
DB::transaction(function () {
    Order::create($data);
    Inventory::decrement('stock', 1);
    // Auto-commits on success, auto-rollbacks on exception
});
```

### Pattern 2: Manual Control
```php
DB::beginTransaction();
try {
    Order::create($data);
    Payment::create($paymentData);
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}
```

### Pattern 3: Multi-Database Transactions
```php
DB::connection('central')->transaction(function () {
    Tenant::create($data);
    Subscription::create($subscriptionData);
});
```

---

## Implementation Status

### ✅ **Already Implemented (ACID-Compliant)**

#### 1. TenantCreationService
**Location:** `app/Services/TenantCreationService.php:41-355`
**Pattern:** Manual control with rollback
**Operations:**
- Subdomain validation
- Tenant record creation
- Domain registration
- Subscription setup
- Database creation
- Migrations & seeding
- Admin user creation

**Rollback Coverage:**
- ✅ Central DB records (tenant, subscription, domains)
- ✅ Physical database deletion on failure
- ✅ Uploaded files cleanup

---

#### 2. LeadConversionService
**Location:** `app/Services/LeadConversionService.php:29-105`
**Pattern:** Manual transaction
**Operations:**
- Lead validation
- Customer creation/linking
- Lead status update
- Activity logging
- Document copying

**Atomic Guarantee:** All operations succeed or all fail together

---

#### 3. SubscriptionController (Plan Upgrades)
**Location:** `app/Http/Controllers/SubscriptionController.php:108-169`
**Pattern:** Manual central DB transaction
**Operations:**
- Payment order creation
- Subscription plan upgrade
- Billing calculations

**Connection:** Uses `DB::connection('central')` for multi-tenant architecture

---

#### 4. CustomerService
**Location:** `app/Services/CustomerService.php:54-121`
**Pattern:** BaseService transaction helpers
**Methods Used:**
- `createInTransaction()` - Customer creation with documents & emails
- `updateInTransaction()` - Customer updates with event dispatching
- `deleteInTransaction()` - Safe customer deletion

**Special Handling:**
- Welcome email sent synchronously within transaction
- Email failure triggers complete rollback
- Document uploads handled atomically

---

### ⚠️ **Needs Implementation**

#### 5. PaymentService (CRITICAL - Financial Operations)
**Location:** `app/Services/PaymentService.php`
**Missing Transactions:**
- ❌ `createOrder()` - Payment record + gateway order creation
- ❌ `verifyPayment()` - Payment verification + subscription update

**Risk:** Partial payments, inconsistent payment states, subscription mismatches

---

#### 6. ClaimController (Multi-Step Operations)
**Location:** `app/Http/Controllers/ClaimController.php`
**Missing Transactions:**
- ❌ `addStage()` - Stage creation + WhatsApp + email notifications
- ❌ `updateClaimNumber()` - Claim update + notifications
- ❌ `updateLiabilityDetails()` - Financial calculations

**Risk:** Inconsistent claim states, missing notifications

---

## Implementation Plan

### Phase 1: Critical Financial Operations (PaymentService)
```php
// app/Services/PaymentService.php

public function createOrder(Subscription $subscription, float $amount, string $gateway, string $type = 'subscription'): array
{
    return DB::connection('central')->transaction(function () use ($subscription, $amount, $gateway, $type) {
        // Create payment record
        $payment = Payment::create([...]);

        // Create gateway order
        $orderData = match ($gateway) {
            'razorpay' => $this->createRazorpayOrder($payment),
            // ...
        };

        // Update payment with order ID
        $payment->update([
            'gateway_order_id' => $orderData['order_id'],
            'metadata' => $orderData,
        ]);

        return [
            'success' => true,
            'payment' => $payment,
            'order_data' => $orderData,
        ];
    });
}

public function verifyPayment(int $paymentId, array $paymentData): array
{
    return DB::connection('central')->transaction(function () use ($paymentId, $paymentData) {
        $payment = Payment::lockForUpdate()->findOrFail($paymentId);

        // Verify with gateway
        $result = match ($payment->payment_gateway) {
            'razorpay' => $this->verifyRazorpayPayment($payment, $paymentData),
            // ...
        };

        if ($result['success']) {
            // Mark payment complete
            $payment->markAsCompleted($result['payment_id'], $result['response']);

            // Update subscription
            $subscription = $payment->subscription;
            $subscription->update([
                'payment_gateway' => $payment->payment_gateway,
                'gateway_subscription_id' => $result['payment_id'],
                'status' => 'active',
            ]);
        } else {
            $payment->markAsFailed($result['error'], $result['response'] ?? []);
        }

        return $result;
    });
}
```

---

### Phase 2: Claim Operations
```php
// app/Http/Controllers/ClaimController.php

public function addStage(Request $request, Claim $claim): JsonResponse
{
    DB::beginTransaction();
    try {
        $request->validate([...]);

        // Update current stage
        $claim->stages()->where('is_current', true)->update(['is_current' => false]);

        // Create new stage
        $stage = $claim->stages()->create([...]);

        // Send notifications (within transaction)
        if ($request->boolean('send_whatsapp')) {
            $whatsappResult = $claim->sendStageUpdateWhatsApp(...);
        }
        $claim->sendStageUpdateNotification(...);

        DB::commit();

        return response()->json(['success' => true, 'stage' => $stage]);

    } catch (\Throwable $e) {
        DB::rollback();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

public function updateClaimNumber(Request $request, Claim $claim): JsonResponse
{
    DB::beginTransaction();
    try {
        $request->validate([...]);

        // Update claim
        $claim->update(['claim_number' => $request->claim_number]);

        // Send notifications
        if ($request->boolean('send_whatsapp')) {
            $whatsappResult = $claim->sendClaimNumberWhatsApp();
        }
        $claim->sendClaimNumberAssignedNotification();

        DB::commit();

        return response()->json(['success' => true]);

    } catch (\Throwable $e) {
        DB::rollback();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

public function updateLiabilityDetails(Request $request, Claim $claim): JsonResponse
{
    DB::beginTransaction();
    try {
        $request->validate([...]);

        $liabilityDetail = $claim->liabilityDetail ?: $claim->liabilityDetail()->create([]);
        $liabilityDetail->update($request->all());

        DB::commit();

        return response()->json(['success' => true, 'liability_detail' => $liabilityDetail->fresh()]);

    } catch (\Throwable $e) {
        DB::rollback();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
```

---

## Database Isolation Levels

### MySQL (InnoDB) - Default: REPEATABLE READ
```php
DB::transaction(function () {
    // Your operations
}, attempts: 5, isolation: 'SERIALIZABLE');
```

**Available Levels:**
- `READ UNCOMMITTED` - Can see uncommitted changes (avoid)
- `READ COMMITTED` - Only sees committed data
- `REPEATABLE READ` - Same reads return same results (default)
- `SERIALIZABLE` - Full isolation (use for financial operations)

---

## Best Practices

### ✅ DO:
- Use transactions for multi-step operations
- Use `lockForUpdate()` for financial records
- Log errors before rollback
- Clean up external resources (files, API calls) on rollback
- Use `DB::connection('central')` for multi-tenant operations
- Use specific isolation levels for critical operations

### ❌ DON'T:
- Commit files/emails within transactions without rollback handling
- Use transactions for single, simple queries
- Use transactions for read-only operations
- Keep transactions open for long durations (locks database rows)
- Skip error logging before throwing exceptions

---

## Testing ACID Properties

### Test 1: Atomicity (All or Nothing)
```php
// Test that partial failures rollback completely
public function test_payment_creation_rollback_on_gateway_failure()
{
    $this->expectException(\Exception::class);

    // Mock gateway to fail
    $this->mock(RazorpayApi::class)->shouldReceive('order->create')->andThrow(new \Exception('Gateway error'));

    $paymentService = app(PaymentService::class);
    $paymentService->createOrder($subscription, 1000, 'razorpay');

    // Assert no payment record was created
    $this->assertDatabaseMissing('payments', [
        'subscription_id' => $subscription->id,
    ]);
}
```

### Test 2: Consistency (Valid States)
```php
public function test_subscription_upgrade_maintains_consistency()
{
    $subscription = Subscription::factory()->create(['status' => 'active']);

    // Attempt upgrade
    $this->paymentService->createOrder($subscription, 2000, 'razorpay', 'upgrade');

    // Subscription should still be in valid state
    $this->assertContains($subscription->fresh()->status, ['active', 'pending_payment']);
}
```

### Test 3: Isolation (Concurrent Safety)
```php
public function test_concurrent_payment_verification_prevents_double_processing()
{
    $payment = Payment::factory()->create(['status' => 'pending']);

    // Simulate concurrent requests
    DB::connection()->enableQueryLog();

    $result1 = $this->paymentService->verifyPayment($payment->id, ['razorpay_payment_id' => 'pay_123']);
    $result2 = $this->paymentService->verifyPayment($payment->id, ['razorpay_payment_id' => 'pay_123']);

    // Only one should succeed
    $queries = DB::getQueryLog();
    $this->assertContains('for update', $queries); // Verify lock was used
}
```

---

## Monitoring & Debugging

### Enable Query Logging
```php
DB::connection()->enableQueryLog();
// ... operations ...
dd(DB::getQueryLog());
```

### Transaction Event Listeners
```php
// app/Providers/EventServiceProvider.php
DB::listen(function ($query) {
    if (str_contains($query->sql, 'BEGIN')) {
        Log::debug('Transaction started', ['connection' => $query->connectionName]);
    }
});
```

---

## Migration Checklist

- [x] TenantCreationService - Already ACID-compliant
- [x] LeadConversionService - Already ACID-compliant
- [x] SubscriptionController - Already ACID-compliant
- [x] CustomerService - Already ACID-compliant
- [ ] PaymentService - Needs implementation (CRITICAL)
- [ ] ClaimController - Needs implementation
- [ ] Create unit tests for ACID properties
- [ ] Document rollback procedures
- [ ] Set up transaction monitoring

---

## References

- Laravel Transactions: https://laravel.com/docs/10.x/database#database-transactions
- MySQL Isolation Levels: https://dev.mysql.com/doc/refman/8.0/en/innodb-transaction-isolation-levels.html
- ACID Properties: https://en.wikipedia.org/wiki/ACID

---

**Last Updated:** 2025-01-13
**Version:** 1.0
**Maintainer:** Development Team
