<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Insurance Management System - Customer Portal">
    <meta name="author" content="Insurance Management System">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} | @yield('title')</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/jpg" href="{{ company_favicon_asset() }}" />

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Fonts - Modern Inter font for customer portal (Dynamic) -->
    <link href="{{ cdn_url('cdn_google_fonts_inter') }}" rel="stylesheet">

    <!-- Customer Portal Compiled CSS (includes Bootstrap 5 + modern design) -->
    <link href="{{ versioned_asset('css/customer.css') }}" rel="stylesheet">

    <!-- Customer Portal Responsive Fixes -->
    <link href="{{ versioned_asset('css/customer-responsive-fixes.css') }}" rel="stylesheet">

    <!-- Third-party CSS -->
    <link rel="stylesheet" href="{{ versioned_asset('admin/toastr/toastr.css') }}">
    <link href="{{ cdn_url('cdn_select2_css') }}" rel="stylesheet">
    <link href="{{ versioned_asset('datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet">

    <!-- Additional page-specific styles -->
    @yield('stylesheets')

    <!-- Cloudflare Turnstile -->
    @turnstileScripts()

    <!-- Dynamic Theme Styles -->
    <style>
        :root {
            {{ theme_styles() }}

            /* Map theme colors to Bootstrap CSS variables */
            --bs-primary: var(--theme-primary);
            --bs-secondary: var(--theme-secondary);
            --bs-success: var(--theme-success);
            --bs-info: var(--theme-info);
            --bs-warning: var(--theme-warning);
            --bs-danger: var(--theme-danger);
            --bs-light: var(--theme-light);
            --bs-dark: var(--theme-dark);

            /* Bootstrap component customization */
            --bs-body-bg: var(--theme-body-bg);
            --bs-body-color: var(--theme-dark);
            --bs-link-color: var(--theme-link-color);
            --bs-link-hover-color: var(--theme-link-hover);
            --bs-border-radius: var(--theme-border-radius);
            --bs-border-radius-sm: calc(var(--theme-border-radius) * 0.75);
            --bs-border-radius-lg: calc(var(--theme-border-radius) * 1.5);
            --bs-box-shadow: var(--theme-box-shadow);
        }
    </style>

    <!-- Theme-aware Critical CSS -->
    <style>
        /* Apply theme variables to customer portal components */
        body {
            background-color: var(--theme-body-bg);
        }

        .navbar {
            background-color: var(--theme-topbar-bg);
            color: var(--theme-topbar-text);
            box-shadow: var(--theme-box-shadow);
        }

        .card {
            background-color: var(--theme-content-bg);
            border-radius: var(--theme-border-radius);
            box-shadow: var(--theme-box-shadow);
            transition: all var(--theme-animation-speed) ease;
        }

        .btn {
            border-radius: var(--theme-border-radius);
            font-weight: 500;
            transition: all var(--theme-animation-speed) ease;
        }

        a {
            color: var(--theme-link-color);
            transition: color var(--theme-animation-speed) ease;
        }

        a:hover {
            color: var(--theme-link-hover);
        }

        input.form-control, select.form-control, textarea.form-control {
            border-radius: var(--theme-border-radius);
        }
    </style>
</head>