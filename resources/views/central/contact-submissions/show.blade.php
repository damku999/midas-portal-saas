@extends('central.layout')

@section('title', 'Contact Submission - ' . $contactSubmission->name)
@section('page-title', 'Contact Submission Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <!-- Submission Details -->
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-envelope me-2"></i>Message Details
                    </h5>
                    <a href="{{ route('central.contact-submissions.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to List
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Name</label>
                            <p class="fw-bold">{{ $contactSubmission->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Email</label>
                            <p class="fw-bold">
                                <a href="mailto:{{ $contactSubmission->email }}">{{ $contactSubmission->email }}</a>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Phone</label>
                            <p class="fw-bold">
                                @if($contactSubmission->phone)
                                    <a href="tel:{{ $contactSubmission->phone }}">{{ $contactSubmission->phone }}</a>
                                @else
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Company</label>
                            <p class="fw-bold">{{ $contactSubmission->company ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Message</label>
                        <div class="p-3 bg-light rounded">
                            {{ $contactSubmission->message }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted small">Submitted On</label>
                            <p class="fw-bold">
                                {{ $contactSubmission->created_at->format('F d, Y \a\t g:i A') }}
                                <small class="text-muted">({{ $contactSubmission->created_at->diffForHumans() }})</small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Current Status</label>
                            <p>
                                @if($contactSubmission->status === 'new')
                                    <span class="badge bg-primary">New</span>
                                @elseif($contactSubmission->status === 'read')
                                    <span class="badge bg-info">Read</span>
                                @elseif($contactSubmission->status === 'replied')
                                    <span class="badge bg-success">Replied</span>
                                @else
                                    <span class="badge bg-secondary">Archived</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Details -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Technical Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">IP Address</label>
                            <p class="fw-bold">{{ $contactSubmission->ip_address ?? 'Unknown' }}</p>
                        </div>
                        <div class="col-md-12">
                            <label class="text-muted small">User Agent</label>
                            <p class="fw-bold text-break">{{ $contactSubmission->user_agent ?? 'Unknown' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="col-lg-4">
            <!-- Status Update -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>Update Status
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('central.contact-submissions.update-status', $contactSubmission) }}">
                        @csrf
                        <div class="mb-3">
                            <select name="status" class="form-select">
                                <option value="new" {{ $contactSubmission->status === 'new' ? 'selected' : '' }}>New</option>
                                <option value="read" {{ $contactSubmission->status === 'read' ? 'selected' : '' }}>Read</option>
                                <option value="replied" {{ $contactSubmission->status === 'replied' ? 'selected' : '' }}>Replied</option>
                                <option value="archived" {{ $contactSubmission->status === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i>Update Status
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <a href="mailto:{{ $contactSubmission->email }}"
                       class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-reply me-1"></i>Reply via Email
                    </a>

                    @if($contactSubmission->phone)
                        <a href="tel:{{ $contactSubmission->phone }}"
                           class="btn btn-outline-success w-100 mb-2">
                            <i class="fas fa-phone me-1"></i>Call
                        </a>
                    @endif

                    <form method="POST"
                          action="{{ route('central.contact-submissions.destroy', $contactSubmission) }}"
                          class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-outline-danger w-100"
                                data-confirm="Are you sure you want to delete this submission? This action cannot be undone."
                                data-confirm-title="Delete Submission"
                                data-confirm-button="Delete"
                                data-confirm-class="btn-danger">
                            <i class="fas fa-trash me-1"></i>Delete Submission
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
