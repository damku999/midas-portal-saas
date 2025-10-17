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
        }
    </style>

    <!-- Performance optimization for critical rendering path -->
    <style>
        /* Critical CSS for above-the-fold content */
        .navbar { box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.06), 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .card { border-radius: 1rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); transition: all 0.3s ease; }
        .btn { border-radius: 0.5rem; font-weight: 500; transition: all 0.3s ease; }
    </style>
</head>