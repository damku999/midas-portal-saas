<nav class="navbar navbar-expand-lg navbar-light shadow-sm mb-4 sticky-top">

    <div class="container-fluid">
        <!-- Brand Logo -->
        @auth('customer')
            <a class="navbar-brand" href="{{ route('customer.dashboard') }}">
                <img src="{{ company_logo_asset() }}" style="max-height: 40px;" alt="{{ company_logo('alt') }}">
            </a>
            <!-- Mobile toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Collapsible navigation -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Main Navigation Links -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link fw-bold {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}"
                            href="{{ route('customer.dashboard') }}">
                            <i class="fas fa-home me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.policies*') ? 'active' : '' }}"
                            href="{{ route('customer.policies') }}">
                            <i class="fas fa-shield-alt me-2"></i> My Policies
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.quotations*') ? 'active' : '' }}"
                            href="{{ route('customer.quotations') }}">
                            <i class="fas fa-calculator me-2"></i> Quotations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.claims*') ? 'active' : '' }}"
                            href="{{ route('customer.claims') }}">
                            <i class="fas fa-clipboard-list me-2"></i> Claims
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.profile*') ? 'active' : '' }}"
                            href="{{ route('customer.profile') }}">
                            <i class="fas fa-user me-2"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customer.two-factor*') ? 'active' : '' }}"
                            href="{{ route('customer.two-factor.index') }}">
                            <i class="fas fa-shield-alt me-2"></i> 2FA Security
                        </a>
                    </li>
                </ul>

                <!-- User Info & Actions -->
                <ul class="navbar-nav ms-auto">
                    <!-- Welcome Message - Desktop -->
                    <li class="nav-item d-none d-lg-flex align-items-center">
                        <span class="navbar-text me-3">
                            <small class="text-muted">Welcome back,</small>
                            <strong>{{ Auth::guard('customer')->user()->name }}</strong>
                            @if (Auth::guard('customer')->user()->isFamilyHead())
                                <span class="badge bg-success ms-1">Family Head</span>
                            @endif
                        </span>
                    </li>

                    <!-- Welcome Message - Mobile -->
                    <li class="nav-item d-lg-none">
                        <div class="navbar-text text-center py-2">
                            <div class="text-muted small">Welcome,</div>
                            <strong>{{ Auth::guard('customer')->user()->name }}</strong>
                            @if (Auth::guard('customer')->user()->isFamilyHead())
                                <div class="mt-1">
                                    <span class="badge bg-success">Family Head</span>
                                </div>
                            @endif
                        </div>
                    </li>

                    <!-- Logout Button -->
                    <li class="nav-item">
                        <form method="POST" action="{{ route('customer.logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i>
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @endauth

    </div>
</nav>
