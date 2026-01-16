/**
 * TableCrafter Elementor Live Preview
 * 
 * Provides live table rendering in Elementor editor for better user experience.
 * Handles data fetching, table rendering, and editor integration.
 * 
 * @package TableCrafter
 * @since 3.1.1
 */
(function ($) {
    'use strict';

    // Global preview manager
    window.TCElementorPreview = {
        instances: new Map(),
        cache: new Map(),
        
        /**
         * Initialize preview for a widget
         */
        init: function (widgetId, config) {
            if (!config.source) return;
            
            // Store instance
            this.instances.set(widgetId, config);
            
            // Check cache first
            const cacheKey = this.getCacheKey(config);
            const cached = this.cache.get(cacheKey);
            
            if (cached && Date.now() - cached.timestamp < 300000) { // 5 minutes
                this.renderPreview(widgetId, cached.data, config);
                return;
            }
            
            // Fetch fresh data
            this.fetchData(widgetId, config);
        },
        
        /**
         * Generate cache key
         */
        getCacheKey: function (config) {
            return btoa(config.source + '|' + config.root + '|' + config.include + '|' + config.exclude).replace(/[^a-zA-Z0-9]/g, '');
        },
        
        /**
         * Fetch data from source
         */
        fetchData: function (widgetId, config) {
            const self = this;
            const $container = $('#tc-preview-' + widgetId);
            
            // Show loading state
            $container.html(`
                <div class="tc-preview-loading" style="display: flex; align-items: center; justify-content: center; height: 200px; color: #666;">
                    <div style="text-align: center;">
                        <div class="tc-spinner" style="border: 3px solid #f3f3f3; border-top: 3px solid #2196f3; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 0 auto 10px;"></div>
                        <p style="margin: 0; font-size: 14px;">Loading table preview...</p>
                    </div>
                </div>
            `);
            
            // AJAX request
            $.post(tablecrafterData.ajaxUrl, {
                action: 'tc_elementor_preview',
                nonce: tablecrafterData.nonce,
                source: config.source,
                root: config.root || '',
                include: config.include || '',
                exclude: config.exclude || '',
                preview_rows: config.perPage || 5
            })
            .done(function (response) {
                if (response.success && response.data) {
                    // Cache the result
                    const cacheKey = self.getCacheKey(config);
                    self.cache.set(cacheKey, {
                        data: response.data,
                        timestamp: Date.now()
                    });
                    
                    self.renderPreview(widgetId, response.data, config);
                } else {
                    self.renderError(widgetId, response.data || 'Failed to load data');
                }
            })
            .fail(function (xhr) {
                self.renderError(widgetId, 'Network error: ' + xhr.statusText);
            });
        },
        
        /**
         * Render successful preview
         */
        renderPreview: function (widgetId, data, config) {
            const $container = $('#tc-preview-' + widgetId);
            
            if (!data || !Array.isArray(data) || data.length === 0) {
                this.renderError(widgetId, 'No data available');
                return;
            }
            
            // Get headers
            let headers = Object.keys(data[0] || {});
            
            // Apply column filtering
            if (config.include) {
                const includeList = config.include.split(',').map(s => s.trim()).filter(Boolean);
                if (includeList.length > 0) {
                    headers = headers.filter(h => includeList.includes(h));
                    // Reorder to match include order
                    headers.sort((a, b) => includeList.indexOf(a) - includeList.indexOf(b));
                }
            }
            
            if (config.exclude) {
                const excludeList = config.exclude.split(',').map(s => s.trim()).filter(Boolean);
                headers = headers.filter(h => !excludeList.includes(h));
            }
            
            if (headers.length === 0) {
                this.renderError(widgetId, 'No columns to display after filtering');
                return;
            }
            
            // Limit preview rows
            const previewData = data.slice(0, config.perPage || 5);
            
            // Build table HTML
            let html = `
                <div class="tc-table-container" style="border: none; border-radius: 0; overflow: hidden;">
                    <table class="tc-table" style="margin: 0;">
                        <thead>
                            <tr>
            `;
            
            // Table headers
            headers.forEach(header => {
                const displayName = this.formatHeader(header);
                html += `<th style="background: #f8f9fa; padding: 12px 16px; border-bottom: 1px solid #e1e5e9; font-weight: 600; color: #495057; text-align: left;">${this.escapeHtml(displayName)}</th>`;
            });
            
            html += `
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            // Table rows
            previewData.forEach((row, index) => {
                html += `<tr style="${index % 2 === 1 ? 'background: #f8f9fa;' : ''}">`;
                headers.forEach(header => {
                    const value = row[header] || '';
                    html += `<td style="padding: 12px 16px; border-bottom: 1px solid #e1e5e9; color: #333; text-align: left;">${this.formatValue(value)}</td>`;
                });
                html += `</tr>`;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            // Add preview note if data was truncated
            if (data.length > previewData.length) {
                html += `
                    <div style="padding: 8px 16px; background: #e3f2fd; color: #1976d2; font-size: 12px; text-align: center; border-top: 1px solid #2196f3;">
                        <strong>Preview:</strong> Showing ${previewData.length} of ${data.length} rows
                    </div>
                `;
            }
            
            $container.html(html);
        },
        
        /**
         * Render error state
         */
        renderError: function (widgetId, message) {
            const $container = $('#tc-preview-' + widgetId);
            
            $container.html(`
                <div style="padding: 40px; text-align: center; color: #dc3545; background: #f8d7da; border: 1px solid #f5c6cb;">
                    <div style="font-size: 32px; margin-bottom: 15px;">⚠️</div>
                    <h4 style="margin: 0 0 10px; color: #721c24; font-size: 16px;">Preview Error</h4>
                    <p style="margin: 0; font-size: 14px; color: #721c24;">${this.escapeHtml(message)}</p>
                    <small style="display: block; margin-top: 10px; color: #6c757d;">Check your data source URL and settings</small>
                </div>
            `);
        },
        
        /**
         * Format header name
         */
        formatHeader: function (str) {
            return str.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },
        
        /**
         * Format table cell value
         */
        formatValue: function (value) {
            if (value === null || value === undefined) {
                return '<span style="color: #999; font-style: italic;">—</span>';
            }
            
            // Truncate long values for preview
            const str = String(value);
            if (str.length > 100) {
                return this.escapeHtml(str.substring(0, 97)) + '<span style="color: #999;">...</span>';
            }
            
            // Handle different data types
            if (typeof value === 'boolean') {
                return value ? 
                    '<span style="color: #28a745; font-weight: 600;">✓ Yes</span>' : 
                    '<span style="color: #dc3545; font-weight: 600;">✗ No</span>';
            }
            
            // Handle URLs
            if (typeof value === 'string' && /^https?:\/\//.test(value)) {
                return `<a href="${this.escapeHtml(value)}" target="_blank" style="color: #007bff; text-decoration: underline;">${this.escapeHtml(str.length > 50 ? str.substring(0, 47) + '...' : str)}</a>`;
            }
            
            // Handle emails
            if (typeof value === 'string' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                return `<a href="mailto:${this.escapeHtml(value)}" style="color: #007bff;">${this.escapeHtml(value)}</a>`;
            }
            
            return this.escapeHtml(str);
        },
        
        /**
         * Escape HTML entities
         */
        escapeHtml: function (text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        /**
         * Refresh preview
         */
        refresh: function (widgetId) {
            const config = this.instances.get(widgetId);
            if (config) {
                // Clear cache
                const cacheKey = this.getCacheKey(config);
                this.cache.delete(cacheKey);
                
                // Refetch
                this.fetchData(widgetId, config);
            }
        },
        
        /**
         * Clean up instance
         */
        destroy: function (widgetId) {
            this.instances.delete(widgetId);
        }
    };

    // Initialize when Elementor panel is loaded
    $(window).on('elementor/frontend/init', function () {
        // Wait for Elementor to be fully loaded
        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
            const $widget = $scope.find('.tc-elementor-preview');
            
            if ($widget.length > 0) {
                const config = $widget.data('preview-config');
                const widgetId = $widget.attr('id');
                
                if (config && config.source && widgetId) {
                    // Small delay to ensure DOM is ready
                    setTimeout(function () {
                        window.TCElementorPreview.init(widgetId, config);
                    }, 100);
                }
            }
        });
    });
    
    // Handle editor changes
    if (window.elementor) {
        elementor.hooks.addAction('panel/open_editor/widget/tablecrafter-data-table', function (panel, model, view) {
            // Refresh preview when settings change
            panel.content.currentView.on('child:render', function () {
                setTimeout(function () {
                    const widgetId = 'tc-elementor-' + model.get('id');
                    window.TCElementorPreview.refresh(widgetId);
                }, 500);
            });
        });
    }

})(jQuery);