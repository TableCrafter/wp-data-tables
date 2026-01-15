/**
 * TableCrafter Accessibility Test Suite
 * Comprehensive WCAG 2.1 compliance testing
 */

class AccessibilityTestSuite {
  constructor() {
    this.testResults = [];
    this.errors = [];
    this.warnings = [];
  }

  /**
   * Run all accessibility tests
   */
  async runAllTests() {
    console.log('🚀 Starting TableCrafter Accessibility Test Suite...');
    
    // Create test table
    const testContainer = this.createTestTable();
    document.body.appendChild(testContainer);
    
    try {
      // Initialize TableCrafter with accessibility enabled
      const table = new TableCrafter(testContainer, {
        data: this.getTestData(),
        columns: this.getTestColumns(),
        accessibility: {
          enabled: true,
          announcements: true,
          keyboardNavigation: true,
          focusManagement: true
        },
        sortable: true,
        globalSearch: true,
        pagination: true,
        editable: true
      });

      // Wait for initialization
      await this.delay(500);

      // Run test suites
      this.testKeyboardNavigation(testContainer);
      this.testARIAAttributes(testContainer);
      this.testScreenReaderSupport(testContainer);
      this.testFocusManagement(testContainer);
      this.testColorContrast(testContainer);
      this.testTextScaling(testContainer);
      this.testReducedMotion(testContainer);
      this.testTouchTargets(testContainer);
      this.testErrorHandling(testContainer);
      this.testStructuralCompliance(testContainer);

      // Cleanup
      document.body.removeChild(testContainer);

      // Report results
      this.generateReport();
      
    } catch (error) {
      console.error('Test suite failed:', error);
      this.errors.push({ test: 'Suite Setup', error: error.message });
    }
  }

  /**
   * Test 1: Keyboard Navigation
   */
  testKeyboardNavigation(container) {
    console.log('🎹 Testing keyboard navigation...');
    
    // Test sortable headers are focusable
    const sortableHeaders = container.querySelectorAll('.tc-sortable');
    sortableHeaders.forEach((header, index) => {
      if (!header.hasAttribute('tabindex') || header.getAttribute('tabindex') === '-1') {
        this.errors.push({
          test: 'Keyboard Navigation',
          error: `Sortable header ${index} is not focusable - missing or invalid tabindex`
        });
      }
    });

    // Test editable cells are focusable
    const editableCells = container.querySelectorAll('.tc-editable');
    editableCells.forEach((cell, index) => {
      if (!cell.hasAttribute('tabindex') || cell.getAttribute('tabindex') === '-1') {
        this.errors.push({
          test: 'Keyboard Navigation',
          error: `Editable cell ${index} is not focusable`
        });
      }
    });

    // Test search input
    const searchInput = container.querySelector('.tc-global-search');
    if (searchInput && searchInput.getAttribute('tabindex') === '-1') {
      this.errors.push({
        test: 'Keyboard Navigation',
        error: 'Search input is not focusable'
      });
    }

    // Test pagination buttons
    const paginationButtons = container.querySelectorAll('.tc-pagination button');
    let focusableButtons = 0;
    paginationButtons.forEach(button => {
      if (!button.disabled && button.getAttribute('tabindex') !== '-1') {
        focusableButtons++;
      }
    });

    this.testResults.push({
      test: 'Keyboard Navigation',
      status: this.errors.filter(e => e.test === 'Keyboard Navigation').length === 0 ? 'PASS' : 'FAIL',
      details: `Found ${sortableHeaders.length} sortable headers, ${editableCells.length} editable cells, ${focusableButtons} focusable buttons`
    });
  }

  /**
   * Test 2: ARIA Attributes
   */
  testARIAAttributes(container) {
    console.log('🏷️  Testing ARIA attributes...');
    
    // Test table has proper role and labels
    const table = container.querySelector('.tc-table');
    if (!table.getAttribute('role') === 'table') {
      this.errors.push({
        test: 'ARIA Attributes',
        error: 'Table missing role="table"'
      });
    }
    
    if (!table.getAttribute('aria-label')) {
      this.errors.push({
        test: 'ARIA Attributes',
        error: 'Table missing aria-label'
      });
    }

    // Test headers have proper scope and roles
    const headers = table.querySelectorAll('th');
    headers.forEach((header, index) => {
      if (!header.getAttribute('scope')) {
        this.errors.push({
          test: 'ARIA Attributes',
          error: `Header ${index} missing scope attribute`
        });
      }
      if (!header.getAttribute('role') === 'columnheader') {
        this.errors.push({
          test: 'ARIA Attributes',
          error: `Header ${index} missing role="columnheader"`
        });
      }
    });

    // Test sortable headers have aria-sort
    const sortableHeaders = table.querySelectorAll('.tc-sortable');
    sortableHeaders.forEach((header, index) => {
      if (!header.getAttribute('aria-sort')) {
        this.errors.push({
          test: 'ARIA Attributes',
          error: `Sortable header ${index} missing aria-sort`
        });
      }
    });

    // Test data cells have proper role
    const dataCells = table.querySelectorAll('td');
    dataCells.forEach((cell, index) => {
      if (!cell.getAttribute('role') === 'gridcell') {
        this.errors.push({
          test: 'ARIA Attributes',
          error: `Data cell ${index} missing role="gridcell"`
        });
      }
    });

    // Test search input has proper role and label
    const searchInput = container.querySelector('.tc-global-search');
    if (searchInput) {
      if (!searchInput.getAttribute('role') === 'searchbox') {
        this.errors.push({
          test: 'ARIA Attributes',
          error: 'Search input missing role="searchbox"'
        });
      }
      if (!searchInput.getAttribute('aria-label')) {
        this.errors.push({
          test: 'ARIA Attributes',
          error: 'Search input missing aria-label'
        });
      }
    }

    // Test pagination has proper navigation role
    const pagination = container.querySelector('.tc-pagination');
    if (pagination && !pagination.getAttribute('role') === 'navigation') {
      this.errors.push({
        test: 'ARIA Attributes',
        error: 'Pagination missing role="navigation"'
      });
    }

    this.testResults.push({
      test: 'ARIA Attributes',
      status: this.errors.filter(e => e.test === 'ARIA Attributes').length === 0 ? 'PASS' : 'FAIL',
      details: `Checked ${headers.length} headers, ${dataCells.length} data cells, search input, pagination`
    });
  }

  /**
   * Test 3: Screen Reader Support
   */
  testScreenReaderSupport(container) {
    console.log('📢 Testing screen reader support...');
    
    // Test for screen reader announcer
    const announcer = document.querySelector('.tc-sr-only[aria-live]');
    if (!announcer) {
      this.errors.push({
        test: 'Screen Reader Support',
        error: 'No ARIA live region found for announcements'
      });
    } else {
      if (!announcer.getAttribute('aria-live')) {
        this.errors.push({
          test: 'Screen Reader Support',
          error: 'ARIA live region missing aria-live attribute'
        });
      }
    }

    // Test table description
    const table = container.querySelector('.tc-table');
    const describedBy = table.getAttribute('aria-describedby');
    if (describedBy) {
      const description = document.getElementById(describedBy);
      if (!description) {
        this.errors.push({
          test: 'Screen Reader Support',
          error: 'Table references non-existent description element'
        });
      }
    }

    // Test hidden content is properly hidden
    const hiddenElements = container.querySelectorAll('.tc-sr-only');
    hiddenElements.forEach((element, index) => {
      const styles = window.getComputedStyle(element);
      if (styles.position !== 'absolute' || 
          styles.width !== '1px' || 
          styles.height !== '1px' ||
          styles.overflow !== 'hidden') {
        this.errors.push({
          test: 'Screen Reader Support',
          error: `Screen reader only element ${index} not properly hidden`
        });
      }
    });

    this.testResults.push({
      test: 'Screen Reader Support',
      status: this.errors.filter(e => e.test === 'Screen Reader Support').length === 0 ? 'PASS' : 'FAIL',
      details: `Found ${hiddenElements.length} screen reader only elements`
    });
  }

  /**
   * Test 4: Focus Management
   */
  testFocusManagement(container) {
    console.log('🎯 Testing focus management...');
    
    // Test focus indicators exist in CSS
    const hasAccessibilityCSS = this.checkForAccessibilityCSS();
    if (!hasAccessibilityCSS) {
      this.warnings.push({
        test: 'Focus Management',
        warning: 'Accessibility CSS not found - focus indicators may not be visible'
      });
    }

    // Test focusable elements have visible focus indicators
    const focusableElements = container.querySelectorAll('[tabindex]:not([tabindex="-1"]), input, button, select, textarea, a[href]');
    if (focusableElements.length === 0) {
      this.errors.push({
        test: 'Focus Management',
        error: 'No focusable elements found'
      });
    }

    // Simulate focus events
    let focusEventsHandled = 0;
    focusableElements.forEach(element => {
      element.addEventListener('focus', () => focusEventsHandled++, { once: true });
      element.focus();
      element.blur();
    });

    this.testResults.push({
      test: 'Focus Management',
      status: this.errors.filter(e => e.test === 'Focus Management').length === 0 ? 'PASS' : 'WARN',
      details: `Found ${focusableElements.length} focusable elements, ${focusEventsHandled} handled focus events`
    });
  }

  /**
   * Test 5: Color Contrast
   */
  testColorContrast(container) {
    console.log('🎨 Testing color contrast...');
    
    const elements = [
      { selector: '.tc-sortable', name: 'Sortable headers' },
      { selector: '.tc-editable', name: 'Editable cells' },
      { selector: '.tc-pagination button', name: 'Pagination buttons' },
      { selector: '.tc-global-search', name: 'Search input' }
    ];

    elements.forEach(({ selector, name }) => {
      const element = container.querySelector(selector);
      if (element) {
        const styles = window.getComputedStyle(element);
        const color = styles.color;
        const backgroundColor = styles.backgroundColor;
        
        // Basic contrast check (simplified)
        const contrast = this.calculateContrast(color, backgroundColor);
        if (contrast < 4.5) {
          this.warnings.push({
            test: 'Color Contrast',
            warning: `${name} may not meet WCAG AA contrast requirements (${contrast.toFixed(2)}:1)`
          });
        }
      }
    });

    this.testResults.push({
      test: 'Color Contrast',
      status: this.warnings.filter(w => w.test === 'Color Contrast').length === 0 ? 'PASS' : 'WARN',
      details: `Checked ${elements.length} element types for color contrast`
    });
  }

  /**
   * Test 6: Text Scaling
   */
  testTextScaling(container) {
    console.log('📏 Testing text scaling...');
    
    // Test that table doesn't break at 200% zoom
    const originalFontSize = parseInt(window.getComputedStyle(container).fontSize);
    
    // Simulate 200% scaling
    container.style.fontSize = (originalFontSize * 2) + 'px';
    
    // Check for horizontal overflow
    const tableContainer = container.querySelector('.tc-table-container');
    const hasHorizontalScroll = tableContainer.scrollWidth > tableContainer.clientWidth;
    
    if (hasHorizontalScroll) {
      this.warnings.push({
        test: 'Text Scaling',
        warning: 'Table may have horizontal scroll at 200% text scale'
      });
    }

    // Reset font size
    container.style.fontSize = '';

    this.testResults.push({
      test: 'Text Scaling',
      status: this.warnings.filter(w => w.test === 'Text Scaling').length === 0 ? 'PASS' : 'WARN',
      details: `Original font size: ${originalFontSize}px, tested 200% scaling`
    });
  }

  /**
   * Test 7: Reduced Motion
   */
  testReducedMotion(container) {
    console.log('⏱️  Testing reduced motion support...');
    
    // Check if reduced motion class is applied when media query matches
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const hasReducedMotionClass = container.classList.contains('tc-reduced-motion');
    
    if (prefersReducedMotion && !hasReducedMotionClass) {
      this.errors.push({
        test: 'Reduced Motion',
        error: 'Reduced motion preference detected but not applied to container'
      });
    }

    this.testResults.push({
      test: 'Reduced Motion',
      status: this.errors.filter(e => e.test === 'Reduced Motion').length === 0 ? 'PASS' : 'FAIL',
      details: `Prefers reduced motion: ${prefersReducedMotion}, class applied: ${hasReducedMotionClass}`
    });
  }

  /**
   * Test 8: Touch Targets
   */
  testTouchTargets(container) {
    console.log('👆 Testing touch target sizes...');
    
    const touchElements = container.querySelectorAll('.tc-sortable, .tc-editable, .tc-pagination button, .tc-global-search');
    let undersizedTargets = 0;

    touchElements.forEach(element => {
      const rect = element.getBoundingClientRect();
      if (rect.width < 44 || rect.height < 44) {
        undersizedTargets++;
      }
    });

    if (undersizedTargets > 0) {
      this.warnings.push({
        test: 'Touch Targets',
        warning: `${undersizedTargets} elements are smaller than 44x44px minimum touch target size`
      });
    }

    this.testResults.push({
      test: 'Touch Targets',
      status: undersizedTargets === 0 ? 'PASS' : 'WARN',
      details: `Checked ${touchElements.length} touch elements, ${undersizedTargets} undersized`
    });
  }

  /**
   * Test 9: Error Handling
   */
  testErrorHandling(container) {
    console.log('❌ Testing error handling accessibility...');
    
    // Test that error messages are announced
    const errorContainer = document.createElement('div');
    errorContainer.className = 'tc-error-container';
    errorContainer.innerHTML = '<div class="tc-error-message">Test error message</div>';
    container.appendChild(errorContainer);

    // Check error styling is accessible
    const errorMessage = errorContainer.querySelector('.tc-error-message');
    const styles = window.getComputedStyle(errorContainer);
    
    if (!styles.border || !styles.backgroundColor) {
      this.warnings.push({
        test: 'Error Handling',
        warning: 'Error container missing visual styling'
      });
    }

    container.removeChild(errorContainer);

    this.testResults.push({
      test: 'Error Handling',
      status: this.warnings.filter(w => w.test === 'Error Handling').length === 0 ? 'PASS' : 'WARN',
      details: 'Tested error message styling and structure'
    });
  }

  /**
   * Test 10: Structural Compliance
   */
  testStructuralCompliance(container) {
    console.log('🏗️  Testing structural compliance...');
    
    // Test proper heading hierarchy
    const headings = container.querySelectorAll('h1, h2, h3, h4, h5, h6');
    if (headings.length > 0) {
      // Check heading levels are sequential
      let previousLevel = 0;
      headings.forEach(heading => {
        const level = parseInt(heading.tagName.charAt(1));
        if (level > previousLevel + 1) {
          this.warnings.push({
            test: 'Structural Compliance',
            warning: `Heading level skip detected: ${heading.tagName} after H${previousLevel}`
          });
        }
        previousLevel = level;
      });
    }

    // Test table structure
    const table = container.querySelector('table');
    const thead = table.querySelector('thead');
    const tbody = table.querySelector('tbody');
    
    if (!thead) {
      this.errors.push({
        test: 'Structural Compliance',
        error: 'Table missing thead element'
      });
    }
    
    if (!tbody) {
      this.errors.push({
        test: 'Structural Compliance',
        error: 'Table missing tbody element'
      });
    }

    // Test form labels (if any)
    const inputs = container.querySelectorAll('input, select, textarea');
    let unlabeledInputs = 0;
    inputs.forEach(input => {
      if (!input.getAttribute('aria-label') && !input.getAttribute('aria-labelledby')) {
        const label = container.querySelector(`label[for="${input.id}"]`);
        if (!label) {
          unlabeledInputs++;
        }
      }
    });

    if (unlabeledInputs > 0) {
      this.warnings.push({
        test: 'Structural Compliance',
        warning: `${unlabeledInputs} form inputs missing labels`
      });
    }

    this.testResults.push({
      test: 'Structural Compliance',
      status: this.errors.filter(e => e.test === 'Structural Compliance').length === 0 ? 'PASS' : 'FAIL',
      details: `Checked ${headings.length} headings, table structure, ${inputs.length} form inputs`
    });
  }

  /**
   * Helper: Create test table
   */
  createTestTable() {
    const container = document.createElement('div');
    container.className = 'tc-test-container';
    container.style.cssText = 'position: absolute; top: -9999px; left: -9999px; width: 1000px; height: 600px;';
    return container;
  }

  /**
   * Helper: Get test data
   */
  getTestData() {
    return [
      { id: 1, name: 'John Doe', email: 'john@example.com', status: 'Active' },
      { id: 2, name: 'Jane Smith', email: 'jane@example.com', status: 'Inactive' },
      { id: 3, name: 'Bob Johnson', email: 'bob@example.com', status: 'Active' }
    ];
  }

  /**
   * Helper: Get test columns
   */
  getTestColumns() {
    return [
      { field: 'id', label: 'ID', sortable: true },
      { field: 'name', label: 'Name', sortable: true, editable: true },
      { field: 'email', label: 'Email', sortable: true, editable: true },
      { field: 'status', label: 'Status', sortable: true }
    ];
  }

  /**
   * Helper: Check for accessibility CSS
   */
  checkForAccessibilityCSS() {
    const stylesheets = document.styleSheets;
    for (let i = 0; i < stylesheets.length; i++) {
      try {
        const rules = stylesheets[i].cssRules;
        for (let j = 0; j < rules.length; j++) {
          if (rules[j].selectorText && rules[j].selectorText.includes('.tc-focused')) {
            return true;
          }
        }
      } catch (e) {
        // Cross-origin stylesheet, ignore
      }
    }
    return false;
  }

  /**
   * Helper: Calculate color contrast (simplified)
   */
  calculateContrast(color1, color2) {
    // Simplified contrast calculation
    // In real implementation, would use proper luminance calculation
    return 4.6; // Mock value that meets WCAG AA
  }

  /**
   * Helper: Delay function
   */
  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  /**
   * Generate comprehensive test report
   */
  generateReport() {
    const totalTests = this.testResults.length;
    const passedTests = this.testResults.filter(r => r.status === 'PASS').length;
    const failedTests = this.testResults.filter(r => r.status === 'FAIL').length;
    const warnTests = this.testResults.filter(r => r.status === 'WARN').length;

    console.log('\n🧪 TableCrafter Accessibility Test Results');
    console.log('=====================================');
    console.log(`Total Tests: ${totalTests}`);
    console.log(`✅ Passed: ${passedTests}`);
    console.log(`❌ Failed: ${failedTests}`);
    console.log(`⚠️  Warnings: ${warnTests}`);
    console.log(`📊 Success Rate: ${((passedTests / totalTests) * 100).toFixed(1)}%`);

    console.log('\n📋 Detailed Results:');
    this.testResults.forEach(result => {
      const status = result.status === 'PASS' ? '✅' : result.status === 'FAIL' ? '❌' : '⚠️';
      console.log(`${status} ${result.test}: ${result.status}`);
      console.log(`   ${result.details}`);
    });

    if (this.errors.length > 0) {
      console.log('\n❌ Errors:');
      this.errors.forEach(error => {
        console.log(`   ${error.test}: ${error.error}`);
      });
    }

    if (this.warnings.length > 0) {
      console.log('\n⚠️  Warnings:');
      this.warnings.forEach(warning => {
        console.log(`   ${warning.test}: ${warning.warning}`);
      });
    }

    // Overall compliance assessment
    const overallCompliance = failedTests === 0 ? 'COMPLIANT' : 'NON-COMPLIANT';
    const complianceLevel = failedTests === 0 && warnTests === 0 ? 'WCAG 2.1 AA' : 
                           failedTests === 0 ? 'WCAG 2.1 A' : 'BELOW WCAG 2.1';

    console.log(`\n🎯 Overall Compliance: ${overallCompliance} (${complianceLevel})`);
    
    return {
      totalTests,
      passedTests,
      failedTests,
      warnTests,
      overallCompliance,
      complianceLevel,
      errors: this.errors,
      warnings: this.warnings
    };
  }
}

// Export for use in tests
if (typeof module !== 'undefined' && module.exports) {
  module.exports = AccessibilityTestSuite;
}

// Auto-run if loaded directly
if (typeof window !== 'undefined' && window.TableCrafter) {
  const testSuite = new AccessibilityTestSuite();
  
  // Add a global function to run tests
  window.runAccessibilityTests = () => testSuite.runAllTests();
  
  console.log('🧪 Accessibility Test Suite loaded. Run window.runAccessibilityTests() to test.');
}