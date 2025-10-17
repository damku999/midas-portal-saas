/**
 * Confirmation Modal Handler
 * Provides a reusable confirmation modal for all action confirmations
 * Replaces JavaScript confirm() dialogs with Bootstrap modals
 */

(function($) {
    'use strict';

    // Initialize on document ready
    $(document).ready(function() {
        initializeConfirmationModal();
    });

    function initializeConfirmationModal() {
        // Handle all links with confirmation modal data attributes
        $(document).on('click', '[data-bs-toggle="modal"][data-bs-target="#confirmationModal"]', function(e) {
            e.preventDefault();

            const $trigger = $(this);

            // Extract data attributes
            const title = $trigger.data('title') || 'Confirm Action';
            const message = $trigger.data('message') || 'Are you sure you want to proceed with this action?';
            const confirmText = $trigger.data('confirm-text') || 'Confirm';
            const cancelText = $trigger.data('cancel-text') || 'Cancel';
            const confirmClass = $trigger.data('confirm-class') || 'btn-danger';
            const actionUrl = $trigger.data('action-url') || $trigger.attr('href');
            const method = $trigger.data('method') || 'GET';

            // Populate modal content
            $('#confirmationModalTitle').text(title);
            $('#confirmationModalMessage').html(message);
            $('#confirmationConfirmText').text(confirmText);
            $('#confirmationCancelText').text(cancelText);

            // Update confirm button styling
            const $confirmBtn = $('#confirmationConfirmBtn');
            $confirmBtn.removeClass('btn-danger btn-warning btn-success btn-primary btn-info')
                       .addClass(confirmClass);

            // Store action details in modal for confirmation handler
            $('#confirmationModal').data({
                'actionUrl': actionUrl,
                'method': method
            });

            // Show modal using Bootstrap 5
            const modalElement = document.getElementById('confirmationModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        });

        // Handle form submissions with confirmation modal
        $(document).on('submit', '[data-confirm-submit="true"]', function(e) {
            e.preventDefault();

            const $form = $(this);

            // Extract data attributes
            const title = $form.data('title') || 'Confirm Action';
            const message = $form.data('message') || 'Are you sure you want to proceed with this action?';
            const confirmText = $form.data('confirm-text') || 'Confirm';
            const cancelText = $form.data('cancel-text') || 'Cancel';
            const confirmClass = $form.data('confirm-class') || 'btn-danger';

            // Populate modal content
            $('#confirmationModalTitle').text(title);
            $('#confirmationModalMessage').html(message);
            $('#confirmationConfirmText').text(confirmText);
            $('#confirmationCancelText').text(cancelText);

            // Update confirm button styling
            const $confirmBtn = $('#confirmationConfirmBtn');
            $confirmBtn.removeClass('btn-danger btn-warning btn-success btn-primary btn-info')
                       .addClass(confirmClass);

            // Store form reference in modal for confirmation handler
            $('#confirmationModal').data({
                'targetForm': $form,
                'actionUrl': null,
                'method': 'FORM'
            });

            // Show modal using Bootstrap 5
            const modalElement = document.getElementById('confirmationModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        });

        // Handle confirmation button click
        $(document).on('click', '#confirmationConfirmBtn', function(e) {
            e.preventDefault();

            const $modal = $('#confirmationModal');
            const method = $modal.data('method');
            const actionUrl = $modal.data('actionUrl');
            const $targetForm = $modal.data('targetForm');
            const onConfirm = $modal.data('onConfirm');

            // Disable confirm button and show loading state
            const $confirmBtn = $(this);
            const originalText = $('#confirmationConfirmText').text();
            $confirmBtn.prop('disabled', true);
            $('#confirmationConfirmText').html('<i class="fas fa-spinner fa-spin me-1"></i>Processing...');

            // Check if custom onConfirm callback exists
            if (typeof onConfirm === 'function') {
                // Close modal first
                const modalElement = document.getElementById('confirmationModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }

                // Execute callback
                onConfirm();

                // Reset button state
                setTimeout(function() {
                    $confirmBtn.prop('disabled', false);
                    $('#confirmationConfirmText').text(originalText);
                }, 300);
            } else if (method === 'FORM' && $targetForm) {
                // Submit the original form
                $targetForm.off('submit').submit();
            } else if (method === 'GET') {
                // Simple GET request - navigate to URL
                window.location.href = actionUrl;
            } else {
                // POST/DELETE request - submit hidden form
                const $form = $('#confirmationForm');
                $form.attr('action', actionUrl);
                $('#confirmationMethod').val(method);
                $form.submit();
            }

            // Close modal after a brief delay to show loading state (for non-callback actions)
            if (typeof onConfirm !== 'function') {
                setTimeout(function() {
                    const modalElement = document.getElementById('confirmationModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }

                    // Reset button state
                    $confirmBtn.prop('disabled', false);
                    $('#confirmationConfirmText').text(originalText);
                }, 300);
            }
        });

        // Reset modal state when closed
        $('#confirmationModal').on('hidden.bs.modal', function() {
            // Clear stored data
            $(this).removeData();

            // Reset button state
            $('#confirmationConfirmBtn').prop('disabled', false);

            // Reset button classes to default
            $('#confirmationConfirmBtn').removeClass('btn-warning btn-success btn-primary btn-info')
                                       .addClass('btn-danger');
        });
    }

    // Global function to programmatically show confirmation modal
    window.showConfirmationModal = function(options) {
        const defaults = {
            title: 'Confirm Action',
            message: 'Are you sure you want to proceed with this action?',
            confirmText: 'Confirm',
            cancelText: 'Cancel',
            confirmClass: 'btn-danger',
            actionUrl: '#',
            method: 'GET',
            onConfirm: null
        };

        const settings = $.extend({}, defaults, options);

        // Populate modal
        $('#confirmationModalTitle').text(settings.title);
        $('#confirmationModalMessage').html(settings.message);
        $('#confirmationConfirmText').text(settings.confirmText);
        $('#confirmationCancelText').text(settings.cancelText);

        // Update button styling
        const $confirmBtn = $('#confirmationConfirmBtn');
        $confirmBtn.removeClass('btn-danger btn-warning btn-success btn-primary btn-info')
                   .addClass(settings.confirmClass);

        // Store action details
        $('#confirmationModal').data({
            'actionUrl': settings.actionUrl,
            'method': settings.method,
            'onConfirm': settings.onConfirm
        });

        // Show modal
        const modalElement = document.getElementById('confirmationModal');
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    };

})(jQuery);
