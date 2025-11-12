@extends('central.layout')

@section('title', 'Newsletter Subscriber Details')
@section('page-title', 'Newsletter Subscriber Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- Subscriber Details Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Subscriber Information</h5>
                    <div>
                        @if($subscriber->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Unsubscribed</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Email:</div>
                        <div class="col-md-9">
                            <a href="mailto:{{ $subscriber->email }}">{{ $subscriber->email }}</a>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Name:</div>
                        <div class="col-md-9">{{ $subscriber->name ?? '-' }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Status:</div>
                        <div class="col-md-9">
                            <span class="text-capitalize">{{ $subscriber->status }}</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Subscribed At:</div>
                        <div class="col-md-9">
                            {{ $subscriber->subscribed_at ? $subscriber->subscribed_at->format('F d, Y \a\t g:i A') : $subscriber->created_at->format('F d, Y \a\t g:i A') }}
                        </div>
                    </div>

                    @if($subscriber->unsubscribed_at)
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">Unsubscribed At:</div>
                        <div class="col-md-9 text-danger">
                            {{ $subscriber->unsubscribed_at->format('F d, Y \a\t g:i A') }}
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">IP Address:</div>
                        <div class="col-md-9">
                            <span class="font-monospace">{{ $subscriber->ip_address ?? '-' }}</span>
                        </div>
                    </div>

                    @if($subscriber->user_agent)
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">User Agent:</div>
                        <div class="col-md-9">
                            <small class="text-muted font-monospace">{{ $subscriber->user_agent }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2">
                        @if($subscriber->status === 'active')
                            <!-- Unsubscribe Button -->
                            <form method="POST"
                                  action="{{ route('central.newsletter-subscribers.update-status', $subscriber) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to unsubscribe this user?');">
                                @csrf
                                <input type="hidden" name="status" value="unsubscribed">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-ban me-1"></i> Unsubscribe
                                </button>
                            </form>
                        @else
                            <!-- Resubscribe Button -->
                            <form method="POST"
                                  action="{{ route('central.newsletter-subscribers.update-status', $subscriber) }}"
                                  class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-circle me-1"></i> Reactivate Subscription
                                </button>
                            </form>
                        @endif

                        <!-- Delete Button -->
                        <form method="POST"
                              action="{{ route('central.newsletter-subscribers.destroy', $subscriber) }}"
                              class="d-inline"
                              onsubmit="return confirm('Are you sure you want to permanently delete this subscriber? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i> Delete Permanently
                            </button>
                        </form>

                        <!-- Back Button -->
                        <a href="{{ route('central.newsletter-subscribers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Member Since</span>
                        <span class="fw-bold">{{ $subscriber->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Current Status</span>
                        <span class="fw-bold text-{{ $subscriber->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($subscriber->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Activity Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Subscribed</h6>
                                <p class="text-muted small mb-0">
                                    {{ $subscriber->subscribed_at ? $subscriber->subscribed_at->format('M d, Y \a\t g:i A') : $subscriber->created_at->format('M d, Y \a\t g:i A') }}
                                </p>
                            </div>
                        </div>

                        @if($subscriber->unsubscribed_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Unsubscribed</h6>
                                <p class="text-muted small mb-0">
                                    {{ $subscriber->unsubscribed_at->format('M d, Y \a\t g:i A') }}
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -23px;
    top: 8px;
    bottom: -20px;
    width: 2px;
    background: #e9ecef;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -28px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px currentColor;
}

.timeline-content h6 {
    font-size: 14px;
    margin-bottom: 4px;
}
</style>
@endsection
