<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'System Notification' }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #dc3545; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #dc3545; margin: 0; font-size: 24px; }
        .content h2 { color: #dc3545; font-size: 18px; margin-top: 25px; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 15px 0; }
        .details { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .footer { border-top: 1px solid #ddd; padding-top: 20px; margin-top: 30px; text-align: center; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $subject ?? 'System Notification' }}</h1>
        </div>
        
        <div class="content">
            @if(isset($message))
                <p>{!! $message !!}</p>
            @endif

            @if(isset($details) && is_array($details))
                <div class="details">
                    <h2>Details:</h2>
                    <ul>
                        @foreach($details as $key => $value)
                            <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(isset($action_url) && isset($action_text))
                <div style="text-align: center; margin: 30px 0;">
                    <a href="{{ $action_url }}" class="btn">{{ $action_text }}</a>
                </div>
            @endif

            @if(isset($footer_message))
                <p>{{ $footer_message }}</p>
            @endif
        </div>

        <div class="footer">
            <p>Thanks,<br><strong>{{ company_name() }} System</strong></p>
        </div>
    </div>
</body>
</html>