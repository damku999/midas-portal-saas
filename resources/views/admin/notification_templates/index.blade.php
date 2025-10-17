@extends('layouts.app')

@section('title', 'Notification Templates')

@section('content')
    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- DataTales Example -->
        <div class="card shadow mt-3 mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">Notification Templates Management</h6>
                @can('notification-template-create')
                    <a href="{{ route('notification-templates.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Template
                    </a>
                @endcan
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
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="25%">
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
                                <th width="15%">
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
                                <th width="20%">Subject</th>
                                <th width="10%">
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
                                <th width="15%">
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
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $template)
                                <tr>
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
                                    <td style="word-break: break-word;">{{ $template->subject ?? '-' }}</td>
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
                                        <div class="d-flex flex-wrap" style="gap: 6px; justify-content: flex-start; align-items: center;">
                                            @can('notification-template-edit')
                                                <a href="{{ route('notification-templates.edit', $template) }}"
                                                    class="btn btn-primary btn-sm" title="Edit Template">
                                                    <i class="fa fa-pen"></i>
                                                </a>
                                            @endcan

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
                                    <td colspan="6" class="text-center">
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
@endsection
