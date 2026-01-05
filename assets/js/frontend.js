/**
 * TableCrafter Frontend Initialization
 * 
 * Automatically finds and initializes all TableCrafter containers
 * based on their ID and data-source attribute.
 */
document.addEventListener('DOMContentLoaded', function () {
    const init = () => {
        // Find all TableCrafter containers
        const containers = document.querySelectorAll('.tablecrafter-container');

        if (containers.length === 0) return;

        // Check if the TableCrafter library is loaded
        if (typeof TableCrafter !== 'undefined') {
            containers.forEach(container => {
                // If already initialized OR hydrating, skip redundant checks
                // but ensure we call the library. The library now handles SSR detection.
                if (container.dataset.tcInitialized === "true" && !container.dataset.ssr) return;

                const source = container.getAttribute('data-source');
                const id = container.getAttribute('id');
                const search = container.getAttribute('data-search') === 'true' || container.getAttribute('data-search') === '1';
                const exportable = container.getAttribute('data-export') === 'true' || container.getAttribute('data-export') === '1';
                const perPage = container.getAttribute('data-per-page') ? parseInt(container.getAttribute('data-per-page')) : 0;

                // Only initialize if both ID and source are present
                if (source && id) {
                    new TableCrafter('#' + id, {
                        data: source,
                        responsive: true,
                        pagination: perPage > 0,
                        pageSize: perPage > 0 ? perPage : 25,
                        globalSearch: search,
                        filterable: search,
                        exportable: exportable,
                        api: {
                            proxy: {
                                url: tablecrafterData.ajaxUrl,
                                nonce: tablecrafterData.nonce
                            }
                        }
                    });
                    container.dataset.tcInitialized = "true";
                }
            });
        } else {
            console.error('TableCrafter: Library (tablecrafter.js) not loaded.');
        }
    };

    // Run now
    init();

    // Also run on a slight delay just in case of race conditions with other scripts
    setTimeout(init, 500);
});
