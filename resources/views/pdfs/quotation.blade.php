<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Motor Insurance Quote Comparison</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            margin: 10px;
            color: {{ theme_color('dark') }};
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px solid {{ theme_color('dark') }};
            padding-bottom: 8px;
        }

        .header h1 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }

        .customer-info {
            margin-bottom: 8px;
            border: 1px solid {{ theme_body_bg_color() }};
            padding: 6px;
        }

        .customer-info h3 {
            margin: 0 0 6px 0;
            font-size: 11px;
            background: {{ theme_color('light') }};
            padding: 3px;
            border-bottom: 1px solid {{ theme_body_bg_color() }};
        }

        .info-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .info-row {
            display: table-row;
        }

        .info-item {
            display: table-cell;
            padding: 2px 3px;
            border-bottom: 1px solid {{ theme_color('light') }};
            width: 25%;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: {{ theme_color('secondary') }};
        }

        .info-value {
            margin-left: 10px;
        }

        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 9px;
        }

        .comparison-table th {
            background: {{ theme_color('dark') }};
            color: white;
            padding: 4px 2px;
            text-align: center;
            font-weight: bold;
            border: 1px solid {{ theme_color('dark') }};
        }

        .comparison-table td {
            padding: 3px 2px;
            text-align: center;
            border: 1px solid {{ theme_body_bg_color() }};
        }

        .section-header {
            background: {{ theme_color('secondary') }} !important;
            color: white !important;
            font-weight: bold;
            text-align: left !important;
        }

        .row-header {
            background: {{ theme_color('light') }};
            font-weight: bold;
            text-align: left !important;
            padding-left: 5px !important;
        }

        .currency {
            text-align: right;
            font-weight: bold;
            color: {{ theme_color('success') }};
        }

        .total-row {
            background: {{ theme_color('light') }} !important;
            font-weight: bold;
        }

        .final-total {
            background: {{ theme_color('success') }} !important;
            color: white !important;
            font-weight: bold;
            font-size: 10px;
        }

        .ranking {
            background: {{ theme_primary_color() }} !important;
            color: white !important;
            font-weight: bold;
            font-size: 10px;
        }

        .rank-1 {
            background: {{ theme_primary_color() }} !important;
        }

        .rank-2 {
            background: {{ theme_color('secondary') }} !important;
        }

        .rank-3 {
            background: {{ theme_color('warning') }} !important;
        }

        .company-column {
            width: 15%;
        }

        .description-column {
            width: 40%;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 8px;
            color: {{ theme_color('secondary') }};
            border-top: 1px solid {{ theme_body_bg_color() }};
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Insurance Quote Comparison</h1>
    </div>

    <div class="customer-info">
        <h3>Customer & Vehicle Information</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-item">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $quotation->customer->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Mobile:</span>
                    <span class="info-value">{{ $quotation->customer->mobile_number ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Vehicle No:</span>
                    <span class="info-value">{{ $quotation->vehicle_number ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Policy Type:</span>
                    <span class="info-value">{{ $quotation->policy_type ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <span class="info-label">Make Model:</span>
                    <span class="info-value">{{ $quotation->make_model_variant ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">RTO:</span>
                    <span class="info-value">{{ $quotation->rto_location ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">NCB Percentage:</span>
                    <span class="info-value"
                        style="font-weight: bold; color: {{ theme_color('success') }};">{{ $quotation->ncb_percentage ?? 0 }}%</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total IDV:</span>
                    <span class="info-value">{{ format_indian_currency($quotation->total_idv ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Insurance Coverage Details Section -->
    {{-- <div class="customer-info" style="margin-top: 15px;">
        <h3>Insurance Coverage Details</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-item">
                    <span class="info-label">Policy Type:</span>
                    <span class="info-value">{{ $quotation->policy_type ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Policy Tenure:</span>
                    <span class="info-value">{{ $quotation->policy_tenure_years ?? 'N/A' }} Year(s)</span>
                </div>
                <div class="info-item">
                    <span class="info-label">NCB Discount:</span>
                    <span class="info-value" style="font-weight: bold; color: {{ theme_color('success') }};">{{ $quotation->ncb_percentage ?? 0 }}%</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="info-value">{{ $quotation->status ?? 'Draft' }}</span>
                </div>
            </div>
            @if ($quotation->addon_covers && count($quotation->addon_covers) > 0)
            <div class="info-row" style="border-top: 1px solid {{ theme_body_bg_color() }}; padding-top: 8px; margin-top: 8px;">
                <div class="info-item" style="width: 100%;">
                    <span class="info-label">Add-on Covers Selected:</span>
                    <span class="info-value">{{ implode(', ', $quotation->addon_covers) }}</span>
                </div>
            </div>
            @endif
        </div>
    </div> --}}

    <table class="comparison-table">
        <thead>
            <tr>
                <th class="description-column">Description</th>
                @foreach ($quotation->quotationCompanies as $company)
                    <th class="company-column">{{ strtoupper($company->insuranceCompany->name) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <!-- Quote Information -->
            <tr>
                <td class="row-header">Quote Number</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td>{{ $company->quote_number ?? 'N/A' }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="row-header">Plan Name</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td>{{ $company->plan_name ?? 'N/A' }}</td>
                @endforeach
            </tr>

            <!-- IDV Breakdown Section -->
            <tr>
                <td class="section-header" colspan="{{ count($quotation->quotationCompanies) + 1 }}">IDV Breakdown
                </td>
            </tr>
            <tr>
                <td class="row-header">Policy Type</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td>{{ $company->policy_type ?? $quotation->policy_type ?? 'N/A' }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="row-header">Policy Tenure</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td>{{ ($company->policy_tenure_years ?? $quotation->policy_tenure_years ?? 1) }} Year(s)</td>
                @endforeach
            </tr>
            <tr>
                <td class="row-header">Vehicle IDV</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->idv_vehicle ?? $quotation->idv_vehicle ?? 0) }}</td>
                @endforeach
            </tr>
            @if($quotation->quotationCompanies->where('idv_trailer', '>', 0)->count() > 0 || $quotation->idv_trailer > 0)
            <tr>
                <td class="row-header">Trailer IDV</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->idv_trailer ?? $quotation->idv_trailer ?? 0) }}</td>
                @endforeach
            </tr>
            @endif
            @if($quotation->quotationCompanies->where('idv_cng_lpg_kit', '>', 0)->count() > 0 || $quotation->idv_cng_lpg_kit > 0)
            <tr>
                <td class="row-header">CNG/LPG Kit IDV</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->idv_cng_lpg_kit ?? $quotation->idv_cng_lpg_kit ?? 0) }}</td>
                @endforeach
            </tr>
            @endif
            @if($quotation->quotationCompanies->where('idv_electrical_accessories', '>', 0)->count() > 0 || $quotation->idv_electrical_accessories > 0)
            <tr>
                <td class="row-header">Electrical Accessories IDV</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->idv_electrical_accessories ?? $quotation->idv_electrical_accessories ?? 0) }}</td>
                @endforeach
            </tr>
            @endif
            @if($quotation->quotationCompanies->where('idv_non_electrical_accessories', '>', 0)->count() > 0 || $quotation->idv_non_electrical_accessories > 0)
            <tr>
                <td class="row-header">Non-Electrical Accessories IDV</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->idv_non_electrical_accessories ?? $quotation->idv_non_electrical_accessories ?? 0) }}</td>
                @endforeach
            </tr>
            @endif
            <tr>
                <td class="row-header total-row">Total IDV</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency total-row">{{ format_indian_currency($company->total_idv ?? $quotation->total_idv ?? 0) }}</td>
                @endforeach
            </tr>

            <!-- Basic Premium Section -->
            <tr>
                <td class="section-header" colspan="{{ count($quotation->quotationCompanies) + 1 }}">Premium Breakdown
                </td>
            </tr>
            <tr>
                <td class="row-header">Basic OD Premium</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->basic_od_premium ?? 0) }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="row-header">Third Party Premium</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->tp_premium ?? 0) }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="row-header">CNG/LPG Premium</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->cng_lpg_premium ?? 0) }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="row-header">Total OD Premium</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->total_od_premium ?? 0) }}</td>
                @endforeach
            </tr>

            <!-- Add On Covers Section -->
            <tr>
                <td class="section-header" colspan="{{ count($quotation->quotationCompanies) + 1 }}">Add On Covers</td>
            </tr>
            @php
                $allAddons = [];
                foreach ($quotation->quotationCompanies as $company) {
                    if (isset($company->addon_covers_breakdown)) {
                        $breakdown = is_string($company->addon_covers_breakdown)
                            ? json_decode($company->addon_covers_breakdown, true)
                            : $company->addon_covers_breakdown;
                        if ($breakdown) {
                            foreach ($breakdown as $addonName => $addonData) {
                                // Show ALL addons in breakdown, not just those with prices
                                if ($addonName !== 'Others') {
                                    $allAddons[$addonName] = true;
                                }
                            }
                        }
                    }
                }
            @endphp

            @foreach (array_keys($allAddons) as $addonName)
                <tr>
                    <td class="row-header">{{ $addonName }}</td>
                    @foreach ($quotation->quotationCompanies as $company)
                        @php
                            $breakdown = is_string($company->addon_covers_breakdown)
                                ? json_decode($company->addon_covers_breakdown, true)
                                : $company->addon_covers_breakdown;
                            $price = isset($breakdown[$addonName]['price']) ? $breakdown[$addonName]['price'] : 0;
                        @endphp
                        <td class="currency">
                            @if($price > 0)
                                {{ format_indian_currency($price) }}
                            @else
                                <span style="color: green; font-weight: bold;">✓ Covered</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach

            <tr>
                <td class="row-header">Total Add on Premium</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->total_addon_premium ?? 0) }}</td>
                @endforeach
            </tr>

            <!-- Net Premium -->
            <tr>
                <td class="row-header total-row">Net Premium</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency total-row">{{ format_indian_currency($company->net_premium ?? 0) }}</td>
                @endforeach
            </tr>

            <!-- GST Section -->
            <tr>
                <td class="section-header" colspan="{{ count($quotation->quotationCompanies) + 1 }}">GST & Final
                    Premium</td>
            </tr>
            <tr>
                <td class="row-header">SGST</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->sgst_amount ?? 0) }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="row-header">CGST</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->cgst_amount ?? 0) }}</td>
                @endforeach
            </tr>
            <tr>
                <td class="row-header">Total Premium</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="currency">{{ format_indian_currency($company->total_premium ?? 0) }}</td>
                @endforeach
            </tr>

            <!-- Final Premium -->
            <tr>
                <td class="final-total">Final Premium</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td class="final-total">{{ format_indian_currency($company->final_premium ?? 0) }}</td>
                @endforeach
            </tr>

            <!-- Ranking -->
            <tr>
                <td class="ranking">RANKING</td>
                @foreach ($quotation->quotationCompanies as $company)
                    @php
                        $rank = $company->ranking ?? 1;
                        $rankClass = match ($rank) {
                            1 => 'rank-1',
                            2 => 'rank-2',
                            3 => 'rank-3',
                            default => 'ranking',
                        };
                    @endphp
                    <td class="ranking {{ $rankClass }}">{{ $rank }}</td>
                @endforeach
            </tr>

            <!-- Recommendation -->
            <tr>
                <td class="row-header">Recommended</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td
                        style="text-align: center; {{ $company->is_recommended ? 'background: {{ theme_color('success') }}; color: white; font-weight: bold;' : '' }}">
                        {{ $company->is_recommended ? 'YES' : 'NO' }}
                    </td>
                @endforeach
            </tr>
            @if($quotation->quotationCompanies->where('recommendation_note', '!=', null)->count() > 0)
            <tr>
                <td class="row-header">Recommendation Note</td>
                @foreach ($quotation->quotationCompanies as $company)
                    <td style="font-size: 9px; text-align: left;">
                        {{ $company->recommendation_note ?? '-' }}
                    </td>
                @endforeach
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Insurance Coverage Details - Company Wise -->
    @if ($quotation->quotationCompanies->count() > 0)
        <div style="page-break-inside: avoid; margin-top: 15px;">
            <h3 style="background: {{ theme_color('dark') }}; color: white; padding: 8px; margin: 10px 0 8px 0; font-size: 12px; text-align: center;">
                INSURANCE COVERAGE DETAILS (COMPANY-WISE)
            </h3>
            
            @foreach ($quotation->quotationCompanies->sortBy('ranking') as $company)
                <div style="border: 1px solid {{ theme_body_bg_color() }}; margin: 8px 0; padding: 8px; page-break-inside: avoid;">
                    <!-- Company Header -->
                    <div style="background: {{ theme_color('light') }}; padding: 5px; border-bottom: 1px solid {{ theme_body_bg_color() }}; margin-bottom: 6px;">
                        <table width="100%" style="border-collapse: collapse;">
                            <tr>
                                <td style="font-weight: bold; font-size: 11px;">
                                    <span style="background: {{ theme_color('dark') }}; color: white; padding: 2px 5px; border-radius: 3px; font-size: 9px;">{{ $company->ranking }}</span>
                                    {{ $company->insuranceCompany->name }}
                                    @if($company->plan_name) - {{ $company->plan_name }}@endif
                                    @if($company->is_recommended)
                                        <span style="background: #d4862a; color: white; padding: 1px 4px; border-radius: 2px; font-size: 8px;">⭐ RECOMMENDED</span>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: bold; font-size: 11px;">
                                    {{ format_indian_currency($company->final_premium ?? 0) }}
                                    @if($company->quote_number)<br><span style="font-size: 8px; font-weight: normal;">Quote: {{ $company->quote_number }}</span>@endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Coverage Details Table -->
                    <table width="100%" style="border-collapse: collapse; font-size: 9px;">
                        <tr style="background: {{ theme_color('light') }};">
                            <td style="padding: 3px; font-weight: bold; color: {{ theme_color('secondary') }};">Plan Name:</td>
                            <td style="padding: 3px;">{{ $company->plan_name ?? 'Standard Plan' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 3px; font-weight: bold; color: {{ theme_color('secondary') }};">Quote Number:</td>
                            <td style="padding: 3px;">
                                @if($company->quote_number)
                                    {{ $company->quote_number }}
                                @else
                                    Auto-generated
                                @endif
                            </td>
                        </tr>
                        <tr style="border-top: 1px solid {{ theme_body_bg_color() }}; background: {{ theme_content_bg_color() }};">
                            <td style="padding: 3px; font-weight: bold; color: {{ theme_color('success') }};">Basic OD Premium:</td>
                            <td style="padding: 3px; font-weight: bold;">{{ format_indian_currency($company->basic_od_premium ?? 0) }}</td>
                        </tr>
                        <tr style="background: {{ theme_content_bg_color() }};">
                            <td style="padding: 3px; font-weight: bold; color: {{ theme_color('success') }};">TP Premium:</td>
                            <td style="padding: 3px; font-weight: bold;">{{ format_indian_currency($company->tp_premium ?? 0) }}</td>
                        </tr>
                        <tr style="background: {{ theme_content_bg_color() }};">
                            <td style="padding: 3px; font-weight: bold; color: {{ theme_color('success') }};">Add-on Premium:</td>
                            <td style="padding: 3px; font-weight: bold;">{{ format_indian_currency($company->total_addon_premium ?? 0) }}</td>
                        </tr>
                        @if($company->cng_lpg_premium > 0)
                        <tr style="background: {{ theme_content_bg_color() }};">
                            <td style="padding: 3px; font-weight: bold; color: {{ theme_color('success') }};">CNG/LPG Premium:</td>
                            <td style="padding: 3px; font-weight: bold;">{{ format_indian_currency($company->cng_lpg_premium) }}</td>
                        </tr>
                        @endif
                        <tr style="border-top: 1px solid {{ theme_body_bg_color() }}; background: {{ theme_content_bg_color() }};">
                            <td style="padding: 3px; font-weight: bold; color: {{ theme_color('info') }};">Net Premium:</td>
                            <td style="padding: 3px; font-weight: bold; color: {{ theme_color('info') }};">{{ format_indian_currency($company->net_premium ?? 0) }}</td>
                        </tr>
                        <tr style="background: {{ theme_content_bg_color() }};">
                            <td style="padding: 3px; font-weight: bold; color: {{ theme_color('warning') }};">Total GST:</td>
                            <td style="padding: 3px; font-weight: bold;">{{ format_indian_currency(($company->sgst_amount ?? 0) + ($company->cgst_amount ?? 0)) }}</td>
                        </tr>
                        @if($company->roadside_assistance > 0)
                        <tr style="background: {{ theme_content_bg_color() }};">
                            <td style="padding: 3px; font-weight: bold; color: {{ theme_color('info') }};">RSA:</td>
                            <td style="padding: 3px; font-weight: bold;">{{ format_indian_currency($company->roadside_assistance) }}</td>
                        </tr>
                        @endif
                        <tr style="border-top: 2px solid {{ theme_color('danger') }}; background: {{ theme_content_bg_color() }};">
                            <td style="padding: 4px; font-weight: bold; color: {{ theme_color('danger') }}; font-size: 10px;">Final Premium:</td>
                            <td style="padding: 4px; font-weight: bold; color: {{ theme_color('danger') }}; font-size: 10px;">{{ format_indian_currency($company->final_premium ?? 0) }}</td>
                        </tr>
                    </table>
                </div>
            @endforeach
        </div>
    @endif

    <div class="footer">
        <p>This quotation comparison is generated automatically. Please verify all details before making any decisions.
            Generated by <a href="{{ footer_developer_url() }}" target="_blank">{{ footer_developer_name() }}</a> - {{ format_app_date(now()) }} {{ now()->format('H:i:s') }}
        </p>
    </div>
</body>

</html>
