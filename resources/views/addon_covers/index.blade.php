@extends('layouts.app')

@section('title', 'Add-on Covers List')

@section('content')
    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- DataTales Example -->
        <div class="card shadow mt-3 mb-4">
            <x-list-header 
                    title="Add-on Covers Management"
                    subtitle="Manage insurance add-on covers"
                    addRoute="addon-covers.create"
                    addPermission="addon-cover-create"
                    exportRoute="addon-covers.export"
            />
            <div class="card-body">
                <form method="GET" action="{{ route('addon-covers.index') }}" id="search_form">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search">Search Add-on Covers</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Cover name, description..." 
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
                                <a href="{{ route('addon-covers.index') }}" class="btn btn-secondary btn-sm">
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
                                <th width="18%">Name</th>
                                <th width="27%">Description</th>
                                <th width="10%">Order</th>
                                <th width="15%">Status</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($addon_covers as $addon_cover)
                                <tr>
                                    <td>{{ $addon_cover->name }}</td>
                                    <td>{{ Str::limit($addon_cover->description, 50) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary text-white">{{ $addon_cover->order_no }}</span>
                                    </td>
                                    <td>
                                        @if ($addon_cover->status == 0)
                                            <span class="badge bg-danger text-white">Inactive</span>
                                        @elseif ($addon_cover->status == 1)
                                            <span class="badge bg-success text-white">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap" style="gap: 6px; justify-content: flex-start; align-items: center;">
                                            @if (auth()->user()->hasPermissionTo('addon-cover-edit'))
                                                <a href="{{ route('addon-covers.edit', ['addon_cover' => $addon_cover->id]) }}"
                                                    class="btn btn-primary btn-sm" title="Edit Add-on Cover">
                                                    <i class="fa fa-pen"></i>
                                                </a>
                                            @endif

                                            @if (auth()->user()->hasPermissionTo('addon-cover-delete'))
                                                @if ($addon_cover->status == 0)
                                                    <a href="{{ route('addon-covers.status', ['addon_cover_id' => $addon_cover->id, 'status' => 1]) }}"
                                                        class="btn btn-success btn-sm" title="Enable Add-on Cover">
                                                        <i class="fa fa-check"></i>
                                                    </a>
                                                @elseif ($addon_cover->status == 1)
                                                    <a href="{{ route('addon-covers.status', ['addon_cover_id' => $addon_cover->id, 'status' => 0]) }}"
                                                        class="btn btn-warning btn-sm" title="Disable Add-on Cover">
                                                        <i class="fa fa-ban"></i>
                                                    </a>
                                                @endif
                                            @endif

                                            @if (auth()->user()->hasPermissionTo('addon-cover-delete'))
                                                <form action="{{ route('addon-covers.delete', $addon_cover->id) }}"
                                                      method="POST"
                                                      style="display: inline;"
                                                      data-confirm-submit="true"
                                                      data-title="Confirm Deletion"
                                                      data-message="Are you sure you want to delete <strong>{{ $addon_cover->name }}</strong>? This action cannot be undone."
                                                      data-confirm-text="Yes, Delete"
                                                      data-confirm-class="btn-danger">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete Add-on Cover">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No Record Found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <x-pagination-with-info :paginator="$addon_covers" />
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script></script>
@endsection