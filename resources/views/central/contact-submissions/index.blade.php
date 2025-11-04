@extends('central.layout')

@section('title', 'Contact Submissions')
@section('page-title', 'Contact Submissions')

@section('content')
<div class="container-fluid">
    <!-- Status Filter Tabs -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === null ? 'active' : '' }}"
                           href="{{ route('central.contact-submissions.index') }}">
                            All ({{ $counts['all'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'new' ? 'active' : '' }}"
                           href="{{ route('central.contact-submissions.index', ['status' => 'new']) }}">
                            New ({{ $counts['new'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'read' ? 'active' : '' }}"
                           href="{{ route('central.contact-submissions.index', ['status' => 'read']) }}">
                            Read ({{ $counts['read'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'replied' ? 'active' : '' }}"
                           href="{{ route('central.contact-submissions.index', ['status' => 'replied']) }}">
                            Replied ({{ $counts['replied'] }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') === 'archived' ? 'active' : '' }}"
                           href="{{ route('central.contact-submissions.index', ['status' => 'archived']) }}">
                            Archived ({{ $counts['archived'] }})
                        </a>
                    </li>
                </ul>

                <!-- Search Form -->
                <form method="GET" class="d-flex">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <input type="text"
                           name="search"
                           class="form-control form-control-sm me-2"
                           placeholder="Search..."
                           value="{{ request('search') }}"
                           style="width: 250px;">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('central.contact-submissions.index', ['status' => request('status')]) }}"
                           class="btn btn-sm btn-secondary ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Submissions Table -->
    <div class="card">
        <div class="card-body">
            @if($submissions->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No contact submissions found.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">
                                    <i class="fas fa-circle"></i>
                                </th>
                                <th width="15%">Name</th>
                                <th width="15%">Email</th>
                                <th width="15%">Company</th>
                                <th width="30%">Message</th>
                                <th width="10%">Status</th>
                                <th width="10%">Date</th>
                                <th width="5%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($submissions as $submission)
                                <tr class="{{ $submission->status === 'new' ? 'fw-bold' : '' }}">
                                    <td>
                                        @if($submission->status === 'new')
                                            <i class="fas fa-circle text-primary" style="font-size: 8px;"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('central.contact-submissions.show', $submission) }}"
                                           class="text-decoration-none">
                                            {{ $submission->name }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $submission->email }}" class="text-decoration-none">
                                            {{ $submission->email }}
                                        </a>
                                    </td>
                                    <td>{{ $submission->company ?? '-' }}</td>
                                    <td>
                                        <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $submission->message }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($submission->status === 'new')
                                            <span class="badge bg-primary">New</span>
                                        @elseif($submission->status === 'read')
                                            <span class="badge bg-info">Read</span>
                                        @elseif($submission->status === 'replied')
                                            <span class="badge bg-success">Replied</span>
                                        @else
                                            <span class="badge bg-secondary">Archived</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $submission->created_at->format('M d, Y') }}<br>
                                            {{ $submission->created_at->format('g:i A') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('central.contact-submissions.show', $submission) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('central.contact-submissions.destroy', $submission) }}"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        data-confirm="Are you sure you want to delete this submission?"
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
                    {{ $submissions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
