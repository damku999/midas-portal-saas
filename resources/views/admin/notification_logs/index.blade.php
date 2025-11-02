@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-bell mr-2"></i>Notification Logs
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('notification-logs.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Channel</label>
                                    <select name="channel" class="form-control">
                                        <option value="">All Channels</option>
                                        <option value="whatsapp" {{ request('channel') == 'whatsapp' ? 'selected' : '' }}>
                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                        </option>
                                        <option value="email" {{ request('channel') == 'email' ? 'selected' : '' }}>
                                            <i class="fas fa-envelope"></i> Email
                                        </option>
                                        <option value="sms" {{ request('channel') == 'sms' ? 'selected' : '' }}>
                                            <i class="fas fa-sms"></i> SMS
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>From Date</label>
                                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>To Date</label>
                                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Search by recipient or message content..."
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="{{ route('notification-logs.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                                <a href="{{ route('notification-logs.analytics') }}" class="btn btn-info">
                                    <i class="fas fa-chart-bar"></i> Analytics
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Bulk Actions -->
                    <form id="bulkResendForm" method="POST" action="{{ route('notification-logs.bulk-resend') }}">
                        @csrf
                        <div class="mb-3">
                            <button type="submit" class="btn btn-warning btn-sm" id="bulkResendBtn" disabled>
                                <i class="fas fa-redo"></i> Resend Selected
                            </button>
                            <span class="ml-2 text-muted" id="selectedCount">0 selected</span>
                        </div>

                        <!-- Notification Logs Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>ID</th>
                                        <th>Channel</th>
                                        <th>Recipient</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Sent At</th>
                                        <th>Delivered At</th>
                                        <th>Retry</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($logs as $log)
                                    <tr>
                                        <td>
                                            @if($log->status == 'failed' && $log->canRetry())
                                            <input type="checkbox" name="log_ids[]" value="{{ $log->id }}" class="log-checkbox">
                                            @endif
                                        </td>
                                        <td>{{ $log->id }}</td>
                                        <td>
                                            <i class="{{ $log->channel_icon }}"></i>
                                            {{ ucfirst($log->channel) }}
                                        </td>
                                        <td>
                                            <small>{{ $log->recipient }}</small>
                                        </td>
                                        <td>
                                            @if($log->notificationType)
                                                <span class="badge badge-secondary">
                                                    {{ $log->notificationType->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $log->status_color }}">
                                                {{ ucfirst($log->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($log->sent_at)
                                                <small>{{ $log->sent_at->format('Y-m-d H:i:s') }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->delivered_at)
                                                <small>{{ $log->delivered_at->format('Y-m-d H:i:s') }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $log->retry_count > 0 ? 'warning' : 'light' }}">
                                                {{ $log->retry_count }}/3
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('notification-logs.show', $log) }}"
                                               class="btn btn-sm btn-info"
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($log->canRetry())
                                                <button type="button"
                                                        class="btn btn-sm btn-warning"
                                                        title="Resend"
                                                        onclick="resendSingleNotification({{ $log->id }})">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <p class="text-muted">No notification logs found.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const logCheckboxes = document.querySelectorAll('.log-checkbox');
    const bulkResendBtn = document.getElementById('bulkResendBtn');
    const selectedCount = document.getElementById('selectedCount');
    const bulkResendForm = document.getElementById('bulkResendForm');

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        logCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    // Individual checkbox change
    logCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const checkedCount = document.querySelectorAll('.log-checkbox:checked').length;
        selectedCount.textContent = checkedCount + ' selected';
        bulkResendBtn.disabled = checkedCount === 0;
    }

    // Bulk resend confirmation
    bulkResendForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const checkedCount = document.querySelectorAll('.log-checkbox:checked').length;
        showConfirmationModal(
            'Bulk Resend Notifications',
            `Are you sure you want to resend ${checkedCount} notification(s)?`,
            'warning',
            function() {
                bulkResendForm.submit();
            }
        );
    });
});

function resendSingleNotification(logId) {
    showConfirmationModal(
        'Resend Notification',
        'Are you sure you want to resend this notification?',
        'warning',
        function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/notification-logs/${logId}/resend`;
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
@endpush
@endsection
