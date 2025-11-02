@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-mobile-alt"></i> Device Details
        </h1>
        <a href="{{ route('customer-devices.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Devices
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Device Information -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Device Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Device Name:</th>
                            <td>{{ $device->device_name ?? 'Unknown Device' }}</td>
                        </tr>
                        <tr>
                            <th>Device Type:</th>
                            <td>
                                @if($device->device_type == 'android')
                                    <i class="fab fa-android text-success"></i> Android
                                @elseif($device->device_type == 'ios')
                                    <i class="fab fa-apple text-dark"></i> iOS
                                @else
                                    <i class="fas fa-globe text-info"></i> Web
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>OS Version:</th>
                            <td>{{ $device->os_version ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>App Version:</th>
                            <td>{{ $device->app_version ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($device->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Registered:</th>
                            <td>{{ $device->created_at->format('d-M-Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Last Active:</th>
                            <td>
                                @if($device->last_active_at)
                                    {{ $device->last_active_at->format('d-M-Y H:i:s') }}
                                    <br><small class="text-muted">({{ $device->last_active_at->diffForHumans() }})</small>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>FCM Token:</th>
                            <td>
                                <small class="text-muted" style="word-break: break-all;">
                                    {{ $device->device_token }}
                                </small>
                            </td>
                        </tr>
                    </table>

                    @if($device->is_active)
                    <div class="mt-3">
                        <button type="button" class="btn btn-warning"
                                onclick="deactivateDevice({{ $device->id }})">
                            <i class="fas fa-ban"></i> Deactivate Device
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Name:</th>
                            <td>{{ $device->customer->name }}</td>
                        </tr>
                        <tr>
                            <th>Mobile:</th>
                            <td>{{ $device->customer->mobile_number }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $device->customer->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Customer Type:</th>
                            <td>{{ $device->customer->customerType->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Registered:</th>
                            <td>{{ $device->customer->created_at->format('d-M-Y') }}</td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        <a href="{{ route('admin.customers.show', $device->customer) }}"
                           class="btn btn-primary">
                            <i class="fas fa-user"></i> View Customer Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Push Notification History</h6>
        </div>
        <div class="card-body">
            @if($notifications && count($notifications) > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Message</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($notification->created_at)->format('d-M-Y H:i') }}</td>
                            <td>
                                <span class="badge badge-info">Push</span>
                            </td>
                            <td>{{ Str::limit($notification->message_content, 100) }}</td>
                            <td>
                                @if($notification->status == 'sent')
                                    <span class="badge badge-success">Sent</span>
                                @elseif($notification->status == 'delivered')
                                    <span class="badge badge-success">Delivered</span>
                                @elseif($notification->status == 'failed')
                                    <span class="badge badge-danger">Failed</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($notification->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-center text-muted mb-0">No push notifications sent to this device yet.</p>
            @endif
        </div>
    </div>
</div>

<script>
function deactivateDevice(deviceId) {
    showConfirmationModal(
        'Deactivate Device',
        'Are you sure you want to deactivate this device?',
        'warning',
        function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/customer-devices/${deviceId}/deactivate`;
            form.innerHTML = '@csrf';
            document.body.appendChild(form);
            form.submit();
        }
    );
}

// Helper function to show confirmation modal
function showConfirmationModal(title, message, variant = 'primary', onConfirm = null) {
    const modalHtml = `
        <div class="modal fade" id="confirmModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-${variant}" id="confirmActionBtn">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('confirmModal')?.remove();
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();

    document.getElementById('confirmActionBtn').addEventListener('click', function() {
        modal.hide();
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
    });
}
</script>
@endsection
