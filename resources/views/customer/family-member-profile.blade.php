@extends('layouts.customer')

@section('title', 'Family Member Profile - ' . $member->name)

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header with back button -->
        <div class="mb-2">
            <div class="d-flex align-items-center">
                <a href="{{ route('customer.profile') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
                <div>
                    <h6 class="mb-0 fw-bold">{{ $member->name }}'s Profile</h6>
                    <small class="text-muted">Family member view</small>
                </div>
            </div>
        </div>

        <!-- Readonly Notice -->
        <div class="alert alert-info py-2 px-3 mb-3">
            <small>
                <i class="fas fa-info-circle me-1"></i>
                <strong>Read-Only:</strong> Family member profile - view only access.
            </small>
        </div>

        <!-- Family Member Profile -->
        <div class="row">
            <!-- Personal Information -->
            <div class="col-xl-10 col-lg-12">
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-user me-1"></i>Personal Information
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-brand fw-bold">
                                        <i class="fas fa-user me-2"></i>Full Name
                                    </label>
                                    <div class="info-value">{{ $member->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-brand fw-bold">
                                        <i class="fas fa-envelope me-2"></i>Email Address
                                    </label>
                                    <div class="info-value">
                                        {{ $member->email }}
                                        @if($member->hasVerifiedEmail())
                                            <span class="badge bg-success ms-2">
                                                <i class="fas fa-check-circle me-1"></i>Verified
                                            </span>
                                        @else
                                            <span class="badge bg-warning ms-2">
                                                <i class="fas fa-exclamation-circle me-1"></i>Not Verified
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-brand fw-bold">
                                        <i class="fas fa-phone me-2"></i>Mobile Number
                                    </label>
                                    <div class="info-value">{{ $member->mobile_number ?? 'Not provided' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-brand fw-bold">
                                        <i class="fas fa-heart me-2"></i>Relationship
                                    </label>
                                    <div class="info-value">
                                        <span class="badge bg-info px-3 py-2">
                                            {{ $member->familyMember?->relationship ?? 'Not specified' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-brand fw-bold">
                                        <i class="fas fa-birthday-cake me-2"></i>Date of Birth
                                    </label>
                                    <div class="info-value">
                                        {{ $member->date_of_birth ? formatDateForUi($member->date_of_birth) : 'Not provided' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-brand fw-bold">
                                        <i class="fas fa-toggle-on me-2"></i>Account Status
                                    </label>
                                    <div class="info-value">
                                        @if($member->status)
                                            <span class="badge bg-success px-3 py-2">
                                                <i class="fas fa-check-circle me-1"></i>Active
                                            </span>
                                        @else
                                            <span class="badge bg-danger px-3 py-2">
                                                <i class="fas fa-times-circle me-1"></i>Inactive
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Two-Factor Authentication Status -->
                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-brand fw-bold">
                                        <i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication
                                    </label>
                                    <div class="info-value">
                                        @if($member->hasCustomerTwoFactorEnabled())
                                            <span class="badge bg-success px-3 py-2">
                                                <i class="fas fa-check-circle me-1"></i>Enabled
                                            </span>
                                            <!-- Disable button for family head only -->
                                            @if($customer->isFamilyHead())
                                                <button type="button" class="btn btn-outline-danger btn-sm mt-2"
                                                        onclick="disableFamilyMember2FA({{ $member->id }}, '{{ $member->name }}')"
                                                        id="disable-2fa-btn-{{ $member->id }}">
                                                    <i class="fas fa-times me-1"></i>Disable 2FA
                                                </button>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary px-3 py-2">
                                                <i class="fas fa-times-circle me-1"></i>Disabled
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-brand fw-bold">
                                        <i class="fas fa-devices me-2"></i>Trusted Devices
                                    </label>
                                    <div class="info-value">
                                        @php
                                            $trustedDevicesCount = $member->getActiveCustomerTrustedDevices()->count();
                                        @endphp
                                        <span class="badge bg-info px-3 py-2">
                                            <i class="fas fa-mobile-alt me-1"></i>{{ $trustedDevicesCount }} Device{{ $trustedDevicesCount !== 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-brand fw-bold">
                                        <i class="fas fa-calendar-plus me-2"></i>Member Since
                                    </label>
                                    <div class="info-value">{{ formatDateForUi($member->created_at) }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="form-label text-brand fw-bold">
                                        <i class="fas fa-edit me-2"></i>Last Updated
                                    </label>
                                    <div class="info-value">{{ formatDateForUi($member->updated_at) }}</div>
                                </div>
                            </div>
                        </div>

                        @if($member->type == 'Retail')
                        <div class="border-top pt-4 mt-4">
                            <h5 class="text-brand mb-4">
                                <i class="fas fa-id-badge me-2"></i>Identity Documents
                            </h5>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="form-label text-brand fw-bold">
                                            <i class="fas fa-credit-card me-2"></i>PAN Card Number
                                        </label>
                                        <div class="info-value">{{ $member->getMaskedPanNumber() ?? 'Not provided' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="form-label text-brand fw-bold">
                                            <i class="fas fa-id-card me-2"></i>Aadhar Card Number
                                        </label>
                                        <div class="info-value">
                                            {{ $member->aadhar_card_number ? '****' . substr($member->aadhar_card_number, -4) : 'Not provided' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Management Actions Sidebar -->
            <div class="col-xl-2 col-lg-12">
                <!-- Family Actions -->
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-tools me-1"></i>Actions
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <div class="d-grid gap-1">
                            <a href="{{ route('customer.family-member.change-password', $member->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-key me-1"></i>Change Password
                            </a>
                            
                            @if(!$member->hasVerifiedEmail())
                                <button class="btn btn-outline-info btn-sm" disabled>
                                    <i class="fas fa-envelope me-1"></i>Not Verified
                                </button>
                            @endif
                            
                            <a href="{{ route('customer.policies') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-shield-alt me-1"></i>Policies
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Family Information -->
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>Family Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="info-item mb-4">
                            <label class="form-label text-brand fw-bold">
                                <i class="fas fa-home me-2"></i>Family Group
                            </label>
                            <div class="info-value">{{ $familyGroup->name }}</div>
                        </div>
                        
                        <div class="info-item mb-4">
                            <label class="form-label text-brand fw-bold">
                                <i class="fas fa-crown me-2"></i>Family Head
                            </label>
                            <div class="info-value">
                                <span class="badge bg-success px-3 py-2">
                                    <i class="fas fa-crown me-1"></i>{{ $customer->name }}
                                </span>
                            </div>
                        </div>

                        <div class="info-item">
                            <label class="form-label text-brand fw-bold">
                                <i class="fas fa-users me-2"></i>Total Members
                            </label>
                            <div class="info-value">
                                <span class="badge bg-primary px-3 py-2">
                                    {{ $familyGroup->members ? $familyGroup->members->count() : 0 }} Members
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add custom styles for info items -->
    <style>
        .info-item {
            margin-bottom: 1.5rem;
        }
        
        .info-item .form-label {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .info-value {
            font-size: 1rem;
            color: var(--text-primary);
            font-weight: 500;
            padding: 0.75rem 1rem;
            background-color: var(--light-bg);
            border-radius: 8px;
            border-left: 3px solid var(--primary-color);
        }

        .family-member-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color) !important;
        }

        .avatar-sm {
            width: 40px;
            height: 40px;
        }
    </style>
@endsection

@push('scripts')
<script>
function disableFamilyMember2FA(memberId, memberName) {
    // Use the reusable confirmation modal instead of JavaScript confirm()
    showConfirmationModal({
        title: 'Disable Two-Factor Authentication',
        message: `Are you sure you want to disable Two-Factor Authentication for <strong>${memberName}</strong>?<br><small class='text-muted'>This will remove all their 2FA settings and trusted devices. They will need to set it up again if they want to re-enable it.</small>`,
        confirmText: 'Yes, Disable 2FA',
        confirmClass: 'btn-warning',
        onConfirm: function() {
            const button = document.getElementById(`disable-2fa-btn-${memberId}`);
            const originalText = button.innerHTML;

            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Disabling...';

            fetch(`/customer/family-member/${memberId}/disable-2fa`, {
                method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                show_notification('success', data.message || `Two-Factor Authentication has been disabled for ${memberName}`);
                // Reload page to update the UI
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                show_notification('error', data.message || 'Failed to disable Two-Factor Authentication');
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            show_notification('error', 'An error occurred while disabling Two-Factor Authentication');
            button.disabled = false;
            button.innerHTML = originalText;
        });
        }
    });
}
</script>
@endpush