@extends('layouts.app')

@section('title', 'Edit WhatsApp Template')

@section('content')
<div class="container-fluid">
    @include('common.alert')

    <div class="card shadow mt-3 mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-edit me-2"></i>Edit WhatsApp Template</h5>
                <small class="text-muted">Update template: {{ $template->name }}</small>
            </div>
            <a href="{{ route('leads.whatsapp.templates.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Templates
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('leads.whatsapp.templates.update', $template->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Template Name -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><span class="text-danger">*</span> Template Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $template->name) }}" placeholder="e.g., Welcome Message" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><span class="text-danger">*</span> Category</label>
                        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            <option value="greeting" {{ old('category', $template->category) == 'greeting' ? 'selected' : '' }}>Greeting</option>
                            <option value="follow-up" {{ old('category', $template->category) == 'follow-up' ? 'selected' : '' }}>Follow-up</option>
                            <option value="promotion" {{ old('category', $template->category) == 'promotion' ? 'selected' : '' }}>Promotion</option>
                            <option value="promotional" {{ old('category', $template->category) == 'promotional' ? 'selected' : '' }}>Promotional</option>
                            <option value="reminder" {{ old('category', $template->category) == 'reminder' ? 'selected' : '' }}>Reminder</option>
                            <option value="general" {{ old('category', $template->category) == 'general' ? 'selected' : '' }}>General</option>
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Message Template -->
                    <div class="col-12">
                        <label class="form-label fw-semibold"><span class="text-danger">*</span> Message Template</label>
                        <textarea name="message_template" id="messageTemplate" rows="10"
                                  class="form-control @error('message_template') is-invalid @enderror"
                                  required>{{ old('message_template', $template->message_template) }}</textarea>
                        @error('message_template')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="mt-2">
                            <small class="text-muted d-block mb-2">
                                <strong>📋 Lead Variables:</strong>
                                <code>{name}</code>, <code>{mobile}</code>, <code>{email}</code>, <code>{source}</code>, <code>{status}</code>, <code>{priority}</code>, <code>{assigned_to}</code>, <code>{product_interest}</code>, <code>{lead_number}</code>
                            </small>
                            <small class="text-muted d-block">
                                <strong>🏢 System Variables:</strong>
                                <code>{company_name}</code>, <code>{company_website}</code>, <code>{company_phone}</code>, <code>{company_email}</code>, <code>{advisor_name}</code>, <code>{current_date}</code>, <code>{current_year}</code>, <code>{portal_url}</code>
                            </small>
                        </div>
                        <small id="charCount" class="text-muted float-end">{{ strlen($template->message_template) }} / 4096 characters</small>
                    </div>

                    <!-- Current Attachment -->
                    @if($template->attachment_path)
                    <div class="col-12">
                        <label class="form-label fw-semibold">Current Attachment</label>
                        <div class="alert alert-info">
                            <i class="fas fa-paperclip me-2"></i>{{ basename($template->attachment_path) }}
                            <small class="text-muted d-block mt-1">Upload a new file to replace</small>
                        </div>
                    </div>
                    @endif

                    <!-- New Attachment -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            {{ $template->attachment_path ? 'Replace Attachment' : 'Add Attachment' }} (Optional)
                        </label>
                        <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        @error('attachment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Supported formats: PDF, JPG, PNG, DOC, DOCX (Max: 5MB)</small>
                    </div>

                    <!-- Active Status -->
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                                   {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="isActive">
                                Active Template
                            </label>
                            <small class="text-muted d-block">Inactive templates won't appear in template selectors</small>
                        </div>
                    </div>

                    <!-- Usage Stats -->
                    <div class="col-12">
                        <div class="alert alert-light">
                            <h6><i class="fas fa-chart-bar me-2"></i>Template Statistics</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Usage Count:</strong> {{ $template->usage_count }} times
                                </div>
                                <div class="col-md-4">
                                    <strong>Created:</strong> {{ $template->created_at->format('d M Y') }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Last Updated:</strong> {{ $template->updated_at->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Message Preview</h6>
                            </div>
                            <div class="card-body">
                                <div class="border rounded p-3 bg-white" style="min-height: 100px;">
                                    <div id="messagePreview"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('leads.whatsapp.templates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Character counter and preview
    $('#messageTemplate').on('input', function() {
        const length = $(this).val().length;
        $('#charCount').text(length + ' / 4096 characters');

        if (length > 4096) {
            $('#charCount').addClass('text-danger');
        } else {
            $('#charCount').removeClass('text-danger');
        }

        updatePreview();
    });

    // Initial preview
    updatePreview();
});

function updatePreview() {
    let message = $('#messageTemplate').val();

    if (!message) {
        $('#messagePreview').html('<span class="text-muted">Type your message to see preview...</span>');
        return;
    }

    const sampleData = {
        // Lead variables
        '{name}': 'John Doe',
        '{mobile}': '+91 98765 43210',
        '{email}': 'john.doe@example.com',
        '{source}': 'Website',
        '{status}': 'New',
        '{priority}': 'High',
        '{assigned_to}': 'Sales Team',
        '{product_interest}': 'Car Insurance',
        '{lead_number}': 'LD-202511-0001',
        // System variables
        '{company_name}': 'Midas Insurance',
        '{company_website}': 'www.midasinsurance.com',
        '{company_phone}': '+91-1234567890',
        '{company_email}': 'info@midasinsurance.com',
        '{advisor_name}': 'Sales Team',
        '{current_date}': '02 Nov 2025',
        '{current_year}': '2025',
        '{portal_url}': 'https://portal.midasinsurance.com'
    };

    let preview = message;
    for (const [variable, value] of Object.entries(sampleData)) {
        preview = preview.replace(new RegExp(variable.replace(/[{}]/g, '\\$&'), 'g'), `<strong class="text-primary">${value}</strong>`);
    }

    preview = preview.replace(/\n/g, '<br>');
    $('#messagePreview').html(preview);
}
</script>
@endsection
