@extends('layouts.app')

@section('title', 'Protected Record')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-warning">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-shield-alt"></i> Protected Record
                    </h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-shield-alt fa-5x text-warning"></i>
                    </div>

                    <div class="alert alert-warning" role="alert">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-triangle"></i> Operation Not Allowed
                        </h5>
                        <p class="mb-0">{{ $message ?? 'This record is protected and cannot be modified.' }}</p>
                    </div>

                    @if(isset($action))
                    <div class="alert alert-info">
                        <strong>Attempted Action:</strong> {{ ucfirst(str_replace('_', ' ', $action)) }}
                    </div>
                    @endif

                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-info-circle"></i> Why is this record protected?</h6>
                            <p class="card-text mb-2">
                                This record is protected because it is critical to the system's operation.
                                Protected records include:
                            </p>
                            <ul class="mb-0">
                                <li>System administrator accounts</li>
                                <li>Webmonks domain accounts (*@webmonks.in)</li>
                                <li>Critical business records</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <a href="{{ url()->previous() }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </a>
                        <a href="{{ route('home') ?? '/' }}" class="btn btn-secondary">
                            <i class="fas fa-home"></i> Go to Dashboard
                        </a>
                    </div>

                    <div class="mt-4 text-muted small text-center">
                        <p class="mb-0">
                            <i class="fas fa-lock"></i> This incident has been logged for security purposes.
                        </p>
                        <p class="mb-0">
                            If you believe this is an error, please contact your system administrator.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card.border-warning {
    border-width: 2px;
}
.fa-5x {
    font-size: 5em;
}
</style>
@endpush
