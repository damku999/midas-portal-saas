<?php

namespace App\Http\Controllers;

use App\Models\Central\Payment;
use App\Models\Central\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Handle Razorpay webhook.
     */
    public function razorpay(Request $request)
    {
        try {
            // Get webhook signature
            $webhookSignature = $request->header('X-Razorpay-Signature');
            $webhookSecret = config('services.razorpay.webhook_secret');
            $webhookBody = $request->getContent();

            // Verify webhook signature
            if (!$this->verifyRazorpaySignature($webhookBody, $webhookSignature, $webhookSecret)) {
                Log::warning('Invalid Razorpay webhook signature', [
                    'ip' => $request->ip(),
                    'signature' => $webhookSignature,
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Invalid signature',
                ], 401);
            }

            $payload = $request->all();
            $event = $payload['event'] ?? null;

            Log::info('Razorpay webhook received', [
                'event' => $event,
                'payload' => $payload,
            ]);

            // Handle different webhook events
            switch ($event) {
                case 'payment.captured':
                    $this->handlePaymentCaptured($payload);
                    break;

                case 'payment.failed':
                    $this->handlePaymentFailed($payload);
                    break;

                case 'payment.authorized':
                    $this->handlePaymentAuthorized($payload);
                    break;

                case 'order.paid':
                    $this->handleOrderPaid($payload);
                    break;

                case 'refund.created':
                    $this->handleRefundCreated($payload);
                    break;

                default:
                    Log::info('Unhandled Razorpay webhook event', ['event' => $event]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Razorpay webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Webhook processing failed',
            ], 500);
        }
    }

    /**
     * Handle Stripe webhook.
     */
    public function stripe(Request $request)
    {
        try {
            $webhookSecret = config('services.stripe.webhook_secret');
            $signature = $request->header('Stripe-Signature');
            $payload = $request->getContent();

            // TODO: Verify Stripe signature
            // \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);

            $event = json_decode($payload, true);
            $eventType = $event['type'] ?? null;

            Log::info('Stripe webhook received', [
                'event' => $eventType,
                'payload' => $event,
            ]);

            // Handle different webhook events
            switch ($eventType) {
                case 'payment_intent.succeeded':
                    // Handle successful payment
                    break;

                case 'payment_intent.payment_failed':
                    // Handle failed payment
                    break;

                case 'customer.subscription.created':
                    // Handle subscription created
                    break;

                case 'customer.subscription.deleted':
                    // Handle subscription cancelled
                    break;

                default:
                    Log::info('Unhandled Stripe webhook event', ['event' => $eventType]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Webhook processing failed',
            ], 500);
        }
    }

    /**
     * Verify Razorpay webhook signature.
     */
    private function verifyRazorpaySignature(string $body, string $signature, string $secret): bool
    {
        $expectedSignature = hash_hmac('sha256', $body, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Handle payment.captured event.
     */
    private function handlePaymentCaptured(array $payload): void
    {
        $paymentData = $payload['payload']['payment']['entity'] ?? [];
        $paymentId = $paymentData['notes']['payment_id'] ?? null;
        $razorpayPaymentId = $paymentData['id'] ?? null;

        if (!$paymentId || !$razorpayPaymentId) {
            Log::warning('Payment captured webhook missing payment_id or razorpay_payment_id', [
                'payload' => $payload,
            ]);
            return;
        }

        $payment = Payment::find($paymentId);

        if (!$payment) {
            Log::warning('Payment not found for captured webhook', [
                'payment_id' => $paymentId,
            ]);
            return;
        }

        // Only update if payment is still pending
        if ($payment->isPending()) {
            $payment->markAsCompleted($razorpayPaymentId, $paymentData);

            // Update subscription status
            $subscription = $payment->subscription;
            $subscription->update([
                'status' => 'active',
                'is_trial' => false,
                'trial_ends_at' => null,
            ]);

            Log::info('Payment captured and subscription activated', [
                'payment_id' => $payment->id,
                'subscription_id' => $subscription->id,
                'razorpay_payment_id' => $razorpayPaymentId,
            ]);
        }
    }

    /**
     * Handle payment.failed event.
     */
    private function handlePaymentFailed(array $payload): void
    {
        $paymentData = $payload['payload']['payment']['entity'] ?? [];
        $paymentId = $paymentData['notes']['payment_id'] ?? null;
        $errorDescription = $paymentData['error_description'] ?? 'Payment failed';

        if (!$paymentId) {
            Log::warning('Payment failed webhook missing payment_id', [
                'payload' => $payload,
            ]);
            return;
        }

        $payment = Payment::find($paymentId);

        if (!$payment) {
            Log::warning('Payment not found for failed webhook', [
                'payment_id' => $paymentId,
            ]);
            return;
        }

        // Mark payment as failed
        $payment->markAsFailed($errorDescription, $paymentData);

        Log::info('Payment marked as failed', [
            'payment_id' => $payment->id,
            'reason' => $errorDescription,
        ]);

        // TODO: Send email notification to user about failed payment
        // TODO: If auto-renewal, schedule retry
    }

    /**
     * Handle payment.authorized event.
     */
    private function handlePaymentAuthorized(array $payload): void
    {
        $paymentData = $payload['payload']['payment']['entity'] ?? [];
        $paymentId = $paymentData['notes']['payment_id'] ?? null;

        if (!$paymentId) {
            return;
        }

        $payment = Payment::find($paymentId);

        if ($payment && $payment->isPending()) {
            $payment->update(['status' => 'processing']);

            Log::info('Payment authorized', [
                'payment_id' => $payment->id,
            ]);
        }
    }

    /**
     * Handle order.paid event.
     */
    private function handleOrderPaid(array $payload): void
    {
        $orderData = $payload['payload']['order']['entity'] ?? [];
        $orderId = $orderData['id'] ?? null;

        if (!$orderId) {
            return;
        }

        $payment = Payment::where('gateway_order_id', $orderId)->first();

        if ($payment && $payment->isPending()) {
            Log::info('Order paid', [
                'payment_id' => $payment->id,
                'order_id' => $orderId,
            ]);

            // Payment will be marked as completed by payment.captured event
        }
    }

    /**
     * Handle refund.created event.
     */
    private function handleRefundCreated(array $payload): void
    {
        $refundData = $payload['payload']['refund']['entity'] ?? [];
        $paymentId = $refundData['payment_id'] ?? null;

        if (!$paymentId) {
            return;
        }

        $payment = Payment::where('gateway_payment_id', $paymentId)->first();

        if ($payment) {
            $payment->markAsRefunded($refundData);

            Log::info('Refund processed', [
                'payment_id' => $payment->id,
                'refund_amount' => $refundData['amount'] ?? 0,
            ]);

            // TODO: Update subscription status if needed
            // TODO: Send email notification about refund
        }
    }
}
