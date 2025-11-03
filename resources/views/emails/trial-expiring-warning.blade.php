<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Expiring Soon - {{ $companyName }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #ff9800; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #ff9800; margin: 0; font-size: 24px; }
        .warning-box { background-color: #fff3cd; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .warning-box strong { color: #856404; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 15px 0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .info-table td:first-child { font-weight: bold; width: 40%; color: #666; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Trial Expiring Soon</h1>
        </div>

        <div class="content">
            <p><strong>Dear {{ $companyName }},</strong></p>

            <div class="warning-box">
                <strong>Important Notice:</strong> Your trial period is expiring in 3 days!
            </div>

            <p>We wanted to remind you that your trial subscription will be expiring soon. Here are the details:</p>

            <table class="info-table">
                <tr>
                    <td>Company Name:</td>
                    <td>{{ $companyName }}</td>
                </tr>
                <tr>
                    <td>Trial Ends:</td>
                    <td>{{ $trialEndsAt->format('F d, Y \a\t h:i A') }}</td>
                </tr>
                <tr>
                    <td>Days Remaining:</td>
                    <td><strong style="color: #ff9800;">3 Days</strong></td>
                </tr>
                <tr>
                    <td>Your Domain:</td>
                    <td>{{ $domain }}</td>
                </tr>
            </table>

            <p><strong>What happens after trial expiration?</strong></p>
            <ul>
                <li>Your account will be automatically suspended</li>
                <li>Access to the system will be temporarily restricted</li>
                <li>Your data will remain safe and secure</li>
                <li>You can reactivate anytime by upgrading to a paid plan</li>
            </ul>

            <p><strong>Don't let your service be interrupted!</strong> Upgrade now to continue enjoying uninterrupted access to all features.</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="https://{{ config('app.central_domain') }}/upgrade" class="btn">Upgrade Now</a>
            </div>

            <p>If you have any questions or need assistance with upgrading, please contact our support team. We're here to help!</p>
        </div>

        <div class="footer">
            <p>Thanks,<br><strong>Midas Portal Team</strong></p>
            <p style="font-size: 12px; color: #999; margin-top: 10px;">
                This is an automated notification. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
