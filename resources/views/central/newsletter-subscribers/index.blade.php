@extends('central.layout')

@section('title', 'Newsletter Subscribers')
@section('page-title', 'Newsletter Subscribers')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Subscribers</h6>
                            <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active</h6>
                            <h3 class="mb-0 text-success">{{ number_format($stats['active']) }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">This Month</h6>
                            <h3 class="mb-0 text-info">{{ number_format($stats['this_month']) }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-calendar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Today</h6>
                            <h3 class="mb-0 text-warning">{{ number_format($stats['today']) }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === null ? 'active' : '' }}"
                           href="{{ route('central.newsletter-subscribers.index') }}">
                            All ({{ $stats['total'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'active' ? 'active' : '' }}"
                           href="{{ route('central.newsletter-subscribers.index', ['status' => 'active']) }}">
                            Active ({{ $stats['active'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'unsubscribed' ? 'active' : '' }}"
                           href="{{ route('central.newsletter-subscribers.index', ['status' => 'unsubscribed']) }}">
                            Unsubscribed ({{ $stats['unsubscribed'] }})
                        </a>
                    </li>
                </ul>

                <div class="d-flex gap-2">
                    <!-- Export Button -->
                    <a href="{{ route('central.newsletter-subscribers.export', request()->query()) }}"
                       class="btn btn-sm btn-success">
                        <i class="fas fa-download me-1"></i> Export CSV
                    </a>

                    <!-- Search Form -->
                    <form method="GET" class="d-flex">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <input type="text"
                               name="search"
                               class="form-control form-control-sm me-2"
                               placeholder="Search email or name..."
                               value="{{ request('search') }}"
                               style="width: 250px;">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('central.newsletter-subscribers.index', ['status' => request('status')]) }}"
                               class="btn btn-sm btn-secondary ms-2">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscribers Table -->
    <div class="card">
        <div class="card-body">
            @if($subscribers->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-envelope-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No newsletter subscribers found.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">
                                    <a href="{{ route('central.newsletter-subscribers.index', array_merge(request()->query(), ['sort_by' => 'email', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}"
                                       class="text-decoration-none text-dark">
                                        Email
                                        @if(request('sort_by') === 'email')
                                            <i class="fas fa-sort-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="20%">
                                    <a href="{{ route('central.newsletter-subscribers.index', array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}"
                                       class="text-decoration-none text-dark">
                                        Name
                                        @if(request('sort_by') === 'name')
                                            <i class="fas fa-sort-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="10%">Status</th>
                                <th width="15%">
                                    <a href="{{ route('central.newsletter-subscribers.index', array_merge(request()->query(), ['sort_by' => 'created_at', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}"
                                       class="text-decoration-none text-dark">
                                        Subscribed At
                                        @if(request('sort_by') === 'created_at' || !request('sort_by'))
                                            <i class="fas fa-sort-{{ request('sort_order', 'desc') === 'asc' ? 'up' : 'down' }}"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="15%">IP Address</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscribers as $subscriber)
                                <tr>
                                    <td>{{ $subscribers->firstItem() + $loop->index }}</td>
                                    <td>
                                        <a href="{{ route('central.newsletter-subscribers.show', $subscriber) }}"
                                           class="text-decoration-none">
                                            {{ $subscriber->email }}
                                        </a>
                                    </td>
                                    <td>{{ $subscriber->name ?? '-' }}</td>
                                    <td>
                                        @if($subscriber->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Unsubscribed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $subscriber->subscribed_at ? $subscriber->subscribed_at->format('M d, Y') : $subscriber->created_at->format('M d, Y') }}<br>
                                            {{ $subscriber->subscribed_at ? $subscriber->subscribed_at->format('g:i A') : $subscriber->created_at->format('g:i A') }}
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted font-monospace">
                                            {{ $subscriber->ip_address ?? '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('central.newsletter-subscribers.show', $subscriber) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('central.newsletter-subscribers.destroy', $subscriber) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this subscriber?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $subscribers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
