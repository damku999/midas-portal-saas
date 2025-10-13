@extends('layouts.app')

@section('title', 'App Settings')

@section('content')
    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- DataTales Example -->
        <div class="card shadow mt-3 mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">App Settings Management</h6>
                <div class="d-flex" style="gap: 8px;">
                    <button type="button" class="btn btn-warning btn-sm" onclick="clearCache()" title="Clear Settings Cache">
                        <i class="fas fa-sync-alt"></i> Clear Cache
                    </button>
                    @if (auth()->user()->hasPermissionTo('app-setting-create'))
                        <a href="{{ route('app-settings.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Setting
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('app-settings.index') }}" id="search_form">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="search">Search Settings</label>
                                <input type="text" class="form-control" id="search" name="search"
                                       placeholder="Key, value, description..."
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
                                <label for="type">Type</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="">All Types</option>
                                    @foreach($types as $key => $value)
                                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
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
                                <a href="{{ route('app-settings.index') }}" class="btn btn-secondary btn-sm">
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
                                <th width="20%">
                                    <a href="{{ route('app-settings.index', array_merge(request()->all(), ['sort_by' => 'key', 'sort_order' => (request('sort_by') == 'key' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}"
                                       class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                        <span>Key</span>
                                        @if(request('sort_by') == 'key')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="20%">Value</th>
                                <th width="10%">
                                    <a href="{{ route('app-settings.index', array_merge(request()->all(), ['sort_by' => 'category', 'sort_order' => (request('sort_by') == 'category' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}"
                                       class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                        <span>Category</span>
                                        @if(request('sort_by') == 'category')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="10%">
                                    <a href="{{ route('app-settings.index', array_merge(request()->all(), ['sort_by' => 'type', 'sort_order' => (request('sort_by') == 'type' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}"
                                       class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                        <span>Type</span>
                                        @if(request('sort_by') == 'type')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="20%">Description</th>
                                <th width="8%">
                                    <a href="{{ route('app-settings.index', array_merge(request()->all(), ['sort_by' => 'is_active', 'sort_order' => (request('sort_by') == 'is_active' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}"
                                       class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                        <span>Status</span>
                                        @if(request('sort_by') == 'is_active')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="12%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settings as $setting)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-primary" style="word-break: break-word;">{{ $setting->key }}</span>
                                            @if($setting->is_encrypted)
                                                <span class="badge bg-warning text-dark mt-1" style="width: fit-content;" title="Encrypted">
                                                    <i class="fas fa-lock"></i> Encrypted
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($setting->is_encrypted)
                                            <span id="encrypted-value-{{ $setting->id }}" class="text-muted">******</span>
                                            <button type="button" class="btn btn-xs btn-outline-warning ms-1" id="decrypt-btn-{{ $setting->id }}" onclick="viewDecryptedValue({{ $setting->id }})" title="View Decrypted Value">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary ms-1 d-none" id="hide-btn-{{ $setting->id }}" onclick="hideDecryptedValue({{ $setting->id }})" title="Hide Value">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        @else
                                            @switch($setting->type)
                                                @case('image')
                                                    @if($setting->value && Storage::disk('public')->exists($setting->value))
                                                        <img src="{{ Storage::url($setting->value) }}"
                                                             alt="{{ $setting->key }}"
                                                             class="img-thumbnail"
                                                             style="max-height: 50px; max-width: 100px; object-fit: contain;">
                                                    @else
                                                        <span class="text-muted small">No image</span>
                                                    @endif
                                                    @break

                                                @case('color')
                                                    <div class="d-flex align-items-center">
                                                        <span class="me-2"
                                                              style="background: {{ $setting->value }}; width: 30px; height: 30px; display: inline-block; border: 1px solid #dee2e6; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                        </span>
                                                        <code class="small">{{ $setting->value }}</code>
                                                    </div>
                                                    @break

                                                @case('boolean')
                                                    <span class="badge {{ $setting->value === 'true' ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $setting->value === 'true' ? 'Yes' : 'No' }}
                                                    </span>
                                                    @break

                                                @case('url')
                                                    <a href="{{ $setting->value }}" target="_blank" class="small text-decoration-none">
                                                        {{ Str::limit($setting->value, 30) }}
                                                        <i class="fas fa-external-link-alt ms-1"></i>
                                                    </a>
                                                    @break

                                                @case('file')
                                                    @if($setting->value && Storage::disk('public')->exists($setting->value))
                                                        <a href="{{ Storage::url($setting->value) }}" target="_blank" class="small text-decoration-none">
                                                            <i class="fas fa-file"></i> {{ basename($setting->value) }}
                                                        </a>
                                                    @else
                                                        <span class="text-muted small">No file</span>
                                                    @endif
                                                    @break

                                                @case('json')
                                                    <code class="small d-block text-truncate" style="max-width: 250px;">
                                                        {{ Str::limit($setting->value, 50) }}
                                                    </code>
                                                    @break

                                                @case('text')
                                                    <span class="small" style="display: block; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        {{ Str::limit($setting->value, 50) }}
                                                    </span>
                                                    @break

                                                @default
                                                    <span class="small">{{ Str::limit($setting->value, 50) }}</span>
                                            @endswitch
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-white">
                                            {{ ucfirst($setting->category) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($setting->type) }}
                                        </span>
                                    </td>
                                    <td style="word-break: break-word;">{{ $setting->description }}</td>
                                    <td>
                                        @if ($setting->is_active == 0)
                                            <span class="badge bg-danger text-white">Inactive</span>
                                        @else
                                            <span class="badge bg-success text-white">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap" style="gap: 6px; justify-content: flex-start; align-items: center;">
                                            @if (auth()->user()->hasPermissionTo('app-setting-edit'))
                                                <a href="{{ route('app-settings.show', $setting->id) }}"
                                                    class="btn btn-info btn-sm" title="View Setting">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('app-settings.edit', $setting->id) }}"
                                                    class="btn btn-primary btn-sm" title="Edit Setting">
                                                    <i class="fa fa-pen"></i>
                                                </a>
                                            @endif

                                            @if (auth()->user()->hasPermissionTo('app-setting-delete'))
                                                @if ($setting->is_active == 0)
                                                    <a href="{{ route('app-settings.status', ['id' => $setting->id, 'status' => 1]) }}"
                                                        class="btn btn-success btn-sm" title="Activate Setting">
                                                        <i class="fa fa-check"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('app-settings.status', ['id' => $setting->id, 'status' => 0]) }}"
                                                        class="btn btn-warning btn-sm" title="Deactivate Setting">
                                                        <i class="fa fa-ban"></i>
                                                    </a>
                                                @endif
                                            @endif

                                            @if (auth()->user()->hasPermissionTo('app-setting-delete'))
                                                @php
                                                    $userEmail = auth()->user()->email ?? '';
                                                    $canDelete = str_ends_with($userEmail, '@webmonks.in') || str_ends_with($userEmail, '@midastech.in');
                                                @endphp
                                                @if($canDelete)
                                                    <form action="{{ route('app-settings.destroy', $setting->id) }}"
                                                          method="POST"
                                                          style="display: inline;"
                                                          data-confirm-submit="true"
                                                          data-title="Confirm Deletion"
                                                          data-message="Are you sure you want to delete <strong>{{ $setting->key }}</strong>? This action cannot be undone."
                                                          data-confirm-text="Yes, Delete"
                                                          data-confirm-class="btn-danger">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete Setting">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No settings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <x-pagination-with-info :paginator="$settings" />
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
<script>
    /**
     * Clear app settings cache
     */
    function clearCache() {
        if (!confirm('Are you sure you want to clear the app settings cache?')) {
            return;
        }

        $.ajax({
            url: '{{ route("app-settings.clear-cache") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Cache cleared successfully!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message || 'Failed to clear cache');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error clearing cache';
                toastr.error(message);
            }
        });
    }

    /**
     * View decrypted value for encrypted settings
     */
    function viewDecryptedValue(settingId) {
        const valueEl = $('#encrypted-value-' + settingId);
        const decryptBtn = $('#decrypt-btn-' + settingId);
        const hideBtn = $('#hide-btn-' + settingId);

        decryptBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '{{ url("app-settings") }}/' + settingId + '/decrypt',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    valueEl.text(response.value).removeClass('text-muted').addClass('text-success fw-bold');
                    decryptBtn.addClass('d-none');
                    hideBtn.removeClass('d-none');
                } else {
                    toastr.error(response.message || 'Failed to decrypt value');
                    decryptBtn.prop('disabled', false).html('<i class="fas fa-eye"></i>');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error decrypting value';
                toastr.error(message);
                decryptBtn.prop('disabled', false).html('<i class="fas fa-eye"></i>');
            }
        });
    }

    /**
     * Hide decrypted value
     */
    function hideDecryptedValue(settingId) {
        const valueEl = $('#encrypted-value-' + settingId);
        const decryptBtn = $('#decrypt-btn-' + settingId);
        const hideBtn = $('#hide-btn-' + settingId);

        valueEl.text('******').addClass('text-muted').removeClass('text-success fw-bold');
        hideBtn.addClass('d-none');
        decryptBtn.removeClass('d-none').prop('disabled', false);
    }
</script>
@endsection
