@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fab fa-whatsapp text-success"></i> WhatsApp Campaigns</h4>
            <p class="text-muted mb-0">Manage and monitor your lead WhatsApp marketing campaigns</p>
        </div>
        <div>
            <a href="{{ route('leads.whatsapp.analytics') }}" class="btn btn-outline-primary btn-sm me-2">
                <i class="fas fa-chart-line"></i> Analytics
            </a>
            <a href="{{ route('leads.whatsapp.campaigns.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Create Campaign
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Campaigns</p>
                            <h3 class="mb-0">{{ $campaigns->total() }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-bullhorn text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Active</p>
                            <h3 class="mb-0 text-warning">{{ $campaigns->where('status', 'active')->count() }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-spinner text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Completed</p>
                            <h3 class="mb-0 text-success">{{ $campaigns->where('status', 'completed')->count() }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-check-circle text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Draft</p>
                            <h3 class="mb-0 text-secondary">{{ $campaigns->where('status', 'draft')->count() }}</h3>
                        </div>
                        <div class="bg-secondary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-file-alt text-secondary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('leads.whatsapp.campaigns.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Created By</label>
                    <select name="created_by" class="form-select form-select-sm">
                        <option value="">All Users</option>
                        @foreach(\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}" {{ request('created_by') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('leads.whatsapp.campaigns.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Campaign Name</th>
                            <th>Status</th>
                            <th>Target Leads</th>
                            <th>Sent</th>
                            <th>Delivered</th>
                            <th>Failed</th>
                            <th class="text-center">Success Rate</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $campaign)
                        <tr>
                            <td>
                                <a href="{{ route('leads.whatsapp.campaigns.show', $campaign->id) }}" class="text-decoration-none fw-bold">
                                    {{ $campaign->name }}
                                </a>
                                @if($campaign->description)
                                <br><small class="text-muted">{{ Str::limit($campaign->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'scheduled' => 'info',
                                        'active' => 'warning',
                                        'completed' => 'success',
                                        'paused' => 'dark',
                                        'cancelled' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$campaign->status] ?? 'secondary' }}">
                                    {{ ucfirst($campaign->status) }}
                                </span>
                            </td>
                            <td><strong>{{ number_format($campaign->total_leads) }}</strong></td>
                            <td>
                                <span class="text-primary fw-bold">{{ number_format($campaign->sent_count) }}</span>
                            </td>
                            <td>
                                <span class="text-success fw-bold">{{ number_format($campaign->delivered_count) }}</span>
                            </td>
                            <td>
                                <span class="text-danger fw-bold">{{ number_format($campaign->failed_count) }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $successRate = $campaign->getSuccessRate();
                                    $rateColor = $successRate >= 80 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $rateColor }}">{{ $successRate }}%</span>
                            </td>
                            <td>
                                <small>{{ $campaign->creator->name ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $campaign->created_at->format('d M Y, h:i A') }}</small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('leads.whatsapp.campaigns.show', $campaign->id) }}" class="btn btn-outline-primary btn-sm" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($campaign->canExecute())
                                    <button onclick="executeCampaign({{ $campaign->id }})" class="btn btn-outline-success btn-sm" title="Execute">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    @endif

                                    @if($campaign->canPause())
                                    <button onclick="pauseCampaign({{ $campaign->id }})" class="btn btn-outline-warning btn-sm" title="Pause">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    @endif

                                    @if($campaign->failed_count > 0 && $campaign->auto_retry_failed)
                                    <button onclick="retryFailed({{ $campaign->id }})" class="btn btn-outline-info btn-sm" title="Retry Failed">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p class="mb-0">No campaigns found</p>
                                    <a href="{{ route('leads.whatsapp.campaigns.create') }}" class="btn btn-primary btn-sm mt-3">
                                        Create Your First Campaign
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $campaigns->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function executeCampaign(campaignId) {
    showConfirmationModal(
        'Execute Campaign',
        'Are you sure you want to start this campaign? Messages will be sent to all targeted leads.',
        'success',
        function() {
            showLoading();
            $.ajax({
                url: `/leads/whatsapp/campaigns/${campaignId}/execute`,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(response) {
                    hideLoading();
                    show_notification('success', response.message || 'Campaign started successfully');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    hideLoading();
                    show_notification('error', xhr.responseJSON?.message || 'Failed to execute campaign');
                }
            });
        }
    );
}

function pauseCampaign(campaignId) {
    showConfirmationModal(
        'Pause Campaign',
        'Do you want to pause this campaign? You can resume it later.',
        'warning',
        function() {
            showLoading();
            $.ajax({
                url: `/leads/whatsapp/campaigns/${campaignId}/pause`,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(response) {
                    hideLoading();
                    show_notification('success', 'Campaign paused successfully');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    hideLoading();
                    show_notification('error', xhr.responseJSON?.message || 'Failed to pause campaign');
                }
            });
        }
    );
}

function retryFailed(campaignId) {
    showConfirmationModal(
        'Retry Failed Messages',
        'This will retry sending failed messages in this campaign. Continue?',
        'info',
        function() {
            showLoading();
            $.ajax({
                url: `/leads/whatsapp/campaigns/${campaignId}/retry-failed`,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(response) {
                    hideLoading();
                    show_notification('success', `Retried ${response.data.retried} messages. Success: ${response.data.success}, Failed: ${response.data.failed}`);
                    setTimeout(() => location.reload(), 2000);
                },
                error: function(xhr) {
                    hideLoading();
                    show_notification('error', xhr.responseJSON?.message || 'Failed to retry messages');
                }
            });
        }
    );
}

function showConfirmationModal(title, message, variant, onConfirm) {
    const modalHtml = `
        <div class="modal fade" id="confirmModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body"><p>${message}</p></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-${variant}" id="confirmActionBtn">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    $('#confirmModal').remove();
    $('body').append(modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
    $('#confirmActionBtn').on('click', function() {
        modal.hide();
        if (typeof onConfirm === 'function') onConfirm();
    });
}

function showLoading() {
    if (typeof toastr !== 'undefined') {
        toastr.info('Processing...', '', {timeOut: 0, extendedTimeOut: 0, closeButton: false, tapToDismiss: false});
    }
}

function hideLoading() {
    if (typeof toastr !== 'undefined') {
        toastr.clear();
    }
}
</script>
@endpush
@endsection
