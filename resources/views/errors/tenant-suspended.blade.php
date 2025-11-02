<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            max-width: 600px;
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
        }
        .error-icon {
            font-size: 80px;
            color: #f5576c;
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-ban"></i>
        </div>
        <h1>Account Suspended</h1>
        <p>Your organization's account has been temporarily suspended.</p>
        <p>This may be due to:</p>
        <ul class="text-start list-unstyled">
            <li><i class="fas fa-circle fa-xs me-2"></i>Payment issues</li>
            <li><i class="fas fa-circle fa-xs me-2"></i>Subscription expiration</li>
            <li><i class="fas fa-circle fa-xs me-2"></i>Policy violations</li>
        </ul>
        <p class="small text-muted mt-4">Please contact support to resolve this issue.</p>
        <a href="mailto:support@midastech.in" class="btn btn-danger">
            <i class="fas fa-envelope me-2"></i>Contact Support
        </a>
    </div>
</body>
</html>
