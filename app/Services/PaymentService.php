<?php

namespace App\Services;

use App\Models\Central\Payment;
use App\Models\Central\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api as RazorpayApi;

class PaymentService
{
    /**
     * Create a payment order.
     *
     * ACID-compliant: Uses database transaction to ensure payment record
     * and gateway order creation are atomic. If gateway fails, payment
     * record is rolled back.
     */
    public function createOrder(Subscription $subscription, float $amount, string $gateway, string $type = 'subscription'): array
    {
        try {
            return DB::connection('central')->transaction(function () use ($subscription, $amount, $gateway, $type) {
                // Create payment record
                $payment = Payment::create([
                    'tenant_id' => $subscription->tenant_id,
                    'subscription_id' => $subscription->id,
                    'payment_gateway' => $gateway,
                    'amount' => $amount,
                    'currency' => 'INR',
                    'status' => 'pending',
                    'type' => $type,
                    'description' => $this->getPaymentDescription($subscription, $type),
                ]);

                // Create order with payment gateway
                // If this fails, the transaction will rollback the payment record
                $orderData = match ($gateway) {
                    'razorpay' => $this->createRazorpayOrder($payment),
                    'stripe' => $this->createStripeOrder($payment),
                    'bank_transfer' => $this->createBankTransferOrder($payment),
                    default => throw new \Exception("Unsupported payment gateway: {$gateway}"),
                };

                // Update payment with gateway order ID
                $payment->update([
                    'gateway_order_id' => $orderData['order_id'],
                    'metadata' => $orderData,
                ]);

                Log::info("Payment order created successfully", [
                    'payment_id' => $payment->id,
                    'gateway' => $gateway,
                    'amount' => $amount,
                ]);

                return [
                    'success' => true,
                    'payment_id' => $payment->id,
                    'order_data' => $orderData,
                    'payment' => $payment,
                ];
            });

        } catch (\Exception $e) {
            Log::error("Payment order creation failed", [
                'subscription_id' => $subscription->id,
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create Razorpay order.
     */
    private function createRazorpayOrder(Payment $payment): array
    {
        $api = new RazorpayApi(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        $order = $api->order->create([
            'amount' => $payment->amount * 100, // Convert to paise
            'currency' => $payment->currency,
            'receipt' => 'receipt_' . $payment->id,
            'notes' => [
                'payment_id' => $payment->id,
                'tenant_id' => $payment->tenant_id,
                'subscription_id' => $payment->subscription_id,
            ],
        ]);

        return [
            'order_id' => $order->id,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'key' => config('services.razorpay.key'),
            'gateway' => 'razorpay',
        ];
    }

    /**
     * Create Stripe payment intent.
     */
    private function createStripeOrder(Payment $payment): array
    {
        // TODO: Implement Stripe integration
        // \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        // $intent = \Stripe\PaymentIntent::create([...]);

        return [
            'order_id' => 'stripe_' . $payment->id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'key' => config('services.stripe.key'),
            'gateway' => 'stripe',
        ];
    }

    /**
     * Create bank transfer order.
     */
    private function createBankTransferOrder(Payment $payment): array
    {
        return [
            'order_id' => 'bank_' . $payment->id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'gateway' => 'bank_transfer',
            'bank_details' => [
                'account_name' => 'Midas Portal Pvt Ltd',
                'account_number' => 'XXXXXXXXXX',
                'ifsc_code' => 'XXXXXXXX',
                'bank_name' => 'State Bank of India',
                'branch' => 'Mumbai Main Branch',
            ],
        ];
    }

    /**
     * Verify payment.
     *
     * ACID-compliant: Uses pessimistic locking and transactions to ensure
     * payment verification and subscription updates are atomic and prevent
     * double-processing from concurrent requests.
     */
    public function verifyPayment(int $paymentId, array $paymentData): array
    {
        try {
            return DB::connection('central')->transaction(function () use ($paymentId, $paymentData) {
                // Use pessimistic locking to prevent concurrent payment verification
                $payment = Payment::lockForUpdate()->findOrFail($paymentId);

                // Check if payment is already processed
                if ($payment->status !== 'pending') {
                    Log::warning("Payment already processed", [
                        'payment_id' => $paymentId,
                        'current_status' => $payment->status,
                    ]);

                    return [
                        'success' => false,
                        'error' => 'Payment has already been processed',
                        'current_status' => $payment->status,
                    ];
                }

                // Verify with payment gateway
                $result = match ($payment->payment_gateway) {
                    'razorpay' => $this->verifyRazorpayPayment($payment, $paymentData),
                    'stripe' => $this->verifyStripePayment($payment, $paymentData),
                    'bank_transfer' => $this->verifyBankTransferPayment($payment, $paymentData),
                    default => throw new \Exception("Unsupported payment gateway"),
                };

                if ($result['success']) {
                    // Mark payment as completed
                    $payment->markAsCompleted($result['payment_id'], $result['response']);

                    // Update subscription (atomic with payment update)
                    $subscription = $payment->subscription;
                    $subscription->update([
                        'payment_gateway' => $payment->payment_gateway,
                        'gateway_subscription_id' => $result['payment_id'],
                        'payment_method' => $result['payment_method'] ?? ['type' => $payment->payment_gateway],
                    ]);

                    Log::info("Payment verified successfully", [
                        'payment_id' => $payment->id,
                        'gateway' => $payment->payment_gateway,
                        'amount' => $payment->amount,
                        'subscription_id' => $subscription->id,
                    ]);
                } else {
                    // Mark payment as failed
                    $payment->markAsFailed($result['error'], $result['response'] ?? []);

                    Log::warning("Payment verification failed", [
                        'payment_id' => $payment->id,
                        'error' => $result['error'],
                    ]);
                }

                return $result;
            });

        } catch (\Exception $e) {
            Log::error("Payment verification exception", [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify Razorpay payment.
     */
    private function verifyRazorpayPayment(Payment $payment, array $data): array
    {
        try {
            $api = new RazorpayApi(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            // Verify signature
            $attributes = [
                'razorpay_order_id' => $data['razorpay_order_id'],
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
            ];

            $api->utility->verifyPaymentSignature($attributes);

            // Fetch payment details
            $razorpayPayment = $api->payment->fetch($data['razorpay_payment_id']);

            return [
                'success' => true,
                'payment_id' => $razorpayPayment->id,
                'response' => $razorpayPayment->toArray(),
                'payment_method' => [
                    'type' => $razorpayPayment->method ?? 'card',
                    'card_last4' => $razorpayPayment->card->last4 ?? null,
                    'card_network' => $razorpayPayment->card->network ?? null,
                ],
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response' => $data,
            ];
        }
    }

    /**
     * Verify Stripe payment.
     */
    private function verifyStripePayment(Payment $payment, array $data): array
    {
        // TODO: Implement Stripe verification
        return [
            'success' => true,
            'payment_id' => $data['payment_intent_id'] ?? 'stripe_test',
            'response' => $data,
            'payment_method' => ['type' => 'stripe'],
        ];
    }

    /**
     * Verify bank transfer payment.
     */
    private function verifyBankTransferPayment(Payment $payment, array $data): array
    {
        // Manual verification required for bank transfers
        return [
            'success' => false,
            'error' => 'Bank transfer payments require manual verification',
            'response' => $data,
        ];
    }

    /**
     * Get payment description.
     */
    private function getPaymentDescription(Subscription $subscription, string $type): string
    {
        $plan = $subscription->plan;

        return match ($type) {
            'subscription' => "New subscription: {$plan->name}",
            'renewal' => "Subscription renewal: {$plan->name}",
            'upgrade' => "Subscription upgrade to: {$plan->name}",
            'addon' => "Add-on purchase for: {$plan->name}",
            default => "Payment for subscription #{$subscription->id}",
        };
    }

    /**
     * Process webhook.
     */
    public function handleWebhook(string $gateway, array $payload): array
    {
        try {
            return match ($gateway) {
                'razorpay' => $this->handleRazorpayWebhook($payload),
                'stripe' => $this->handleStripeWebhook($payload),
                default => throw new \Exception("Unsupported webhook gateway"),
            };
        } catch (\Exception $e) {
            Log::error("Webhook handling failed", [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle Razorpay webhook.
     */
    private function handleRazorpayWebhook(array $payload): array
    {
        $event = $payload['event'] ?? null;

        if ($event === 'payment.captured') {
            $paymentData = $payload['payload']['payment']['entity'] ?? [];
            $paymentId = $paymentData['notes']['payment_id'] ?? null;

            if ($paymentId) {
                $payment = Payment::find($paymentId);
                if ($payment && $payment->isPending()) {
                    $payment->markAsCompleted($paymentData['id'], $paymentData);
                }
            }
        }

        return ['success' => true];
    }

    /**
     * Handle Stripe webhook.
     */
    private function handleStripeWebhook(array $payload): array
    {
        // TODO: Implement Stripe webhook handling
        return ['success' => true];
    }
}
