@extends('layouts.app')

@section('title', 'Notification Templates')

@section('styles')
<style>
.bulk-actions-bar {
    position: sticky;
    top: 0;
    z-index: 1020;
    background: #f8f9fc;
    border-bottom: 2px solid #4e73df;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: none;
}

.bulk-actions-bar.active {
    display: block;
}

.checkbox-cell {
    width: 40px;
    text-align: center;
}

.variable-badge {
    font-size: 10px;
    padding: 2px 6px;
    margin: 2px;
    display: inline-block;
}

.template-stats {
    font-size: 11px;
    color: #858796;
}

.progress-container {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
    min-width: 400px;
}

.progress-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9998;
}
</style>
@endsection

@section('content')
    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- Bulk Actions Bar (Hidden by default) -->
        <div class="bulk-actions-bar py-2 px-3" id="bulkActionsBar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-bold text-primary"><span id="selectedCount">0</span> template(s) selected</span>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-success btn-sm" id="bulkActivateBtn">
                        <i class="fas fa-check-circle"></i> Activate
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" id="bulkDeactivateBtn">
                        <i class="fas fa-times-circle"></i> Deactivate
                    </button>
                    <button type="button" class="btn btn-info btn-sm" id="bulkExportBtn">
                        <i class="fas fa-download"></i> Export JSON
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" id="clearSelectionBtn">
                        <i class="fas fa-times"></i> Clear Selection
                    </button>
                </div>
            </div>
        </div>

        <!-- Progress Overlay & Container -->
        <div class="progress-overlay" id="progressOverlay"></div>
        <div class="progress-container" id="progressContainer">
            <h5 class="mb-3" id="progressTitle">Processing...</h5>
            <div class="progress" style="height: 25px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated"
                     role="progressbar"
                     id="progressBar"
                     style="width: 0%">0%</div>
            </div>
            <p class="mt-2 mb-0 text-muted" id="progressText">Starting...</p>
        </div>

        <!-- DataTales Example -->
        <div class="card shadow mt-3 mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">Notification Templates Management</h6>
                <div class="d-flex gap-2">
                    @can('notification-template-create')
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-upload"></i> Import JSON
                        </button>
                        <a href="{{ route('notification-templates.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Template
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('notification-templates.index') }}" id="search_form">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="search">Search Templates</label>
                                <input type="text" class="form-control" id="search" name="search"
                                       placeholder="Type name, subject, content..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select class="form-control" id="category" name="category">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $key => $value)
                                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                            {{ ucfirst($value) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="channel">Channel</label>
                                <select class="form-control" id="channel" name="channel">
                                    <option value="">All Channels</option>
                                    <option value="whatsapp" {{ request('channel') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                    <option value="email" {{ request('channel') == 'email' ? 'selected' : '' }}>Email</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="{{ route('notification-templates.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="checkbox-cell">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th width="20%">
                                    <a href="{{ route('notification-templates.index', array_merge(request()->all(), ['sort_by' => 'notification_type_id', 'sort_order' => (request('sort_by') == 'notification_type_id' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}"
                                       class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                        <span>Notification Type</span>
                                        @if(request('sort_by') == 'notification_type_id')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="10%">
                                    <a href="{{ route('notification-templates.index', array_merge(request()->all(), ['sort_by' => 'channel', 'sort_order' => (request('sort_by') == 'channel' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}"
                                       class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                        <span>Channel</span>
                                        @if(request('sort_by') == 'channel')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="15%">Subject</th>
                                <th width="15%">Variables</th>
                                <th width="8%">
                                    <a href="{{ route('notification-templates.index', array_merge(request()->all(), ['sort_by' => 'is_active', 'sort_order' => (request('sort_by') == 'is_active' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}"
                                       class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                        <span>Status</span>
                                        @if(request('sort_by') == 'is_active')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="12%">
                                    <a href="{{ route('notification-templates.index', array_merge(request()->all(), ['sort_by' => 'updated_at', 'sort_order' => (request('sort_by') == 'updated_at' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}"
                                       class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                        <span>Updated</span>
                                        @if(request('sort_by') == 'updated_at')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
                                    <td class="checkbox-cell">
                                        <input type="checkbox" class="form-check-input template-checkbox"
                                               value="{{ $template->id }}"
                                               data-template-name="{{ $template->notificationType->name ?? 'Unknown' }}">
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark">{{ $template->notificationType->name ?? 'Unknown' }}</span>
                                            <small class="text-muted">
                                                <span class="badge bg-info text-white">{{ ucfirst($template->notificationType->category ?? 'N/A') }}</span>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($template->channel === 'whatsapp')
                                            <span class="badge bg-success"><i class="fab fa-whatsapp"></i> WhatsApp</span>
                                        @elseif($template->channel === 'email')
                                            <span class="badge bg-primary"><i class="fas fa-envelope"></i> Email</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="fas fa-paper-plane"></i> Both</span>
                                        @endif
                                    </td>
                                    <td style="word-break: break-word;">
                                        {{ $template->subject ?? '-' }}
                                        @if($template->versions_count > 0)
                                            <br><small class="text-muted">{{ $template->versions_count }} version(s)</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($template->available_variables))
                                            @foreach(array_slice($template->available_variables, 0, 3) as $var)
                                                <span class="variable-badge badge bg-secondary">{{ $var }}</span>
                                            @endforeach
                                            @if(count($template->available_variables) > 3)
                                                <span class="variable-badge badge bg-light text-dark">+{{ count($template->available_variables) - 3 }} more</span>
                                            @endif
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($template->is_active == 0)
                                            <span class="badge bg-danger text-white">Inactive</span>
                                        @else
                                            <span class="badge bg-success text-white">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $template->updated_at->format('d M Y, h:i A') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @can('notification-template-edit')
                                                <a href="{{ route('notification-templates.edit', $template) }}"
                                                    class="btn btn-primary btn-sm" title="Edit Template">
                                                    <i class="fa fa-pen"></i>
                                                </a>
                                            @endcan

                                            @can('notification-template-create')
                                                <button type="button" class="btn btn-info btn-sm duplicate-btn"
                                                        data-template-id="{{ $template->id }}"
                                                        data-template-name="{{ $template->notificationType->name ?? 'Unknown' }}"
                                                        title="Duplicate Template">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            @endcan

                                            <button type="button" class="btn btn-warning btn-sm analytics-btn"
                                                    data-template-id="{{ $template->id }}"
                                                    title="View Analytics">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>

                                            @can('notification-template-delete')
                                                <form action="{{ route('notification-templates.delete', $template) }}"
                                                      method="POST"
                                                      style="display: inline;"
                                                      data-confirm-submit="true"
                                                      data-title="Confirm Deletion"
                                                      data-message="Are you sure you want to delete the template for <strong>{{ $template->notificationType->name ?? 'this notification' }}</strong>?"
                                                      data-confirm-text="Yes, Delete"
                                                      data-confirm-class="btn-danger">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete Template">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        No templates found.
                                        @can('notification-template-create')
                                            <a href="{{ route('notification-templates.create') }}">Create your first template</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <x-pagination-with-info :paginator="$templates" />
                </div>
            </div>
        </div>

    </div>

    <!-- Duplicate Modal -->
    <div class="modal fade" id="duplicateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Duplicate Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="duplicateForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="duplicate_template_id" name="template_id">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Original Template</label>
                            <input type="text" class="form-control" id="duplicate_original_name" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Duplicate To Channel</label>
                            <select class="form-control" id="duplicate_channel" name="channel" required>
                                <option value="">Select Channel</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="email">Email</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Duplicate To Notification Type (Optional)</label>
                            <select class="form-control" id="duplicate_notification_type" name="notification_type_id">
                                <option value="">Keep Same Type</option>
                                @foreach($notificationTypes ?? [] as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->category }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="duplicate_inactive" name="inactive">
                            <label class="form-check-label" for="duplicate_inactive">
                                Create as inactive
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="duplicateSubmitBtn">
                            <i class="fas fa-copy"></i> Duplicate Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Templates from JSON</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="importForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Upload JSON File</label>
                            <input type="file" class="form-control" id="import_file" name="file" accept=".json" required>
                            <small class="text-muted">Upload a JSON file exported from this system</small>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="import_overwrite" name="overwrite">
                            <label class="form-check-label" for="import_overwrite">
                                Overwrite existing templates (match by notification type & channel)
                            </label>
                        </div>

                        <div id="importPreview" class="border rounded p-3 bg-light" style="display: none;">
                            <h6 class="fw-bold">Preview:</h6>
                            <div id="importPreviewContent"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="importSubmitBtn">
                            <i class="fas fa-upload"></i> Import Templates
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Analytics Modal -->
    <div class="modal fade" id="analyticsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Template Analytics</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="analyticsContent">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">Loading analytics...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const templateCheckboxes = document.querySelectorAll('.template-checkbox');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    const progressOverlay = document.getElementById('progressOverlay');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressTitle = document.getElementById('progressTitle');
    const progressText = document.getElementById('progressText');

    // Select All functionality
    selectAll.addEventListener('change', function() {
        templateCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionsBar();
    });

    // Individual checkbox selection
    templateCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionsBar);
    });

    // Update bulk actions bar visibility
    function updateBulkActionsBar() {
        const selectedCheckboxes = document.querySelectorAll('.template-checkbox:checked');
        const count = selectedCheckboxes.length;

        selectedCount.textContent = count;

        if (count > 0) {
            bulkActionsBar.classList.add('active');
        } else {
            bulkActionsBar.classList.remove('active');
        }

        // Update select all checkbox state
        if (count === templateCheckboxes.length && count > 0) {
            selectAll.checked = true;
            selectAll.indeterminate = false;
        } else if (count > 0) {
            selectAll.checked = false;
            selectAll.indeterminate = true;
        } else {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
    }

    // Clear selection
    document.getElementById('clearSelectionBtn').addEventListener('click', function() {
        templateCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateBulkActionsBar();
    });

    // Get selected template IDs
    function getSelectedTemplateIds() {
        return Array.from(document.querySelectorAll('.template-checkbox:checked'))
            .map(checkbox => checkbox.value);
    }

    // Show progress
    function showProgress(title, text = 'Starting...') {
        progressTitle.textContent = title;
        progressText.textContent = text;
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';
        progressOverlay.style.display = 'block';
        progressContainer.style.display = 'block';
    }

    // Update progress
    function updateProgress(percent, text) {
        progressBar.style.width = percent + '%';
        progressBar.textContent = percent + '%';
        progressText.textContent = text;
    }

    // Hide progress
    function hideProgress() {
        progressOverlay.style.display = 'none';
        progressContainer.style.display = 'none';
    }

    // Bulk Activate
    document.getElementById('bulkActivateBtn').addEventListener('click', function() {
        const ids = getSelectedTemplateIds();
        if (ids.length === 0) return;

        showConfirmationModal(
            'Activate Templates',
            `Are you sure you want to activate ${ids.length} template(s)?`,
            'success',
            function() {
                bulkUpdateStatus(ids, 1, 'Activating templates...');
            }
        );
    });

    // Bulk Deactivate
    document.getElementById('bulkDeactivateBtn').addEventListener('click', function() {
        const ids = getSelectedTemplateIds();
        if (ids.length === 0) return;

        showConfirmationModal(
            'Deactivate Templates',
            `Are you sure you want to deactivate ${ids.length} template(s)?`,
            'warning',
            function() {
                bulkUpdateStatus(ids, 0, 'Deactivating templates...');
            }
        );
    });

    // Bulk status update
    function bulkUpdateStatus(ids, status, title) {
        showProgress(title);

        fetch('{{ route("notification-templates.bulk-update-status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: ids, status: status })
        })
        .then(response => response.json())
        .then(data => {
            hideProgress();
            if (data.success) {
                toastr.success(data.message);
                location.reload();
            } else {
                toastr.error(data.message || 'Operation failed');
            }
        })
        .catch(error => {
            hideProgress();
            toastr.error('Error: ' + error.message);
        });
    }

    // Bulk Export
    document.getElementById('bulkExportBtn').addEventListener('click', function() {
        const ids = getSelectedTemplateIds();
        if (ids.length === 0) return;

        showProgress('Exporting templates...', 'Preparing export...');

        fetch('{{ route("notification-templates.bulk-export") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.blob())
        .then(blob => {
            hideProgress();

            // Create download link
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `templates_export_${Date.now()}.json`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            toastr.success('Templates exported successfully');
        })
        .catch(error => {
            hideProgress();
            toastr.error('Export failed: ' + error.message);
        });
    });

    // Bulk Delete
    document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
        const ids = getSelectedTemplateIds();
        if (ids.length === 0) return;

        showConfirmationModal(
            'Permanently Delete Templates',
            `<strong>Warning:</strong> This will permanently delete ${ids.length} template(s). This action cannot be undone!`,
            'danger',
            function() {
                performBulkDelete(ids);
            }
        );
    });

    function performBulkDelete(ids) {
        showProgress('Deleting templates...', 'Please wait...');

        fetch('{{ route("notification-templates.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            hideProgress();
            if (data.success) {
                toastr.success(data.message);
                location.reload();
            } else {
                toastr.error(data.message || 'Delete failed');
            }
        })
        .catch(error => {
            hideProgress();
            toastr.error('Error: ' + error.message);
        });
    }

    // Duplicate Template
    document.querySelectorAll('.duplicate-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const templateId = this.dataset.templateId;
            const templateName = this.dataset.templateName;

            document.getElementById('duplicate_template_id').value = templateId;
            document.getElementById('duplicate_original_name').value = templateName;

            const modal = new bootstrap.Modal(document.getElementById('duplicateModal'));
            modal.show();
        });
    });

    // Submit duplicate form
    document.getElementById('duplicateForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = document.getElementById('duplicateSubmitBtn');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Duplicating...';

        fetch('{{ route("notification-templates.duplicate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                location.reload();
            } else {
                toastr.error(data.message || 'Duplication failed');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-copy"></i> Duplicate Template';
            }
        })
        .catch(error => {
            toastr.error('Error: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-copy"></i> Duplicate Template';
        });
    });

    // Import templates - preview on file select
    document.getElementById('import_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(event) {
            try {
                const json = JSON.parse(event.target.result);
                const preview = document.getElementById('importPreview');
                const content = document.getElementById('importPreviewContent');

                if (Array.isArray(json)) {
                    content.innerHTML = `
                        <p class="mb-2"><strong>${json.length}</strong> template(s) found in file:</p>
                        <ul class="mb-0">
                            ${json.slice(0, 5).map(t => `<li>${t.notification_type?.name || 'Unknown'} - ${t.channel}</li>`).join('')}
                            ${json.length > 5 ? `<li class="text-muted">... and ${json.length - 5} more</li>` : ''}
                        </ul>
                    `;
                    preview.style.display = 'block';
                } else {
                    content.innerHTML = '<p class="text-danger">Invalid JSON format</p>';
                    preview.style.display = 'block';
                }
            } catch (err) {
                const preview = document.getElementById('importPreview');
                const content = document.getElementById('importPreviewContent');
                content.innerHTML = '<p class="text-danger">Invalid JSON file</p>';
                preview.style.display = 'block';
            }
        };
        reader.readAsText(file);
    });

    // Submit import form
    document.getElementById('importForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = document.getElementById('importSubmitBtn');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';

        showProgress('Importing templates...', 'Processing file...');

        fetch('{{ route("notification-templates.bulk-import") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideProgress();

            if (data.success) {
                toastr.success(data.message);
                location.reload();
            } else {
                toastr.error(data.message || 'Import failed');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-upload"></i> Import Templates';
            }
        })
        .catch(error => {
            hideProgress();
            toastr.error('Error: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload"></i> Import Templates';
        });
    });

    // View Analytics
    document.querySelectorAll('.analytics-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const templateId = this.dataset.templateId;

            const modal = new bootstrap.Modal(document.getElementById('analyticsModal'));
            modal.show();

            // Load analytics
            fetch(`{{ url('notification-templates') }}/${templateId}/analytics`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayAnalytics(data.analytics);
                    } else {
                        document.getElementById('analyticsContent').innerHTML =
                            '<div class="alert alert-danger">Failed to load analytics</div>';
                    }
                })
                .catch(error => {
                    document.getElementById('analyticsContent').innerHTML =
                        '<div class="alert alert-danger">Error loading analytics: ' + error.message + '</div>';
                });
        });
    });

    function displayAnalytics(analytics) {
        const content = document.getElementById('analyticsContent');
        content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Variable Usage</h6>
                            <div class="mb-2">
                                <strong>Used Variables (${analytics.variables_used.length}):</strong>
                                ${analytics.variables_used.length > 0
                                    ? analytics.variables_used.map(v => `<span class="badge bg-primary me-1">${v}</span>`).join('')
                                    : '<span class="text-muted">None</span>'}
                            </div>
                            <div>
                                <strong>Unused Variables (${analytics.variables_unused.length}):</strong>
                                ${analytics.variables_unused.length > 0
                                    ? analytics.variables_unused.slice(0, 10).map(v => `<span class="badge bg-secondary me-1">${v}</span>`).join('')
                                    : '<span class="text-success">All available variables used</span>'}
                                ${analytics.variables_unused.length > 10 ? `<span class="text-muted">... +${analytics.variables_unused.length - 10} more</span>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Template Statistics</h6>
                            <ul class="list-unstyled mb-0">
                                <li><strong>Total Versions:</strong> ${analytics.versions_count}</li>
                                <li><strong>Test Sends:</strong> ${analytics.test_sends}</li>
                                <li><strong>Character Count:</strong> ${analytics.character_count}</li>
                                <li><strong>Last Modified:</strong> ${analytics.last_modified}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Template Content Preview</h6>
                    <pre class="bg-light p-3 rounded" style="white-space: pre-wrap;">${analytics.content_preview}</pre>
                </div>
            </div>
        `;
    }
});
</script>
@endsection
