@extends('central.layout')

@section('title', 'Testimonials')
@section('page-title', 'Testimonials')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manage Testimonials</h5>
                <a href="{{ route('central.testimonials.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Testimonial
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($testimonials->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-quote-right fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No testimonials found.</p>
                    <a href="{{ route('central.testimonials.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Your First Testimonial
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">Order</th>
                                <th width="20%">Name</th>
                                <th width="20%">Company</th>
                                <th width="10%">Rating</th>
                                <th width="10%">Status</th>
                                <th width="15%">Date</th>
                                <th width="20%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($testimonials as $testimonial)
                                <tr>
                                    <td class="fw-bold">{{ $testimonial->display_order }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $testimonial->name }}</div>
                                        <small class="text-muted">{{ $testimonial->role }}</small>
                                    </td>
                                    <td>{{ $testimonial->company }}</td>
                                    <td>
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $testimonial->rating ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('central.testimonials.toggle-status', $testimonial) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="badge {{ $testimonial->status === 'active' ? 'bg-success' : 'bg-secondary' }} border-0" style="cursor: pointer;">
                                                {{ ucfirst($testimonial->status) }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $testimonial->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('central.testimonials.edit', $testimonial) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('central.testimonials.destroy', $testimonial) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this testimonial?');">
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
                    {{ $testimonials->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
