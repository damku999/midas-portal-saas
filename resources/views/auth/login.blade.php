@extends('auth.layouts.app')

@section('title', 'Admin Login')

@section('content')
<div class="auth-container d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="auth-card card shadow-lg fade-in-scale">
                    <!-- Auth Header -->
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="{{ asset('images/parth_logo.png') }}" alt="WebMonks" class="img-fluid mb-3" style="max-width: 120px;">
                            <h4 class="text-dark fw-bold">Admin Portal</h4>
                            <p class="text-muted">Sign in to access the admin dashboard</p>
                        </div>

        <!-- Alerts -->
        @if (session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        @if (session('message'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope me-1"></i>Email Address
                </label>
                <input type="email"
                       class="form-control @error('email') is-invalid @enderror"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="Enter your email address"
                       required
                       autofocus>
                @error('email')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock me-1"></i>Password
                </label>
                <input type="password"
                       class="form-control @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       placeholder="Enter your password"
                       required>
                @error('password')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-decoration-none">
                        <i class="fas fa-key me-1"></i>Forgot Password?
                    </a>
                </div>
            </div>

            <!-- Cloudflare Turnstile -->
            <div class="form-group">
                <x-turnstile />
                @error('cf-turnstile-response')
                    <div class="text-danger mt-2">
                        <small><i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}</small>
                    </div>
                @enderror
            </div>

                        <button type="submit" class="btn btn-primary w-100 mb-4">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </form>

                    <!-- Customer Link -->
                    <div class="text-center">
                        <p class="text-muted mb-0">
                            <small>Need customer access?
                                <a href="{{ route('customer.login') }}" class="text-decoration-none">
                                    <i class="fas fa-user me-1"></i>Customer Login
                                </a>
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Midastech Credit -->
        @include('common.midastech-credit')
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add focus state enhancement
    $(document).ready(function() {
        $('.form-control').on('focus', function() {
            $(this).parent().addClass('focused');
        }).on('blur', function() {
            $(this).parent().removeClass('focused');
        });
    });
</script>
@endpush
