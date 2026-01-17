/**
 * TableCrafter Performance Optimizer
 * 
 * Implements virtual scrolling, lazy loading, and performance optimizations
 * for large datasets to ensure smooth rendering and user experience.
 */

(function() {
    'use strict';
    
    // Global performance configuration
    const PERF_CONFIG = window.tcPerformance || {
        virtualScrollThreshold: 500,
        virtualRowsRendered: 50,
        virtualBufferRows: 10,
        enableVirtualScrolling: true,
        enableLazyImages: true,
        enableDeferredRendering: true
    };
    
    /**
     * Virtual Scroll Manager
     * Handles large dataset rendering with virtual scrolling
     */
    class VirtualScrollManager {
        constructor(container, data, options = {}) {
            this.container = container;
            this.originalData = data;
            this.options = options;
            this.virtualContainer = null;
            this.viewport = null;
            this.scrollbar = null;
            
            // Virtual scroll state
            this.startIndex = 0;
            this.endIndex = PERF_CONFIG.virtualRowsRendered;
            this.rowHeight = options.estimatedRowHeight || 45;
            this.totalHeight = this.originalData.length * this.rowHeight;
            this.viewportHeight = 0;
            
            // Performance tracking
            this.lastScrollTime = 0;
            this.scrollThrottle = 16; // ~60fps
            this.isScrolling = false;
            
            this.init();
        }
        
        init() {
            this.createVirtualContainer();
            this.setupEventListeners();
            this.renderInitialRows();
            
            // Performance monitoring
            console.log(`[TableCrafter] Virtual scrolling initialized for ${this.originalData.length} rows`);
        }
        
        createVirtualContainer() {
            const table = this.container.querySelector('.tc-table');
            if (!table) return;
            
            // Create virtual container
            this.virtualContainer = document.createElement('div');
            this.virtualContainer.className = 'tc-virtual-container';
            this.virtualContainer.style.cssText = `
                position: relative;
                height: 400px;
                overflow-y: auto;
                border: 1px solid #e1e5e9;
                border-radius: 8px;
                background: white;
            `;
            
            // Create viewport for visible content
            this.viewport = document.createElement('div');
            this.viewport.className = 'tc-virtual-viewport';
            this.viewport.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: ${this.totalHeight}px;
            `;
            
            // Create content container for rendered rows
            this.contentContainer = document.createElement('div');
            this.contentContainer.className = 'tc-virtual-content';
            this.contentContainer.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                transform: translateY(0px);
            `;
            
            // Preserve original table for header
            const headerTable = table.cloneNode(true);
            const headerTbody = headerTable.querySelector('tbody');
            if (headerTbody) headerTbody.remove();
            
            // Style header
            headerTable.style.cssText = `
                margin: 0;
                border-bottom: 2px solid #e1e5e9;
                background: #f8f9fa;
                position: sticky;
                top: 0;
                z-index: 10;
            `;
            
            // Build structure
            this.viewport.appendChild(this.contentContainer);
            this.virtualContainer.appendChild(headerTable);
            this.virtualContainer.appendChild(this.viewport);
            
            // Replace original table
            table.parentNode.replaceChild(this.virtualContainer, table);
            
            // Store dimensions
            this.viewportHeight = this.virtualContainer.clientHeight - headerTable.clientHeight;
            this.viewport.style.height = this.viewportHeight + 'px';
            this.viewport.style.top = headerTable.clientHeight + 'px';
        }
        
        setupEventListeners() {
            if (!this.viewport) return;
            
            // Optimized scroll handler
            this.viewport.addEventListener('scroll', this.throttle(() => {
                this.handleScroll();
            }, this.scrollThrottle));
            
            // Intersection observer for lazy loading
            this.intersectionObserver = new IntersectionObserver(
                this.handleIntersection.bind(this),
                { root: this.viewport, rootMargin: '50px' }
            );
        }
        
        handleScroll() {
            const scrollTop = this.viewport.scrollTop;
            const newStartIndex = Math.floor(scrollTop / this.rowHeight);
            
            // Add buffer to reduce flickering
            const bufferStart = Math.max(0, newStartIndex - PERF_CONFIG.virtualBufferRows);
            const bufferEnd = Math.min(
                this.originalData.length,
                newStartIndex + this.getVisibleRowCount() + PERF_CONFIG.virtualBufferRows
            );
            
            // Only re-render if we've scrolled significantly
            if (Math.abs(bufferStart - this.startIndex) > 5) {
                this.startIndex = bufferStart;
                this.endIndex = bufferEnd;
                this.renderVisibleRows();
            }
        }
        
        getVisibleRowCount() {
            return Math.ceil(this.viewportHeight / this.rowHeight) + 1;
        }
        
        renderInitialRows() {
            this.renderVisibleRows();
        }
        
        renderVisibleRows() {
            if (!this.contentContainer) return;
            
            const startTime = performance.now();
            
            // Get slice of data to render
            const visibleData = this.originalData.slice(this.startIndex, this.endIndex);
            
            // Clear existing content
            this.contentContainer.innerHTML = '';
            
            // Create table for this slice
            const table = document.createElement('table');
            table.className = 'tc-table tc-virtual-table';
            table.style.cssText = `
                width: 100%;
                border-collapse: collapse;
                margin: 0;
            `;
            
            const tbody = document.createElement('tbody');
            
            // Render rows
            visibleData.forEach((rowData, index) => {
                const realIndex = this.startIndex + index;
                const row = this.renderRow(rowData, realIndex);
                tbody.appendChild(row);
            });
            
            table.appendChild(tbody);
            this.contentContainer.appendChild(table);
            
            // Position content based on scroll
            const offsetY = this.startIndex * this.rowHeight;
            this.contentContainer.style.transform = `translateY(${offsetY}px)`;
            
            // Performance logging
            const renderTime = performance.now() - startTime;
            if (renderTime > 50) { // Log if rendering takes > 50ms
                console.log(`[TableCrafter] Virtual render took ${renderTime.toFixed(2)}ms for ${visibleData.length} rows`);
            }
            
            // Trigger lazy loading for images
            this.setupLazyLoading();
        }
        
        renderRow(rowData, index) {
            const row = document.createElement('tr');
            row.className = 'tc-virtual-row';
            row.setAttribute('data-row-index', index);
            
            // Add hover effect
            row.style.cssText = `
                transition: background-color 0.2s;
                min-height: ${this.rowHeight}px;
            `;
            
            // Create cells based on headers
            const headers = Object.keys(rowData);
            headers.forEach(header => {
                const cell = document.createElement('td');
                cell.style.cssText = `
                    padding: 12px 16px;
                    border-bottom: 1px solid #e1e5e9;
                    vertical-align: top;
                `;
                
                const value = rowData[header];
                cell.innerHTML = this.renderCellContent(value, header);
                
                row.appendChild(cell);
            });
            
            return row;
        }
        
        renderCellContent(value, header) {
            // Handle optimized data types
            if (typeof value === 'object' && value !== null) {
                switch (value.type) {
                    case 'lazy_image':
                        return `<img src="${value.placeholder}" 
                                   data-lazy-src="${value.src}" 
                                   class="tc-lazy-image tc-cell-image" 
                                   alt="Loading..." 
                                   style="max-height: 40px; opacity: 0.7;">`;
                    
                    case 'long_text':
                        return `<div class="tc-expandable-text" data-full="${this.escapeHtml(value.full_content)}">
                                   ${this.escapeHtml(value.preview)}
                                   <button class="tc-expand-btn" onclick="this.parentNode.classList.toggle('expanded')">
                                       <span class="more">...more</span>
                                       <span class="less">less</span>
                                   </button>
                                </div>`;
                    
                    default:
                        return this.escapeHtml(String(value));
                }
            }
            
            // Handle standard values
            return this.formatCellValue(value);
        }
        
        formatCellValue(value) {
            if (value === null || value === undefined) return '';
            
            const str = String(value).trim();
            
            // Image detection
            if (/\.(jpeg|jpg|gif|png|webp|svg|bmp)$/i.test(str) || str.startsWith('data:image')) {
                return `<img src="${str}" class="tc-cell-image" style="max-height: 40px; height: auto;">`;
            }
            
            // Email detection
            if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(str)) {
                return `<a href="mailto:${str}">${str}</a>`;
            }
            
            // URL detection
            if (str.startsWith('http://') || str.startsWith('https://')) {
                return `<a href="${str}" target="_blank" rel="noopener">${str}</a>`;
            }
            
            // Boolean values
            if (str.toLowerCase() === 'true') {
                return '<span class="tc-badge tc-badge-success">Yes</span>';
            }
            if (str.toLowerCase() === 'false') {
                return '<span class="tc-badge tc-badge-error">No</span>';
            }
            
            return this.escapeHtml(str);
        }
        
        setupLazyLoading() {
            if (!PERF_CONFIG.enableLazyImages) return;
            
            const lazyImages = this.contentContainer.querySelectorAll('.tc-lazy-image');
            
            lazyImages.forEach(img => {
                this.intersectionObserver.observe(img);
            });
        }
        
        handleIntersection(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const lazySrc = img.getAttribute('data-lazy-src');
                    
                    if (lazySrc) {
                        // Create new image to preload
                        const newImg = new Image();
                        newImg.onload = () => {
                            img.src = lazySrc;
                            img.style.opacity = '1';
                            img.removeAttribute('data-lazy-src');
                            img.classList.remove('tc-lazy-image');
                        };
                        newImg.onerror = () => {
                            img.alt = 'Failed to load';
                            img.style.opacity = '0.5';
                        };
                        newImg.src = lazySrc;
                    }
                    
                    this.intersectionObserver.unobserve(img);
                }
            });
        }
        
        // Utility methods
        throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
        
        escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
        
        // Public API
        updateData(newData) {
            this.originalData = newData;
            this.totalHeight = newData.length * this.rowHeight;
            this.viewport.style.height = this.totalHeight + 'px';
            this.renderVisibleRows();
        }
        
        scrollToRow(index) {
            const scrollTop = index * this.rowHeight;
            this.viewport.scrollTop = scrollTop;
        }
        
        destroy() {
            if (this.intersectionObserver) {
                this.intersectionObserver.disconnect();
            }
            if (this.virtualContainer && this.virtualContainer.parentNode) {
                this.virtualContainer.remove();
            }
        }
    }
    
    /**
     * Performance Monitor
     * Tracks and reports performance metrics
     */
    class PerformanceMonitor {
        constructor() {
            this.metrics = {
                renderTimes: [],
                scrollEvents: 0,
                memoryUsage: [],
                lastUpdate: Date.now()
            };
            
            this.startMonitoring();
        }
        
        startMonitoring() {
            // Monitor memory usage every 30 seconds
            setInterval(() => {
                if (performance.memory) {
                    this.metrics.memoryUsage.push({
                        used: performance.memory.usedJSHeapSize,
                        total: performance.memory.totalJSHeapSize,
                        timestamp: Date.now()
                    });
                    
                    // Keep only last 10 measurements
                    if (this.metrics.memoryUsage.length > 10) {
                        this.metrics.memoryUsage.shift();
                    }
                }
            }, 30000);
        }
        
        recordRenderTime(time) {
            this.metrics.renderTimes.push(time);
            if (this.metrics.renderTimes.length > 50) {
                this.metrics.renderTimes.shift();
            }
        }
        
        recordScrollEvent() {
            this.metrics.scrollEvents++;
        }
        
        getAverageRenderTime() {
            if (this.metrics.renderTimes.length === 0) return 0;
            
            const sum = this.metrics.renderTimes.reduce((a, b) => a + b, 0);
            return sum / this.metrics.renderTimes.length;
        }
        
        getReport() {
            return {
                averageRenderTime: this.getAverageRenderTime(),
                totalScrollEvents: this.metrics.scrollEvents,
                currentMemory: performance.memory ? performance.memory.usedJSHeapSize : null,
                memoryTrend: this.metrics.memoryUsage
            };
        }
    }
    
    // Initialize performance monitoring
    const perfMonitor = new PerformanceMonitor();
    
    // Extend TableCrafter with virtual scrolling
    if (window.TableCrafter) {
        const originalInit = window.TableCrafter.prototype.init;
        
        window.TableCrafter.prototype.init = function() {
            // Call original init first
            originalInit.call(this);
            
            // Check if we should enable virtual scrolling
            if (this.data && Array.isArray(this.data) && 
                this.data.length > PERF_CONFIG.virtualScrollThreshold &&
                PERF_CONFIG.enableVirtualScrolling) {
                
                console.log(`[TableCrafter] Enabling virtual scrolling for ${this.data.length} rows`);
                
                // Initialize virtual scrolling
                this.virtualScrollManager = new VirtualScrollManager(
                    this.container,
                    this.data,
                    {
                        estimatedRowHeight: 45,
                        bufferRows: PERF_CONFIG.virtualBufferRows
                    }
                );
                
                // Mark as virtual scroll enabled
                this.isVirtualScrollEnabled = true;
                this.container.classList.add('tc-virtual-scroll-enabled');
            }
        };
        
        // Override data update for virtual scroll
        const originalSetData = window.TableCrafter.prototype.setData;
        
        window.TableCrafter.prototype.setData = function(newData) {
            if (this.isVirtualScrollEnabled && this.virtualScrollManager) {
                this.virtualScrollManager.updateData(newData);
                this.data = newData;
            } else {
                originalSetData.call(this, newData);
            }
        };
        
        // Add performance reporting
        window.TableCrafter.prototype.getPerformanceReport = function() {
            return perfMonitor.getReport();
        };
    }
    
    // Add CSS for virtual scrolling and performance optimizations
    const style = document.createElement('style');
    style.textContent = `
        .tc-virtual-scroll-enabled {
            contain: layout style paint;
        }
        
        .tc-virtual-table {
            table-layout: fixed;
            will-change: transform;
        }
        
        .tc-virtual-row {
            contain: layout;
        }
        
        .tc-lazy-image {
            transition: opacity 0.3s ease;
        }
        
        .tc-expandable-text.expanded .more {
            display: none;
        }
        
        .tc-expandable-text .less {
            display: none;
        }
        
        .tc-expandable-text.expanded .less {
            display: inline;
        }
        
        .tc-expand-btn {
            background: none;
            border: none;
            color: #007cba;
            cursor: pointer;
            font-size: 0.9em;
            margin-left: 4px;
        }
        
        .tc-expand-btn:hover {
            text-decoration: underline;
        }
        
        /* Performance monitoring indicator */
        .tc-perf-indicator {
            position: fixed;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            z-index: 10000;
            font-family: monospace;
        }
    `;
    document.head.appendChild(style);
    
    // Expose utilities globally for debugging
    window.TableCrafterPerf = {
        VirtualScrollManager,
        PerformanceMonitor: perfMonitor,
        config: PERF_CONFIG
    };
    
})();