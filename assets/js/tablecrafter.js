/**
 * TableCrafter Library
 * 
 * High-performance, SEO-friendly JSON data table engine for WordPress.
 * Supports SSR hydration, live search, client-side pagination, and interactive sorting.
 * 
 * @version 1.5.0
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

        // Sorting State
        this.sortKey = null;
        this.sortDirection = 'asc'; // 'asc' or 'desc'

        if (!this.container) {
            console.error('TableCrafter: Container not found', this.selector);
            return;
        }

        // --- Hybrid Hydration Strategy ---
        if (this.container.dataset.ssr === "true" && this.container.querySelector('table')) {
            this.container.dataset.tcInitialized = "true";

            // Require full data for Sorting or Pagination
            if (this.perPage > 0 || this.container.dataset.search === "true") {
                this.init();
            } else {
                this.initSearch();
                this.bindEvents(); // Bind headers even for SSR
                return;
            }
        }

        this.init();
    }

    /**
     * Initialize the table.
     */
    async init() {
        try {
            this.container.innerHTML = '<div class="tc-loading">Fetching data...</div>';
            this.allData = await this.fetchData();
            this.filteredData = [...this.allData];
            this.render();
            this.initSearch();
        } catch (error) {
            console.error('TableCrafter Error:', error);
            this.container.innerHTML = `<div class="notice notice-error inline"><p>${error.message}</p></div>`;
        }
    }

    /**
     * Data Fetcher.
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

            // Re-apply sort if active
            if (this.sortKey) {
                this.sortData(this.sortKey, true);
            }

            this.currentPage = 1;
            this.render();
        });
    }

    /**
     * Sort Data by Column.
     * 
     * @param {string} key The column key to sort by.
     * @param {boolean} preserveDirection If true, don't toggle ASC/DESC.
     */
    sortData(key, preserveDirection = false) {
        if (!preserveDirection) {
            if (this.sortKey === key) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortKey = key;
                this.sortDirection = 'asc';
            }
        }

        this.filteredData.sort((a, b) => {
            let valA = a[key];
            let valB = b[key];

            // Smart type detection
            const numA = parseFloat(valA);
            const numB = parseFloat(valB);

            if (!isNaN(numA) && !isNaN(numB)) {
                return this.sortDirection === 'asc' ? numA - numB : numB - numA;
            }

            // Fallback to string comparison
            valA = String(valA).toLowerCase();
            valB = String(valB).toLowerCase();

            if (valA < valB) return this.sortDirection === 'asc' ? -1 : 1;
            if (valA > valB) return this.sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
    }

    /**
     * Core Render Method.
     */
    render() {
        let data = [...this.filteredData];
        const totalItems = data.length;

        if (totalItems === 0) {
            const searchHtmlExisting = this.container.querySelector('.tc-search-container')?.outerHTML || '';
            this.container.innerHTML = searchHtmlExisting + '<div class="tc-empty">No data found</div>';
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
        let rawHeaders = Object.keys(this.filteredData[0]);
        let headers = rawHeaders;
        if (include.length > 0) headers = headers.filter(h => include.includes(h));
        if (exclude.length > 0) headers = headers.filter(h => !exclude.includes(h));

        let html = '<table class="tc-table">';
        html += '<thead><tr>';
        headers.forEach(h => {
            const isSorted = this.sortKey === h;
            const sortClass = isSorted ? `tc-sort-${this.sortDirection}` : '';
            html += `<th class="tc-sortable ${sortClass}" data-key="${h}">${this.escapeHTML(this.formatHeader(h))}<span class="tc-sort-icon"></span></th>`;
        });
        html += '</tr></thead>';

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
     * Bind dynamic events.
     */
    bindEvents() {
        const prevBtn = this.container.querySelector('.tc-page-prev');
        const nextBtn = this.container.querySelector('.tc-page-next');

        if (prevBtn) {
            prevBtn.onclick = () => {
                this.currentPage--;
                this.render();
            };
        }
        if (nextBtn) {
            nextBtn.onclick = () => {
                this.currentPage++;
                this.render();
            };
        }

        // Header Sorters
        const headers = this.container.querySelectorAll('th.tc-sortable');
        headers.forEach(header => {
            header.onclick = () => {
                const key = header.dataset.key;
                this.sortData(key);
                this.currentPage = 1; // Reset to page 1 on sort
                this.render();
            };
        });
    }

    /**
     * Clean HTML for security.
     */
    escapeHTML(str) {
        if (typeof str !== 'string') return str;
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Smart Value Rendering.
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
     */
    formatHeader(str) {
        return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }
}
