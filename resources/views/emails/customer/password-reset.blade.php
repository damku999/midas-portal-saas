<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Request</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #dc3545; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #dc3545; margin: 0; font-size: 24px; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 15px 0; }
        .security-info { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 14px; }
        .url-copy { background-color: #f8f9fa; padding: 10px; border-radius: 5px; word-break: break-all; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Reset Request</h1>
        </div>
        
        <div class="content">
            <p><strong>Dear {{ $customer->name ?? 'Valued Customer' }},</strong></p>
            
            <p>We received a request to reset the password for your Customer Portal account associated with <strong>{{ $customer->email }}</strong>.</p>

            <p>To complete the password reset process, please click the button below:</p>

            @if(isset($resetUrl))
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ $resetUrl }}" class="btn">Reset My Password</a>
                </div>
            @endif

            <div class="security-info">
                <h3>Important Security Information:</h3>
                <ul>
                    <li>This password reset link will expire in <strong>60 minutes</strong> for your security</li>
                    <li>If you did not request this password reset, please ignore this email - no action is required</li>
                    <li>For your security, never share this reset link with anyone</li>
                </ul>
            </div>

            @if(isset($resetUrl))
                <p>If you're having trouble with the button above, you can also copy and paste the following link into your web browser:</p>
                <div class="url-copy">{{ $resetUrl }}</div>
            @endif

            <hr style="margin: 30px 0; border: 1px solid #ddd;">

            <p><strong>Need assistance?</strong> Our team is here to help with your insurance needs and account questions.</p>
        </div>

        <div class="footer">
            <p>Best regards,<br>
            <strong>{{ company_advisor_name() }}</strong><br>
            Insurance Advisor<br>
            {{ company_name() }}</p>
            
            <p style="font-size: 12px; color: #999; margin-top: 20px;">
                This is an automated security email from your Customer Portal. If you have any concerns about your account security, please contact our support team immediately.
            </p>
        </div>
    </div>
</body>
</html>
