<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Required - {{ config('app.name') }}</title>

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
            color: #ffc107;
            margin-bottom: 25px;
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

        .info-box {
            background: #fff8e1;
            border-left: 4px solid var(--webmonks-teal);
            border-radius: 12px;
            padding: 25px 30px;
            margin: 30px 0;
            text-align: left;
        }

        .info-box p {
            color: #555;
            margin-bottom: 10px;
            line-height: 1.7;
        }

        .info-box p i {
            color: var(--webmonks-teal);
            margin-right: 10px;
        }

        .info-box strong {
            color: var(--webmonks-gray);
        }

        .info-box ul {
            margin-top: 15px;
            padding-left: 25px;
        }

        .info-box li {
            color: #666;
            margin-bottom: 8px;
        }

        .btn-contact {
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

        .btn-contact:hover {
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
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <h1>No Active Subscription</h1>

        <p class="lead-text">
            This organization does not have an active subscription to access the portal.
        </p>

        @if(session('error'))
            <div class="alert alert-warning">
                <i class="fas fa-info-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="info-box">
            <p>
                <i class="fas fa-info-circle"></i>
                <strong>Access to this portal requires an active subscription.</strong>
            </p>
            <p>
                This could mean:
            </p>
            <ul>
                <li>Your organization's subscription has not been set up yet</li>
                <li>The subscription may have expired</li>
                <li>There was an issue with subscription activation</li>
            </ul>
        </div>

        <a href="mailto:support@midastech.in?subject=Subscription Setup Required" class="btn-contact">
            <i class="fas fa-envelope me-2"></i>Contact Administrator
        </a>

        <div class="contact-info">
            <p><strong>Need help setting up your subscription?</strong></p>
            <p>Email: support@midastech.in</p>
            <p>Please contact your system administrator or our support team</p>
        </div>
    </div>
</body>
</html>
