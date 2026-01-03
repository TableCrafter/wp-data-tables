/**
 * TableCrafter Admin Dashboard Scripts
 * 
 * Handles the live-preview functionality, shortcode generator, 
 * and clipboard operations on the TableCrafter settings page.
 */
document.addEventListener('DOMContentLoaded', function () {
    const urlInput = document.getElementById('tc-preview-url');
    const previewBtn = document.getElementById('tc-preview-btn');
    const copyBtn = document.getElementById('tc-copy-shortcode');
    const shortcodeDisplay = document.getElementById('tc-shortcode-display');
    const container = document.getElementById('tc-preview-container');
    const demoLinks = document.querySelectorAll('.tc-demo-links a');

    if (!urlInput || !previewBtn || !copyBtn) return; // Exit if not on the settings page

    /**
     * Update shortcode display element when the URL input changes.
     */
    urlInput.addEventListener('input', function () {
        const url = this.value.trim() || 'URL';
        shortcodeDisplay.textContent = `[tablecrafter source="${url}"]`;
    });

    /**
     * Handle Demo Link clicks.
     * Populates the input and triggers a preview automatically.
     */
    demoLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            urlInput.value = this.dataset.url;
            urlInput.dispatchEvent(new Event('input'));
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

        if (typeof TableCrafter !== 'undefined') {
            const tableId = 'tc-preview-' + Date.now();
            container.innerHTML = `<div id="${tableId}" class="tablecrafter-container">${tablecrafterAdmin.i18n.loading}</div>`;

            // Initialize the TableCrafter instance with proxy support for admin preview
            new TableCrafter('#' + tableId, {
                data: url,
                pagination: true,
                responsive: true,
                api: {
                    proxy: {
                        url: tablecrafterAdmin.ajaxUrl,
                        nonce: tablecrafterAdmin.nonce
                    }
                }
            });
        } else {
            container.innerHTML = `<div class="notice notice-error inline"><p>${tablecrafterAdmin.i18n.libMissing}</p></div>`;
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
