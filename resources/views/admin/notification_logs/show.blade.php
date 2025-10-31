@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Back Button -->
            <div class="mb-3">
                <a href="{{ route('admin.notification-logs.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Logs
                </a>
                @if($log->canRetry())
                    <form action="{{ route('admin.notification-logs.resend', $log) }}"
                          method="POST"
                          style="display:inline-block;">
                        @csrf
                        <button type="submit"
                                class="btn btn-warning"
                                onclick="return confirm('Are you sure you want to resend this notification?')">
                            <i class="fas fa-redo"></i> Resend Notification
                        </button>
                    </form>
                @endif
            </div>

            <!-- Notification Details Card -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-bell mr-2"></i>Notification Log #{{ $log->id }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Basic Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Channel</th>
                                    <td>
                                        <i class="{{ $log->channel_icon }}"></i>
                                        {{ ucfirst($log->channel) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge badge-{{ $log->status_color }}">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Recipient</th>
                                    <td>{{ $log->recipient }}</td>
                                </tr>
                                @if($log->subject)
                                <tr>
                                    <th>Subject</th>
                                    <td>{{ $log->subject }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Notification Type</th>
                                    <td>
                                        @if($log->notificationType)
                                            <span class="badge badge-info">
                                                {{ $log->notificationType->name }}
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ $log->notificationType->code }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Template Used</th>
                                    <td>
                                        @if($log->template)
                                            {{ $log->template->name }}
                                            <br>
                                            <small class="text-muted">ID: {{ $log->template_id }}</small>
                                        @else
                                            <span class="text-muted">No template (hardcoded message)</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Retry Count</th>
                                    <td>
                                        <span class="badge badge-{{ $log->retry_count > 0 ? 'warning' : 'success' }}">
                                            {{ $log->retry_count }}/3
                                        </span>
                                        @if($log->next_retry_at)
                                            <br>
                                            <small class="text-muted">
                                                Next retry: {{ $log->next_retry_at->format('Y-m-d H:i:s') }}
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Timestamps</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Created At</th>
                                    <td>
                                        @if($log->created_at)
                                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sent At</th>
                                    <td>
                                        @if($log->sent_at)
                                            {{ $log->sent_at->format('Y-m-d H:i:s') }}
                                            <br>
                                            <small class="text-muted">
                                                ({{ $log->sent_at->diffForHumans() }})
                                            </small>
                                        @else
                                            <span class="text-muted">Not sent yet</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Delivered At</th>
                                    <td>
                                        @if($log->delivered_at)
                                            {{ $log->delivered_at->format('Y-m-d H:i:s') }}
                                            <br>
                                            <small class="text-muted">
                                                ({{ $log->delivered_at->diffForHumans() }})
                                            </small>
                                        @else
                                            <span class="text-muted">Not delivered yet</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Read At</th>
                                    <td>
                                        @if($log->read_at)
                                            {{ $log->read_at->format('Y-m-d H:i:s') }}
                                            <br>
                                            <small class="text-muted">
                                                ({{ $log->read_at->diffForHumans() }})
                                            </small>
                                        @else
                                            <span class="text-muted">Not read yet</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Sent By</th>
                                    <td>
                                        @if($log->sender)
                                            {{ $log->sender->name }}
                                            <br>
                                            <small class="text-muted">{{ $log->sender->email }}</small>
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <h5 class="mb-3 mt-4">Related Entity</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Type</th>
                                    <td>{{ class_basename($log->notifiable_type) }}</td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <td>{{ $log->notifiable_id }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="mb-3">Message Content</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <pre class="mb-0" style="white-space: pre-wrap;">{{ $log->message_content }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Variables Used -->
                    @if($log->variables_used)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="mb-3">Variables Used</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Variable</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($log->variables_used as $key => $value)
                                        <tr>
                                            <td><code>{{ $key }}</code></td>
                                            <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Error Message -->
                    @if($log->error_message)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="mb-3 text-danger">Error Details</h5>
                            <div class="alert alert-danger">
                                <strong>Error:</strong> {{ $log->error_message }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- API Response -->
                    @if($log->api_response)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="mb-3">API Response</h5>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <pre class="mb-0">{{ json_encode($log->api_response, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Delivery Tracking Timeline -->
                    @if($log->deliveryTracking->isNotEmpty())
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="mb-3">Delivery Timeline</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Timestamp</th>
                                            <th>Provider Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($log->deliveryTracking->sortBy('tracked_at') as $tracking)
                                        <tr>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ ucfirst($tracking->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $tracking->tracked_at->format('Y-m-d H:i:s') }}</td>
                                            <td>
                                                @if($tracking->provider_status)
                                                    <small><pre>{{ json_encode($tracking->provider_status, JSON_PRETTY_PRINT) }}</pre></small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
