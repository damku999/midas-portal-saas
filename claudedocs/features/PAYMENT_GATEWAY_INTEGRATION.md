# Payment Gateway Integration Guide

**Project**: Midas Portal - Multi-Tenant CRM
**Feature**: Razorpay Payment Integration for Subscription Management
**Last Updated**: 2025-11-05
**Status**: Production Ready

---

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Setup & Configuration](#setup--configuration)
4. [Payment Flow](#payment-flow)
5. [Webhook Integration](#webhook-integration)
6. [Testing Guide](#testing-guide)
7. [API Reference](#api-reference)
8. [Troubleshooting](#troubleshooting)

---

## Overview

### Features Implemented

✅ **Multi-Gateway Support**
- Razorpay (Card, UPI, Net Banking, Wallets)
- Stripe (Ready for future integration)
- Bank Transfer (Manual verification)

✅ **Subscription Management**
- Trial-to-Paid conversion
- Plan upgrades with payment
- Auto-renewal with stored payment methods
- Billing cycle support (monthly/annual)

✅ **Security**
- Webhook signature verification
- CSRF protection
- Payment gateway encryption
- PCI-DSS compliant payment handling

✅ **User Experience**
- Razorpay Checkout integration
- Real-time payment verification
- Auto-renewal options
- Email notifications

---

## Architecture

### Database Schema

**payments** table:
```sql
id                  - Primary key
tenant_id           - Foreign key to tenants
subscription_id     - Foreign key to subscriptions
payment_gateway     - razorpay|stripe|bank_transfer
gateway_payment_id  - Payment ID from gateway
gateway_order_id    - Order ID from gateway
amount              - Payment amount
currency            - INR (default)
status              - pending|processing|completed|failed|refunded
type                - subscription|renewal|upgrade|addon
gateway_response    - JSON response from gateway
paid_at             - Payment completion timestamp
failed_at           - Payment failure timestamp
refunded_at         - Refund timestamp
failure_reason      - Error message for failed payments
description         - Human-readable description
metadata            - Additional JSON data
```

**subscriptions** table additions:
```sql
auto_renew          - Boolean (enable auto-renewal)
payment_gateway     - Selected payment gateway
payment_method      - JSON (stored payment details)
```

### Service Layer

**PaymentService** (`app/Services/PaymentService.php`)
- Multi-gateway architecture
- Order creation and verification
- Webhook handling
- Payment status management

**Key Methods**:
- `createOrder()` - Create payment order for any gateway
- `verifyPayment()` - Verify payment signature and update status
- `handleWebhook()` - Process gateway webhooks

---

## Setup & Configuration

### 1. Environment Variables

Add to `.env`:

```env
# Razorpay Configuration
RAZORPAY_KEY=rzp_test_xxxxxxxxxxxxx
RAZORPAY_SECRET=xxxxxxxxxxxxxxxxxxxxx
RAZORPAY_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx

# Stripe Configuration (Optional - for future)
STRIPE_KEY=pk_test_xxxxxxxxxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxx
```

### 2. Get Razorpay Credentials

**Test Mode**:
1. Sign up at https://dashboard.razorpay.com/signup
2. Navigate to Settings → API Keys
3. Generate Test API Keys
4. Copy Key ID and Key Secret

**Production Mode**:
1. Complete KYC verification
2. Activate account
3. Generate Live API Keys
4. Update .env with live credentials

### 3. Configure Webhooks

**Razorpay Webhook Setup**:
1. Go to https://dashboard.razorpay.com/app/webhooks
2. Click "Add New Webhook"
3. Enter Webhook URL: `https://yourdomain.com/webhooks/payments/razorpay`
4. Select Events:
   - ✅ payment.captured
   - ✅ payment.failed
   - ✅ payment.authorized
   - ✅ order.paid
   - ✅ refund.created
5. Copy Webhook Secret to `.env` as `RAZORPAY_WEBHOOK_SECRET`

### 4. Run Migrations

```bash
php artisan migrate --path=database/migrations/central --database=central
```

Migrations applied:
- `2025_11_05_141822_add_payment_method_to_subscriptions_table.php`
- `2025_11_05_143507_create_payments_table.php`

### 5. Seed Plans

```bash
php artisan db:seed --class=PlanSeeder
```

Creates 4 plans:
- Starter: ₹499/month
- Professional: ₹999/month
- Enterprise: ₹2499/month
- Professional Annual: ₹9590/year (20% discount)

---

## Payment Flow

### User Upgrade Flow

```
1. User clicks "Upgrade" on Plans page
   ↓
2. Upgrade page loads (subscription.upgrade view)
   - Shows plan comparison
   - Payment method selection
   - Billing cycle (monthly/annual)
   - Auto-renewal checkbox
   ↓
3. User submits form
   ↓
4. AJAX POST to /subscription/upgrade/{plan}
   - SubscriptionController@processUpgrade
   - Validates input
   - Creates Payment record
   - Calls PaymentService->createOrder()
   ↓
5. PaymentService creates Razorpay Order
   - RazorpayApi->order->create()
   - Returns order_id, amount, key
   ↓
6. Frontend opens Razorpay Checkout modal
   - User completes payment (card/UPI/net banking)
   ↓
7. Razorpay callback with payment_id, order_id, signature
   ↓
8. Frontend verifies payment via AJAX POST
   - POST to /subscription/verify-payment
   - SubscriptionController@verifyPayment
   - PaymentService->verifyPayment()
   - Razorpay signature verification
   ↓
9. Payment marked as completed
   - Subscription status → active
   - is_trial → false
   - trial_ends_at → null
   ↓
10. Redirect to subscription dashboard
```

### Auto-Renewal Flow

```
1. Hourly cron runs: php artisan subscriptions:process-trials --auto-convert
   ↓
2. Finds expired trials with:
   - is_trial = true
   - status = 'trial'
   - auto_renew = true
   - payment_method IS NOT NULL
   - trial_ends_at <= now()
   ↓
3. For each subscription:
   - Creates Payment record
   - Calls PaymentService->createOrder()
   - Razorpay creates subscription payment
   - Automatically charges stored payment method
   ↓
4. Webhook receives payment.captured event
   ↓
5. PaymentWebhookController@razorpay
   - Verifies signature
   - Updates Payment status
   - Activates Subscription
   - Logs success
```

### Trial Reminder Flow

```
1. Daily cron at 8 AM: php artisan subscriptions:process-trials --send-reminders
   ↓
2. Finds trial subscriptions expiring in 7, 3, or 1 days
   ↓
3. For each subscription:
   - Sends TrialExpiringMail to tenant admin
   - Email includes:
     * Days remaining
     * Plan features
     * Upgrade link
     * Call-to-action button
```

---

## Webhook Integration

### Webhook Security

All webhooks verify signatures before processing:

**Razorpay**:
```php
$expectedSignature = hash_hmac('sha256', $webhookBody, $webhookSecret);
$isValid = hash_equals($expectedSignature, $receivedSignature);
```

**Stripe** (when implemented):
```php
\Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);
```

### Webhook Events Handled

#### Razorpay

**payment.captured**:
- Payment successfully completed
- Marks Payment as completed
- Activates Subscription
- Clears trial status

**payment.failed**:
- Payment declined/failed
- Marks Payment as failed
- Logs failure reason
- TODO: Send failure notification email

**payment.authorized**:
- Payment authorized but not captured
- Updates Payment status to processing
- Waits for capture event

**order.paid**:
- Order fully paid
- Logs event
- Waits for payment.captured

**refund.created**:
- Refund issued
- Marks Payment as refunded
- TODO: Update subscription status
- TODO: Send refund notification

### Webhook URLs

```
Production:
POST https://yourdomain.com/webhooks/payments/razorpay
POST https://yourdomain.com/webhooks/payments/stripe

Staging:
POST https://staging.yourdomain.com/webhooks/payments/razorpay

Local Testing (ngrok):
POST https://abc123.ngrok.io/webhooks/payments/razorpay
```

---

## Testing Guide

### Test with Razorpay Test Mode

**Test Cards**:
```
Success:
  Card: 4111 1111 1111 1111
  CVV: Any 3 digits
  Expiry: Any future date
  Name: Any name

Decline:
  Card: 4000 0000 0000 0002

Network Error:
  Card: 4000 0000 0000 0119
```

**Test UPI**:
```
Success: success@razorpay
Failure: failure@razorpay
```

**Test Net Banking**:
All test banks will show success/failure options

### Manual Testing Steps

1. **Test Upgrade Flow**:
```bash
# 1. Login as tenant admin
# 2. Navigate to /subscription/plans
# 3. Click "Upgrade" on any higher plan
# 4. Select monthly billing
# 5. Keep auto-renew checked
# 6. Select Razorpay payment
# 7. Click "Proceed to Payment"
# 8. Use test card: 4111 1111 1111 1111
# 9. Complete payment
# 10. Verify redirect to subscription dashboard
# 11. Check subscription status = 'active'
```

2. **Test Trial Expiry Command**:
```bash
# Create test subscription expiring in 3 days
php artisan tinker
>>> $subscription = Subscription::first();
>>> $subscription->update(['trial_ends_at' => now()->addDays(3)]);
>>> exit

# Run reminder command
php artisan subscriptions:process-trials --send-reminders

# Check email sent (check logs or Mailtrap)
php artisan mcp__laravel-boost__read-log-entries --entries=50
```

3. **Test Auto-Conversion**:
```bash
# Create expired trial with payment method
php artisan tinker
>>> $subscription = Subscription::first();
>>> $subscription->update([
>>>     'is_trial' => true,
>>>     'status' => 'trial',
>>>     'auto_renew' => true,
>>>     'payment_method' => ['type' => 'razorpay'],
>>>     'payment_gateway' => 'razorpay',
>>>     'trial_ends_at' => now()->subDay()
>>> ]);
>>> exit

# Run auto-convert command
php artisan subscriptions:process-trials --auto-convert

# Check logs
php artisan mcp__laravel-boost__read-log-entries --entries=20
```

4. **Test Webhook**:
```bash
# Option 1: Use Razorpay Dashboard → Webhooks → Send Test Webhook

# Option 2: Use cURL
curl -X POST https://yourdomain.com/webhooks/payments/razorpay \
  -H "Content-Type: application/json" \
  -H "X-Razorpay-Signature: test_signature" \
  -d '{
    "event": "payment.captured",
    "payload": {
      "payment": {
        "entity": {
          "id": "pay_test123",
          "amount": 49900,
          "status": "captured",
          "notes": {
            "payment_id": 1
          }
        }
      }
    }
  }'
```

---

## API Reference

### Create Order

**Endpoint**: `POST /subscription/upgrade/{plan}`

**Request**:
```json
{
  "payment_gateway": "razorpay",
  "billing_cycle": "monthly",
  "auto_renew": true,
  "payment_details": {}
}
```

**Response Success**:
```json
{
  "success": true,
  "payment": {
    "id": 123,
    "amount": 999,
    "status": "pending",
    "gateway_order_id": "order_abc123"
  },
  "order_data": {
    "order_id": "order_abc123",
    "amount": 99900,
    "currency": "INR",
    "key": "rzp_test_xxxxx",
    "gateway": "razorpay"
  },
  "redirect_url": "/subscription"
}
```

**Response Error**:
```json
{
  "success": false,
  "error": "Failed to create payment order: insufficient funds"
}
```

### Verify Payment

**Endpoint**: `POST /subscription/verify-payment`

**Request**:
```json
{
  "payment_id": 123,
  "razorpay_payment_id": "pay_abc123",
  "razorpay_order_id": "order_abc123",
  "razorpay_signature": "abc123signature"
}
```

**Response Success**:
```json
{
  "success": true,
  "message": "Payment verified successfully!",
  "redirect_url": "/subscription"
}
```

**Response Error**:
```json
{
  "success": false,
  "error": "Payment verification failed: invalid signature"
}
```

### Webhook

**Endpoint**: `POST /webhooks/payments/razorpay`

**Headers**:
```
X-Razorpay-Signature: abc123...
Content-Type: application/json
```

**Payload** (example):
```json
{
  "event": "payment.captured",
  "payload": {
    "payment": {
      "entity": {
        "id": "pay_abc123",
        "amount": 99900,
        "currency": "INR",
        "status": "captured",
        "order_id": "order_abc123",
        "method": "card",
        "card": {
          "last4": "1111",
          "network": "Visa"
        },
        "notes": {
          "payment_id": 123,
          "tenant_id": "tenant123",
          "subscription_id": 456
        }
      }
    }
  }
}
```

**Response**:
```json
{
  "success": true
}
```

---

## Troubleshooting

### Common Issues

#### 1. Payment Order Creation Fails

**Symptom**: "Failed to create payment order" error

**Possible Causes**:
- Invalid Razorpay credentials
- Razorpay API connection timeout
- Invalid amount (must be > 0)

**Debug**:
```bash
# Check Razorpay credentials
php artisan mcp__laravel-boost__get-config services.razorpay

# Check last error
php artisan mcp__laravel-boost__last-error

# Check logs
php artisan mcp__laravel-boost__read-log-entries --entries=50
```

**Fix**:
- Verify `.env` has correct RAZORPAY_KEY and RAZORPAY_SECRET
- Test Razorpay API with cURL:
```bash
curl -u rzp_test_key:rzp_test_secret \
  -X POST https://api.razorpay.com/v1/orders \
  -H "Content-Type: application/json" \
  -d '{"amount": 50000, "currency": "INR"}'
```

#### 2. Payment Verification Fails

**Symptom**: "Payment verification failed: invalid signature"

**Possible Causes**:
- Incorrect signature calculation
- Wrong Razorpay secret
- Tampered payment response

**Debug**:
```bash
# Check payment record
php artisan tinker
>>> Payment::find(123)->gateway_response

# Check logs
php artisan mcp__laravel-boost__read-log-entries --entries=20
```

**Fix**:
- Verify RAZORPAY_SECRET matches dashboard
- Don't modify Razorpay response in frontend
- Check Razorpay dashboard for payment status

#### 3. Webhook Not Received

**Symptom**: Payment completed but subscription not activated

**Possible Causes**:
- Webhook URL not configured
- Webhook signature mismatch
- Server firewall blocking webhook

**Debug**:
```bash
# Check webhook logs
grep "Razorpay webhook" storage/logs/laravel.log

# Test webhook manually with cURL
```

**Fix**:
- Verify webhook URL in Razorpay dashboard
- Check RAZORPAY_WEBHOOK_SECRET matches dashboard
- Whitelist Razorpay IPs in firewall
- Test with ngrok for local development

#### 4. Auto-Renewal Not Working

**Symptom**: Trial expires but not converted to paid

**Possible Causes**:
- Cron job not running
- auto_renew not set
- No payment_method stored

**Debug**:
```bash
# Check subscription
php artisan tinker
>>> $sub = Subscription::where('is_trial', true)->first();
>>> $sub->auto_renew
>>> $sub->payment_method
>>> $sub->trial_ends_at

# Run command manually
php artisan subscriptions:process-trials --auto-convert
```

**Fix**:
- Verify cron is configured: `php artisan schedule:list`
- Test cron manually: `php artisan schedule:run`
- Ensure payment_method is stored on first payment

#### 5. Email Reminders Not Sent

**Symptom**: Trial expiring but no reminder emails

**Possible Causes**:
- Mail configuration incorrect
- Cron not running
- Queue not processed

**Debug**:
```bash
# Check mail config
php artisan mcp__laravel-boost__get-config mail

# Test email
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));

# Run reminder command
php artisan subscriptions:process-trials --send-reminders
```

**Fix**:
- Configure `.env` MAIL_* variables
- Test with Mailtrap for development
- Check queue workers running: `php artisan queue:work`

---

## Security Best Practices

### 1. Environment Security
- ✅ Never commit `.env` file
- ✅ Use strong webhook secrets (min 32 characters)
- ✅ Rotate API keys periodically
- ✅ Use test keys in development

### 2. Payment Security
- ✅ Always verify webhook signatures
- ✅ Never store raw card details
- ✅ Use HTTPS for all payment pages
- ✅ Validate all user input

### 3. Access Control
- ✅ Authenticate all subscription endpoints
- ✅ Verify tenant ownership before payment
- ✅ Log all payment attempts
- ✅ Monitor for suspicious activity

### 4. Data Privacy
- ✅ Store minimal payment data
- ✅ Encrypt sensitive data at rest
- ✅ Comply with PCI-DSS standards
- ✅ Regular security audits

---

## Maintenance

### Regular Tasks

**Daily**:
- Monitor payment logs for errors
- Check failed payment alerts
- Review auto-renewal success rate

**Weekly**:
- Reconcile payments with gateway dashboard
- Review refund requests
- Check webhook delivery logs

**Monthly**:
- Analyze payment conversion rates
- Review gateway fees
- Update payment documentation

### Monitoring Queries

```sql
-- Failed payments today
SELECT * FROM payments
WHERE status = 'failed'
AND DATE(created_at) = CURDATE();

-- Successful payments this month
SELECT COUNT(*), SUM(amount)
FROM payments
WHERE status = 'completed'
AND MONTH(created_at) = MONTH(CURDATE());

-- Pending payments (stuck)
SELECT * FROM payments
WHERE status = 'pending'
AND created_at < NOW() - INTERVAL 1 HOUR;

-- Auto-renewal success rate
SELECT
  COUNT(*) as total,
  SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful,
  (SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as success_rate
FROM payments
WHERE type = 'renewal'
AND MONTH(created_at) = MONTH(CURDATE());
```

---

## Future Enhancements

### Planned Features

🔮 **Stripe Integration**
- Complete Stripe payment flow
- Stripe subscription management
- Stripe webhooks implementation

🔮 **Invoice Generation**
- PDF invoice generation
- Email invoices automatically
- Invoice history for users

🔮 **Payment Analytics**
- Revenue dashboard
- Conversion rate tracking
- Failed payment analysis
- Gateway comparison reports

🔮 **Advanced Features**
- Prorated billing for mid-cycle upgrades
- Discount codes and coupons
- Multi-currency support
- Payment retry logic for failed auto-renewals
- Grace period for expired subscriptions
- Dunning management for failed payments

---

## Support & Resources

### Documentation
- [Razorpay API Docs](https://razorpay.com/docs/api/)
- [Razorpay Checkout](https://razorpay.com/docs/payments/payment-gateway/web-integration/)
- [Razorpay Webhooks](https://razorpay.com/docs/webhooks/)

### Contact
- **Developer**: Claude Code
- **Project Repo**: (Add your Git repo here)
- **Support Email**: (Add support email here)

---

**Last Updated**: 2025-11-05
**Version**: 1.0.0
**Status**: ✅ Production Ready
