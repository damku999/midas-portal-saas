// Fix submenu collapse/expand toggle
document.addEventListener('DOMContentLoaded', function() {
    // Get all submenu toggle links
    const submenuToggles = document.querySelectorAll('[data-bs-toggle="collapse"]');

    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();

            // Get the target collapse element
            const targetId = this.getAttribute('data-bs-target');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                // Get Bootstrap collapse instance or create new one
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(targetElement);

                // Toggle the collapse
                bsCollapse.toggle();

                // Toggle chevron icon
                const chevron = this.querySelector('.fa-chevron-down, .fa-chevron-up');
                if (chevron) {
                    if (targetElement.classList.contains('show')) {
                        chevron.classList.remove('fa-chevron-down');
                        chevron.classList.add('fa-chevron-up');
                    } else {
                        chevron.classList.remove('fa-chevron-up');
                        chevron.classList.add('fa-chevron-down');
                    }
                }
            }
        });
    });

    // Update chevron icons based on initial state
    document.querySelectorAll('.collapse').forEach(collapse => {
        collapse.addEventListener('shown.bs.collapse', function() {
            const toggleLink = document.querySelector(`[data-bs-target="#${this.id}"]`);
            if (toggleLink) {
                const chevron = toggleLink.querySelector('.fa-chevron-down, .fa-chevron-up');
                if (chevron) {
                    chevron.classList.remove('fa-chevron-down');
                    chevron.classList.add('fa-chevron-up');
                }
            }
        });

        collapse.addEventListener('hidden.bs.collapse', function() {
            const toggleLink = document.querySelector(`[data-bs-target="#${this.id}"]`);
            if (toggleLink) {
                const chevron = toggleLink.querySelector('.fa-chevron-down, .fa-chevron-up');
                if (chevron) {
                    chevron.classList.remove('fa-chevron-up');
                    chevron.classList.add('fa-chevron-down');
                }
            }
        });
    });
});
