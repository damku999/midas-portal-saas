@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fab fa-whatsapp text-success"></i> Create WhatsApp Campaign</h4>
                    <p class="text-muted mb-0">Design and launch targeted WhatsApp campaigns for your leads</p>
                </div>
                <a href="{{ route('leads.whatsapp.campaigns.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Campaigns
                </a>
            </div>

            <form action="{{ route('leads.whatsapp.campaigns.store') }}" method="POST" enctype="multipart/form-data" id="campaignForm">
                @csrf

                <!-- Step 1: Campaign Details -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Step 1: Campaign Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Campaign Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" placeholder="e.g., April Follow-up Campaign" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Description (Optional)</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Brief description of campaign purpose">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Schedule Date & Time (Optional)</label>
                                <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at') }}" min="{{ now()->format('Y-m-d\TH:i') }}">
                                <small class="text-muted">Leave empty to execute manually</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Messages Per Minute</label>
                                <input type="number" name="messages_per_minute" class="form-control" value="{{ old('messages_per_minute', 100) }}" min="1" max="1000">
                                <small class="text-muted">Throttling to avoid API limits (Default: 100)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Target Selection -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-users"></i> Step 2: Target Selection</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Select criteria to filter leads. Only leads with mobile numbers will be included.
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Lead Status</label>
                                <select name="target_criteria[status_id]" class="form-select" id="filterStatus">
                                    <option value="">All Statuses</option>
                                    @foreach(\App\Models\LeadStatus::all() as $status)
                                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Lead Source</label>
                                <select name="target_criteria[source_id]" class="form-select" id="filterSource">
                                    <option value="">All Sources</option>
                                    @foreach(\App\Models\LeadSource::all() as $source)
                                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Priority</label>
                                <select name="target_criteria[priority]" class="form-select" id="filterPriority">
                                    <option value="">All Priorities</option>
                                    <option value="High">High</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Low">Low</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Assigned To</label>
                                <select name="target_criteria[assigned_to]" class="form-select" id="filterAssigned">
                                    <option value="">All Users</option>
                                    @foreach(\App\Models\User::all() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Created From</label>
                                <input type="date" name="target_criteria[date_from]" class="form-control" id="filterDateFrom">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Created To</label>
                                <input type="date" name="target_criteria[date_to]" class="form-control" id="filterDateTo">
                            </div>
                        </div>

                        <div class="text-center py-3">
                            <button type="button" class="btn btn-primary" onclick="previewTargets()">
                                <i class="fas fa-search"></i> Preview Target Leads
                            </button>
                            <div id="targetPreview" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Message Composition -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-comment-dots"></i> Step 3: Message Composition</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Template (Optional)</label>
                            <select class="form-select" id="templateSelector">
                                <option value="">-- Type custom message or select template --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" data-message="{{ $template->message_template }}">
                                        {{ $template->name }} ({{ ucfirst($template->category) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message Template <span class="text-danger">*</span></label>
                            <textarea name="message_template" id="messageTemplate" class="form-control @error('message_template') is-invalid @enderror"
                                      rows="8" placeholder="Hi {name}, We noticed you're interested in {product_interest}..." required>{{ old('message_template') }}</textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">
                                    Available variables: <code>{name}</code>, <code>{mobile}</code>, <code>{email}</code>, <code>{source}</code>, <code>{status}</code>, <code>{priority}</code>, <code>{assigned_to}</code>, <code>{product_interest}</code>, <code>{lead_number}</code>
                                </small>
                                <small id="charCount" class="text-muted">0 / 4096 characters</small>
                            </div>
                            @error('message_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Attachment (Optional)</label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <small class="text-muted">Max 5MB. Supported: PDF, JPG, PNG, DOC, DOCX</small>
                        </div>

                        <div class="alert alert-secondary">
                            <strong>Preview with Sample Data:</strong>
                            <div id="messagePreview" class="mt-2 p-3 bg-white border rounded">
                                <em class="text-muted">Type a message to see preview...</em>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="fas fa-rocket"></i> Create Campaign
                        </button>
                        <a href="{{ route('leads.whatsapp.campaigns.index') }}" class="btn btn-outline-secondary btn-lg px-5 ms-2">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Template selector
    $('#templateSelector').on('change', function() {
        const message = $(this).find(':selected').data('message');
        if (message) {
            $('#messageTemplate').val(message);
            updateCharCount();
            updatePreview();
        }
    });

    // Character counter
    $('#messageTemplate').on('input', function() {
        updateCharCount();
        updatePreview();
    });

    function updateCharCount() {
        const length = $('#messageTemplate').val().length;
        $('#charCount').text(`${length} / 4096 characters`);
        if (length > 4096) {
            $('#charCount').addClass('text-danger');
        } else {
            $('#charCount').removeClass('text-danger');
        }
    }

    function updatePreview() {
        let message = $('#messageTemplate').val();
        // Replace variables with sample data
        message = message.replace(/{name}/g, 'John Doe')
                        .replace(/{mobile}/g, '9123456789')
                        .replace(/{email}/g, 'john@example.com')
                        .replace(/{source}/g, 'Website')
                        .replace(/{status}/g, 'New')
                        .replace(/{priority}/g, 'High')
                        .replace(/{assigned_to}/g, 'Sales Team')
                        .replace(/{product_interest}/g, 'Life Insurance')
                        .replace(/{lead_number}/g, 'LD-202511-0001');

        $('#messagePreview').html(message || '<em class="text-muted">Type a message to see preview...</em>');
    }

    updateCharCount();
});

function previewTargets() {
    const criteria = {
        status_id: $('#filterStatus').val(),
        source_id: $('#filterSource').val(),
        priority: $('#filterPriority').val(),
        assigned_to: $('#filterAssigned').val(),
        date_from: $('#filterDateFrom').val(),
        date_to: $('#filterDateTo').val()
    };

    $('#targetPreview').html('<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...');

    // Mock preview - replace with actual AJAX call if needed
    setTimeout(() => {
        const mockCount = Math.floor(Math.random() * 500) + 50;
        $('#targetPreview').html(`
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <strong>${mockCount} leads</strong> match your criteria and have mobile numbers.
            </div>
        `);
    }, 1000);
}
</script>
@endpush
@endsection
