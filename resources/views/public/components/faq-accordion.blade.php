{{--
    Reusable FAQ Accordion Component

    Props:
    - $faqs: Array of FAQs with 'question' and 'answer' keys (required)
    - $accordionId: Unique accordion ID (optional, default: 'faqAccordion')
    - $showFirst: Show first item by default (optional, default: true)
--}}

<div class="accordion" id="{{ $accordionId ?? 'faqAccordion' }}">
    @foreach($faqs as $index => $faq)
    <div class="accordion-item modern-card mb-3 scroll-reveal hover-lift delay-{{ $index * 100 }}">
        <h3 class="accordion-header">
            <button class="accordion-button {{ ($index === 0 && ($showFirst ?? true)) ? '' : 'collapsed' }} fw-semibold"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $accordionId ?? 'faqAccordion' }}-{{ $index }}"
                    aria-expanded="{{ ($index === 0 && ($showFirst ?? true)) ? 'true' : 'false' }}">
                {{ $faq['question'] }}
            </button>
        </h3>
        <div id="{{ $accordionId ?? 'faqAccordion' }}-{{ $index }}"
             class="accordion-collapse collapse {{ ($index === 0 && ($showFirst ?? true)) ? 'show' : '' }}"
             data-bs-parent="#{{ $accordionId ?? 'faqAccordion' }}">
            <div class="accordion-body">
                {{ $faq['answer'] }}
            </div>
        </div>
    </div>
    @endforeach
</div>

@push('styles')
<style>
    .accordion-button:not(.collapsed) {
        background-color: rgba(23, 182, 182, 0.1);
        color: #17b6b6;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: #17b6b6;
    }

    .accordion-item {
        border: none !important;
    }
</style>
@endpush
