/**
 * TableCrafter Library
 * 
 * High-performance, SEO-friendly JSON data table engine for WordPress.
 * Supports SSR hydration, live search, client-side pagination, and interactive sorting.
 * 
 * @version 1.9.1
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

        // Export Buttons integration
        if (this.container.dataset.export === "true") {
            this.initExport(searchContainer);
        }

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
     * Initialize Export Toolbar.
     * 
     * @param {HTMLElement} container The container to append buttons to.
     */
    initExport(container) {
        // Create Export Group
        const exportGroup = document.createElement('div');
        exportGroup.className = 'tc-export-group';

        // CSV Button
        const csvBtn = document.createElement('button');
        csvBtn.textContent = 'CSV';
        csvBtn.className = 'tc-btn tc-export-btn';
        csvBtn.title = 'Export to CSV';
        csvBtn.onclick = () => this.exportCSV();

        // Copy Button
        const copyBtn = document.createElement('button');
        copyBtn.textContent = 'Copy';
        copyBtn.className = 'tc-btn tc-export-btn';
        copyBtn.title = 'Copy to Clipboard';
        copyBtn.onclick = () => this.copyTable();

        exportGroup.appendChild(csvBtn);
        exportGroup.appendChild(copyBtn);
        container.appendChild(exportGroup);
    }

    /**
     * Export Visible Data to CSV.
     */
    exportCSV() {
        if (!this.filteredData || this.filteredData.length === 0) return;

        // Re-derive Header Order from DOM or Filter logic
        let headers = this.getAliasedHeaders();

        const csvRows = [];

        // Add Header Row (Aliased)
        const headerLabels = headers.map(h => this.formatHeader(h));
        csvRows.push(headerLabels.join(','));

        // Add Data Rows
        this.filteredData.forEach(row => {
            const values = headers.map(header => {
                let val = row[header] === null || row[header] === undefined ? '' : row[header];
                val = String(val).replace(/"/g, '""'); // Escape double quotes
                return `"${val}"`; // Wrap in quotes
            });
            csvRows.push(values.join(','));
        });

        const csvString = csvRows.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.setAttribute('hidden', '');
        a.setAttribute('href', url);
        a.setAttribute('download', 'data-export.csv');
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    /**
     * Copy Visible Data to Clipboard.
     */
    copyTable() {
        if (!this.filteredData || this.filteredData.length === 0) return;

        const headers = this.getAliasedHeaders();

        // Aliased TSV
        const headerLabels = headers.map(h => this.formatHeader(h));
        let text = headerLabels.join('\t') + '\n';

        this.filteredData.forEach(row => {
            text += headers.map(h => row[h]).join('\t') + '\n';
        });

        navigator.clipboard.writeText(text).then(() => {
            const btn = this.container.querySelector('button.tc-export-btn:nth-child(2)');
            const originalText = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(() => btn.textContent = originalText, 2000);
        }).catch(err => {
            console.error('Failed to copy', err);
        });
    }

    getAliasedHeaders() {
        let headers = Object.keys(this.filteredData[0]);
        let includeKeys = [];
        if (this.container.dataset.include) {
            const items = this.container.dataset.include.split(',');
            items.forEach(i => {
                if (i.includes(':')) {
                    includeKeys.push(i.split(':')[0].trim());
                } else {
                    includeKeys.push(i.trim());
                }
            });
        }

        if (includeKeys.length > 0) {
            headers = headers.filter(h => includeKeys.includes(h));
            headers.sort((a, b) => includeKeys.indexOf(a) - includeKeys.indexOf(b));
        }

        const exclude = this.container.dataset.exclude ? this.container.dataset.exclude.split(',').map(s => s.trim()) : [];
        if (exclude.length > 0) headers = headers.filter(h => !exclude.includes(h));

        return headers;
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

        // Parse Include/Exclude with Alias Support
        let includeKeys = [];
        this.headerMap = {}; // Reset map

        if (this.container.dataset.include) {
            const items = this.container.dataset.include.split(',');
            items.forEach(i => {
                if (i.includes(':')) {
                    const parts = i.split(':');
                    const key = parts[0].trim();
                    const alias = parts.slice(1).join(':').trim();
                    includeKeys.push(key);
                    this.headerMap[key] = alias;
                } else {
                    includeKeys.push(i.trim());
                }
            });
        }

        const exclude = this.container.dataset.exclude ? this.container.dataset.exclude.split(',').map(s => s.trim()) : [];

        // Header Logic
        let rawHeaders = Object.keys(this.filteredData[0]);
        let headers = [...rawHeaders];

        if (includeKeys.length > 0) {
            // Filter
            headers = headers.filter(h => includeKeys.includes(h));
            // Sort by include order
            headers.sort((a, b) => includeKeys.indexOf(a) - includeKeys.indexOf(b));
        }

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
            html += '<tr>' + headers.map(h => `<td data-tc-label="${this.escapeHTML(this.formatHeader(h))}">${this.renderValue(row[h])}</td>`).join('') + '</tr>';
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

        // 1. Boolean (True/False)
        if (val === true || strVal.toLowerCase() === 'true') {
            return '<span class="tc-badge tc-yes">Yes</span>';
        }
        if (val === false || strVal.toLowerCase() === 'false') {
            return '<span class="tc-badge tc-no">No</span>';
        }

        // 2. Images
        if (strVal.match(/\.(jpeg|jpg|gif|png|webp|svg)$/i) || strVal.startsWith('data:image')) {
            return `<img src="${encodeURI(strVal)}" style="max-width: 100px; height: auto; display: block;">`;
        }

        // 3. Email Addresses
        // Regex: Simple email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailRegex.test(strVal)) {
            return `<a href="mailto:${this.escapeHTML(strVal)}">${this.escapeHTML(strVal)}</a>`;
        }

        // 4. URLs
        if (strVal.startsWith('http://') || strVal.startsWith('https://')) {
            return `<a href="${encodeURI(strVal)}" target="_blank" rel="noopener noreferrer">${this.escapeHTML(strVal)}</a>`;
        }

        // 5. ISO Dates (YYYY-MM-DD or YYYY-MM-DDTHH:MM:SS)
        // Regex: matches 2023-01-01 or 2023-01-01T12:00:00Z
        const dateRegex = /^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}:\d{2}(.\d+)?(Z|[\+-]\d{2}:?\d{2})?)?$/;
        if (typeof val === 'string' && dateRegex.test(strVal)) {
            const date = new Date(strVal);
            if (!isNaN(date.getTime())) {
                return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            }
        }

        // 6. Arrays (Tags UI)
        if (Array.isArray(val)) {
            if (val.length === 0) return '';
            const tags = val.map(item => {
                let display = item;
                if (typeof item === 'object' && item !== null) {
                    display = item.name || item.title || item.label || JSON.stringify(item);
                }
                return `<span class="tc-tag">${this.escapeHTML(String(display))}</span>`;
            });
            return `<div class="tc-tag-list">${tags.join('')}</div>`;
        }

        // 7. Objects (Fallback to property search)
        if (typeof val === 'object' && val !== null) {
            const display = val.name || val.title || val.label || val.text || JSON.stringify(val);
            return `<span class="tc-tag">${this.escapeHTML(String(display))}</span>`;
        }

        return this.escapeHTML(strVal);
    }

    /**
     * Key to Title Case formatter (with Alias support).
     */
    formatHeader(str) {
        if (this.headerMap && this.headerMap[str]) {
            return this.headerMap[str];
        }
        return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }
}
