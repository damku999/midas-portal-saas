<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usage Warning Alert</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px solid #f59e0b; padding-bottom: 20px; margin-bottom: 30px; background-color: #fffbeb; padding: 20px; border-radius: 8px; }
        .header h1 { color: #d97706; margin: 0; font-size: 24px; }
        .header .icon { font-size: 48px; margin-bottom: 10px; }
        .alert-box { background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .usage-stats { background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .usage-stats table { width: 100%; border-collapse: collapse; }
        .usage-stats td { padding: 10px 5px; border-bottom: 1px solid #d1d5db; }
        .usage-stats td:first-child { font-weight: bold; color: #4b5563; }
        .usage-stats td:last-child { text-align: right; color: #1f2937; font-weight: 600; }
        .progress-bar { background: #e5e7eb; height: 25px; border-radius: 12px; overflow: hidden; margin: 10px 0; }
        .progress-fill { background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%); height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px; }
        .btn { display: inline-block; padding: 12px 30px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 15px 0; }
        .btn:hover { background-color: #1d4ed8; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 14px; }
        .recommendation { background: #eff6ff; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .recommendation h3 { margin-top: 0; color: #1e40af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">⚠️</div>
            <h1>Usage Warning Alert</h1>
        </div>

        <div class="content">
            <p><strong>Dear {{ $company_name }} Team,</strong></p>

            <div class="alert-box">
                <p style="margin: 0; font-size: 16px;">
                    Your <strong>{{ $resource_type }}</strong> usage has reached <strong>{{ $usage_percentage }}%</strong> of your plan limit.
                </p>
            </div>

            <p>This is a friendly reminder that you're approaching your {{ $plan_name }} plan limits.</p>

            <div class="usage-stats">
                <h3 style="margin-top: 0; color: #1f2937;">Current Usage Details</h3>
                <table>
                    <tr>
                        <td>Resource Type:</td>
                        <td>{{ $resource_type }}</td>
                    </tr>
                    <tr>
                        <td>Current Usage:</td>
                        <td>{{ $usage_display }}</td>
                    </tr>
                    <tr>
                        <td>Threshold Level:</td>
                        <td><span style="color: #d97706; font-weight: bold;">{{ $threshold_level }}</span></td>
                    </tr>
                    <tr>
                        <td>Your Plan:</td>
                        <td>{{ $plan_name }}</td>
                    </tr>
                </table>

                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $usage_percentage }}%;">
                        {{ $usage_percentage }}%
                    </div>
                </div>
            </div>

            <div class="recommendation">
                <h3>💡 What You Can Do</h3>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Monitor your usage closely over the next few days</li>
                    <li>Review and optimize your current {{ strtolower($resource_type) }} if possible</li>
                    <li>Consider upgrading to a higher plan to avoid service interruptions</li>
                    <li>Contact our support team for guidance on usage optimization</li>
                </ul>
            </div>

            <p><strong>Note:</strong> You'll receive additional notifications if your usage reaches 90% (Critical) or 100% (Limit Exceeded). At 100%, you'll have a 3-day grace period before resource creation is restricted.</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $upgrade_url }}" class="btn">Upgrade Your Plan</a>
            </div>

            <p style="color: #6b7280; font-size: 14px;">
                If you have any questions or need assistance, please contact our support team.
            </p>
        </div>

        <div class="footer">
            <p><strong>Midas Portal</strong><br>
            This is an automated notification from your subscription management system.</p>
            <p style="font-size: 12px; color: #9ca3af; margin-top: 10px;">
                You received this email because your account usage has reached a monitored threshold.
            </p>
        </div>
    </div>
</body>
</html>
