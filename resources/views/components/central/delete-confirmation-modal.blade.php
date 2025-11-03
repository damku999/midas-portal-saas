@props([
    'modalId' => 'deleteConfirmationModal',
    'formId' => 'deleteTenantForm',
    'confirmationText' => '',
    'itemType' => 'Tenant',
])

<!-- Delete Confirmation Modal with Input -->
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete {{ $itemType }} - Final Warning
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3">
                    <strong>Warning!</strong> This action CANNOT be undone!
                </div>

                <p class="mb-3 fw-bold">What will be deleted:</p>

                <!-- Always deleted items -->
                <div class="alert alert-info mb-3">
                    <strong>Automatically Deleted (always):</strong>
                    <ul class="mb-0 mt-2">
                        <li>Subscription & billing records</li>
                        <li>Domain configurations</li>
                    </ul>
                </div>

                <p class="mb-2"><strong>Optional deletion items:</strong></p>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="{{ $modalId }}DeleteDatabase" name="delete_database" value="1" checked>
                    <label class="form-check-label" for="{{ $modalId }}DeleteDatabase">
                        <strong>Tenant Database</strong> - All customer data, policies, and transactions
                    </label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="{{ $modalId }}DeleteFiles" name="delete_files" value="1" checked>
                    <label class="form-check-label" for="{{ $modalId }}DeleteFiles">
                        <strong>Files & Uploads</strong> - Documents, images, and attachments
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="{{ $modalId }}DeleteSubscription" name="delete_subscription" value="1" checked>
                    <label class="form-check-label" for="{{ $modalId }}DeleteSubscription">
                        <strong>Audit Logs</strong> - Activity history and audit trail
                    </label>
                </div>

                <hr class="my-3">

                <p class="mb-3">To confirm deletion, please type:</p>
                <p class="mb-2"><strong><code>DELETE {{ strtoupper($confirmationText) }}</code></strong></p>
                <input type="text" class="form-control" id="{{ $modalId }}Input" placeholder="Type here to confirm..." autocomplete="off">
                <small class="text-muted">This confirmation is case-sensitive</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="{{ $modalId }}ConfirmBtn" disabled>
                    <i class="fas fa-trash-alt me-2"></i>Yes, Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const modalId = '{{ $modalId }}';
    const formId = '{{ $formId }}';
    const confirmationText = @json($confirmationText);
    const expectedConfirmation = 'DELETE ' + confirmationText.toUpperCase();

    const modalElement = document.getElementById(modalId);
    const inputElement = document.getElementById(modalId + 'Input');
    const confirmBtn = document.getElementById(modalId + 'ConfirmBtn');
    const formElement = document.getElementById(formId);

    if (!modalElement || !inputElement || !confirmBtn || !formElement) {
        console.error('Delete confirmation modal elements not found');
        return;
    }

    // Enable delete button only when confirmation text matches
    inputElement.addEventListener('input', function(e) {
        const input = e.target.value;

        if (input === expectedConfirmation) {
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('btn-secondary');
            confirmBtn.classList.add('btn-danger');
        } else {
            confirmBtn.disabled = true;
            confirmBtn.classList.remove('btn-danger');
            confirmBtn.classList.add('btn-secondary');
        }
    });

    // Handle delete confirmation
    confirmBtn.addEventListener('click', function() {
        const input = inputElement.value;

        if (input === expectedConfirmation) {
            // Add hidden input with confirmation text to form
            const confirmationInput = document.createElement('input');
            confirmationInput.type = 'hidden';
            confirmationInput.name = 'confirmation';
            confirmationInput.value = input;
            formElement.appendChild(confirmationInput);

            // Add checkbox values as hidden inputs
            const checkboxes = [
                { id: modalId + 'DeleteDatabase', name: 'delete_database' },
                { id: modalId + 'DeleteFiles', name: 'delete_files' },
                { id: modalId + 'DeleteDomains', name: 'delete_domains' },
                { id: modalId + 'DeleteSubscription', name: 'delete_subscription' }
            ];

            checkboxes.forEach(function(checkbox) {
                const element = document.getElementById(checkbox.id);
                if (element && element.checked) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = checkbox.name;
                    hiddenInput.value = '1';
                    formElement.appendChild(hiddenInput);
                }
            });

            // Submit form
            formElement.submit();

            // Close modal
            bootstrap.Modal.getInstance(modalElement).hide();
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error('Confirmation text does not match!');
            } else {
                alert('Confirmation text does not match!');
            }
        }
    });

    // Reset modal state when closed
    modalElement.addEventListener('hidden.bs.modal', function() {
        inputElement.value = '';
        confirmBtn.disabled = true;
        confirmBtn.classList.remove('btn-danger');
        confirmBtn.classList.add('btn-secondary');

        // Reset all checkboxes to checked
        document.getElementById(modalId + 'DeleteDatabase').checked = true;
        document.getElementById(modalId + 'DeleteFiles').checked = true;
        document.getElementById(modalId + 'DeleteDomains').checked = true;
        document.getElementById(modalId + 'DeleteSubscription').checked = true;
    });
})();
</script>
@endpush
