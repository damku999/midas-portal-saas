<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Central Admin') - {{ config('app.name') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #4e73df;
            --sidebar-bg: #1a1f36;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            padding: 20px 0;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .sidebar-brand h4 {
            color: white;
            margin: 0;
            font-size: 1.2rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: #f8f9fc;
        }

        .topbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .content-wrapper {
            padding: 0 30px 30px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-color);
        }

        .stat-card h6 {
            color: #858796;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .stat-card .h3 {
            color: #5a5c69;
            font-weight: 700;
        }

        .card {
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: none;
        }

        .badge-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-active { background: #d4edda; color: #155724; }
        .badge-trial { background: #fff3cd; color: #856404; }
        .badge-suspended { background: #f8d7da; color: #721c24; }
        .badge-expired { background: #e2e3e5; color: #383d41; }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h4><i class="fas fa-shield-alt"></i> Central Admin</h4>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('central.dashboard') }}" class="{{ request()->routeIs('central.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('central.tenants.index') }}" class="{{ request()->routeIs('central.tenants.*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    <span>Tenants</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
            </div>
            <div>
                <span class="text-muted">
                    <i class="fas fa-user-shield"></i>
                    {{ auth('central')->user()->name }}
                </span>
            </div>
        </div>

        <!-- Content -->
        <div class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('central.logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
