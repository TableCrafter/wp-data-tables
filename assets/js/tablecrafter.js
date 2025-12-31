/**
 * TableCrafter Library
 * 
 * High-performance, SEO-friendly JSON data table engine for WordPress.
 * Supports SSR hydration, live search, and client-side pagination.
 * 
 * @version 1.4.1
 */
class TableCrafter {
    /**
     * Constructor
     * 
     * @param {Object} config Configuration object.
     * @param {string} config.selector CSS selector for the table container.
     * @param {string} config.source JSON data source URL.
     * @param {Object} [config.proxy] Optional WP AJAX proxy configuration.
     */
    constructor(config) {
        this.config = config;
        this.selector = config.selector;
        this.source = config.source;
        this.container = document.querySelector(this.selector);

        // Internal State
        this.currentPage = 1;
        this.perPage = parseInt(this.container.dataset.perPage) || 0;
        this.allData = [];
        this.filteredData = [];

        if (!this.container) {
            console.error('TableCrafter: Container not found', this.selector);
            return;
        }

        // --- Hybrid Hydration Strategy ---
        // If SSR is present and no pagination is required, we skip the initial fetch.
        if (this.container.dataset.ssr === "true" && this.container.querySelector('table')) {
            this.container.dataset.tcInitialized = "true";

            // Re-fetch only if interactive features like Pagination require the raw data object.
            if (this.perPage > 0) {
                this.init();
            } else {
                this.initSearch();
                return;
            }
        }

        this.init();
    }

    /**
     * Initialize the table.
     * Fetches data, renders, and initializes interactive layers.
     */
    async init() {
        try {
            this.container.innerHTML = '<div class="tc-loading">Fetching data...</div>';
            this.allData = await this.fetchData();
            this.filteredData = this.allData;
            this.render();
            this.initSearch();
        } catch (error) {
            console.error('TableCrafter Error:', error);
            this.container.innerHTML = `<div class="notice notice-error inline"><p>${error.message}</p></div>`;
        }
    }

    /**
     * Data Fetcher.
     * Supports direct fetch and WordPress Proxy (CORS bypass).
     * 
     * @returns {Promise<Array>} Data array.
     */
    async fetchData() {
        let data;
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
            const response = await fetch(this.source);
            if (!response.ok) throw new Error('Network response was not ok');
            data = await response.json();
        }

        // Navigate nested JSON paths
        const rootPath = this.container.dataset.root ? this.container.dataset.root.split('.') : [];
        if (rootPath.length > 0) {
            rootPath.forEach(segment => {
                if (data && data[segment]) {
                    data = data[segment];
                }
            });
        }

        return Array.isArray(data) ? data : [];
    }

    /**
     * Initialize Live Search layer.
     */
    initSearch() {
        if (this.container.dataset.search !== "true") return;
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
            this.filteredData = this.allData.filter(row => {
                return Object.values(row).some(val => String(val).toLowerCase().includes(query));
            });
            this.currentPage = 1; // Reset to page 1 on search
            this.render();
        });
    }

    /**
     * Core Render Method.
     * Handles column filtering, pagination slicing, and DOM updates.
     */
    render() {
        let data = this.filteredData;
        const totalItems = data.length;

        if (totalItems === 0) {
            this.container.innerHTML = '<div class="tc-empty">No data found</div>';
            return;
        }

        // Apply Pagination Slice
        if (this.perPage > 0) {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            data = data.slice(start, end);
        }

        const include = this.container.dataset.include ? this.container.dataset.include.split(',').map(s => s.trim()) : [];
        const exclude = this.container.dataset.exclude ? this.container.dataset.exclude.split(',').map(s => s.trim()) : [];

        // Header Logic
        let headers = Object.keys(this.filteredData[0]);
        if (include.length > 0) headers = headers.filter(h => include.includes(h));
        if (exclude.length > 0) headers = headers.filter(h => !exclude.includes(h));

        let html = '<table class="tc-table">';
        html += '<thead><tr>' + headers.map(h => `<th>${this.escapeHTML(this.formatHeader(h))}</th>`).join('') + '</tr></thead>';
        html += '<tbody>';
        data.forEach(row => {
            html += '<tr>' + headers.map(h => `<td>${this.renderValue(row[h])}</td>`).join('') + '</tr>';
        });
        html += '</tbody></table>';

        // Pagination Controls
        if (this.perPage > 0 && totalItems > this.perPage) {
            const totalPages = Math.ceil(totalItems / this.perPage);
            html += `<div class="tc-pagination">
                <button ${this.currentPage === 1 ? 'disabled' : ''} class="tc-page-prev">Previous</button>
                <span class="tc-page-info">Page ${this.currentPage} of ${totalPages}</span>
                <button ${this.currentPage === totalPages ? 'disabled' : ''} class="tc-page-next">Next</button>
            </div>`;
        }

        const searchHtml = this.container.querySelector('.tc-search-container')?.outerHTML || '';
        this.container.innerHTML = searchHtml + html;
        this.bindEvents();
    }

    /**
     * Bind dynamic events (Pagination, etc).
     */
    bindEvents() {
        const prevBtn = this.container.querySelector('.tc-page-prev');
        const nextBtn = this.container.querySelector('.tc-page-next');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                this.currentPage--;
                this.render();
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                this.currentPage++;
                this.render();
            });
        }
    }

    /**
     * Clean HTML for security.
     * 
     * @param {string} str Raw string.
     * @returns {string} Escaped string.
     */
    escapeHTML(str) {
        if (typeof str !== 'string') return str;
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Smart Value Rendering.
     * Detects images and URLs automatically.
     * 
     * @param {mixed} val Raw data value.
     * @returns {string} HTML or text.
     */
    renderValue(val) {
        if (val === null || val === undefined) return '';
        const strVal = String(val).trim();
        if (strVal.match(/\.(jpeg|jpg|gif|png|webp|svg)$/i) || strVal.startsWith('data:image')) {
            return `<img src="${encodeURI(strVal)}" style="max-width: 100px; height: auto; display: block;">`;
        }
        if (strVal.startsWith('http://') || strVal.startsWith('https://')) {
            return `<a href="${encodeURI(strVal)}" target="_blank" rel="noopener noreferrer">${this.escapeHTML(strVal)}</a>`;
        }
        return this.escapeHTML(strVal);
    }

    /**
     * Key to Title Case formatter.
     * 
     * @param {string} str Key like "product_name".
     * @returns {string} String like "Product Name".
     */
    formatHeader(str) {
        return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }
}
