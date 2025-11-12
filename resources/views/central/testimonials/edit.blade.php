@extends('central.layout')

@section('title', 'Edit Testimonial')
@section('page-title', 'Edit Testimonial')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('central.testimonials.update', $testimonial) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $testimonial->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="company" class="form-label">Company *</label>
                            <input type="text" class="form-control @error('company') is-invalid @enderror"
                                   id="company" name="company" value="{{ old('company', $testimonial->company) }}" required>
                            @error('company')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role/Position *</label>
                            <input type="text" class="form-control @error('role') is-invalid @enderror"
                                   id="role" name="role" value="{{ old('role', $testimonial->role) }}" required>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="testimonial" class="form-label">Testimonial Text *</label>
                            <textarea class="form-control @error('testimonial') is-invalid @enderror"
                                      id="testimonial" name="testimonial" rows="4" required>{{ old('testimonial', $testimonial->testimonial) }}</textarea>
                            @error('testimonial')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="rating" class="form-label">Rating *</label>
                                <select class="form-select @error('rating') is-invalid @enderror" id="rating" name="rating" required>
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" {{ old('rating', $testimonial->rating) == $i ? 'selected' : '' }}>
                                            {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                                        </option>
                                    @endfor
                                </select>
                                @error('rating')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror"
                                       id="display_order" name="display_order" value="{{ old('display_order', $testimonial->display_order) }}" min="0">
                                <small class="text-muted">Lower numbers appear first</small>
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="photo" class="form-label">Photo (Optional)</label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror"
                                   id="photo" name="photo" accept="image/*">
                            @if($testimonial->photo)
                                <div class="mt-2">
                                    <img src="{{ Storage::url($testimonial->photo) }}" alt="{{ $testimonial->name }}"
                                         class="rounded" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                    <p class="text-muted small mt-1">Current photo (upload new to replace)</p>
                                </div>
                            @endif
                            <small class="text-muted">Recommended: Square image, minimum 200x200px</small>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $testimonial->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $testimonial->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Testimonial
                            </button>
                            <a href="{{ route('central.testimonials.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
