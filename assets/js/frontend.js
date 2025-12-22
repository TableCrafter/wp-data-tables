/**
 * TableCrafter Frontend Initialization
 * 
 * Automatically finds and initializes all TableCrafter containers
 * based on their ID and data-source attribute.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Check if the TableCrafter library is loaded
    if (typeof TableCrafter !== 'undefined') {
        // Find all TableCrafter containers
        const containers = document.querySelectorAll('.tablecrafter-container');

        containers.forEach(container => {
            const source = container.getAttribute('data-source');
            const id = container.getAttribute('id');

            // Only initialize if both ID and source are present
            if (source && id) {
                new TableCrafter({
                    selector: '#' + id,
                    source: source
                });
            }
        });
    }
});
