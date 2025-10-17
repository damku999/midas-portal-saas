{{-- Reusable Confirmation Modal Component --}}
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    <span id="confirmationModalTitle">Confirm Action</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmationModalMessage" class="mb-0">Are you sure you want to proceed with this action?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="confirmationCancelBtn">
                    <i class="fas fa-times me-1"></i>
                    <span id="confirmationCancelText">Cancel</span>
                </button>
                <button type="button" class="btn btn-danger" id="confirmationConfirmBtn">
                    <i class="fas fa-check me-1"></i>
                    <span id="confirmationConfirmText">Confirm</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Hidden form for POST/DELETE submissions --}}
<form id="confirmationForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="_method" id="confirmationMethod" value="POST">
</form>
