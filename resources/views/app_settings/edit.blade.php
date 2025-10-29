@extends('layouts.app')

@section('title', 'Edit App Setting')

@section('content')

    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- App Setting Form -->
        <div class="card shadow mb-3 mt-2">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">Edit App Setting</h6>
                <a href="{{ route('app-settings.index') }}" onclick="window.history.go(-1); return false;"
                    class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                    <i class="fas fa-chevron-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <form method="POST" action="{{ route('app-settings.update', $setting->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body py-3">
                    <!-- Section: Basic Information -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-cog me-2"></i>Basic Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="key" class="form-label fw-semibold"><span class="text-danger">*</span> Key</label>
                                <input type="text" class="form-control form-control-sm @error('key') is-invalid @enderror"
                                    name="key" id="key" placeholder="e.g., whatsapp.api_token" value="{{ old('key', $setting->key) }}">
                                <small class="text-muted">Unique identifier for this setting (use dot notation for grouping)</small>
                                @error('key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label fw-semibold"><span class="text-danger">*</span> Category</label>
                                <select class="form-control form-control-sm @error('category') is-invalid @enderror"
                                    name="category" id="category">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $key => $value)
                                        <option value="{{ $key }}" {{ old('category', $setting->category) == $key ? 'selected' : '' }}>
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
                        @if($setting->is_encrypted)
                            <div class="alert alert-warning" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> This setting contains encrypted data. Changing the value will re-encrypt it with the new data.
                                Leave the value field empty if you don't want to change the encrypted value.
                            </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="type" class="form-label fw-semibold"><span class="text-danger">*</span> Type</label>
                                <select class="form-control form-control-sm @error('type') is-invalid @enderror"
                                    name="type" id="type">
                                    <option value="">Select Type</option>
                                    @foreach($types as $key => $value)
                                        <option value="{{ $key }}" {{ old('type', $setting->type) == $key ? 'selected' : '' }}>
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
                                <label for="value-string" class="form-label fw-semibold">
                                    <span class="text-danger">*</span> Value
                                    @if($setting->is_encrypted)
                                        <span class="badge bg-warning text-dark ms-1">
                                            <i class="fas fa-lock"></i> Encrypted
                                        </span>
                                    @endif
                                </label>
                                @if($setting->is_encrypted)
                                    <div class="mb-2">
                                        <div class="p-2 bg-light border rounded">
                                            <span id="encrypted-value-{{ $setting->id }}" class="text-muted font-monospace">******</span>
                                            <button type="button" class="btn btn-xs btn-outline-warning ms-2" id="decrypt-btn-{{ $setting->id }}" onclick="viewDecryptedValue({{ $setting->id }})">
                                                <i class="fas fa-eye"></i> View Current Value
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary ms-2 d-none" id="hide-btn-{{ $setting->id }}" onclick="hideDecryptedValue({{ $setting->id }})">
                                                <i class="fas fa-eye-slash"></i> Hide
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-1">Current encrypted value (click to view)</small>
                                    </div>
                                @endif

                                @php
                                    $currentValue = old('value', $setting->is_encrypted ? '' : (is_array($setting->value) ? json_encode($setting->value, JSON_PRETTY_PRINT) : $setting->value));
                                @endphp

                                <!-- String Input -->
                                <input type="text" class="form-control form-control-sm @error('value') is-invalid @enderror value-input"
                                    {{ $setting->type === 'string' ? 'name=value' : '' }} data-name="value" id="value-string" placeholder="Enter text value"
                                    value="{{ $setting->type === 'string' ? $currentValue : '' }}" data-type="string" style="{{ $setting->type === 'string' ? '' : 'display: none;' }}">

                                <!-- Text/Textarea Input -->
                                <textarea class="form-control form-control-sm @error('value') is-invalid @enderror value-input"
                                    {{ $setting->type === 'text' ? 'name=value' : '' }} data-name="value" rows="3" id="value-text" placeholder="Enter text content"
                                    data-type="text" style="{{ $setting->type === 'text' ? '' : 'display: none;' }}">{{ $setting->type === 'text' ? $currentValue : '' }}</textarea>

                                <!-- JSON Input -->
                                <textarea class="form-control form-control-sm @error('value') is-invalid @enderror value-input font-monospace"
                                    {{ $setting->type === 'json' ? 'name=value' : '' }} data-name="value" rows="4" id="value-json" placeholder='{"key": "value"}'
                                    data-type="json" style="{{ $setting->type === 'json' ? '' : 'display: none;' }}">{{ $setting->type === 'json' ? $currentValue : '' }}</textarea>

                                <!-- Number Input -->
                                <input type="number" class="form-control form-control-sm @error('value') is-invalid @enderror value-input"
                                    {{ $setting->type === 'numeric' ? 'name=value' : '' }} data-name="value" id="value-numeric" placeholder="Enter number"
                                    value="{{ $setting->type === 'numeric' ? $currentValue : '' }}" data-type="numeric" style="{{ $setting->type === 'numeric' ? '' : 'display: none;' }}">

                                <!-- Boolean Input -->
                                <div class="value-input" id="value-boolean" data-type="boolean" style="{{ $setting->type === 'boolean' ? '' : 'display: none;' }}">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" {{ $setting->type === 'boolean' ? 'name=value' : '' }} data-name="value" id="boolean-checkbox"
                                            value="1" {{ ($currentValue == 'true' || $currentValue == '1') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="boolean-checkbox">
                                            <span class="badge bg-success" id="boolean-label-yes" {{ ($currentValue == 'true' || $currentValue == '1') ? '' : 'style=display:none;' }}>Yes / Enabled</span>
                                            <span class="badge bg-secondary" id="boolean-label-no" {{ ($currentValue == 'true' || $currentValue == '1') ? 'style=display:none;' : '' }}>No / Disabled</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Color Input -->
                                <div class="value-input" id="value-color" data-type="color" style="{{ $setting->type === 'color' ? '' : 'display: none;' }}">
                                    <div class="input-group input-group-sm">
                                        <input type="color" class="form-control form-control-sm form-control-color"
                                            id="color-picker" value="{{ $setting->type === 'color' && $currentValue && str_starts_with($currentValue, '#') ? $currentValue : theme_primary_color() }}">
                                        <input type="text" class="form-control form-control-sm"
                                            {{ $setting->type === 'color' ? 'name=value' : '' }} data-name="value" id="color-hex" placeholder="Enter hex or rgba value" value="{{ $setting->type === 'color' ? $currentValue : '' }}">
                                    </div>
                                    <small class="text-muted">Supports hex ({{ theme_primary_color() }}) and rgba (rgba(255,255,255,0.1))</small>
                                </div>

                                <!-- URL Input -->
                                <input type="url" class="form-control form-control-sm @error('value') is-invalid @enderror value-input"
                                    {{ $setting->type === 'url' ? 'name=value' : '' }} data-name="value" id="value-url" placeholder="https://example.com"
                                    value="{{ $setting->type === 'url' ? $currentValue : '' }}" data-type="url" style="{{ $setting->type === 'url' ? '' : 'display: none;' }}">

                                <!-- Email Input -->
                                <input type="email" class="form-control form-control-sm @error('value') is-invalid @enderror value-input"
                                    {{ $setting->type === 'email' ? 'name=value' : '' }} data-name="value" id="value-email" placeholder="email@example.com"
                                    value="{{ $setting->type === 'email' ? $currentValue : '' }}" data-type="email" style="{{ $setting->type === 'email' ? '' : 'display: none;' }}">

                                <!-- Image Upload Input -->
                                <div class="value-input" id="value-image" data-type="image" style="{{ $setting->type === 'image' ? '' : 'display: none;' }}">
                                    @if($setting->type === 'image' && $setting->value)
                                        <div class="mb-2">
                                            <label for="image-file-input" class="form-label fw-semibold small">Current Image:</label>
                                            <div>
                                                <img src="{{ Storage::url($setting->value) }}" alt="Current Image" class="img-thumbnail" style="max-height: 150px; max-width: 200px;">
                                            </div>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control form-control-sm @error('image_file') is-invalid @enderror"
                                        name="image_file" id="image-file-input" accept="image/*">
                                    <small class="text-muted d-block mt-1">Max 2MB (JPG, PNG, GIF, SVG) - Leave empty to keep current</small>
                                    <div id="image-preview" class="mt-2" style="display: none;">
                                        <label class="form-label fw-semibold small">New Preview:</label>
                                        <img src="" alt="Preview" class="img-thumbnail" style="max-height: 150px; max-width: 200px;">
                                    </div>
                                    @error('image_file')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- File Upload Input -->
                                <div class="value-input" id="value-file" data-type="file" style="{{ $setting->type === 'file' ? '' : 'display: none;' }}">
                                    @if($setting->type === 'file' && $setting->value)
                                        <div class="mb-2">
                                            <label for="file-upload-input" class="form-label fw-semibold small">Current File:</label>
                                            <div>
                                                <a href="{{ Storage::url($setting->value) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-file"></i> {{ basename($setting->value) }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control form-control-sm @error('file_upload') is-invalid @enderror"
                                        name="file_upload" id="file-upload-input">
                                    <small class="text-muted d-block mt-1">Max 5MB - Leave empty to keep current</small>
                                    <div id="file-preview" class="mt-2" style="display: none;">
                                        <label class="form-label fw-semibold small">New File:</label>
                                        <span class="badge bg-info"><i class="fas fa-file"></i> <span id="file-name"></span></span>
                                    </div>
                                    @error('file_upload')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <small class="text-muted" id="value-hint">
                                    @if($setting->is_encrypted)
                                        Enter new value above to update the encrypted setting (leave empty to keep current)
                                    @else
                                        Current value is displayed above
                                    @endif
                                </small>
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
                                <label for="description" class="form-label fw-semibold">Description</label>
                                <textarea class="form-control form-control-sm @error('description') is-invalid @enderror"
                                    name="description" id="description" rows="3" placeholder="Describe the purpose of this setting">{{ old('description', $setting->description) }}</textarea>
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
                                        value="1" {{ old('is_encrypted', $setting->is_encrypted) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_encrypted">
                                        <i class="fas fa-lock text-warning me-1"></i> Encrypt Value
                                    </label>
                                    <small class="d-block text-muted">Enable encryption for sensitive data (API keys, passwords, tokens)</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                                        value="1" {{ old('is_active', $setting->is_active) ? 'checked' : '' }}>
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
                            <i class="fas fa-save me-1"></i>Update Setting
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

@endsection

@section('scripts')
<script nonce="{{ $cspNonce ?? '' }}">
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const valueInputs = document.querySelectorAll('.value-input');
        const valueHint = document.getElementById('value-hint');
        const isEncrypted = {{ $setting->is_encrypted ? 'true' : 'false' }};

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
            'image': 'Upload an image file (JPG, PNG, GIF, SVG) - Leave empty to keep current',
            'file': 'Upload any file type - Leave empty to keep current'
        };

        // Toggle input fields based on type selection
        typeSelect.addEventListener('change', function() {
            const selectedType = this.value;

            // Hide all inputs and remove name attribute
            valueInputs.forEach(input => {
                input.style.display = 'none';
                const inputEl = input.querySelector('input, textarea');
                if (inputEl && inputEl.getAttribute('data-name') === 'value') {
                    inputEl.removeAttribute('name');
                }
            });

            // Show selected input and add name attribute
            if (selectedType) {
                const activeInput = document.querySelector(`[data-type="${selectedType}"]`);
                if (activeInput) {
                    activeInput.style.display = 'block';
                    const inputEl = activeInput.querySelector('input, textarea');
                    if (inputEl && inputEl.getAttribute('data-name') === 'value') {
                        inputEl.setAttribute('name', 'value');
                    }
                }

                // Update hint
                if (!isEncrypted) {
                    valueHint.textContent = typeHints[selectedType] || 'Enter the setting value';
                }
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

        // Color picker sync (only for hex colors)
        const colorPicker = document.getElementById('color-picker');
        const colorHex = document.getElementById('color-hex');
        if (colorPicker && colorHex) {
            colorPicker.addEventListener('input', function() {
                colorHex.value = this.value;
            });

            // If color hex is changed manually and is valid hex, update picker
            colorHex.addEventListener('input', function() {
                const hexRegex = /^#[0-9A-F]{6}$/i;
                if (hexRegex.test(this.value)) {
                    colorPicker.value = this.value;
                }
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

        // Initialize correct input on page load
        if (typeSelect.value) {
            typeSelect.dispatchEvent(new Event('change'));
        }

        // Form validation and submission
        const form = document.querySelector('form');
        if (form) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Validate form
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return false;
                    }

                    // Submit form
                    form.submit();
                });
            }
        }
    });

    // Decrypt functions
    function viewDecryptedValue(settingId) {
        const valueEl = $('#encrypted-value-' + settingId);
        const decryptBtn = $('#decrypt-btn-' + settingId);
        const hideBtn = $('#hide-btn-' + settingId);

        decryptBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

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
                    decryptBtn.prop('disabled', false).html('<i class="fas fa-eye"></i> View Current Value');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error decrypting value';
                toastr.error(message);
                decryptBtn.prop('disabled', false).html('<i class="fas fa-eye"></i> View Current Value');
            }
        });
    }

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
