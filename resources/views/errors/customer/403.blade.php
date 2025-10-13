@extends('layouts.customer-auth')

@section('title', 'Access Denied')

@section('content')
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <!-- Logo Section -->
                    <div class="text-center mb-4">
                        <img src="{{ company_logo_asset() }}" alt="{{ company_logo('alt') }}" class="img-fluid mb-3"
                            style="max-height: 50px;">
                        <h4 class="fw-bold text-dark mb-1">{{ company_name() }}</h4>
                        <p class="text-muted small mb-0">Access Denied</p>
                    </div>

                    <!-- Error Message -->
                    <div class="text-center mb-4">
                        <div class="error mx-auto mb-3" style="font-size: 4rem; color: #dc3545; font-weight: bold;">403</div>
                        <h5 class="fw-bold text-dark mb-2">Permission Denied!</h5>
                        <p class="text-muted small mb-3">It looks like you don't have permission to access this resource.</p>
                    </div>

                    <!-- Navigation Links -->
                    <div class="text-center">
                        <a href="{{ route('customer.dashboard') }}" class="btn btn-webmonks w-100 mb-3">
                            Back to Dashboard
                        </a>
                        <a href="{{ route('customer.login') }}" class="small text-muted text-decoration-none">Login Again</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Midastech Credit -->
        @include('common.midastech-credit')
    </div>

    <style>
        .login-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(32, 178, 170, 0.15);
            border-top: 4px solid #20b2aa;
        }

        .btn-webmonks {
            background: linear-gradient(135deg, #20b2aa, #1a9695);
            border: none;
            color: white;
            padding: 0.75rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-webmonks:hover {
            background: linear-gradient(135deg, #1a9695, #178b8a);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(32, 178, 170, 0.3);
            color: white;
        }

        body {
            background: linear-gradient(135deg, #f0fdfc, #e6fffa) !important;
        }
    </style>
@endsection