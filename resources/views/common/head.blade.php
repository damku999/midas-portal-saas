<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Insurance Management System - Admin Panel">
    <meta name="author" content="Insurance Management System">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} | @yield('title')</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/jpg" href="{{ company_favicon_asset() }}" />

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Fonts (Dynamic) -->
    <link href="{{ cdn_url('cdn_google_fonts_combined') }}" rel="stylesheet">

    <!-- Admin Portal Compiled CSS (includes Bootstrap 5 + SB Admin 2 compatibility) -->
    <link href="{{ versioned_asset('css/admin.css') }}" rel="stylesheet">

    <!-- Minimal Essential Styles -->
    <link href="{{ versioned_asset('css/admin-minimal.css') }}" rel="stylesheet">

    <!-- Modern Close Button Styles -->
    <link href="{{ versioned_asset('css/modern-close-button.css') }}" rel="stylesheet">

    <!-- Third-party CSS -->
    <link rel="stylesheet" href="{{ versioned_asset('admin/toastr/toastr.css') }}">
    <link href="{{ cdn_url('cdn_select2_css') }}" rel="stylesheet">
    <!-- Simple Date Picker -->
    <link rel="stylesheet" href="{{ cdn_url('cdn_flatpickr_css') }}">
    <link rel="stylesheet" href="{{ cdn_url('cdn_flatpickr_monthselect_css') }}">

    <!-- Additional page-specific styles -->
    @yield('stylesheets')
    @stack('styles')

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
        /* Apply theme variables to components */
        body {
            background-color: var(--theme-body-bg);
        }

        .sidebar {
            transition: width var(--theme-animation-speed) ease;
            background-color: var(--theme-sidebar-bg) !important;
        }

        .sidebar .nav-link {
            color: var(--theme-sidebar-text);
            transition: background-color var(--theme-animation-speed) ease;
        }

        .sidebar .nav-link:hover {
            background-color: var(--theme-sidebar-hover);
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link.active:hover {
            background-color: var(--theme-sidebar-active);
        }

        .topbar {
            background-color: var(--theme-topbar-bg);
            color: var(--theme-topbar-text);
            box-shadow: var(--theme-box-shadow);
        }

        .card {
            background-color: var(--theme-content-bg);
            border-radius: var(--theme-border-radius);
            box-shadow: var(--theme-box-shadow);
        }

        .btn {
            border-radius: var(--theme-border-radius);
            transition: all var(--theme-animation-speed) ease;
        }

        a {
            color: var(--theme-link-color);
            transition: color var(--theme-animation-speed) ease;
        }

        a:hover {
            color: var(--theme-link-hover);
        }

        /* Simple Date Picker Calendar Icon */
        .form-control.datepicker {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%236c757d' viewBox='0 0 16 16'%3e%3cpath d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 2a1 1 0 0 0-1 1v1h14V3a1 1 0 0 0-1-1H2zm13 3H1v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V5z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 40px;
        }

        input.form-control, select.form-control, textarea.form-control {
            border-radius: var(--theme-border-radius);
        }
    </style>
</head>
