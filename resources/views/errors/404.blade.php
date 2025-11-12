@php
    // Determine context based on URL and authentication
    $isCentralAdmin = request()->is('midas-admin*');
    $isTenantAdmin = auth()->check() && !$isCentralAdmin && !auth('customer')->check();
    $isCustomerPortal = auth('customer')->check();
@endphp

@if($isCentralAdmin)
    @include('errors.404-central')
@elseif($isTenantAdmin)
    @include('errors.404-tenant')
@elseif($isCustomerPortal)
    @include('errors.404-customer')
@else
    @include('errors.404-public')
@endif
