<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Required</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #007bff; margin: 0; font-size: 24px; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 15px 0; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Email Verification Required</h1>
        </div>
        
        <div class="content">
            <p><strong>Dear {{ $customer_name ?? 'Valued Customer' }},</strong></p>
            
            <p>Thank you for registering with <strong>{{ company_name() }}</strong>. To complete your account setup, please verify your email address by clicking the button below.</p>

            @if(isset($verification_url))
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ $verification_url }}" class="btn">Verify Email Address</a>
                </div>
            @endif

            <p>This verification link will expire in <strong>{{ $expires_in ?? '24 hours' }}</strong>.</p>

            <p>If you did not create an account with us, please ignore this email.</p>
        </div>

        <div class="footer">
            <p>Thanks,<br><strong>{{ company_name() }} Team</strong></p>
        </div>
    </div>
</body>
</html>