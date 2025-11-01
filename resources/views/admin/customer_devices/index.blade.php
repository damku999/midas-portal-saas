@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-mobile-alt"></i> Customer Devices
        </h1>
        <button class="btn btn-sm btn-danger" onclick="cleanupInvalid()">
            <i class="fas fa-broom"></i> Cleanup Inactive Devices
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Devices</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-mobile-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Inactive</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactive'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Android</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['android'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fab fa-android fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">iOS</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['ios'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fab fa-apple fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Web</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['web'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.customer-devices.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search customer/device..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="device_type" class="form-control">
                            <option value="">All Device Types</option>
                            <option value="android" {{ request('device_type') == 'android' ? 'selected' : '' }}>Android</option>
                            <option value="ios" {{ request('device_type') == 'ios' ? 'selected' : '' }}>iOS</option>
                            <option value="web" {{ request('device_type') == 'web' ? 'selected' : '' }}>Web</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.customer-devices.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Devices Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Registered Devices ({{ $devices->total() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Device Name</th>
                            <th>Type</th>
                            <th>OS Version</th>
                            <th>Last Active</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $device)
                        <tr>
                            <td>
                                <strong>{{ $device->customer->name }}</strong><br>
                                <small class="text-muted">{{ $device->customer->mobile_number }}</small>
                            </td>
                            <td>
                                {{ $device->device_name ?? 'Unknown Device' }}<br>
                                <small class="text-muted">{{ Str::limit($device->device_token, 30) }}</small>
                            </td>
                            <td>
                                @if($device->device_type == 'android')
                                    <i class="fab fa-android text-success"></i> Android
                                @elseif($device->device_type == 'ios')
                                    <i class="fab fa-apple text-dark"></i> iOS
                                @else
                                    <i class="fas fa-globe text-info"></i> Web
                                @endif
                            </td>
                            <td>{{ $device->os_version ?? 'N/A' }}</td>
                            <td>
                                @if($device->last_active_at)
                                    {{ $device->last_active_at->diffForHumans() }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                @if($device->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.customer-devices.show', $device) }}"
                                   class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($device->is_active)
                                    <button type="button" class="btn btn-sm btn-warning"
                                            onclick="deactivateDeviceInline({{ $device->id }})"
                                            title="Deactivate">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No devices found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $devices->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function cleanupInvalid() {
    showConfirmationModal(
        'Confirm Cleanup',
        'This will deactivate all devices inactive for 90+ days. Do you want to continue?',
        'danger',
        function() {
            showLoading('Cleaning up inactive devices...');
            fetch('{{ route("admin.customer-devices.cleanup-invalid") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                show_notification('success', data.message || 'Cleanup completed');
                setTimeout(() => location.reload(), 1500);
            })
            .catch(error => {
                hideLoading();
                show_notification('error', 'Error: ' + error.message);
            });
        }
    );
}

function deactivateDeviceInline(deviceId) {
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

    // Remove existing modal if any
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
