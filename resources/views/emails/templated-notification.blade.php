<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $companyName }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px;
            text-align: center;
            color: #ffffff;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px 25px;
        }
        .email-content {
            font-size: 15px;
            line-height: 1.8;
            color: #555555;
        }
        .email-content strong {
            color: #333333;
            font-weight: 600;
        }
        .email-content a {
            color: #667eea;
            text-decoration: none;
        }
        .email-content a:hover {
            text-decoration: underline;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px 25px;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }
        .footer-info {
            font-size: 13px;
            color: #6c757d;
            margin: 5px 0;
        }
        .footer-contact {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
        .footer-contact p {
            margin: 5px 0;
            font-size: 13px;
            color: #6c757d;
        }
        .footer-contact a {
            color: #667eea;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 4px;
            }
            .email-header {
                padding: 20px 15px;
            }
            .email-header h1 {
                font-size: 20px;
            }
            .email-body {
                padding: 20px 15px;
            }
            .email-footer {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>{{ $companyName }}</h1>
            <p>{{ $companyAdvisor ?? 'Your Insurance Advisor' }}</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="email-content">
                {!! $htmlContent !!}
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-info">
                <p style="margin: 0; font-weight: 600; color: #495057;">{{ $companyName }}</p>
                @if(isset($companyWebsite))
                    <p style="margin: 5px 0;">
                        <a href="{{ $companyWebsite }}" target="_blank">{{ $companyWebsite }}</a>
                    </p>
                @endif
            </div>

            <div class="footer-contact">
                @if(isset($companyPhone))
                    <p><strong>Phone:</strong> {{ $companyPhone }}</p>
                @endif
                <p style="margin-top: 10px; font-size: 12px; color: #868e96;">
                    This is an automated notification. Please do not reply to this email.
                </p>
            </div>

            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #dee2e6;">
                <p style="margin: 0; font-size: 11px; color: #adb5bd;">
                    © {{ date('Y') }} {{ $companyName }}. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
