<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }

        .invoice-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .company-logo {
            font-size: 24px;
            font-weight: bold;
            color: #5BC0DE;
        }

        .company-name {
            font-size: 18px;
            color: #666;
            margin-top: 5px;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-badge {
            display: inline-block;
            padding: 5px 15px;
            background: #5CB85C;
            color: white;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .invoice-badge.unpaid {
            background: #F0AD4E;
        }

        .invoice-info {
            text-align: right;
            color: #666;
        }

        .invoice-info div {
            margin-bottom: 5px;
        }

        .parties {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .party-box {
            width: 48%;
            padding: 20px;
            background: #F0F8FF;
            border-radius: 8px;
        }

        .party-box.billed-to {
            background: #E8F5E9;
        }

        .party-title {
            font-size: 16px;
            font-weight: bold;
            color: #5BC0DE;
            margin-bottom: 10px;
        }

        .party-box.billed-to .party-title {
            color: #5CB85C;
        }

        .party-details {
            color: #333;
        }

        .party-details div {
            margin-bottom: 5px;
        }

        .party-details strong {
            display: inline-block;
            width: 80px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table thead {
            background: #5BC0DE;
            color: white;
        }

        .items-table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        .items-table tbody tr:last-child td {
            border-bottom: 2px solid #5BC0DE;
        }

        .text-right {
            text-align: right;
        }

        .total-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .bank-details {
            width: 48%;
            padding: 20px;
            background: #F0F8FF;
            border-radius: 8px;
        }

        .bank-details-title {
            font-size: 14px;
            font-weight: bold;
            color: #5BC0DE;
            margin-bottom: 10px;
        }

        .bank-details table {
            width: 100%;
        }

        .bank-details td {
            padding: 5px 0;
        }

        .bank-details td:first-child {
            font-weight: bold;
            width: 40%;
        }

        .totals {
            width: 48%;
        }

        .totals table {
            width: 100%;
        }

        .totals td {
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }

        .totals td:first-child {
            font-weight: bold;
        }

        .totals td:last-child {
            text-align: right;
        }

        .totals .grand-total {
            font-size: 16px;
            font-weight: bold;
            background: #F8F9FA;
            padding: 12px 0 !important;
            border-top: 2px solid #5BC0DE;
            border-bottom: 2px solid #5BC0DE;
        }

        .amount-in-words {
            margin-bottom: 20px;
            padding: 15px;
            background: #FFF9E6;
            border-left: 4px solid #F0AD4E;
        }

        .tax-summary {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }

        .tax-summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .tax-summary thead {
            background: #5BC0DE;
            color: white;
        }

        .tax-summary th,
        .tax-summary td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .tax-summary .text-right {
            text-align: right;
        }

        .tax-summary tfoot {
            background: #F8F9FA;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            color: #999;
            font-size: 11px;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .notes {
            margin-bottom: 20px;
            padding: 15px;
            background: #F8F9FA;
            border-radius: 4px;
        }

        @media print {
            body {
                margin: 0;
            }
            .invoice-container {
                max-width: 100%;
                margin: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div>
                <div class="company-logo">WebMonks</div>
                <div class="company-name">Technologies</div>
            </div>
            <div class="invoice-title">
                <span class="invoice-badge {{ $invoice->status === 'paid' ? 'paid' : 'unpaid' }}">
                    Invoice {{ $invoice->status === 'paid' ? 'Paid' : 'Unpaid' }}
                </span>
                <div class="invoice-info">
                    <div><strong>Invoice No #</strong> {{ $invoice->invoice_number }}</div>
                    <div><strong>Invoice Date</strong> {{ $invoice->invoice_date->format('M d, Y') }}</div>
                    <div><strong>Due Date</strong> {{ $invoice->due_date->format('M d, Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Parties -->
        <div class="parties">
            <div class="party-box">
                <div class="party-title">Billed By</div>
                <div class="party-details">
                    <div><strong>{{ \App\Services\InvoiceService::COMPANY_NAME }}</strong></div>
                    <div>{{ \App\Services\InvoiceService::COMPANY_ADDRESS }}</div>
                    <div><strong>GSTIN:</strong> {{ \App\Services\InvoiceService::COMPANY_GSTIN }}</div>
                    <div><strong>PAN:</strong> {{ \App\Services\InvoiceService::COMPANY_PAN }}</div>
                    <div><strong>Email:</strong> {{ \App\Services\InvoiceService::COMPANY_EMAIL }}</div>
                    <div><strong>Phone:</strong> {{ \App\Services\InvoiceService::COMPANY_PHONE }}</div>
                </div>
            </div>

            <div class="party-box billed-to">
                <div class="party-title">Billed To</div>
                <div class="party-details">
                    <div><strong>{{ $invoice->customer_name }}</strong></div>
                    @if($invoice->customer_address)
                        <div>{{ $invoice->customer_address }}</div>
                    @endif
                    @if($invoice->customer_gstin)
                        <div><strong>GSTIN:</strong> {{ $invoice->customer_gstin }}</div>
                    @endif
                    @if($invoice->customer_pan)
                        <div><strong>PAN:</strong> {{ $invoice->customer_pan }}</div>
                    @endif
                    @if($invoice->customer_email)
                        <div><strong>Email:</strong> {{ $invoice->customer_email }}</div>
                    @endif
                    @if($invoice->customer_phone)
                        <div><strong>Phone:</strong> {{ $invoice->customer_phone }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>GST Rate</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Amount</th>
                    @if($invoice->cgst_amount > 0)
                        <th class="text-right">CGST</th>
                        <th class="text-right">SGST</th>
                    @else
                        <th class="text-right">IGST</th>
                    @endif
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                    <tr>
                        <td>
                            <div><strong>{{ $item['description'] }}</strong></div>
                            <div style="color: #999; font-size: 11px;">(HSN/SAC: {{ $item['hsn_sac'] }})</div>
                        </td>
                        <td>{{ $invoice->cgst_amount > 0 ? '18%' : '18%' }}</td>
                        <td class="text-right">{{ $item['quantity'] }}</td>
                        <td class="text-right">₹{{ number_format($item['rate'], 2) }}</td>
                        <td class="text-right">₹{{ number_format($item['amount'], 2) }}</td>
                        @if($invoice->cgst_amount > 0)
                            <td class="text-right">₹{{ number_format($invoice->cgst_amount, 2) }}</td>
                            <td class="text-right">₹{{ number_format($invoice->sgst_amount, 2) }}</td>
                        @else
                            <td class="text-right" colspan="2">₹{{ number_format($invoice->igst_amount, 2) }}</td>
                        @endif
                        <td class="text-right"><strong>₹{{ number_format($invoice->total_amount, 2) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Amount in Words -->
        <div class="amount-in-words">
            <strong>Total (in words):</strong> {{ $invoice->getTotalInWords() }}
        </div>

        <!-- Totals and Bank Details -->
        <div class="total-section">
            <div class="bank-details">
                <div class="bank-details-title">Bank Details</div>
                <table>
                    <tr>
                        <td>Account Name</td>
                        <td>{{ \App\Services\InvoiceService::BANK_ACCOUNT_NAME }}</td>
                    </tr>
                    <tr>
                        <td>Account Number</td>
                        <td>{{ \App\Services\InvoiceService::BANK_ACCOUNT_NUMBER }}</td>
                    </tr>
                    <tr>
                        <td>IFSC</td>
                        <td>{{ \App\Services\InvoiceService::BANK_IFSC }}</td>
                    </tr>
                    <tr>
                        <td>SWIFT Code</td>
                        <td>{{ \App\Services\InvoiceService::BANK_SWIFT }}</td>
                    </tr>
                    <tr>
                        <td>Bank</td>
                        <td>{{ \App\Services\InvoiceService::BANK_NAME }}</td>
                    </tr>
                    <tr>
                        <td>MICR Code</td>
                        <td>{{ \App\Services\InvoiceService::BANK_MICR }}</td>
                    </tr>
                </table>
            </div>

            <div class="totals">
                <table>
                    <tr>
                        <td>Amount</td>
                        <td>₹{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->cgst_amount > 0)
                        <tr>
                            <td>CGST</td>
                            <td>₹{{ number_format($invoice->cgst_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>SGST</td>
                            <td>₹{{ number_format($invoice->sgst_amount, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td>IGST</td>
                            <td>₹{{ number_format($invoice->igst_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($invoice->round_off != 0)
                        <tr>
                            <td>Round off</td>
                            <td>₹{{ number_format($invoice->round_off, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="grand-total">
                        <td>Total (INR)</td>
                        <td>₹{{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                    @if($invoice->gateway_charges > 0)
                        <tr>
                            <td>Transaction Charge</td>
                            <td>₹{{ number_format($invoice->gateway_charges, 2) }}</td>
                        </tr>
                        <tr>
                            <td>GST on Charges</td>
                            <td>₹{{ number_format($invoice->gateway_charges_gst, 2) }}</td>
                        </tr>
                        <tr class="grand-total">
                            <td>Final Amount</td>
                            <td>₹{{ number_format($invoice->total_with_gateway_charges, 2) }}</td>
                        </tr>
                    @endif
                    @if($invoice->status === 'paid')
                        <tr>
                            <td>Amount Paid</td>
                            <td style="color: #5CB85C;">₹{{ number_format($invoice->amount_paid, 2) }}</td>
                        </tr>
                    @endif
                    @if($invoice->balance_due > 0)
                        <tr>
                            <td>Balance Due</td>
                            <td style="color: #D9534F;">₹{{ number_format($invoice->balance_due, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Tax Summary -->
        <div class="tax-summary">
            <table>
                <thead>
                    <tr>
                        <th>HSN</th>
                        <th class="text-right">Taxable Value</th>
                        @if($invoice->cgst_amount > 0)
                            <th class="text-right" colspan="2">CGST</th>
                            <th class="text-right" colspan="2">SGST</th>
                        @else
                            <th class="text-right" colspan="2">IGST</th>
                        @endif
                        <th class="text-right">Total</th>
                    </tr>
                    @if($invoice->cgst_amount > 0 || $invoice->igst_amount > 0)
                        <tr>
                            <th></th>
                            <th></th>
                            <th class="text-right">Rate</th>
                            <th class="text-right">Amount</th>
                            @if($invoice->cgst_amount > 0)
                                <th class="text-right">Rate</th>
                                <th class="text-right">Amount</th>
                            @endif
                            <th></th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item['hsn_sac'] }}</td>
                            <td class="text-right">₹{{ number_format($item['amount'], 2) }}</td>
                            @if($invoice->cgst_amount > 0)
                                <td class="text-right">{{ number_format($invoice->cgst_rate, 0) }}%</td>
                                <td class="text-right">₹{{ number_format($invoice->cgst_amount, 2) }}</td>
                                <td class="text-right">{{ number_format($invoice->sgst_rate, 0) }}%</td>
                                <td class="text-right">₹{{ number_format($invoice->sgst_amount, 2) }}</td>
                            @else
                                <td class="text-right">{{ number_format($invoice->igst_rate, 0) }}%</td>
                                <td class="text-right">₹{{ number_format($invoice->igst_amount, 2) }}</td>
                            @endif
                            <td class="text-right">₹{{ number_format($invoice->total_tax, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td><strong>Total</strong></td>
                        <td class="text-right"><strong>₹{{ number_format($invoice->subtotal, 2) }}</strong></td>
                        @if($invoice->cgst_amount > 0)
                            <td></td>
                            <td class="text-right"><strong>₹{{ number_format($invoice->cgst_amount, 2) }}</strong></td>
                            <td></td>
                            <td class="text-right"><strong>₹{{ number_format($invoice->sgst_amount, 2) }}</strong></td>
                        @else
                            <td></td>
                            <td class="text-right"><strong>₹{{ number_format($invoice->igst_amount, 2) }}</strong></td>
                        @endif
                        <td class="text-right"><strong>₹{{ number_format($invoice->total_tax, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="amount-in-words">
            <strong>Total Tax In Words:</strong> {{ $invoice->getTaxInWords() }}
        </div>

        @if($invoice->notes)
            <div class="notes">
                <strong>Notes:</strong><br>
                {{ $invoice->notes }}
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>For any enquiry, reach out via email at {{ \App\Services\InvoiceService::COMPANY_EMAIL }}, call on {{ \App\Services\InvoiceService::COMPANY_PHONE }}</p>
            <p style="margin-top: 10px;">This is an electronically generated document, no signature is required.</p>
        </div>
    </div>
</body>
</html>
