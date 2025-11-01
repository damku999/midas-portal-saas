@extends('layouts.app')

@section('title', 'Leads List')

@section('content')
    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- DataTales Example -->
        <div class="card shadow mt-3 mb-4">
            <x-list-header
                    title="Leads Management"
                    subtitle="Manage all lead records"
                    addRoute="leads.create"
                    addPermission="lead-create"
            />

            <!-- Bulk Actions Bar -->
            <div class="card-body border-bottom" id="bulkActionsBar" style="display:none;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span id="selectedCount" class="fw-bold">0</span> leads selected
                    </div>
                    <div class="d-flex gap-2">
                        @if (auth()->user()->hasPermissionTo('lead-bulk-assign'))
                            <button type="button" class="btn btn-info btn-sm" onclick="showBulkAssignModal()">
                                <i class="fas fa-user-tag me-1"></i>Bulk Assign
                            </button>
                        @endif
                        @if (auth()->user()->hasPermissionTo('lead-bulk-convert'))
                            <button type="button" class="btn btn-success btn-sm" onclick="bulkConvert()">
                                <i class="fas fa-user-check me-1"></i>Bulk Convert
                            </button>
                        @endif
                        @if (auth()->user()->hasPermissionTo('lead-whatsapp-send'))
                            <button type="button" class="btn btn-success btn-sm" onclick="showBulkWhatsAppModal()">
                                <i class="fab fa-whatsapp me-1"></i>Send WhatsApp
                            </button>
                        @endif
                        <button type="button" class="btn btn-secondary btn-sm" onclick="clearSelection()">
                            <i class="fas fa-times me-1"></i>Clear Selection
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('leads.index') }}" id="search_form">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="search">Search Leads</label>
                                <input type="text" class="form-control" id="search" name="search"
                                       placeholder="Name, email, mobile..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="source_id">Source</label>
                                <select class="form-control" id="source_id" name="source_id">
                                    <option value="">All Sources</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source->id }}" {{ request('source_id') == $source->id ? 'selected' : '' }}>
                                            {{ $source->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status_id">Status</label>
                                <select class="form-control" id="status_id" name="status_id">
                                    <option value="">All Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="assigned_to">Assigned To</label>
                                <select class="form-control" id="assigned_to" name="assigned_to">
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <select class="form-control" id="priority" name="priority">
                                    <option value="">All Priority</option>
                                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('leads.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i>
                                </a>
                                @if (auth()->user()->hasPermissionTo('lead-export'))
                                    <a href="{{ route('leads.export', request()->query()) }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-export"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="3%">
                                    <input type="checkbox" id="selectAll" onclick="toggleAll(this)">
                                </th>
                                <th width="7%">Lead #</th>
                                <th width="11%">Name</th>
                                <th width="10%">Mobile</th>
                                <th width="11%">Email</th>
                                <th width="7%">Source</th>
                                <th width="10%">Status</th>
                                <th width="7%">Priority</th>
                                <th width="10%">Assigned To</th>
                                <th width="7%">Activities</th>
                                <th width="17%">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($leads as $lead)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="lead-checkbox" value="{{ $lead->id }}" onchange="updateSelection()">
                                    </td>
                                    <td>{{ $lead->lead_number }}</td>
                                    <td>{{ $lead->name }}</td>
                                    <td>{{ $lead->mobile_number }}</td>
                                    <td>{{ $lead->email ?? 'N/A' }}</td>
                                    <td>{{ $lead->source->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $lead->status->color ?? '#6c757d' }}">
                                            {{ $lead->status->name ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($lead->priority == 'high')
                                            <span class="badge bg-danger">High</span>
                                        @elseif($lead->priority == 'medium')
                                            <span class="badge bg-warning">Medium</span>
                                        @else
                                            <span class="badge bg-info">Low</span>
                                        @endif
                                    </td>
                                    <td>{{ $lead->assignedUser->first_name ?? 'Unassigned' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary" title="{{ $lead->activities->count() }} activities recorded">
                                            <i class="fas fa-history"></i> {{ $lead->activities->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap" style="gap: 6px; justify-content: flex-start; align-items: center;">
                                            @if (auth()->user()->hasPermissionTo('lead-view'))
                                                <a href="{{ route('leads.show', ['lead' => $lead->id]) }}"
                                                    class="btn btn-info btn-sm" title="View Lead">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            @endif

                                            @if (auth()->user()->hasPermissionTo('lead-edit'))
                                                <a href="{{ route('leads.edit', ['lead' => $lead->id]) }}"
                                                    class="btn btn-primary btn-sm" title="Edit Lead">
                                                    <i class="fa fa-pen"></i>
                                                </a>
                                            @endif

                                            @if (auth()->user()->hasPermissionTo('lead-delete'))
                                                <a class="btn btn-danger btn-sm" href="javascript:void(0);" title="Delete Lead"
                                                    onclick="delete_conf_common('{{ $lead['id'] }}','Lead','Lead', '{{ route('leads.index') }}');">
                                                    <i class="fa fa-trash-alt"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11">No Record Found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ $leads->links() }}
                </div>
            </div>
        </div>

        <!-- Bulk Assign Modal -->
        <div class="modal fade" id="bulkAssignModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('leads.bulk-assign') }}" method="POST" id="bulkAssignForm">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Bulk Assign Leads</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="bulkAssignLeadIds"></div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Assign To</label>
                                <select class="form-select form-select-sm" name="assigned_to" required>
                                    <option value="">Select User</option>
                                    @foreach(\App\Models\User::where('status', true)->get(['id', 'first_name', 'last_name']) as $user)
                                        <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="fas fa-check me-1"></i>Assign Selected Leads
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk WhatsApp Modal -->
        <div class="modal fade" id="bulkWhatsAppModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="bulkWhatsAppForm" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Send Bulk WhatsApp Messages</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Sending to <strong id="whatsappLeadCount">0</strong> selected leads
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Select Template (Optional)</label>
                                <select class="form-select form-select-sm" id="whatsappTemplateSelect">
                                    <option value="">-- Type custom message or select template --</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Message</label>
                                <textarea class="form-control" id="whatsappMessage" name="message" rows="6"
                                          placeholder="Hi {name}, We noticed you're interested in our services..." required></textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Variables: <code>{name}</code>, <code>{mobile}</code>, <code>{email}</code>, <code>{source}</code></small>
                                    <small id="whatsappCharCount" class="text-muted">0 / 4096</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Attachment (Optional)</label>
                                <input type="file" class="form-control" id="whatsappAttachment" name="attachment"
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted">Max 5MB. Supported: PDF, JPG, PNG, DOC, DOCX</small>
                            </div>

                            <div id="whatsappLeadIds"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-sm" id="sendWhatsAppBtn">
                                <i class="fab fa-whatsapp me-1"></i>Send WhatsApp Messages
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
<script>
function toggleAll(checkbox) {
    $('.lead-checkbox').prop('checked', checkbox.checked);
    updateSelection();
}

function updateSelection() {
    const selected = $('.lead-checkbox:checked').length;
    $('#selectedCount').text(selected);

    if (selected > 0) {
        $('#bulkActionsBar').slideDown();
    } else {
        $('#bulkActionsBar').slideUp();
    }
}

function clearSelection() {
    $('.lead-checkbox').prop('checked', false);
    $('#selectAll').prop('checked', false);
    updateSelection();
}

function showBulkAssignModal() {
    const selectedIds = [];
    $('.lead-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        show_notification('warning', 'Please select at least one lead');
        return;
    }

    // Clear previous hidden inputs
    $('#bulkAssignLeadIds').empty();

    // Add hidden input for each selected lead ID
    selectedIds.forEach(function(id) {
        $('#bulkAssignLeadIds').append('<input type="hidden" name="lead_ids[]" value="' + id + '">');
    });

    showModal('bulkAssignModal');
}

function bulkConvert() {
    const selectedIds = [];
    $('.lead-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        show_notification('warning', 'Please select at least one lead');
        return;
    }

    showConfirmationModal(
        'Bulk Convert Leads',
        `Are you sure you want to convert ${selectedIds.length} leads to customers?`,
        'success',
        function() {
            convertLeadsToCustomers(selectedIds);
        }
    );
}

function convertLeadsToCustomers(selectedIds) {

    showLoading('Converting leads...');

    $.ajax({
        url: '{{ route("leads.bulk-convert") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            lead_ids: selectedIds
        },
        success: function(response) {
            hideLoading();
            if (response.status === 'success' || response.message) {
                show_notification('success', response.message || 'Leads converted successfully');
                setTimeout(() => window.location.reload(), 1500);
            }
        },
        error: function(xhr) {
            hideLoading();
            const error = xhr.responseJSON?.message || 'Failed to convert leads';
            show_notification('error', error);
        }
    });
}

function showBulkWhatsAppModal() {
    const selectedIds = [];
    $('.lead-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        show_notification('warning', 'Please select at least one lead');
        return;
    }

    $('#whatsappLeadCount').text(selectedIds.length);
    $('#whatsappLeadIds').empty();
    selectedIds.forEach(function(id) {
        $('#whatsappLeadIds').append('<input type="hidden" name="lead_ids[]" value="' + id + '">');
    });

    // Load templates
    $.get('{{ route("leads.whatsapp.templates.api") }}', function(response) {
        if (response.success && response.data) {
            $('#whatsappTemplateSelect').html('<option value="">-- Type custom message or select template --</option>');
            response.data.forEach(function(template) {
                $('#whatsappTemplateSelect').append(
                    `<option value="${template.id}" data-message="${template.message_template}">${template.name} (${template.category})</option>`
                );
            });
        }
    });

    showModal('bulkWhatsAppModal');
}

// Template selection
$('#whatsappTemplateSelect').on('change', function() {
    const message = $(this).find(':selected').data('message');
    if (message) {
        $('#whatsappMessage').val(message);
        updateWhatsAppCharCount();
    }
});

// Character counter
$('#whatsappMessage').on('input', updateWhatsAppCharCount);

function updateWhatsAppCharCount() {
    const length = $('#whatsappMessage').val().length;
    $('#whatsappCharCount').text(`${length} / 4096`);
    if (length > 4096) {
        $('#whatsappCharCount').addClass('text-danger');
    } else {
        $('#whatsappCharCount').removeClass('text-danger');
    }
}

// Submit bulk WhatsApp form
$('#bulkWhatsAppForm').on('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const selectedIds = [];
    $('.lead-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });
    selectedIds.forEach(id => formData.append('lead_ids[]', id));

    $('#sendWhatsAppBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

    $.ajax({
        url: '{{ route("leads.whatsapp.bulk-send") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#sendWhatsAppBtn').prop('disabled', false).html('<i class="fab fa-whatsapp me-1"></i>Send WhatsApp Messages');
            if (response.success) {
                show_notification('success', response.message || 'WhatsApp messages sent successfully');
                $('#bulkWhatsAppModal').modal('hide');
                $('#bulkWhatsAppForm')[0].reset();
                clearSelection();
            }
        },
        error: function(xhr) {
            $('#sendWhatsAppBtn').prop('disabled', false).html('<i class="fab fa-whatsapp me-1"></i>Send WhatsApp Messages');
            const error = xhr.responseJSON?.message || 'Failed to send WhatsApp messages';
            show_notification('error', error);
        }
    });
});
</script>
@endsection
