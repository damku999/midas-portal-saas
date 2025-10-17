@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mt-3 mb-4">
                <x-list-header
                        title="Quotations Management"
                        subtitle="Manage insurance quotations and quotes"
                        addRoute="quotations.create"
                        addPermission="quotation-create"
                        exportRoute="quotations.export"
                />
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('quotations.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by customer name, mobile, vehicle number..." 
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="Generated" {{ request('status') == 'Generated' ? 'selected' : '' }}>Generated</option>
                                    <option value="Sent" {{ request('status') == 'Sent' ? 'selected' : '' }}>Sent</option>
                                    <option value="Accepted" {{ request('status') == 'Accepted' ? 'selected' : '' }}>Accepted</option>
                                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="col-md-3 text-right">
                                <a href="{{ route('quotations.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-refresh"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Quotations Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>Quote Ref</th>
                                    <th>Customer</th>
                                    <th>Vehicle Details</th>
                                    <th>Policy Type</th>
                                    <th>Best Quote</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quotations as $quotation)
                                    <tr>
                                        <td>
                                            <strong>{{ $quotation->getQuoteReference() }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ $quotation->customer->name }}</strong><br>
                                            <small class="text-muted">{{ $quotation->customer->mobile_number }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $quotation->make_model_variant }}</strong><br>
                                            <small class="text-muted">
                                                {{ $quotation->vehicle_number ?? 'To be registered' }} | 
                                                {{ $quotation->fuel_type }} | 
                                                IDV: {{ format_indian_currency($quotation->total_idv) }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-white">{{ $quotation->policy_type }}</span><br>
                                            <small>{{ $quotation->policy_tenure_years }} Year(s)</small>
                                        </td>
                                        <td>
                                            @if($quotation->bestQuote())
                                                <strong class="text-success">{{ $quotation->bestQuote()->getFormattedPremium() }}</strong><br>
                                                <small class="text-muted">{{ $quotation->bestQuote()->insuranceCompany->name }}</small>
                                            @else
                                                <span class="text-muted">Not generated</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'Draft' => 'secondary',
                                                    'Generated' => 'info',
                                                    'Sent' => 'warning',
                                                    'Accepted' => 'success',
                                                    'Rejected' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$quotation->status] ?? 'secondary' }} text-white">
                                                {{ $quotation->status }}
                                            </span>
                                            @if($quotation->sent_at)
                                                <br><small class="text-muted">
                                                    Sent: {{ formatDateForUi($quotation->sent_at, 'M d, Y H:i') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ formatDateForUi($quotation->created_at, 'M d, Y') }}<br>
                                            <small class="text-muted">{{ $quotation->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap" style="gap: 6px; justify-content: flex-start; align-items: center;">
                                                <!-- 1. WhatsApp (First Priority) -->
                                                @if($quotation->quotationCompanies->count() > 0)
                                                    @can('quotation-send-whatsapp')
                                                        @if($quotation->status === 'Sent')
                                                            <button type="button" class="btn btn-warning btn-sm" 
                                                                    title="Resend via WhatsApp"
                                                                    onclick="showResendWhatsAppModal({{ $quotation->id }})">
                                                                <i class="fab fa-whatsapp"></i>
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-success btn-sm" 
                                                                    title="Send via WhatsApp"
                                                                    onclick="showSendWhatsAppModal({{ $quotation->id }})">
                                                                <i class="fab fa-whatsapp"></i>
                                                            </button>
                                                        @endif
                                                    @endcan
                                                @endif

                                                <!-- 2. Edit (Second Priority) -->
                                                @can('quotation-edit')
                                                <a href="{{ route('quotations.edit', $quotation) }}" 
                                                   class="btn btn-primary btn-sm" title="Edit Quotation">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endcan
                                                
                                                <!-- 3. Download (Third Priority) -->
                                                @if($quotation->quotationCompanies->count() > 0)
                                                    @can('quotation-download-pdf')
                                                    <a href="{{ route('quotations.download-pdf', $quotation) }}" 
                                                       class="btn btn-info btn-sm" title="Download PDF">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    @endcan
                                                @endif

                                                <!-- View Details (Keep for functionality) -->
                                                @can('quotation-edit')
                                                <a href="{{ route('quotations.show', $quotation) }}" 
                                                   class="btn btn-secondary btn-sm" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @endcan

                                                <!-- Generate Quotes (For quotations without companies) -->
                                                @if($quotation->quotationCompanies->count() == 0)
                                                    @can('quotation-generate')
                                                    <form method="POST" action="{{ route('quotations.generate-quotes', $quotation) }}" 
                                                          style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-warning btn-sm" 
                                                                title="Generate Quotes">
                                                            <i class="fas fa-cog"></i>
                                                        </button>
                                                    </form>
                                                    @endcan
                                                @endif
                                                
                                                <!-- Delete (Last) -->
                                                @can('quotation-delete')
                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                            title="Delete Quotation"
                                                            onclick="showDeleteQuotationModal({{ $quotation->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No quotations found</h5>
                                            <p class="text-muted">Start by creating your first insurance quotation.</p>
                                            @can('quotation-create')
                                            <a href="{{ route('quotations.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Create Quotation
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination with Record Count -->
                    <x-pagination-with-info :paginator="$quotations" :request="request()->query()" />
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for each quotation -->
@foreach($quotations as $quotation)
    @if($quotation->quotationCompanies->count() > 0)
        <!-- Send WhatsApp Modal -->
        <div class="modal fade" id="sendWhatsAppModal{{ $quotation->id }}" tabindex="-1" role="dialog" aria-labelledby="sendWhatsAppModalLabel{{ $quotation->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h6 class="modal-title" id="sendWhatsAppModalLabel{{ $quotation->id }}">
                            <i class="fab fa-whatsapp"></i> Send Quotation via WhatsApp
                        </h6>
                        <button type="button" class="btn-close btn-close-white" onclick="hideWhatsAppModal('sendWhatsAppModal{{ $quotation->id }}')" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="fab fa-whatsapp fa-2x text-success"></i>
                        </div>
                        <p class="text-center">Send quotation with PDF attachment to:</p>
                        <div class="alert alert-info">
                            <strong>{{ $quotation->getQuoteReference() }}</strong><br>
                            <strong>Customer:</strong> {{ $quotation->customer->name }}<br>
                            <strong>WhatsApp:</strong> {{ $quotation->whatsapp_number ?? $quotation->customer->mobile_number }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideWhatsAppModal('sendWhatsAppModal{{ $quotation->id }}')">Cancel</button>
                        <form method="POST" action="{{ route('quotations.send-whatsapp', $quotation) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fab fa-whatsapp"></i> Send Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resend WhatsApp Modal -->
        <div class="modal fade" id="resendWhatsAppModal{{ $quotation->id }}" tabindex="-1" role="dialog" aria-labelledby="resendWhatsAppModalLabel{{ $quotation->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h6 class="modal-title" id="resendWhatsAppModalLabel{{ $quotation->id }}">
                            <i class="fab fa-whatsapp"></i> Resend Quotation via WhatsApp
                        </h6>
                        <button type="button" class="btn-close" onclick="hideWhatsAppModal('resendWhatsAppModal{{ $quotation->id }}')" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="fab fa-whatsapp fa-2x text-warning"></i>
                        </div>
                        <p class="text-center">Resend quotation with updated PDF attachment to:</p>
                        <div class="alert alert-warning">
                            <strong>{{ $quotation->getQuoteReference() }}</strong><br>
                            <strong>Customer:</strong> {{ $quotation->customer->name }}<br>
                            <strong>WhatsApp:</strong> {{ $quotation->whatsapp_number ?? $quotation->customer->mobile_number }}<br>
                            <strong>Last Sent:</strong> {{ $quotation->sent_at ? formatDateForUi($quotation->sent_at, 'd M Y, H:i') : 'Not available' }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="hideWhatsAppModal('resendWhatsAppModal{{ $quotation->id }}')">Cancel</button>
                        <form method="POST" action="{{ route('quotations.send-whatsapp', $quotation) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="fab fa-whatsapp"></i> Resend Now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Quotation Modal -->
    <div class="modal fade" id="deleteQuotationModal{{ $quotation->id }}" tabindex="-1" role="dialog" aria-labelledby="deleteQuotationModalLabel{{ $quotation->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h6 class="modal-title" id="deleteQuotationModalLabel{{ $quotation->id }}">
                        <i class="fas fa-trash"></i> Delete Quotation
                    </h6>
                    <button type="button" class="btn-close btn-close-white" onclick="hideDeleteModal('deleteQuotationModal{{ $quotation->id }}')" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                    </div>
                    <p class="text-center"><strong>Are you sure you want to delete this quotation?</strong></p>
                    <div class="alert alert-danger">
                        <strong>{{ $quotation->getQuoteReference() }}</strong><br>
                        <strong>Customer:</strong> {{ $quotation->customer->name }}<br>
                        <strong>Vehicle:</strong> {{ $quotation->make_model_variant }}
                        @if($quotation->quotationCompanies->count() > 0)
                            <br><strong>Company Quotes:</strong> {{ $quotation->quotationCompanies->count() }} will also be deleted
                        @endif
                    </div>
                    <p class="text-warning small"><strong>Warning:</strong> This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideDeleteModal('deleteQuotationModal{{ $quotation->id }}')">Cancel</button>
                    <form method="POST" action="{{ route('quotations.delete', $quotation) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Permanently
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- Modal functions are now centralized in layouts/app.blade.php --}}

@endsection