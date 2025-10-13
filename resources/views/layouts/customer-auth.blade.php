<!DOCTYPE html>
<html lang="en">

{{-- Clean Head for Customer Auth --}}
@include('common.customer-head')

<body>

    {{-- Content Goes Here FOR Customer Auth --}}
    @yield('content')

    {{-- Scripts for Customer Auth --}}
    <!-- Modern Customer Portal JavaScript Bundle -->
    <script src="{{ versioned_asset('js/customer.js') }}"></script>

    <!-- Toastr for notifications -->
    <script src="{{ versioned_asset('admin/toastr/toastr.min.js') }}"></script>

    @yield('scripts')

    <script>
        function show_notification(type, message) {
            if (type == 'success') {
                toastr.success(message);
            } else if (type == 'error') {
                toastr.error(message);
            } else if (type == 'warning') {
                toastr.warning(message);
            } else if (type == 'information') {
                toastr.info(message);
            }
        }

        // Show session messages as notifications
        @if (session('message'))
            show_notification('success', '{{ session('message') }}');
        @endif

        @if (session('error'))
            show_notification('error', '{{ session('error') }}');
        @endif

        @if (session('info'))
            show_notification('info', '{{ session('info') }}');
        @endif
    </script>

</body>

</html>