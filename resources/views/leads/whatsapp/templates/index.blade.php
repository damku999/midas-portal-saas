@extends('layouts.app')

@section('title', 'WhatsApp Templates')

@section('content')
<div class="container-fluid">
    @include('common.alert')

    <div class="card shadow mt-3 mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-file-alt me-2"></i>WhatsApp Templates</h5>
                <small class="text-muted">Manage reusable message templates for lead communication</small>
            </div>
            <div class="d-flex gap-2">
                @if(auth()->user()->hasPermissionTo('lead-whatsapp-template-create'))
                <a href="{{ route('leads.whatsapp.templates.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Create Template
                </a>
                @endif
                <a href="{{ route('leads.whatsapp.campaigns.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to Campaigns
                </a>
            </div>
        </div>

        <div class="card-body">
            @if($templates->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Name</th>
                            <th width="12%">Category</th>
                            <th width="35%">Message Preview</th>
                            <th width="8%">Variables</th>
                            <th width="8%">Usage</th>
                            <th width="7%">Status</th>
                            <th width="10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $template)
                        <tr>
                            <td>{{ $loop->iteration + ($templates->currentPage() - 1) * $templates->perPage() }}</td>
                            <td>
                                <strong>{{ $template->name }}</strong>
                                <br><small class="text-muted">By: {{ $template->creator->first_name ?? 'System' }}</small>
                            </td>
                            <td>
                                @php
                                    $categoryColors = [
                                        'greeting' => 'primary',
                                        'follow-up' => 'info',
                                        'promotion' => 'success',
                                        'reminder' => 'warning',
                                        'general' => 'secondary'
                                    ];
                                    $color = $categoryColors[$template->category] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ ucfirst($template->category) }}</span>
                            </td>
                            <td>
                                <small>{{ Str::limit($template->message_template, 100) }}</small>
                                @if($template->attachment_path)
                                <br><span class="badge bg-info mt-1"><i class="fas fa-paperclip"></i> Has Attachment</span>
                                @endif
                            </td>
                            <td>
                                @if($template->variables && count($template->variables) > 0)
                                <small>
                                    @foreach(array_slice($template->variables, 0, 3) as $var)
                                        <code class="small">{{'{'}}{{ $var }}{{'}'}}</code>
                                    @endforeach
                                    @if(count($template->variables) > 3)
                                        <br><span class="text-muted">+{{ count($template->variables) - 3 }} more</span>
                                    @endif
                                </small>
                                @else
                                <small class="text-muted">None</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $template->usage_count }}</span>
                            </td>
                            <td>
                                @if($template->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    @if(auth()->user()->hasPermissionTo('lead-whatsapp-template-edit'))
                                    <a href="{{ route('leads.whatsapp.templates.edit', $template->id) }}"
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    @if(auth()->user()->hasPermissionTo('lead-whatsapp-template-delete'))
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="deleteTemplate({{ $template->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $templates->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Templates Created Yet</h5>
                <p class="text-muted">Create your first WhatsApp message template to get started</p>
                @if(auth()->user()->hasPermissionTo('lead-whatsapp-template-create'))
                <a href="{{ route('leads.whatsapp.templates.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Create First Template
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function deleteTemplate(templateId) {
    showConfirmationModal(
        'Delete Template',
        'Are you sure you want to delete this template? This action cannot be undone.',
        'danger',
        function() {
            $.ajax({
                url: `/leads/whatsapp/templates/${templateId}/delete`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        show_notification('success', 'Template deleted successfully');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        show_notification('error', response.message || 'Failed to delete template');
                    }
                },
                error: function(xhr) {
                    const error = xhr.responseJSON?.message || 'Failed to delete template';
                    show_notification('error', error);
                }
            });
        }
    );
}
</script>
@endsection
