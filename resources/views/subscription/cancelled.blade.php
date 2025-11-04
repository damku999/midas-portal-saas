<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Cancelled - {{ config('app.name') }}</title>

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
            color: #6c757d;
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
            background: #f8f9fa;
            border-left: 4px solid var(--webmonks-teal);
            border-radius: 12px;
            padding: 25px 30px;
            margin: 30px 0;
            text-align: left;
        }

        .info-box h5 {
            color: var(--webmonks-gray);
            font-weight: 600;
            margin-bottom: 20px;
        }

        .info-box h5 i {
            color: var(--webmonks-teal);
            margin-right: 10px;
        }

        .info-box p {
            color: #555;
            margin-bottom: 15px;
            line-height: 1.7;
            padding-left: 10px;
        }

        .info-box strong {
            color: var(--webmonks-gray);
        }

        .btn-reactivate {
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
            margin: 10px;
        }

        .btn-reactivate:hover {
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
            <i class="fas fa-times-circle"></i>
        </div>

        <h1>Subscription Cancelled</h1>

        <p class="lead-text">
            Your organization's subscription has been cancelled. Access to the portal is no longer available.
        </p>

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="info-box">
            <h5><i class="fas fa-info-circle"></i>What happens now?</h5>
            <p>
                <strong>Your data is safe:</strong> All your data remains securely stored in our system for 30 days after cancellation.
            </p>
            <p>
                <strong>Reactivation available:</strong> You can reactivate your subscription anytime within this period to regain full access.
            </p>
            <p>
                <strong>Data export:</strong> Contact support if you need to export your data before the retention period ends.
            </p>
        </div>

        <div>
            <a href="mailto:support@midastech.in?subject=Reactivate Subscription" class="btn-reactivate">
                <i class="fas fa-redo me-2"></i>Reactivate Subscription
            </a>
        </div>

        <div class="contact-info">
            <p><strong>Questions about your cancellation?</strong></p>
            <p>Email: support@midastech.in</p>
            <p>We're here to help with reactivation or data export requests</p>
        </div>
    </div>
</body>
</html>
