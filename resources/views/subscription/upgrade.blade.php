@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-arrow-up me-2"></i>Upgrade to {{ $plan->name }}</h4>
                </div>
                <div class="card-body">
                    <p class="lead">You're about to upgrade from <strong>{{ $currentSubscription->plan->name }}</strong> to <strong>{{ $plan->name }}</strong></p>
                </div>
            </div>

            <!-- Plan Comparison -->
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-balance-scale me-2"></i>What's Changing</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Current Plan</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-users text-muted me-2"></i>{{ $currentSubscription->plan->max_users == -1 ? 'Unlimited' : $currentSubscription->plan->max_users }} Users</li>
                                <li class="mb-2"><i class="fas fa-user-tie text-muted me-2"></i>{{ $currentSubscription->plan->max_customers == -1 ? 'Unlimited' : number_format($currentSubscription->plan->max_customers) }} Customers</li>
                                <li class="mb-2"><i class="fas fa-database text-muted me-2"></i>{{ $currentSubscription->plan->storage_limit_gb == -1 ? 'Unlimited' : $currentSubscription->plan->storage_limit_gb . ' GB' }} Storage</li>
                                <li class="mb-2"><strong class="text-muted">₹{{ number_format($currentSubscription->plan->price) }}/month</strong></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">New Plan</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-users text-primary me-2"></i><strong>{{ $plan->max_users == -1 ? 'Unlimited' : $plan->max_users }} Users</strong></li>
                                <li class="mb-2"><i class="fas fa-user-tie text-primary me-2"></i><strong>{{ $plan->max_customers == -1 ? 'Unlimited' : number_format($plan->max_customers) }} Customers</strong></li>
                                <li class="mb-2"><i class="fas fa-database text-primary me-2"></i><strong>{{ $plan->storage_limit_gb == -1 ? 'Unlimited' : $plan->storage_limit_gb . ' GB' }} Storage</strong></li>
                                <li class="mb-2"><strong class="text-primary">₹{{ number_format($plan->price) }}/month</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upgrade Form -->
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Details</h5>
                </div>
                <div class="card-body">
                    <form id="upgradeForm" method="POST" action="{{ route('subscription.process-upgrade', $plan) }}">
                        @csrf

                        <!-- Billing Cycle -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Billing Cycle</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="billing_cycle" id="monthly" value="monthly" checked>
                                <label class="btn btn-outline-primary" for="monthly">
                                    <i class="fas fa-calendar-alt me-1"></i>Monthly
                                    <br><small>₹{{ number_format($plan->price) }}/month</small>
                                </label>

                                @if(isset($plan->annual_price))
                                <input type="radio" class="btn-check" name="billing_cycle" id="annual" value="annual">
                                <label class="btn btn-outline-primary" for="annual">
                                    <i class="fas fa-calendar-check me-1"></i>Annual
                                    <br><small>₹{{ number_format($plan->annual_price) }}/year</small>
                                    <span class="badge bg-success">Save 20%</span>
                                </label>
                                @endif
                            </div>
                        </div>

                        <!-- Payment Gateway -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Payment Method</label>
                            <div class="list-group">
                                <label class="list-group-item d-flex align-items-center">
                                    <input class="form-check-input me-3" type="radio" name="payment_gateway" value="razorpay" checked>
                                    <div class="flex-fill">
                                        <i class="fas fa-credit-card text-primary me-2"></i>
                                        <strong>Credit/Debit Card, UPI, Net Banking</strong>
                                        <br><small class="text-muted">Powered by Razorpay - Secure payment gateway</small>
                                    </div>
                                </label>

                                <label class="list-group-item d-flex align-items-center">
                                    <input class="form-check-input me-3" type="radio" name="payment_gateway" value="bank_transfer">
                                    <div class="flex-fill">
                                        <i class="fas fa-university text-success me-2"></i>
                                        <strong>Direct Bank Transfer</strong>
                                        <br><small class="text-muted">Manual verification required (2-3 business days)</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Auto-Renewal -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="auto_renew" name="auto_renew" value="1" checked>
                                <label class="form-check-label" for="auto_renew">
                                    <strong>Enable Auto-Renewal</strong>
                                    <br><small class="text-muted">Automatically renew your subscription before it expires</small>
                                </label>
                            </div>
                        </div>

                        <!-- Total Amount -->
                        <div class="alert alert-info mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Total Amount</h5>
                                    <small class="text-muted">Amount payable now</small>
                                </div>
                                <div class="text-end">
                                    <h3 class="mb-0 text-primary" id="totalAmount">₹{{ number_format($plan->price) }}</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="proceedButton">
                                <i class="fas fa-lock me-2"></i>Proceed to Payment
                            </button>
                            <a href="{{ route('subscription.plans') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Plans
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('upgradeForm');
    const monthlyRadio = document.getElementById('monthly');
    const annualRadio = document.getElementById('annual');
    const totalAmountEl = document.getElementById('totalAmount');
    const proceedButton = document.getElementById('proceedButton');

    const monthlyPrice = {{ $plan->price }};
    const annualPrice = {{ $plan->annual_price ?? ($plan->price * 12 * 0.8) }};

    // Update total amount when billing cycle changes
    function updateTotalAmount() {
        const isAnnual = annualRadio && annualRadio.checked;
        const amount = isAnnual ? annualPrice : monthlyPrice;
        totalAmountEl.textContent = '₹' + amount.toLocaleString('en-IN');
    }

    if (monthlyRadio) monthlyRadio.addEventListener('change', updateTotalAmount);
    if (annualRadio) annualRadio.addEventListener('change', updateTotalAmount);

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const paymentGateway = form.querySelector('input[name="payment_gateway"]:checked').value;

        // If bank transfer, submit form directly
        if (paymentGateway === 'bank_transfer') {
            showBankTransferInstructions();
            return;
        }

        // For Razorpay, create order first
        proceedButton.disabled = true;
        proceedButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (paymentGateway === 'razorpay') {
                    openRazorpayCheckout(data.order_data, data.payment);
                }
            } else {
                throw new Error(data.error || 'Payment order creation failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to process upgrade: ' + error.message);
            proceedButton.disabled = false;
            proceedButton.innerHTML = '<i class="fas fa-lock me-2"></i>Proceed to Payment';
        });
    });

    // Open Razorpay Checkout
    function openRazorpayCheckout(orderData, payment) {
        const options = {
            key: orderData.key,
            amount: orderData.amount,
            currency: orderData.currency,
            name: 'Midas Portal',
            description: 'Subscription Upgrade - {{ $plan->name }}',
            order_id: orderData.order_id,
            handler: function(response) {
                verifyPayment(payment.id, response);
            },
            prefill: {
                name: '{{ auth()->user()->name }}',
                email: '{{ auth()->user()->email }}',
            },
            theme: {
                color: '#0d6efd'
            },
            modal: {
                ondismiss: function() {
                    proceedButton.disabled = false;
                    proceedButton.innerHTML = '<i class="fas fa-lock me-2"></i>Proceed to Payment';
                }
            }
        };

        const rzp = new Razorpay(options);
        rzp.open();
    }

    // Verify payment after Razorpay callback
    function verifyPayment(paymentId, razorpayResponse) {
        proceedButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying Payment...';

        fetch('{{ route("subscription.verify-payment") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                payment_id: paymentId,
                razorpay_payment_id: razorpayResponse.razorpay_payment_id,
                razorpay_order_id: razorpayResponse.razorpay_order_id,
                razorpay_signature: razorpayResponse.razorpay_signature
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and redirect
                showSuccessMessage();
                setTimeout(() => {
                    window.location.href = data.redirect_url || '{{ route("subscription.index") }}';
                }, 2000);
            } else {
                throw new Error(data.error || 'Payment verification failed');
            }
        })
        .catch(error => {
            console.error('Verification Error:', error);
            alert('Payment verification failed: ' + error.message);
            proceedButton.disabled = false;
            proceedButton.innerHTML = '<i class="fas fa-lock me-2"></i>Proceed to Payment';
        });
    }

    // Show bank transfer instructions
    function showBankTransferInstructions() {
        alert('Bank Transfer:\n\nPlease transfer the amount to:\nAccount: XXXXXXXXXX\nIFSC: XXXXXXXX\nBank: State Bank of India\n\nSend payment proof to billing@midasportal.com');
        proceedButton.disabled = false;
    }

    // Show success message
    function showSuccessMessage() {
        const successDiv = document.createElement('div');
        successDiv.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
        successDiv.style.zIndex = '9999';
        successDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i><strong>Payment Successful!</strong> Your subscription has been upgraded.';
        document.body.appendChild(successDiv);
    }
});
</script>
@endsection
