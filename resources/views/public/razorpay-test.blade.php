<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Razorpay Payment Test - Midas Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .test-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            margin-bottom: 30px;
        }
        .status-badge {
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            display: inline-block;
            margin: 10px 0;
        }
        .payment-option {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-option:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .payment-option.selected {
            border-color: #667eea;
            background: #f0f3ff;
        }
        .test-result {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }
        .config-status {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="text-center text-white mb-4">
            <h1 class="display-4 fw-bold"><i class="bi bi-credit-card-2-front"></i> Razorpay Test Page</h1>
            <p class="lead">Test payment integration before going live</p>
        </div>

        <!-- Configuration Status -->
        <div class="test-card">
            <h4><i class="bi bi-gear-fill text-warning"></i> Configuration Status</h4>
            <div class="config-status">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Razorpay Key:</strong>
                        <code>{{ config('services.razorpay.key') ?: 'NOT CONFIGURED' }}</code>
                    </div>
                    <div class="col-md-6">
                        <strong>Webhook Secret:</strong>
                        <code>{{ config('services.razorpay.webhook_secret') ? 'CONFIGURED ✓' : 'NOT CONFIGURED ✗' }}</code>
                    </div>
                </div>
                @if(!config('services.razorpay.key'))
                    <div class="alert alert-danger mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Missing Configuration!</strong> Please add Razorpay credentials to your .env file
                    </div>
                @endif
            </div>
        </div>

        <!-- Webhook URLs -->
        <div class="test-card">
            <h4><i class="bi bi-link-45deg text-info"></i> Webhook Configuration</h4>
            <div class="alert alert-info">
                <h6><strong>Configure this webhook URL in your Razorpay Dashboard:</strong></h6>
                <div class="input-group mt-2">
                    <input type="text" class="form-control" id="webhookUrl"
                           value="{{ url('/webhooks/payments/razorpay') }}" readonly>
                    <button class="btn btn-primary" onclick="copyWebhookUrl()">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <small class="text-muted mt-2 d-block">
                    <strong>Events to enable:</strong> payment.captured, payment.failed, payment.authorized, order.paid, refund.created
                </small>
            </div>
        </div>

        <!-- Test Payment Form -->
        <div class="test-card">
            <h4><i class="bi bi-currency-rupee text-success"></i> Test Payment</h4>

            <form id="testPaymentForm">
                @csrf

                <!-- Amount Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Select Test Amount</label>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="payment-option" data-amount="100" onclick="selectAmount(100)">
                                <h5 class="mb-0">₹100</h5>
                                <small class="text-muted">Minimum</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="payment-option selected" data-amount="500" onclick="selectAmount(500)">
                                <h5 class="mb-0">₹500</h5>
                                <small class="text-muted">Standard</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="payment-option" data-amount="1000" onclick="selectAmount(1000)">
                                <h5 class="mb-0">₹1,000</h5>
                                <small class="text-muted">Premium</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="customAmount"
                                       placeholder="Custom" min="1" onchange="selectCustomAmount()">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="selectedAmount" value="500">
                </div>

                <!-- Test Description -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Test Description</label>
                    <input type="text" class="form-control" id="testDescription"
                           value="Test Payment - Razorpay Integration" placeholder="Enter description">
                </div>

                <!-- Test Cards Reference -->
                <div class="alert alert-warning">
                    <h6><strong><i class="bi bi-info-circle"></i> Razorpay Test Cards:</strong></h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Success:</strong> <code>4111 1111 1111 1111</code><br>
                            <strong>Decline:</strong> <code>4000 0000 0000 0002</code>
                        </div>
                        <div class="col-md-6">
                            <strong>UPI Success:</strong> <code>success@razorpay</code><br>
                            <strong>UPI Failure:</strong> <code>failure@razorpay</code>
                        </div>
                    </div>
                    <small class="text-muted">CVV: Any 3 digits | Expiry: Any future date</small>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary btn-lg" onclick="initiatePayment()"
                            {{ !config('services.razorpay.key') ? 'disabled' : '' }}>
                        <i class="bi bi-credit-card"></i> Start Test Payment
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="checkPaymentStatus()">
                        <i class="bi bi-arrow-clockwise"></i> Check Last Payment Status
                    </button>
                </div>
            </form>
        </div>

        <!-- Test Results -->
        <div class="test-card test-result" id="testResult">
            <h4><i class="bi bi-clipboard-check text-success"></i> Test Result</h4>
            <div id="resultContent"></div>
        </div>

        <!-- Recent Test Payments -->
        <div class="test-card">
            <h4><i class="bi bi-clock-history text-secondary"></i> Recent Test Payments</h4>
            <div id="recentPayments">
                <div class="text-center text-muted py-4">
                    <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
                    <p class="mt-2">No test payments yet. Start a test above!</p>
                </div>
            </div>
        </div>

        <!-- Back Link -->
        <div class="text-center mt-4">
            <a href="{{ url('/') }}" class="btn btn-outline-light">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <!-- Razorpay Checkout Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentPaymentId = null;

        function selectAmount(amount) {
            $('.payment-option').removeClass('selected');
            $(`.payment-option[data-amount="${amount}"]`).addClass('selected');
            $('#selectedAmount').val(amount);
            $('#customAmount').val('');
        }

        function selectCustomAmount() {
            const customAmount = $('#customAmount').val();
            if (customAmount && customAmount > 0) {
                $('.payment-option').removeClass('selected');
                $('#selectedAmount').val(customAmount);
            }
        }

        function copyWebhookUrl() {
            const webhookUrl = document.getElementById('webhookUrl');
            webhookUrl.select();
            document.execCommand('copy');

            // Show feedback
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copied!';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 2000);
        }

        function initiatePayment() {
            const amount = $('#selectedAmount').val();
            const description = $('#testDescription').val();

            if (!amount || amount <= 0) {
                alert('Please select or enter a valid amount');
                return;
            }

            console.log('Initiating payment:', { amount, description });

            // Create test payment order
            $.ajax({
                url: '{{ url("/razorpay-test/create-order") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                data: JSON.stringify({
                    amount: amount,
                    description: description
                }),
                success: function(response) {
                    console.log('Order created:', response);
                    if (response.success) {
                        currentPaymentId = response.payment_id;
                        openRazorpayCheckout(response.order_data);
                    } else {
                        showResult('error', 'Failed to create order: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    console.error('Order creation failed:', xhr);
                    let errorMsg = 'Unknown error';

                    if (xhr.responseJSON) {
                        errorMsg = xhr.responseJSON.message || xhr.responseJSON.error || errorMsg;
                    } else if (xhr.responseText) {
                        try {
                            const parsed = JSON.parse(xhr.responseText);
                            errorMsg = parsed.message || parsed.error || errorMsg;
                        } catch(e) {
                            errorMsg = xhr.statusText || errorMsg;
                        }
                    }

                    showResult('error', 'Error: ' + errorMsg);
                }
            });
        }

        function openRazorpayCheckout(orderData) {
            const options = {
                key: orderData.key,
                amount: orderData.amount,
                currency: orderData.currency,
                order_id: orderData.order_id,
                name: 'Midas Portal',
                description: $('#testDescription').val(),
                image: '{{ asset("images/logo.png") }}',
                handler: function(response) {
                    verifyPayment(response);
                },
                prefill: {
                    name: 'Test User',
                    email: 'test@example.com',
                    contact: '9999999999'
                },
                theme: {
                    color: '#667eea'
                },
                modal: {
                    ondismiss: function() {
                        showResult('warning', 'Payment cancelled by user');
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.open();
        }

        function verifyPayment(response) {
            $.ajax({
                url: '{{ url("/razorpay-test/verify-payment") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    payment_id: currentPaymentId,
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature
                },
                success: function(result) {
                    if (result.success) {
                        showResult('success', 'Payment Successful!', result.payment);
                        loadRecentPayments();
                    } else {
                        showResult('error', 'Payment verification failed: ' + result.error);
                    }
                },
                error: function(xhr) {
                    showResult('error', 'Verification error: ' + xhr.responseJSON?.message);
                }
            });
        }

        function showResult(type, message, payment = null) {
            let icon = '';
            let bgClass = '';

            switch(type) {
                case 'success':
                    icon = 'bi-check-circle-fill text-success';
                    bgClass = 'alert-success';
                    break;
                case 'error':
                    icon = 'bi-x-circle-fill text-danger';
                    bgClass = 'alert-danger';
                    break;
                case 'warning':
                    icon = 'bi-exclamation-triangle-fill text-warning';
                    bgClass = 'alert-warning';
                    break;
            }

            let html = `
                <div class="alert ${bgClass}">
                    <h5><i class="bi ${icon}"></i> ${message}</h5>
                </div>
            `;

            if (payment) {
                html += `
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr><th>Payment ID:</th><td>${payment.id}</td></tr>
                            <tr><th>Gateway Payment ID:</th><td><code>${payment.gateway_payment_id}</code></td></tr>
                            <tr><th>Amount:</th><td>₹${payment.amount}</td></tr>
                            <tr><th>Status:</th><td><span class="badge bg-success">${payment.status}</span></td></tr>
                            <tr><th>Gateway:</th><td>${payment.payment_gateway}</td></tr>
                            <tr><th>Created:</th><td>${new Date(payment.created_at).toLocaleString()}</td></tr>
                        </table>
                    </div>
                `;
            }

            $('#resultContent').html(html);
            $('#testResult').slideDown();
        }

        function checkPaymentStatus() {
            if (!currentPaymentId) {
                alert('No payment initiated yet. Please start a test payment first.');
                return;
            }

            $.ajax({
                url: '{{ url("/razorpay-test/payment-status") }}/' + currentPaymentId,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        showResult('success', 'Payment Status Retrieved', response.payment);
                    } else {
                        showResult('error', 'Payment not found');
                    }
                },
                error: function(xhr) {
                    showResult('error', 'Error retrieving payment status');
                }
            });
        }

        function loadRecentPayments() {
            $.ajax({
                url: '{{ url("/razorpay-test/recent-payments") }}',
                method: 'GET',
                success: function(response) {
                    if (response.payments && response.payments.length > 0) {
                        let html = '<div class="table-responsive"><table class="table table-hover">';
                        html += '<thead><tr><th>ID</th><th>Amount</th><th>Status</th><th>Gateway ID</th><th>Date</th></tr></thead><tbody>';

                        response.payments.forEach(payment => {
                            const statusClass = payment.status === 'completed' ? 'success' :
                                              payment.status === 'failed' ? 'danger' : 'warning';
                            html += `
                                <tr>
                                    <td>#${payment.id}</td>
                                    <td>₹${payment.amount}</td>
                                    <td><span class="badge bg-${statusClass}">${payment.status}</span></td>
                                    <td><code>${payment.gateway_payment_id || 'N/A'}</code></td>
                                    <td>${new Date(payment.created_at).toLocaleString()}</td>
                                </tr>
                            `;
                        });

                        html += '</tbody></table></div>';
                        $('#recentPayments').html(html);
                    }
                }
            });
        }

        // Load recent payments on page load
        $(document).ready(function() {
            loadRecentPayments();
        });
    </script>
</body>
</html>
