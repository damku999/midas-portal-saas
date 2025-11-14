@extends('layouts.customer-auth')

@section('title', 'Reset Password')

@section('content')
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-5 col-lg-4">
                <div class="login-card">
                    <!-- Logo Section -->
                    <div class="text-center mb-4">
                        <img src="{{ company_logo_asset() }}" alt="{{ company_logo('alt') }}" class="img-fluid mb-3"
                            style="max-height: 50px;">
                        <h4 class="fw-bold text-dark mb-1">{{ company_name() }}</h4>
                        <p class="text-muted small mb-0">Reset your password</p>
                    </div>

                    <!-- Description -->
                    <div class="text-center mb-4">
                        <p class="text-muted small">Enter your email address and we'll send you a link to reset your password.</p>
                    </div>

                    <!-- Alerts -->
                    @if (session('error'))
                        <div class="alert alert-danger py-2">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success py-2">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Reset Form -->
                    <form method="POST" action="{{ route('customer.password.email') }}">
                        @csrf

                        <div class="mb-3">
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                name="email" value="{{ old('email') }}" placeholder="Email address" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Cloudflare Turnstile - Only in Production -->
                        @if(app()->environment('production'))
                        <div class="mb-3">
                            <x-turnstile />
                            @error('cf-turnstile-response')
                                <div class="text-danger mt-2">
                                    <small><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</small>
                                </div>
                            @enderror
                        </div>
                        @endif

                        <button type="submit" class="btn btn-webmonks w-100 mb-3">
                            Send Password Reset Link
                        </button>
                    </form>

                    <!-- Navigation Links -->
                    <div class="text-center">
                        <a href="{{ route('customer.login') }}" class="small text-decoration-none me-3">Back to Login</a>
                        <a href="{{ route('login') }}" class="small text-muted text-decoration-none">Admin Login</a>
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

        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #20b2aa;
            box-shadow: 0 0 0 0.2rem rgba(32, 178, 170, 0.25);
        }

        .alert {
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        body {
            background: linear-gradient(135deg, #f0fdfc, #e6fffa) !important;
        }

        /* Override any bad gradients */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #20b2aa, #1a9695) !important;
            background-image: linear-gradient(135deg, #20b2aa, #1a9695) !important;
        }
    </style>
@endsection
