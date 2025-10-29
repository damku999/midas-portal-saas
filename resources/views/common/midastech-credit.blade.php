<!-- Professional Footer Credit -->
<div class="text-center mt-4">
    <p class="mb-0 text-muted small">
        @if(show_footer_year())
            {{ footer_copyright_text() }} &copy; {{ date('Y') }}
        @else
            {{ footer_copyright_text() }}
        @endif
        @if(show_footer_developer())
            | Developed by
            <a href="{{ footer_developer_url() }}" target="_blank" class="text-decoration-none fw-medium" style="color: #2563eb;">
                <i class="fas fa-code me-1"></i>{{ footer_developer_name() }}
            </a>
        @endif
    </p>
</div>