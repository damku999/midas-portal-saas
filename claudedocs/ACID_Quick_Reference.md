# ACID Quick Reference Guide

Quick reference for implementing ACID transactions in Laravel/MIDAS Portal.

---

## 🚀 **Quick Start Patterns**

### Pattern 1: Automatic Transaction (Recommended)
```php
use Illuminate\Support\Facades\DB;

// Single database
DB::transaction(function () {
    Order::create($data);
    Inventory::decrement('stock', 1);
    Notification::send($customer);
});

// Multi-tenant (central database)
DB::connection('central')->transaction(function () {
    Tenant::create($data);
    Subscription::create($subscriptionData);
});
```

### Pattern 2: Manual Control
```php
DB::beginTransaction();
try {
    $order = Order::create($data);
    $payment = Payment::create($paymentData);

    DB::commit();
    return $order;
} catch (\Exception $e) {
    DB::rollback();
    Log::error('Order creation failed', ['error' => $e->getMessage()]);
    throw $e;
}
```

### Pattern 3: Pessimistic Locking (Financial Operations)
```php
DB::transaction(function () {
    // Lock record to prevent concurrent updates
    $payment = Payment::lockForUpdate()->findOrFail($id);

    // Check status to prevent double-processing
    if ($payment->status !== 'pending') {
        throw new \Exception('Payment already processed');
    }

    // Process payment
    $payment->update(['status' => 'completed']);
});
```

---

## ✅ **When to Use Transactions**

### ✅ **ALWAYS Use Transactions For:**
- 💰 **Financial operations** (payments, refunds, commissions)
- 🔄 **Multi-step CRUD** (create customer + profile + documents)
- 📧 **Database + Notification** (create record + send email/WhatsApp)
- 🏗️ **Resource provisioning** (create tenant + database + subscription)
- 📊 **Aggregate calculations** (update totals + counts + averages)
- 🔗 **Related records** (parent + children in multiple tables)

### ❌ **DON'T Use Transactions For:**
- 📖 Read-only operations (SELECT queries)
- ✏️ Single, simple updates (one table, one record)
- 🕐 Long-running operations (>5 seconds - locks database)
- 📤 External API calls only (no database changes)

---

## 🔐 **Pessimistic Locking**

### When to Use `lockForUpdate()`
```php
// ✅ Financial records (prevent double-processing)
$payment = Payment::lockForUpdate()->find($id);

// ✅ Inventory updates (prevent overselling)
$product = Product::lockForUpdate()->find($id);
$product->decrement('stock', $quantity);

// ✅ Sequence generation (prevent duplicates)
$lastInvoice = Invoice::lockForUpdate()->orderBy('id', 'desc')->first();
$nextNumber = $lastInvoice->number + 1;
```

### ⚠️ **Locking Warnings:**
- Only lock records you're updating (not reads)
- Keep locked transaction duration short (<100ms)
- Always lock in consistent order to prevent deadlocks

---

## 🎯 **Real-World Examples from MIDAS**

### Example 1: Payment Processing
```php
// app/Services/PaymentService.php
public function createOrder(Subscription $subscription, float $amount, string $gateway): array
{
    return DB::connection('central')->transaction(function () use ($subscription, $amount, $gateway) {
        // 1. Create payment record
        $payment = Payment::create([
            'subscription_id' => $subscription->id,
            'amount' => $amount,
            'status' => 'pending',
        ]);

        // 2. Create gateway order (if fails, payment record is rolled back)
        $orderData = $this->createRazorpayOrder($payment);

        // 3. Update payment with gateway order ID
        $payment->update(['gateway_order_id' => $orderData['order_id']]);

        return ['success' => true, 'payment' => $payment];
    });
}
```

### Example 2: Payment Verification (with Locking)
```php
public function verifyPayment(int $paymentId, array $data): array
{
    return DB::connection('central')->transaction(function () use ($paymentId, $data) {
        // Lock payment to prevent concurrent verification
        $payment = Payment::lockForUpdate()->findOrFail($paymentId);

        // Prevent double-processing
        if ($payment->status !== 'pending') {
            return ['success' => false, 'error' => 'Already processed'];
        }

        // Verify with gateway
        $result = $this->verifyRazorpayPayment($payment, $data);

        if ($result['success']) {
            // Update payment + subscription atomically
            $payment->markAsCompleted($result['payment_id']);
            $payment->subscription->update(['status' => 'active']);
        }

        return $result;
    });
}
```

### Example 3: Customer Creation (with Email)
```php
// app/Services/CustomerService.php
public function createCustomer(StoreCustomerRequest $request): Customer
{
    return DB::transaction(function () use ($request) {
        // 1. Create customer
        $customer = Customer::create($request->validated());

        // 2. Upload documents
        $this->handleCustomerDocuments($request, $customer);

        // 3. Send welcome email (if fails, rollback customer creation)
        try {
            $this->sendWelcomeEmailSync($customer);
        } catch (\Exception $e) {
            Log::error('Welcome email failed', ['customer_id' => $customer->id]);
            throw new \Exception('Unable to send welcome email', 0, $e);
        }

        return $customer;
    });
}
```

### Example 4: Claim Stage Update (with Notifications)
```php
// app/Http/Controllers/ClaimController.php
public function addStage(Request $request, Claim $claim): JsonResponse
{
    DB::beginTransaction();
    try {
        // 1. Update current stage
        $claim->stages()->where('is_current', true)->update(['is_current' => false]);

        // 2. Create new stage
        $stage = $claim->stages()->create([
            'stage_name' => $request->stage_name,
            'is_current' => true,
        ]);

        // 3. Send notifications (within transaction)
        if ($request->boolean('send_whatsapp')) {
            $claim->sendStageUpdateWhatsApp($request->stage_name);
        }

        DB::commit();
        return response()->json(['success' => true, 'stage' => $stage]);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Stage update failed', ['claim_id' => $claim->id, 'error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
```

---

## ⚡ **Common Mistakes to Avoid**

### ❌ **Mistake 1: Forgetting to Rollback**
```php
// ❌ BAD
DB::beginTransaction();
try {
    Order::create($data);
    DB::commit();
} catch (\Exception $e) {
    // Missing DB::rollback()!
    return 'error';
}

// ✅ GOOD
DB::beginTransaction();
try {
    Order::create($data);
    DB::commit();
} catch (\Exception $e) {
    DB::rollback(); // Always rollback on exception
    throw $e;
}
```

### ❌ **Mistake 2: Long-Running Transactions**
```php
// ❌ BAD - Locks database for 10 seconds
DB::transaction(function () {
    $users = User::all();
    foreach ($users as $user) {
        $this->sendEmail($user); // 10 seconds of emails
    }
});

// ✅ GOOD - Commit first, then send emails
$users = DB::transaction(function () {
    return User::all();
});

foreach ($users as $user) {
    $this->sendEmail($user); // Outside transaction
}
```

### ❌ **Mistake 3: No Locking on Financial Operations**
```php
// ❌ BAD - Two concurrent requests can process same payment
DB::transaction(function () use ($paymentId) {
    $payment = Payment::find($paymentId); // No lock!
    if ($payment->status === 'pending') {
        $payment->update(['status' => 'completed']);
    }
});

// ✅ GOOD - Lock prevents concurrent processing
DB::transaction(function () use ($paymentId) {
    $payment = Payment::lockForUpdate()->find($paymentId); // Locked!
    if ($payment->status === 'pending') {
        $payment->update(['status' => 'completed']);
    }
});
```

---

## 🧪 **Testing Transactions**

### Test Rollback Behavior
```php
public function test_payment_rollback_on_gateway_failure()
{
    $this->expectException(\Exception::class);

    // Mock gateway to fail
    $this->mock(RazorpayApi::class)
        ->shouldReceive('order->create')
        ->andThrow(new \Exception('Gateway error'));

    $paymentService->createOrder($subscription, 1000, 'razorpay');

    // Assert no payment was created (rolled back)
    $this->assertDatabaseMissing('payments', [
        'subscription_id' => $subscription->id,
    ]);
}
```

### Test Pessimistic Locking
```php
public function test_concurrent_requests_use_locking()
{
    DB::connection()->enableQueryLog();

    $paymentService->verifyPayment($paymentId, $data);

    $queries = DB::getQueryLog();
    $lockQuery = collect($queries)->first(fn($q) =>
        str_contains(strtolower($q['query']), 'for update')
    );

    $this->assertNotNull($lockQuery, 'Should use SELECT FOR UPDATE');
}
```

---

## 📊 **Isolation Levels**

```php
// Default: REPEATABLE READ (recommended for most cases)
DB::transaction(function () {
    // Your operations
});

// SERIALIZABLE: Maximum isolation (for critical financial operations)
DB::transaction(function () {
    // Critical financial operations
}, attempts: 5, isolation: 'SERIALIZABLE');

// READ COMMITTED: Lower isolation (faster, but can have phantom reads)
DB::transaction(function () {
    // Less critical operations
}, attempts: 3, isolation: 'READ COMMITTED');
```

---

## 🔍 **Debugging Transactions**

### Enable Query Logging
```php
DB::connection()->enableQueryLog();

// Your operations

dd(DB::getQueryLog()); // See all queries + transaction commands
```

### Monitor Transaction Duration
```php
$start = microtime(true);

DB::transaction(function () {
    // Your operations
});

$duration = (microtime(true) - $start) * 1000;
Log::info("Transaction duration: {$duration}ms");
```

---

## 📚 **Further Reading**

- **ACID Implementation Guide**: `claudedocs/ACID_Implementation_Guide.md`
- **Implementation Summary**: `claudedocs/ACID_Implementation_Summary.md`
- **Test Suite**: `tests/Feature/ACIDComplianceTest.php`
- **Laravel Docs**: https://laravel.com/docs/10.x/database#database-transactions

---

**Last Updated**: 2025-01-13
**Version**: 1.0
