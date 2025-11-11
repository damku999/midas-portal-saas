<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usage Limit Exceeded</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 3px solid #dc2626; padding-bottom: 20px; margin-bottom: 30px; background-color: #fef2f2; padding: 20px; border-radius: 8px; }
        .header h1 { color: #991b1b; margin: 0; font-size: 24px; }
        .header .icon { font-size: 48px; margin-bottom: 10px; }
        .alert-box { background-color: #fef2f2; border: 2px solid #dc2626; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center; }
        .alert-box h2 { color: #991b1b; margin: 0 0 10px 0; font-size: 22px; }
        .usage-stats { background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .usage-stats table { width: 100%; border-collapse: collapse; }
        .usage-stats td { padding: 10px 5px; border-bottom: 1px solid #d1d5db; }
        .usage-stats td:first-child { font-weight: bold; color: #4b5563; }
        .usage-stats td:last-child { text-align: right; color: #1f2937; font-weight: 600; }
        .progress-bar { background: #e5e7eb; height: 25px; border-radius: 12px; overflow: hidden; margin: 10px 0; }
        .progress-fill { background: linear-gradient(90deg, #dc2626 0%, #991b1b 100%); height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px; }
        .btn { display: inline-block; padding: 14px 35px; background-color: #dc2626; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 15px 0; font-size: 16px; }
        .btn:hover { background-color: #b91c1c; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 14px; }
        .grace-period { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 2px solid #dc2626; padding: 20px; margin: 20px 0; border-radius: 8px; text-align: center; }
        .grace-period h3 { margin-top: 0; color: #991b1b; font-size: 20px; }
        .grace-period .countdown { font-size: 36px; color: #dc2626; font-weight: bold; margin: 10px 0; }
        .timeline { background: #fff; border: 1px solid #e5e7eb; padding: 20px; margin: 20px 0; border-radius: 8px; }
        .timeline-item { display: flex; align-items: flex-start; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e5e7eb; }
        .timeline-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .timeline-icon { font-size: 24px; margin-right: 15px; }
        .timeline-content h4 { margin: 0 0 5px 0; color: #1f2937; }
        .timeline-content p { margin: 0; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">⛔</div>
            <h1>Usage Limit Exceeded</h1>
        </div>

        <div class="content">
            <p><strong>Dear {{ $company_name }} Team,</strong></p>

            <div class="alert-box">
                <h2>🚫 Limit Reached</h2>
                <p style="margin: 0; font-size: 16px; color: #991b1b;">
                    Your <strong>{{ $resource_type }}</strong> usage has reached <strong>100%</strong> of your {{ $plan_name }} plan limit.
                </p>
            </div>

            <div class="grace-period">
                <h3>⏰ Grace Period Active</h3>
                <div class="countdown">3 Days</div>
                <p style="margin: 0; color: #7f1d1d; font-size: 15px;">
                    You have a 3-day grace period to upgrade your plan or reduce usage.<br>
                    All features remain accessible during this period.
                </p>
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
                        <td>Status:</td>
                        <td><span style="color: #dc2626; font-weight: bold;">⛔ Limit Exceeded</span></td>
                    </tr>
                    <tr>
                        <td>Your Plan:</td>
                        <td>{{ $plan_name }}</td>
                    </tr>
                </table>

                <div class="progress-bar">
                    <div class="progress-fill" style="width: 100%;">
                        100% - LIMIT REACHED
                    </div>
                </div>
            </div>

            <div class="timeline">
                <h3 style="margin-top: 0; color: #1f2937;">📅 What Happens Next?</h3>

                <div class="timeline-item">
                    <div class="timeline-icon">✅</div>
                    <div class="timeline-content">
                        <h4>Now - Day 3 (Grace Period)</h4>
                        <p>All features remain fully accessible. You can continue using the system normally.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-icon">⚠️</div>
                    <div class="timeline-content">
                        <h4>After Day 3</h4>
                        <p>New {{ strtolower($resource_type) }} creation will be restricted. Existing {{ strtolower($resource_type) }} remain accessible.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-icon">🔓</div>
                    <div class="timeline-content">
                        <h4>After Upgrade</h4>
                        <p>All restrictions are immediately lifted. You can create new {{ strtolower($resource_type) }} without limits.</p>
                    </div>
                </div>
            </div>

            <div style="background: #eff6ff; border-left: 4px solid #2563eb; padding: 20px; margin: 20px 0; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #1e40af;">💡 Your Options</h3>

                <div style="margin: 15px 0;">
                    <h4 style="margin: 0 0 8px 0; color: #1f2937;">Option 1: Upgrade Your Plan (Recommended)</h4>
                    <p style="margin: 0; color: #4b5563;">Get higher limits instantly and unlock additional features. Choose from our Professional or Enterprise plans.</p>
                </div>

                <div style="margin: 15px 0;">
                    <h4 style="margin: 0 0 8px 0; color: #1f2937;">Option 2: Optimize Current Usage</h4>
                    <p style="margin: 0; color: #4b5563;">Review and remove unused {{ strtolower($resource_type) }} to stay within your current plan limits.</p>
                </div>

                <div style="margin: 15px 0;">
                    <h4 style="margin: 0 0 8px 0; color: #1f2937;">Option 3: Contact Support</h4>
                    <p style="margin: 0; color: #4b5563;">Our team can help you find the best solution for your specific needs.</p>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $upgrade_url }}" class="btn">Upgrade Now - Avoid Restrictions</a>
            </div>

            <div style="background: #fef2f2; border: 2px solid #dc2626; padding: 20px; margin: 20px 0; border-radius: 8px;">
                <h3 style="margin-top: 0; color: #991b1b;">⚠️ Important Information</h3>
                <ul style="margin: 10px 0; padding-left: 20px; color: #7f1d1d;">
                    <li><strong>Grace Period:</strong> You have 3 days from today to take action</li>
                    <li><strong>During Grace:</strong> All existing features work normally</li>
                    <li><strong>After Grace:</strong> Creating new {{ strtolower($resource_type) }} will be blocked</li>
                    <li><strong>Existing Data:</strong> All your existing {{ strtolower($resource_type) }} remain fully accessible</li>
                    <li><strong>Instant Resolution:</strong> Upgrading immediately lifts all restrictions</li>
                </ul>
            </div>

            <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
                <strong>Need assistance?</strong> Our support team is here to help you choose the right plan and answer any questions you may have.
            </p>
        </div>

        <div class="footer">
            <p><strong>Midas Portal</strong><br>
            This is an urgent automated notification from your subscription management system.</p>
            <p style="font-size: 12px; color: #9ca3af; margin-top: 10px;">
                You received this email because your account has exceeded plan limits.
            </p>
        </div>
    </div>
</body>
</html>
