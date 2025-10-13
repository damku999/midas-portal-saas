@extends('auth.layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="auth-container d-flex align-items-center justify-content-center min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="auth-card card shadow-lg fade-in-scale">
                    <!-- Auth Header -->
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="{{ company_logo_asset() }}" alt="{{ company_logo('alt') }}" class="img-fluid mb-3" style="max-width: 120px;">
                            <h4 class="text-dark fw-bold">Reset Password</h4>
                            <p class="text-muted">Enter your new password below</p>
                        </div>

                        <!-- Alerts -->
                        @if (session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                            </div>
                        @endif

                        <!-- Reset Password Form -->
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope me-1"></i>Email Address
                                </label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ $email ?? old('email') }}"
                                       placeholder="Enter your email address"
                                       required
                                       autocomplete="email"
                                       autofocus>
                                @error('email')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">
                                    <i class="fas fa-lock me-1"></i>New Password
                                </label>
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       placeholder="Enter your new password"
                                       required
                                       autocomplete="new-password">
                                @error('password')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password-confirm">
                                    <i class="fas fa-lock me-1"></i>Confirm Password
                                </label>
                                <input type="password"
                                       class="form-control"
                                       id="password-confirm"
                                       name="password_confirmation"
                                       placeholder="Confirm your new password"
                                       required
                                       autocomplete="new-password">
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
                                <i class="fas fa-key me-2"></i>Reset Password
                            </button>
                        </form>

                        <!-- Back to Login Link -->
                        <div class="text-center">
                            <p class="text-muted mb-0">
                                <small>Remember your password?
                                    <a href="{{ route('login') }}" class="text-decoration-none">
                                        <i class="fas fa-sign-in-alt me-1"></i>Back to Login
                                    </a>
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Midastech Credit -->
        @include('common.midastech-credit')
    </div>
</div>
@endsection
