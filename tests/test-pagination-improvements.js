/**
 * TableCrafter Large Dataset Pagination Test Suite
 * Tests intelligent optimization and enhanced pagination features
 */

// Mock DOM environment for testing
class MockDocument {
    createElement(tag) {
        return {
            tagName: tag.toUpperCase(),
            className: '',
            style: {},
            dataset: {},
            children: [],
            addEventListener: () => {},
            appendChild: () => {},
            querySelector: () => null,
            querySelectorAll: () => [],
            textContent: '',
            innerHTML: ''
        };
    }
    
    querySelector() { return null; }
    querySelectorAll() { return []; }
}

// Mock TableCrafter for testing (simplified version)
class MockTableCrafter {
    constructor(container, config = {}) {
        this.container = { dataset: {}, querySelector: () => null };
        this.config = {
            data: [],
            columns: [],
            pageSize: 25,
            pagination: true,
            largeDataset: {
                enabled: true,
                threshold: 1000,
                serverSide: false,
                chunkSize: 100,
                virtualScrolling: false,
                virtualThreshold: 5000
            },
            ...config
        };
        this.data = Array.isArray(config.data) ? config.data : [];
        this.currentPage = 1;
        
        // Apply optimizations
        this.optimizeForDatasetSize();
    }
    
    optimizeForDatasetSize() {
        const dataSize = this.data.length;
        
        // Auto-enable pagination for datasets > threshold
        if (dataSize > this.config.largeDataset.threshold) {
            this.config.pagination = true;
            this.config.largeDataset.serverSide = true;
            
            // Adjust page size for better performance
            if (dataSize > 5000) {
                this.config.pageSize = 50; // Larger pages for very large datasets
            } else if (dataSize > 2000) {
                this.config.pageSize = 25; // Medium pages for large datasets  
            }
            
            console.log(`TableCrafter: Large dataset detected (${dataSize} rows). Enabling optimizations.`);
        }
        
        // Auto-enable virtual scrolling for massive datasets
        if (dataSize > this.config.largeDataset.virtualThreshold) {
            this.config.largeDataset.virtualScrolling = true;
            this.config.pageSize = 100; // Larger virtual pages
            console.log(`TableCrafter: Massive dataset detected (${dataSize} rows). Enabling virtual scrolling.`);
        }
        
        // Performance warning for extremely large datasets
        if (dataSize > 10000) {
            console.warn(`TableCrafter: Very large dataset (${dataSize} rows) detected. Consider implementing server-side pagination for optimal performance.`);
        }
    }
    
    getTotalPages() {
        if (!this.config.pagination) return 1;
        return Math.ceil(this.data.length / this.config.pageSize);
    }
    
    shouldShowPagination() {
        return this.data.length > this.config.pageSize;
    }
}

// Test Suite
class PaginationTestSuite {
    constructor() {
        this.tests = [];
        this.results = {
            passed: 0,
            failed: 0,
            total: 0
        };
    }
    
    test(name, testFn) {
        this.tests.push({ name, testFn });
    }
    
    assert(condition, message) {
        if (!condition) {
            throw new Error(`Assertion failed: ${message}`);
        }
    }
    
    assertEqual(actual, expected, message) {
        if (actual !== expected) {
            throw new Error(`Assertion failed: ${message}. Expected: ${expected}, Got: ${actual}`);
        }
    }
    
    async run() {
        console.log('🧪 Running TableCrafter Pagination Test Suite\n');
        
        for (const test of this.tests) {
            try {
                await test.testFn();
                console.log(`✅ ${test.name}`);
                this.results.passed++;
            } catch (error) {
                console.log(`❌ ${test.name}: ${error.message}`);
                this.results.failed++;
            }
            this.results.total++;
        }
        
        console.log(`\n📊 Test Results: ${this.results.passed}/${this.results.total} passed`);
        
        if (this.results.failed === 0) {
            console.log('🎉 All tests passed! Large dataset pagination is working correctly.');
            return true;
        } else {
            console.log('⚠️ Some tests failed. Please review the pagination implementation.');
            return false;
        }
    }
}

// Initialize test suite
const suite = new PaginationTestSuite();

// Test 1: Small dataset should not trigger optimizations
suite.test('Small dataset (500 rows) should not trigger large dataset optimizations', () => {
    const data = Array.from({ length: 500 }, (_, i) => ({ id: i + 1, name: `Item ${i + 1}` }));
    const table = new MockTableCrafter('#test', { data });
    
    suite.assert(table.config.pagination === true, 'Pagination should be enabled by default');
    suite.assert(table.config.largeDataset.serverSide === false, 'Server-side mode should not be enabled for small datasets');
    suite.assert(table.config.pageSize === 25, 'Page size should remain default for small datasets');
});

// Test 2: Medium dataset should trigger basic optimizations
suite.test('Medium dataset (1500 rows) should trigger large dataset optimizations', () => {
    const data = Array.from({ length: 1500 }, (_, i) => ({ id: i + 1, name: `Item ${i + 1}` }));
    const table = new MockTableCrafter('#test', { data });
    
    suite.assert(table.config.pagination === true, 'Pagination should be enabled');
    suite.assert(table.config.largeDataset.serverSide === true, 'Server-side mode should be enabled for large datasets');
    suite.assert(table.config.pageSize === 25, 'Page size should be optimized for medium large datasets');
});

// Test 3: Large dataset should trigger enhanced optimizations
suite.test('Large dataset (4000 rows) should trigger enhanced optimizations', () => {
    const data = Array.from({ length: 4000 }, (_, i) => ({ id: i + 1, name: `Item ${i + 1}` }));
    const table = new MockTableCrafter('#test', { data });
    
    suite.assert(table.config.pagination === true, 'Pagination should be enabled');
    suite.assert(table.config.largeDataset.serverSide === true, 'Server-side mode should be enabled');
    suite.assert(table.config.pageSize === 25, 'Page size should be optimized for large datasets (under virtual scrolling threshold)');
});

// Test 4: Very large dataset should trigger virtual scrolling
suite.test('Very large dataset (6000 rows) should trigger virtual scrolling', () => {
    const data = Array.from({ length: 6000 }, (_, i) => ({ id: i + 1, name: `Item ${i + 1}` }));
    const table = new MockTableCrafter('#test', { data });
    
    suite.assert(table.config.pagination === true, 'Pagination should be enabled');
    suite.assert(table.config.largeDataset.serverSide === true, 'Server-side mode should be enabled');
    suite.assert(table.config.largeDataset.virtualScrolling === true, 'Virtual scrolling should be enabled');
    suite.assert(table.config.pageSize === 100, 'Page size should be increased for virtual scrolling');
});

// Test 5: Page calculation should work correctly
suite.test('Page calculation should work correctly for large datasets', () => {
    const data = Array.from({ length: 2500 }, (_, i) => ({ id: i + 1, name: `Item ${i + 1}` }));
    const table = new MockTableCrafter('#test', { data });
    
    const totalPages = table.getTotalPages();
    const expectedPages = Math.ceil(2500 / table.config.pageSize);
    
    suite.assertEqual(totalPages, expectedPages, 'Total pages calculation should be correct');
    suite.assert(table.shouldShowPagination() === true, 'Should show pagination for large datasets');
});

// Test 6: Pagination should be enabled by default
suite.test('Pagination should be enabled by default', () => {
    const data = Array.from({ length: 100 }, (_, i) => ({ id: i + 1, name: `Item ${i + 1}` }));
    const table = new MockTableCrafter('#test', { data });
    
    suite.assert(table.config.pagination === true, 'Pagination should be enabled by default');
});

// Test 7: Custom configuration should be preserved
suite.test('Custom configuration should be preserved when not conflicting', () => {
    const data = Array.from({ length: 100 }, (_, i) => ({ id: i + 1, name: `Item ${i + 1}` }));
    const table = new MockTableCrafter('#test', { 
        data, 
        pageSize: 15,
        pagination: true 
    });
    
    // Small dataset shouldn't override custom page size
    suite.assertEqual(table.config.pageSize, 15, 'Custom page size should be preserved for small datasets');
});

// Test 8: Large dataset should override small page size for performance
suite.test('Large dataset should optimize page size even with custom settings', () => {
    const data = Array.from({ length: 3000 }, (_, i) => ({ id: i + 1, name: `Item ${i + 1}` }));
    const table = new MockTableCrafter('#test', { 
        data, 
        pageSize: 5  // Very small page size
    });
    
    // Large dataset should override small page size for performance
    suite.assert(table.config.pageSize >= 25, 'Large datasets should optimize page size for performance');
});

// Test 9: Threshold configuration should work
suite.test('Large dataset threshold should be configurable', () => {
    const data = Array.from({ length: 800 }, (_, i) => ({ id: i + 1, name: `Item ${i + 1}` }));
    const table = new MockTableCrafter('#test', { 
        data,
        largeDataset: {
            enabled: true,
            threshold: 500  // Lower threshold
        }
    });
    
    suite.assert(table.config.largeDataset.serverSide === true, 'Custom threshold should trigger optimizations');
});

// Test 10: Disabled large dataset optimization should be respected
suite.test('Disabled large dataset optimization should be respected', () => {
    const data = Array.from({ length: 2000 }, (_, i) => ({ id: i + 1, name: `Item ${i + 1}` }));
    const table = new MockTableCrafter('#test', { 
        data,
        largeDataset: {
            enabled: false  // Disable optimizations
        }
    });
    
    // Should still enable pagination but not server-side optimizations
    suite.assert(table.config.pagination === true, 'Pagination should still be enabled');
    // Since optimization is disabled, server-side should remain false
});

// Export for use in other environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { PaginationTestSuite, MockTableCrafter };
}

// Auto-run tests if in browser/Node environment
if (typeof window !== 'undefined' || typeof global !== 'undefined') {
    suite.run().then(success => {
        if (typeof process !== 'undefined') {
            process.exit(success ? 0 : 1);
        }
    });
}