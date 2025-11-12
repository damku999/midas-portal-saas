<?php

namespace App\Http\Controllers;

use App\Models\Central\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RazorpayTestController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Show test page
     */
    public function index()
    {
        return view('public.razorpay-test');
    }

    /**
     * Create test payment order
     */
    public function createOrder(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:1',
                'description' => 'nullable|string|max:255',
            ]);

            // Create a test payment record
            $payment = Payment::create([
                'tenant_id' => null, // Test payment - no tenant
                'subscription_id' => null, // Test payment - no subscription
                'payment_gateway' => 'razorpay',
                'amount' => $validated['amount'],
                'currency' => 'INR',
                'status' => 'pending',
                'type' => 'subscription', // Test type
                'description' => $validated['description'] ?? 'Test Payment - Razorpay Integration',
                'metadata' => [
                    'test_mode' => true,
                    'test_page' => true,
                ],
            ]);

            // Create Razorpay order
            $api = new \Razorpay\Api\Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $order = $api->order->create([
                'amount' => $payment->amount * 100, // Convert to paise
                'currency' => $payment->currency,
                'receipt' => 'test_receipt_' . $payment->id,
                'notes' => [
                    'payment_id' => $payment->id,
                    'test_mode' => true,
                ],
            ]);

            // Update payment with order ID
            $payment->update([
                'gateway_order_id' => $order->id,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'razorpay_order' => $order->toArray(),
                ]),
            ]);

            Log::info('Test payment order created', [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'amount' => $payment->amount,
            ]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'order_data' => [
                    'order_id' => $order->id,
                    'amount' => $order->amount,
                    'currency' => $order->currency,
                    'key' => config('services.razorpay.key'),
                    'gateway' => 'razorpay',
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Test payment order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify test payment
     */
    public function verifyPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'payment_id' => 'required|integer|exists:payments,id',
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);

            $payment = Payment::findOrFail($validated['payment_id']);

            // Verify signature
            $api = new \Razorpay\Api\Api(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $attributes = [
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
            ];

            $api->utility->verifyPaymentSignature($attributes);

            // Fetch payment details
            $razorpayPayment = $api->payment->fetch($validated['razorpay_payment_id']);

            // Mark payment as completed
            $payment->update([
                'status' => 'completed',
                'gateway_payment_id' => $razorpayPayment->id,
                'paid_at' => now(),
                'gateway_response' => $razorpayPayment->toArray(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'payment_method' => $razorpayPayment->method ?? 'card',
                    'card_last4' => $razorpayPayment->card->last4 ?? null,
                    'card_network' => $razorpayPayment->card->network ?? null,
                    'verified_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::info('Test payment verified successfully', [
                'payment_id' => $payment->id,
                'razorpay_payment_id' => $razorpayPayment->id,
                'amount' => $payment->amount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully!',
                'payment' => $payment,
            ]);

        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            Log::error('Test payment signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Payment signature verification failed. Payment may be tampered.',
            ], 400);

        } catch (\Exception $e) {
            Log::error('Test payment verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment status
     */
    public function paymentStatus($paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);

            return response()->json([
                'success' => true,
                'payment' => $payment,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Payment not found',
            ], 404);
        }
    }

    /**
     * Get recent test payments
     */
    public function recentPayments()
    {
        try {
            // Get last 10 test payments (where tenant_id is null)
            $payments = Payment::whereNull('tenant_id')
                ->whereNull('subscription_id')
                ->latest()
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'payments' => $payments,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
