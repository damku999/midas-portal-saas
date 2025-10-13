@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        function getArrows($currentValue, $previousValue)
        {
            if ($currentValue > $previousValue) {
                return '<i class="fas fa-arrow-up text-success me-1"></i>';
            } elseif ($currentValue < $previousValue) {
                return '<i class="fas fa-arrow-down text-danger me-1"></i>';
            } else {
                return '<i class="fas fa-minus text-muted me-1"></i>';
            }
        }

        function getChangePercentage($currentValue, $previousValue)
        {
            if ($previousValue == 0) {
                return '';
            }
            $change = (($currentValue - $previousValue) / $previousValue) * 100;
            $class = $change > 0 ? 'positive' : ($change < 0 ? 'negative' : '');
            return '<span class="metric-change ' .
                $class .
                '">' .
                ($change > 0 ? '+' : '') .
                number_format($change, 1) .
                '%</span>';
        }

        $metricLabels = [
            'sum_final_premium' => 'Final Premium',
            'sum_my_commission' => 'My Commission',
            'sum_transfer_commission' => 'Commission Given',
            'sum_actual_earnings' => 'My Earning',
        ];
    @endphp

    <div class="container-fluid">
        <!-- Daily Performance Comparison -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar-day me-2"></i>Daily Performance Comparison</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Metric</th>
                                        <th class="text-center">Today<br><small
                                                class="text-muted">{{ formatDateForUi($data['date']) }}</small></th>
                                        <th class="text-center">Yesterday<br><small
                                                class="text-muted">{{ formatDateForUi($data['yesterday']) }}</small></th>
                                        <th class="text-center">Day Before<br><small
                                                class="text-muted">{{ formatDateForUi($data['day_before_yesterday']) }}</small>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Final Premium</strong></td>
                                        <td class="text-center">
                                            {!! getArrows($data['today_data']['sum_final_premium'], $data['yesterday_data']['sum_final_premium']) !!}
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['today_data']['sum_final_premium']) }}</span>
                                            <br><small>{!! getChangePercentage($data['today_data']['sum_final_premium'], $data['yesterday_data']['sum_final_premium']) !!}</small>
                                        </td>
                                        <td class="text-center">
                                            {!! getArrows(
                                                $data['yesterday_data']['sum_final_premium'],
                                                $data['day_before_yesterday_data']['sum_final_premium'],
                                            ) !!}
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['yesterday_data']['sum_final_premium']) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['day_before_yesterday_data']['sum_final_premium']) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>My Commission</strong></td>
                                        <td class="text-center">
                                            {!! getArrows($data['today_data']['sum_my_commission'], $data['yesterday_data']['sum_my_commission']) !!}
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['today_data']['sum_my_commission']) }}</span>
                                            <br><small>{!! getChangePercentage($data['today_data']['sum_my_commission'], $data['yesterday_data']['sum_my_commission']) !!}</small>
                                        </td>
                                        <td class="text-center">
                                            {!! getArrows(
                                                $data['yesterday_data']['sum_my_commission'],
                                                $data['day_before_yesterday_data']['sum_my_commission'],
                                            ) !!}
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['yesterday_data']['sum_my_commission']) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['day_before_yesterday_data']['sum_my_commission']) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Commission Given</strong></td>
                                        <td class="text-center">
                                            {!! getArrows(
                                                $data['today_data']['sum_transfer_commission'],
                                                $data['yesterday_data']['sum_transfer_commission'],
                                            ) !!}
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['today_data']['sum_transfer_commission']) }}</span>
                                            <br><small>{!! getChangePercentage(
                                                $data['today_data']['sum_transfer_commission'],
                                                $data['yesterday_data']['sum_transfer_commission'],
                                            ) !!}</small>
                                        </td>
                                        <td class="text-center">
                                            {!! getArrows(
                                                $data['yesterday_data']['sum_transfer_commission'],
                                                $data['day_before_yesterday_data']['sum_transfer_commission'],
                                            ) !!}
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['yesterday_data']['sum_transfer_commission']) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['day_before_yesterday_data']['sum_transfer_commission']) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>My Earning</strong></td>
                                        <td class="text-center">
                                            {!! getArrows($data['today_data']['sum_actual_earnings'], $data['yesterday_data']['sum_actual_earnings']) !!}
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['today_data']['sum_actual_earnings']) }}</span>
                                            <br><small>{!! getChangePercentage(
                                                $data['today_data']['sum_actual_earnings'],
                                                $data['yesterday_data']['sum_actual_earnings'],
                                            ) !!}</small>
                                        </td>
                                        <td class="text-center">
                                            {!! getArrows(
                                                $data['yesterday_data']['sum_actual_earnings'],
                                                $data['day_before_yesterday_data']['sum_actual_earnings'],
                                            ) !!}
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['yesterday_data']['sum_actual_earnings']) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['day_before_yesterday_data']['sum_actual_earnings']) }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Yearly Performance Comparison -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Annual Performance Comparison</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Metric</th>
                                        <th class="text-center">Current Year<br><small
                                                class="text-muted">{{ \Carbon\Carbon::parse($data['financial_year_start'])->format('M-Y') }}
                                                to
                                                {{ \Carbon\Carbon::parse($data['financial_year_end'])->format('M-Y') }}</small>
                                        </th>
                                        <th class="text-center">Last Year<br><small
                                                class="text-muted">{{ \Carbon\Carbon::parse($data['previous_financial_year_start'])->format('M-Y') }}
                                                to
                                                {{ \Carbon\Carbon::parse($data['previous_financial_year_end'])->format('M-Y') }}</small>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Final Premium</strong></td>
                                        <td class="text-center">
                                            {!! getArrows($data['current_year_data']['sum_final_premium'], $data['last_year_data']['sum_final_premium']) !!}
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['current_year_data']['sum_final_premium']) }}</span>
                                            <br><small>{!! getChangePercentage(
                                                $data['current_year_data']['sum_final_premium'],
                                                $data['last_year_data']['sum_final_premium'],
                                            ) !!}</small>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['last_year_data']['sum_final_premium']) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>My Commission</strong></td>
                                        <td class="text-center">
                                            {!! getArrows($data['current_year_data']['sum_my_commission'], $data['last_year_data']['sum_my_commission']) !!}
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['current_year_data']['sum_my_commission']) }}</span>
                                            <br><small>{!! getChangePercentage(
                                                $data['current_year_data']['sum_my_commission'],
                                                $data['last_year_data']['sum_my_commission'],
                                            ) !!}</small>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['last_year_data']['sum_my_commission']) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Commission Given</strong></td>
                                        <td class="text-center">
                                            {!! getArrows(
                                                $data['current_year_data']['sum_transfer_commission'],
                                                $data['last_year_data']['sum_transfer_commission'],
                                            ) !!}
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['current_year_data']['sum_transfer_commission']) }}</span>
                                            <br><small>{!! getChangePercentage(
                                                $data['current_year_data']['sum_transfer_commission'],
                                                $data['last_year_data']['sum_transfer_commission'],
                                            ) !!}</small>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['last_year_data']['sum_transfer_commission']) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>My Earning</strong></td>
                                        <td class="text-center">
                                            {!! getArrows($data['current_year_data']['sum_actual_earnings'], $data['last_year_data']['sum_actual_earnings']) !!}
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['current_year_data']['sum_actual_earnings']) }}</span>
                                            <br><small>{!! getChangePercentage(
                                                $data['current_year_data']['sum_actual_earnings'],
                                                $data['last_year_data']['sum_actual_earnings'],
                                            ) !!}</small>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="fw-bold">{{ format_indian_currency($data['last_year_data']['sum_actual_earnings']) }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quarterly Performance Breakdown -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Quarterly Performance Breakdown</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 150px;">Metric</th>
                                        @foreach ($data['quarters_data'] as $quarter)
                                            <th class="text-center">
                                                Q{{ $loop->iteration }}<br>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($data['quarter_date'][$loop->iteration - 1]['quarter_start'])->format('M-Y') }}
                                                    to
                                                    {{ \Carbon\Carbon::parse($data['quarter_date'][$loop->iteration - 1]['quarter_end'])->format('M-Y') }}
                                                </small>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($metricLabels as $metricKey => $metricLabel)
                                        <tr>
                                            <td><strong>{{ $metricLabel }}</strong></td>
                                            @foreach ($data['quarters_data'] as $index => $quarter)
                                                <td class="text-center">
                                                    @if ($index > 0)
                                                        {!! getArrows($quarter[$metricKey], $data['quarters_data'][$index - 1][$metricKey]) !!}
                                                    @endif
                                                    <span
                                                        class="fw-bold">{{ format_indian_currency($quarter[$metricKey]) }}</span>
                                                    @if ($index > 0)
                                                        <br><small>{!! getChangePercentage($quarter[$metricKey], $data['quarters_data'][$index - 1][$metricKey]) !!}</small>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Renewal Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-3"><i class="fas fa-sync-alt me-2"></i>Monthly Renewal Overview</h4>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                <div class="card card-metric card-primary h-100" onclick="redirectToCustomerInsuranceIndex()"
                    style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="metric-label">Total Renewing This Month</div>
                                <div class="metric-value">{{ $total_renewing_this_month }}</div>
                            </div>
                            <div>
                                <i class="fas fa-calendar-check fa-2x text-primary opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                <div class="card card-metric card-success h-100" onclick="redirectToCustomerInsuranceIndex(1)"
                    style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="metric-label">Already Renewed This Month</div>
                                <div class="metric-value">{{ $already_renewed_this_month }}</div>
                            </div>
                            <div>
                                <i class="fas fa-check-circle fa-2x text-success opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                <div class="card card-metric card-warning h-100" onclick="redirectToCustomerInsuranceIndex(0,1)"
                    style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="metric-label">Pending Renewal This Month</div>
                                <div class="metric-value">{{ $pending_renewal_this_month }}</div>
                            </div>
                            <div>
                                <i class="fas fa-clock fa-2x text-warning opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Month Financial Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-3"><i class="fas fa-calendar-week me-2"></i>Current Month Financial Overview</h4>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card card-metric card-primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="metric-label">Turn Over (with GST)</div>
                                <div class="metric-value">
                                    {{ format_indian_currency($current_month_final_premium_with_gst) }}</div>
                            </div>
                            <div>
                                <i class="fas fa-chart-line fa-2x text-primary opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card card-metric card-success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="metric-label">Commission Received</div>
                                <div class="metric-value">
                                    {{ format_indian_currency($current_month_my_commission_amount) }}</div>
                            </div>
                            <div>
                                <i class="fas fa-hand-holding-usd fa-2x text-success opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card card-metric card-warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="metric-label">Commission Transferred</div>
                                <div class="metric-value">
                                    {{ format_indian_currency($current_month_transfer_commission_amount) }}</div>
                            </div>
                            <div>
                                <i class="fas fa-exchange-alt fa-2x text-warning opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                <div class="card card-metric card-info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="metric-label">Actual Earning</div>
                                <div class="metric-value">{{ format_indian_currency($current_month_actual_earnings) }}
                                </div>
                            </div>
                            <div>
                                <i class="fas fa-wallet fa-2x text-info opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last Month vs Lifetime Financial Comparison -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-calendar-minus me-2"></i>Last Month Financial Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="text-center">
                                    <div class="metric-label">Turn Over (GST)</div>
                                    <div class="metric-value" style="font-size: 1.5rem;">
                                        {{ format_indian_currency($last_month_final_premium_with_gst) }}</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-center">
                                    <div class="metric-label">Commission Received</div>
                                    <div class="metric-value" style="font-size: 1.5rem;">
                                        {{ format_indian_currency($last_month_my_commission_amount) }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="metric-label">Commission Transferred</div>
                                    <div class="metric-value" style="font-size: 1.5rem;">
                                        {{ format_indian_currency($last_month_transfer_commission_amount) }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="metric-label">Actual Earning</div>
                                    <div class="metric-value" style="font-size: 1.5rem;">
                                        {{ format_indian_currency($last_month_actual_earnings) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-infinity me-2"></i>Lifetime Financial Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="text-center">
                                    <div class="metric-label">Turn Over (GST)</div>
                                    <div class="metric-value" style="font-size: 1.5rem;">
                                        {{ format_indian_currency($life_time_final_premium_with_gst) }}</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="text-center">
                                    <div class="metric-label">Commission Received</div>
                                    <div class="metric-value" style="font-size: 1.5rem;">
                                        {{ format_indian_currency($life_time_my_commission_amount) }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="metric-label">Commission Transferred</div>
                                    <div class="metric-value" style="font-size: 1.5rem;">
                                        {{ format_indian_currency($life_time_transfer_commission_amount) }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="metric-label">Actual Earning</div>
                                    <div class="metric-value" style="font-size: 1.5rem;">
                                        {{ format_indian_currency($life_time_actual_earnings) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Policy & Customer Overview -->
        <div class="row mb-4">
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Policy Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-6 mb-3">
                                <div class="card card-metric card-primary h-100" onclick="redirectToPolicyList()"
                                    style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <div class="metric-label">Total Policies</div>
                                        <div class="metric-value">{{ $total_customer_insurance }}</div>
                                        <i class="fas fa-shield-alt fa-lg text-primary opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6 mb-3">
                                <div class="card card-metric card-success h-100" onclick="redirectToPolicyList('active')"
                                    style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <div class="metric-label">Active Policies</div>
                                        <div class="metric-value">{{ $active_customer_insurance }}</div>
                                        <i class="fas fa-check-circle fa-lg text-success opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6 mb-3">
                                <div class="card card-metric card-warning h-100"
                                    onclick="redirectToPolicyList('inactive')" style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <div class="metric-label">Inactive Policies</div>
                                        <div class="metric-value">{{ $inactive_customer_insurance }}</div>
                                        <i class="fas fa-pause-circle fa-lg text-warning opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6 mb-3">
                                <div class="card card-metric card-danger h-100" onclick="redirectToPolicyList('expiring')"
                                    style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <div class="metric-label">Expiring This Month</div>
                                        <div class="metric-value">{{ $expiring_customer_insurance }}</div>
                                        <i class="fas fa-exclamation-triangle fa-lg text-danger opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Customer Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="card card-metric card-primary h-100" onclick="redirectToCustomerList()"
                                    style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <div class="metric-label">Total Customers</div>
                                        <div class="metric-value">{{ $total_customer }}</div>
                                        <i class="fas fa-users fa-lg text-primary opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="card card-metric card-success h-100"
                                    onclick="redirectToCustomerList('active')" style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <div class="metric-label">Active</div>
                                        <div class="metric-value">{{ $active_customer }}</div>
                                        <i class="fas fa-user-check fa-lg text-success opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="card card-metric card-warning h-100"
                                    onclick="redirectToCustomerList('inactive')" style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <div class="metric-label">Inactive</div>
                                        <div class="metric-value">{{ $inactive_customer }}</div>
                                        <i class="fas fa-user-times fa-lg text-warning opacity-25"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Performance Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card dashboard-chart">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Monthly Financial Performance Trends</h5>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 400px;">
                            <canvas id="earningsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ cdn_url('cdn_chartjs_url') }}"></script>
    <script>
        const data = {!! $json_data !!};

        const labels = Object.keys(data);
        const colors = [
            'rgba(79, 70, 229, 0.8)', // Primary
            'rgba(34, 197, 94, 0.8)', // Success
            'rgba(251, 146, 60, 0.8)', // Warning
            'rgba(14, 165, 233, 0.8)' // Info
        ];

        const borderColors = [
            'rgba(79, 70, 229, 1)',
            'rgba(34, 197, 94, 1)',
            'rgba(251, 146, 60, 1)',
            'rgba(14, 165, 233, 1)'
        ];

        const datasets = Object.keys(data[labels[0]]).map((key, index) => ({
            label: key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '),
            backgroundColor: colors[index % colors.length],
            borderColor: borderColors[index % borderColors.length],
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
            data: labels.map(label => data[label][key]),
        }));

        const config = {
            type: "bar",
            data: {
                labels: labels,
                datasets: datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#1e293b',
                        bodyColor: '#64748b',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₹' + context.parsed.y.toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: false,
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 11,
                                weight: '500'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9',
                            lineWidth: 1
                        },
                        ticks: {
                            color: '#64748b',
                            font: {
                                size: 11
                            },
                            callback: function(value) {
                                return '₹' + value.toLocaleString('en-IN');
                            }
                        }
                    },
                },
            },
        };

        const myChart = new Chart(document.getElementById("earningsChart"), config);

        function redirectToCustomerInsuranceIndex(already_renewed_this_month = 0, pending_renewal_this_month = 0) {
            var date = new Date();

            // Start of the month
            var start = new Date(date.getFullYear(), date.getMonth(), 1);
            var startDate = ("0" + start.getDate()).slice(-2) + "/" +
                ("0" + (start.getMonth() + 1)).slice(-2) + "/" +
                start.getFullYear();

            // End of the month
            var end = new Date(date.getFullYear(), date.getMonth() + 1, 0);
            var endDate = ("0" + end.getDate()).slice(-2) + "/" +
                ("0" + (end.getMonth() + 1)).slice(-2) + "/" +
                end.getFullYear();

            const url =
                `{{ route('customer_insurances.index') }}?start_date=${startDate}&end_date=${endDate}&already_renewed_this_month=${already_renewed_this_month}&pending_renewal_this_month=${pending_renewal_this_month}`;
            window.location.href = url;
        }

        // Policy Statistics Navigation
        function redirectToPolicyList(status = '') {
            let url = '{{ route('customer_insurances.index') }}';

            if (status === 'active') {
                url += '?status=1'; // Active policies
            } else if (status === 'inactive') {
                url += '?status=0'; // Inactive policies
            } else if (status === 'expiring') {
                // Expiring this month - use current month date range
                var date = new Date();
                var start = new Date(date.getFullYear(), date.getMonth(), 1);
                var end = new Date(date.getFullYear(), date.getMonth() + 1, 0);

                var startDate = ("0" + start.getDate()).slice(-2) + "-" +
                    ("0" + (start.getMonth() + 1)).slice(-2) + "-" +
                    start.getFullYear();
                var endDate = ("0" + end.getDate()).slice(-2) + "-" +
                    ("0" + (end.getMonth() + 1)).slice(-2) + "-" +
                    end.getFullYear();

                url += `?renewal_due_start=${startDate}&renewal_due_end=${endDate}`;
            }

            window.location.href = url;
        }

        // Customer Statistics Navigation
        function redirectToCustomerList(status = '') {
            let url = '{{ route('customers.index') }}';

            if (status === 'active') {
                url += '?status=1'; // Active customers
            } else if (status === 'inactive') {
                url += '?status=0'; // Inactive customers
            }

            window.location.href = url;
        }
    </script>

@endsection
