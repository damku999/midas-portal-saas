<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Insurance Quotation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #28a745; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #28a745; margin: 0; font-size: 24px; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 15px 0; }
        .details { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Your Insurance Quotation</h1>
        </div>
        
        <div class="content">
            <p><strong>Dear {{ $customer_name ?? 'Valued Customer' }},</strong></p>
            
            <p>Thank you for requesting an insurance quotation from <strong>{{ company_name() }}</strong>. Please find your personalized quote attached to this email.</p>

            @if(isset($quotation_details))
                <div class="details">
                    <h2>Quotation Details:</h2>
                    <ul>
                        <li><strong>Quotation Number:</strong> {{ $quotation_details['quotation_number'] ?? 'N/A' }}</li>
                        <li><strong>Policy Type:</strong> {{ $quotation_details['policy_type'] ?? 'N/A' }}</li>
                        <li><strong>Premium Amount:</strong> ₹{{ number_format($quotation_details['premium_amount'] ?? 0, 2) }}</li>
                        <li><strong>Valid Until:</strong> {{ $quotation_details['validity_date'] ?? 'N/A' }}</li>
                    </ul>
                </div>
            @endif

            @if(isset($view_quotation_url))
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ $view_quotation_url }}" class="btn">View Quotation Online</a>
                </div>
            @endif

            <p>If you have any questions about your quotation, please don't hesitate to contact our team.</p>
        </div>

        <div class="footer">
            <p>Thanks,<br><strong>{{ company_name() }} Team</strong></p>
        </div>
    </div>
</body>
</html>