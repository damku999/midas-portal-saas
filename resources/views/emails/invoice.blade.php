<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #5BC0DE;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .invoice-details {
            background: white;
            padding: 15px;
            border-left: 4px solid #5BC0DE;
            margin: 20px 0;
        }
        .invoice-details table {
            width: 100%;
        }
        .invoice-details td {
            padding: 8px 0;
        }
        .invoice-details td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #5CB85C;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .status-unpaid {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">WebMonks Technologies</h1>
        <p style="margin: 10px 0 0 0;">Invoice {{ $invoice->invoice_number }}</p>
    </div>

    <div class="content">
        <p>Dear {{ $invoice->customer_name }},</p>

        <p>Thank you for your business! Please find attached your invoice for the recent payment.</p>

        <div class="invoice-details">
            <table>
                <tr>
                    <td>Invoice Number:</td>
                    <td><strong>{{ $invoice->invoice_number }}</strong></td>
                </tr>
                <tr>
                    <td>Invoice Date:</td>
                    <td>{{ $invoice->invoice_date->format('d M, Y') }}</td>
                </tr>
                <tr>
                    <td>Due Date:</td>
                    <td>{{ $invoice->due_date->format('d M, Y') }}</td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td>
                        <span class="status-badge {{ $invoice->status === 'paid' ? 'status-paid' : 'status-unpaid' }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Amount:</td>
                    <td><strong style="font-size: 18px; color: #5BC0DE;">₹{{ number_format($invoice->total_amount, 2) }}</strong></td>
                </tr>
                @if($invoice->gateway_charges > 0)
                    <tr>
                        <td>Gateway Charges:</td>
                        <td>₹{{ number_format($invoice->gateway_charges + $invoice->gateway_charges_gst, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total Amount:</td>
                        <td><strong style="font-size: 18px; color: #5BC0DE;">₹{{ number_format($invoice->total_with_gateway_charges, 2) }}</strong></td>
                    </tr>
                @endif
            </table>
        </div>

        @if($invoice->status === 'paid')
            <p style="color: #155724; background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;">
                <strong>Payment Received!</strong><br>
                Thank you for your prompt payment. Your invoice has been marked as paid.
            </p>
        @else
            <p style="color: #856404; background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                <strong>Payment Pending</strong><br>
                Please process the payment at your earliest convenience.
            </p>
        @endif

        <p>The invoice PDF is attached to this email for your records. You can also view it online using the button below:</p>

        <center>
            <a href="{{ route('central.invoices.show', $invoice) }}" class="button">
                View Invoice Online
            </a>
        </center>

        @if($invoice->notes)
            <div style="margin-top: 20px; padding: 15px; background: white; border-left: 4px solid #5BC0DE;">
                <strong>Notes:</strong><br>
                {{ $invoice->notes }}
            </div>
        @endif
    </div>

    <div class="content" style="background: white; border: 1px solid #ddd;">
        <h3 style="margin-top: 0; color: #5BC0DE;">Need Help?</h3>
        <p>If you have any questions about this invoice, please contact us:</p>
        <p style="margin: 10px 0;">
            <strong>Email:</strong> darshan@webmonks.in<br>
            <strong>Phone:</strong> +91 80000 71413<br>
            <strong>Address:</strong> 30 Shubh Residancy, Near UGVCL-GEB, Bopal, Ahmedabad, Gujarat - 380058
        </p>
    </div>

    <div class="footer">
        <p><strong>WebMonks Technologies</strong></p>
        <p>GSTIN: 24CFDPB1228P1ZM | PAN: CFDPB1228P</p>
        <p>This is an automatically generated email. Please do not reply to this email.</p>
        <p style="margin-top: 15px;">© {{ date('Y') }} WebMonks Technologies. All rights reserved.</p>
    </div>
</body>
</html>
