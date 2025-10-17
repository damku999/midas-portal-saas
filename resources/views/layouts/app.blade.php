<!DOCTYPE html>
<html lang="en">
<script src="{{ cdn_url('cdn_jquery_url') }}" crossorigin="anonymous"></script>
{{-- Include Head --}}
@include('common.head')

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        
        <!-- Mobile Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        @include('common.sidebar')
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                @include('common.header')
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                @yield('content')
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
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

    <!-- Logout Modal-->
    @include('common.logout-modal')

    <!-- Confirmation Modal -->
    @include('common.confirmation-modal')

    <!-- Modern Admin Portal JavaScript Bundle -->
    <script src="{{ versioned_asset('js/admin.js') }}"></script>

    <!-- Confirmation Modal Handler -->
    <script src="{{ versioned_asset('js/confirmation-modal.js') }}"></script>

    <!-- Form Validation Utility Library -->
    <script src="{{ versioned_asset('js/form-validation.js') }}"></script>

    <!-- Bootstrap 5 JS (Dynamic) -->
    <script src="{{ cdn_url('cdn_bootstrap_js') }}"></script>
    <script src="{{ versioned_asset('admin/toastr/toastr.min.js') }}"></script>
    <script src="{{ versioned_asset('js/admin-utils.js') }}"></script>
    <script src="{{ cdn_url('cdn_select2_js') }}"></script>
    <!-- Modern Flatpickr Date Picker (Dynamic) -->
    <script src="{{ cdn_url('cdn_flatpickr_js') }}"></script>
    <script src="{{ cdn_url('cdn_flatpickr_monthselect_js') }}"></script>

    @yield('scripts')
    <script>
        function filterDataAjax(url, search_serialized = null) {
            performAjaxOperation({
                async: true,
                type: "GET",
                url: "{{ config('app.url') }}/" + url,
                data: search_serialized,
                loaderMessage: 'Filtering data...',
                showSuccessNotification: false,
                success: function(res) {
                    $("#list_load").html(res);
                },
                complete: function(result) {
                    // Handle session expiration
                    if (result.responseText == '{"error":"Unauthenticated."}') {
                        show_notification('warning', 'Session expired. Redirecting to login...');
                        setTimeout(() => window.location.href = "login", 2000);
                        return;
                    }

                    // Handle form reset
                    if (search_serialized == '&reset=yes') {
                        if ($('#search_form select[name=product_type]').length) {
                            $('#search_form select[name=product_type]').val('');
                        }
                        if ($('#search_form select[name=packaging_type]').length) {
                            $('#search_form select[name=packaging_type]').val('');
                        }
                        $('.select2').select2().on('select2:close', function name(e) {
                            $(this).valid();
                        });
                    }
                }
            });
        }

        function delete_conf_common(record_id, model, display_title, table_id_or_url = '') {
            $('.module_action').html('Delete');
            $('#module_title').html(" " + display_title);
            table_id_or_url = window.location.href;
            $('#delete-btn').attr('onclick', 'delete_common("' + record_id + '","' + model + '","' + table_id_or_url +
                '","' + display_title + '")');
            showModal('delete_confirm');
            return true;
        }

        function delete_common(record_id, model, table_id_or_url = '', display_title = '') {
            hideModal('delete_confirm');
            
            performAjaxOperation({
                type: "POST",
                url: "{{ route('delete_common') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    record_id: record_id,
                    model: model,
                    table_id_or_url: table_id_or_url,
                    display_title: display_title
                },
                dataType: "json",
                loaderMessage: 'Deleting ' + display_title + '...',
                showSuccessNotification: false,
                success: function(data) {
                    if (data.status == 'success') {
                        show_notification(data.status, data.message);
                        setTimeout(function() {
                            window.location.href = table_id_or_url;
                        }, 1000);
                    } else {
                        show_notification(data.status, data.message);
                    }
                }
            });
        }

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
        $(document).ready(function() {
            // Prevent jQuery datepicker conflicts - provide fallback
            if (typeof $.fn.datepicker === 'undefined') {
                $.fn.datepicker = function(options) {
                    console.warn('jQuery datepicker not available, using Flatpickr fallback');
                    return this.each(function() {
                        if (!$(this).hasClass('flatpickr-input')) {
                            var flatpickrOptions = $.extend({
                                dateFormat: 'd/m/Y',
                                allowInput: true
                            }, options || {});
                            $(this).flatpickr(flatpickrOptions);
                        }
                    });
                };
            }

            // Simple Clean Date Picker
            $('.datepicker').flatpickr({
                dateFormat: 'd/m/Y',
                allowInput: true
            });
            
            // Simple month picker for reports
            $('.datepicker_month').flatpickr({
                plugins: [new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "m/Y",
                    altFormat: "F Y"
                })]
            });

            // Fix menu collapse functionality (jQuery-only implementation)
            $('[data-toggle="collapse"]').on('click', function(e) {
                e.preventDefault();
                var $this = $(this);
                var target = $this.attr('data-target');
                var $target = $(target);

                // Toggle the target element
                if ($target.hasClass('show')) {
                    // Hide the collapse
                    $target.removeClass('show').slideUp(300);
                    $this.addClass('collapsed').attr('aria-expanded', 'false');
                } else {
                    // Show the collapse
                    $target.addClass('show').slideDown(300);
                    $this.removeClass('collapsed').attr('aria-expanded', 'true');
                }

                // Close other open dropdowns (accordion behavior)
                $('[data-toggle="collapse"]').not($this).each(function() {
                    var otherTarget = $(this).attr('data-target');
                    var $otherTarget = $(otherTarget);
                    if ($otherTarget.hasClass('show')) {
                        $otherTarget.removeClass('show').slideUp(300);
                        $(this).addClass('collapsed').attr('aria-expanded', 'false');
                    }
                });
            });

            // Bootstrap 5 collapse fix (for data-bs-toggle)
            $('[data-bs-toggle="collapse"]').on('click', function(e) {
                e.preventDefault();
                var $this = $(this);
                var target = $this.attr('href') || $this.attr('data-bs-target');
                var $target = $(target);
                var $chevron = $this.find('.fa-chevron-down, .fa-chevron-up');

                // Toggle the target element
                if ($target.hasClass('show')) {
                    $target.removeClass('show').slideUp(300);
                    $this.attr('aria-expanded', 'false');
                    $chevron.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                } else {
                    $target.addClass('show').slideDown(300);
                    $this.attr('aria-expanded', 'true');
                    $chevron.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                }
            });

            // =======================================================
            // CENTRALIZED MODAL UTILITY FUNCTIONS (jQuery-only)
            // =======================================================
            
            // Generic Modal Functions
            window.showModal = function(modalId) {
                $('#' + modalId).css('display', 'block').addClass('show');
                $('body').addClass('modal-open');
                $('.modal-backdrop').remove();
                $('body').append('<div class="modal-backdrop fade show"></div>');
            };

            window.hideModal = function(modalId) {
                $('#' + modalId).css('display', 'none').removeClass('show');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            };

            // WhatsApp Modal Functions 
            window.showSendWhatsAppModal = function(quotationId) {
                var modalId = quotationId ? 'sendWhatsAppModal' + quotationId : 'sendWhatsAppModal';
                showModal(modalId);
            };

            window.showResendWhatsAppModal = function(quotationId) {
                var modalId = quotationId ? 'resendWhatsAppModal' + quotationId : 'resendWhatsAppModal';
                showModal(modalId);
            };

            window.hideWhatsAppModal = function(modalId) {
                hideModal(modalId);
            };

            // Delete Modal Functions
            window.showDeleteQuotationModal = function(quotationId) {
                var modalId = quotationId ? 'deleteQuotationModal' + quotationId : 'deleteQuotationModal';
                showModal(modalId);
            };

            window.hideDeleteModal = function(modalId) {
                hideModal(modalId);
            };

            // Logout Modal Functions
            window.showLogoutModal = function() {
                showModal('logoutModal');
            };

            window.hideLogoutModal = function() {
                hideModal('logoutModal');
            };

            // Global Delete Confirmation Modal Functions
            window.showDeleteConfirmModal = function() {
                showModal('delete_confirm');
            };

            window.hideDeleteConfirmModal = function() {
                hideModal('delete_confirm');
            };

            // =======================================================
            // LOADING SPINNER UTILITIES 
            // =======================================================
            
            window.showLoading = function(message = 'Loading...') {
                $('#cover-spin .sr-only').text(message);
                $('#cover-spin').show();
            };

            window.hideLoading = function() {
                $('#cover-spin').hide();
            };

            // Enhanced AJAX operations with loading states
            window.performAjaxOperation = function(options) {
                const defaults = {
                    showLoader: true,
                    loaderMessage: 'Processing...',
                    showSuccessNotification: true,
                    showErrorNotification: true
                };
                options = $.extend(defaults, options);
                
                if (options.showLoader) showLoading(options.loaderMessage);
                
                return $.ajax(options)
                    .done(function(response) {
                        if (options.showSuccessNotification && response.message) {
                            show_notification('success', response.message);
                        }
                    })
                    .fail(function(xhr) {
                        if (options.showErrorNotification) {
                            const errorMessage = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                            show_notification('error', errorMessage);
                        }
                    })
                    .always(function() {
                        if (options.showLoader) hideLoading();
                    });
            };

            // Global Modal Event Handlers
            $(document).on('click', '.modal-backdrop', function() {
                // Close all visible modals
                $('.modal.show').each(function() {
                    hideModal(this.id);
                });
            });

            // Close modals on Escape key
            $(document).keydown(function(e) {
                if (e.keyCode === 27) { // ESC key
                    $('.modal.show').each(function() {
                        hideModal(this.id);
                    });
                }
            });

            // Initialize Bootstrap 5 dropdowns manually for compatibility
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });

            // Additional fix for dropdown functionality
            $(document).on('click', '.dropdown-toggle', function(e) {
                e.preventDefault();
                var dropdown = bootstrap.Dropdown.getInstance(this) || new bootstrap.Dropdown(this);
                dropdown.toggle();
            });

            // Specific function for user dropdown toggle
            window.toggleUserDropdown = function(event) {
                event.preventDefault();
                event.stopPropagation();
                const userDropdown = document.getElementById('userDropdown');
                const dropdown = bootstrap.Dropdown.getInstance(userDropdown) || new bootstrap.Dropdown(userDropdown);
                dropdown.toggle();
            };

            // Alternative fallback for dropdowns that might fail
            $(document).on('click', '#userDropdown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const $dropdown = $(this).next('.dropdown-menu');
                if ($dropdown.hasClass('show')) {
                    $dropdown.removeClass('show');
                    $(this).attr('aria-expanded', 'false');
                } else {
                    // Close any other open dropdowns first
                    $('.dropdown-menu.show').removeClass('show');
                    $('[aria-expanded="true"]').attr('aria-expanded', 'false');
                    // Show this dropdown
                    $dropdown.addClass('show');
                    $(this).attr('aria-expanded', 'true');
                }
            });

            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown-menu.show').removeClass('show');
                    $('[aria-expanded="true"]').attr('aria-expanded', 'false');
                }
            });

            // =======================================================
            // GLOBAL AJAX ERROR HANDLING & SETUP
            // =======================================================
            
            // Global AJAX setup for better error handling
            $.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    // Add CSRF token to all requests
                    if (!settings.crossDomain) {
                        xhr.setRequestHeader('X-CSRF-Token', $('meta[name="csrf-token"]').attr('content'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.status, error);
                    
                    // Handle common HTTP errors
                    switch(xhr.status) {
                        case 401:
                            show_notification('error', 'Unauthorized access. Please log in again.');
                            setTimeout(() => window.location.href = '/login', 2000);
                            break;
                        case 403:
                            show_notification('error', 'You do not have permission to perform this action.');
                            break;
                        case 404:
                            show_notification('error', 'The requested resource was not found.');
                            break;
                        case 419:
                            show_notification('error', 'Session expired. Please refresh the page.');
                            break;
                        case 422:
                            // Validation errors - handle in specific contexts
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errorMessages = Object.values(xhr.responseJSON.errors).flat();
                                show_notification('error', errorMessages.join('<br>'));
                            } else {
                                show_notification('error', 'Validation failed. Please check your input.');
                            }
                            break;
                        case 500:
                            show_notification('error', 'Server error occurred. Please try again or contact support.');
                            break;
                        default:
                            if (xhr.status >= 400) {
                                const message = xhr.responseJSON?.message || 'An unexpected error occurred.';
                                show_notification('error', message);
                            }
                    }
                    
                    // Always hide loading spinner on error
                    hideLoading();
                }
            });

            // Enhanced notification function with auto-dismiss and better styling
            window.showEnhancedNotification = function(type, message, options = {}) {
                const defaults = {
                    autoDismiss: true,
                    timeout: type === 'error' ? 8000 : 5000,
                    position: 'top-right',
                    closeButton: true
                };
                options = $.extend(defaults, options);
                
                toastr.options = {
                    "closeButton": options.closeButton,
                    "debug": false,
                    "newestOnTop": true,
                    "progressBar": true,
                    "positionClass": `toast-${options.position}`,
                    "preventDuplicates": true,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": options.autoDismiss ? options.timeout : "0",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                
                toastr[type](message);
            };

            // =======================================================
            // SIDEBAR TOGGLE FUNCTIONALITY (Mobile + Desktop)
            // =======================================================
            
            // Unified Sidebar Toggle Functions
            window.toggleSidebar = function() {
                const sidebar = $('#accordionSidebar');
                const overlay = $('#sidebarOverlay');
                const wrapper = $('#wrapper');
                const contentWrapper = $('#content-wrapper');
                
                if ($(window).width() <= 768) {
                    // Mobile behavior - show/hide sidebar with overlay
                    if (sidebar.hasClass('show')) {
                        sidebar.removeClass('show');
                        overlay.removeClass('show');
                        $('body').removeClass('sidebar-open');
                    } else {
                        sidebar.addClass('show');
                        overlay.addClass('show');
                        $('body').addClass('sidebar-open');
                    }
                } else {
                    // Desktop behavior - collapse/expand sidebar
                    if (sidebar.hasClass('toggled')) {
                        sidebar.removeClass('toggled');
                        wrapper.removeClass('sidebar-toggled');
                        contentWrapper.removeClass('sidebar-toggled');
                    } else {
                        sidebar.addClass('toggled');
                        wrapper.addClass('sidebar-toggled');
                        contentWrapper.addClass('sidebar-toggled');
                    }
                }
            };
            
            window.hideSidebar = function() {
                const sidebar = $('#accordionSidebar');
                const overlay = $('#sidebarOverlay');
                const wrapper = $('#wrapper');
                const contentWrapper = $('#content-wrapper');
                
                // Mobile: hide sidebar
                sidebar.removeClass('show');
                overlay.removeClass('show');
                $('body').removeClass('sidebar-open');
                
                // Desktop: keep current state (don't auto-collapse)
            };
            
            // Sidebar Event Handlers (Bootstrap-compatible)
            $('#sidebarToggleTop').on('click', function(e) {
                e.preventDefault();
                toggleSidebar();
            });
            
            // Close sidebar when clicking overlay (mobile only)
            $('#sidebarOverlay').on('click', function() {
                if ($(window).width() <= 768) {
                    hideSidebar();
                }
            });
            
            // Handle window resize - clean up mobile states on desktop
            $(window).on('resize', function() {
                if ($(window).width() > 768) {
                    // Clean up mobile states when switching to desktop
                    $('#accordionSidebar').removeClass('show');
                    $('#sidebarOverlay').removeClass('show');
                    $('body').removeClass('sidebar-open');
                }
            });
            
            // Close sidebar when clicking outside on mobile only
            $(document).on('click', function(e) {
                if ($(window).width() <= 768) {
                    const sidebar = $('#accordionSidebar');
                    const toggle = $('#sidebarToggleTop');
                    
                    // Check if click is outside sidebar and toggle button
                    if (!sidebar.is(e.target) && sidebar.has(e.target).length === 0 && 
                        !toggle.is(e.target) && toggle.has(e.target).length === 0 && 
                        sidebar.hasClass('show')) {
                        hideSidebar();
                    }
                }
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
