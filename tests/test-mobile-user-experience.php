<?php

/**
 * Mobile User Experience Test Suite
 * 
 * Tests for mobile responsiveness, touch interactions, and accessibility
 * Part of the business-critical mobile UX improvement initiative
 */
class Test_Mobile_User_Experience extends WP_UnitTestCase {
    private $tablecrafter;
    
    public function setUp(): void {
        parent::setUp();
        $this->tablecrafter = new TableCrafter();
    }
    
    /**
     * Test responsive breakpoint detection
     */
    public function test_responsive_breakpoints_are_configured() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check if responsive configuration is present in the output
        $this->assertStringContainsString('breakpoints', $shortcode_output);
        $this->assertStringContainsString('mobile', $shortcode_output);
        $this->assertStringContainsString('"width":768', $shortcode_output);
        $this->assertStringContainsString('"layout":"cards"', $shortcode_output);
    }
    
    /**
     * Test mobile-specific CSS classes are applied
     */
    public function test_mobile_css_classes_applied() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for mobile-specific CSS classes
        $this->assertStringContainsString('tablecrafter-container', $shortcode_output);
        $this->assertStringContainsString('tc-table-container', $shortcode_output);
        $this->assertStringContainsString('tc-mobile-cards', $shortcode_output);
    }
    
    /**
     * Test touch gesture JavaScript is loaded
     */
    public function test_touch_gesture_javascript_loaded() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check if touch gesture methods are referenced
        $this->assertStringContainsString('addTouchGestures', $shortcode_output);
        $this->assertStringContainsString('isTouchDevice', $shortcode_output);
    }
    
    /**
     * Test mobile card layout structure
     */
    public function test_mobile_card_layout_structure() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for card-specific elements
        $this->assertStringContainsString('tc-mobile-card', $shortcode_output);
        $this->assertStringContainsString('tc-card-header', $shortcode_output);
        $this->assertStringContainsString('tc-card-body', $shortcode_output);
        $this->assertStringContainsString('tc-card-actions', $shortcode_output);
    }
    
    /**
     * Test WCAG touch target accessibility compliance
     */
    public function test_wcag_touch_target_compliance() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for minimum 44px touch targets
        $this->assertStringContainsString('min-height: 44px', $shortcode_output);
        $this->assertStringContainsString('min-width: 44px', $shortcode_output);
    }
    
    /**
     * Test swipe gesture configuration
     */
    public function test_swipe_gesture_configuration() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for swipe-related configuration
        $this->assertStringContainsString('swipeThreshold', $shortcode_output);
        $this->assertStringContainsString('touchStartX', $shortcode_output);
        $this->assertStringContainsString('touchEndX', $shortcode_output);
    }
    
    /**
     * Test toast notification system
     */
    public function test_toast_notification_system() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for toast notification elements
        $this->assertStringContainsString('showToast', $shortcode_output);
        $this->assertStringContainsString('tc-toast', $shortcode_output);
    }
    
    /**
     * Test mobile search functionality
     */
    public function test_mobile_search_functionality() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users" search="true"]');
        
        // Check for mobile-optimized search
        $this->assertStringContainsString('tc-mobile-search', $shortcode_output);
        $this->assertStringContainsString('search-placeholder', $shortcode_output);
        $this->assertStringContainsString('globalSearch: true', $shortcode_output);
    }
    
    /**
     * Test mobile filter controls
     */
    public function test_mobile_filter_controls() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users" filters="true"]');
        
        // Check for mobile filter interface
        $this->assertStringContainsString('tc-mobile-filters', $shortcode_output);
        $this->assertStringContainsString('filterable: true', $shortcode_output);
    }
    
    /**
     * Test mobile pagination
     */
    public function test_mobile_pagination() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users" per-page="5"]');
        
        // Check for mobile pagination controls
        $this->assertStringContainsString('tc-mobile-pagination', $shortcode_output);
        $this->assertStringContainsString('pageSize: 5', $shortcode_output);
    }
    
    /**
     * Test mobile export functionality
     */
    public function test_mobile_export_functionality() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users" export="true"]');
        
        // Check for mobile export interface
        $this->assertStringContainsString('tc-mobile-export', $shortcode_output);
        $this->assertStringContainsString('exportable: true', $shortcode_output);
    }
    
    /**
     * Test horizontal scrolling on small screens
     */
    public function test_horizontal_scrolling_implementation() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for horizontal scroll CSS
        $this->assertStringContainsString('overflow-x: auto', $shortcode_output);
        $this->assertStringContainsString('-webkit-overflow-scrolling: touch', $shortcode_output);
    }
    
    /**
     * Test error handling on mobile
     */
    public function test_mobile_error_handling() {
        // Test with invalid URL to trigger error
        $shortcode_output = do_shortcode('[tablecrafter url="https://invalid-url-that-will-fail.com/data.json"]');
        
        // Check for mobile-friendly error display
        $this->assertStringContainsString('tc-mobile-error', $shortcode_output);
        $this->assertStringContainsString('Error loading data', $shortcode_output);
    }
    
    /**
     * Test performance on mobile (virtual scrolling activation)
     */
    public function test_mobile_performance_optimization() {
        // Create large dataset scenario
        $large_data_url = 'https://jsonplaceholder.typicode.com/photos'; // 5000 items
        $shortcode_output = do_shortcode("[tablecrafter url=\"{$large_data_url}\"]");
        
        // Check for virtual scrolling activation
        $this->assertStringContainsString('virtualScrolling', $shortcode_output);
        $this->assertStringContainsString('performanceOptimization', $shortcode_output);
    }
    
    /**
     * Test mobile accessibility features
     */
    public function test_mobile_accessibility_features() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for accessibility attributes
        $this->assertStringContainsString('aria-label', $shortcode_output);
        $this->assertStringContainsString('role="grid"', $shortcode_output);
        $this->assertStringContainsString('aria-expanded', $shortcode_output);
        $this->assertStringContainsString('tabindex', $shortcode_output);
    }
    
    /**
     * Test mobile card expand/collapse functionality
     */
    public function test_mobile_card_expand_collapse() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for expand/collapse elements
        $this->assertStringContainsString('tc-card-toggle', $shortcode_output);
        $this->assertStringContainsString('tc-card-expanded', $shortcode_output);
        $this->assertStringContainsString('tc-card-collapsed', $shortcode_output);
    }
    
    /**
     * Test mobile loading states
     */
    public function test_mobile_loading_states() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for mobile loading indicators
        $this->assertStringContainsString('tc-mobile-loading', $shortcode_output);
        $this->assertStringContainsString('tc-loading-spinner', $shortcode_output);
    }
    
    /**
     * Test mobile data refresh capability
     */
    public function test_mobile_data_refresh() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for pull-to-refresh or refresh button
        $this->assertStringContainsString('tc-refresh-button', $shortcode_output);
        $this->assertStringContainsString('refreshData', $shortcode_output);
    }
    
    /**
     * Test mobile viewport meta tag compatibility
     */
    public function test_mobile_viewport_compatibility() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for viewport-aware styling
        $this->assertStringContainsString('viewport-responsive', $shortcode_output);
        $this->assertRegExp('/width:\s*100%/', $shortcode_output);
    }
    
    /**
     * Test mobile gesture feedback system
     */
    public function test_mobile_gesture_feedback() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for haptic feedback and visual feedback elements
        $this->assertStringContainsString('gesture-feedback', $shortcode_output);
        $this->assertStringContainsString('vibrate', $shortcode_output);
    }
    
    /**
     * Test mobile orientation change handling
     */
    public function test_mobile_orientation_handling() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for orientation change listeners
        $this->assertStringContainsString('orientationchange', $shortcode_output);
        $this->assertStringContainsString('handleOrientationChange', $shortcode_output);
    }
    
    /**
     * Test mobile memory management
     */
    public function test_mobile_memory_management() {
        $shortcode_output = do_shortcode('[tablecrafter url="https://jsonplaceholder.typicode.com/users"]');
        
        // Check for memory-efficient mobile features
        $this->assertStringContainsString('memoryOptimization', $shortcode_output);
        $this->assertStringContainsString('lazyLoading', $shortcode_output);
    }
}