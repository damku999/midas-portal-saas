@extends('layouts.app')

@section('title', 'Lead Details')

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

        <!-- Lead Details -->
        <div class="card shadow mb-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">Lead Details - {{ $lead->lead_number }}</h6>
                <div class="d-flex gap-2">
                    @if (auth()->user()->hasPermissionTo('lead-edit'))
                        <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                    @endif
                    <a href="{{ route('leads.index') }}" onclick="window.history.go(-1); return false;"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-chevron-left me-1"></i>Back
                    </a>
                </div>
            </div>
            <div class="card-body py-3">
                <!-- Section 1: Basic Information -->
                <div class="mb-4">
                    <h6 class="text-muted fw-bold mb-3"><i class="fas fa-user me-2"></i>Basic Information</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Lead Number</label>
                            <p class="mb-0">{{ $lead->lead_number }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Name</label>
                            <p class="mb-0">{{ $lead->name }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Email</label>
                            <p class="mb-0">{{ $lead->email ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Mobile Number</label>
                            <p class="mb-0">{{ $lead->mobile_number }}</p>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Alternate Mobile</label>
                            <p class="mb-0">{{ $lead->alternate_mobile ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Date of Birth</label>
                            <p class="mb-0">{{ $lead->date_of_birth ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Occupation</label>
                            <p class="mb-0">{{ $lead->occupation ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Product Interest</label>
                            <p class="mb-0">{{ $lead->product_interest ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Address Information -->
                <div class="mb-4">
                    <h6 class="text-muted fw-bold mb-3"><i class="fas fa-map-marker-alt me-2"></i>Address Information</h6>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-muted">Address</label>
                            <p class="mb-0">{{ $lead->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted">City</label>
                            <p class="mb-0">{{ $lead->city ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted">State</label>
                            <p class="mb-0">{{ $lead->state ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted">Pincode</label>
                            <p class="mb-0">{{ $lead->pincode ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Lead Details -->
                <div class="mb-4">
                    <h6 class="text-muted fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Lead Status & Details</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Source</label>
                            <p class="mb-0">{{ $lead->source->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Status</label>
                            <p class="mb-0">
                                <span class="badge" style="background-color: {{ $lead->status->color ?? '#6c757d' }}">
                                    {{ $lead->status->name ?? 'Unknown' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Priority</label>
                            <p class="mb-0">
                                @if($lead->priority == 'high')
                                    <span class="badge bg-danger">High</span>
                                @elseif($lead->priority == 'medium')
                                    <span class="badge bg-warning">Medium</span>
                                @elseif($lead->priority == 'low')
                                    <span class="badge bg-info">Low</span>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Next Follow-up</label>
                            <p class="mb-0">{{ $lead->next_follow_up_date ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Assignment -->
                <div class="mb-4">
                    <h6 class="text-muted fw-bold mb-3"><i class="fas fa-user-tag me-2"></i>Assignment Details</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted">Assigned To</label>
                            <p class="mb-0">{{ $lead->assignedUser ? $lead->assignedUser->first_name . ' ' . $lead->assignedUser->last_name : 'Unassigned' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted">Relationship Manager</label>
                            <p class="mb-0">{{ $lead->relationshipManager->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-muted">Reference User</label>
                            <p class="mb-0">{{ $lead->referenceUser->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Remarks & History -->
                <div class="mb-4">
                    <h6 class="text-muted fw-bold mb-3"><i class="fas fa-comment-alt me-2"></i>Remarks</h6>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <p class="mb-0">{{ $lead->remarks ?? 'No remarks available' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Section 6: Metadata -->
                <div class="mb-3">
                    <h6 class="text-muted fw-bold mb-3"><i class="fas fa-clock me-2"></i>Record Information</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Created By</label>
                            <p class="mb-0">{{ $lead->creator ? $lead->creator->first_name . ' ' . $lead->creator->last_name : 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Created At</label>
                            <p class="mb-0">{{ $lead->created_at ? $lead->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Updated By</label>
                            <p class="mb-0">{{ $lead->updater ? $lead->updater->first_name . ' ' . $lead->updater->last_name : 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted">Updated At</label>
                            <p class="mb-0">{{ $lead->updated_at ? $lead->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
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
                                            <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="deleteActivity({{ $lead->id }}, {{ $activity->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
                                            <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="deleteDocument({{ $lead->id }}, {{ $document->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
        @if (auth()->user()->hasPermissionTo('lead-convert') && !$lead->converted_at)
        <div class="modal fade" id="convertModal" tabindex="-1" aria-labelledby="convertModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('leads.convert-auto', $lead->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="convertModalLabel">Convert Lead to Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                <textarea class="form-control form-control-sm" name="conversion_notes" rows="2" placeholder="Enter notes"></textarea>
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
        @endif

        <!-- Assign Lead Modal -->
        @if (auth()->user()->hasPermissionTo('lead-assign'))
        <div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('leads.assign', $lead->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="assignModalLabel">Assign Lead</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
        @endif

        <!-- Change Status Modal -->
        @if (auth()->user()->hasPermissionTo('lead-status-change'))
        <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('leads.update-status', $lead->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="statusModalLabel">Change Lead Status</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Current Status</label>
                                <p class="mb-2">
                                    <span class="badge" style="background-color: {{ $lead->status->color ?? '#6c757d' }}">
                                        {{ $lead->status->name ?? 'Unknown' }}
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> New Status</label>
                                <select class="form-select form-select-sm" name="status_id" required>
                                    <option value="">Select Status</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ $lead->status_id == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea class="form-control form-control-sm" name="notes" rows="2" placeholder="Enter notes"></textarea>
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
        @endif

        <!-- Mark as Lost Modal -->
        @if (auth()->user()->hasPermissionTo('lead-mark-lost') && !$lead->converted_at)
        <div class="modal fade" id="lostModal" tabindex="-1" aria-labelledby="lostModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('leads.mark-as-lost', $lead->id) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="lostModalLabel">Mark Lead as Lost</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>This action will mark the lead as lost and cannot be undone.
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Reason for Loss</label>
                                <textarea class="form-control form-control-sm" name="reason" rows="3" placeholder="Enter reason why this lead is lost" required></textarea>
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
        @endif

    </div>

@endsection

@section('scripts')
<script>
function deleteActivity(leadId, activityId) {
    showConfirmationModal(
        'Delete Activity',
        'Are you sure you want to delete this activity?',
        'danger',
        function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/leads/${leadId}/activities/${activityId}`;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    );
}

function deleteDocument(leadId, documentId) {
    showConfirmationModal(
        'Delete Document',
        'Are you sure you want to delete this document?',
        'danger',
        function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/leads/${leadId}/documents/${documentId}`;
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    );
}
</script>
@endsection
