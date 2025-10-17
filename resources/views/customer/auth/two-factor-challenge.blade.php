@extends('auth.layouts.app')

@section('title', 'Two-Factor Authentication')

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
                            <h4 class="text-dark fw-bold"><i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication</h4>
                            <p class="text-muted">Enter your verification code to continue</p>
                        </div>
                    @if(session('info'))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                        <p class="text-muted">
                            Please enter the 6-digit verification code from your authenticator app to complete your login.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('customer.customer.two-factor.verify') }}" id="twoFactorForm">
                        @csrf

                        <div class="mb-4">
                            <label for="code" class="form-label fw-bold">
                                <i class="fas fa-key me-1"></i>Verification Code
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                                   id="code"
                                   name="code"
                                   placeholder="000000"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   autocomplete="one-time-code"
                                   autofocus
                                   required
                                   data-max-attempts="5">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-clock me-1"></i>Codes expire every 30 seconds
                            </div>
                        </div>

                        <input type="hidden" name="code_type" value="totp" id="code_type">

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="trust_device" id="trust_device" value="1">
                                <label class="form-check-label" for="trust_device">
                                    <i class="fas fa-laptop me-1"></i>Trust this device for 30 days
                                </label>
                                <div class="form-text">You won't need 2FA on this device for 30 days</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="verifyBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>Verify & Continue
                            </button>
                        </div>

                        <!-- Rate limiting feedback -->
                        <div id="rateLimitWarning" class="alert alert-warning mt-3" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="attemptsRemaining">5</span> attempts remaining before temporary lockout.
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <details class="mb-3">
                            <summary class="btn btn-link text-muted p-0">
                                <i class="fas fa-key me-1"></i>Use Recovery Code Instead
                            </summary>
                            <div class="mt-3">
                                <form method="POST" action="{{ route('customer.customer.two-factor.verify') }}" id="recoveryForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="recovery_code" class="form-label">Recovery Code</label>
                                        <input type="text"
                                               class="form-control"
                                               id="recovery_code"
                                               name="code"
                                               placeholder="XXXXXXXX"
                                               pattern="[A-Z0-9]{8}"
                                               maxlength="8">
                                        <div class="form-text">Enter one of your backup recovery codes</div>
                                    </div>
                                    <input type="hidden" name="code_type" value="recovery">
                                    <button type="submit" class="btn btn-outline-secondary" id="recoveryBtn">
                                        <i class="fas fa-unlock me-1"></i>Use Recovery Code
                                    </button>
                                </form>
                            </div>
                        </details>

                        <div class="text-muted small">
                            <i class="fas fa-question-circle me-1"></i>
                            Having trouble? Contact support for assistance.
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('customer.login') }}" class="btn btn-link text-muted">
                                <i class="fas fa-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeInput = document.getElementById('code');
    const recoveryInput = document.getElementById('recovery_code');
    const verifyBtn = document.getElementById('verifyBtn');
    const recoveryBtn = document.getElementById('recoveryBtn');
    const rateLimitWarning = document.getElementById('rateLimitWarning');
    const attemptsRemaining = document.getElementById('attemptsRemaining');

    let failedAttempts = 0;
    const maxAttempts = parseInt(codeInput.getAttribute('data-max-attempts')) || 5;

    // Enhanced TOTP input handling
    codeInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');

        // Visual feedback for completed code
        if (this.value.length === 6) {
            this.classList.add('is-valid');
            verifyBtn.classList.remove('btn-primary');
            verifyBtn.classList.add('btn-success');

            // Auto-submit after brief delay to show feedback
            setTimeout(() => {
                if (this.value.length === 6) {
                    document.getElementById('twoFactorForm').submit();
                }
            }, 300);
        } else {
            this.classList.remove('is-valid');
            verifyBtn.classList.remove('btn-success');
            verifyBtn.classList.add('btn-primary');
        }
    });

    // Enhanced recovery code input handling
    if (recoveryInput) {
        recoveryInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^A-Z0-9]/g, '').substring(0, 8).toUpperCase();

            // Visual feedback for completed recovery code
            if (this.value.length === 8) {
                this.classList.add('is-valid');
                recoveryBtn.classList.remove('btn-outline-secondary');
                recoveryBtn.classList.add('btn-outline-success');
            } else {
                this.classList.remove('is-valid');
                recoveryBtn.classList.remove('btn-outline-success');
                recoveryBtn.classList.add('btn-outline-secondary');
            }
        });
    }

    // Handle form submission errors and rate limiting
    const forms = [document.getElementById('twoFactorForm'), document.getElementById('recoveryForm')];
    forms.forEach(form => {
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');

                // Show loading state
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';

                // Re-enable after timeout (in case of network issues)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 10000);
            });
        }
    });

    // Show error feedback and handle rate limiting
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error') || document.querySelector('.invalid-feedback')) {
        failedAttempts++;

        if (failedAttempts >= maxAttempts - 2) {
            rateLimitWarning.style.display = 'block';
            attemptsRemaining.textContent = Math.max(0, maxAttempts - failedAttempts);

            if (failedAttempts >= maxAttempts) {
                // Disable form for rate limiting
                codeInput.disabled = true;
                if (recoveryInput) recoveryInput.disabled = true;
                verifyBtn.disabled = true;
                if (recoveryBtn) recoveryBtn.disabled = true;

                rateLimitWarning.classList.remove('alert-warning');
                rateLimitWarning.classList.add('alert-danger');
                rateLimitWarning.innerHTML = '<i class="fas fa-ban me-2"></i>Too many failed attempts. Please try again later.';
            }
        }
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+R or F5 to refresh and clear attempts
        if ((e.ctrlKey && e.key === 'r') || e.key === 'F5') {
            failedAttempts = 0;
        }
    });

    // Focus management for better UX
    codeInput.focus();

    // Clear input on focus if there's an error
    if (document.querySelector('.invalid-feedback')) {
        codeInput.addEventListener('focus', function() {
            this.select();
        }, { once: true });
    }
});
</script>
@endsection