class TableCrafter {
    constructor(config) {
        this.config = config;
        this.selector = config.selector;
        this.source = config.source;
        this.container = document.querySelector(this.selector);

        if (!this.container) {
            console.error('TableCrafter: Container not found', this.selector);
            return;
        }

        // --- HYBRID HYDRATION START ---
        // If the table was pre-rendered on the server (SSR), skip the fetch/render cycle
        if (this.container.dataset.ssr === "true" && this.container.querySelector('table')) {
            this.container.dataset.tcInitialized = "true";
            // Still initialize search if enabled
            this.initSearch();
            return;
        }
        // --- HYBRID HYDRATION END ---

        this.init();
    }

    async init() {
        try {
            this.container.innerHTML = '<div class="tc-loading">Fetching data...</div>';
            const data = await this.fetchData();
            this.render(data);
            this.initSearch();
        } catch (error) {
            console.error('TableCrafter Error:', error);
            this.container.innerHTML = `<div class="notice notice-error inline"><p>${error.message}</p></div>`;
        }
    }

    async fetchData() {
        let data;
        // Check if we should use the WordPress Proxy (for CORS and Caching)
        if (this.config.proxy && this.config.proxy.url) {
            const formData = new FormData();
            formData.append('action', 'tc_proxy_fetch');
            formData.append('url', this.source);
            formData.append('nonce', this.config.proxy.nonce);

            const response = await fetch(this.config.proxy.url, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (!result.success) throw new Error(result.data || 'Proxy fetch failed');
            data = result.data;
        } else {
            // Direct fetch fallback
            const response = await fetch(this.source);
            if (!response.ok) throw new Error('Network response was not ok');
            data = await response.json();
        }
        return data;
    }

    initSearch() {
        if (this.container.dataset.search !== "true") return;

        // Prevent duplicate search bars
        if (this.container.querySelector('.tc-search-container')) return;

        const table = this.container.querySelector('table');
        if (!table) return;

        const searchContainer = document.createElement('div');
        searchContainer.className = 'tc-search-container';

        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search table...';
        searchInput.className = 'tc-search-input';

        searchContainer.appendChild(searchInput);
        this.container.insertBefore(searchContainer, table);

        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    render(data) {
        if (!Array.isArray(data) || data.length === 0) {
            this.container.innerHTML = '<div class="tc-empty">No data found</div>';
            return;
        }

        // Configuration for columns
        const include = this.container.dataset.include ? this.container.dataset.include.split(',').map(s => s.trim()) : [];
        const exclude = this.container.dataset.exclude ? this.container.dataset.exclude.split(',').map(s => s.trim()) : [];
        const rootPath = this.container.dataset.root ? this.container.dataset.root.split('.') : [];

        // Navigate to the root path if provided
        if (rootPath.length > 0) {
            rootPath.forEach(segment => {
                if (data && data[segment]) {
                    data = data[segment];
                }
            });
        }

        if (!Array.isArray(data) || data.length === 0) {
            this.container.innerHTML = '<div class="notice notice-warning inline"><p>TableCrafter: No data found at the specified root.</p></div>';
            return;
        }

        // Determine which headers to show
        let headers = Object.keys(data[0]);

        if (include.length > 0) {
            headers = headers.filter(h => include.includes(h));
        }
        if (exclude.length > 0) {
            headers = headers.filter(h => !exclude.includes(h));
        }

        let html = '<table class="tc-table">';

        // Header
        html += '<thead><tr>';
        headers.forEach(header => {
            html += `<th>${this.escapeHTML(this.formatHeader(header))}</th>`;
        });
        html += '</tr></thead>';

        // Body
        html += '<tbody>';
        data.forEach(row => {
            html += '<tr>';
            headers.forEach(header => {
                const val = row[header];
                html += `<td>${this.renderValue(val)}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody>';
        html += '</table>';

        this.container.innerHTML = html;
        this.container.dataset.tcInitialized = "true";
    }

    /**
     * Secures output by escaping HTML tags.
     */
    escapeHTML(str) {
        if (typeof str !== 'string') return str;
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Smartly renders values (detects images and links)
     */
    renderValue(val) {
        if (val === null || val === undefined) return '';

        const strVal = String(val).trim();

        // Detect Images
        if (strVal.match(/\.(jpeg|jpg|gif|png|webp|svg)$/i) || strVal.startsWith('data:image')) {
            return `<img src="${encodeURI(strVal)}" style="max-width: 100px; height: auto; display: block;" onerror="this.onerror=null; this.outerHTML='${this.escapeHTML(strVal)}';">`;
        }

        // Detect Links
        if (strVal.startsWith('http://') || strVal.startsWith('https://')) {
            return `<a href="${encodeURI(strVal)}" target="_blank" rel="noopener noreferrer">${this.escapeHTML(strVal)}</a>`;
        }

        return this.escapeHTML(strVal);
    }

    formatHeader(str) {
        // basic title case
        return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }
}
