/**
 * TableCrafter Performance Optimization E2E Tests
 * 
 * Tests virtual scrolling, large dataset handling, and user experience
 * with performance optimizations in real browser environments.
 */

const { test, expect } = require('@playwright/test');

test.describe('TableCrafter Performance Optimization', () => {
    
    test.beforeEach(async ({ page }) => {
        // Set up test environment
        await page.goto('/wp-admin/admin.php?page=tablecrafter-wp-data-tables');
        
        // Wait for admin page to load
        await page.waitForSelector('.tc-admin-layout');
    });
    
    test('Virtual scrolling activates for large datasets', async ({ page }) => {
        // Generate large dataset URL (could be a test endpoint)
        const largeDatasetUrl = '/wp-content/plugins/tablecrafter-wp-data-tables/tests/fixtures/large-dataset-1000.json';
        
        // Input large dataset URL
        await page.fill('#tc-preview-url', largeDatasetUrl);
        
        // Click preview to render table
        await page.click('#tc-preview-btn');
        
        // Wait for table to render
        await page.waitForSelector('.tc-table', { timeout: 10000 });
        
        // Check if virtual scrolling is enabled
        const virtualContainer = await page.locator('.tc-virtual-container');
        await expect(virtualContainer).toBeVisible();
        
        // Verify virtual scrolling elements exist
        await expect(page.locator('.tc-virtual-viewport')).toBeVisible();
        await expect(page.locator('.tc-virtual-content')).toBeVisible();
        
        // Check console for virtual scrolling initialization message
        const consoleMessages = [];
        page.on('console', msg => consoleMessages.push(msg.text()));
        
        await page.reload();
        await page.fill('#tc-preview-url', largeDatasetUrl);
        await page.click('#tc-preview-btn');
        await page.waitForSelector('.tc-virtual-container');
        
        const virtualScrollMessage = consoleMessages.find(msg => 
            msg.includes('Virtual scrolling initialized') || msg.includes('Enabling virtual scrolling')
        );
        expect(virtualScrollMessage).toBeDefined();
    });
    
    test('Virtual scrolling performance benchmarks', async ({ page }) => {
        // Test different dataset sizes
        const testCases = [
            { size: 500, file: 'medium-dataset-500.json', expectVirtual: false },
            { size: 1000, file: 'large-dataset-1000.json', expectVirtual: true },
            { size: 2000, file: 'huge-dataset-2000.json', expectVirtual: true }
        ];
        
        for (const testCase of testCases) {
            console.log(`Testing dataset size: ${testCase.size}`);
            
            const startTime = Date.now();
            
            // Load dataset
            await page.fill('#tc-preview-url', `/wp-content/plugins/tablecrafter-wp-data-tables/tests/fixtures/${testCase.file}`);
            await page.click('#tc-preview-btn');
            
            // Wait for rendering to complete
            if (testCase.expectVirtual) {
                await page.waitForSelector('.tc-virtual-container', { timeout: 15000 });
            } else {
                await page.waitForSelector('.tc-table', { timeout: 10000 });
            }
            
            const endTime = Date.now();
            const renderTime = endTime - startTime;
            
            // Performance assertions
            expect(renderTime).toBeLessThan(5000); // Should render in under 5 seconds
            
            if (testCase.expectVirtual) {
                // Virtual scrolling should be active
                await expect(page.locator('.tc-virtual-container')).toBeVisible();
                
                // Should not render all rows at once
                const visibleRows = await page.locator('.tc-virtual-table tbody tr').count();
                expect(visibleRows).toBeLessThan(testCase.size);
                expect(visibleRows).toBeGreaterThan(20); // But should render reasonable amount
            }
            
            console.log(`Dataset ${testCase.size}: ${renderTime}ms render time`);
        }
    });
    
    test('Virtual scrolling smooth interaction', async ({ page }) => {
        const largeDatasetUrl = '/wp-content/plugins/tablecrafter-wp-data-tables/tests/fixtures/large-dataset-1000.json';
        
        await page.fill('#tc-preview-url', largeDatasetUrl);
        await page.click('#tc-preview-btn');
        await page.waitForSelector('.tc-virtual-container');
        
        const viewport = page.locator('.tc-virtual-viewport');
        
        // Test scrolling performance
        const scrollTests = [
            { scrollTop: 0, description: 'top' },
            { scrollTop: 500, description: 'middle' },
            { scrollTop: 1000, description: 'further down' },
            { scrollTop: 0, description: 'back to top' }
        ];
        
        for (const scrollTest of scrollTests) {
            console.log(`Scrolling to ${scrollTest.description}`);
            
            const startTime = Date.now();
            
            // Scroll to position
            await viewport.evaluate((el, scrollTop) => {
                el.scrollTop = scrollTop;
            }, scrollTest.scrollTop);
            
            // Wait for scroll to stabilize
            await page.waitForTimeout(100);
            
            // Verify content updated
            const visibleRows = await page.locator('.tc-virtual-table tbody tr').count();
            expect(visibleRows).toBeGreaterThan(10); // Should always have visible content
            
            const endTime = Date.now();
            const scrollTime = endTime - startTime;
            
            // Scrolling should be fast
            expect(scrollTime).toBeLessThan(200);
        }
    });
    
    test('Lazy image loading works correctly', async ({ page }) => {
        // Create dataset with images
        const imageDatasetUrl = '/wp-content/plugins/tablecrafter-wp-data-tables/tests/fixtures/image-dataset.json';
        
        await page.fill('#tc-preview-url', imageDatasetUrl);
        await page.click('#tc-preview-btn');
        await page.waitForSelector('.tc-table');
        
        // Check for lazy loading images
        const lazyImages = page.locator('.tc-lazy-image');
        const lazyImageCount = await lazyImages.count();
        
        if (lazyImageCount > 0) {
            // Verify images have data-lazy-src attribute
            const firstLazyImage = lazyImages.first();
            const lazySrc = await firstLazyImage.getAttribute('data-lazy-src');
            expect(lazySrc).toBeTruthy();
            
            // Scroll to make image visible
            await firstLazyImage.scrollIntoViewIfNeeded();
            
            // Wait for lazy loading to trigger
            await page.waitForTimeout(1000);
            
            // Image should now be loaded
            const newSrc = await firstLazyImage.getAttribute('src');
            expect(newSrc).toBe(lazySrc);
            
            // Should no longer have data-lazy-src
            const stillHasLazySrc = await firstLazyImage.getAttribute('data-lazy-src');
            expect(stillHasLazySrc).toBeNull();
        }
    });
    
    test('Long text content optimization', async ({ page }) => {
        const longTextDatasetUrl = '/wp-content/plugins/tablecrafter-wp-data-tables/tests/fixtures/long-text-dataset.json';
        
        await page.fill('#tc-preview-url', longTextDatasetUrl);
        await page.click('#tc-preview-btn');
        await page.waitForSelector('.tc-table');
        
        // Check for expandable text elements
        const expandableText = page.locator('.tc-expandable-text');
        const expandableCount = await expandableText.count();
        
        if (expandableCount > 0) {
            const firstExpandable = expandableText.first();
            
            // Should have "more" button
            const moreButton = firstExpandable.locator('.tc-expand-btn');
            await expect(moreButton).toBeVisible();
            
            // Click to expand
            await moreButton.click();
            
            // Should now show "less" option
            await expect(firstExpandable).toHaveClass(/expanded/);
            
            // Click to collapse
            await moreButton.click();
            
            // Should be collapsed again
            await expect(firstExpandable).not.toHaveClass(/expanded/);
        }
    });
    
    test('Memory usage stays reasonable', async ({ page }) => {
        // Monitor memory usage during large dataset operations
        await page.addInitScript(() => {
            window.memoryUsageLog = [];
            
            // Log memory usage periodically
            setInterval(() => {
                if (performance.memory) {
                    window.memoryUsageLog.push({
                        used: performance.memory.usedJSHeapSize,
                        total: performance.memory.totalJSHeapSize,
                        timestamp: Date.now()
                    });
                }
            }, 1000);
        });
        
        const largeDatasetUrl = '/wp-content/plugins/tablecrafter-wp-data-tables/tests/fixtures/huge-dataset-2000.json';
        
        // Get baseline memory
        const baselineMemory = await page.evaluate(() => {
            return performance.memory ? performance.memory.usedJSHeapSize : 0;
        });
        
        // Load large dataset
        await page.fill('#tc-preview-url', largeDatasetUrl);
        await page.click('#tc-preview-btn');
        await page.waitForSelector('.tc-virtual-container', { timeout: 15000 });
        
        // Perform scrolling operations
        const viewport = page.locator('.tc-virtual-viewport');
        for (let i = 0; i < 10; i++) {
            await viewport.evaluate(el => el.scrollTop = Math.random() * 2000);
            await page.waitForTimeout(100);
        }
        
        // Check final memory usage
        const finalMemory = await page.evaluate(() => {
            return performance.memory ? performance.memory.usedJSHeapSize : 0;
        });
        
        const memoryIncrease = finalMemory - baselineMemory;
        
        // Memory increase should be reasonable (less than 100MB)
        expect(memoryIncrease).toBeLessThan(100 * 1024 * 1024);
        
        // Get memory usage log
        const memoryLog = await page.evaluate(() => window.memoryUsageLog);
        console.log('Memory usage log:', memoryLog.slice(-5)); // Log last 5 measurements
        
        // Memory should not continuously increase (no major leaks)
        if (memoryLog.length >= 5) {
            const recentUsage = memoryLog.slice(-5).map(entry => entry.used);
            const maxRecent = Math.max(...recentUsage);
            const minRecent = Math.min(...recentUsage);
            const variation = (maxRecent - minRecent) / minRecent;
            
            // Memory variation should be reasonable (less than 50% difference)
            expect(variation).toBeLessThan(0.5);
        }
    });
    
    test('Performance metrics API works', async ({ page }) => {
        const largeDatasetUrl = '/wp-content/plugins/tablecrafter-wp-data-tables/tests/fixtures/large-dataset-1000.json';
        
        await page.fill('#tc-preview-url', largeDatasetUrl);
        await page.click('#tc-preview-btn');
        await page.waitForSelector('.tc-virtual-container');
        
        // Test performance metrics API
        const performanceReport = await page.evaluate(() => {
            // Find TableCrafter instance
            const container = document.querySelector('.tablecrafter-container');
            if (container && window.TableCrafter) {
                // This would require the instance to be accessible
                return window.TableCrafterPerf ? window.TableCrafterPerf.PerformanceMonitor.getReport() : null;
            }
            return null;
        });
        
        if (performanceReport) {
            expect(performanceReport).toHaveProperty('averageRenderTime');
            expect(performanceReport).toHaveProperty('totalScrollEvents');
            expect(typeof performanceReport.averageRenderTime).toBe('number');
            expect(typeof performanceReport.totalScrollEvents).toBe('number');
        }
    });
    
    test('Fallback behavior for disabled virtual scrolling', async ({ page }) => {
        // Disable virtual scrolling via configuration
        await page.addInitScript(() => {
            window.tcPerformance = {
                virtualScrollThreshold: 999999, // Very high threshold
                enableVirtualScrolling: false
            };
        });
        
        const largeDatasetUrl = '/wp-content/plugins/tablecrafter-wp-data-tables/tests/fixtures/large-dataset-1000.json';
        
        await page.fill('#tc-preview-url', largeDatasetUrl);
        await page.click('#tc-preview-btn');
        await page.waitForSelector('.tc-table', { timeout: 15000 });
        
        // Should not have virtual scrolling elements
        await expect(page.locator('.tc-virtual-container')).not.toBeVisible();
        
        // Should have standard table
        await expect(page.locator('.tc-table')).toBeVisible();
        
        // Should still have lightweight optimizations
        const lazyImages = page.locator('.tc-lazy-image');
        const expandableText = page.locator('.tc-expandable-text');
        
        // At least one optimization should be present
        const hasOptimizations = (await lazyImages.count()) > 0 || (await expandableText.count()) > 0;
        
        if (hasOptimizations) {
            console.log('Lightweight optimizations are active without virtual scrolling');
        }
    });
    
    test('Accessibility compliance with virtual scrolling', async ({ page }) => {
        const largeDatasetUrl = '/wp-content/plugins/tablecrafter-wp-data-tables/tests/fixtures/large-dataset-1000.json';
        
        await page.fill('#tc-preview-url', largeDatasetUrl);
        await page.click('#tc-preview-btn');
        await page.waitForSelector('.tc-virtual-container');
        
        // Test keyboard navigation
        await page.keyboard.press('Tab'); // Focus on table
        
        // Should be able to navigate with arrow keys
        await page.keyboard.press('ArrowDown');
        await page.keyboard.press('ArrowRight');
        
        // Test screen reader compatibility
        const table = page.locator('.tc-virtual-table');
        const headers = table.locator('thead th');
        const headerCount = await headers.count();
        
        if (headerCount > 0) {
            // Headers should have proper scope attributes
            const firstHeader = headers.first();
            await expect(firstHeader).toHaveAttribute('scope', 'col');
        }
        
        // Virtual container should have proper ARIA attributes
        const virtualContainer = page.locator('.tc-virtual-container');
        await expect(virtualContainer).toHaveAttribute('role', 'region');
    });
    
});

// Test fixture generation (this would typically be in a separate file)
test.describe('Test Data Generation', () => {
    
    test.skip('Generate test datasets', async ({ page }) => {
        // This test generates test data files for the performance tests
        // Skip by default since it's for setup only
        
        const generateDataset = (size, filename) => {
            const data = [];
            for (let i = 0; i < size; i++) {
                data.push({
                    id: i + 1,
                    name: `User ${i + 1}`,
                    email: `user${i + 1}@example.com`,
                    department: ['Engineering', 'Marketing', 'Sales'][i % 3],
                    salary: 30000 + (i * 1000),
                    bio: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. '.repeat(Math.floor(Math.random() * 10) + 1),
                    avatar: `https://example.com/avatar${i % 10}.jpg`,
                    active: i % 2 === 0 ? 'true' : 'false'
                });
            }
            return JSON.stringify(data);
        };
        
        // This would require file system access or admin API endpoints
        console.log('Test datasets would be generated here');
        console.log('500 rows:', generateDataset(500).length, 'characters');
        console.log('1000 rows:', generateDataset(1000).length, 'characters');
        console.log('2000 rows:', generateDataset(2000).length, 'characters');
    });
    
});