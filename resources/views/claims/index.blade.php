@extends('layouts.app')

@section('title', 'Claims Management')

@section('content')
    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        @if (isset($error))
            <div class="alert alert-danger">
                {{ $error }}
            </div>
        @endif

        <!-- DataTales Example -->
        <div class="card shadow mt-3 mb-4">
            <x-list-header
                    title="Claims Management"
                    subtitle="Manage all insurance claim records"
                    addRoute="claims.create"
                    addPermission="claim-create"
                    exportRoute="claims.export"
            />
            <div class="card-body">
                <form method="GET" action="{{ route('claims.index') }}" id="search_form">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="search">Search Claims</label>
                                <input type="text" class="form-control" id="search" name="search"
                                       placeholder="Claim number, customer name, policy..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="insurance_type">Insurance Type</label>
                                <select class="form-control" id="insurance_type" name="insurance_type">
                                    <option value="">All Types</option>
                                    <option value="Health" {{ request('insurance_type') == 'Health' ? 'selected' : '' }}>Health</option>
                                    <option value="Vehicle" {{ request('insurance_type') == 'Vehicle' ? 'selected' : '' }}>Vehicle</option>
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
                                <label for="date_from">Date From</label>
                                <input type="text" class="form-control date-picker" id="date_from" name="date_from"
                                       placeholder="DD/MM/YYYY" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date_to">Date To</label>
                                <input type="text" class="form-control date-picker" id="date_to" name="date_to"
                                       placeholder="DD/MM/YYYY" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="{{ route('claims.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('claims.index', ['sort_field' => 'claim_number', 'sort_order' => $sortField == 'claim_number' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Claim Number
                                        @if($sortField == 'claim_number')
                                            @if($sortOrder == 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Customer Name</th>
                                <th>Policy No</th>
                                <th>Registration/Vehicle No</th>
                                <th>
                                    <a href="{{ route('claims.index', ['sort_field' => 'insurance_type', 'sort_order' => $sortField == 'insurance_type' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Type
                                        @if($sortField == 'insurance_type')
                                            @if($sortOrder == 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Current Stage</th>
                                <th>
                                    <a href="{{ route('claims.index', ['sort_field' => 'incident_date', 'sort_order' => $sortField == 'incident_date' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Incident Date
                                        @if($sortField == 'incident_date')
                                            @if($sortOrder == 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('claims.index', ['sort_field' => 'status', 'sort_order' => $sortField == 'status' && $sortOrder == 'asc' ? 'desc' : 'asc']) }}">
                                        Status
                                        @if($sortField == 'status')
                                            @if($sortOrder == 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($claims as $claim)
                                <tr>
                                    <td>
                                        <strong class="text-primary">{{ $claim->claim_number }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $claim->customer->name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $claim->customer->mobile_number ?? '' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $claim->customerInsurance->policy_no ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        {{ $claim->customerInsurance->registration_no ?? '-' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $claim->insurance_type == 'Health' ? 'success' : 'primary' }}">
                                            {{ $claim->insurance_type }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($claim->currentStage)
                                            <span class="badge badge-warning">{{ $claim->currentStage->stage_name }}</span>
                                        @else
                                            <span class="badge badge-secondary">No Stage</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $claim->incident_date ? $claim->incident_date->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td>
                                        @if ($claim->status)
                                            @can('claim-edit')
                                                <a href="#"
                                                   class="badge badge-success text-decoration-none"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#confirmationModal"
                                                   data-title="Confirm Deactivation"
                                                   data-message="Are you sure you want to deactivate claim <strong>#{{ $claim->claim_no }}</strong>?"
                                                   data-confirm-text="Yes, Deactivate"
                                                   data-confirm-class="btn-warning"
                                                   data-action-url="{{ route('claims.status', [$claim->id, 0]) }}"
                                                   data-method="GET">
                                                    Active
                                                </a>
                                            @else
                                                <span class="badge badge-success">Active</span>
                                            @endcan
                                        @else
                                            @can('claim-edit')
                                                <a href="#"
                                                   class="badge badge-danger text-decoration-none"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#confirmationModal"
                                                   data-title="Confirm Activation"
                                                   data-message="Are you sure you want to activate claim <strong>#{{ $claim->claim_no }}</strong>?"
                                                   data-confirm-text="Yes, Activate"
                                                   data-confirm-class="btn-success"
                                                   data-action-url="{{ route('claims.status', [$claim->id, 1]) }}"
                                                   data-method="GET">
                                                    Inactive
                                                </a>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endcan
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @can('claim-list')
                                                <a href="{{ route('claims.show', $claim) }}"
                                                   class="btn btn-outline-info btn-sm"
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan
                                            @can('claim-edit')
                                                <a href="{{ route('claims.edit', $claim) }}"
                                                   class="btn btn-outline-warning btn-sm"
                                                   title="Edit Claim">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('claim-delete')
                                                <form method="POST" action="{{ route('claims.delete', $claim) }}"
                                                      id="delete-form-{{ $claim->id }}" style="display: inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                <button type="button"
                                                        class="btn btn-outline-danger btn-sm"
                                                        data-bs-toggle="tooltip"
                                                        title="Delete Claim"
                                                        onclick="confirmDelete({{ $claim->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            <h5>No Claims Found</h5>
                                            <p>No claims match your search criteria.</p>
                                            @can('claim-create')
                                                <a href="{{ route('claims.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Create First Claim
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <x-pagination-with-info :paginator="$claims" :request="$request" />
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize date pickers
            $('.date-picker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                orientation: 'bottom'
            });

            // Auto-submit form on filter change
            $('#insurance_type, #status').change(function() {
                $('#search_form').submit();
            });

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        // Delete confirmation function following UI guidelines
        function confirmDelete(claimId) {
            // Create a proper confirmation modal following the component guidelines
            const modalHtml = `
                <div class="modal fade" id="deleteModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header border-0 bg-danger text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Confirm Delete
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center py-3">
                                    <i class="fas fa-trash fa-3x text-danger mb-3"></i>
                                    <h6 class="mb-3">Are you sure you want to delete this claim?</h6>
                                    <p class="text-muted mb-0">This action cannot be undone. All associated data will be permanently removed.</p>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </button>
                                <button type="button" class="btn btn-danger" onclick="executeDelete(${claimId})">
                                    <i class="fas fa-trash me-1"></i> Delete Claim
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove any existing modal
            $('#deleteModal').remove();

            // Add modal to page and show
            $('body').append(modalHtml);
            $('#deleteModal').modal('show');

            // Clean up modal after it's hidden
            $('#deleteModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        }

        // Execute delete action
        function executeDelete(claimId) {
            $('#deleteModal').modal('hide');

            // Show loading state
            const deleteBtn = $(`button[onclick="confirmDelete(${claimId})"]`);
            const originalText = deleteBtn.html();
            deleteBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            // Submit the form
            setTimeout(() => {
                $(`#delete-form-${claimId}`).submit();
            }, 500);
        }
    </script>
@endpush