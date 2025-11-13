<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Models\Tenant;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'central';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_number',
        'tenant_id',
        'subscription_id',
        'payment_id',
        'customer_name',
        'customer_address',
        'customer_gstin',
        'customer_pan',
        'customer_email',
        'customer_phone',
        'invoice_date',
        'due_date',
        'status',
        'items',
        'subtotal',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'igst_rate',
        'igst_amount',
        'total_tax',
        'round_off',
        'total_amount',
        'gateway_charges',
        'gateway_charges_gst',
        'total_with_gateway_charges',
        'amount_paid',
        'balance_due',
        'paid_at',
        'notes',
        'metadata',
        'pdf_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'items' => 'array',
        'metadata' => 'array',
        'subtotal' => 'decimal:2',
        'cgst_rate' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_rate' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_rate' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'round_off' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'gateway_charges' => 'decimal:2',
        'gateway_charges_gst' => 'decimal:2',
        'total_with_gateway_charges' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the invoice.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Get the subscription that owns the invoice.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the payment associated with the invoice.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Generate next invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $lastInvoice = static::latest('id')->first();

        if (!$lastInvoice) {
            return 'A00001';
        }

        // Extract number from last invoice (e.g., A00001 -> 1)
        $lastNumber = (int) substr($lastInvoice->invoice_number, 1);
        $nextNumber = $lastNumber + 1;

        return 'A' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate GST amounts based on subtotal.
     * For intra-state (Gujarat): CGST 9% + SGST 9% = 18%
     * For inter-state: IGST 18%
     */
    public function calculateGST(bool $isInterState = false): void
    {
        if ($isInterState) {
            // Inter-state transaction - use IGST
            $this->igst_rate = 18.00;
            $this->igst_amount = round(($this->subtotal * $this->igst_rate) / 100, 2);
            $this->cgst_rate = 0;
            $this->cgst_amount = 0;
            $this->sgst_rate = 0;
            $this->sgst_amount = 0;
        } else {
            // Intra-state transaction - use CGST + SGST
            $this->cgst_rate = 9.00;
            $this->sgst_rate = 9.00;
            $this->cgst_amount = round(($this->subtotal * $this->cgst_rate) / 100, 2);
            $this->sgst_amount = round(($this->subtotal * $this->sgst_rate) / 100, 2);
            $this->igst_rate = 0;
            $this->igst_amount = 0;
        }

        $this->total_tax = $this->cgst_amount + $this->sgst_amount + $this->igst_amount;
        $this->total_amount = $this->subtotal + $this->total_tax + $this->round_off;
    }

    /**
     * Calculate payment gateway charges.
     * Razorpay charges: 2% + GST on transaction amount
     */
    public function calculateGatewayCharges(string $gateway = 'razorpay'): void
    {
        $chargeRate = match($gateway) {
            'razorpay' => 2.00, // 2%
            'stripe' => 2.90,   // 2.9%
            default => 2.00,
        };

        // Calculate gateway charges on total amount
        $this->gateway_charges = round(($this->total_amount * $chargeRate) / 100, 2);

        // Calculate GST on gateway charges (18%)
        $this->gateway_charges_gst = round(($this->gateway_charges * 18) / 100, 2);

        // Total with gateway charges
        $this->total_with_gateway_charges = $this->total_amount + $this->gateway_charges + $this->gateway_charges_gst;
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(float $amount): void
    {
        $this->amount_paid = $amount;
        $this->balance_due = max(0, $this->total_with_gateway_charges - $amount);
        $this->status = $this->balance_due > 0 ? 'unpaid' : 'paid';
        $this->paid_at = $this->balance_due > 0 ? null : now();
        $this->save();
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Get formatted total in words.
     */
    public function getTotalInWords(): string
    {
        return $this->numberToWords($this->total_amount);
    }

    /**
     * Get formatted tax total in words.
     */
    public function getTaxInWords(): string
    {
        return $this->numberToWords($this->total_tax);
    }

    /**
     * Convert number to words (Indian numbering system).
     */
    private function numberToWords(float $number): string
    {
        $amount = floor($number);
        $decimal = round(($number - $amount) * 100);

        $words = [
            0 => '', 1 => 'ONE', 2 => 'TWO', 3 => 'THREE', 4 => 'FOUR', 5 => 'FIVE',
            6 => 'SIX', 7 => 'SEVEN', 8 => 'EIGHT', 9 => 'NINE', 10 => 'TEN',
            11 => 'ELEVEN', 12 => 'TWELVE', 13 => 'THIRTEEN', 14 => 'FOURTEEN', 15 => 'FIFTEEN',
            16 => 'SIXTEEN', 17 => 'SEVENTEEN', 18 => 'EIGHTEEN', 19 => 'NINETEEN', 20 => 'TWENTY',
            30 => 'THIRTY', 40 => 'FORTY', 50 => 'FIFTY', 60 => 'SIXTY', 70 => 'SEVENTY',
            80 => 'EIGHTY', 90 => 'NINETY'
        ];

        if ($amount == 0) {
            return 'ZERO RUPEES ONLY';
        }

        $result = '';

        // Crores
        $crore = (int)($amount / 10000000);
        if ($crore) {
            $result .= $this->convertGroup($crore, $words) . ' CRORE ';
            $amount %= 10000000;
        }

        // Lakhs
        $lakh = (int)($amount / 100000);
        if ($lakh) {
            $result .= $this->convertGroup($lakh, $words) . ' LAKH ';
            $amount %= 100000;
        }

        // Thousands
        $thousand = (int)($amount / 1000);
        if ($thousand) {
            $result .= $this->convertGroup($thousand, $words) . ' THOUSAND ';
            $amount %= 1000;
        }

        // Hundreds
        $hundred = (int)($amount / 100);
        if ($hundred) {
            $result .= $words[$hundred] . ' HUNDRED ';
            $amount %= 100;
        }

        // Remaining
        if ($amount > 0) {
            if ($amount < 20) {
                $result .= $words[$amount] . ' ';
            } else {
                $result .= $words[($amount - $amount % 10)] . ' ';
                if ($amount % 10) {
                    $result .= $words[$amount % 10] . ' ';
                }
            }
        }

        return trim($result) . ' RUPEES ONLY';
    }

    /**
     * Convert a group of numbers to words.
     */
    private function convertGroup(int $number, array $words): string
    {
        $result = '';

        if ($number >= 100) {
            $result .= $words[(int)($number / 100)] . ' HUNDRED ';
            $number %= 100;
        }

        if ($number >= 20) {
            $result .= $words[$number - $number % 10] . ' ';
            $number %= 10;
        }

        if ($number > 0 && $number < 20) {
            $result .= $words[$number] . ' ';
        }

        return trim($result);
    }

    /**
     * Scope to get paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope to get unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }
}
