@extends('layouts.app')

@section('title', 'Quotation Details')

@section('content')
    <div class="container-fluid">
        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- Quotation Details Card -->
        <div class="card shadow mb-3 mt-2">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold text-primary">Quotation Details</h6>
                    <small class="text-muted">{{ $quotation->quotation_number }} | {{ $quotation->customer->name ?? 'N/A' }} | {{ ucfirst($quotation->status) }} | {{ $quotation->quotationCompanies->count() }} Companies</small>
                </div>
                <div class="d-flex gap-2">
                    @can('quotation-edit')
                        <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-primary btn-sm d-flex align-items-center">
                            <i class="fas fa-edit me-2"></i>
                            <span>Edit Quotation</span>
                        </a>
                    @endcan
                    <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                        <i class="fas fa-list me-2"></i>
                        <span>Back to List</span>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="row m-0">
            <!-- Quotation Summary -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Quotation Summary</h6>
                        @php
                            $statusColors = [
                                'Draft' => 'secondary',
                                'Generated' => 'info',
                                'Sent' => 'warning',
                                'Accepted' => 'success',
                                'Rejected' => 'danger',
                            ];
                        @endphp
                        <span class="badge badge-{{ $statusColors[$quotation->status] ?? 'secondary' }}">
                            {{ $quotation->status }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Quote Reference:</strong><br>
                            <span class="h6 text-primary">{{ $quotation->getQuoteReference() }}</span>
                        </div>

                        <div class="mb-3">
                            <strong>Customer:</strong><br>
                            {{ $quotation->customer->name }}<br>
                            <small class="text-muted">
                                <strong>Mobile:</strong> {{ $quotation->customer->mobile_number }}
                                @if ($quotation->whatsapp_number && $quotation->whatsapp_number !== $quotation->customer->mobile_number)
                                    <br><strong>WhatsApp:</strong> {{ $quotation->whatsapp_number }}
                                @endif
                            </small>
                        </div>

                        <div class="mb-3">
                            <strong>Vehicle Details:</strong><br>
                            {{ $quotation->make_model_variant }}<br>
                            <small class="text-muted">
                                <strong>Number:</strong> {{ $quotation->vehicle_number ?? 'New Vehicle' }}<br>
                                <strong>RTO:</strong> {{ $quotation->rto_location }} |
                                <strong>Fuel:</strong> {{ $quotation->fuel_type }} |
                                <strong>NCB:</strong> {{ $quotation->ncb_percentage ?? 0 }}% |
                                <strong>Year:</strong> {{ $quotation->manufacturing_year }}<br>
                                <strong>CC/KW:</strong> {{ number_format($quotation->cubic_capacity_kw) }} |
                                <strong>Seating:</strong> {{ $quotation->seating_capacity }} seats
                            </small>
                        </div>

                        <div class="mb-3">
                            <strong>Policy Details:</strong><br>
                            {{ $quotation->policy_type ?? 'Comprehensive' }} - {{ $quotation->policy_tenure_years ?? 1 }}
                            Year(s)<br>
                            <small class="text-muted">
                                <div class="mt-2">
                                    <strong>IDV Breakdown:</strong><br>
                                    <div class="ml-2">
                                        Vehicle: {{ format_indian_currency($quotation->idv_vehicle ?? 0) }}<br>
                                        @if ($quotation->idv_trailer > 0)
                                            Trailer: {{ format_indian_currency($quotation->idv_trailer) }}<br>
                                        @endif
                                        @if ($quotation->idv_cng_lpg_kit > 0)
                                            CNG/LPG Kit: {{ format_indian_currency($quotation->idv_cng_lpg_kit) }}<br>
                                        @endif
                                        @if ($quotation->idv_electrical_accessories > 0)
                                            Electrical Accessories:
                                            {{ format_indian_currency($quotation->idv_electrical_accessories) }}<br>
                                        @endif
                                        @if ($quotation->idv_non_electrical_accessories > 0)
                                            Non-Electrical Accessories:
                                            {{ format_indian_currency($quotation->idv_non_electrical_accessories) }}<br>
                                        @endif
                                    </div>
                                    <strong class="text-success">Total IDV:
                                        {{ format_indian_currency($quotation->total_idv) }}</strong>
                                </div>
                            </small>
                        </div>

                        @if ($quotation->addon_covers)
                            <div class="mb-3">
                                <strong>Add-on Covers:</strong><br>
                                @foreach ($quotation->addon_covers as $addon)
                                    <span class="badge badge-light">{{ $addon }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if ($quotation->notes)
                            <div class="mb-3">
                                <strong>Notes:</strong><br>
                                <small>{{ $quotation->notes }}</small>
                            </div>
                        @endif

                        <div class="mb-3">
                            <strong>Created:</strong><br>
                            <small class="text-muted">
                                {{ formatDateForUi($quotation->created_at, 'd M Y, H:i') }}
                            </small>
                        </div>

                        @if ($quotation->sent_at)
                            <div class="mb-3">
                                <strong>Sent:</strong><br>
                                <small class="text-muted">
                                    {{ formatDateForUi($quotation->sent_at, 'd M Y, H:i') }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow mt-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        @if ($quotation->quotationCompanies->count() == 0)
                            @can('quotation-generate')
                                <form method="POST" action="{{ route('quotations.generate-quotes', $quotation) }}"
                                    class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-block">
                                        <i class="fas fa-cog"></i> Generate Company Quotes
                                    </button>
                                </form>
                            @endcan
                        @else
                            @can('quotation-download-pdf')
                                <a href="{{ route('quotations.download-pdf', $quotation) }}"
                                    class="btn btn-primary btn-block mb-2">
                                    <i class="fas fa-download"></i> Download PDF
                                </a>
                            @endcan

                            @can('quotation-send-whatsapp')
                                @if ($quotation->status === 'Sent')
                                    <button type="button" class="btn btn-warning btn-block mb-2" onclick="showModal('resendWhatsAppModal')">
                                        <i class="fab fa-whatsapp"></i> Resend via WhatsApp
                                    </button>
                                @else
                                    <button type="button" class="btn btn-success btn-block mb-2" onclick="showModal('sendWhatsAppModal')">
                                        <i class="fab fa-whatsapp"></i> Send via WhatsApp
                                    </button>
                                @endif
                            @endcan
                        @endif

                        @can('quotation-delete')
                            <button type="button" class="btn btn-danger btn-block" onclick="showDeleteQuotationModal()">
                                <i class="fas fa-trash"></i> Delete Quotation
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Company Quotes -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-building"></i> Company Quotes
                            @if ($quotation->quotationCompanies->count() > 0)
                                <span class="badge badge-info">{{ $quotation->quotationCompanies->count() }}</span>
                            @endif
                        </h6>
                        @if ($quotation->quotationCompanies->count() > 0 && $quotation->bestQuote())
                            <div class="text-right">
                                <small class="text-muted">Best Quote:</small><br>
                                <strong class="text-success">{{ $quotation->bestQuote()->getFormattedPremium() }}</strong>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @if ($quotation->quotationCompanies->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Rank</th>
                                            <th>Insurance Company</th>
                                            <th>Plan Name</th>
                                            <th>Basic OD</th>
                                            <th>TP Premium</th>
                                            <th>Add-on</th>
                                            <th>CNG/LPG</th>
                                            <th>Net Premium</th>
                                            <th>GST</th>
                                            <th>Final Premium</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($quotation->quotationCompanies->sortBy('ranking') as $company)
                                            <tr class="{{ $company->is_recommended ? 'table-success' : '' }}">
                                                <td>
                                                    @if ($company->is_recommended)
                                                        <span class="badge badge-success" data-toggle="tooltip"
                                                            title="{{ $company->recommendation_note ?? 'Recommended quote' }}">
                                                            <i class="fas fa-star"></i> {{ $company->ranking }}
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary">{{ $company->ranking }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $company->insuranceCompany->name }}</strong>
                                                    @if ($company->is_recommended)
                                                        <span class="badge badge-success ml-1">
                                                            <i class="fas fa-star"></i> Recommended
                                                        </span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">
                                                        @if ($company->quote_number)
                                                            Quote: {{ $company->quote_number }}
                                                        @else
                                                            Auto-generated
                                                        @endif
                                                        @if ($company->policy_type)
                                                            | {{ $company->policy_type }}
                                                        @endif
                                                        @if ($company->policy_tenure_years)
                                                            | {{ $company->policy_tenure_years }} Year(s)
                                                        @endif
                                                    </small>
                                                    @if ($company->is_recommended && $company->recommendation_note)
                                                        <br><small class="text-success font-weight-bold">
                                                            <i class="fas fa-quote-left"></i>
                                                            {{ $company->recommendation_note }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>{{ $company->plan_name ?? 'Standard Plan' }}</td>
                                                <td>{{ format_indian_currency($company->basic_od_premium) }}</td>
                                                <td>{{ format_indian_currency($company->tp_premium ?? 0) }}</td>
                                                <td>{{ format_indian_currency($company->total_addon_premium) }}</td>
                                                <td>{{ format_indian_currency($company->cng_lpg_premium ?? 0) }}</td>
                                                <td>{{ format_indian_currency($company->net_premium) }}</td>
                                                <td>{{ format_indian_currency($company->sgst_amount + $company->cgst_amount) }}
                                                </td>
                                                <td>
                                                    <strong
                                                        class="text-primary">{{ $company->getFormattedPremium() }}</strong>
                                                    @if ($company->roadside_assistance > 0)
                                                        <br><small class="text-muted">+RSA</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Add-on Coverage Breakdown -->
                            @php
                                $companiesWithAddons = $quotation->quotationCompanies->filter(function($company) {
                                    return ($company->addon_covers_breakdown && count($company->addon_covers_breakdown) > 0) || $company->total_addon_premium > 0;
                                });
                            @endphp
                            @if ($companiesWithAddons->count() > 0)
                                <div class="mt-4">
                                    <h6 class="font-weight-bold text-success">
                                        <i class="fas fa-plus-circle"></i> Add-on Coverage Breakdown
                                    </h6>
                                    <div class="row">
                                        @foreach ($companiesWithAddons as $company)
                                            <div class="col-md-12 mb-4">
                                                <div class="card border-left-success">
                                                    <div class="card-header bg-success text-white py-2">
                                                        <h6 class="m-0 font-weight-bold">
                                                            {{ $company->insuranceCompany->name }}
                                                            @if ($company->quote_number)
                                                                <small
                                                                    class="ml-2">({{ $company->quote_number }})</small>
                                                            @endif
                                                            <span class="float-right">Total:
                                                                {{ format_indian_currency($company->total_addon_premium) }}</span>
                                                        </h6>
                                                    </div>
                                                    <div class="card-body py-2">
                                                        @if ($company->addon_covers_breakdown)
                                                            <div class="row">
                                                                @php
                                                                    $addonCount = 0;
                                                                    // Show ALL addons in breakdown, not just those with prices
                                                                    $totalAddons = count($company->addon_covers_breakdown);
                                                                @endphp
                                                                @foreach ($company->addon_covers_breakdown as $addon => $data)
                                                                    @if (is_array($data))
                                                                        @php $addonCount++; @endphp
                                                                        <div class="col-md-4">
                                                                            <div class="mb-2">
                                                                                <div
                                                                                    class="d-flex justify-content-between">
                                                                                    <strong
                                                                                        class="small text-primary">{{ $addon }}:</strong>
                                                                                    <strong
                                                                                        class="small">
                                                                                        @if(isset($data['price']) && $data['price'] > 0)
                                                                                            {{ format_indian_currency($data['price']) }}
                                                                                        @else
                                                                                            <span class="badge badge-success">Covered</span>
                                                                                        @endif
                                                                                    </strong>
                                                                                </div>
                                                                                @if (!empty($data['note']))
                                                                                    <div class="text-muted"
                                                                                        style="font-size: 0.75rem;">
                                                                                        <em>{{ $data['note'] }}</em>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        @if ($addonCount % 3 == 0 && $addonCount < $totalAddons)
                                                            </div>
                                                            <div class="row">
                                                        @endif
                                                    @elseif(is_numeric($data))
                                                        @php $addonCount++; @endphp
                                                        <div class="col-md-4">
                                                            <div class="mb-2">
                                                                <div class="d-flex justify-content-between">
                                                                    <strong
                                                                        class="small text-primary">{{ $addon }}:</strong>
                                                                    <strong
                                                                        class="small">
                                                                        @if($data > 0)
                                                                            ₹{{ number_format($data) }}
                                                                        @else
                                                                            <span class="badge badge-success">Covered</span>
                                                                        @endif
                                                                    </strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if ($addonCount % 3 == 0 && $addonCount < $totalAddons)
                                                    </div>
                                                    <div class="row">
                                        @endif
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted">
                        <small>No addon breakdown details available</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    </div>
    @endif
@else
    <div class="text-center py-5">
        <i class="fas fa-building fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No Company Quotes Generated</h5>
        <p class="text-muted">Generate quotes from multiple insurance companies to compare premiums and coverage.</p>
        @can('quotation-generate')
            <form method="POST" action="{{ route('quotations.generate-quotes', $quotation) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-cog"></i> Generate Quotes Now
                </button>
            </form>
        @endcan
    </div>
    @endif
    </div>
    </div>
    </div>
    </div>
    </div>

    <!-- Send WhatsApp Modal -->
    <div class="modal fade" id="sendWhatsAppModal" tabindex="-1" role="dialog"
        aria-labelledby="sendWhatsAppModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="sendWhatsAppModalLabel">
                        <i class="fab fa-whatsapp"></i> Send Quotation via WhatsApp
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="hideWhatsAppModal('sendWhatsAppModal')" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fab fa-whatsapp fa-3x text-success"></i>
                    </div>
                    <p class="text-center">Send quotation with PDF attachment to:</p>
                    <div class="alert alert-info">
                        <strong>Customer:</strong> {{ $quotation->customer->name }}<br>
                        <strong>WhatsApp Number:</strong>
                        {{ $quotation->whatsapp_number ?? $quotation->customer->mobile_number }}
                    </div>
                    <p class="text-muted small">This will generate and attach a PDF comparison of all quotes.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideWhatsAppModal('sendWhatsAppModal')">Cancel</button>
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
    <div class="modal fade" id="resendWhatsAppModal" tabindex="-1" role="dialog"
        aria-labelledby="resendWhatsAppModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="resendWhatsAppModalLabel">
                        <i class="fab fa-whatsapp"></i> Resend Quotation via WhatsApp
                    </h5>
                    <button type="button" class="btn-close" onclick="hideWhatsAppModal('resendWhatsAppModal')" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fab fa-whatsapp fa-3x text-warning"></i>
                    </div>
                    <p class="text-center">Resend quotation with updated PDF attachment to:</p>
                    <div class="alert alert-warning">
                        <strong>Customer:</strong> {{ $quotation->customer->name }}<br>
                        <strong>WhatsApp Number:</strong>
                        {{ $quotation->whatsapp_number ?? $quotation->customer->mobile_number }}<br>
                        <strong>Last Sent:</strong>
                        {{ $quotation->sent_at ? formatDateForUi($quotation->sent_at, 'd M Y, H:i') : 'Not available' }}
                    </div>
                    <p class="text-muted small">This will generate a fresh PDF with current quotes and send it again.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideWhatsAppModal('resendWhatsAppModal')">Cancel</button>
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

    <!-- Delete Quotation Modal -->
    <div class="modal fade" id="deleteQuotationModal" tabindex="-1" role="dialog"
        aria-labelledby="deleteQuotationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteQuotationModalLabel">
                        <i class="fas fa-trash"></i> Delete Quotation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="hideDeleteModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                    </div>
                    <p class="text-center"><strong>Are you sure you want to delete this quotation?</strong></p>
                    <div class="alert alert-danger">
                        <strong>Quotation:</strong> {{ $quotation->getQuoteReference() }}<br>
                        <strong>Customer:</strong> {{ $quotation->customer->name }}<br>
                        <strong>Vehicle:</strong> {{ $quotation->make_model_variant }}
                        @if ($quotation->quotationCompanies->count() > 0)
                            <br><strong>Company Quotes:</strong> {{ $quotation->quotationCompanies->count() }} will also be
                            deleted
                        @endif
                    </div>
                    <p class="text-warning small"><strong>Warning:</strong> This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideDeleteModal()">Cancel</button>
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
            </div> <!-- End card-body -->
        </div> <!-- End wrapper card -->
    </div> <!-- End container-fluid -->
@endsection

@section('scripts')
    <style>
        /* Ensure proper layout and spacing */
        .container-fluid {
            padding-left: 15px;
            padding-right: 15px;
        }

        .card {
            margin-bottom: 1.5rem;
        }

        .table-responsive {
            border: none;
        }

        /* Better responsive behavior for buttons */
        @media (max-width: 768px) {
            .d-flex .btn {
                margin-bottom: 0.5rem;
            }

            .card-header .d-flex {
                flex-direction: column;
                align-items: flex-start;
            }

            .card-header .text-right {
                text-align: left !important;
                margin-top: 0.5rem;
            }
        }

        /* Enhanced styling for recommended quotes */
        .table-success {
            background-color: rgba(40, 167, 69, 0.1) !important;
        }

        .badge-success {
            background-color: #28a745;
        }
    </style>

    <script>
        $(document).ready(function() {
            // Initialize Bootstrap tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Add hover effects for recommended quotes
            $('.table-success').hover(
                function() {
                    $(this).addClass('bg-success text-white');
                },
                function() {
                    $(this).removeClass('bg-success text-white');
                }
            );

            // Ensure proper container behavior
            $('.container-fluid').css('max-width', '100%');

            // Add smooth transitions
            $('.card').css('transition', 'all 0.3s ease');
        });

        // Modal functions are now centralized in layouts/app.blade.php
    </script>
@endsection
