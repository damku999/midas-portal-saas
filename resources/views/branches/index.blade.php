@extends('layouts.app')

@section('title', 'Branches List')

@section('content')
    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- DataTales Example -->
        <div class="card shadow mt-3 mb-4">
            <x-list-header 
                    title="Branches Management"
                    subtitle="Manage all branch records"
                    addRoute="branches.create"
                    exportRoute="branches.export"
            />
            <div class="card-body">
                <form method="GET" action="{{ route('branches.index') }}" id="search_form">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search">Search Branches</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Branch name, location..." 
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="{{ route('branches.index') }}" class="btn btn-secondary btn-sm">
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
                                <th width="25%">Name</th>
                                <th width="25%">Email</th>
                                <th width="15%">Mobile</th>
                                <th width="15%">Status</th>
                                <th width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branches as $branch)
                                <tr>
                                    <td>{{ $branch->name }}</td>
                                    <td>{{ $branch->email ?? 'N/A' }}</td>
                                    <td>{{ $branch->mobile_number ?? 'N/A' }}</td>
                                    <td>
                                        @if ($branch->status == 1)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('branches.edit', $branch->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if ($branch->status == 1)
                                                <a href="#"
                                                   class="btn btn-sm btn-outline-warning"
                                                   title="Deactivate"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#confirmationModal"
                                                   data-title="Confirm Deactivation"
                                                   data-message="Are you sure you want to deactivate <strong>{{ $branch->name }}</strong>?"
                                                   data-confirm-text="Yes, Deactivate"
                                                   data-confirm-class="btn-warning"
                                                   data-action-url="{{ route('branches.status', [$branch->id, 0]) }}"
                                                   data-method="GET">
                                                    <i class="fas fa-ban"></i>
                                                </a>
                                            @else
                                                <a href="#"
                                                   class="btn btn-sm btn-outline-success"
                                                   title="Activate"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#confirmationModal"
                                                   data-title="Confirm Activation"
                                                   data-message="Are you sure you want to activate <strong>{{ $branch->name }}</strong>?"
                                                   data-confirm-text="Yes, Activate"
                                                   data-confirm-class="btn-success"
                                                   data-action-url="{{ route('branches.status', [$branch->id, 1]) }}"
                                                   data-method="GET">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No branches found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $branches->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection