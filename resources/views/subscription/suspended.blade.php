<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Suspended - {{ config('app.name') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/jpg" href="{{ asset('images/logo-icon@2000x.png') }}" />

    <!-- Bootstrap 5 CSS -->
    <link href="{{ cdn_url('cdn_bootstrap_css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ cdn_url('cdn_fontawesome_css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css') }}">

    <!-- Google Fonts - Inter -->
    <link href="{{ cdn_url('cdn_google_fonts_inter', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap') }}" rel="stylesheet">

    <style>
        :root {
            {{ theme_styles() }}
            --webmonks-teal: #17a2b8;
            --webmonks-teal-light: #5fd0e3;
            --webmonks-gray: #555555;
        }

        body {
            font-family: {{ theme_primary_font() }}, 'Inter', sans-serif;
            background: linear-gradient(135deg, {{ theme_color('light') }} 0%, #e8f4f8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .status-container {
            max-width: 650px;
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            text-align: center;
            border-top: 5px solid var(--webmonks-teal);
        }

        .logo-container {
            margin-bottom: 30px;
        }

        .logo-container img {
            max-width: 300px;
            height: auto;
        }

        .status-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 25px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(0.95); }
        }

        h1 {
            color: var(--webmonks-gray);
            font-size: 32px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .lead-text {
            color: #666;
            font-size: 17px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .reason-box {
            background: #f8f9fa;
            border-left: 4px solid var(--webmonks-teal);
            border-radius: 12px;
            padding: 25px 30px;
            margin: 30px 0;
            text-align: left;
        }

        .reason-box strong {
            color: var(--webmonks-gray);
            font-weight: 600;
            display: block;
            margin-bottom: 15px;
        }

        .reason-box ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .reason-box li {
            padding: 10px 0;
            color: #555;
            border-bottom: 1px solid #e9ecef;
        }

        .reason-box li:last-child {
            border-bottom: none;
        }

        .reason-box li i {
            color: var(--webmonks-teal);
            margin-right: 12px;
            width: 20px;
        }

        .btn-support {
            background: linear-gradient(135deg, var(--webmonks-teal) 0%, var(--webmonks-teal-light) 100%);
            border: none;
            color: white;
            padding: 14px 35px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }

        .btn-support:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);
            color: white;
        }

        .contact-info {
            margin-top: 35px;
            padding-top: 30px;
            border-top: 2px solid #f1f3f5;
        }

        .contact-info p {
            color: #999;
            font-size: 14px;
            margin: 5px 0;
        }

        .contact-info strong {
            color: var(--webmonks-gray);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <!-- WebMonks Logo -->
        <div class="logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="WebMonks Technologies">
        </div>

        <!-- Status Icon -->
        <div class="status-icon">
            <i class="fas fa-pause-circle"></i>
        </div>

        <h1>Subscription Suspended</h1>

        <p class="lead-text">
            Your organization's subscription has been temporarily suspended. Access to the portal is currently restricted.
        </p>

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="reason-box">
            <strong>Common reasons for suspension:</strong>
            <ul>
                <li><i class="fas fa-credit-card"></i> Payment method declined or expired</li>
                <li><i class="fas fa-file-invoice-dollar"></i> Outstanding invoice payment required</li>
                <li><i class="fas fa-user-edit"></i> Billing information update needed</li>
                <li><i class="fas fa-shield-alt"></i> Account verification pending</li>
            </ul>
        </div>

        <a href="mailto:support@midastech.in" class="btn-support">
            <i class="fas fa-envelope me-2"></i>Contact Support to Reactivate
        </a>

        <div class="contact-info">
            <p><strong>Need immediate assistance?</strong></p>
            <p>Email: support@midastech.in</p>
            <p>Our team typically responds within 24 hours</p>
        </div>
    </div>
</body>
</html>
