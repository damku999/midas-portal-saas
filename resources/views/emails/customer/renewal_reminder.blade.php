<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Renewal Reminder</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #ffc107; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #ffc107; margin: 0; font-size: 24px; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 15px 0; }
        .details { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .benefits { background-color: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Policy Renewal Reminder</h1>
        </div>
        
        <div class="content">
            <p><strong>Dear {{ $customer_name ?? 'Valued Customer' }},</strong></p>
            
            <p>This is a friendly reminder that your insurance policy is due for renewal soon.</p>

            @if(isset($policy_details))
                <div class="details">
                    <h2>Policy Details:</h2>
                    <ul>
                        <li><strong>Policy Number:</strong> {{ $policy_details['policy_number'] ?? 'N/A' }}</li>
                        <li><strong>Policy Type:</strong> {{ $policy_details['policy_type'] ?? 'N/A' }}</li>
                        <li><strong>Current Expiry Date:</strong> {{ $policy_details['expiry_date'] ?? 'N/A' }}</li>
                        <li><strong>Days Until Expiry:</strong> {{ $policy_details['days_until_expiry'] ?? 'N/A' }}</li>
                    </ul>
                </div>
            @endif

            <p>To avoid any lapse in coverage, please renew your policy before the expiry date. Our team is ready to assist you with the renewal process and help you with the best available options.</p>

            @if(isset($renewal_url))
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ $renewal_url }}" class="btn">Renew Policy Now</a>
                </div>
            @endif

            <div class="benefits">
                <h3>Benefits of Early Renewal:</h3>
                <ul>
                    <li>No lapse in coverage</li>
                    <li>Continued protection for you and your family</li>
                    <li>Potential discounts for early renewal</li>
                    <li>Seamless continuation of benefits</li>
                </ul>
            </div>

            <p>If you have any questions or need assistance with renewal, please contact our customer service team.</p>
        </div>

        <div class="footer">
            <p>Thanks,<br><strong>{{ company_name() }} Team</strong></p>
        </div>
    </div>
</body>
</html>