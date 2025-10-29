<!-- Professional Footer Credit -->
<div class="row justify-content-center mt-4">
    <div class="col-md-8 col-lg-6 col-xl-5">
        <div class="text-center">
            <p class="mb-0 text-muted small">
                @if(show_footer_year())
                    {{ footer_copyright_text() }} - {{ date('Y') }}
                @else
                    {{ footer_copyright_text() }}
                @endif
                @if(show_footer_developer())
                    | Developed by
                    <a href="{{ footer_developer_url() }}" target="_blank" class="text-decoration-none fw-medium" style="color: #2563eb;">
                        <i class="fas fa-globe me-1"></i>{{ footer_developer_name() }}
                    </a>
                @endif
            </p>
        </div>
    </div>
</div>