<?php

namespace App\Services;

use App\Models\Central\Invoice;
use App\Models\Central\Payment;
use App\Models\Central\Subscription;
use Stancl\Tenancy\Database\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    /**
     * Company details for invoice generation.
     */
    const COMPANY_NAME = 'Webmonks Technologies';
    const COMPANY_LEGAL_NAME = 'Darshankumar Himmatlal Baraiya';
    const COMPANY_ADDRESS = '30 Shubh Residancy, Near UGVCL-GEB, Bopal, Ahmedabad, Gujarat, India - 380058';
    const COMPANY_GSTIN = '24CFDPB1228P1ZM';
    const COMPANY_PAN = 'CFDPB1228P';
    const COMPANY_EMAIL = 'darshan@webmonks.in';
    const COMPANY_PHONE = '+91 80000 71413';

    /**
     * Bank details for invoice.
     */
    const BANK_ACCOUNT_NAME = 'WebMonks Technologies';
    const BANK_ACCOUNT_NUMBER = '3547292129';
    const BANK_IFSC = 'KKBK0002560';
    const BANK_SWIFT = 'KKBKINBB';
    const BANK_NAME = 'KOTAK MAHINDRA BANK';
    const BANK_MICR = '380485016';

    /**
     * HSN/SAC code for software services.
     */
    const HSN_SAC_SOFTWARE = '998314';

    /**
     * Generate invoice for a payment.
     */
    public function generateInvoiceForPayment(Payment $payment, ?string $description = null): Invoice
    {
        DB::connection('central')->beginTransaction();

        try {
            $tenant = $payment->tenant;
            $subscription = $payment->subscription;
            $plan = $subscription->plan;

            // Check if tenant has GST details
            $customerGstin = $tenant->data['gstin'] ?? null;
            $customerState = $this->extractStateFromGSTIN($customerGstin);
            $isInterState = $customerState && $customerState !== '24'; // 24 is Gujarat

            // Generate invoice number
            $invoiceNumber = Invoice::generateInvoiceNumber();

            // Prepare invoice data
            $invoiceData = [
                'invoice_number' => $invoiceNumber,
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,

                // Customer details
                'customer_name' => $tenant->data['company_name'] ?? $tenant->id,
                'customer_address' => $this->formatTenantAddress($tenant),
                'customer_gstin' => $customerGstin,
                'customer_pan' => $tenant->data['pan'] ?? null,
                'customer_email' => $tenant->data['company_email'] ?? null,
                'customer_phone' => $tenant->data['company_phone'] ?? null,

                // Invoice details
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => $payment->status === 'completed' ? 'paid' : 'unpaid',

                // Line items
                'items' => [[
                    'description' => $description ?? "{$plan->name} Subscription ({$plan->billing_interval_label})",
                    'hsn_sac' => self::HSN_SAC_SOFTWARE,
                    'quantity' => 1,
                    'rate' => (float) $plan->price,
                    'amount' => (float) $plan->price,
                ]],

                // Amount breakdown
                'subtotal' => (float) $plan->price,
                'total_amount' => (float) $plan->price, // Will be recalculated with GST
            ];

            // Create invoice
            $invoice = Invoice::create($invoiceData);

            // Calculate GST
            $invoice->calculateGST($isInterState);

            // Calculate gateway charges if payment was made online
            if (in_array($payment->payment_gateway, ['razorpay', 'stripe'])) {
                $invoice->calculateGatewayCharges($payment->payment_gateway);
            }

            // Save calculations
            $invoice->save();

            // Mark as paid if payment is completed
            if ($payment->status === 'completed') {
                $amountPaid = $invoice->total_with_gateway_charges > 0
                    ? $invoice->total_with_gateway_charges
                    : $invoice->total_amount;
                $invoice->markAsPaid($amountPaid);
            }

            DB::connection('central')->commit();

            Log::info("Invoice {$invoiceNumber} generated for payment #{$payment->id}");

            return $invoice;

        } catch (\Exception $e) {
            DB::connection('central')->rollBack();
            Log::error("Failed to generate invoice for payment #{$payment->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate invoice for a subscription (manual invoice).
     */
    public function generateInvoiceForSubscription(
        Subscription $subscription,
        float $amount,
        string $description,
        ?string $gateway = null
    ): Invoice {
        DB::connection('central')->beginTransaction();

        try {
            $tenant = $subscription->tenant;
            $plan = $subscription->plan;

            // Check if tenant has GST details
            $customerGstin = $tenant->data['gstin'] ?? null;
            $customerState = $this->extractStateFromGSTIN($customerGstin);
            $isInterState = $customerState && $customerState !== '24'; // 24 is Gujarat

            // Generate invoice number
            $invoiceNumber = Invoice::generateInvoiceNumber();

            // Prepare invoice data
            $invoiceData = [
                'invoice_number' => $invoiceNumber,
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,

                // Customer details
                'customer_name' => $tenant->data['company_name'] ?? $tenant->id,
                'customer_address' => $this->formatTenantAddress($tenant),
                'customer_gstin' => $customerGstin,
                'customer_pan' => $tenant->data['pan'] ?? null,
                'customer_email' => $tenant->data['company_email'] ?? null,
                'customer_phone' => $tenant->data['company_phone'] ?? null,

                // Invoice details
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => 'unpaid',

                // Line items
                'items' => [[
                    'description' => $description,
                    'hsn_sac' => self::HSN_SAC_SOFTWARE,
                    'quantity' => 1,
                    'rate' => $amount,
                    'amount' => $amount,
                ]],

                // Amount breakdown
                'subtotal' => $amount,
                'total_amount' => $amount, // Will be recalculated with GST
            ];

            // Create invoice
            $invoice = Invoice::create($invoiceData);

            // Calculate GST
            $invoice->calculateGST($isInterState);

            // Calculate gateway charges if specified
            if ($gateway && in_array($gateway, ['razorpay', 'stripe'])) {
                $invoice->calculateGatewayCharges($gateway);
            }

            // Save calculations
            $invoice->save();

            DB::connection('central')->commit();

            Log::info("Invoice {$invoiceNumber} generated for subscription #{$subscription->id}");

            return $invoice;

        } catch (\Exception $e) {
            DB::connection('central')->rollBack();
            Log::error("Failed to generate invoice for subscription #{$subscription->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract state code from GSTIN.
     * First 2 digits of GSTIN represent state code.
     */
    private function extractStateFromGSTIN(?string $gstin): ?string
    {
        if (!$gstin || strlen($gstin) < 2) {
            return null;
        }

        return substr($gstin, 0, 2);
    }

    /**
     * Format tenant address for invoice.
     */
    private function formatTenantAddress(Tenant $tenant): string
    {
        $parts = [];

        if (!empty($tenant->data['company_address'])) {
            $parts[] = $tenant->data['company_address'];
        }

        if (!empty($tenant->data['city'])) {
            $parts[] = $tenant->data['city'];
        }

        if (!empty($tenant->data['state'])) {
            $parts[] = $tenant->data['state'];
        }

        if (!empty($tenant->data['country'])) {
            $parts[] = $tenant->data['country'];
        }

        if (!empty($tenant->data['pincode'])) {
            $parts[] = $tenant->data['pincode'];
        }

        return implode(', ', array_filter($parts));
    }

    /**
     * Get company details for invoice.
     */
    public static function getCompanyDetails(): array
    {
        return [
            'name' => self::COMPANY_NAME,
            'legal_name' => self::COMPANY_LEGAL_NAME,
            'address' => self::COMPANY_ADDRESS,
            'gstin' => self::COMPANY_GSTIN,
            'pan' => self::COMPANY_PAN,
            'email' => self::COMPANY_EMAIL,
            'phone' => self::COMPANY_PHONE,
        ];
    }

    /**
     * Get bank details for invoice.
     */
    public static function getBankDetails(): array
    {
        return [
            'account_name' => self::BANK_ACCOUNT_NAME,
            'account_number' => self::BANK_ACCOUNT_NUMBER,
            'ifsc' => self::BANK_IFSC,
            'swift' => self::BANK_SWIFT,
            'bank_name' => self::BANK_NAME,
            'micr' => self::BANK_MICR,
        ];
    }
}
