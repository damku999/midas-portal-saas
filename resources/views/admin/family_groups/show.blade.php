@extends('layouts.app')

@section('title', 'Family Group Details')

@section('content')
<div class="container-fluid">

    <div class="row">
        <!-- Family Group Information -->
        <div class="col-lg-8">
            <!-- Basic Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Family Group: {{ $familyGroup->name }}</h6>
                    <div class="d-flex">
                        <a href="{{ route('family_groups.edit', $familyGroup) }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit me-1"></i> Edit Group
                        </a>
                        <a href="{{ route('family_groups.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">Family ID:</td>
                                    <td>{{ $familyGroup->id }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Family Name:</td>
                                    <td>{{ $familyGroup->name }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Status:</td>
                                    <td>
                                        @if($familyGroup->status)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">Total Members:</td>
                                    <td>{{ $familyGroup->familyMembers->count() }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Created Date:</td>
                                    <td>{{ formatDateForUi($familyGroup->created_at, 'd M Y, h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Last Updated:</td>
                                    <td>{{ formatDateForUi($familyGroup->updated_at, 'd M Y, h:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Family Head Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-crown text-warning mr-2"></i>Family Head
                    </h6>
                </div>
                <div class="card-body">
                    @if($familyGroup->familyHead)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <img class="img-profile rounded-circle mb-2" 
                                         src="{{ asset('admin/img/undraw_profile.svg') }}" 
                                         style="width: 80px; height: 80px;">
                                    <div class="badge badge-success">Family Head</div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="font-weight-bold">Name:</td>
                                        <td>{{ $familyGroup->familyHead->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Email:</td>
                                        <td>{{ $familyGroup->familyHead->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Mobile:</td>
                                        <td>{{ $familyGroup->familyHead->mobile_number ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Date of Birth:</td>
                                        <td>{{ $familyGroup->familyHead->date_of_birth ? formatDateForUi($familyGroup->familyHead->date_of_birth) : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Status:</td>
                                        <td>
                                            @if($familyGroup->familyHead->status)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No family head assigned to this group.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Family Members -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-users mr-2"></i>All Family Members ({{ $familyGroup->familyMembers->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($familyGroup->familyMembers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Relationship</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($familyGroup->familyMembers as $member)
                                    <tr class="{{ $member->is_head ? 'table-warning' : '' }}">
                                        <td>
                                            <strong>{{ $member->customer->name }}</strong>
                                            @if($member->is_head)
                                                <i class="fas fa-crown text-warning ml-2" title="Family Head"></i>
                                            @endif
                                        </td>
                                        <td>{{ $member->customer->email }}</td>
                                        <td>{{ $member->customer->mobile_number ?? 'N/A' }}</td>
                                        <td>
                                            @if($member->relationship)
                                                <span class="badge badge-info">{{ ucfirst($member->relationship) }}</span>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($member->is_head)
                                                <span class="badge badge-warning">Head</span>
                                            @else
                                                <span class="badge badge-secondary">Member</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($member->customer->status)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$member->is_head)
                                                <form method="POST"
                                                      action="{{ route('family_groups.member.remove', $member) }}"
                                                      style="display: inline;"
                                                      data-confirm-submit="true"
                                                      data-title="Confirm Member Removal"
                                                      data-message="Are you sure you want to remove <strong>{{ $member->customer->name }}</strong> from this family?<br><small class='text-muted'>This will reset their login credentials.</small>"
                                                      data-confirm-text="Yes, Remove"
                                                      data-confirm-class="btn-danger">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Member">
                                                        <i class="fas fa-user-minus"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted" title="Cannot remove family head">
                                                    <i class="fas fa-lock"></i>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            No family members found in this group.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-right">
                                <h4 class="text-primary">{{ $familyGroup->familyMembers->count() }}</h4>
                                <small class="text-muted">Total Members</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $familyGroup->familyMembers->where('customer.status', true)->count() }}</h4>
                            <small class="text-muted">Active Members</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('family_groups.edit', $familyGroup) }}" class="btn btn-warning btn-sm btn-block">
                            <i class="fas fa-edit"></i> Edit Family Group
                        </a>
                        
                        @if($familyGroup->status)
                            <a href="{{ route('family_groups.status', [$familyGroup->id, 0]) }}" 
                               class="btn btn-outline-danger btn-sm btn-block">
                                <i class="fas fa-pause"></i> Deactivate Group
                            </a>
                        @else
                            <a href="{{ route('family_groups.status', [$familyGroup->id, 1]) }}" 
                               class="btn btn-outline-success btn-sm btn-block">
                                <i class="fas fa-play"></i> Activate Group
                            </a>
                        @endif

                        <form method="POST"
                              action="{{ route('family_groups.destroy', $familyGroup) }}"
                              data-confirm-submit="true"
                              data-title="Confirm Group Deletion"
                              data-message="Are you sure you want to delete <strong>{{ $familyGroup->name }}</strong>?<br><small class='text-muted'>This will remove all family associations. This action cannot be undone.</small>"
                              data-confirm-text="Yes, Delete Group"
                              data-confirm-class="btn-danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm btn-block">
                                <i class="fas fa-trash"></i> Delete Group
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Information</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small">
                        <li><i class="fas fa-info-circle text-info mr-2"></i>Family head can view all family member policies</li>
                        <li><i class="fas fa-info-circle text-info mr-2"></i>Other members can only view their own policies</li>
                        <li><i class="fas fa-info-circle text-info mr-2"></i>Inactive groups prevent customer login</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection