<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Policy Document</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #28a745; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #28a745; margin: 0; font-size: 24px; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 15px 0; }
        .details { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .important { background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin: 20px 0; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Your Policy Document</h1>
        </div>
        
        <div class="content">
            <p><strong>Dear {{ $customer_name ?? 'Valued Customer' }},</strong></p>
            
            <p>Congratulations! Your insurance policy has been successfully processed. Please find your policy document attached to this email.</p>

            @if(isset($policy_details))
                <div class="details">
                    <h2>Policy Details:</h2>
                    <ul>
                        <li><strong>Policy Number:</strong> {{ $policy_details['policy_number'] ?? 'N/A' }}</li>
                        <li><strong>Policy Type:</strong> {{ $policy_details['policy_type'] ?? 'N/A' }}</li>
                        <li><strong>Coverage Amount:</strong> {{ app_currency_symbol() }}{{ number_format($policy_details['coverage_amount'] ?? 0, 2) }}</li>
                        <li><strong>Start Date:</strong> {{ $policy_details['start_date'] ?? 'N/A' }}</li>
                        <li><strong>End Date:</strong> {{ $policy_details['end_date'] ?? 'N/A' }}</li>
                    </ul>
                </div>
            @endif

            <div class="important">
                <strong>Important:</strong> Please keep your policy document safe and accessible. You may need it for claims or other insurance-related matters.
            </div>

            @if(isset($customer_portal_url))
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ $customer_portal_url }}" class="btn">Access Customer Portal</a>
                </div>
            @endif

            <p>If you have any questions about your policy, please contact our customer service team.</p>
        </div>

        <div class="footer">
            <p>Thanks,<br><strong>{{ config('app.name') }} Team</strong></p>
        </div>
    </div>
</body>
</html>