@extends('layouts.app')

@section('title', 'Edit Lead')

@section('content')

    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- Lead Action Buttons -->
        @if(!$lead->converted_at)
        <div class="card shadow mb-3 mt-2">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2">
                    @if (auth()->user()->hasPermissionTo('lead-convert'))
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#convertModal">
                            <i class="fas fa-user-check me-1"></i>Convert to Customer
                        </button>
                    @endif
                    @if (auth()->user()->hasPermissionTo('lead-assign'))
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#assignModal">
                            <i class="fas fa-user-tag me-1"></i>Assign Lead
                        </button>
                    @endif
                    @if (auth()->user()->hasPermissionTo('lead-status-change'))
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#statusModal">
                            <i class="fas fa-exchange-alt me-1"></i>Change Status
                        </button>
                    @endif
                    @if (auth()->user()->hasPermissionTo('lead-mark-lost'))
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#lostModal">
                            <i class="fas fa-times-circle me-1"></i>Mark as Lost
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-success mt-2">
            <i class="fas fa-check-circle me-2"></i>This lead has been converted to a customer.
            @if($lead->convertedCustomer)
                <a href="{{ route('customers.show', $lead->convertedCustomer->id) }}" class="alert-link">View Customer Profile</a>
            @endif
        </div>
        @endif

        <!-- Lead Form -->
        <div class="card shadow mb-3 mt-2">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">Edit Lead - {{ $lead->lead_number }}</h6>
                <a href="{{ route('leads.index') }}" onclick="window.history.go(-1); return false;"
                    class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                    <i class="fas fa-chevron-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <form method="POST" action="{{ route('leads.update', $lead->id) }}">
                @csrf
                @method('PUT')
                <div class="card-body py-3">
                    <!-- Section 1: Basic Information -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-user me-2"></i>Basic Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Name</label>
                                <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror"
                                    name="name" placeholder="Enter full name" value="{{ old('name', $lead->name) }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                                    name="email" placeholder="Enter email address" value="{{ old('email', $lead->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Mobile Number</label>
                                <input type="tel" class="form-control form-control-sm @error('mobile_number') is-invalid @enderror"
                                    name="mobile_number" placeholder="Enter mobile number" value="{{ old('mobile_number', $lead->mobile_number) }}">
                                @error('mobile_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Alternate Mobile</label>
                                <input type="tel" class="form-control form-control-sm @error('alternate_mobile') is-invalid @enderror"
                                    name="alternate_mobile" placeholder="Enter alternate mobile" value="{{ old('alternate_mobile', $lead->alternate_mobile) }}">
                                @error('alternate_mobile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date of Birth</label>
                                <input type="text" class="form-control form-control-sm datepicker @error('date_of_birth') is-invalid @enderror"
                                    name="date_of_birth" placeholder="DD/MM/YYYY" value="{{ old('date_of_birth', $lead->date_of_birth) }}">
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Occupation</label>
                                <input type="text" class="form-control form-control-sm @error('occupation') is-invalid @enderror"
                                    name="occupation" placeholder="Enter occupation" value="{{ old('occupation', $lead->occupation) }}">
                                @error('occupation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Address Information -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-map-marker-alt me-2"></i>Address Information</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Address</label>
                                <textarea class="form-control form-control-sm @error('address') is-invalid @enderror"
                                    name="address" rows="2" placeholder="Enter address">{{ old('address', $lead->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" class="form-control form-control-sm @error('city') is-invalid @enderror"
                                    name="city" placeholder="Enter city" value="{{ old('city', $lead->city) }}">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">State</label>
                                <input type="text" class="form-control form-control-sm @error('state') is-invalid @enderror"
                                    name="state" placeholder="Enter state" value="{{ old('state', $lead->state) }}">
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Pincode</label>
                                <input type="text" class="form-control form-control-sm @error('pincode') is-invalid @enderror"
                                    name="pincode" placeholder="Enter pincode" value="{{ old('pincode', $lead->pincode) }}">
                                @error('pincode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Lead Details -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Lead Details</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Source</label>
                                <select class="form-select form-select-sm @error('source_id') is-invalid @enderror" name="source_id">
                                    <option value="">Select Source</option>
                                    @foreach($sources as $source)
                                        <option value="{{ $source->id }}" {{ old('source_id', $lead->source_id) == $source->id ? 'selected' : '' }}>
                                            {{ $source->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('source_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Status</label>
                                <select class="form-select form-select-sm @error('status_id') is-invalid @enderror" name="status_id">
                                    <option value="">Select Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ old('status_id', $lead->status_id) == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Priority</label>
                                <select class="form-select form-select-sm @error('priority') is-invalid @enderror" name="priority">
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority', $lead->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $lead->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $lead->priority) == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Product Interest</label>
                                <input type="text" class="form-control form-control-sm @error('product_interest') is-invalid @enderror"
                                    name="product_interest" placeholder="Enter product interest" value="{{ old('product_interest', $lead->product_interest) }}">
                                @error('product_interest')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Assignment -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-user-tag me-2"></i>Assignment</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Assigned To</label>
                                <select class="form-select form-select-sm @error('assigned_to') is-invalid @enderror" name="assigned_to">
                                    <option value="">Select User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_to', $lead->assigned_to) == $user->id ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Relationship Manager</label>
                                <select class="form-select form-select-sm @error('relationship_manager_id') is-invalid @enderror" name="relationship_manager_id">
                                    <option value="">Select RM</option>
                                    @foreach($relationshipManagers as $rm)
                                        <option value="{{ $rm->id }}" {{ old('relationship_manager_id', $lead->relationship_manager_id) == $rm->id ? 'selected' : '' }}>
                                            {{ $rm->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('relationship_manager_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Reference User</label>
                                <select class="form-select form-select-sm @error('reference_user_id') is-invalid @enderror" name="reference_user_id">
                                    <option value="">Select Reference</option>
                                    @foreach($referenceUsers as $reference)
                                        <option value="{{ $reference->id }}" {{ old('reference_user_id', $lead->reference_user_id) == $reference->id ? 'selected' : '' }}>
                                            {{ $reference->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('reference_user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: Follow-up & Remarks -->
                    <div class="mb-3">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-calendar-check me-2"></i>Follow-up & Remarks</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Next Follow-up Date</label>
                                <input type="text" class="form-control form-control-sm datepicker @error('next_follow_up_date') is-invalid @enderror"
                                    name="next_follow_up_date" placeholder="DD/MM/YYYY" value="{{ old('next_follow_up_date', $lead->next_follow_up_date) }}">
                                @error('next_follow_up_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Remarks</label>
                                <textarea class="form-control form-control-sm @error('remarks') is-invalid @enderror"
                                    name="remarks" rows="2" placeholder="Enter remarks">{{ old('remarks', $lead->remarks) }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer py-2 bg-light">
                    <div class="d-flex justify-content-end gap-2">
                        <a class="btn btn-secondary btn-sm px-4" href="{{ route('leads.index') }}">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-success btn-sm px-4">
                            <i class="fas fa-save me-1"></i>Update Lead
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Activities Section -->
        <div class="card shadow mb-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-history me-2"></i>Activities</h6>
                @if (auth()->user()->hasPermissionTo('lead-activity-create'))
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addActivityModal">
                        <i class="fas fa-plus me-1"></i>Add Activity
                    </button>
                @endif
            </div>
            <div class="card-body">
                @if($lead->activities && $lead->activities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="12%">Date/Time</th>
                                    <th width="10%">Type</th>
                                    <th width="15%">Subject</th>
                                    <th width="25%">Description</th>
                                    <th width="15%">Outcome</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Created By</th>
                                    <th width="3%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lead->activities as $activity)
                                <tr>
                                    <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($activity->activity_type) }}</span>
                                    </td>
                                    <td>{{ $activity->subject ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($activity->description ?? 'N/A', 50) }}</td>
                                    <td>{{ $activity->outcome ?? 'N/A' }}</td>
                                    <td>
                                        @if($activity->completed_at)
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $activity->creator ? $activity->creator->first_name : 'N/A' }}</td>
                                    <td>
                                        @if (auth()->user()->hasPermissionTo('lead-activity-complete') && !$activity->completed_at)
                                            <form action="{{ route('leads.activities.complete', [$lead->id, $activity->id]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Mark as Complete">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if (auth()->user()->hasPermissionTo('lead-activity-delete'))
                                            <form action="{{ route('leads.activities.destroy', [$lead->id, $activity->id]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No activities recorded yet.</p>
                @endif
            </div>
        </div>

        <!-- Add Activity Modal -->
        @if (auth()->user()->hasPermissionTo('lead-activity-create'))
        <div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('leads.activities.store', $lead->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addActivityModalLabel">Add New Activity</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold"><span class="text-danger">*</span> Activity Type</label>
                                    <select class="form-select form-select-sm @error('activity_type') is-invalid @enderror" name="activity_type" required>
                                        <option value="">Select Type</option>
                                        <option value="call">Call</option>
                                        <option value="email">Email</option>
                                        <option value="meeting">Meeting</option>
                                        <option value="note">Note</option>
                                    </select>
                                    @error('activity_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Scheduled At</label>
                                    <input type="datetime-local" class="form-control form-control-sm @error('scheduled_at') is-invalid @enderror" name="scheduled_at">
                                    @error('scheduled_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Subject</label>
                                    <input type="text" class="form-control form-control-sm @error('subject') is-invalid @enderror" name="subject" placeholder="Enter subject">
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Description</label>
                                    <textarea class="form-control form-control-sm @error('description') is-invalid @enderror" name="description" rows="3" placeholder="Enter description"></textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Outcome</label>
                                    <textarea class="form-control form-control-sm @error('outcome') is-invalid @enderror" name="outcome" rows="2" placeholder="Enter outcome"></textarea>
                                    @error('outcome')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Next Action</label>
                                    <textarea class="form-control form-control-sm @error('next_action') is-invalid @enderror" name="next_action" rows="2" placeholder="Enter next action"></textarea>
                                    @error('next_action')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-save me-1"></i>Save Activity
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Documents Section -->
        <div class="card shadow mb-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-file me-2"></i>Documents</h6>
                @if (auth()->user()->hasPermissionTo('lead-document-upload'))
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                        <i class="fas fa-upload me-1"></i>Upload Document
                    </button>
                @endif
            </div>
            <div class="card-body">
                @if($lead->documents && $lead->documents->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="20%">Document Type</th>
                                    <th width="30%">File Name</th>
                                    <th width="10%">Size</th>
                                    <th width="15%">Uploaded By</th>
                                    <th width="15%">Uploaded At</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lead->documents as $document)
                                <tr>
                                    <td>{{ ucfirst($document->document_type) }}</td>
                                    <td>{{ $document->file_name }}</td>
                                    <td>{{ number_format($document->file_size / 1024, 2) }} KB</td>
                                    <td>{{ $document->uploader ? $document->uploader->first_name : 'N/A' }}</td>
                                    <td>{{ $document->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if (auth()->user()->hasPermissionTo('lead-document-download'))
                                            <a href="{{ route('leads.documents.download', [$lead->id, $document->id]) }}" class="btn btn-sm btn-primary" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif
                                        @if (auth()->user()->hasPermissionTo('lead-document-delete'))
                                            <form action="{{ route('leads.documents.destroy', [$lead->id, $document->id]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No documents uploaded yet.</p>
                @endif
            </div>
        </div>

        <!-- Add Document Modal -->
        @if (auth()->user()->hasPermissionTo('lead-document-upload'))
        <div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('leads.documents.store', $lead->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addDocumentModalLabel">Upload Document</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Document Type</label>
                                <select class="form-select form-select-sm @error('document_type') is-invalid @enderror" name="document_type" required>
                                    <option value="">Select Type</option>
                                    <option value="id_proof">ID Proof</option>
                                    <option value="address_proof">Address Proof</option>
                                    <option value="income_proof">Income Proof</option>
                                    <option value="photo">Photo</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('document_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> File</label>
                                <input type="file" class="form-control form-control-sm @error('file') is-invalid @enderror" name="file" required>
                                <small class="text-muted">Maximum file size: 10MB</small>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-upload me-1"></i>Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Convert to Customer Modal -->
        <div class="modal fade" id="convertModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('leads.convert-auto', $lead->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Convert Lead to Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Customer Type</label>
                                <select class="form-select form-select-sm" name="type">
                                    <option value="Retail">Retail</option>
                                    <option value="Corporate">Corporate</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">PAN Card Number</label>
                                <input type="text" class="form-control form-control-sm" name="pan_card_number" placeholder="Enter PAN">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Aadhar Card Number</label>
                                <input type="text" class="form-control form-control-sm" name="aadhar_card_number" placeholder="Enter Aadhar">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">GST Number</label>
                                <input type="text" class="form-control form-control-sm" name="gst_number" placeholder="Enter GST">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Conversion Notes</label>
                                <textarea class="form-control form-control-sm" name="conversion_notes" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-check me-1"></i>Convert to Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Assign Lead Modal -->
        <div class="modal fade" id="assignModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('leads.assign', $lead->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Assign Lead</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Assign To</label>
                                <select class="form-select form-select-sm" name="assigned_to" required>
                                    <option value="">Select User</option>
                                    @foreach(\App\Models\User::where('status', true)->get(['id', 'first_name', 'last_name']) as $user)
                                        <option value="{{ $user->id }}" {{ $lead->assigned_to == $user->id ? 'selected' : '' }}>
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="fas fa-check me-1"></i>Assign Lead
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Status Modal -->
        <div class="modal fade" id="statusModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('leads.update-status', $lead->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Change Lead Status</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Status</label>
                                <select class="form-select form-select-sm" name="status_id" required>
                                    <option value="">Select Status</option>
                                    @foreach(\App\Models\LeadStatus::active()->ordered()->get() as $status)
                                        <option value="{{ $status->id }}" {{ $lead->status_id == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea class="form-control form-control-sm" name="notes" rows="3" placeholder="Enter notes about status change"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fas fa-check me-1"></i>Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mark as Lost Modal -->
        <div class="modal fade" id="lostModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('leads.mark-as-lost', $lead->id) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Mark Lead as Lost</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This action will mark the lead as lost and it cannot be converted to a customer.
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Reason</label>
                                <textarea class="form-control form-control-sm" name="reason" rows="3" placeholder="Enter reason for marking as lost" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-times-circle me-1"></i>Mark as Lost
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
@endsection
