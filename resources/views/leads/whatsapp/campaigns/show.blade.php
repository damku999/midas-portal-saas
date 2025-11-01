@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fab fa-whatsapp text-success"></i> {{ $campaign->name }}</h4>
            <p class="text-muted mb-0">Campaign Details & Performance</p>
        </div>
        <div>
            <a href="{{ route('leads.whatsapp.campaigns.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Status & Actions -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-2">Campaign Status</h6>
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
                    <span class="badge bg-{{ $statusColors[$campaign->status] ?? 'secondary' }} fs-6">
                        {{ ucfirst($campaign->status) }}
                    </span>
                    @if($campaign->scheduled_at)
                    <div class="mt-2 small text-muted">
                        <i class="fas fa-clock"></i> Scheduled: {{ $campaign->scheduled_at->format('d M Y, h:i A') }}
                    </div>
                    @endif
                </div>
                <div class="col-md-6 text-end">
                    @if($campaign->canExecute())
                    <button onclick="executeCampaign()" class="btn btn-success">
                        <i class="fas fa-play"></i> Execute Campaign
                    </button>
                    @endif

                    @if($campaign->canPause())
                    <button onclick="pauseCampaign()" class="btn btn-warning">
                        <i class="fas fa-pause"></i> Pause Campaign
                    </button>
                    @endif

                    @if($campaign->failed_count > 0)
                    <button onclick="retryFailed()" class="btn btn-info">
                        <i class="fas fa-redo"></i> Retry Failed ({{ $campaign->failed_count }})
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0">{{ number_format($statistics['total_leads']) }}</h3>
                    <small class="text-muted">Total Leads</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-paper-plane fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0 text-primary">{{ number_format($statistics['sent_count']) }}</h3>
                    <small class="text-muted">Sent</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-check-double fa-2x text-success mb-2"></i>
                    <h3 class="mb-0 text-success">{{ number_format($statistics['delivered_count']) }}</h3>
                    <small class="text-muted">Delivered</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-eye fa-2x text-info mb-2"></i>
                    <h3 class="mb-0 text-info">{{ number_format($statistics['read_count']) }}</h3>
                    <small class="text-muted">Read</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                    <h3 class="mb-0 text-danger">{{ number_format($statistics['failed_count']) }}</h3>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0 text-warning">{{ number_format($statistics['pending_count']) }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Success Rate</h6>
                    @php
                        $successRate = $statistics['success_rate'];
                        $rateColor = $successRate >= 80 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger');
                    @endphp
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-{{ $rateColor }}" style="width: {{ $successRate }}%">
                            <strong>{{ $successRate }}%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Delivery Rate</h6>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-info" style="width: {{ $statistics['delivery_rate'] }}%">
                            <strong>{{ $statistics['delivery_rate'] }}%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Read Rate</h6>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-primary" style="width: {{ $statistics['read_rate'] }}%">
                            <strong>{{ $statistics['read_rate'] }}%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Details -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Campaign Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted" width="40%">Created By:</td>
                            <td><strong>{{ $campaign->creator->name ?? 'N/A' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created At:</td>
                            <td>{{ $campaign->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                        @if($campaign->started_at)
                        <tr>
                            <td class="text-muted">Started At:</td>
                            <td>{{ $campaign->started_at->format('d M Y, h:i A') }}</td>
                        </tr>
                        @endif
                        @if($campaign->completed_at)
                        <tr>
                            <td class="text-muted">Completed At:</td>
                            <td>{{ $campaign->completed_at->format('d M Y, h:i A') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Throttling:</td>
                            <td>{{ $campaign->messages_per_minute }} msgs/min</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Auto Retry:</td>
                            <td>
                                @if($campaign->auto_retry_failed)
                                <span class="badge bg-success">Enabled</span> (Max {{ $campaign->max_retry_attempts }} attempts)
                                @else
                                <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    @if($campaign->description)
                    <hr>
                    <p class="mb-0"><strong>Description:</strong><br>{{ $campaign->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-comment-dots"></i> Message Template</h6>
                </div>
                <div class="card-body">
                    <div class="p-3 bg-light rounded mb-3" style="white-space: pre-wrap;">{{ $campaign->message_template }}</div>

                    @if($campaign->attachment_path)
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-paperclip"></i> <strong>Attachment:</strong> {{ $campaign->attachment_type }} file included
                        <a href="{{ $campaign->getAttachmentUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary float-end">
                            <i class="fas fa-download"></i> View
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Status Breakdown -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-list"></i> Individual Lead Status ({{ $campaign->campaignLeads->count() }} leads)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Lead</th>
                            <th>Mobile</th>
                            <th>Status</th>
                            <th>Sent At</th>
                            <th>Delivered At</th>
                            <th>Retry Count</th>
                            <th>Error Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaign->campaignLeads->take(50) as $campaignLead)
                        <tr>
                            <td>
                                <a href="{{ route('leads.show', $campaignLead->lead_id) }}" class="text-decoration-none">
                                    {{ $campaignLead->lead->name ?? 'N/A' }}
                                </a>
                            </td>
                            <td><small>{{ $campaignLead->lead->mobile_number ?? 'N/A' }}</small></td>
                            <td>
                                @php
                                    $statusIcons = [
                                        'pending' => ['icon' => 'clock', 'color' => 'secondary'],
                                        'sent' => ['icon' => 'check', 'color' => 'primary'],
                                        'delivered' => ['icon' => 'check-double', 'color' => 'success'],
                                        'read' => ['icon' => 'eye', 'color' => 'info'],
                                        'failed' => ['icon' => 'times-circle', 'color' => 'danger']
                                    ];
                                    $statusData = $statusIcons[$campaignLead->status] ?? ['icon' => 'question', 'color' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $statusData['color'] }}">
                                    <i class="fas fa-{{ $statusData['icon'] }}"></i> {{ ucfirst($campaignLead->status) }}
                                </span>
                            </td>
                            <td><small>{{ $campaignLead->sent_at?->format('d M, h:i A') ?? '-' }}</small></td>
                            <td><small>{{ $campaignLead->delivered_at?->format('d M, h:i A') ?? '-' }}</small></td>
                            <td class="text-center">
                                @if($campaignLead->retry_count > 0)
                                <span class="badge bg-warning">{{ $campaignLead->retry_count }}</span>
                                @else
                                <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                @if($campaignLead->error_message)
                                <small class="text-danger">{{ Str::limit($campaignLead->error_message, 50) }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No leads in this campaign</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($campaign->campaignLeads->count() > 50)
            <div class="alert alert-info mt-3 mb-0">
                <i class="fas fa-info-circle"></i> Showing first 50 leads. Total: {{ $campaign->campaignLeads->count() }} leads.
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function executeCampaign() {
    showConfirmationModal(
        'Execute Campaign',
        'Start sending messages to all targeted leads?',
        'success',
        function() {
            showLoading();
            $.ajax({
                url: `/leads/whatsapp/campaigns/{{ $campaign->id }}/execute`,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(response) {
                    hideLoading();
                    show_notification('success', response.message || 'Campaign started');
                    setTimeout(() => location.reload(), 2000);
                },
                error: function(xhr) {
                    hideLoading();
                    show_notification('error', xhr.responseJSON?.message || 'Failed to execute');
                }
            });
        }
    );
}

function pauseCampaign() {
    showConfirmationModal(
        'Pause Campaign',
        'Pause this campaign? You can resume later.',
        'warning',
        function() {
            showLoading();
            $.ajax({
                url: `/leads/whatsapp/campaigns/{{ $campaign->id }}/pause`,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(response) {
                    hideLoading();
                    show_notification('success', 'Campaign paused');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    hideLoading();
                    show_notification('error', xhr.responseJSON?.message || 'Failed to pause');
                }
            });
        }
    );
}

function retryFailed() {
    showConfirmationModal(
        'Retry Failed Messages',
        'Retry all failed messages in this campaign?',
        'info',
        function() {
            showLoading();
            $.ajax({
                url: `/leads/whatsapp/campaigns/{{ $campaign->id }}/retry-failed`,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(response) {
                    hideLoading();
                    show_notification('success', `Retried: ${response.data.success} success, ${response.data.failed} failed`);
                    setTimeout(() => location.reload(), 2000);
                },
                error: function(xhr) {
                    hideLoading();
                    show_notification('error', xhr.responseJSON?.message || 'Retry failed');
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
        toastr.info('Processing...', '', {timeOut: 0, extendedTimeOut: 0});
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
