@extends('layouts.app')

@section('title', 'Customer Insurance List')

@section('content')
    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- DataTales Example -->
        <div class="card shadow mt-3 mb-4">
            <x-list-header title="Customer Insurances Management" subtitle="Manage all active insurance policies"
                addRoute="customer_insurances.create" addPermission="customer-insurance-create"
                exportRoute="customer_insurances.export" />
            <div class="card-body">
                <form method="GET" action="{{ route('customer_insurances.index') }}" id="search_form">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="search">Search Customer Insurances</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    placeholder="Customer, policy number..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="start_date">Expiry Start Date</label>
                                <input type="text" class="form-control datepicker" id="start_date" name="start_date"
                                    placeholder="Start Date"
                                    value="{{ request('start_date') ?: request('renewal_due_start') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="end_date">Expiry End Date</label>
                                <input type="text" class="form-control datepicker" id="end_date" name="end_date"
                                    placeholder="End Date" value="{{ request('end_date') ?: request('renewal_due_end') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label><br>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="{{ route('customer_insurances.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden fields to preserve renewal filter parameters -->
                    @if (request('renewal_due_start'))
                        <input type="hidden" name="renewal_due_start" value="{{ request('renewal_due_start') }}">
                    @endif
                    @if (request('renewal_due_end'))
                        <input type="hidden" name="renewal_due_end" value="{{ request('renewal_due_end') }}">
                    @endif
                    <input type="hidden" name="sort" value="{{ request('sort', 'updated_at') }}">
                    <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="22%">
                                    <a href="{{ route('customer_insurances.index', array_merge(request()->query(), ['sort' => 'customer_name', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}"
                                        class="{{ $sort === 'customer_name' ? 'active' : '' }}">Customer Name
                                        @if ($sort === 'customer_name')
                                            @if ($direction === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="22%">
                                    <a href="{{ route('customer_insurances.index', array_merge(request()->query(), ['sort' => 'policy_no', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}"
                                        class="{{ $sort === 'policy_no' ? 'active' : '' }}">POLICY NO.
                                        @if ($sort === 'policy_no')
                                            @if ($direction === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="14%">
                                    <a href="{{ route('customer_insurances.index', array_merge(request()->query(), ['sort' => 'registration_no', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}"
                                        class="{{ $sort === 'registration_no' ? 'active' : '' }}">Registration NO.
                                        @if ($sort === 'registration_no')
                                            @if ($direction === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="10%">
                                    <a href="{{ route('customer_insurances.index', array_merge(request()->query(), ['sort' => 'start_date', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}"
                                        class="{{ $sort === 'start_date' ? 'active' : '' }}">Start Date
                                        @if ($sort === 'start_date')
                                            @if ($direction === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="10%">
                                    <a href="{{ route('customer_insurances.index', array_merge(request()->query(), ['sort' => 'expired_date', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}"
                                        class="{{ $sort === 'expired_date' ? 'active' : '' }}">Expired Date
                                        @if ($sort === 'expired_date')
                                            @if ($direction === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th width="12%">
                                    <a href="{{ route('customer_insurances.index', array_merge(request()->query(), ['sort' => 'premium_types.name', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}"
                                        class="{{ $sort === 'premium_types.name' ? 'active' : '' }}">Premium Type
                                        @if ($sort === 'premium_types.name')
                                            @if ($direction === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>

                                <th width="10%">
                                    <a href="{{ route('customer_insurances.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => $direction === 'asc' ? 'desc' : 'asc'])) }}"
                                        class="{{ $sort === 'status' ? 'active' : '' }}">Status
                                        @if ($sort === 'status')
                                            @if ($direction === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($customer_insurances as $customer_insurance)
                                <tr>
                                    <td>{{ $customer_insurance->customer_name }}</td>
                                    <td>{{ $customer_insurance->policy_no }}</td>
                                    <td>{{ $customer_insurance->registration_no }}</td>
                                    <td>{{ formatDateForUi($customer_insurance->start_date) }}</td>
                                    <td>{{ formatDateForUi($customer_insurance->expired_date) }}
                                    </td>
                                    <td>{{ $customer_insurance->policy_type_name }}</td>
                                    <td>
                                        @if ($customer_insurance->status == 0)
                                            <span class="badge bg-danger text-white">Inactive</span>
                                        @elseif ($customer_insurance->status == 1)
                                            <span class="badge bg-success text-white">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $expiredDate = \Carbon\Carbon::parse($customer_insurance->expired_date);
                                            $oneMonthBefore = $expiredDate->copy()->subMonth();
                                            $oneMonthAfter = $expiredDate->copy()->addMonth();
                                            $currentDate = \Carbon\Carbon::now();
                                        @endphp

                                        <div class="d-flex flex-nowrap"
                                            style="gap: 4px; justify-content: flex-start; align-items: center; overflow-x: auto;">
                                            <!-- 1. WhatsApp Send Document -->
                                            @if ($customer_insurance->policy_document_path)
                                                <a href="{{ route('customer_insurances.sendWADocument', ['customer_insurance' => $customer_insurance->id]) }}"
                                                    class="btn btn-success btn-sm" title="Send Document via WhatsApp">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                            @endif

                                            <!-- 2. WhatsApp Renewal Reminder -->
                                            @if (auth()->user()->hasPermissionTo('customer-insurance-edit') &&
                                                    $currentDate->greaterThanOrEqualTo($oneMonthBefore) &&
                                                    $customer_insurance->is_renewed == 0)
                                                <a href="{{ route('customer_insurances.sendRenewalReminderWA', ['customer_insurance' => $customer_insurance->id]) }}"
                                                    class="btn btn-warning btn-sm"
                                                    title="Send Renewal Reminder via WhatsApp"
                                                    style="white-space: nowrap;">
                                                    <i class="fa fa-bell"></i><i class="fab fa-whatsapp"
                                                        style="margin-left: 1px;"></i>
                                                </a>
                                            @endif

                                            <!-- 3. Edit -->
                                            @if (auth()->user()->hasPermissionTo('customer-insurance-edit'))
                                                <a href="{{ route('customer_insurances.edit', ['customer_insurance' => $customer_insurance->id]) }}"
                                                    class="btn btn-primary btn-sm" title="Edit Policy">
                                                    <i class="fa fa-pen"></i>
                                                </a>
                                            @endif

                                            <!-- 4. Download -->
                                            @if ($customer_insurance->policy_document_path)
                                                <a href="{{ asset('storage/' . $customer_insurance->policy_document_path) }}"
                                                    class="btn btn-info btn-sm" target="_blank"
                                                    title="Download Policy Document">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            @endif

                                            <!-- 5. Renew -->
                                            @if (auth()->user()->hasPermissionTo('customer-insurance-edit') &&
                                                    $currentDate->greaterThanOrEqualTo($oneMonthBefore) &&
                                                    $customer_insurance->is_renewed == 0)
                                                <a href="{{ route('customer_insurances.renew', ['customer_insurance' => $customer_insurance->id]) }}"
                                                    class="btn btn-secondary btn-sm" title="Renew Policy">
                                                    <i class="fas fa-redo"></i>
                                                </a>
                                            @endif

                                            <!-- 6. Enable/Disable -->
                                            @if (auth()->user()->hasPermissionTo('customer-insurance-delete'))
                                                @if ($customer_insurance->status == 0)
                                                    <a href="{{ route('customer_insurances.status', ['customer_insurance_id' => $customer_insurance->id, 'status' => 1]) }}"
                                                        class="btn btn-success btn-sm" title="Enable Policy">
                                                        <i class="fa fa-check"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('customer_insurances.status', ['customer_insurance_id' => $customer_insurance->id, 'status' => 0]) }}"
                                                        class="btn btn-danger btn-sm" title="Disable Policy">
                                                        <i class="fa fa-ban"></i>
                                                    </a>
                                                @endif
                                            @endif

                                            <!-- 7. Delete -->
                                            @if (auth()->user()->hasPermissionTo('customer-insurance-delete'))
                                                <a class="btn btn-danger btn-sm" href="javascript:void(0);"
                                                    title="Delete Policy"
                                                    onclick="delete_conf_common('{{ $customer_insurance->id }}','CustomerInsurance', 'Customer Insurance', '{{ route('customer_insurances.index') }}');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">No Record Found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <x-pagination-with-info :paginator="$customer_insurances" :request="$request" />
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#customer_id').select2();

            // Optional: Add logic to ensure the start date is before or equal to the end date
            $('.datepicker[name="start_date"]').on('changeDate', function(selected) {
                var endDate = $('.datepicker[name="end_date"]');
                endDate.datepicker('setStartDate', selected.date);
                if (selected.date > endDate.datepicker('getDate')) {
                    endDate.datepicker('setDate', selected.date);
                }
            });

            // Optional: Add logic to ensure the end date is after or equal to the start date
            $('.datepicker[name="end_date"]').on('changeDate', function(selected) {
                var startDate = $('.datepicker[name="start_date"]');
                startDate.datepicker('setEndDate', selected.date);
                if (selected.date < startDate.datepicker('getDate')) {
                    startDate.datepicker('setDate', selected.date);
                }
            });
        });
    </script>
@endsection
@section('stylesheets')
    <style>
        .icon-group {
            display: inline-flex !important;
            align-items: center !important;
        }

        .icon-group svg {
            margin-left: 10px !important;
            /* Adjust spacing as needed */
        }

        .icon-group svg:first-child {
            margin-left: 0 !important;
        }
    </style>
@endsection
