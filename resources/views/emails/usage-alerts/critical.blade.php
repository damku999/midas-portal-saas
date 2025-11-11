<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Critical Usage Alert</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px solid #ef4444; padding-bottom: 20px; margin-bottom: 30px; background-color: #fef2f2; padding: 20px; border-radius: 8px; }
        .header h1 { color: #dc2626; margin: 0; font-size: 24px; }
        .header .icon { font-size: 48px; margin-bottom: 10px; }
        .alert-box { background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .usage-stats { background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .usage-stats table { width: 100%; border-collapse: collapse; }
        .usage-stats td { padding: 10px 5px; border-bottom: 1px solid #d1d5db; }
        .usage-stats td:first-child { font-weight: bold; color: #4b5563; }
        .usage-stats td:last-child { text-align: right; color: #1f2937; font-weight: 600; }
        .progress-bar { background: #e5e7eb; height: 25px; border-radius: 12px; overflow: hidden; margin: 10px 0; }
        .progress-fill { background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%); height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px; }
        .btn { display: inline-block; padding: 12px 30px; background-color: #dc2626; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 15px 0; }
        .btn:hover { background-color: #b91c1c; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 14px; }
        .action-required { background: #fef2f2; border: 2px solid #ef4444; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center; }
        .action-required h3 { margin-top: 0; color: #dc2626; font-size: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">🚨</div>
            <h1>Critical Usage Alert</h1>
        </div>

        <div class="content">
            <p><strong>Dear {{ $company_name }} Team,</strong></p>

            <div class="alert-box">
                <p style="margin: 0; font-size: 16px; color: #dc2626;">
                    <strong>CRITICAL:</strong> Your <strong>{{ $resource_type }}</strong> usage has reached <strong>{{ $usage_percentage }}%</strong> of your plan limit!
                </p>
            </div>

            <div class="action-required">
                <h3>⚠️ Immediate Action Required</h3>
                <p style="margin: 10px 0;">You are approaching your {{ $resource_type }} limit. Service restrictions may apply once you reach 100%.</p>
            </div>

            <div class="usage-stats">
                <h3 style="margin-top: 0; color: #1f2937;">Current Usage Details</h3>
                <table>
                    <tr>
                        <td>Resource Type:</td>
                        <td>{{ $resource_type }}</td>
                    </tr>
                    <tr>
                        <td>Current Usage:</td>
                        <td><span style="color: #dc2626; font-weight: bold;">{{ $usage_display }}</span></td>
                    </tr>
                    <tr>
                        <td>Threshold Level:</td>
                        <td><span style="color: #dc2626; font-weight: bold;">{{ $threshold_level }}</span></td>
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

            <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <h3 style="margin-top: 0; color: #92400e;">⏰ What Happens Next?</h3>
                <ul style="margin: 10px 0; padding-left: 20px; color: #78350f;">
                    <li><strong>At 100%:</strong> You'll receive a final alert and enter a 3-day grace period</li>
                    <li><strong>During Grace Period:</strong> All features remain accessible</li>
                    <li><strong>After Grace Period:</strong> New {{ strtolower($resource_type) }} creation will be restricted until you upgrade or reduce usage</li>
                </ul>
            </div>

            <div style="background: #eff6ff; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <h3 style="margin-top: 0; color: #1e40af;">📈 Recommended Actions</h3>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li><strong>Upgrade Now:</strong> Increase your limits by upgrading to a higher plan</li>
                    <li><strong>Optimize Usage:</strong> Review and remove unused {{ strtolower($resource_type) }}</li>
                    <li><strong>Contact Support:</strong> Our team can help you find the best solution</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $upgrade_url }}" class="btn">Upgrade Now to Avoid Restrictions</a>
            </div>

            <p style="background: #fef2f2; padding: 15px; border-radius: 6px; font-size: 14px; color: #7f1d1d;">
                <strong>⚠️ Important:</strong> To ensure uninterrupted service, please take action before reaching 100% usage. Once the grace period ends, you won't be able to create new {{ strtolower($resource_type) }} until you upgrade your plan.
            </p>

            <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
                Need help? Contact our support team at any time for assistance.
            </p>
        </div>

        <div class="footer">
            <p><strong>Midas Portal</strong><br>
            This is an urgent automated notification from your subscription management system.</p>
            <p style="font-size: 12px; color: #9ca3af; margin-top: 10px;">
                You received this email because your account usage has reached a critical threshold.
            </p>
        </div>
    </div>
</body>
</html>
