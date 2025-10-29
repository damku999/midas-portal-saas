@extends('layouts.app')

@section('title', 'Create Notification Template')

@section('styles')
<style>
/* Match Bootstrap form-control-sm size for dropdowns only */
#preview_customer_id + .select2-container .select2-selection--single,
#preview_insurance_id + .select2-container .select2-selection--single,
#preview_quotation_id + .select2-container .select2-selection--single {
    height: calc(1.5em + 0.5rem + 2px) !important;
    padding: 0.25rem 0.5rem !important;
    font-size: 0.875rem !important;
}

#preview_customer_id + .select2-container .select2-selection__rendered,
#preview_insurance_id + .select2-container .select2-selection__rendered,
#preview_quotation_id + .select2-container .select2-selection__rendered {
    line-height: 1.5 !important;
    padding-left: 0 !important;
}

#preview_customer_id + .select2-container .select2-selection__arrow,
#preview_insurance_id + .select2-container .select2-selection__arrow,
#preview_quotation_id + .select2-container .select2-selection__arrow {
    height: calc(1.5em + 0.5rem) !important;
}
</style>
@endsection

@section('content')

    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- Notification Template Form -->
        <div class="card shadow mb-3 mt-2">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">Create Notification Template</h6>
                <a href="{{ route('notification-templates.index') }}" onclick="window.history.go(-1); return false;"
                    class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                    <i class="fas fa-chevron-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <form method="POST" action="{{ route('notification-templates.store') }}">
                @csrf
                <div class="card-body p-2">
                    <div class="row g-2">
                        <!-- Left Column: Form -->
                        <div class="col-md-6">
                            <!-- Basic Details -->
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold mb-1"><span class="text-danger">*</span> Notification Type</label>
                                    <select class="form-control form-control-sm @error('notification_type_id') is-invalid @enderror"
                                            id="notification_type_id" name="notification_type_id" required>
                                        <option value="">Select Type</option>
                                        @foreach($notificationTypes->groupBy('category') as $category => $types)
                                            <optgroup label="{{ ucwords($category) }}">
                                                @foreach($types as $type)
                                                    <option value="{{ $type->id }}"
                                                        {{ old('notification_type_id') == $type->id ? 'selected' : '' }}>
                                                        {{ $type->name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    @error('notification_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold mb-1"><span class="text-danger">*</span> Channel</label>
                                    <select class="form-control form-control-sm @error('channel') is-invalid @enderror"
                                            id="channel" name="channel" required>
                                        <option value="">Select</option>
                                        <option value="whatsapp" {{ old('channel') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                        <option value="email" {{ old('channel') === 'email' ? 'selected' : '' }}>Email</option>
                                    </select>
                                    @error('channel')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold mb-1">Status</label>
                                    <select class="form-control form-control-sm" id="is_active" name="is_active">
                                        <option value="1" {{ old('is_active', 1) ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ !old('is_active', 1) ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Email Subject (conditional) -->
                            <div class="mb-2" id="subjectSection" style="{{ old('channel') === 'email' ? '' : 'display:none;' }}">
                                <label class="form-label fw-semibold mb-1"><span class="text-danger">*</span> Email Subject</label>
                                <input type="text" class="form-control form-control-sm @error('subject') is-invalid @enderror"
                                       id="subject" name="subject" value="{{ old('subject') }}"
                                       placeholder="Enter email subject">
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Template Content -->
                            <div class="mb-2">
                                <label class="form-label fw-semibold mb-1"><span class="text-danger">*</span> Message Template</label>
                                <textarea class="form-control form-control-sm font-monospace @error('template_content') is-invalid @enderror"
                                          id="template_content" name="template_content" rows="8" required
                                          placeholder="Use @{{variable_name}} for dynamic content">{{ old('template_content') }}</textarea>
                                @error('template_content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Available Variables (Dynamic Loading) -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold mb-0">Available Variables</label>
                                    <small class="text-muted"><i class="fas fa-mouse-pointer"></i> Click to insert or copy</small>
                                </div>
                                <div class="border rounded bg-white">
                                    <div id="variablesContainer" class="accordion accordion-flush">
                                        <div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Loading variables...</div>
                                    </div>
                                </div>
                                <input type="hidden" id="available_variables" name="available_variables" value='{{ old('available_variables', '[]') }}'>
                            </div>
                        </div>

                        <!-- Right Column: Preview & Test -->
                        <div class="col-md-6">
                            <!-- Preview Data Selector -->
                            <div class="mb-2">
                                <label class="form-label fw-semibold mb-1"><i class="fas fa-database me-1"></i> Preview With Real Data</label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <select class="form-control form-control-sm select2" id="preview_customer_id">
                                            <option value="">Random Customer</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}">
                                                    {{ $customer->name }}
                                                    @if($customer->mobile_number) - {{ $customer->mobile_number }}@endif
                                                    @if($customer->email) - {{ $customer->email }}@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-control form-control-sm select2" id="preview_insurance_id" disabled>
                                            <option value="">Select customer first</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-control form-control-sm select2" id="preview_quotation_id" disabled>
                                            <option value="">Select customer first</option>
                                        </select>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Select customer to load their policies and quotations</small>
                            </div>

                            <!-- Live Preview -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold mb-0"><i class="fas fa-eye me-1"></i> Live Preview</label>
                                    <button type="button" class="btn btn-info btn-sm py-0 px-2" id="refreshPreview">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                                <div id="previewContent" class="border rounded p-2 bg-light font-monospace" style="height: 200px; overflow-y: auto; white-space: pre-wrap; font-size: 13px;">
                                    <span class="text-muted">Preview will appear here...</span>
                                </div>
                                <small id="previewContext" class="text-muted d-block mt-1"></small>
                            </div>

                            <!-- Send Test -->
                            <div class="mb-2">
                                <label class="form-label fw-semibold mb-1"><i class="fas fa-paper-plane me-1"></i> Send Test Message</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm" id="test_recipient"
                                           placeholder="Phone: {{ app_setting('notification_test_phone', 'application', '919727793123') }} or Email: {{ app_setting('notification_test_email', 'application', 'test@example.com') }}">
                                    <button type="button" class="btn btn-warning btn-sm" id="sendTestBtn">
                                        <i class="fas fa-paper-plane"></i> Send
                                    </button>
                                </div>
                                <div id="testResult" class="mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer py-2 bg-light">
                    <div class="d-flex justify-content-end gap-2">
                        <a class="btn btn-secondary btn-sm px-4" href="{{ route('notification-templates.index') }}">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-success btn-sm px-4">
                            <i class="fas fa-save me-1"></i>Create Template
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
    const channelSelect = document.getElementById('channel');
    const subjectSection = document.getElementById('subjectSection');
    const subjectInput = document.getElementById('subject');
    const templateContent = document.getElementById('template_content');
    const availableVariables = document.getElementById('available_variables');
    const previewContent = document.getElementById('previewContent');
    const previewContext = document.getElementById('previewContext');
    const refreshPreviewBtn = document.getElementById('refreshPreview');
    const sendTestBtn = document.getElementById('sendTestBtn');
    const testRecipient = document.getElementById('test_recipient');
    const testResult = document.getElementById('testResult');
    const variablesContainer = document.getElementById('variablesContainer');
    const previewCustomerId = document.getElementById('preview_customer_id');
    const previewInsuranceId = document.getElementById('preview_insurance_id');
    const previewQuotationId = document.getElementById('preview_quotation_id');

    // Show/hide email subject based on channel
    channelSelect.addEventListener('change', function() {
        if (this.value === 'email') {
            subjectSection.style.display = '';
            subjectInput.required = true;
        } else {
            subjectSection.style.display = 'none';
            subjectInput.required = false;
        }
    });

    // Load variables dynamically from API
    let isLoadingVariables = false;
    function loadVariables() {
        if (isLoadingVariables) {
            return;
        }

        isLoadingVariables = true;
        const notificationType = document.getElementById('notification_type_id')?.value;

        fetch('{{ route('notification-templates.variables') }}' + (notificationType ? `?notification_type=${notificationType}` : ''), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.variables) {
                renderVariables(data.variables);
            } else {
                variablesContainer.innerHTML = '<div class="text-danger">Failed to load variables</div>';
            }
        })
        .catch(error => {
            variablesContainer.innerHTML = '<div class="text-danger">Error loading variables: ' + error.message + '</div>';
        })
        .finally(() => {
            isLoadingVariables = false;
        });
    }

    // Render variable buttons with accordion UI
    function renderVariables(variablesGrouped) {
        variablesContainer.innerHTML = '';

        variablesGrouped.forEach((group, groupIndex) => {
            // Create accordion item
            const accordionItem = document.createElement('div');
            accordionItem.className = 'accordion-item border-0';

            // Accordion header
            const accordionHeader = document.createElement('h2');
            accordionHeader.className = 'accordion-header';
            accordionHeader.id = 'heading' + groupIndex;

            // Accordion button
            const accordionButton = document.createElement('button');
            accordionButton.className = 'accordion-button' + (groupIndex === 0 ? '' : ' collapsed');
            accordionButton.type = 'button';
            accordionButton.setAttribute('data-bs-toggle', 'collapse');
            accordionButton.setAttribute('data-bs-target', '#collapse' + groupIndex);
            accordionButton.setAttribute('aria-expanded', groupIndex === 0 ? 'true' : 'false');
            accordionButton.setAttribute('aria-controls', 'collapse' + groupIndex);
            accordionButton.style.fontSize = '13px';
            accordionButton.style.fontWeight = '600';
            accordionButton.style.padding = '8px 12px';
            accordionButton.textContent = group.label;

            accordionHeader.appendChild(accordionButton);
            accordionItem.appendChild(accordionHeader);

            // Accordion collapse
            const accordionCollapse = document.createElement('div');
            accordionCollapse.id = 'collapse' + groupIndex;
            accordionCollapse.className = 'accordion-collapse collapse' + (groupIndex === 0 ? ' show' : '');
            accordionCollapse.setAttribute('aria-labelledby', 'heading' + groupIndex);
            accordionCollapse.setAttribute('data-bs-parent', '#variablesContainer');

            // Accordion body
            const accordionBody = document.createElement('div');
            accordionBody.className = 'accordion-body p-2';

            // Variables wrapper
            const varsWrapper = document.createElement('div');
            varsWrapper.className = 'd-flex flex-wrap gap-1';

            group.variables.forEach(variable => {
                // Variable badge container
                const varBadge = document.createElement('div');
                varBadge.className = 'position-relative';
                varBadge.style.display = 'inline-block';

                // Main variable button (insert)
                const insertBtn = document.createElement('button');
                insertBtn.type = 'button';
                insertBtn.className = 'badge';
                insertBtn.style.cursor = 'pointer';
                insertBtn.style.fontSize = '11px';
                insertBtn.style.padding = '5px 10px';
                insertBtn.style.border = '1px solid';
                insertBtn.style.fontWeight = '500';
                insertBtn.setAttribute('title', variable.description + ' (Click to insert)');

                // Set color-specific styles for better readability (using theme colors)
                const colorMap = {
                    'primary': { bg: '{{ theme_color("primary") }}', border: '{{ theme_color("primary") }}', text: '#ffffff' },
                    'success': { bg: '{{ theme_color("success") }}', border: '{{ theme_color("success") }}', text: '#ffffff' },
                    'info': { bg: '{{ theme_color("info") }}', border: '{{ theme_color("info") }}', text: '#000000' },
                    'warning': { bg: '{{ theme_color("warning") }}', border: '{{ theme_color("warning") }}', text: '#000000' },
                    'danger': { bg: '{{ theme_color("danger") }}', border: '{{ theme_color("danger") }}', text: '#ffffff' },
                    'secondary': { bg: '{{ theme_color("secondary") }}', border: '{{ theme_color("secondary") }}', text: '#ffffff' },
                    'dark': { bg: '{{ theme_color("dark") }}', border: '{{ theme_color("dark") }}', text: '#ffffff' },
                    'light': { bg: '{{ theme_color("light") }}', border: '{{ theme_color("light") }}', text: '#000000' }
                };

                const colors = colorMap[variable.color] || colorMap['secondary'];
                insertBtn.style.backgroundColor = colors.bg;
                insertBtn.style.borderColor = colors.border;
                insertBtn.style.color = colors.text;

                // Create icon
                const plusIcon = document.createElement('i');
                plusIcon.className = 'fas fa-plus';
                plusIcon.style.fontSize = '9px';
                plusIcon.style.marginRight = '4px';

                // Set button content
                insertBtn.appendChild(plusIcon);
                insertBtn.appendChild(document.createTextNode(variable.key));

                insertBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    insertVariable(variable.key, this, variable.color);
                });

                // Copy button (small icon on hover)
                const copyBtn = document.createElement('button');
                copyBtn.type = 'button';
                copyBtn.className = 'btn btn-sm position-absolute';
                copyBtn.style.cssText = 'top: -8px; right: -8px; width: 18px; height: 18px; padding: 0; font-size: 9px; background: white; border: 1px solid #ddd; border-radius: 50%; opacity: 0; transition: opacity 0.2s;';
                copyBtn.setAttribute('title', 'Copy variable');
                copyBtn.innerHTML = '<i class="fas fa-copy"></i>';

                copyBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const varText = '{' + '{' + variable.key + '}' + '}';
                    copyToClipboard(varText, this);
                });

                // Show copy button on hover
                varBadge.addEventListener('mouseenter', function() {
                    copyBtn.style.opacity = '1';
                });
                varBadge.addEventListener('mouseleave', function() {
                    copyBtn.style.opacity = '0';
                });

                varBadge.appendChild(insertBtn);
                varBadge.appendChild(copyBtn);
                varsWrapper.appendChild(varBadge);
            });

            accordionBody.appendChild(varsWrapper);
            accordionCollapse.appendChild(accordionBody);
            accordionItem.appendChild(accordionCollapse);
            variablesContainer.appendChild(accordionItem);
        });
    }

    // Insert variable into template at cursor position
    function insertVariable(varName, buttonElement, color) {
        const textarea = templateContent;
        const cursorPos = textarea.selectionStart;
        const textBefore = textarea.value.substring(0, cursorPos);
        const textAfter = textarea.value.substring(cursorPos);

        // Insert variable with double curly braces
        const varText = '{' + '{' + varName + '}' + '}';
        textarea.value = textBefore + varText + textAfter;

        // Set cursor position after inserted variable
        textarea.selectionStart = textarea.selectionEnd = cursorPos + varText.length;
        textarea.focus();

        // Trigger preview refresh
        const event = new Event('input');
        textarea.dispatchEvent(event);

        // Visual feedback on button
        if (buttonElement) {
            // Save original styles
            const originalBg = buttonElement.style.backgroundColor;
            const originalBorder = buttonElement.style.borderColor;
            const originalColor = buttonElement.style.color;
            const originalContent = buttonElement.cloneNode(true);

            // Clear and set success state
            buttonElement.innerHTML = '';
            buttonElement.style.backgroundColor = '{{ theme_color("success") }}';
            buttonElement.style.borderColor = '{{ theme_color("success") }}';
            buttonElement.style.color = '#ffffff';

            const checkIcon = document.createElement('i');
            checkIcon.className = 'fas fa-check';
            checkIcon.style.fontSize = '9px';
            checkIcon.style.marginRight = '4px';

            buttonElement.appendChild(checkIcon);
            buttonElement.appendChild(document.createTextNode('Inserted!'));

            setTimeout(() => {
                // Restore original state
                buttonElement.innerHTML = '';
                buttonElement.style.backgroundColor = originalBg;
                buttonElement.style.borderColor = originalBorder;
                buttonElement.style.color = originalColor;

                while (originalContent.firstChild) {
                    buttonElement.appendChild(originalContent.firstChild);
                }
            }, 1500);
        }
    }

    // Copy to clipboard function
    function copyToClipboard(text, buttonElement) {
        // Use modern clipboard API
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                showCopyFeedback(buttonElement, text);
            }).catch(err => {
                // Fallback to older method
                fallbackCopyToClipboard(text, buttonElement);
            });
        } else {
            fallbackCopyToClipboard(text, buttonElement);
        }
    }

    // Fallback copy method for older browsers
    function fallbackCopyToClipboard(text, buttonElement) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand('copy');
            showCopyFeedback(buttonElement, text);
        } catch (err) {
            toastr.error('Failed to copy');
        }

        document.body.removeChild(textArea);
    }

    // Show copy success feedback
    function showCopyFeedback(buttonElement, copiedText) {
        if (buttonElement) {
            const originalHTML = buttonElement.innerHTML;
            const checkIcon = document.createElement('i');
            checkIcon.className = 'fas fa-check';
            buttonElement.innerHTML = '';
            buttonElement.appendChild(checkIcon);
            buttonElement.style.background = '{{ theme_color("success") }}';
            buttonElement.style.borderColor = '{{ theme_color("success") }}';
            buttonElement.style.color = 'white';

            setTimeout(() => {
                buttonElement.innerHTML = originalHTML;
                buttonElement.style.background = 'white';
                buttonElement.style.borderColor = '#ddd';
                buttonElement.style.color = '';
            }, 1000);
        }
        toastr.success('Copied: ' + copiedText);
    }

    // Initialize Simple Select2 (matching quotation form style)
    if (typeof $.fn.select2 !== 'undefined') {
        $(previewCustomerId).select2({
            placeholder: 'Random Customer',
            allowClear: true,
            width: '100%'
        });

        $(previewInsuranceId).select2({
            placeholder: 'Select customer first',
            allowClear: true,
            width: '100%'
        });

        $(previewQuotationId).select2({
            placeholder: 'Select customer first',
            allowClear: true,
            width: '100%'
        });
    }

    // Handle customer selection - load their policies and quotations
    $(previewCustomerId).on('change', function(e) {
        const customerId = $(this).val();

        if (!customerId) {
            // No customer selected - reset to defaults
            $(previewInsuranceId).empty().append('<option value="">Select customer first</option>');
            $(previewInsuranceId).prop('disabled', true);
            $(previewQuotationId).empty().append('<option value="">Select customer first</option>');
            $(previewQuotationId).prop('disabled', true);
            refreshPreview();
            return;
        }

        // Show loading state
        $(previewInsuranceId).empty().append('<option value="">Loading...</option>');
        $(previewInsuranceId).prop('disabled', true);
        $(previewQuotationId).empty().append('<option value="">Loading...</option>');
        $(previewQuotationId).prop('disabled', true);

        // Fetch customer's policies and quotations
        fetch('{{ route('notification-templates.customer-data') }}?customer_id=' + customerId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Populate policies
                    $(previewInsuranceId).empty().append('<option value="">Select Policy (Optional)</option>');
                    if (data.policies && data.policies.length > 0) {
                        data.policies.forEach(policy => {
                            let text = policy.policy_no;
                            if (policy.registration_no) text += ' - ' + policy.registration_no;
                            if (policy.status == 0) text += ' (Inactive)';
                            $(previewInsuranceId).append(new Option(text, policy.id));
                        });
                    }
                    $(previewInsuranceId).prop('disabled', false);

                    // Populate quotations
                    $(previewQuotationId).empty().append('<option value="">Select Quotation (Optional)</option>');
                    if (data.quotations && data.quotations.length > 0) {
                        data.quotations.forEach(quotation => {
                            let text = 'Q#' + quotation.id;
                            if (quotation.vehicle_number) text += ' - ' + quotation.vehicle_number;
                            if (quotation.make_model_variant) {
                                text += ' (' + quotation.make_model_variant + ')';
                            }
                            $(previewQuotationId).append(new Option(text.trim(), quotation.id));
                        });
                    }
                    $(previewQuotationId).prop('disabled', false);

                    // Refresh preview
                    refreshPreview();
                } else {
                    throw new Error(data.message || 'Failed to load data');
                }
            })
            .catch(error => {
                toastr.error('Failed to load customer data: ' + error.message);
                $(previewInsuranceId).empty().append('<option value="">Error loading</option>');
                $(previewInsuranceId).prop('disabled', true);
                $(previewQuotationId).empty().append('<option value="">Error loading</option>');
                $(previewQuotationId).prop('disabled', true);
            });
    });

    // Refresh preview when policy or quotation changes
    $(previewInsuranceId).on('change', function() {
        refreshPreview();
    });

    $(previewQuotationId).on('change', function() {
        refreshPreview();
    });

    // Initialize: Load variables on page load
    loadVariables();

    // Auto-detect variables used in template and update hidden field
    function updateAvailableVariables() {
        const content = templateContent.value;
        const variablePattern = /\{\{(\w+)\}\}/g;
        const matches = content.matchAll(variablePattern);
        const variables = [...new Set([...matches].map(m => m[1]))];
        availableVariables.value = JSON.stringify(variables);
    }

    templateContent.addEventListener('input', function() {
        updateAvailableVariables();
    });

    // Initial update
    updateAvailableVariables();

    // Live Preview Function with Real Data
    function refreshPreview() {
        const content = templateContent.value;
        const customerId = previewCustomerId.value;
        const insuranceId = previewInsuranceId.value;
        const quotationId = previewQuotationId.value;

        if (!content) {
            previewContent.innerHTML = '<span class="text-muted">Enter template content to preview...</span>';
            previewContext.textContent = '';
            return;
        }

        refreshPreviewBtn.disabled = true;
        refreshPreviewBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

        const requestData = {
            template_content: content
        };

        if (customerId) requestData.customer_id = parseInt(customerId);
        if (insuranceId) requestData.insurance_id = parseInt(insuranceId);
        if (quotationId) requestData.quotation_id = parseInt(quotationId);

        fetch('{{ route('notification-templates.preview') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                previewContent.innerHTML = data.preview.replace(/\n/g, '<br>');

                // Show context info
                if (data.context_info) {
                    let contextText = '<i class="fas fa-check-circle text-success"></i> Using: ';
                    if (data.context_info.customer) {
                        contextText += data.context_info.customer;
                    }
                    if (data.context_info.insurance) {
                        contextText += ' | Policy: ' + data.context_info.insurance;
                    }
                    previewContext.innerHTML = contextText;
                } else {
                    previewContext.textContent = '';
                }
            } else {
                previewContent.innerHTML = '<span class="text-danger">Preview failed: ' + (data.error || 'Unknown error') + '</span>';
                previewContext.textContent = '';
            }
        })
        .catch(error => {
            previewContent.innerHTML = '<span class="text-danger">Error: ' + error.message + '</span>';
            previewContext.textContent = '';
        })
        .finally(() => {
            refreshPreviewBtn.disabled = false;
            refreshPreviewBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        });
    }

    // Auto-refresh preview on content change (debounced)
    let previewTimeout;
    templateContent.addEventListener('input', function() {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(refreshPreview, 1500);
    });

    availableVariables.addEventListener('input', function() {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(refreshPreview, 1500);
    });

    // Manual refresh
    refreshPreviewBtn.addEventListener('click', refreshPreview);

    // Send Test Function with Real Data
    sendTestBtn.addEventListener('click', function() {
        const recipient = testRecipient.value.trim();
        const channel = channelSelect.value;
        const subject = subjectInput.value;
        const content = templateContent.value;
        const customerId = previewCustomerId.value;
        const insuranceId = previewInsuranceId.value;

        if (!recipient) {
            toastr.error('Please enter test recipient');
            return;
        }

        if (!content) {
            toastr.error('Template content is empty');
            return;
        }

        if (!channel) {
            toastr.error('Please select a channel');
            return;
        }

        sendTestBtn.disabled = true;
        sendTestBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        testResult.innerHTML = '';

        const requestData = {
            recipient: recipient,
            channel: channel,
            subject: subject,
            template_content: content
        };

        if (customerId) requestData.customer_id = parseInt(customerId);
        if (insuranceId) requestData.insurance_id = parseInt(insuranceId);

        fetch('{{ route('notification-templates.send-test') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                testResult.innerHTML = '<div class="alert alert-success alert-sm py-2 mb-0"><i class="fas fa-check-circle"></i> ' + data.message + '</div>';
                toastr.success('Test message sent successfully');
            } else {
                testResult.innerHTML = '<div class="alert alert-danger alert-sm py-2 mb-0"><i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Failed to send') + '</div>';
                toastr.error(data.message || 'Failed to send test');
            }
        })
        .catch(error => {
            testResult.innerHTML = '<div class="alert alert-danger alert-sm py-2 mb-0"><i class="fas fa-exclamation-circle"></i> Error: ' + error.message + '</div>';
            toastr.error('Failed to send test message');
        })
        .finally(() => {
            sendTestBtn.disabled = false;
            sendTestBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
        });
    });
});
</script>
@endsection
