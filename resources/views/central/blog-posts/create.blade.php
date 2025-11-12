@extends('central.layout')

@section('title', 'Create Blog Post')
@section('page-title', 'Create Blog Post')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('central.blog-posts.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title *</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                                           id="title" name="title" value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="slug" class="form-label">Slug</label>
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                           id="slug" name="slug" value="{{ old('slug') }}" readonly>
                                    <small class="text-muted">Auto-generated from title</small>
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="excerpt" class="form-label">Excerpt *</label>
                                    <textarea class="form-control @error('excerpt') is-invalid @enderror"
                                              id="excerpt" name="excerpt" rows="3" maxlength="500" required>{{ old('excerpt') }}</textarea>
                                    <small class="text-muted">Maximum 500 characters</small>
                                    @error('excerpt')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label">Content *</label>
                                    <div class="editor-container">
                                        <div id="content"></div>
                                    </div>
                                    <textarea name="content" style="display:none;" required>{{ old('content') }}</textarea>
                                    <small class="text-muted d-block mt-2">Use the rich text editor above to format your blog content with headings, lists, images, and more</small>
                                    @error('content')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- SEO Section -->
                                <div class="card bg-light mb-3">
                                    <div class="card-header">
                                        <i class="fas fa-search me-2"></i>SEO Settings
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="meta_title" class="form-label">Meta Title</label>
                                            <input type="text" class="form-control @error('meta_title') is-invalid @enderror"
                                                   id="meta_title" name="meta_title" value="{{ old('meta_title') }}" maxlength="60">
                                            <small class="text-muted">Recommended: 50-60 characters</small>
                                            @error('meta_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="meta_description" class="form-label">Meta Description</label>
                                            <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                                      id="meta_description" name="meta_description" rows="2" maxlength="160">{{ old('meta_description') }}</textarea>
                                            <small class="text-muted">Recommended: 150-160 characters</small>
                                            @error('meta_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-0">
                                            <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                            <textarea class="form-control @error('meta_keywords') is-invalid @enderror"
                                                      id="meta_keywords" name="meta_keywords" rows="2">{{ old('meta_keywords') }}</textarea>
                                            <small class="text-muted">Comma-separated keywords</small>
                                            @error('meta_keywords')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <i class="fas fa-cog me-2"></i>Post Settings
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status *</label>
                                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category *</label>
                                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                                <option value="">Select Category</option>
                                                <option value="product-updates" {{ old('category') === 'product-updates' ? 'selected' : '' }}>Product Updates</option>
                                                <option value="insurance-tips" {{ old('category') === 'insurance-tips' ? 'selected' : '' }}>Insurance Tips</option>
                                                <option value="claims" {{ old('category') === 'claims' ? 'selected' : '' }}>Claims</option>
                                                <option value="insurance-types" {{ old('category') === 'insurance-types' ? 'selected' : '' }}>Insurance Types</option>
                                                <option value="addons" {{ old('category') === 'addons' ? 'selected' : '' }}>Add-ons</option>
                                            </select>
                                            @error('category')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="tags" class="form-label">Tags</label>
                                            <input type="text" class="form-control @error('tags') is-invalid @enderror"
                                                   id="tags" name="tags" value="{{ old('tags') }}" placeholder="tag1, tag2, tag3">
                                            <small class="text-muted">Comma-separated tags</small>
                                            @error('tags')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-0">
                                            <label for="reading_time_minutes" class="form-label">Reading Time (minutes)</label>
                                            <input type="number" class="form-control @error('reading_time_minutes') is-invalid @enderror"
                                                   id="reading_time_minutes" name="reading_time_minutes" value="{{ old('reading_time_minutes', 5) }}" min="1">
                                            @error('reading_time_minutes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header">
                                        <i class="fas fa-image me-2"></i>Featured Image
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-0">
                                            <input type="file" class="form-control @error('featured_image') is-invalid @enderror"
                                                   id="featured_image" name="featured_image" accept="image/*">
                                            <small class="text-muted d-block mt-2">Recommended: 1200x630px for social sharing</small>
                                            @error('featured_image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Create Post
                                    </button>
                                    <a href="{{ route('central.blog-posts.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    /* Quill Editor Custom Styling */
    .editor-container {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        overflow: hidden;
        background: white;
    }

    .ql-toolbar {
        border: none !important;
        border-bottom: 1px solid #dee2e6 !important;
        background: #f8f9fa;
        padding: 12px 8px;
    }

    .ql-container {
        border: none !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 16px;
        line-height: 1.6;
    }

    .ql-editor {
        min-height: 450px;
        padding: 20px;
    }

    .ql-editor.ql-blank::before {
        color: #6c757d;
        font-style: normal;
    }

    /* Better formatting for content */
    .ql-editor p {
        margin-bottom: 1em;
    }

    .ql-editor h1, .ql-editor h2, .ql-editor h3,
    .ql-editor h4, .ql-editor h5, .ql-editor h6 {
        font-weight: 600;
        margin-top: 1.5em;
        margin-bottom: 0.5em;
        line-height: 1.3;
    }

    .ql-editor h1 { font-size: 2em; }
    .ql-editor h2 { font-size: 1.5em; }
    .ql-editor h3 { font-size: 1.25em; }

    .ql-editor ul, .ql-editor ol {
        padding-left: 1.5em;
        margin-bottom: 1em;
    }

    .ql-editor blockquote {
        border-left: 4px solid #17a2b8;
        padding-left: 1em;
        margin: 1em 0;
        font-style: italic;
        color: #6c757d;
    }

    .ql-editor pre {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 1em;
        overflow-x: auto;
    }

    .ql-editor img {
        max-width: 100%;
        height: auto;
        border-radius: 0.25rem;
    }

    .ql-editor a {
        color: #17a2b8;
        text-decoration: underline;
    }

    /* Toolbar button styling */
    .ql-toolbar button:hover,
    .ql-toolbar button:focus,
    .ql-toolbar button.ql-active {
        color: #17a2b8!important;
    }

    .ql-toolbar .ql-stroke {
        stroke: #495057;
    }

    .ql-toolbar button:hover .ql-stroke,
    .ql-toolbar button:focus .ql-stroke,
    .ql-toolbar button.ql-active .ql-stroke {
        stroke: #17a2b8;
    }

    .ql-toolbar .ql-fill {
        fill: #495057;
    }

    .ql-toolbar button:hover .ql-fill,
    .ql-toolbar button:focus .ql-fill,
    .ql-toolbar button.ql-active .ql-fill {
        fill: #17a2b8;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    // Initialize Quill Editor with enhanced toolbar
    var quill = new Quill('#content', {
        theme: 'snow',
        placeholder: 'Start writing your amazing blog content here... Use the toolbar above to format text, add images, create lists, and more!',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'font': [] }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }, { 'align': [] }],
                ['link', 'image', 'video'],
                ['blockquote', 'code-block'],
                ['clean']
            ]
        }
    });

    // Sync Quill content with hidden textarea for form submission
    var form = document.querySelector('form');
    form.onsubmit = function() {
        var content = document.querySelector('textarea[name=content]');
        content.value = quill.root.innerHTML;

        // Basic validation - check if content is not empty
        if (quill.root.textContent.trim().length === 0) {
            alert('Please add some content to your blog post!');
            return false;
        }
        return true;
    };

    // Auto-generate slug from title
    document.getElementById('title').addEventListener('input', function() {
        const title = this.value;
        const slug = title.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        document.getElementById('slug').value = slug;
    });

    // Character counter for excerpt
    const excerpt = document.getElementById('excerpt');
    excerpt.addEventListener('input', function() {
        const maxLength = this.getAttribute('maxlength');
        const currentLength = this.value.length;
        const small = this.parentElement.querySelector('small');
        small.textContent = `${currentLength}/${maxLength} characters`;
    });
</script>
@endpush
@endsection
