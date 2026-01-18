/**
 * TableCrafter Admin Dashboard Scripts
 * 
 * Handles the live-preview functionality, shortcode generator, 
 * and clipboard operations on the TableCrafter settings page.
 */
document.addEventListener('DOMContentLoaded', function () {
    const urlInput = document.getElementById('tc-preview-url');
    const rootInput = document.getElementById('tc-data-root');
    const perPageInput = document.getElementById('tc-per-page');
    const includeColsInput = document.getElementById('tc-include-cols');
    const excludeColsInput = document.getElementById('tc-exclude-cols');
    const searchToggle = document.getElementById('tc-enable-search');
    const filterToggle = document.getElementById('tc-enable-filters');
    const exportToggle = document.getElementById('tc-enable-export');
    const previewBtn = document.getElementById('tc-preview-btn');
    const copyBtn = document.getElementById('tc-copy-shortcode');
    const shortcodeDisplay = document.getElementById('tc-shortcode-display');
    const container = document.getElementById('tc-preview-container');
    const demoLinks = document.querySelectorAll('.tc-demo-links a');
    const uploadBtn = document.getElementById('tc-upload-csv-btn'); // [v2.5.0]

    if (!urlInput || !previewBtn || !copyBtn) return; // Exit if not on the settings page

    // Auto-trigger preview if coming from welcome screen with demo URL
    const urlParams = new URLSearchParams(window.location.search);
    const demoUrl = urlParams.get('demo_url');
    if (demoUrl) {
        setTimeout(() => {
            urlInput.dispatchEvent(new Event('input')); // Update shortcode
            previewBtn.click(); // Trigger preview
        }, 500);
    }

    // --- Media Library Upload Handler (v2.5.0) ---
    // --- Media Library Upload Handler (v2.5.0) ---
    if (uploadBtn) {
        let fileFrame;
        uploadBtn.addEventListener('click', function (e) {
            e.preventDefault();

            if (fileFrame) {
                fileFrame.open();
                return;
            }

            fileFrame = wp.media({
                title: 'Select Data Source (CSV/JSON)',
                button: {
                    text: 'Use this Data Source'
                },
                multiple: false
            });

            fileFrame.on('select', function () {
                const attachment = fileFrame.state().get('selection').first().toJSON();
                urlInput.value = attachment.url;
                
                // Trigger events to update UI
                urlInput.dispatchEvent(new Event('input'));
                
                // Auto-preview
                setTimeout(() => previewBtn.click(), 100);
            });

            fileFrame.open();
        });
    }

    const sheetBtn = document.getElementById('tc-google-sheet-btn');
    if (sheetBtn) {
        sheetBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = prompt('Paste your Google Sheet URL (Must be "Anyone with the link can view"):', '');
            if (url) {
                urlInput.value = url.trim();
                urlInput.dispatchEvent(new Event('input'));
                setTimeout(() => previewBtn.click(), 100);
            }
        });
    }
    // ---------------------------------------------

    const BUTTON_COLORS = {
        DEFAULT: '', // WordPress default
        DIRTY: '#d63638', // WordPress error red
        SYNCED: '#46b450' // WordPress success green
    };

    /**
     * Set the preview button state.
     * @param {string} state 'dirty' or 'synced'
     */
    function setButtonState(state) {
        if (state === 'dirty') {
            previewBtn.style.backgroundColor = BUTTON_COLORS.DIRTY;
            previewBtn.style.borderColor = BUTTON_COLORS.DIRTY;
            previewBtn.style.color = '#fff';
        } else if (state === 'synced') {
            previewBtn.style.backgroundColor = BUTTON_COLORS.SYNCED;
            previewBtn.style.borderColor = BUTTON_COLORS.SYNCED;
            previewBtn.style.color = '#fff';
        }
    }

    /**
     * Generate the shortcode based on all current inputs.
     */
    function generateShortcode() {
        const url = urlInput.value.trim();
        const root = rootInput.value.trim();
        const perPage = perPageInput.value.trim();
        const include = includeColsInput.value.trim();
        const exclude = excludeColsInput.value.trim();
        const search = searchToggle.checked;
        const filters = filterToggle.checked;
        const exportTable = exportToggle.checked;

        let shortcode = `[tablecrafter source="${url || 'URL'}"`;
        
        if (root) shortcode += ` root="${root}"`;
        if (perPage && perPage !== '10') shortcode += ` per_page="${perPage}"`;
        if (include) shortcode += ` include="${include}"`;
        if (exclude) shortcode += ` exclude="${exclude}"`;
        
        // Always be explicit with boolean attributes to avoid PHP/JS default mismatches
        shortcode += ` search="${search ? 'true' : 'false'}"`;
        shortcode += ` filters="${filters ? 'true' : 'false'}"`;
        shortcode += ` export="${exportTable ? 'true' : 'false'}"`;
        
        shortcode += ']';
        shortcodeDisplay.textContent = shortcode;
    }

    // Add listeners to all inputs for real-time generation and dirty state
    [urlInput, rootInput, perPageInput, includeColsInput, excludeColsInput].forEach(el => {
        el.addEventListener('input', () => {
            generateShortcode();
            setButtonState('dirty');
        });
    });

    [searchToggle, filterToggle, exportToggle].forEach(el => {
        el.addEventListener('change', () => {
            generateShortcode();
            setButtonState('dirty');
            // Auto-trigger preview for toggles
            previewBtn.click();
        });
    });

    /**
     * Handle Demo Link clicks.
     * Populates the input and triggers a preview automatically.
     */
    demoLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            urlInput.value = this.dataset.url || '';
            // Reset other fields for demos
            if (rootInput) rootInput.value = '';
            if (perPageInput) perPageInput.value = '10';
            if (includeColsInput) includeColsInput.value = '';
            if (excludeColsInput) excludeColsInput.value = '';
            if (searchToggle) searchToggle.checked = true;
            if (filterToggle) filterToggle.checked = true;
            if (exportToggle) exportToggle.checked = false;
            
            generateShortcode();
            previewBtn.click();
        });
    });

    /**
     * Trigger Table Preview.
     * Uses the core TableCrafter library to render a live table in the admin area.
     */
    previewBtn.addEventListener('click', function () {
        const url = urlInput.value.trim();
        if (!url) {
            alert(tablecrafterAdmin.i18n.enterUrl);
            return;
        }

        container.innerHTML = '';
        container.style.display = 'block';
        
        // Add class for table layout styling
        container.classList.add('tc-has-table');

        if (typeof TableCrafter !== 'undefined') {
            const tableId = 'tc-preview-' + Date.now();
            const previewDiv = document.createElement('div');
            previewDiv.id = tableId;
            previewDiv.className = 'tablecrafter-container';
            previewDiv.textContent = tablecrafterAdmin.i18n.loading;
            container.appendChild(previewDiv);

            const root = rootInput.value.trim();
            const perPage = parseInt(perPageInput.value) || 10;
            const include = includeColsInput.value.trim();
            const exclude = excludeColsInput.value.trim();
            const search = searchToggle.checked;
            const filters = filterToggle.checked;
            const exportData = exportToggle.checked;

            try {
                const config = {
                    data: url,
                    root: root || undefined,
                    perPage: perPage,
                    pagination: true,
                    globalSearch: search,
                    filterable: filters,
                    exportable: exportData,
                    include: include || undefined,
                    exclude: exclude || undefined,
                    responsive: true,
                    api: {
                        proxy: {
                            url: tablecrafterAdmin.ajaxUrl,
                            nonce: tablecrafterAdmin.nonce
                        }
                    }
                };
                const tableInstance = new TableCrafter('#' + tableId, config);

                // Success! Set button to green
                setButtonState('synced');
            } catch (error) {
                console.error('TableCrafter Admin: Initialization error:', error);
                // SECURITY: Create error message safely without innerHTML
                previewDiv.innerHTML = '';
                const errorDiv = document.createElement('div');
                errorDiv.className = 'notice notice-error inline';
                errorDiv.style.cssText = 'padding: 15px; margin: 0;';
                
                const errorParagraph = document.createElement('p');
                const errorStrong = document.createElement('strong');
                errorStrong.textContent = 'Initialization error: ';
                errorParagraph.appendChild(errorStrong);
                errorParagraph.appendChild(document.createTextNode(error.message || 'Unknown error'));
                
                errorDiv.appendChild(errorParagraph);
                previewDiv.appendChild(errorDiv);
            }
        } else {
            // SECURITY: Create error message safely without innerHTML
            container.innerHTML = '';
            const errorDiv = document.createElement('div');
            errorDiv.className = 'notice notice-error inline';
            
            const errorParagraph = document.createElement('p');
            errorParagraph.textContent = tablecrafterAdmin.i18n.libMissing;
            
            errorDiv.appendChild(errorParagraph);
            container.appendChild(errorDiv);
        }
    });

    /**
     * Copy Shortcode to Clipboard.
     * Uses the modern Clipboard API with a robust fallback for legacy or non-secure contexts.
     */
    copyBtn.addEventListener('click', function () {
        const text = shortcodeDisplay.textContent;

        const copyToClipboard = async (text) => {
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(text);
                } else {
                    throw new Error('Clipboard API unavailable');
                }
            } catch (err) {
                // Fallback for non-HTTPS or legacy browsers
                const textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                textArea.style.left = "-9999px";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    textArea.remove();
                } catch (e) {
                    console.error('Copy failed', e);
                    textArea.remove();
                    alert(tablecrafterAdmin.i18n.copyFailed);
                    return;
                }
            }

            // Provide visual feedback
            const originalText = copyBtn.textContent;
            copyBtn.textContent = tablecrafterAdmin.i18n.copied;
            setTimeout(() => copyBtn.textContent = originalText, 2000);
        };

        copyToClipboard(text);
    });
});
