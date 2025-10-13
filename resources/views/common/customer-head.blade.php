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
    <link rel="shortcut icon" type="image/jpg" href="{{ asset('images/icon.png') }}" />
    
    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts - Modern Inter font for customer portal -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Customer Portal Compiled CSS (includes Bootstrap 5 + modern design) -->
    <link href="{{ url('css/customer.css') }}" rel="stylesheet">

    <!-- Customer Portal Responsive Fixes -->
    <link href="{{ asset('css/customer-responsive-fixes.css') }}" rel="stylesheet">

    <!-- Third-party CSS -->
    <link rel="stylesheet" href="{{ asset('admin/toastr/toastr.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="{{ asset('datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet">

    <!-- Additional page-specific styles -->
    @yield('stylesheets')

    <!-- Cloudflare Turnstile -->
    @turnstileScripts()

    <!-- Performance optimization for critical rendering path -->
    <style>
        /* Critical CSS for above-the-fold content */
        .navbar { box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.06), 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .card { border-radius: 1rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); transition: all 0.3s ease; }
        .btn { border-radius: 0.5rem; font-weight: 500; transition: all 0.3s ease; }
    </style>
</head>