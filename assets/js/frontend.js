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
                // If already initialized, skip
                if (container.dataset.tcInitialized) return;

                const source = container.getAttribute('data-source');
                const id = container.getAttribute('id');

                // Only initialize if both ID and source are present
                if (source && id) {
                    new TableCrafter('#' + id, {
                        data: source,
                        responsive: true,
                        pagination: true,
                        pageSize: container.dataset.perPage ? parseInt(container.dataset.perPage) : 10,
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
