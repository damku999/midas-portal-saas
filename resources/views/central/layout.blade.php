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
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

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
            @yield('content')
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="confirmModalBody">
                    Are you sure you want to perform this action?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmModalAction">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('central.logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Toastr configuration
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Show session messages
        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif

        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif

        @if(session('info'))
            toastr.info("{{ session('info') }}");
        @endif

        @if(session('warning'))
            toastr.warning("{{ session('warning') }}");
        @endif

        // Confirmation modal helper
        function showConfirmModal(title, message, onConfirm, btnText = 'Confirm', btnClass = 'btn-danger') {
            $('#confirmModalTitle').text(title);
            $('#confirmModalBody').text(message);
            $('#confirmModalAction').text(btnText).removeClass().addClass('btn ' + btnClass);

            $('#confirmModalAction').off('click').on('click', function() {
                $('#confirmModal').modal('hide');
                if (typeof onConfirm === 'function') {
                    onConfirm();
                }
            });

            $('#confirmModal').modal('show');
        }

        // Handle forms with confirmation
        $(document).on('click', '[data-confirm]', function(e) {
            e.preventDefault();
            var $this = $(this);
            var message = $this.data('confirm');
            var title = $this.data('confirm-title') || 'Confirm Action';
            var btnText = $this.data('confirm-button') || 'Confirm';
            var btnClass = $this.data('confirm-class') || 'btn-danger';

            showConfirmModal(title, message, function() {
                if ($this.is('form')) {
                    $this.submit();
                } else if ($this.closest('form').length) {
                    $this.closest('form').submit();
                } else if ($this.attr('href')) {
                    window.location.href = $this.attr('href');
                }
            }, btnText, btnClass);
        });
    </script>

    @stack('scripts')
</body>
</html>
