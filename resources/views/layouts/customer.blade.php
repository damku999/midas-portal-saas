<!DOCTYPE html>
<html lang="en">
{{-- Include Customer Head --}}
@include('common.customer-head')

<body id="page-top">

    <!-- Customer Layout - No Sidebar -->
    <div id="wrapper">

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Customer Header -->
                @include('customer.partials.header')
                <!-- End of Customer Header -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            @auth('customer')
                @include('customer.partials.footer')
            @endauth
            @include('common.footer')
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Customer Logout Modal-->
    @include('customer.partials.logout-modal')

    <!-- Modern Customer Portal JavaScript Bundle (includes Bootstrap 5, jQuery, etc.) -->
    <script src="{{ versioned_asset('js/customer.js') }}"></script>

    <!-- Toastr -->
    <script src="{{ versioned_asset('admin/toastr/toastr.min.js') }}"></script>

    @yield('scripts')
    @stack('scripts')

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
