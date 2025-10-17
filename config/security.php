<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Content Security Policy Configuration
    |--------------------------------------------------------------------------
    |
    | Configure CSP settings for enhanced XSS protection
    |
    */

    'csp_enabled' => env('CSP_ENABLED', true),
    'csp_report_only' => env('CSP_REPORT_ONLY', false),
    'csp_report_uri' => env('CSP_REPORT_URI', null),

    /*
    |--------------------------------------------------------------------------
    | Trusted Hosts
    |--------------------------------------------------------------------------
    |
    | Additional trusted hosts for CSP script and style sources
    |
    */

    'trusted_hosts' => [
        // Add additional trusted domains here
        // 'https://your-cdn.example.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Strict Transport Security (HSTS)
    |--------------------------------------------------------------------------
    |
    | HSTS configuration for enforcing HTTPS
    |
    */

    'hsts_max_age' => env('HSTS_MAX_AGE', 31536000), // 1 year
    'hsts_include_subdomains' => env('HSTS_INCLUDE_SUBDOMAINS', true),
    'hsts_preload' => env('HSTS_PRELOAD', false),

    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    |
    | Additional security headers and their configurations
    |
    */

    'headers' => [
        // Enable/disable specific security headers
        'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS', true),
        'x_content_type_options' => env('SECURITY_X_CONTENT_TYPE_OPTIONS', true),
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', true),
        'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', true),
        'cross_origin_policies' => env('SECURITY_CROSS_ORIGIN_POLICIES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | XSS Protection Configuration
    |--------------------------------------------------------------------------
    |
    | XSS protection settings and input sanitization
    |
    */

    'xss_protection' => [
        'auto_escape_blade' => env('XSS_AUTO_ESCAPE_BLADE', true),
        'sanitize_inputs' => env('XSS_SANITIZE_INPUTS', true),
        'allowed_html_tags' => ['b', 'i', 'u', 'em', 'strong', 'br', 'p'],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Security settings for file uploads
    |
    */

    'file_uploads' => [
        'max_size' => env('UPLOAD_MAX_SIZE', 10240), // KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
        'scan_for_malware' => env('UPLOAD_SCAN_MALWARE', false),
        'quarantine_suspicious' => env('UPLOAD_QUARANTINE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Enhanced session security configurations
    |
    */

    'session_security' => [
        'rotate_on_login' => env('SESSION_ROTATE_LOGIN', true),
        'timeout_warning' => env('SESSION_TIMEOUT_WARNING', 300), // 5 minutes
        'strict_ip_check' => env('SESSION_STRICT_IP', false),
        'fingerprint_validation' => env('SESSION_FINGERPRINT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Monitoring
    |--------------------------------------------------------------------------
    |
    | Settings for security event monitoring and alerting
    |
    */

    'monitoring' => [
        'log_failed_logins' => env('SECURITY_LOG_FAILED_LOGINS', true),
        'log_csp_violations' => env('SECURITY_LOG_CSP_VIOLATIONS', true),
        'alert_threshold' => env('SECURITY_ALERT_THRESHOLD', 10),
        'alert_window' => env('SECURITY_ALERT_WINDOW', 300), // 5 minutes
        'notification_email' => env('SECURITY_NOTIFICATION_EMAIL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Enhanced rate limiting configurations
    |
    */

    'rate_limiting' => [
        'login_attempts' => env('RATE_LIMIT_LOGIN', 5),
        'login_window' => env('RATE_LIMIT_LOGIN_WINDOW', 900), // 15 minutes
        'api_requests' => env('RATE_LIMIT_API', 100),
        'api_window' => env('RATE_LIMIT_API_WINDOW', 60), // 1 minute
        'max_requests' => env('RATE_LIMIT_MAX_REQUESTS', 1000),
        'window' => env('RATE_LIMIT_WINDOW', 3600), // 1 hour
        'suspicious_threshold' => env('RATE_LIMIT_SUSPICIOUS_THRESHOLD', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Enhanced Security Features
    |--------------------------------------------------------------------------
    |
    | Additional security configurations
    |
    */

    'enhanced_features' => [
        'ip_validation' => env('SECURITY_IP_VALIDATION', false),
        'session_fingerprinting' => env('SECURITY_SESSION_FINGERPRINTING', true),
        'user_agent_validation' => env('SECURITY_USER_AGENT_VALIDATION', true),
        'suspicious_activity_detection' => env('SECURITY_SUSPICIOUS_ACTIVITY_DETECTION', true),
        'automatic_account_lockout' => env('SECURITY_AUTO_LOCKOUT', false),
        'security_event_retention_days' => env('SECURITY_EVENT_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Security
    |--------------------------------------------------------------------------
    |
    | Database-level security configurations
    |
    */

    'database_security' => [
        'query_logging' => env('SECURITY_QUERY_LOGGING', false),
        'slow_query_threshold' => env('SECURITY_SLOW_QUERY_THRESHOLD', 2000), // milliseconds
        'max_query_results' => env('SECURITY_MAX_QUERY_RESULTS', 10000),
        'validate_field_names' => env('SECURITY_VALIDATE_FIELD_NAMES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security
    |--------------------------------------------------------------------------
    |
    | Content-related security settings
    |
    */

    'content_security' => [
        'allowed_iframe_domains' => env('SECURITY_ALLOWED_IFRAME_DOMAINS', ''),
        'script_nonce_required' => env('SECURITY_SCRIPT_NONCE_REQUIRED', true),
        'style_nonce_required' => env('SECURITY_STYLE_NONCE_REQUIRED', false),
        'report_violations' => env('SECURITY_REPORT_VIOLATIONS', true),
    ],
];
