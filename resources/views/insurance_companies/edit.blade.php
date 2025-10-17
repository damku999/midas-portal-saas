@extends('layouts.app')

@section('title', 'Edit Insurance Company')

@section('content')

    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- Insurance Company Form -->
        <div class="card shadow mb-3 mt-2">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">Edit Insurance Company</h6>
                <a href="{{ route('insurance_companies.index') }}" onclick="window.history.go(-1); return false;"
                    class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                    <i class="fas fa-chevron-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <form method="POST" action="{{ route('insurance_companies.update', ['insurance_company' => $insurance_company->id]) }}">
                @csrf
                @method('PUT')
                <div class="card-body py-3">
                    <!-- Section: Insurance Company Information -->
                    <div class="mb-3">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-building me-2"></i>Insurance Company Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Name</label>
                                <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror"
                                    name="name" placeholder="Enter company name" 
                                    value="{{ old('name') ? old('name') : $insurance_company->name }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                                    name="email" placeholder="Enter email address" 
                                    value="{{ old('email') ? old('email') : $insurance_company->email }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mobile Number</label>
                                <input type="tel" class="form-control form-control-sm @error('mobile_number') is-invalid @enderror"
                                    name="mobile_number" placeholder="Enter mobile number" pattern="[0-9+\-\s()]{10,15}"
                                    value="{{ old('mobile_number') ? old('mobile_number') : $insurance_company->mobile_number }}">
                                @error('mobile_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer py-2 bg-light">
                    <div class="d-flex justify-content-end gap-2">
                        <a class="btn btn-secondary btn-sm px-4" href="{{ route('insurance_companies.index') }}">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="fas fa-save me-1"></i>Update Insurance Company
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

@endsection

@section('scripts')
    <script>
        // Initialize Form Validation
        const validator = new FormValidator('form');
        
        // Define validation rules for insurance company edit form
        validator.addRules({
            name: { 
                rules: { required: true, minLength: 2, maxLength: 100 },
                displayName: 'Company Name'
            },
            email: {
                rules: { email: true },
                displayName: 'Email'
            },
            mobile_number: {
                rules: { phone: true },
                displayName: 'Mobile Number'
            },
            status: { 
                rules: { required: true },
                displayName: 'Status'
            }
        });

        // Enable real-time validation
        validator.enableRealTimeValidation();

        // Convert text inputs to uppercase (preserve existing functionality)
        const inputElements = document.querySelectorAll('input[type="text"]');
        function convertToUppercase(event) {
            const input = event.target;
            input.value = input.value.toUpperCase();
        }
        inputElements.forEach(input => {
            input.addEventListener('input', convertToUppercase);
        });
    </script>
@endsection