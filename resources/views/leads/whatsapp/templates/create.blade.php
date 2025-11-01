@extends('layouts.app')

@section('title', 'Create WhatsApp Template')

@section('content')
<div class="container-fluid">
    @include('common.alert')

    <div class="card shadow mt-3 mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-plus-circle me-2"></i>Create WhatsApp Template</h5>
                <small class="text-muted">Create a reusable message template for lead communication</small>
            </div>
            <a href="{{ route('leads.whatsapp.templates.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Templates
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('leads.whatsapp.templates.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <!-- Template Name -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><span class="text-danger">*</span> Template Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g., Welcome Message" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Give your template a descriptive name</small>
                    </div>

                    <!-- Category -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><span class="text-danger">*</span> Category</label>
                        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            <option value="greeting" {{ old('category') == 'greeting' ? 'selected' : '' }}>Greeting</option>
                            <option value="follow-up" {{ old('category') == 'follow-up' ? 'selected' : '' }}>Follow-up</option>
                            <option value="promotion" {{ old('category') == 'promotion' ? 'selected' : '' }}>Promotion</option>
                            <option value="reminder" {{ old('category') == 'reminder' ? 'selected' : '' }}>Reminder</option>
                            <option value="general" {{ old('category') == 'general' ? 'selected' : '' }}>General</option>
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
                                  placeholder="Hi {name}, Thank you for your interest..." required>{{ old('message_template') }}</textarea>
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
                        <small id="charCount" class="text-muted float-end">0 / 4096 characters</small>
                    </div>

                    <!-- Attachment -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Attachment (Optional)</label>
                        <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror"
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        @error('attachment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Supported formats: PDF, JPG, PNG, DOC, DOCX (Max: 5MB)</small>
                    </div>

                    <!-- Preview Section -->
                    <div class="col-12">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Message Preview</h6>
                            </div>
                            <div class="card-body">
                                <div class="border rounded p-3 bg-white" style="min-height: 100px;">
                                    <div id="messagePreview" class="text-muted">
                                        Type your message to see preview...
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">
                                    <i class="fas fa-info-circle"></i> Variables will be replaced with actual lead data when sending
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('leads.whatsapp.templates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Template
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
    // Character counter
    $('#messageTemplate').on('input', function() {
        const length = $(this).val().length;
        $('#charCount').text(length + ' / 4096 characters');

        if (length > 4096) {
            $('#charCount').addClass('text-danger');
        } else {
            $('#charCount').removeClass('text-danger');
        }

        // Update preview
        updatePreview();
    });

    // Initial preview update
    updatePreview();
});

function updatePreview() {
    let message = $('#messageTemplate').val();

    if (!message) {
        $('#messagePreview').html('<span class="text-muted">Type your message to see preview...</span>');
        return;
    }

    // Replace variables with example data
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

    // Convert line breaks to <br>
    preview = preview.replace(/\n/g, '<br>');

    $('#messagePreview').html(preview);
}
</script>
@endsection
