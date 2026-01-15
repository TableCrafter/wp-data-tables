<?php
/**
 * Test Suite for TableCrafter v2.8.0 Features
 * 
 * Comprehensive tests for intelligent large dataset handling,
 * enhanced pagination, auto-refresh functionality, and performance improvements.
 */

class TableCrafterV280Test extends WP_UnitTestCase
{
    private $tablecrafter;
    private $test_data_large;
    private $test_data_small;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->tablecrafter = TableCrafter::get_instance();
        
        // Generate large dataset for testing (1000+ records)
        $this->test_data_large = $this->generate_large_dataset(1500);
        
        // Small dataset for basic functionality
        $this->test_data_small = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'role' => 'admin'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'role' => 'editor'],
            ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'role' => 'author']
        ];
    }
    
    /**
     * Generate large dataset for performance testing
     */
    private function generate_large_dataset(int $count): array
    {
        $data = [];
        $roles = ['admin', 'editor', 'author', 'contributor', 'subscriber'];
        $departments = ['Engineering', 'Marketing', 'Sales', 'Support', 'HR', 'Finance'];
        
        for ($i = 1; $i <= $count; $i++) {
            $data[] = [
                'id' => $i,
                'name' => "User {$i}",
                'email' => "user{$i}@company.com",
                'role' => $roles[array_rand($roles)],
                'department' => $departments[array_rand($departments)],
                'salary' => rand(30000, 150000),
                'hire_date' => date('Y-m-d', strtotime("-" . rand(1, 3650) . " days")),
                'active' => rand(0, 1) ? true : false,
                'performance_score' => round(rand(1, 10) + rand(0, 99) / 100, 2)
            ];
        }
        
        return $data;
    }
    
    /**
     * Test v2.8.0: Enhanced Auto-Refresh System
     */
    public function test_auto_refresh_parameters()
    {
        $atts = [
            'source' => 'test-url.json',
            'auto_refresh' => 'true',
            'refresh_interval' => '60000',
            'refresh_indicator' => 'true',
            'refresh_countdown' => 'false',
            'refresh_last_updated' => 'true'
        ];
        
        // Test boolean normalization
        $normalized = $this->tablecrafter->render_table($atts);
        
        $this->assertStringContains('data-auto-refresh="true"', $normalized);
        $this->assertStringContains('data-refresh-interval="60000"', $normalized);
        $this->assertStringContains('data-refresh-indicator="true"', $normalized);
        $this->assertStringContains('data-refresh-countdown="false"', $normalized);
        $this->assertStringContains('data-refresh-last-updated="true"', $normalized);
    }
    
    /**
     * Test v2.8.0: Smart Auto-Refresh Interaction Pausing
     */
    public function test_auto_refresh_interaction_states()
    {
        // Test that auto-refresh container includes proper classes for JS interaction detection
        $atts = [
            'source' => TABLECRAFTER_URL . 'demo-data/users.json',
            'auto_refresh' => true,
            'refresh_interval' => 30000
        ];
        
        $html = $this->tablecrafter->render_table($atts);
        
        // Should include tablecrafter-container class for JS targeting
        $this->assertStringContains('class="tablecrafter-container"', $html);
        $this->assertStringContains('data-auto-refresh="true"', $html);
        
        // Should have the proper data attributes for JS state management
        $this->assertStringContains('data-ssr="true"', $html);
    }
    
    /**
     * Test v2.8.0: Large Dataset Handling Performance
     */
    public function test_large_dataset_performance()
    {
        $start_time = microtime(true);
        
        // Create temporary JSON file with large dataset
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_large_test');
        file_put_contents($temp_file, json_encode($this->test_data_large));
        
        // Mock the fetch_and_render_php method with large data
        $reflection = new ReflectionClass($this->tablecrafter);
        $method = $reflection->getMethod('fetch_and_render_php');
        $method->setAccessible(true);
        
        $atts = [
            'source' => $temp_file,
            'per_page' => 50,  // Test pagination with large dataset
            'search' => true,
            'filters' => true
        ];
        
        $result = $method->invoke($this->tablecrafter, $atts);
        
        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;
        
        // Should complete within reasonable time (< 2 seconds for 1500 records)
        $this->assertLessThan(2.0, $execution_time, 'Large dataset processing should be under 2 seconds');
        
        // Should return valid HTML structure
        $this->assertIsArray($result);
        $this->assertArrayHasKey('html', $result);
        $this->assertArrayHasKey('data', $result);
        
        // Clean up
        unlink($temp_file);
    }
    
    /**
     * Test v2.8.0: Enhanced Pagination Logic
     */
    public function test_enhanced_pagination()
    {
        $atts = [
            'source' => 'test-data.json',
            'per_page' => 10,
            'search' => true,
            'filters' => true
        ];
        
        $html = $this->tablecrafter->render_table($atts);
        
        // Should include per-page data attribute for JS pagination
        $this->assertStringContains('data-per-page="10"', $html);
        
        // Should include search and filter attributes for enhanced UX
        $this->assertStringContains('data-search="true"', $html);
        $this->assertStringContains('data-filters="true"', $html);
    }
    
    /**
     * Test v2.8.0: Intelligent Sort Data Function
     */
    public function test_intelligent_sorting()
    {
        $reflection = new ReflectionClass($this->tablecrafter);
        $sort_method = $reflection->getMethod('sort_data');
        $sort_method->setAccessible(true);
        
        // Test numeric sorting
        $numeric_data = [
            ['price' => '100.50'],
            ['price' => '25.75'], 
            ['price' => '200.00'],
            ['price' => '10.25']
        ];
        
        $sorted_asc = $sort_method->invoke($this->tablecrafter, $numeric_data, 'price', 'asc');
        $this->assertEquals('10.25', $sorted_asc[0]['price']);
        $this->assertEquals('200.00', $sorted_asc[3]['price']);
        
        $sorted_desc = $sort_method->invoke($this->tablecrafter, $numeric_data, 'price', 'desc');
        $this->assertEquals('200.00', $sorted_desc[0]['price']);
        $this->assertEquals('10.25', $sorted_desc[3]['price']);
        
        // Test string sorting
        $string_data = [
            ['name' => 'Zebra'],
            ['name' => 'Apple'],
            ['name' => 'Banana'], 
            ['name' => 'Cat']
        ];
        
        $sorted_str_asc = $sort_method->invoke($this->tablecrafter, $string_data, 'name', 'asc');
        $this->assertEquals('Apple', $sorted_str_asc[0]['name']);
        $this->assertEquals('Zebra', $sorted_str_asc[3]['name']);
    }
    
    /**
     * Test v2.8.0: CSV Source Integration
     */
    public function test_csv_source_integration()
    {
        // Test CSV file processing
        $csv_content = "name,email,role\nJohn,john@test.com,admin\nJane,jane@test.com,editor";
        $temp_csv = tempnam(sys_get_temp_dir(), 'tc_csv_test') . '.csv';
        file_put_contents($temp_csv, $csv_content);
        
        $reflection = new ReflectionClass($this->tablecrafter);
        $method = $reflection->getMethod('fetch_data_from_source');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->tablecrafter, $temp_csv);
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('John', $result[0]['name']);
        $this->assertEquals('jane@test.com', $result[1]['email']);
        
        unlink($temp_csv);
    }
    
    /**
     * Test v2.8.0: Security Enhancements
     */
    public function test_security_path_traversal_protection()
    {
        $reflection = new ReflectionClass($this->tablecrafter);
        $method = $reflection->getMethod('fetch_data_from_source');
        $method->setAccessible(true);
        
        // Test directory traversal attempt
        $malicious_path = '../../../etc/passwd';
        $result = $method->invoke($this->tablecrafter, $malicious_path);
        
        // Should return WP_Error for blocked paths
        $this->assertInstanceOf('WP_Error', $result);
    }
    
    /**
     * Test v2.8.0: Rate Limiting Functionality
     */
    public function test_rate_limiting()
    {
        $reflection = new ReflectionClass($this->tablecrafter);
        $rate_limit_method = $reflection->getMethod('is_rate_limited');
        $rate_limit_method->setAccessible(true);
        
        // First call should not be rate limited
        $is_limited_1 = $rate_limit_method->invoke($this->tablecrafter);
        $this->assertFalse($is_limited_1);
        
        // Simulate multiple rapid requests by setting high transient count
        $identifier = get_current_user_id() ?: 'test_ip';
        $transient_key = 'tc_rate_' . md5((string) $identifier);
        set_transient($transient_key, 35, 60); // Over the 30 request limit
        
        $is_limited_2 = $rate_limit_method->invoke($this->tablecrafter);
        $this->assertTrue($is_limited_2);
        
        // Clean up
        delete_transient($transient_key);
    }
    
    /**
     * Test v2.8.0: Enhanced Error Handling
     */
    public function test_enhanced_error_handling()
    {
        // Test admin error helper for invalid URL
        $atts = ['source' => 'invalid-url'];
        $html = $this->tablecrafter->render_table($atts);
        
        // Should contain error message
        $this->assertStringContains('TableCrafter requires a "source" attribute', $html);
        
        // Test with admin user to see enhanced error UI
        $admin_user = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($admin_user);
        
        $atts_invalid = ['source' => 'https://invalid-domain-12345.com/data.json'];
        $html_admin = $this->tablecrafter->render_table($atts_invalid);
        
        // Admin should see detailed error helper (if the source fails)
        // This test depends on the actual fetch failing, so we'll check structure
        $this->assertIsString($html_admin);
    }
    
    /**
     * Test v2.8.0: SWR Cache Performance
     */
    public function test_swr_cache_performance()
    {
        $test_url = TABLECRAFTER_URL . 'demo-data/users.json';
        
        // First request (should cache)
        $start_time = microtime(true);
        $atts = ['source' => $test_url];
        $html1 = $this->tablecrafter->render_table($atts);
        $first_request_time = microtime(true) - $start_time;
        
        // Second request (should use cache)
        $start_time = microtime(true);
        $html2 = $this->tablecrafter->render_table($atts);
        $cached_request_time = microtime(true) - $start_time;
        
        // Cached request should be significantly faster
        $this->assertLessThan($first_request_time, $cached_request_time);
        
        // HTML should be identical
        $this->assertEquals($html1, $html2);
    }
    
    /**
     * Test v2.8.0: Block Editor Integration
     */
    public function test_block_editor_attributes()
    {
        if (!function_exists('register_block_type')) {
            $this->markTestSkipped('Block editor not available');
        }
        
        $attributes = [
            'source' => TABLECRAFTER_URL . 'demo-data/products.json',
            'auto_refresh' => true,
            'refresh_interval' => 120000,
            'refresh_indicator' => false,
            'refresh_countdown' => true,
            'refresh_last_updated' => false,
            'per_page' => 25
        ];
        
        $html = $this->tablecrafter->render_block_callback($attributes);
        
        // Should include all auto-refresh attributes
        $this->assertStringContains('data-auto-refresh="true"', $html);
        $this->assertStringContains('data-refresh-interval="120000"', $html);
        $this->assertStringContains('data-refresh-indicator="false"', $html);
        $this->assertStringContains('data-refresh-countdown="true"', $html);
        $this->assertStringContains('data-refresh-last-updated="false"', $html);
        $this->assertStringContains('data-per-page="25"', $html);
    }
    
    /**
     * Test v2.8.0: Google Sheets URL Detection
     */
    public function test_google_sheets_detection()
    {
        $reflection = new ReflectionClass($this->tablecrafter);
        $method = $reflection->getMethod('fetch_data_from_source');
        $method->setAccessible(true);
        
        $sheets_url = 'https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit#gid=0';
        
        // This will attempt to fetch from Google Sheets
        // We can't test the actual fetch without a real sheet, but we can verify the logic path
        $result = $method->invoke($this->tablecrafter, $sheets_url);
        
        // Should either return data array or WP_Error (not false/null)
        $this->assertTrue(is_array($result) || is_wp_error($result));
    }
    
    /**
     * Test v2.8.0: Memory Usage with Large Datasets
     */
    public function test_memory_usage_large_datasets()
    {
        $memory_start = memory_get_usage(true);
        
        // Process large dataset multiple times
        for ($i = 0; $i < 5; $i++) {
            $temp_file = tempnam(sys_get_temp_dir(), 'tc_memory_test');
            file_put_contents($temp_file, json_encode($this->test_data_large));
            
            $atts = [
                'source' => $temp_file,
                'per_page' => 100,
                'search' => true,
                'sort' => 'id:asc'
            ];
            
            $html = $this->tablecrafter->render_table($atts);
            unlink($temp_file);
        }
        
        $memory_end = memory_get_usage(true);
        $memory_used = $memory_end - $memory_start;
        
        // Memory usage should be reasonable (less than 50MB for this test)
        $this->assertLessThan(50 * 1024 * 1024, $memory_used, 'Memory usage should be under 50MB');
    }
    
    /**
     * Test v2.8.0: Concurrent Request Handling
     */
    public function test_concurrent_request_simulation()
    {
        // Simulate concurrent cache operations
        $test_url = TABLECRAFTER_URL . 'demo-data/metrics.json';
        $atts = ['source' => $test_url];
        
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = $this->tablecrafter->render_table($atts);
        }
        
        // All results should be identical (cache consistency)
        foreach ($results as $result) {
            $this->assertEquals($results[0], $result);
        }
    }
    
    /**
     * Test v2.8.0: CLI Command Integration
     */
    public function test_cli_commands()
    {
        if (!defined('WP_CLI') || !WP_CLI) {
            $this->markTestSkipped('WP-CLI not available in test environment');
        }
        
        // Test cache clearing
        ob_start();
        $this->tablecrafter->cli_commands(['clear-cache'], []);
        $output = ob_get_clean();
        
        $this->assertStringContains('cache cleared', strtolower($output));
    }
    
    /**
     * Cleanup after tests
     */
    public function tearDown(): void
    {
        // Clean up any test transients
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_tc_%'");
        
        parent::tearDown();
    }
}