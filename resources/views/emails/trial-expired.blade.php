<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Expired - Action Required - {{ $companyName }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #dc3545; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #dc3545; margin: 0; font-size: 24px; }
        .alert-box { background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .alert-box strong { color: #721c24; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 15px 0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .info-table td:first-child { font-weight: bold; width: 40%; color: #666; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 14px; }
        .feature-list { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .feature-list h3 { margin-top: 0; color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 Trial Period Expired</h1>
        </div>

        <div class="content">
            <p><strong>Dear {{ $companyName }},</strong></p>

            <div class="alert-box">
                <strong>Action Required:</strong> Your trial period has expired and your account has been suspended.
            </div>

            <p>Your trial subscription expired on <strong>{{ $trialEndsAt->format('F d, Y \a\t h:i A') }}</strong>. As a result, your account has been automatically suspended.</p>

            <table class="info-table">
                <tr>
                    <td>Company Name:</td>
                    <td>{{ $companyName }}</td>
                </tr>
                <tr>
                    <td>Trial Expired:</td>
                    <td><strong style="color: #dc3545;">{{ $trialEndsAt->format('F d, Y') }}</strong></td>
                </tr>
                <tr>
                    <td>Account Status:</td>
                    <td><span style="color: #dc3545; font-weight: bold;">Suspended</span></td>
                </tr>
                <tr>
                    <td>Your Domain:</td>
                    <td>{{ $domain }}</td>
                </tr>
            </table>

            <p><strong>What this means:</strong></p>
            <ul>
                <li>Access to your account is currently restricted</li>
                <li>Your team members cannot log in</li>
                <li>All your data remains safe and secure</li>
                <li>No data has been deleted</li>
            </ul>

            <div class="feature-list">
                <h3>Restore Access Immediately</h3>
                <p>Upgrade to a paid plan now to:</p>
                <ul>
                    <li>✅ Restore full access to your account</li>
                    <li>✅ Resume all operations without data loss</li>
                    <li>✅ Access all premium features</li>
                    <li>✅ Get priority support</li>
                    <li>✅ Enjoy uninterrupted service</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="https://{{ config('app.central_domain') }}/upgrade" class="btn">Upgrade & Restore Access</a>
            </div>

            <p><strong>Need help deciding?</strong> Contact our sales team for a personalized plan recommendation or to discuss custom pricing options.</p>

            <p style="margin-top: 30px;">If you have any questions or need assistance, please reach out to our support team. We're here to help you get back on track!</p>
        </div>

        <div class="footer">
            <p>Thanks,<br><strong>Midas Portal Team</strong></p>
            <p style="font-size: 12px; color: #999; margin-top: 10px;">
                This is an automated notification. Please do not reply to this email.<br>
                For support, contact us at <a href="mailto:support@midastech.in">support@midastech.in</a>
            </p>
        </div>
    </div>
</body>
</html>
