<?php

namespace Tests\Feature;

use App\Models\Central\Payment;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Razorpay\Api\Api as RazorpayApi;
use Tests\TestCase;

/**
 * ACID Compliance Test Suite
 *
 * Tests ACID (Atomicity, Consistency, Isolation, Durability) properties
 * across critical financial and multi-step operations in the MIDAS portal.
 */
class ACIDComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = app(PaymentService::class);
    }

    /**
     * Test 1: Atomicity - Payment creation rollback on gateway failure
     *
     * Verifies that if the payment gateway fails, the entire transaction
     * is rolled back and no payment record is created in the database.
     */
    public function test_payment_creation_rollback_on_gateway_failure(): void
    {
        // Arrange: Create test subscription
        $plan = Plan::factory()->create(['price' => 1000]);
        $tenant = Tenant::factory()->create();
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);

        // Mock Razorpay to throw exception
        $this->mock(RazorpayApi::class, function ($mock) {
            $mock->shouldReceive('order->create')
                ->andThrow(new \Exception('Gateway service unavailable'));
        });

        // Act & Assert
        $result = $this->paymentService->createOrder($subscription, 1000, 'razorpay');

        // Verify failure result
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);

        // Verify no payment record was created (ATOMICITY)
        $this->assertDatabaseMissing('payments', [
            'subscription_id' => $subscription->id,
            'amount' => 1000,
        ]);
    }

    /**
     * Test 2: Atomicity - Payment verification updates both payment and subscription
     *
     * Verifies that payment verification and subscription update happen atomically.
     * Both should succeed or both should fail together.
     */
    public function test_payment_verification_updates_both_payment_and_subscription(): void
    {
        // Arrange: Create test data
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create();
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'trial',
        ]);
        $payment = Payment::factory()->create([
            'subscription_id' => $subscription->id,
            'tenant_id' => $tenant->id,
            'status' => 'pending',
            'payment_gateway' => 'razorpay',
        ]);

        // Mock successful Razorpay verification
        $this->mock(RazorpayApi::class, function ($mock) {
            $mock->shouldReceive('utility->verifyPaymentSignature')->andReturn(true);
            $mock->shouldReceive('payment->fetch')->andReturn((object)[
                'id' => 'pay_123',
                'method' => 'card',
                'status' => 'captured',
                'toArray' => fn() => ['id' => 'pay_123', 'status' => 'captured'],
            ]);
        });

        // Act
        $result = $this->paymentService->verifyPayment($payment->id, [
            'razorpay_payment_id' => 'pay_123',
            'razorpay_order_id' => 'order_123',
            'razorpay_signature' => 'signature_123',
        ]);

        // Assert: Both payment and subscription updated (ATOMICITY)
        $this->assertTrue($result['success']);

        $payment->refresh();
        $subscription->refresh();

        $this->assertEquals('completed', $payment->status);
        $this->assertEquals('pay_123', $subscription->gateway_subscription_id);
        $this->assertEquals('razorpay', $subscription->payment_gateway);
    }

    /**
     * Test 3: Consistency - Subscription remains in valid state after failed upgrade
     *
     * Verifies that failed operations don't leave the database in invalid states.
     */
    public function test_subscription_upgrade_maintains_consistency_on_failure(): void
    {
        // Arrange
        $plan = Plan::factory()->create(['price' => 1000]);
        $tenant = Tenant::factory()->create();
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);

        $originalStatus = $subscription->status;
        $originalPlanId = $subscription->plan_id;

        // Mock gateway failure
        $this->mock(RazorpayApi::class, function ($mock) {
            $mock->shouldReceive('order->create')
                ->andThrow(new \Exception('Payment gateway timeout'));
        });

        // Act
        $newPlan = Plan::factory()->create(['price' => 2000]);
        $result = $this->paymentService->createOrder($subscription, 2000, 'razorpay', 'upgrade');

        // Assert: Subscription maintains original valid state (CONSISTENCY)
        $subscription->refresh();
        $this->assertEquals($originalStatus, $subscription->status);
        $this->assertEquals($originalPlanId, $subscription->plan_id);
    }

    /**
     * Test 4: Isolation - Concurrent payment verification prevented by locking
     *
     * Verifies that pessimistic locking prevents double-processing of payments
     * from concurrent requests.
     */
    public function test_concurrent_payment_verification_uses_pessimistic_locking(): void
    {
        // Arrange
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create();
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);
        $payment = Payment::factory()->create([
            'subscription_id' => $subscription->id,
            'tenant_id' => $tenant->id,
            'status' => 'pending',
        ]);

        // Enable query logging to verify locking
        DB::connection('central')->enableQueryLog();

        // Mock successful verification
        $this->mock(RazorpayApi::class, function ($mock) {
            $mock->shouldReceive('utility->verifyPaymentSignature')->andReturn(true);
            $mock->shouldReceive('payment->fetch')->andReturn((object)[
                'id' => 'pay_123',
                'method' => 'card',
                'toArray' => fn() => ['id' => 'pay_123'],
            ]);
        });

        // Act
        $this->paymentService->verifyPayment($payment->id, [
            'razorpay_payment_id' => 'pay_123',
            'razorpay_order_id' => 'order_123',
            'razorpay_signature' => 'signature_123',
        ]);

        // Assert: Verify that SELECT FOR UPDATE was used (ISOLATION)
        $queries = DB::connection('central')->getQueryLog();
        $lockQuery = collect($queries)->first(function ($query) {
            return str_contains(strtolower($query['query']), 'for update');
        });

        $this->assertNotNull($lockQuery, 'Payment verification should use SELECT FOR UPDATE');
    }

    /**
     * Test 5: Isolation - Already processed payment cannot be re-verified
     *
     * Verifies that the status check prevents double-processing.
     */
    public function test_already_processed_payment_cannot_be_reverified(): void
    {
        // Arrange: Create completed payment
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create();
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);
        $payment = Payment::factory()->create([
            'subscription_id' => $subscription->id,
            'tenant_id' => $tenant->id,
            'status' => 'completed', // Already processed
            'gateway_payment_id' => 'pay_original',
        ]);

        // Act: Attempt to verify again
        $result = $this->paymentService->verifyPayment($payment->id, [
            'razorpay_payment_id' => 'pay_duplicate',
            'razorpay_order_id' => 'order_123',
            'razorpay_signature' => 'signature_123',
        ]);

        // Assert: Verification rejected (ISOLATION)
        $this->assertFalse($result['success']);
        $this->assertEquals('Payment has already been processed', $result['error']);

        // Verify original payment ID wasn't changed
        $payment->refresh();
        $this->assertEquals('pay_original', $payment->gateway_payment_id);
    }

    /**
     * Test 6: Durability - Committed payments persist after transaction
     *
     * Verifies that once a transaction commits, the data is permanently stored.
     */
    public function test_committed_payment_persists_in_database(): void
    {
        // Arrange
        $plan = Plan::factory()->create();
        $tenant = Tenant::factory()->create();
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
        ]);

        // Mock successful Razorpay order creation
        $this->mock(RazorpayApi::class, function ($mock) {
            $mock->shouldReceive('order->create')->andReturn((object)[
                'id' => 'order_123',
                'amount' => 100000,
                'currency' => 'INR',
            ]);
        });

        // Act: Create payment
        $result = $this->paymentService->createOrder($subscription, 1000, 'razorpay');

        // Assert: Payment persists (DURABILITY)
        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('payments', [
            'id' => $result['payment_id'],
            'subscription_id' => $subscription->id,
            'amount' => 1000,
            'gateway_order_id' => 'order_123',
            'status' => 'pending',
        ]);

        // Verify payment can be retrieved after transaction
        $payment = Payment::find($result['payment_id']);
        $this->assertNotNull($payment);
        $this->assertEquals('order_123', $payment->gateway_order_id);
    }

    /**
     * Test 7: Multi-step atomicity - Claim stage update with notifications
     *
     * Verifies that claim stage updates and notifications happen atomically.
     */
    public function test_claim_stage_update_with_notifications_is_atomic(): void
    {
        // This test would require setting up claim data and mocking WhatsApp/Email services
        // Included as documentation of what should be tested
        $this->markTestIncomplete('Claim stage update atomicity test requires claim setup');
    }
}
