@extends('layouts.app')

@section('title', 'Add App Setting')

@section('content')

    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- App Setting Form -->
        <div class="card shadow mb-3 mt-2">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">Add New App Setting</h6>
                <a href="{{ route('app-settings.index') }}" onclick="window.history.go(-1); return false;"
                    class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                    <i class="fas fa-chevron-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <form method="POST" action="{{ route('app-settings.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="card-body py-3">
                    <!-- Section: Basic Information -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-cog me-2"></i>Basic Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Key</label>
                                <input type="text" class="form-control form-control-sm @error('key') is-invalid @enderror"
                                    name="key" placeholder="e.g., whatsapp.api_token" value="{{ old('key') }}">
                                <small class="text-muted">Unique identifier for this setting (use dot notation for grouping)</small>
                                @error('key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Category</label>
                                <select class="form-control form-control-sm @error('category') is-invalid @enderror"
                                    name="category">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $key => $value)
                                        <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section: Value Configuration -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-database me-2"></i>Value Configuration</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Type</label>
                                <select class="form-control form-control-sm @error('type') is-invalid @enderror"
                                    name="type" id="type">
                                    <option value="">Select Type</option>
                                    @foreach($types as $key => $value)
                                        <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Data type for the value</small>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Value</label>

                                <!-- String Input -->
                                <input type="text" class="form-control form-control-sm @error('value') is-invalid @enderror value-input"
                                    name="value" id="value-string" placeholder="Enter text value"
                                    value="{{ old('value') }}" data-type="string">

                                <!-- Text/Textarea Input -->
                                <textarea class="form-control form-control-sm @error('value') is-invalid @enderror value-input"
                                    name="value" rows="3" id="value-text" placeholder="Enter text content"
                                    data-type="text" style="display: none;">{{ old('value') }}</textarea>

                                <!-- JSON Input -->
                                <textarea class="form-control form-control-sm @error('value') is-invalid @enderror value-input font-monospace"
                                    name="value" rows="4" id="value-json" placeholder='{"key": "value"}'
                                    data-type="json" style="display: none;">{{ old('value') }}</textarea>

                                <!-- Number Input -->
                                <input type="number" class="form-control form-control-sm @error('value') is-invalid @enderror value-input"
                                    name="value" id="value-numeric" placeholder="Enter number"
                                    value="{{ old('value') }}" data-type="numeric" style="display: none;">

                                <!-- Boolean Input -->
                                <div class="value-input" id="value-boolean" data-type="boolean" style="display: none;">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="value" id="boolean-checkbox"
                                            value="1" {{ old('value') == 'true' || old('value') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="boolean-checkbox">
                                            <span class="badge bg-success" id="boolean-label-yes" {{ old('value') == 'true' || old('value') == '1' ? '' : 'style=display:none;' }}>Yes / Enabled</span>
                                            <span class="badge bg-secondary" id="boolean-label-no" {{ old('value') == 'true' || old('value') == '1' ? 'style=display:none;' : '' }}>No / Disabled</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Color Input -->
                                <div class="value-input" id="value-color" data-type="color" style="display: none;">
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-sm form-control-color"
                                            name="value" id="color-picker" value="{{ old('value', '#4e73df') }}">
                                        <input type="text" class="form-control form-control-sm"
                                            id="color-hex" placeholder="#4e73df" value="{{ old('value', '#4e73df') }}" readonly>
                                    </div>
                                    <small class="text-muted">Supports hex (#4e73df) and rgba (rgba(255,255,255,0.1))</small>
                                </div>

                                <!-- URL Input -->
                                <input type="url" class="form-control form-control-sm @error('value') is-invalid @enderror value-input"
                                    name="value" id="value-url" placeholder="https://example.com"
                                    value="{{ old('value') }}" data-type="url" style="display: none;">

                                <!-- Email Input -->
                                <input type="email" class="form-control form-control-sm @error('value') is-invalid @enderror value-input"
                                    name="value" id="value-email" placeholder="email@example.com"
                                    value="{{ old('value') }}" data-type="email" style="display: none;">

                                <!-- Image Upload Input -->
                                <div class="value-input" id="value-image" data-type="image" style="display: none;">
                                    <input type="file" class="form-control form-control-sm @error('image_file') is-invalid @enderror"
                                        name="image_file" id="image-file-input" accept="image/*">
                                    <small class="text-muted d-block mt-1">Max 2MB (JPG, PNG, GIF, SVG)</small>
                                    <div id="image-preview" class="mt-2" style="display: none;">
                                        <img src="" alt="Preview" class="img-thumbnail" style="max-height: 150px; max-width: 200px;">
                                    </div>
                                    @error('image_file')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- File Upload Input -->
                                <div class="value-input" id="value-file" data-type="file" style="display: none;">
                                    <input type="file" class="form-control form-control-sm @error('file_upload') is-invalid @enderror"
                                        name="file_upload" id="file-upload-input">
                                    <small class="text-muted d-block mt-1">Max 5MB</small>
                                    <div id="file-preview" class="mt-2" style="display: none;">
                                        <span class="badge bg-info"><i class="fas fa-file"></i> <span id="file-name"></span></span>
                                    </div>
                                    @error('file_upload')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <small class="text-muted" id="value-hint">Select a type to see the appropriate input field</small>
                                @error('value')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section: Description and Options -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Description and Options</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea class="form-control form-control-sm @error('description') is-invalid @enderror"
                                    name="description" rows="3" placeholder="Describe the purpose of this setting">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section: Security and Status -->
                    <div class="mb-3">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-shield-alt me-2"></i>Security and Status</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_encrypted" id="is_encrypted"
                                        value="1" {{ old('is_encrypted') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_encrypted">
                                        <i class="fas fa-lock text-warning me-1"></i> Encrypt Value
                                    </label>
                                    <small class="d-block text-muted">Enable encryption for sensitive data (API keys, passwords, tokens)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                                        value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">
                                        Active
                                    </label>
                                    <small class="d-block text-muted">Only active settings are used by the application</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer py-2 bg-light">
                    <div class="d-flex justify-content-end gap-2">
                        <a class="btn btn-secondary btn-sm px-4" href="{{ route('app-settings.index') }}">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-success btn-sm px-4">
                            <i class="fas fa-save me-1"></i>Save Setting
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const valueInputs = document.querySelectorAll('.value-input');
        const valueHint = document.getElementById('value-hint');

        // Type hints
        const typeHints = {
            'string': 'Enter text value (single line)',
            'text': 'Enter text content (multiple lines)',
            'json': 'Enter valid JSON data (e.g., {"key": "value"})',
            'boolean': 'Toggle to enable/disable',
            'numeric': 'Enter numeric value (integer or decimal)',
            'color': 'Pick a color or enter hex/rgba value',
            'url': 'Enter a valid URL (e.g., https://example.com)',
            'email': 'Enter a valid email address',
            'image': 'Upload an image file (JPG, PNG, GIF, SVG)',
            'file': 'Upload any file type'
        };

        // Toggle input fields based on type selection
        typeSelect.addEventListener('change', function() {
            const selectedType = this.value;

            // Hide all inputs
            valueInputs.forEach(input => {
                input.style.display = 'none';
                // Disable hidden inputs to prevent submission
                const inputEl = input.querySelector('input, textarea');
                if (inputEl && inputEl.name === 'value') {
                    inputEl.disabled = true;
                }
            });

            // Show selected input
            if (selectedType) {
                const activeInput = document.querySelector(`[data-type="${selectedType}"]`);
                if (activeInput) {
                    activeInput.style.display = 'block';
                    // Enable the active input
                    const inputEl = activeInput.querySelector('input, textarea');
                    if (inputEl && inputEl.name === 'value') {
                        inputEl.disabled = false;
                    }
                }

                // Update hint
                valueHint.textContent = typeHints[selectedType] || 'Enter the setting value';
            } else {
                valueHint.textContent = 'Select a type to see the appropriate input field';
            }
        });

        // Boolean checkbox toggle
        const booleanCheckbox = document.getElementById('boolean-checkbox');
        if (booleanCheckbox) {
            booleanCheckbox.addEventListener('change', function() {
                const yesLabel = document.getElementById('boolean-label-yes');
                const noLabel = document.getElementById('boolean-label-no');
                if (this.checked) {
                    yesLabel.style.display = 'inline';
                    noLabel.style.display = 'none';
                } else {
                    yesLabel.style.display = 'none';
                    noLabel.style.display = 'inline';
                }
            });
        }

        // Color picker sync
        const colorPicker = document.getElementById('color-picker');
        const colorHex = document.getElementById('color-hex');
        if (colorPicker && colorHex) {
            colorPicker.addEventListener('input', function() {
                colorHex.value = this.value;
            });
        }

        // Image preview
        const imageInput = document.getElementById('image-file-input');
        const imagePreview = document.getElementById('image-preview');
        if (imageInput && imagePreview) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.querySelector('img').src = e.target.result;
                        imagePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.style.display = 'none';
                }
            });
        }

        // File preview
        const fileInput = document.getElementById('file-upload-input');
        const filePreview = document.getElementById('file-preview');
        const fileName = document.getElementById('file-name');
        if (fileInput && filePreview && fileName) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    fileName.textContent = file.name;
                    filePreview.style.display = 'block';
                } else {
                    filePreview.style.display = 'none';
                }
            });
        }

        // Trigger on page load if type is selected
        if (typeSelect.value) {
            typeSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection
