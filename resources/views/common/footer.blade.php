<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>
                {{ footer_copyright_text() }}@if(show_footer_year()) &copy; {{ date('Y') }}@endif
                @if(show_footer_developer())
                    | Developed by
                    <a href="{{ footer_developer_url() }}" target="_blank" class="text-decoration-none fw-medium" style="color: #2563eb;">
                        <i class="fas fa-globe me-1"></i>{{ footer_developer_name() }}
                    </a>
                @endif
            </span>
        </div>
    </div>
</footer>
<div class="modal fade" id="delete_confirm">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    <span class="module_action">Delete</span> Confirmation
                </h5>
                <button type="button" class="btn-close" onclick="hideModal('delete_confirm')" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <span class='module_action'>Delete</span> this <span
                        id="module_title"></span>?</p><br>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="delete-btn">
                    <span class="module_action">Delete</span>
                </button>
                <button type="button" class="btn btn-default btn-sm"
                    onclick="hideModal('delete_confirm')">Close</button>
            </div>
        </div>
    </div>
</div>
