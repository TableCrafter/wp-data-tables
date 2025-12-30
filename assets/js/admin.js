document.addEventListener('DOMContentLoaded', function () {
    const urlInput = document.getElementById('tc-preview-url');
    const previewBtn = document.getElementById('tc-preview-btn');
    const copyBtn = document.getElementById('tc-copy-shortcode');
    const shortcodeDisplay = document.getElementById('tc-shortcode-display');
    const container = document.getElementById('tc-preview-container');
    const demoLinks = document.querySelectorAll('.tc-demo-links a');

    if (!urlInput || !previewBtn || !copyBtn) return; // Exit if not on the settings page

    // Update shortcode display on input
    urlInput.addEventListener('input', function () {
        const url = this.value.trim() || 'URL';
        // We use textContent for security instead of innerText
        shortcodeDisplay.textContent = `[tablecrafter source="${url}"]`;
    });

    // Load demo URL on click
    demoLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            urlInput.value = this.dataset.url;
            // Trigger input event to update shortcode
            urlInput.dispatchEvent(new Event('input'));
            previewBtn.click();
        });
    });

    // Preview functionality
    previewBtn.addEventListener('click', function () {
        const url = urlInput.value.trim();
        if (!url) {
            alert(tablecrafterAdmin.i18n.enterUrl);
            return;
        }

        // Reset container
        container.innerHTML = '';
        container.style.display = 'block';

        if (typeof TableCrafter !== 'undefined') {
            // Create a unique ID for the inner container
            const tableId = 'tc-preview-' + Date.now();
            container.innerHTML = `<div id="${tableId}" class="tablecrafter-container">${tablecrafterAdmin.i18n.loading}</div>`;

            // Init TableCrafter
            new TableCrafter({
                selector: '#' + tableId,
                source: url,
                proxy: {
                    url: tablecrafterAdmin.ajaxUrl,
                    nonce: tablecrafterAdmin.nonce
                }
            });
        } else {
            container.innerHTML = `<div class="notice notice-error inline"><p>${tablecrafterAdmin.i18n.libMissing}</p></div>`;
        }
    });

    // Copy shortcode functionality
    copyBtn.addEventListener('click', function () {
        const text = shortcodeDisplay.textContent;

        // Robust copy function with fallback
        const copyToClipboard = async (text) => {
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(text);
                } else {
                    throw new Error('Clipboard API unavailable');
                }
            } catch (err) {
                // Fallback for HTTP/non-secure contexts
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

            // Success feedback
            const originalText = copyBtn.textContent;
            copyBtn.textContent = tablecrafterAdmin.i18n.copied;
            setTimeout(() => copyBtn.textContent = originalText, 2000);
        };

        copyToClipboard(text);
    });
});
