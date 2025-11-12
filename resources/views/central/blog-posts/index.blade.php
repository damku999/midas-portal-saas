@extends('central.layout')

@section('title', 'Blog Posts')
@section('page-title', 'Blog Posts')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manage Blog Posts</h5>
                <a href="{{ route('central.blog-posts.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Post
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('central.blog-posts.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="product-updates" {{ request('category') === 'product-updates' ? 'selected' : '' }}>Product Updates</option>
                        <option value="insurance-tips" {{ request('category') === 'insurance-tips' ? 'selected' : '' }}>Insurance Tips</option>
                        <option value="claims" {{ request('category') === 'claims' ? 'selected' : '' }}>Claims</option>
                        <option value="insurance-types" {{ request('category') === 'insurance-types' ? 'selected' : '' }}>Insurance Types</option>
                        <option value="addons" {{ request('category') === 'addons' ? 'selected' : '' }}>Add-ons</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search by title..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('central.blog-posts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($posts->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-blog fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No blog posts found.</p>
                    <a href="{{ route('central.blog-posts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Your First Post
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">Image</th>
                                <th width="30%">Title</th>
                                <th width="15%">Category</th>
                                <th width="10%">Status</th>
                                <th width="10%">Views</th>
                                <th width="15%">Published</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posts as $post)
                                <tr>
                                    <td>
                                        @if($post->featured_image)
                                            <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}"
                                                 class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ Str::limit($post->title, 50) }}</div>
                                        <small class="text-muted">{{ Str::limit($post->excerpt, 60) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucwords(str_replace('-', ' ', $post->category)) }}</span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('central.blog-posts.toggle-status', $post) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="badge {{ $post->status === 'published' ? 'bg-success' : 'bg-warning' }} border-0" style="cursor: pointer;">
                                                {{ ucfirst($post->status) }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <i class="fas fa-eye text-muted"></i> {{ number_format($post->views_count) }}
                                    </td>
                                    <td>
                                        @if($post->published_at)
                                            <small class="text-muted">{{ $post->published_at->format('M d, Y') }}</small>
                                        @else
                                            <small class="text-muted">Not published</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('central.blog-posts.edit', $post) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('central.blog-posts.destroy', $post) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
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
                <div class="mt-3">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
