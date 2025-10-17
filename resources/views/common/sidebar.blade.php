<!-- Insurance Management System Sidebar - With Sub-menus -->
<div class="navbar-nav bg-primary sidebar sidebar-dark accordion vh-100 overflow-auto shadow" id="accordionSidebar">

    <!-- Sidebar Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center py-4 text-decoration-none border-bottom border-light border-opacity-25" href="{{ route('home') }}">
        <img src="{{ company_logo_asset() }}" alt="{{ company_logo('alt') }}" class="img-fluid" style="max-height: {{ app_setting('company_sidebar_logo_height', 'branding', '60px') }}; width: auto;">
    </a>

    <!-- Dashboard -->
    <div class="nav-item">
        <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('home') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('home') }}" data-tooltip="Dashboard">
            <i class="fas fa-tachometer-alt me-3"></i>
            <span>Dashboard</span>
        </a>
    </div>

    <!-- Customers -->
    <div class="nav-item">
        <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('customers.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('customers.index') }}" data-tooltip="Customers">
            <i class="fas fa-users me-3"></i>
            <span>Customers</span>
        </a>
    </div>

    <!-- Customer Insurances -->
    <div class="nav-item">
        <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('customer_insurances.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('customer_insurances.index') }}" data-tooltip="Customer Insurances">
            <i class="fas fa-shield-alt me-3"></i>
            <span>Customer Insurances</span>
        </a>
    </div>

    <!-- Quotations -->
    <div class="nav-item">
        <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('quotations.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('quotations.index') }}" data-tooltip="Quotations">
            <i class="fas fa-file-invoice me-3"></i>
            <span>Quotations</span>
        </a>
    </div>

    <!-- Claims -->
    @if(auth()->check() && auth()->user()->hasPermissionTo('claim-list'))
    <div class="nav-item">
        <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('claims.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('claims.index') }}" data-tooltip="Claims">
            <i class="fas fa-file-medical me-3"></i>
            <span>Claims</span>
        </a>
    </div>
    @endif

    <!-- WhatsApp Marketing -->
    <div class="nav-item">
        <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('marketing.whatsapp.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('marketing.whatsapp.index') }}" data-tooltip="WhatsApp Marketing">
            <i class="fab fa-whatsapp me-3"></i>
            <span>WhatsApp Marketing</span>
        </a>
    </div>

    <!-- Family Groups -->
    <div class="nav-item">
        <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('family_groups.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('family_groups.index') }}" data-tooltip="Family Groups">
            <i class="fas fa-users-cog me-3"></i>
            <span>Family Groups</span>
        </a>
    </div>

    <!-- Business Reports -->
    <div class="nav-item">
        <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('reports.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('reports.index') }}" data-tooltip="Reports">
            <i class="fas fa-chart-bar me-3"></i>
            <span>Reports</span>
        </a>
    </div>

    <!-- Divider -->
    <hr class="border-light border-opacity-25 my-2 mx-3">

    <!-- NOTIFICATIONS SUBMENU -->
    @php
        $notificationRoutes = ['notification-templates.*', 'admin.notification-logs.*', 'admin.customer-devices.*'];
        $isNotificationActive = collect($notificationRoutes)->contains(fn($route) => request()->routeIs($route));
    @endphp
    <div class="nav-item">
        <a class="nav-link d-flex align-items-center justify-content-between py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ $isNotificationActive ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}"
           href="#notificationSubmenu"
           data-bs-toggle="collapse"
           role="button"
           data-tooltip="Notifications"
           aria-expanded="{{ $isNotificationActive ? 'true' : 'false' }}"
           aria-controls="notificationSubmenu">
            <div class="d-flex align-items-center">
                <i class="fas fa-bell me-3"></i>
                <span>Notifications</span>
            </div>
            <i class="fas fa-chevron-{{ $isNotificationActive ? 'up' : 'down' }}"></i>
        </a>
        <div class="collapse {{ $isNotificationActive ? 'show' : '' }}" id="notificationSubmenu">
            <div class="ms-4">
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('notification-templates.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('notification-templates.index') }}">
                    <i class="fas fa-file-alt me-3 fs-6"></i>
                    <span>Templates</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('admin.notification-logs.index') && !request()->routeIs('admin.notification-logs.analytics') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('admin.notification-logs.index') }}">
                    <i class="fas fa-list me-3 fs-6"></i>
                    <span>Notification Logs</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('admin.notification-logs.analytics') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('admin.notification-logs.analytics') }}">
                    <i class="fas fa-chart-line me-3 fs-6"></i>
                    <span>Analytics</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('admin.customer-devices.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('admin.customer-devices.index') }}">
                    <i class="fas fa-mobile-alt me-3 fs-6"></i>
                    <span>Customer Devices</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('admin.notification-logs.index') && request('status') == 'failed' ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('admin.notification-logs.index', ['status' => 'failed']) }}">
                    <i class="fas fa-exclamation-triangle text-danger me-3 fs-6"></i>
                    <span>Failed Notifications</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Divider -->
    <hr class="border-light border-opacity-25 my-2 mx-3">

    <!-- MASTER DATA SUBMENU -->
    @php
        $masterRoutes = ['relationship_managers.*', 'reference_users.*', 'insurance_companies.*', 'brokers.*', 'addon-covers.*', 'policy_type.*', 'premium_type.*', 'fuel_type.*', 'branches.*'];
        $isMasterActive = collect($masterRoutes)->contains(fn($route) => request()->routeIs($route));
    @endphp
    <div class="nav-item">
        <a class="nav-link d-flex align-items-center justify-content-between py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ $isMasterActive ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}"
           href="#masterSubmenu"
           data-bs-toggle="collapse"
           role="button"
           data-tooltip="Master Data"
           aria-expanded="{{ $isMasterActive ? 'true' : 'false' }}"
           aria-controls="masterSubmenu">
            <div class="d-flex align-items-center">
                <i class="fas fa-database me-3"></i>
                <span>Master Data</span>
            </div>
            <i class="fas fa-chevron-{{ $isMasterActive ? 'up' : 'down' }}"></i>
        </a>
        <div class="collapse {{ $isMasterActive ? 'show' : '' }}" id="masterSubmenu">
            <div class="ms-4">
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('relationship_managers.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('relationship_managers.index') }}">
                    <i class="fas fa-user-tie me-3 fs-6"></i>
                    <span>Relationship Managers</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('reference_users.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('reference_users.index') }}">
                    <i class="fas fa-user-friends me-3 fs-6"></i>
                    <span>Reference Users</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('insurance_companies.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('insurance_companies.index') }}">
                    <i class="fas fa-building me-3 fs-6"></i>
                    <span>Insurance Companies</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('brokers.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('brokers.index') }}">
                    <i class="fas fa-handshake me-3 fs-6"></i>
                    <span>Brokers</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('addon-covers.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('addon-covers.index') }}">
                    <i class="fas fa-plus-circle me-3 fs-6"></i>
                    <span>Addon Covers</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('policy_type.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('policy_type.index') }}">
                    <i class="fas fa-clipboard-list me-3 fs-6"></i>
                    <span>Policy Types</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('premium_type.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('premium_type.index') }}">
                    <i class="fas fa-dollar-sign me-3 fs-6"></i>
                    <span>Premium Types</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('fuel_type.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('fuel_type.index') }}">
                    <i class="fas fa-gas-pump me-3 fs-6"></i>
                    <span>Fuel Types</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('branches.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('branches.index') }}">
                    <i class="fas fa-map-marker-alt me-3 fs-6"></i>
                    <span>Branches</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Divider -->
    <hr class="border-light border-opacity-25 my-2 mx-3">

    <!-- USERS & ADMINISTRATION SUBMENU -->
    @php
        $adminRoutes = ['users.*', 'roles.*', 'permissions.*', 'app-settings.*'];
        $isAdminActive = collect($adminRoutes)->contains(fn($route) => request()->routeIs($route));
    @endphp
    <div class="nav-item">
        <a class="nav-link d-flex align-items-center justify-content-between py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ $isAdminActive ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}"
           href="#adminSubmenu"
           data-bs-toggle="collapse"
           role="button"
           data-tooltip="Users & Administration"
           aria-expanded="{{ $isAdminActive ? 'true' : 'false' }}"
           aria-controls="adminSubmenu">
            <div class="d-flex align-items-center">
                <i class="fas fa-users-cog me-3"></i>
                <span>Users & Administration</span>
            </div>
            <i class="fas fa-chevron-{{ $isAdminActive ? 'up' : 'down' }}"></i>
        </a>
        <div class="collapse {{ $isAdminActive ? 'show' : '' }}" id="adminSubmenu">
            <div class="ms-4">
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('users.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('users.index') }}">
                    <i class="fas fa-user me-3 fs-6"></i>
                    <span>Users</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('roles.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('roles.index') }}">
                    <i class="fas fa-user-tag me-3 fs-6"></i>
                    <span>Roles</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('permissions.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('permissions.index') }}">
                    <i class="fas fa-key me-3 fs-6"></i>
                    <span>Permissions</span>
                </a>
                <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('app-settings.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('app-settings.index') }}">
                    <i class="fas fa-cog me-3 fs-6"></i>
                    <span>App Settings</span>
                </a>
            </div>
        </div>
    </div>


    <!-- SYSTEM LOGS (Conditional Visibility for System Admins only) -->
    @php
        $userEmail = auth()->user()->email ?? '';
        $showSystemLogs = is_system_admin($userEmail);
    @endphp
    @if($showSystemLogs)
    <div class="nav-item">
        <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-white-50 text-decoration-none {{ request()->routeIs('log-viewer.*') ? 'bg-light bg-opacity-10 text-white fw-semibold' : '' }}" href="{{ route('log-viewer.index') }}" data-tooltip="System Logs">
            <i class="fas fa-bug me-3"></i>
            <span>System Logs</span>
        </a>
    </div>
    @endif

    <!-- Mobile Logout -->
    <div class="d-md-none mt-4">
        <hr class="border-light border-opacity-25 my-3 mx-3">
        <div class="nav-item">
            <a class="nav-link d-flex align-items-center py-2 mx-2 my-1 rounded text-danger text-decoration-none" href="#" onclick="showLogoutModal()">
                <i class="fas fa-sign-out-alt me-3"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Bottom spacer for better scrolling -->
    <div class="py-5"></div>
    
</div>