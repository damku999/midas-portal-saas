<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Your Customer Portal</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #007bff; margin: 0; font-size: 24px; }
        .content h2 { color: #007bff; font-size: 18px; margin-top: 25px; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 15px 0; }
        .features { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .features ul { margin: 0; padding-left: 20px; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 14px; }
        .signature { margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Your Customer Portal!</h1>
        </div>
        
        <div class="content">
            <p><strong>Dear {{ $customer_name ?? 'Valued Customer' }},</strong></p>
            
            <p>Welcome to <strong>{{ company_name() }}</strong> Customer Portal! We're thrilled to have you as part of our family of satisfied customers.</p>

            <p>Your account has been successfully created on <strong>{{ $registration_date ?? format_app_date(now()) }}</strong>. You now have access to our comprehensive customer portal where you can manage all your insurance needs in one convenient location.</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('customer.login') }}" class="btn">Access Your Portal</a>
            </div>
            
            <h2>What You Can Do Now</h2>
            
            <p><strong>Immediate Next Steps:</strong></p>
            <ul>
                @if(isset($next_steps) && is_array($next_steps))
                    @foreach($next_steps as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                @else
                    <li>Complete your profile with additional details</li>
                    <li>Verify your email address if not already done</li>
                    <li>Request your first insurance quotation</li>
                @endif
            </ul>
            
            <div class="features">
                <h2>Portal Features Available to You:</h2>
                <ul>
                    <li><strong>View Policies:</strong> Access all your insurance policies and coverage details</li>
                    <li><strong>Track Renewals:</strong> Monitor policy renewal dates and payment schedules</li>
                    <li><strong>Download Documents:</strong> Access certificates, policy documents, and receipts</li>
                    <li><strong>Get Quotations:</strong> Request new insurance quotes for various coverage types</li>
                    <li><strong>Family Management:</strong> Add family members and manage their policies together</li>
                    <li><strong>Direct Support:</strong> Contact our expert team directly through the portal</li>
                </ul>
            </div>
            
            <h2>Why Choose Us?</h2>
            <ul>
                <li>✓ <strong>Expert Guidance:</strong> Personalized insurance advice from qualified professionals</li>
                <li>✓ <strong>Comprehensive Coverage:</strong> Wide range of insurance products to meet your needs</li>
                <li>✓ <strong>Digital Convenience:</strong> Manage everything online with our modern portal</li>
                <li>✓ <strong>Dedicated Support:</strong> Professional assistance whenever you need it</li>
                <li>✓ <strong>Competitive Rates:</strong> Best prices from trusted insurance providers</li>
            </ul>
            
            <h2>Need Assistance?</h2>
            <p>Our customer support team is ready to help you get started.</p>
            <ul>
                @if(isset($support_contact))
                    <li>📧 Support Email: {{ $support_contact }}</li>
                @endif
                <li>📞 Customer Care: Available during business hours</li>
                <li>💬 Live Chat: Through your customer portal</li>
            </ul>
            
            <p>We're committed to providing you with excellent service and comprehensive insurance solutions. Welcome aboard!</p>
        </div>
        
        <div class="footer">
            <div class="signature">
                Best regards,<br>
                <strong>{{ company_advisor_name() }}</strong><br>
                Insurance Advisor<br>
                {{ company_title() }}
            </div>
            <br>
            <p><small>This welcome email was sent because an account was created for {{ $customer_name ?? 'you' }} in our customer portal. If you believe this was sent in error, please contact our support team immediately.</small></p>
        </div>
    </div>
</body>
</html>