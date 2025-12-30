class TableCrafter {
    constructor(config) {
        this.config = config;
        this.selector = config.selector;
        this.source = config.source;
        this.container = document.querySelector(this.selector);

        // Pagination state
        this.currentPage = 1;
        this.perPage = parseInt(this.container.dataset.perPage) || 0;
        this.allData = [];
        this.filteredData = [];

        if (!this.container) {
            console.error('TableCrafter: Container not found', this.selector);
            return;
        }

        // --- HYBRID HYDRATION START ---
        if (this.container.dataset.ssr === "true" && this.container.querySelector('table')) {
            this.container.dataset.tcInitialized = "true";

            // For SSR tables, we can't easily paginate without the full data
            // unless we've enabled hydration. 
            // In v1.4.0, we always initialize fully if pagination or search is needed.
            if (this.perPage > 0) {
                this.init(); // Re-fetch and re-render with pagination UI
            } else {
                this.initSearch();
                return;
            }
        }
        // --- HYBRID HYDRATION END ---

        this.init();
    }

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

        // Handle root path
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
            this.currentPage = 1;
            this.render();
        });
    }

    render() {
        let data = this.filteredData;
        const totalItems = data.length;

        if (totalItems === 0) {
            this.container.innerHTML = '<div class="tc-empty">No data found</div>';
            return;
        }

        // Apply Pagination
        if (this.perPage > 0) {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            data = data.slice(start, end);
        }

        // Configuration for columns
        const include = this.container.dataset.include ? this.container.dataset.include.split(',').map(s => s.trim()) : [];
        const exclude = this.container.dataset.exclude ? this.container.dataset.exclude.split(',').map(s => s.trim()) : [];

        // Determine columns
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

    escapeHTML(str) {
        if (typeof str !== 'string') return str;
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

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

    formatHeader(str) {
        return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }
}
