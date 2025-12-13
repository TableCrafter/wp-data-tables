/**
 * TableCrafter JS Library (Placeholder/Base)
 * based on git@github.com:TableCrafter/tablecrafter.git
 */

class TableCrafter {
    constructor(config) {
        this.selector = config.selector;
        this.source = config.source;
        this.container = document.querySelector(this.selector);

        if (!this.container) {
            console.error('TableCrafter: Container not found', this.selector);
            return;
        }

        this.init();
    }

    async init() {
        try {
            this.container.innerHTML = '<div class="tc-loading">Fetching data...</div>';

            const response = await fetch(this.source);
            if (!response.ok) throw new Error('Network response was not ok');

            const data = await response.json();
            this.render(data);

        } catch (error) {
            console.error('TableCrafter Error:', error);
            this.container.innerHTML = `<div class="tc-error">Error loading data: ${error.message}</div>`;
        }
    }

    render(data) {
        if (!Array.isArray(data) || data.length === 0) {
            this.container.innerHTML = '<div class="tc-empty">No data found</div>';
            return;
        }

        // Basic table generation
        const headers = Object.keys(data[0]);

        let html = '<table class="tc-table">';

        // Header
        html += '<thead><tr>';
        headers.forEach(header => {
            html += `<th>${this.formatHeader(header)}</th>`;
        });
        html += '</tr></thead>';

        // Body
        html += '<tbody>';
        data.forEach(row => {
            html += '<tr>';
            headers.forEach(header => {
                html += `<td>${row[header] !== null ? row[header] : ''}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody>';
        html += '</table>';

        this.container.innerHTML = html;
    }

    formatHeader(str) {
        // basic title case
        return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }
}
