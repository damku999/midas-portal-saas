<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Not Found - {{ config('app.name') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/jpg" href="{{ asset('images/logo-icon@2000x.png') }}" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --webmonks-teal: #17a2b8;
            --webmonks-teal-dark: #138496;
            --webmonks-teal-light: #5fd0e3;
        }

        body {
            background: #f8f9fc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .error-container {
            max-width: 650px;
            background: white;
            border-radius: 12px;
            padding: 50px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }

        .logo-container {
            margin-bottom: 30px;
        }

        .logo-container img {
            max-width: 250px;
            height: auto;
        }

        .error-icon {
            font-size: 80px;
            color: var(--webmonks-teal);
            margin-bottom: 25px;
        }

        h1 {
            color: #5a5c69;
            margin-bottom: 20px;
            font-size: 32px;
            font-weight: 700;
        }

        p {
            color: #858796;
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.6;
        }

        .domain-info {
            background: #f8f9fc;
            border-left: 4px solid var(--webmonks-teal);
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: left;
        }

        .domain-info .label {
            color: #858796;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .domain-info .domain-value {
            color: #5a5c69;
            font-family: "Courier New", monospace;
            font-size: 16px;
            font-weight: 600;
            word-break: break-all;
        }

        .help-text {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 25px 0;
        }

        .help-text i {
            color: #856404;
        }

        .help-text p {
            color: #856404;
            margin: 0;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--webmonks-teal);
            border: none;
            padding: 14px 35px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
        }

        .btn-primary:hover {
            background: var(--webmonks-teal-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
        }

        .divider {
            border-top: 1px solid #e3e6f0;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <!-- Logo -->
        <div class="logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}">
        </div>

        <!-- Error Icon -->
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <!-- Title & Message -->
        <h1>Tenant Not Found</h1>
        <p>The organization you're trying to access doesn't exist or may have been removed from our system.</p>

        <!-- Domain Info -->
        @if(isset($domain))
            <div class="domain-info">
                <div class="label">
                    <i class="fas fa-globe me-1"></i> Requested Domain
                </div>
                <div class="domain-value">{{ $domain }}</div>
            </div>
        @endif

        <!-- Help Text -->
        <div class="help-text">
            <i class="fas fa-info-circle me-2"></i>
            <p class="mb-0">
                <strong>Need help?</strong> If you believe this is an error, please contact your system administrator or support team for assistance.
            </p>
        </div>

        <div class="divider"></div>

        <!-- Action Button -->
        <a href="{{ config('app.url') }}" class="btn btn-primary">
            <i class="fas fa-home me-2"></i>Return to Central Portal
        </a>
    </div>
</body>
</html>
