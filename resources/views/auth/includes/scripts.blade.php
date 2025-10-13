<!-- jQuery (Dynamic) -->
<script src="{{ cdn_url('cdn_jquery_url') }}"></script>

<!-- Bootstrap 5 JS (Dynamic) -->
<script src="{{ cdn_url('cdn_bootstrap_js') }}"></script>

<!-- App core JavaScript-->
<script src="{{ versioned_asset('js/app.js') }}"></script>

<!-- Custom scripts for all pages-->
<script src="{{ versioned_asset('admin/js/sb-admin-2.min.js') }}"></script>

<!-- Modern Auth Enhancements -->
@stack('scripts')