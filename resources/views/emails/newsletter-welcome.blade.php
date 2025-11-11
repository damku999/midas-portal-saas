<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Midas Portal Newsletter</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #17b6b6 0%, #13918e 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .button {
            display: inline-block;
            background: #17b6b6;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Welcome to Midas Portal!</h1>
    </div>

    <div class="content">
        <h2>Thank you for subscribing! 🎉</h2>

        <p>Welcome to the Midas Portal Newsletter! We're excited to have you join our community of insurance professionals.</p>

        <p><strong>Here's what you can expect from us:</strong></p>
        <ul>
            <li>📊 Latest insurance industry insights and trends</li>
            <li>💡 Tips and tricks for managing your insurance agency</li>
            <li>🚀 Product updates and new feature announcements</li>
            <li>📝 Comprehensive guides on claims, policies, and add-ons</li>
            <li>🎓 Best practices for customer management and retention</li>
        </ul>

        <p>We promise to deliver valuable content directly to your inbox, helping you stay ahead in the insurance industry.</p>

        <div style="text-align: center;">
            <a href="{{ url('/blog') }}" class="button">Explore Our Blog</a>
        </div>

        <p>If you have any questions or suggestions for topics you'd like us to cover, feel free to reach out to us at <a href="mailto:Info@midastech.in">Info@midastech.in</a></p>
    </div>

    <div class="footer">
        <p><strong>Midas Portal by WebMonks Technologies</strong></p>
        <p>C243, Second Floor, SoBo Center, Gala Gymkhana Road, South Bopal<br>
        Ahmedabad - 380058, Gujarat, India</p>
        <p>© {{ date('Y') }} Midas Portal. All rights reserved.</p>
        <p style="font-size: 12px; color: #999;">
            You're receiving this email because you subscribed to our newsletter at {{ url('/') }}
        </p>
    </div>
</body>
</html>
