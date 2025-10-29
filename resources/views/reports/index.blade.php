@extends('layouts.app')

@push('scripts')
    <script>
        // Load Chart.js asynchronously to avoid blocking and module errors
        function loadChartJS() {
            if (typeof Chart !== 'undefined') {
                return; // Already loaded
            }

            const script = document.createElement('script');
            // Use Chart.js v3.9.1 which is compatible with regular script loading
            script.src = '{{ cdn_url('cdn_chartjs_url') }}';
            script.crossOrigin = 'anonymous';
            script.referrerPolicy = 'no-referrer';
            script.async = true;

            script.onerror = function() {
                console.log('Primary Chart.js CDN failed, trying backup...');
                const backupScript = document.createElement('script');
                backupScript.src = '{{ cdn_url('cdn_chartjs_url') }}';
                backupScript.async = true;
                backupScript.onerror = function() {
                    console.log('Chart.js failed to load from both CDNs, using fallback charts');
                };
                document.head.appendChild(backupScript);
            };

            document.head.appendChild(script);
        }

        // Load Chart.js when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', loadChartJS);
        } else {
            loadChartJS();
        }
    </script>
@endpush

@section('title', 'Reports Dashboard')

@section('content')
    <div class="container-fluid">
        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- Modern Reports Dashboard -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow border-0 rounded">
                    <div class="card-header bg-gradient-primary text-white py-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-chart-bar fa-lg me-2"></i>
                            <div>
                                <h5 class="mb-0 font-weight-bold">Reports Dashboard</h5>
                                <small class="opacity-75">Generate comprehensive insurance reports</small>
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('reports.index') }}" method="POST" id="reportForm" class="modern-form">
                        @csrf
                        <div class="card-body p-3">
                            <!-- Report Type Selection - Compact Design -->
                            <div class="row mb-3">
                                <div class="col-lg-8 col-md-10 mx-auto">
                                    <div class="report-selector-card">
                                        <label class="form-label fw-bold text-primary mb-2">
                                            <i class="fas fa-chart-line me-1"></i>Select Report Type <span class="text-danger">*</span>
                                        </label>
                                        <div class="custom-select-wrapper">
                                            <select class="custom-select form-select @error('report_name') is-invalid @enderror" 
                                                    id="reportName" name="report_name" required>
                                                <option value="">Choose a report to generate...</option>
                                                @foreach (config('constants.REPORTS') as $reportName => $reportDescription)
                                                    <option value="{{ $reportName }}"{{ request('report_name') === $reportName ? ' selected' : '' }}>
                                                        {{ $reportDescription }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <i class="fas fa-chevron-down select-arrow"></i>
                                        </div>
                                        @error('report_name')
                                            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Primary Date Filters (Always Visible) -->
                            <div class="primary-filters-container mb-3">
                                <div class="row mb-2">
                                    <!-- Issue Date Range (Required for insurance_detail, Optional for cross_selling) -->
                                    <div class="col-lg-6 col-md-6 mb-2 fields-to-toggle insurance_detail cross_selling" style="display: none;">
                                        <div class="card border-primary">
                                            <div class="card-header bg-primary text-white py-1">
                                                <small>
                                                    <i class="fas fa-calendar-alt me-1"></i>Issue Date Range 
                                                    <span class="date-requirement insurance_detail text-warning fw-bold">(Required)</span>
                                                    <span class="date-requirement cross_selling text-light">(Optional)</span>
                                                </small>
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <div class="form-floating">
                                                            <input type="text" class="form-control form-control-sm datepicker" 
                                                                   id="issue_start_date" name="issue_start_date" 
                                                                   value="{{ request('issue_start_date') }}" 
                                                                   placeholder="From Date" readonly>
                                                            <label for="issue_start_date"><small>From Date</small></label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-floating">
                                                            <input type="text" class="form-control form-control-sm datepicker" 
                                                                   id="issue_end_date" name="issue_end_date" 
                                                                   value="{{ request('issue_end_date') }}" 
                                                                   placeholder="To Date" readonly>
                                                            <label for="issue_end_date"><small>To Date</small></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Due Policy Period (Required for due_policy_detail) -->
                                    <div class="col-lg-6 col-md-6 mb-2 fields-to-toggle due_policy_detail" style="display: none;">
                                        <div class="card border-warning">
                                            <div class="card-header bg-warning text-dark py-1">
                                                <small>
                                                    <i class="fas fa-calendar-check me-1"></i>Due Policy Period 
                                                    <span class="text-danger fw-bold">(Required)</span>
                                                </small>
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <div class="form-floating">
                                                            <input type="text" class="form-control form-control-sm datepicker_month" 
                                                                   id="due_start_date" name="due_start_date" 
                                                                   value="{{ request('due_start_date') }}" 
                                                                   placeholder="From Month" readonly>
                                                            <label for="due_start_date"><small>From Month</small></label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-floating">
                                                            <input type="text" class="form-control form-control-sm datepicker_month" 
                                                                   id="due_end_date" name="due_end_date" 
                                                                   value="{{ request('due_end_date') }}" 
                                                                   placeholder="To Month" readonly>
                                                            <label for="due_end_date"><small>To Month</small></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Advanced Filters Section -->
                            <div class="advanced-filters-container mb-3">
                                <div class="filter-toggle-header text-center mb-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="toggleFilters">
                                        <i class="fas fa-filter me-1"></i>Advanced Filters <small>(Optional)</small>
                                        <i class="fas fa-chevron-down ms-1" id="filterChevron"></i>
                                    </button>
                                </div>
                                
                                <div class="filters-content" id="filtersContent" style="display: none;">
                                    <!-- Optional Date Range Filters Row -->
                                    <div class="row mb-2">
                                        <!-- Record Creation Date -->
                                        <div class="col-lg-4 col-md-6 mb-2">
                                            <div class="filter-card">
                                                <div class="filter-header">
                                                    <i class="fas fa-plus-circle me-1"></i><small>Record Creation Date</small>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <input type="text" class="form-control form-control-sm datepicker" 
                                                               id="record_creation_start_date" name="record_creation_start_date" 
                                                               value="{{ request('record_creation_start_date') }}" 
                                                               placeholder="From Date" readonly>
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="text" class="form-control form-control-sm datepicker" 
                                                               id="record_creation_end_date" name="record_creation_end_date" 
                                                               value="{{ request('record_creation_end_date') }}" 
                                                               placeholder="To Date" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Business Entity Filters Row -->
                                    <div class="row mb-2">
                                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                            <div class="filter-card">
                                                <div class="filter-header">
                                                    <i class="fas fa-user-tie me-1"></i><small>Broker</small>
                                                </div>
                                                <select class="form-select form-select-sm" name="broker_id">
                                                    <option value="">All Brokers</option>
                                                    @if(isset($brokers))
                                                        @foreach($brokers as $broker)
                                                            <option value="{{ $broker->id }}" {{ request('broker_id') == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                            <div class="filter-card">
                                                <div class="filter-header">
                                                    <i class="fas fa-user-friends me-1"></i><small>RM</small>
                                                </div>
                                                <select class="form-select form-select-sm" name="relationship_manager_id">
                                                    <option value="">All RMs</option>
                                                    @if(isset($relationship_managers))
                                                        @foreach($relationship_managers as $rm)
                                                            <option value="{{ $rm->id }}" {{ request('relationship_manager_id') == $rm->id ? 'selected' : '' }}>{{ $rm->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <div class="filter-card">
                                                <div class="filter-header">
                                                    <i class="fas fa-shield-alt me-1"></i><small>Insurance Company</small>
                                                </div>
                                                <select class="form-select form-select-sm" name="insurance_company_id">
                                                    <option value="">All Companies</option>
                                                    @if(isset($insurance_companies))
                                                        @foreach($insurance_companies as $company)
                                                            <option value="{{ $company->id }}" {{ request('insurance_company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <div class="filter-card">
                                                <div class="filter-header">
                                                    <i class="fas fa-users me-1"></i><small>Customer</small>
                                                </div>
                                                <select class="form-select form-select-sm" name="customer_id">
                                                    <option value="">All Customers</option>
                                                    @if(isset($customers))
                                                        @foreach($customers as $customer)
                                                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Policy & Premium Filters Row -->
                                    <div class="row mb-2">
                                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                            <div class="filter-card">
                                                <div class="filter-header">
                                                    <i class="fas fa-file-contract me-1"></i><small>Policy Type</small>
                                                </div>
                                                <select class="form-select form-select-sm" name="policy_type_id">
                                                    <option value="">All Policy Types</option>
                                                    @if(isset($policy_types))
                                                        @foreach($policy_types as $policyType)
                                                            <option value="{{ $policyType->id }}" {{ request('policy_type_id') == $policyType->id ? 'selected' : '' }}>{{ $policyType->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                            <div class="filter-card">
                                                <div class="filter-header">
                                                    <i class="fas fa-gas-pump me-1"></i><small>Fuel Type</small>
                                                </div>
                                                <select class="form-select form-select-sm" name="fuel_type_id">
                                                    <option value="">All Fuel Types</option>
                                                    @if(isset($fuel_types))
                                                        @foreach($fuel_types as $fuelType)
                                                            <option value="{{ $fuelType->id }}" {{ request('fuel_type_id') == $fuelType->id ? 'selected' : '' }}>{{ $fuelType->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                            <div class="filter-card">
                                                <div class="filter-header">
                                                    <i class="fas fa-money-bill-wave me-1"></i><small>Premium Type</small>
                                                </div>
                                                <select class="form-select form-select-sm" name="premium_type_id">
                                                    <option value="">All Premium Types</option>
                                                    @if(isset($premium_types))
                                                        @foreach($premium_types as $premiumType)
                                                            <option value="{{ $premiumType->id }}" {{ request('premium_type_id') == $premiumType->id ? 'selected' : '' }}>{{ $premiumType->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                            <div class="filter-card">
                                                <div class="filter-header">
                                                    <i class="fas fa-toggle-on me-1"></i><small>Status</small>
                                                </div>
                                                <select class="form-select form-select-sm" name="status">
                                                    <option value="">All Status</option>
                                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Not Renewed</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-4 col-md-8 mb-2">
                                            <div class="filter-card">
                                                <div class="filter-header">
                                                    <i class="fas fa-rupee-sign me-1"></i><small>Premium Amount Range</small>
                                                </div>
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <input type="number" class="form-control form-control-sm" 
                                                               name="premium_amount_min" 
                                                               value="{{ request('premium_amount_min') }}" 
                                                               placeholder="Min Amount" min="0" step="0.01">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="number" class="form-control form-control-sm" 
                                                               name="premium_amount_max" 
                                                               value="{{ request('premium_amount_max') }}" 
                                                               placeholder="Max Amount" min="0" step="0.01">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="card-footer bg-light p-2">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="resetForm()">
                                    <i class="fas fa-redo me-1"></i>Reset
                                </button>
                                <button type="submit" name="view" value="1" class="btn btn-primary btn-sm px-3" onclick="console.log('Button clicked with name:', this.name, 'value:', this.value);">
                                    <i class="fas fa-eye me-1"></i>View Report
                                </button>
                                <button type="button" class="btn btn-success btn-sm px-3" onclick="downloadReport(this)">
                                    <i class="fas fa-download me-1"></i>Download Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Report Results Section -->
        @if(isset($cross_selling_report) && !empty($cross_selling_report))
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>Cross Selling Report</h4>
                            <span class="badge bg-light text-dark">{{ count($cross_selling_report) }} Records</span>
                        </div>
                        <div class="card-body p-0">
                            <!-- Nav Tabs -->
                            <nav>
                                <div class="nav nav-tabs border-0 bg-light" id="cross-selling-tab" role="tablist">
                                    <button class="nav-link active px-4 py-3 border-0" id="cross-summary-tab" data-bs-toggle="tab" data-bs-target="#cross-summary" type="button" role="tab">
                                        <i class="fas fa-chart-pie me-2"></i>Summary
                                    </button>
                                    <button class="nav-link px-4 py-3 border-0" id="cross-details-tab" data-bs-toggle="tab" data-bs-target="#cross-details" type="button" role="tab">
                                        <i class="fas fa-table me-2"></i>Detailed Data
                                    </button>
                                </div>
                            </nav>
                            
                            <!-- Tab Content -->
                            <div class="tab-content" id="cross-selling-tabContent">
                                <!-- Summary Tab -->
                                <div class="tab-pane fade show active p-4" id="cross-summary" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <div class="card bg-light h-100">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary"><i class="fas fa-chart-bar me-2"></i>Top Premium Types</h6>
                                                    <canvas id="premiumTypeChart" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-4">
                                            <div class="card bg-light h-100">
                                                <div class="card-body">
                                                    <h6 class="card-title text-success"><i class="fas fa-money-bill-wave me-2"></i>Revenue Distribution</h6>
                                                    <canvas id="revenueChart" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-info"><i class="fas fa-chart-line me-2"></i>Key Metrics</h6>
                                                    <div class="row text-center" id="keyMetrics">
                                                        <!-- Metrics will be populated by JavaScript -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Details Tab -->
                                <div class="tab-pane fade p-4" id="cross-details" role="tabpanel">
                                    @if(isset($cross_selling_report) && !empty($cross_selling_report) && $cross_selling_report->isNotEmpty())
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover" id="dataTable">
                                                <thead class="table-dark">
                                                    <tr>
                                                        @foreach($cross_selling_report->first() as $key => $value)
                                                            <th>{{ $key }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($cross_selling_report as $row)
                                                        <tr>
                                                            @foreach($row as $cell)
                                                                <td>
                                                                    @if(is_array($cell))
                                                                        {{ json_encode($cell) }}
                                                                    @else
                                                                        {{ $cell }}
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info text-center">
                                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                                            <h5>No Cross-Selling Data Available</h5>
                                            <p>Please select a report type and click "View Report" to see detailed cross-selling analysis.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($insurance_reports) && !empty($insurance_reports) && is_array($insurance_reports) && (is_object($insurance_reports[0] ?? null) || (is_array($insurance_reports[0] ?? null) && isset($insurance_reports[0]['customer_name']))))
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-info text-white">
                            <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Insurance Detail Dashboard</h4>
                        </div>
                        <div class="card-body p-0">
                            <!-- Nav Tabs -->
                            <nav>
                                <div class="nav nav-tabs border-0 bg-light" id="insurance-tab" role="tablist">
                                    <button class="nav-link active px-4 py-3 border-0" id="insurance-summary-tab" data-bs-toggle="tab" data-bs-target="#insurance-summary" type="button" role="tab">
                                        <i class="fas fa-chart-bar me-2"></i>Summary Analysis
                                    </button>
                                    <button class="nav-link px-4 py-3 border-0" id="insurance-details-tab" data-bs-toggle="tab" data-bs-target="#insurance-details" type="button" role="tab">
                                        <i class="fas fa-table me-2"></i>Detailed Data
                                    </button>
                                </div>
                            </nav>

                            <!-- Tab Content -->
                            <div class="tab-content" id="insuranceTabContent">
                                <!-- Summary Tab -->
                                <div class="tab-pane fade show active p-4" id="insurance-summary" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-info"><i class="fas fa-chart-line me-2"></i>Insurance Portfolio Key Metrics</h6>
                                                    <div class="row text-center" id="insuranceMetrics">
                                                        <!-- Metrics will be populated by JavaScript -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="card border-success">
                                                <div class="card-body">
                                                    <h6 class="card-title text-success"><i class="fas fa-chart-pie me-2"></i>Policy Status Distribution</h6>
                                                    <canvas id="statusChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-primary">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary"><i class="fas fa-building me-2"></i>Top Insurance Companies</h6>
                                                    <canvas id="insuranceCompanyChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <div class="card border-warning">
                                                <div class="card-body">
                                                    <h6 class="card-title text-warning"><i class="fas fa-calendar-alt me-2"></i>Premium Timeline Analysis</h6>
                                                    <canvas id="timelineChart" width="800" height="300"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Details Tab -->
                                <div class="tab-pane fade p-4" id="insurance-details" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="insuranceTable">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Sr No</th>
                                                    <th>Customer Name</th>
                                                    <th>Policy Number</th>
                                                    <th>Insurance Company</th>
                                                    <th>Issue Date</th>
                                                    <th>Expiry Date</th>
                                                    <th>Premium Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($insurance_reports as $index => $report)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ is_object($report) ? ($report->customer_name ?? 'N/A') : ($report['customer_name'] ?? 'N/A') }}</td>
                                                        <td>{{ is_object($report) ? ($report->policy_number ?? 'N/A') : ($report['policy_number'] ?? 'N/A') }}</td>
                                                        <td>{{ is_object($report) ? ($report->insurance_company ?? 'N/A') : ($report['insurance_company'] ?? 'N/A') }}</td>
                                                        <td>{{ is_object($report) ? ($report->issue_date ?? 'N/A') : ($report['issue_date'] ?? 'N/A') }}</td>
                                                        <td>{{ is_object($report) ? ($report->expired_date ?? 'N/A') : ($report['expired_date'] ?? 'N/A') }}</td>
                                                        <td>{{ is_object($report) ? ($report->premium_amount ?? 'N/A') : ($report['premium_amount'] ?? 'N/A') }}</td>
                                                        <td>
                                                            @php
                                                                $status = is_object($report) ? ($report->status ?? 0) : ($report['status'] ?? 0);
                                                            @endphp
                                                            <span class="badge {{ $status == 1 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $status == 1 ? 'Active' : 'Not Renewed' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($due_policy_reports) && !empty($due_policy_reports) && is_array($due_policy_reports) && (is_object($due_policy_reports[0] ?? null) || (is_array($due_policy_reports[0] ?? null) && isset($due_policy_reports[0]['customer_name']))))
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-warning text-dark">
                            <h4 class="mb-0"><i class="fas fa-clock me-2"></i>Due Policy Dashboard</h4>
                        </div>
                        <div class="card-body p-0">
                            <!-- Nav Tabs -->
                            <nav>
                                <div class="nav nav-tabs border-0 bg-light" id="due-policy-tab" role="tablist">
                                    <button class="nav-link active px-4 py-3 border-0" id="due-summary-tab" data-bs-toggle="tab" data-bs-target="#due-summary" type="button" role="tab">
                                        <i class="fas fa-chart-pie me-2"></i>Summary Analysis
                                    </button>
                                    <button class="nav-link px-4 py-3 border-0" id="due-details-tab" data-bs-toggle="tab" data-bs-target="#due-details" type="button" role="tab">
                                        <i class="fas fa-table me-2"></i>Detailed Data
                                    </button>
                                </div>
                            </nav>

                            <!-- Tab Content -->
                            <div class="tab-content" id="duePolicyTabContent">
                                <!-- Summary Tab -->
                                <div class="tab-pane fade show active p-4" id="due-summary" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-title text-warning"><i class="fas fa-chart-line me-2"></i>Due Policy Key Metrics</h6>
                                                    <div class="row text-center" id="duePolicyMetrics">
                                                        <!-- Metrics will be populated by JavaScript -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <div class="card border-warning">
                                                <div class="card-body">
                                                    <h6 class="card-title text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Policy Urgency Analysis</h6>
                                                    <canvas id="urgencyChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-info">
                                                <div class="card-body">
                                                    <h6 class="card-title text-info"><i class="fas fa-building me-2"></i>Company-wise Due Policies</h6>
                                                    <canvas id="companyChart" width="400" height="200"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Details Tab -->
                                <div class="tab-pane fade p-4" id="due-details" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="duePolicyTable">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Sr No</th>
                                                    <th>Customer Name</th>
                                                    <th>Policy Number</th>
                                                    <th>Insurance Company</th>
                                                    <th>Issue Date</th>
                                                    <th>Expiry Date</th>
                                                    <th>Premium Amount</th>
                                                    <th>Days Remaining</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($due_policy_reports as $index => $report)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ is_object($report) ? ($report->customer_name ?? 'N/A') : ($report['customer_name'] ?? 'N/A') }}</td>
                                                        <td>{{ is_object($report) ? ($report->policy_number ?? 'N/A') : ($report['policy_number'] ?? 'N/A') }}</td>
                                                        <td>{{ is_object($report) ? ($report->insurance_company ?? 'N/A') : ($report['insurance_company'] ?? 'N/A') }}</td>
                                                        <td>{{ is_object($report) ? ($report->issue_date ?? 'N/A') : ($report['issue_date'] ?? 'N/A') }}</td>
                                                        <td>{{ is_object($report) ? ($report->expired_date ?? 'N/A') : ($report['expired_date'] ?? 'N/A') }}</td>
                                                        <td>{{ is_object($report) ? ($report->premium_amount ?? 'N/A') : ($report['premium_amount'] ?? 'N/A') }}</td>
                                                        <td>
                                                            @php
                                                                $expiredDate = is_object($report) ? ($report->expired_date ?? null) : ($report['expired_date'] ?? null);
                                                                if ($expiredDate) {
                                                                    $expiryDate = \Carbon\Carbon::parse($expiredDate);
                                                                    $today = \Carbon\Carbon::now();
                                                                    $daysRemaining = $today->diffInDays($expiryDate, false); // false means signed difference
                                                                    $isExpired = $expiryDate->isPast();
                                                                    $daysExpiredAgo = abs($daysRemaining);
                                                                } else {
                                                                    $daysRemaining = 0;
                                                                    $isExpired = false;
                                                                    $daysExpiredAgo = 0;
                                                                }
                                                                
                                                                // Color coding logic based on urgency and days expired
                                                                if ($isExpired) {
                                                                    // Expired policies - gradient red system based on how long expired
                                                                    if ($daysExpiredAgo <= 7) {
                                                                        $badgeClass = 'badge text-white';
                                                                        $bgStyle = 'background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); border: 2px solid #dc2626; box-shadow: 0 0 8px rgba(220, 38, 38, 0.4);'; // Bright dark red - critical
                                                                        $icon = 'fas fa-times-circle';
                                                                    } elseif ($daysExpiredAgo <= 30) {
                                                                        $badgeClass = 'badge text-white';
                                                                        $bgStyle = 'background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%); border: 1px solid #b91c1c;'; // Very dark red - urgent follow-up
                                                                        $icon = 'fas fa-exclamation-triangle';
                                                                    } elseif ($daysExpiredAgo <= 90) {
                                                                        $badgeClass = 'badge text-white';
                                                                        $bgStyle = 'background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: 1px solid #ef4444;'; // Medium red - important
                                                                        $icon = 'fas fa-clock';
                                                                    } else {
                                                                        $badgeClass = 'badge text-white';
                                                                        $bgStyle = 'background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); border: 1px solid #6b7280;'; // Gray - historical
                                                                        $icon = 'fas fa-archive';
                                                                    }
                                                                } else {
                                                                    // Active policies - color by renewal urgency
                                                                    if ($daysRemaining <= 3) {
                                                                        $badgeClass = 'badge text-white';
                                                                        $bgStyle = 'background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); border: 2px solid #dc2626; box-shadow: 0 0 12px rgba(220, 38, 38, 0.5); animation: pulse 2s infinite;'; // Pulsing red - immediate action
                                                                        $icon = 'fas fa-exclamation-circle';
                                                                    } elseif ($daysRemaining <= 7) {
                                                                        $badgeClass = 'badge bg-danger text-white';
                                                                        $bgStyle = 'box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);';
                                                                        $icon = 'fas fa-exclamation-triangle';
                                                                    } elseif ($daysRemaining <= 15) {
                                                                        $badgeClass = 'badge bg-warning text-dark';
                                                                        $bgStyle = 'font-weight: bold;';
                                                                        $icon = 'fas fa-clock';
                                                                    } elseif ($daysRemaining <= 30) {
                                                                        $badgeClass = 'badge bg-info text-white';
                                                                        $bgStyle = '';
                                                                        $icon = 'fas fa-calendar-alt';
                                                                    } else {
                                                                        $badgeClass = 'badge bg-success';
                                                                        $bgStyle = '';
                                                                        $icon = 'fas fa-check-circle';
                                                                    }
                                                                }
                                                            @endphp
                                                            @if($isExpired)
                                                                <span class="{{ $badgeClass }}" style="{{ $bgStyle }}">
                                                                    <i class="{{ $icon }} me-1"></i>EXPIRED {{ $daysExpiredAgo }} days ago
                                                                </span>
                                                            @else
                                                                <span class="{{ $badgeClass }}" style="{{ $bgStyle }}">
                                                                    <i class="{{ $icon }} me-1"></i>{{ $daysRemaining }} days remaining
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .modern-form {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .card {
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .form-floating > .form-control,
        .form-floating > .form-select {
            height: calc(2.5rem + 2px);
        }
        
        .form-floating > .form-control-sm {
            height: calc(2rem + 2px);
        }
        
        .btn-lg {
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            border-radius: 0.5rem;
        }
        
        .bg-gradient-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
        }
        
        .fields-to-toggle {
            transition: all 0.5s ease;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.75rem;
        }
        
        /* Compact Dropdown Styling */
        .report-selector-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .report-selector-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        
        .custom-select-wrapper {
            position: relative;
            background: white;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .custom-select-wrapper:hover {
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .custom-select {
            appearance: none;
            background: transparent;
            border: 2px solid transparent;
            padding: 0.75rem 2.5rem 0.75rem 1rem;
            font-size: 1rem;
            font-weight: 500;
            color: #2c3e50;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .custom-select:focus {
            outline: none;
            border-color: #007bff;
            background: #f8f9ff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        .custom-select option {
            padding: 0.75rem;
            font-weight: 500;
            background: white;
            color: #2c3e50;
        }
        
        .custom-select option:hover {
            background: #e3f2fd;
        }
        
        .select-arrow {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #007bff;
            font-size: 0.8rem;
            pointer-events: none;
            transition: all 0.3s ease;
        }
        
        .custom-select-wrapper:hover .select-arrow {
            color: #0056b3;
            transform: translateY(-50%) rotate(180deg);
        }
        
        .custom-select:focus + .select-arrow {
            transform: translateY(-50%) rotate(180deg);
        }
        
        /* Enhanced Date Range Cards */
        .fields-to-toggle .card {
            border: none;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .fields-to-toggle .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        
        .fields-to-toggle .card-header {
            border: none;
            font-weight: 600;
            font-size: 0.8rem;
            letter-spacing: 0.3px;
        }
        
        .form-floating > .form-control {
            border-radius: 6px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-floating > .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
        }
        
        /* Action Buttons Enhancement */
        .btn-sm {
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-sm::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-sm:hover::before {
            left: 100%;
        }
        
        .btn-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
        }
        
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
        }
        
        .btn-outline-secondary {
            background: transparent;
            border: 2px solid #6c757d;
            color: #6c757d;
        }
        
        .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
        }
        
        /* Primary Date Filters Container */
        .primary-filters-container {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #ffc107;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.1);
        }
        
        /* Advanced Filters Container */
        .advanced-filters-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #dee2e6;
        }
        
        .filter-toggle-header button {
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        }
        
        .filter-toggle-header button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
        }
        
        .filters-content {
            transition: all 0.5s ease;
            overflow: hidden;
        }
        
        /* Compact Filter Cards */
        .filter-card {
            background: white;
            border-radius: 6px;
            padding: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .filter-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-color: #007bff;
        }
        
        .filter-header {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .filter-card .form-select,
        .filter-card .form-control {
            border: 1px solid #ced4da;
            font-size: 0.85rem;
            padding: 0.25rem 0.5rem;
            transition: all 0.3s ease;
        }
        
        .filter-card .form-select:focus,
        .filter-card .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 1px rgba(0, 123, 255, 0.1);
        }
        
        /* Responsive adjustments for filters */
        @media (max-width: 768px) {
            .filter-card {
                margin-bottom: 0.5rem;
            }
            
            .advanced-filters-container {
                padding: 0.75rem;
            }
        }
        
        /* Animation for chevron */
        #filterChevron {
            transition: transform 0.3s ease;
        }
        
        #filterChevron.rotated {
            transform: rotate(180deg);
        }
        
        /* Pulse Animation for Critical Expiry */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 8px rgba(220, 38, 38, 0.4);
            }
            50% {
                box-shadow: 0 0 16px rgba(220, 38, 38, 0.7), 0 0 24px rgba(220, 38, 38, 0.4);
                transform: scale(1.02);
            }
            100% {
                box-shadow: 0 0 8px rgba(220, 38, 38, 0.4);
            }
        }
        
        /* Enhanced Badge Styles */
        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.025em;
            padding: 0.4rem 0.7rem;
            border-radius: 6px;
        }
        
        .badge i {
            font-size: 0.7rem;
        }
    </style>

    <script>
        // Simple download action without validation
        function downloadReport(button) {
            // Show loading state
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Downloading...';
            button.disabled = true;
            
            // Build query string from form data
            const form = document.getElementById('reportForm');
            const formData = new FormData(form);
            const params = new URLSearchParams();
            
            for (let [key, value] of formData.entries()) {
                if (value && key !== '_token' && key !== '_method' && key !== 'view') {
                    params.append(key, value);
                }
            }
            
            // Create download URL
            const downloadUrl = "{{ route('reports.export') }}?" + params.toString();
            
            // Trigger download
            window.location.href = downloadUrl;
            
            // Reset button state
            setTimeout(() => {
                button.innerHTML = originalHTML;
                button.disabled = false;
            }, 2000);
        }

        // Advanced Filters Toggle
        document.getElementById('toggleFilters').addEventListener('click', function() {
            const filtersContent = document.getElementById('filtersContent');
            const chevron = document.getElementById('filterChevron');
            
            if (filtersContent.style.display === 'none') {
                filtersContent.style.display = 'block';
                chevron.classList.add('rotated');
                this.innerHTML = '<i class="fas fa-filter me-1"></i>Hide Filters <small>(Optional)</small> <i class="fas fa-chevron-up ms-1" id="filterChevron"></i>';
            } else {
                filtersContent.style.display = 'none';
                chevron.classList.remove('rotated');
                this.innerHTML = '<i class="fas fa-filter me-1"></i>Advanced Filters <small>(Optional)</small> <i class="fas fa-chevron-down ms-1" id="filterChevron"></i>';
            }
        });

        // Toggle fields based on report type
        document.getElementById('reportName').addEventListener('change', function() {
            const selectedReport = this.value;
            const fieldsToToggle = document.querySelectorAll('.fields-to-toggle');
            
            // Hide all conditional fields first
            fieldsToToggle.forEach(field => {
                field.style.display = 'none';
            });
            
            // Show relevant primary date filters immediately (always visible when report is selected)
            if (selectedReport) {
                const primaryFields = document.querySelectorAll('.primary-filters-container .fields-to-toggle.' + selectedReport);
                primaryFields.forEach(field => {
                    field.style.display = 'block';
                });
                
                // Show/hide appropriate requirement indicators
                const dateRequirements = document.querySelectorAll('.date-requirement');
                dateRequirements.forEach(req => {
                    req.style.display = 'none';
                });
                
                if (selectedReport === 'insurance_detail') {
                    const reqElements = document.querySelectorAll('.date-requirement.insurance_detail');
                    reqElements.forEach(req => req.style.display = 'inline');
                } else if (selectedReport === 'cross_selling') {
                    const reqElements = document.querySelectorAll('.date-requirement.cross_selling');
                    reqElements.forEach(req => req.style.display = 'inline');
                }
                
                // Show relevant advanced filter fields if filters are open
                if (document.getElementById('filtersContent').style.display === 'block') {
                    const advancedFields = document.querySelectorAll('.filters-content .fields-to-toggle.' + selectedReport);
                    advancedFields.forEach(field => {
                        field.style.display = 'block';
                    });
                }
            }
        });

        // Reset form function
        function resetForm() {
            document.getElementById('reportForm').reset();
            
            // Hide filters
            const filtersContent = document.getElementById('filtersContent');
            const chevron = document.getElementById('filterChevron');
            const toggleButton = document.getElementById('toggleFilters');
            
            filtersContent.style.display = 'none';
            chevron.classList.remove('rotated');
            toggleButton.innerHTML = '<i class="fas fa-filter me-1"></i>Advanced Filters <small>(Optional)</small> <i class="fas fa-chevron-down ms-1" id="filterChevron"></i>';
            
            // Hide all conditional fields
            const fieldsToToggle = document.querySelectorAll('.fields-to-toggle');
            fieldsToToggle.forEach(field => {
                field.style.display = 'none';
            });
        }


        // Initialize form functionality when document is ready
        $(document).ready(function() {
            console.log('Reports dashboard initialized successfully');
            
            // Restore filter visibility state on page load
            restoreFilterVisibility();
            
            // Check if advanced filters should be open
            checkAdvancedFilterState();
            
            // Initialize charts if cross selling data exists
            @if(isset($cross_selling_report) && !empty($cross_selling_report))
                initializeCrossSellingCharts();
            @endif
            
            // Initialize charts if due policy data exists
            @if(isset($due_policy_reports) && !empty($due_policy_reports))
                initializeDuePolicyCharts();
            @endif
            
            // Initialize charts if insurance data exists
            @if(isset($insurance_reports) && !empty($insurance_reports))
                initializeInsuranceCharts();
            @endif
        });

        // Function to restore filter visibility based on selected report
        function restoreFilterVisibility() {
            const selectedReport = document.getElementById('reportName').value;
            
            if (selectedReport) {
                console.log('Restoring filter visibility for report:', selectedReport);
                
                // Trigger change event to show appropriate filters
                const event = new Event('change');
                document.getElementById('reportName').dispatchEvent(event);
            }
        }

        // Function to check if advanced filters should be open
        function checkAdvancedFilterState() {
            const formData = new FormData(document.getElementById('reportForm'));
            let hasAppliedFilters = false;
            
            // Check for applied filters (excluding report name and tokens)
            for (let [key, value] of formData.entries()) {
                if (value && key !== 'report_name' && key !== '_token' && key !== '_method' && key !== 'view') {
                    hasAppliedFilters = true;
                    break;
                }
            }
            
            if (hasAppliedFilters) {
                console.log('Applied filters detected, opening advanced filters');
                const filtersContent = document.getElementById('filtersContent');
                const chevron = document.getElementById('filterChevron');
                const toggleButton = document.getElementById('toggleFilters');
                
                if (filtersContent && filtersContent.style.display !== 'block') {
                    filtersContent.style.display = 'block';
                    if (chevron) chevron.classList.add('rotated');
                    if (toggleButton) toggleButton.innerHTML = '<i class="fas fa-filter me-1"></i>Hide Filters <small>(Optional)</small> <i class="fas fa-chevron-up ms-1" id="filterChevron"></i>';
                }
            }
        }
        
        // Chart initialization function
        function initializeCrossSellingCharts() {
            const reportData = @json($cross_selling_report ?? []);
            const premiumTypes = @json($premiumTypes ?? []);
            
            console.log('Chart Data Debug:', {
                reportDataLength: reportData ? reportData.length : 0,
                premiumTypesLength: premiumTypes ? premiumTypes.length : 0,
                sampleReportData: reportData ? reportData.slice(0, 2) : null,
                premiumTypes: premiumTypes
            });
            
            if (!reportData || reportData.length === 0) {
                console.log('No report data available for charts');
                document.getElementById('keyMetrics').innerHTML = '<div class="col-12"><div class="alert alert-info">No data available for charts. Try adjusting your filters.</div></div>';
                return;
            }
            
            // Calculate summary data with detailed financial metrics
            const totalCustomers = reportData.length;
            let totalPremium = 0;
            let totalEarnings = 0;
            let totalCommission = 0;
            let totalCommissionGiven = 0;
            const premiumTypeCounts = {};
            
            reportData.forEach(customer => {
                // Parse premium and earnings (remove currency formatting)
                const premiumStr = customer['Total Premium (Last Year)'] || '0';
                const earningsStr = customer['Actual Earnings (Last Year)'] || '0';
                
                const premium = parseFloat(premiumStr.toString().replace(/[₹,]/g, '')) || 0;
                const earnings = parseFloat(earningsStr.toString().replace(/[₹,]/g, '')) || 0;
                
                totalPremium += premium;
                totalEarnings += earnings;
                
                // Calculate detailed financial metrics
                // My Commission = typically 10-15% of premium (using 12% as average)
                totalCommission += premium * 0.12;
                // Commission Given = typically 3-5% of premium (using 4% as average) 
                totalCommissionGiven += premium * 0.04;
                
                // Count premium types for cross-selling analysis
                premiumTypes.forEach(type => {
                    if (customer[type.name] === 'Yes') {
                        premiumTypeCounts[type.name] = (premiumTypeCounts[type.name] || 0) + 1;
                    }
                });
            });
            
            // My Earning = My Commission - Commission Given
            const myEarning = totalCommission - totalCommissionGiven;
            
            // Key Metrics
            const metricsElement = document.getElementById('keyMetrics');
            if (metricsElement) {
                metricsElement.innerHTML = `
                    <div class="col-md-3 mb-3">
                        <div class="metric-card p-3 bg-white rounded border shadow-sm">
                            <h5 class="text-primary mb-1">₹${totalPremium.toLocaleString('en-IN', {maximumFractionDigits: 0})}</h5>
                            <small class="text-muted"><i class="fas fa-file-contract me-1"></i>Final Premium</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-card p-3 bg-white rounded border shadow-sm">
                            <h5 class="text-success mb-1">₹${totalCommission.toLocaleString('en-IN', {maximumFractionDigits: 0})}</h5>
                            <small class="text-muted"><i class="fas fa-hand-holding-usd me-1"></i>My Commission</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-card p-3 bg-white rounded border shadow-sm">
                            <h5 class="text-warning mb-1">₹${totalCommissionGiven.toLocaleString('en-IN', {maximumFractionDigits: 0})}</h5>
                            <small class="text-muted"><i class="fas fa-share me-1"></i>Commission Given</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-card p-3 bg-white rounded border shadow-sm">
                            <h5 class="text-info mb-1">₹${myEarning.toLocaleString('en-IN', {maximumFractionDigits: 0})}</h5>
                            <small class="text-muted"><i class="fas fa-coins me-1"></i>My Earning</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="metric-card p-3 bg-light rounded border">
                            <h6 class="text-dark mb-2"><i class="fas fa-users me-1"></i>Business Summary</h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 class="text-primary mb-0">${totalCustomers}</h4>
                                    <small class="text-muted">Total Customers</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-secondary mb-0">${Object.keys(premiumTypeCounts).length}</h4>
                                    <small class="text-muted">Premium Types</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="metric-card p-3 bg-gradient-primary text-white rounded border">
                            <h6 class="mb-2"><i class="fas fa-percentage me-1"></i>Commission Rate Analysis</h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="mb-0">${((totalCommission/totalPremium)*100).toFixed(1)}%</h5>
                                    <small>My Commission Rate</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="mb-0">${((myEarning/totalPremium)*100).toFixed(1)}%</h5>
                                    <small>Net Earning Rate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Check if Chart.js is available
            console.log('Chart.js available:', typeof Chart !== 'undefined');
            
            if (typeof Chart === 'undefined') {
                console.error('Chart.js failed to load, creating fallback charts...');
                
                // Enhanced Cross-Selling Analysis Chart
                const premiumCanvas = document.getElementById('premiumTypeChart');
                if (premiumCanvas) {
                    const topTypes = Object.entries(premiumTypeCounts)
                        .sort((a, b) => b[1] - a[1])
                        .slice(0, 10);
                    
                    console.log('Creating cross-selling analysis chart with types:', topTypes);
                    
                    // Find max selling product
                    const maxSellingProduct = topTypes.length > 0 ? topTypes[0] : null;
                    
                    let fallbackChart = `
                        <div class="fallback-chart">
                            <h6>Cross-Selling Product Analysis (Text Chart)</h6>
                            ${maxSellingProduct ? `
                                <div class="alert alert-success mb-3">
                                    <i class="fas fa-trophy me-2"></i>
                                    <strong>Top Selling Product:</strong> ${maxSellingProduct[0]} 
                                    <span class="badge bg-success ms-2">${maxSellingProduct[1]} customers (${Math.round((maxSellingProduct[1]/totalCustomers)*100)}%)</span>
                                </div>
                            ` : ''}
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Analysis shows how many customers are using each product type. Higher percentages indicate better market penetration.
                                </small>
                            </div>
                    `;
                    
                    topTypes.forEach(([type, count], index) => {
                        const percentage = Math.round((count / totalCustomers) * 100);
                        const isTopProduct = index === 0;
                        const barColor = isTopProduct ? 'bg-warning' : 'bg-primary';
                        const textColor = isTopProduct ? 'text-warning' : 'text-primary';
                        
                        fallbackChart += `
                            <div class="chart-bar mb-3 ${isTopProduct ? 'border border-warning rounded p-2' : ''}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="${textColor}">
                                        ${isTopProduct ? '<i class="fas fa-star me-1"></i>' : '<i class="fas fa-box me-1"></i>'}
                                        <strong>${type}</strong>
                                        ${isTopProduct ? '<small class="ms-2 badge badge-warning">Best Seller</small>' : ''}
                                    </span>
                                    <span class="badge ${isTopProduct ? 'bg-warning' : 'bg-primary'}">${count} customers</span>
                                </div>
                                <div class="progress mt-2" style="height: 12px;">
                                    <div class="progress-bar ${barColor}" style="width: ${percentage}%" title="${percentage}% market penetration"></div>
                                </div>
                                <small class="text-muted">
                                    ${percentage}% market penetration
                                    ${count > (totalCustomers * 0.5) ? ' <i class="fas fa-thumbs-up text-success ms-1"></i> High adoption' : ''}
                                    ${count < (totalCustomers * 0.2) ? ' <i class="fas fa-exclamation-triangle text-warning ms-1"></i> Growth opportunity' : ''}
                                </small>
                            </div>
                        `;
                    });
                    
                    fallbackChart += `
                            <div class="mt-3 p-2 bg-light rounded">
                                <h6 class="mb-2"><i class="fas fa-chart-line me-1"></i>Cross-Selling Insights</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Products Offered: <strong>${topTypes.length}</strong></small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Avg. Penetration: <strong>${Math.round(topTypes.reduce((sum, [, count]) => sum + ((count/totalCustomers)*100), 0)/topTypes.length)}%</strong></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    premiumCanvas.parentElement.innerHTML = fallbackChart;
                    console.log('Cross-selling analysis chart created successfully');
                } else {
                    console.error('Premium canvas element not found');
                }
                
                // Enhanced Revenue Fallback Chart with Detailed Financial Breakdown
                const revenueCanvas = document.getElementById('revenueChart');
                if (revenueCanvas) {
                    const totalFinancialValue = totalPremium + totalCommission + totalCommissionGiven + myEarning;
                    const premiumPercentage = totalFinancialValue > 0 ? Math.round((totalPremium / totalFinancialValue) * 100) : 0;
                    const commissionPercentage = totalFinancialValue > 0 ? Math.round((totalCommission / totalFinancialValue) * 100) : 0;
                    const commissionGivenPercentage = totalFinancialValue > 0 ? Math.round((totalCommissionGiven / totalFinancialValue) * 100) : 0;
                    const myEarningPercentage = totalFinancialValue > 0 ? Math.round((myEarning / totalFinancialValue) * 100) : 0;
                    
                    console.log('Creating enhanced revenue fallback chart with data:', {
                        totalPremium,
                        totalCommission,
                        totalCommissionGiven,
                        myEarning,
                        totalFinancialValue
                    });
                    
                    let fallbackRevenue = `
                        <div class="fallback-chart">
                            <h6>Revenue Distribution (Text Chart)</h6>
                            <div class="revenue-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-file-contract text-primary me-2"></i><strong>Final Premium</strong></span>
                                    <span class="badge bg-primary">₹${totalPremium.toLocaleString('en-IN')}</span>
                                </div>
                                <div class="progress mt-2" style="height: 12px;">
                                    <div class="progress-bar bg-primary" style="width: ${premiumPercentage}%" title="${premiumPercentage}%"></div>
                                </div>
                            </div>
                            <div class="revenue-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-hand-holding-usd text-success me-2"></i><strong>My Commission</strong></span>
                                    <span class="badge bg-success">₹${totalCommission.toLocaleString('en-IN')}</span>
                                </div>
                                <div class="progress mt-2" style="height: 12px;">
                                    <div class="progress-bar bg-success" style="width: ${commissionPercentage}%" title="${commissionPercentage}%"></div>
                                </div>
                            </div>
                            <div class="revenue-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-share text-warning me-2"></i><strong>Commission Given</strong></span>
                                    <span class="badge bg-warning">₹${totalCommissionGiven.toLocaleString('en-IN')}</span>
                                </div>
                                <div class="progress mt-2" style="height: 12px;">
                                    <div class="progress-bar bg-warning" style="width: ${commissionGivenPercentage}%" title="${commissionGivenPercentage}%"></div>
                                </div>
                            </div>
                            <div class="revenue-item mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-coins text-info me-2"></i><strong>My Earning</strong></span>
                                    <span class="badge bg-info">₹${myEarning.toLocaleString('en-IN')}</span>
                                </div>
                                <div class="progress mt-2" style="height: 12px;">
                                    <div class="progress-bar bg-info" style="width: ${myEarningPercentage}%" title="${myEarningPercentage}%"></div>
                                </div>
                            </div>
                            <div class="mt-3 p-2 bg-light rounded">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Commission Rate: ${((totalCommission/totalPremium)*100).toFixed(1)}% | 
                                    Net Earning Rate: ${((myEarning/totalPremium)*100).toFixed(1)}%
                                </small>
                            </div>
                        </div>
                    `;
                    revenueCanvas.parentElement.innerHTML = fallbackRevenue;
                    console.log('Enhanced revenue fallback chart created successfully');
                } else {
                    console.error('Revenue canvas element not found');
                }
                
                return; // Skip Chart.js code
            }
            
            // Premium Type Distribution Chart (Chart.js)
            const premiumCanvas = document.getElementById('premiumTypeChart');
            console.log('Premium canvas found:', !!premiumCanvas);
            console.log('Premium type counts:', premiumTypeCounts);
            
            if (premiumCanvas) {
                const premiumCtx = premiumCanvas.getContext('2d');
                const topPremiumTypes = Object.entries(premiumTypeCounts)
                    .sort((a, b) => b[1] - a[1])
                    .slice(0, 10);
                
                console.log('Top premium types for chart:', topPremiumTypes);
                
                new Chart(premiumCtx, {
                    type: 'bar',
                    data: {
                        labels: topPremiumTypes.map(item => item[0]),
                        datasets: [{
                            label: 'Customer Count',
                            data: topPremiumTypes.map(item => item[1]),
                            backgroundColor: '#2B7EC8',
                            borderColor: '#1f5f98',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
            
            // Revenue Chart (Chart.js)
            const revenueCanvas = document.getElementById('revenueChart');
            if (revenueCanvas) {
                console.log('Creating Chart.js revenue chart with data:', { totalPremium, totalEarnings });
                const revenueCtx = revenueCanvas.getContext('2d');
                new Chart(revenueCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Total Premium', 'Total Earnings'],
                        datasets: [{
                            data: [totalPremium, totalEarnings],
                            backgroundColor: ['{{ chart_color("primary") }}', '{{ chart_color("success") }}'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }
        }

        // Due Policy Chart initialization function
        function initializeDuePolicyCharts() {
            // Data comes pre-sorted from backend: expired first, then by due date ascending
            const reportData = @json($due_policy_reports ?? []);
            
            console.log('Due Policy Chart Data Debug:', {
                reportDataLength: reportData ? reportData.length : 0,
                sampleReportData: reportData ? reportData.slice(0, 2) : null,
                sortingNote: 'Data is pre-sorted: expired policies first, then by due date ascending'
            });
            
            if (!reportData || reportData.length === 0) {
                console.log('No due policy data available for charts');
                document.getElementById('duePolicyMetrics').innerHTML = '<div class="col-12"><div class="alert alert-info">No data available for charts. Try adjusting your filters.</div></div>';
                return;
            }
            
            // Calculate due policy metrics
            let totalPolicies = reportData.length;
            let totalPremium = 0;
            let expiredCount = 0;  // Already expired
            let criticalCount = 0; // <= 7 days
            let urgentCount = 0;   // 8-30 days
            let normalCount = 0;   // > 30 days
            const companyBreakdown = {};
            
            reportData.forEach(policy => {
                // Parse premium amount
                const premiumStr = policy.premium_amount || '0';
                const premium = parseFloat(premiumStr.toString().replace(/[₹,]/g, '')) || 0;
                totalPremium += premium;
                
                // Calculate days remaining
                const expiredDate = policy.expired_date;
                let daysRemaining = 0;
                let isExpired = false;
                if (expiredDate) {
                    const expiry = new Date(expiredDate);
                    const today = new Date();
                    daysRemaining = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
                    isExpired = expiry < today;
                }
                
                // Categorize by urgency
                if (isExpired) {
                    expiredCount++;
                } else if (daysRemaining <= 7) {
                    criticalCount++;
                } else if (daysRemaining <= 30) {
                    urgentCount++;
                } else {
                    normalCount++;
                }
                
                // Company breakdown
                const company = policy.insurance_company || 'Unknown';
                companyBreakdown[company] = (companyBreakdown[company] || 0) + 1;
            });
            
            // Due Policy Key Metrics
            const metricsElement = document.getElementById('duePolicyMetrics');
            if (metricsElement) {
                metricsElement.innerHTML = `
                    <div class="col-md-3 mb-3">
                        <div class="metric-card p-3 bg-white rounded border shadow-sm">
                            <h5 class="text-warning mb-1">${totalPolicies}</h5>
                            <small class="text-muted"><i class="fas fa-file-contract me-1"></i>Total Due Policies</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-card p-3 bg-white rounded border shadow-sm">
                            <h5 class="text-success mb-1">₹${totalPremium.toLocaleString('en-IN', {maximumFractionDigits: 0})}</h5>
                            <small class="text-muted"><i class="fas fa-money-bill-wave me-1"></i>Total Premium Value</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-card p-3 bg-white rounded border shadow-sm">
                            <h5 class="text-dark mb-1">${expiredCount}</h5>
                            <small class="text-muted"><i class="fas fa-exclamation-triangle me-1"></i>Already Expired</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-card p-3 bg-white rounded border shadow-sm">
                            <h5 class="text-danger mb-1">${criticalCount}</h5>
                            <small class="text-muted"><i class="fas fa-exclamation-circle me-1"></i>Critical (≤7 days)</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="metric-card p-3 bg-light rounded border">
                            <h6 class="text-dark mb-2"><i class="fas fa-chart-pie me-1"></i>Policy Status Distribution</h6>
                            <div class="row text-center">
                                <div class="col-3">
                                    <h5 class="text-dark mb-0">${Math.round((expiredCount/totalPolicies)*100)}%</h5>
                                    <small class="text-muted">Expired</small>
                                </div>
                                <div class="col-3">
                                    <h5 class="text-danger mb-0">${Math.round((criticalCount/totalPolicies)*100)}%</h5>
                                    <small class="text-muted">Critical</small>
                                </div>
                                <div class="col-3">
                                    <h5 class="text-warning mb-0">${Math.round((urgentCount/totalPolicies)*100)}%</h5>
                                    <small class="text-muted">Urgent</small>
                                </div>
                                <div class="col-3">
                                    <h5 class="text-success mb-0">${Math.round((normalCount/totalPolicies)*100)}%</h5>
                                    <small class="text-muted">Normal</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="metric-card p-3 bg-gradient-warning text-dark rounded border">
                            <h6 class="mb-2"><i class="fas fa-building me-1"></i>Company Analysis</h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="mb-0">${Object.keys(companyBreakdown).length}</h5>
                                    <small>Insurance Companies</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="mb-0">₹${Math.round(totalPremium/totalPolicies).toLocaleString('en-IN')}</h5>
                                    <small>Avg. Premium</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Check if Chart.js is available
            console.log('Chart.js available for Due Policy:', typeof Chart !== 'undefined');
            
            if (typeof Chart === 'undefined') {
                console.error('Chart.js failed to load, creating fallback charts for Due Policy...');
                
                // Urgency Analysis Fallback Chart
                const urgencyCanvas = document.getElementById('urgencyChart');
                if (urgencyCanvas) {
                    const urgencyData = [
                        ['Critical (≤7 days)', criticalCount, 'danger'],
                        ['Urgent (8-30 days)', urgentCount, 'warning'],
                        ['Normal (>30 days)', normalCount, 'success']
                    ];
                    
                    let fallbackUrgency = `
                        <div class="fallback-chart">
                            <h6>Policy Urgency Analysis (Text Chart)</h6>
                            <div class="alert alert-${criticalCount > 0 ? 'danger' : (urgentCount > 0 ? 'warning' : 'success')} mb-3">
                                <i class="fas fa-${criticalCount > 0 ? 'exclamation-triangle' : (urgentCount > 0 ? 'clock' : 'check-circle')} me-2"></i>
                                <strong>Priority Alert:</strong> 
                                ${criticalCount} critical policies need immediate attention!
                            </div>
                    `;
                    
                    urgencyData.forEach(([category, count, color]) => {
                        const percentage = Math.round((count / totalPolicies) * 100);
                        fallbackUrgency += `
                            <div class="chart-bar mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-${color}">
                                        <i class="fas fa-${color === 'danger' ? 'exclamation-triangle' : (color === 'warning' ? 'clock' : 'check-circle')} me-1"></i>
                                        <strong>${category}</strong>
                                    </span>
                                    <span class="badge bg-${color}">${count} policies</span>
                                </div>
                                <div class="progress mt-2" style="height: 12px;">
                                    <div class="progress-bar bg-${color}" style="width: ${percentage}%" title="${percentage}%"></div>
                                </div>
                                <small class="text-muted">${percentage}% of total policies</small>
                            </div>
                        `;
                    });
                    
                    fallbackUrgency += '</div>';
                    urgencyCanvas.parentElement.innerHTML = fallbackUrgency;
                }
                
                // Company Breakdown Fallback Chart
                const companyCanvas = document.getElementById('companyChart');
                if (companyCanvas) {
                    const topCompanies = Object.entries(companyBreakdown)
                        .sort((a, b) => b[1] - a[1])
                        .slice(0, 5);
                    
                    let fallbackCompany = `
                        <div class="fallback-chart">
                            <h6>Company-wise Due Policies (Text Chart)</h6>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Top insurance companies with policies due for renewal
                                </small>
                            </div>
                    `;
                    
                    topCompanies.forEach(([company, count], index) => {
                        const percentage = Math.round((count / totalPolicies) * 100);
                        const isTop = index === 0;
                        
                        fallbackCompany += `
                            <div class="chart-bar mb-3 ${isTop ? 'border border-info rounded p-2' : ''}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="${isTop ? 'text-info' : 'text-primary'}">
                                        ${isTop ? '<i class="fas fa-crown me-1"></i>' : '<i class="fas fa-building me-1"></i>'}
                                        <strong>${company}</strong>
                                        ${isTop ? '<small class="ms-2 badge bg-info">Most Policies</small>' : ''}
                                    </span>
                                    <span class="badge ${isTop ? 'bg-info' : 'bg-primary'}">${count} policies</span>
                                </div>
                                <div class="progress mt-2" style="height: 12px;">
                                    <div class="progress-bar ${isTop ? 'bg-info' : 'bg-primary'}" style="width: ${percentage}%" title="${percentage}%"></div>
                                </div>
                                <small class="text-muted">${percentage}% of due policies</small>
                            </div>
                        `;
                    });
                    
                    fallbackCompany += '</div>';
                    companyCanvas.parentElement.innerHTML = fallbackCompany;
                }
                
                return; // Skip Chart.js code
            }
            
            // Urgency Analysis Chart (Chart.js)
            const urgencyCanvas = document.getElementById('urgencyChart');
            if (urgencyCanvas) {
                const urgencyCtx = urgencyCanvas.getContext('2d');
                new Chart(urgencyCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Critical (≤7 days)', 'Urgent (8-30 days)', 'Normal (>30 days)'],
                        datasets: [{
                            data: [criticalCount, urgentCount, normalCount],
                            backgroundColor: ['{{ chart_color("danger") }}', '{{ chart_color("warning") }}', '{{ chart_color("success") }}'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }
            
            // Company Chart (Chart.js)
            const companyCanvas = document.getElementById('companyChart');
            if (companyCanvas) {
                const companyCtx = companyCanvas.getContext('2d');
                const topCompanies = Object.entries(companyBreakdown)
                    .sort((a, b) => b[1] - a[1])
                    .slice(0, 5);
                
                new Chart(companyCtx, {
                    type: 'bar',
                    data: {
                        labels: topCompanies.map(item => item[0]),
                        datasets: [{
                            label: 'Due Policies',
                            data: topCompanies.map(item => item[1]),
                            backgroundColor: '{{ chart_color("info") }}',
                            borderColor: '{{ preg_replace("/0\.\d+\)/", "1)", chart_color("info")) }}',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            }
        }

        // Insurance Chart initialization function
        function initializeInsuranceCharts() {
            const reportData = @json($insurance_reports ?? []);
            
            console.log('Insurance Chart Data Debug:', {
                reportDataLength: reportData ? reportData.length : 0,
                sampleReportData: reportData ? reportData.slice(0, 2) : null
            });
            
            if (!reportData || reportData.length === 0) {
                console.log('No insurance data available for charts');
                document.getElementById('insuranceMetrics').innerHTML = '<div class="col-12"><div class="alert alert-info">No data available for charts. Try adjusting your filters.</div></div>';
                return;
            }
            
            // Calculate insurance metrics
            let totalPolicies = reportData.length;
            let totalPremium = 0;
            let activeCount = 0;
            let notRenewedCount = 0;
            const companyBreakdown = {};
            const monthlyData = {};
            let totalCommission = 0;
            let totalEarnings = 0;
            
            reportData.forEach(policy => {
                // Parse premium amount
                const premiumStr = policy.premium_amount || '0';
                const premium = parseFloat(premiumStr.toString().replace(/[₹,]/g, '')) || 0;
                totalPremium += premium;
                
                // Calculate commission and earnings (same rates as cross-selling)
                totalCommission += premium * 0.12; // 12% commission
                const commissionGiven = premium * 0.04; // 4% commission given
                totalEarnings += (premium * 0.12) - commissionGiven; // Net earnings
                
                // Status breakdown
                const status = policy.status || 0;
                if (status == 1) {
                    activeCount++;
                } else {
                    notRenewedCount++;
                }
                
                // Company breakdown
                const company = policy.insurance_company || 'Unknown';
                companyBreakdown[company] = (companyBreakdown[company] || 0) + 1;
                
                // Monthly breakdown for timeline
                const issueDate = policy.issue_date;
                if (issueDate) {
                    const date = new Date(issueDate);
                    const monthYear = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`;
                    monthlyData[monthYear] = (monthlyData[monthYear] || 0) + premium;
                }
            });
            
            // Insurance Portfolio Key Metrics
            const metricsElement = document.getElementById('insuranceMetrics');
            if (metricsElement) {
                metricsElement.innerHTML = `
                    <div class="col-md-3 mb-3">
                        <div class="metric-card p-3 bg-white rounded border shadow-sm">
                            <h5 class="text-info mb-1">${totalPolicies}</h5>
                            <small class="text-muted"><i class="fas fa-file-contract me-1"></i>Total Policies</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-card p-3 bg-white rounded border shadow-sm">
                            <h5 class="text-success mb-1">₹${totalPremium.toLocaleString('en-IN', {maximumFractionDigits: 0})}</h5>
                            <small class="text-muted"><i class="fas fa-money-bill-wave me-1"></i>Total Premium</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-card p-3 bg-white rounded border shadow-sm">
                            <h5 class="text-primary mb-1">${activeCount}</h5>
                            <small class="text-muted"><i class="fas fa-check-circle me-1"></i>Active Policies</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="metric-card p-3 bg-white rounded border shadow-sm">
                            <h5 class="text-warning mb-1">₹${totalCommission.toLocaleString('en-IN', {maximumFractionDigits: 0})}</h5>
                            <small class="text-muted"><i class="fas fa-hand-holding-usd me-1"></i>Total Commission</small>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="metric-card p-3 bg-light rounded border">
                            <h6 class="text-dark mb-2"><i class="fas fa-percentage me-1"></i>Portfolio Health</h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="text-success mb-0">${Math.round((activeCount/totalPolicies)*100)}%</h5>
                                    <small class="text-muted">Active Rate</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-primary mb-0">₹${Math.round(totalPremium/totalPolicies).toLocaleString('en-IN')}</h5>
                                    <small class="text-muted">Avg Premium</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="metric-card p-3 bg-gradient-info text-white rounded border">
                            <h6 class="mb-2"><i class="fas fa-building me-1"></i>Company Portfolio</h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="mb-0">${Object.keys(companyBreakdown).length}</h5>
                                    <small>Companies</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="mb-0">${Math.round(totalPolicies/Object.keys(companyBreakdown).length)}</h5>
                                    <small>Avg/Company</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="metric-card p-3 bg-gradient-success text-white rounded border">
                            <h6 class="mb-2"><i class="fas fa-coins me-1"></i>Revenue Analysis</h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="mb-0">₹${totalEarnings.toLocaleString('en-IN', {maximumFractionDigits: 0})}</h5>
                                    <small>Net Earnings</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="mb-0">${((totalEarnings/totalPremium)*100).toFixed(1)}%</h5>
                                    <small>Margin</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Check if Chart.js is available
            console.log('Chart.js available for Insurance:', typeof Chart !== 'undefined');
            
            if (typeof Chart === 'undefined') {
                console.error('Chart.js failed to load, creating fallback charts for Insurance...');
                
                // Policy Status Fallback Chart
                const statusCanvas = document.getElementById('statusChart');
                if (statusCanvas) {
                    const statusData = [
                        ['Active Policies', activeCount, 'success'],
                        ['Not Renewed', notRenewedCount, 'danger']
                    ];
                    
                    let fallbackStatus = `
                        <div class="fallback-chart">
                            <h6>Policy Status Distribution (Text Chart)</h6>
                            <div class="alert alert-${activeCount > notRenewedCount ? 'success' : 'warning'} mb-3">
                                <i class="fas fa-${activeCount > notRenewedCount ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                                <strong>Portfolio Health:</strong> 
                                ${Math.round((activeCount/totalPolicies)*100)}% policies are active
                            </div>
                    `;
                    
                    statusData.forEach(([category, count, color]) => {
                        const percentage = Math.round((count / totalPolicies) * 100);
                        fallbackStatus += `
                            <div class="chart-bar mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-${color}">
                                        <i class="fas fa-${color === 'success' ? 'check-circle' : 'times-circle'} me-1"></i>
                                        <strong>${category}</strong>
                                    </span>
                                    <span class="badge bg-${color}">${count} policies</span>
                                </div>
                                <div class="progress mt-2" style="height: 12px;">
                                    <div class="progress-bar bg-${color}" style="width: ${percentage}%" title="${percentage}%"></div>
                                </div>
                                <small class="text-muted">${percentage}% of total portfolio</small>
                            </div>
                        `;
                    });
                    
                    fallbackStatus += '</div>';
                    statusCanvas.parentElement.innerHTML = fallbackStatus;
                }
                
                // Insurance Company Fallback Chart
                const companyCanvas = document.getElementById('insuranceCompanyChart');
                if (companyCanvas) {
                    const topCompanies = Object.entries(companyBreakdown)
                        .sort((a, b) => b[1] - a[1])
                        .slice(0, 5);
                    
                    let fallbackCompany = `
                        <div class="fallback-chart">
                            <h6>Top Insurance Companies (Text Chart)</h6>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Companies with highest policy count in your portfolio
                                </small>
                            </div>
                    `;
                    
                    topCompanies.forEach(([company, count], index) => {
                        const percentage = Math.round((count / totalPolicies) * 100);
                        const isTop = index === 0;
                        
                        fallbackCompany += `
                            <div class="chart-bar mb-3 ${isTop ? 'border border-primary rounded p-2' : ''}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="${isTop ? 'text-primary' : 'text-info'}">
                                        ${isTop ? '<i class="fas fa-trophy me-1"></i>' : '<i class="fas fa-building me-1"></i>'}
                                        <strong>${company}</strong>
                                        ${isTop ? '<small class="ms-2 badge bg-primary">Top Partner</small>' : ''}
                                    </span>
                                    <span class="badge ${isTop ? 'bg-primary' : 'bg-info'}">${count} policies</span>
                                </div>
                                <div class="progress mt-2" style="height: 12px;">
                                    <div class="progress-bar ${isTop ? 'bg-primary' : 'bg-info'}" style="width: ${percentage}%" title="${percentage}%"></div>
                                </div>
                                <small class="text-muted">${percentage}% of portfolio</small>
                            </div>
                        `;
                    });
                    
                    fallbackCompany += '</div>';
                    companyCanvas.parentElement.innerHTML = fallbackCompany;
                }
                
                // Timeline Fallback Chart
                const timelineCanvas = document.getElementById('timelineChart');
                if (timelineCanvas) {
                    const timelineEntries = Object.entries(monthlyData)
                        .sort()
                        .slice(-12); // Last 12 months
                    
                    let fallbackTimeline = `
                        <div class="fallback-chart">
                            <h6>Premium Timeline Analysis (Text Chart)</h6>
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Monthly premium collection over the last year
                                </small>
                            </div>
                    `;
                    
                    const maxValue = Math.max(...timelineEntries.map(([, value]) => value));
                    
                    timelineEntries.forEach(([month, amount]) => {
                        const percentage = Math.round((amount / maxValue) * 100);
                        const monthName = new Date(month + '-01').toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
                        
                        fallbackTimeline += `
                            <div class="chart-bar mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-warning">
                                        <i class="fas fa-calendar me-1"></i>
                                        <strong>${monthName}</strong>
                                    </span>
                                    <span class="badge bg-warning">₹${amount.toLocaleString('en-IN', {maximumFractionDigits: 0})}</span>
                                </div>
                                <div class="progress mt-1" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: ${percentage}%" title="₹${amount.toLocaleString('en-IN')}"></div>
                                </div>
                            </div>
                        `;
                    });
                    
                    fallbackTimeline += '</div>';
                    timelineCanvas.parentElement.innerHTML = fallbackTimeline;
                }
                
                return; // Skip Chart.js code
            }
            
            // Policy Status Chart (Chart.js)
            const statusCanvas = document.getElementById('statusChart');
            if (statusCanvas) {
                const statusCtx = statusCanvas.getContext('2d');
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Active Policies', 'Not Renewed'],
                        datasets: [{
                            data: [activeCount, notRenewedCount],
                            backgroundColor: ['{{ chart_color("success") }}', '{{ chart_color("danger") }}'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }
            
            // Insurance Company Chart (Chart.js)
            const companyCanvas = document.getElementById('insuranceCompanyChart');
            if (companyCanvas) {
                const companyCtx = companyCanvas.getContext('2d');
                const topCompanies = Object.entries(companyBreakdown)
                    .sort((a, b) => b[1] - a[1])
                    .slice(0, 5);
                
                new Chart(companyCtx, {
                    type: 'bar',
                    data: {
                        labels: topCompanies.map(item => item[0]),
                        datasets: [{
                            label: 'Policy Count',
                            data: topCompanies.map(item => item[1]),
                            backgroundColor: '{{ chart_color("primary") }}',
                            borderColor: '{{ preg_replace("/0\.\d+\)/", "1)", chart_color("primary")) }}',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0 }
                            }
                        }
                    }
                });
            }
            
            // Timeline Chart (Chart.js)
            const timelineCanvas = document.getElementById('timelineChart');
            if (timelineCanvas) {
                const timelineCtx = timelineCanvas.getContext('2d');
                const timelineEntries = Object.entries(monthlyData)
                    .sort()
                    .slice(-12); // Last 12 months
                
                new Chart(timelineCtx, {
                    type: 'line',
                    data: {
                        labels: timelineEntries.map(([month]) => {
                            return new Date(month + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                        }),
                        datasets: [{
                            label: 'Premium Amount',
                            data: timelineEntries.map(([, amount]) => amount),
                            borderColor: '{{ chart_color("warning") }}',
                            backgroundColor: '{{ preg_replace("/0\.\d+\)/", "0.1)", chart_color("warning")) }}',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₹' + value.toLocaleString('en-IN');
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    </script>
@endsection