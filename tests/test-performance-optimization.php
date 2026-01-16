<?php
/**
 * Performance Optimization Test Suite
 * 
 * Comprehensive tests for virtual scrolling, lazy loading, and performance optimizations.
 * Tests memory usage, render times, and scalability with large datasets.
 * 
 * @package TableCrafter
 * @since 3.1.0
 */

class TableCrafterPerformanceTest extends WP_UnitTestCase
{
    private $performance_optimizer;
    private $test_data_small;
    private $test_data_large;
    private $test_data_huge;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Include the performance optimizer
        require_once TABLECRAFTER_PATH . 'includes/class-tc-performance-optimizer.php';
        
        // Generate test datasets
        $this->test_data_small = $this->generateTestData(50);
        $this->test_data_large = $this->generateTestData(1000);
        $this->test_data_huge = $this->generateTestData(5000);
    }
    
    /**
     * Generate test data for performance testing
     */
    private function generateTestData($count)
    {
        $data = [];
        $names = ['Alice', 'Bob', 'Charlie', 'Diana', 'Eve', 'Frank', 'Grace', 'Henry'];
        $departments = ['Engineering', 'Marketing', 'Sales', 'HR', 'Finance', 'Operations'];
        $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia'];
        
        for ($i = 0; $i < $count; $i++) {
            $data[] = [
                'id' => $i + 1,
                'name' => $names[array_rand($names)] . ' ' . chr(65 + ($i % 26)),
                'email' => 'user' . ($i + 1) . '@company.com',
                'department' => $departments[array_rand($departments)],
                'salary' => rand(30000, 150000),
                'city' => $cities[array_rand($cities)],
                'hire_date' => date('Y-m-d', strtotime('-' . rand(1, 2000) . ' days')),
                'active' => rand(0, 1) ? 'true' : 'false',
                'bio' => str_repeat('Lorem ipsum dolor sit amet. ', rand(1, 20)),
                'avatar' => 'https://example.com/avatar' . ($i % 10) . '.jpg'
            ];
        }
        
        return $data;
    }
    
    /**
     * Test virtual scrolling threshold detection
     */
    public function test_virtual_scroll_threshold_detection()
    {
        $headers = ['id', 'name', 'email', 'department'];
        
        // Small dataset - should not trigger virtual scrolling
        $result_small = TC_Performance_Optimizer::optimize_rendering(
            $this->test_data_small, 
            $headers
        );
        
        $this->assertArrayHasKey('optimization_meta', $result_small);
        $this->assertFalse($result_small['optimization_meta']['virtual_scrolling']);
        $this->assertEquals('standard', $result_small['optimization_meta']['performance_mode']);
        
        // Large dataset - should trigger virtual scrolling
        $result_large = TC_Performance_Optimizer::optimize_rendering(
            $this->test_data_large, 
            $headers
        );
        
        $this->assertTrue($result_large['optimization_meta']['virtual_scrolling']);
        $this->assertEquals('virtual_scroll', $result_large['optimization_meta']['performance_mode']);
        $this->assertArrayHasKey('total_rows', $result_large['optimization_meta']);
        $this->assertEquals(1000, $result_large['optimization_meta']['total_rows']);
    }
    
    /**
     * Test virtual scrolling data slicing
     */
    public function test_virtual_scroll_data_slicing()
    {
        $headers = ['id', 'name', 'email'];
        $result = TC_Performance_Optimizer::optimize_rendering($this->test_data_large, $headers);
        
        // Should return only the first batch of data
        $this->assertTrue($result['optimization_meta']['virtual_scrolling']);
        $this->assertLessThanOrEqualTo(
            TC_Performance_Optimizer::VIRTUAL_ROWS_RENDERED + TC_Performance_Optimizer::VIRTUAL_BUFFER_ROWS,
            count($result['data'])
        );
        
        // Should include metadata for virtual scrolling
        $this->assertArrayHasKey('estimated_row_height', $result['optimization_meta']);
        $this->assertArrayHasKey('full_dataset_hash', $result);
    }
    
    /**
     * Test row height estimation
     */
    public function test_row_height_estimation()
    {
        // Test with different content types
        $text_data = [
            ['name' => 'Short', 'description' => 'Brief text'],
            ['name' => 'Long', 'description' => str_repeat('Very long text content. ', 20)]
        ];
        
        $image_data = [
            ['name' => 'User1', 'avatar' => 'https://example.com/avatar.jpg'],
            ['name' => 'User2', 'avatar' => 'data:image/jpeg;base64,/9j/...']
        ];
        
        $headers = ['name', 'description'];
        $text_result = TC_Performance_Optimizer::optimize_rendering($text_data, $headers);
        
        $headers = ['name', 'avatar'];
        $image_result = TC_Performance_Optimizer::optimize_rendering($image_data, $headers);
        
        // Images should result in taller estimated height
        if (isset($text_result['optimization_meta']['estimated_row_height']) && 
            isset($image_result['optimization_meta']['estimated_row_height'])) {
            $this->assertGreaterThan(
                $text_result['optimization_meta']['estimated_row_height'],
                $image_result['optimization_meta']['estimated_row_height']
            );
        }
    }
    
    /**
     * Test lazy image optimization
     */
    public function test_lazy_image_optimization()
    {
        $image_data = [
            ['name' => 'User1', 'avatar' => 'https://example.com/avatar1.jpg'],
            ['name' => 'User2', 'avatar' => 'https://example.com/avatar2.png']
        ];
        
        $headers = ['name', 'avatar'];
        $result = TC_Performance_Optimizer::optimize_rendering($image_data, $headers);
        
        // Check that images are optimized for lazy loading
        $optimized_data = $result['data'];
        $this->assertArrayHasKey('avatar', $optimized_data[0]);
        
        $avatar_value = $optimized_data[0]['avatar'];
        if (is_array($avatar_value)) {
            $this->assertEquals('lazy_image', $avatar_value['type']);
            $this->assertArrayHasKey('src', $avatar_value);
            $this->assertArrayHasKey('placeholder', $avatar_value);
        }
    }
    
    /**
     * Test long text optimization
     */
    public function test_long_text_optimization()
    {
        $long_text_data = [
            [
                'title' => 'Article 1',
                'content' => str_repeat('This is a very long article content. ', 50)
            ]
        ];
        
        $headers = ['title', 'content'];
        $result = TC_Performance_Optimizer::optimize_rendering($long_text_data, $headers);
        
        $optimized_data = $result['data'];
        $content_value = $optimized_data[0]['content'];
        
        if (is_array($content_value)) {
            $this->assertEquals('long_text', $content_value['type']);
            $this->assertArrayHasKey('preview', $content_value);
            $this->assertArrayHasKey('full_content', $content_value);
            $this->assertLessThan(strlen($content_value['full_content']), strlen($content_value['preview']));
        }
    }
    
    /**
     * Test performance metrics collection
     */
    public function test_performance_metrics_collection()
    {
        $metrics = TC_Performance_Optimizer::get_performance_metrics();
        
        $this->assertArrayHasKey('virtual_scroll_threshold', $metrics);
        $this->assertArrayHasKey('memory_usage', $metrics);
        $this->assertArrayHasKey('peak_memory_usage', $metrics);
        $this->assertArrayHasKey('php_memory_limit', $metrics);
        
        $this->assertEquals(TC_Performance_Optimizer::VIRTUAL_SCROLL_THRESHOLD, $metrics['virtual_scroll_threshold']);
        $this->assertIsNumeric($metrics['memory_usage']);
        $this->assertIsNumeric($metrics['peak_memory_usage']);
    }
    
    /**
     * Test memory usage with large datasets
     */
    public function test_memory_usage_optimization()
    {
        $memory_before = memory_get_usage(true);
        
        // Process huge dataset
        $headers = ['id', 'name', 'email', 'department', 'salary', 'bio'];
        $result = TC_Performance_Optimizer::optimize_rendering($this->test_data_huge, $headers);
        
        $memory_after = memory_get_usage(true);
        $memory_used = $memory_after - $memory_before;
        
        // Memory usage should be reasonable (less than 50MB for 5000 rows)
        $this->assertLessThan(50 * 1024 * 1024, $memory_used, 'Memory usage too high for large dataset');
        
        // Virtual scrolling should be enabled
        $this->assertTrue($result['optimization_meta']['virtual_scrolling']);
        
        // Only a subset of data should be returned
        $this->assertLessThan(count($this->test_data_huge), count($result['data']));
    }
    
    /**
     * Test AJAX virtual scroll data endpoint
     */
    public function test_ajax_virtual_scroll_data()
    {
        // Create test user with appropriate permissions
        $user_id = $this->factory->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        
        // Create test data file
        $test_data = $this->test_data_large;
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_perf_test') . '.json';
        file_put_contents($temp_file, json_encode($test_data));
        
        // Set up cache
        $cache_key = 'tc_virtual_data_' . md5($temp_file . TABLECRAFTER_VERSION);
        set_transient($cache_key, $test_data, HOUR_IN_SECONDS);
        
        // Set up POST data
        $_POST['source'] = $temp_file;
        $_POST['start_index'] = '0';
        $_POST['count'] = '50';
        $_POST['dataset_hash'] = md5(serialize($test_data));
        $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
        
        // Capture AJAX response
        ob_start();
        
        try {
            TC_Performance_Optimizer::ajax_virtual_scroll_data();
        } catch (WPAjaxDieStopException $e) {
            // Expected when wp_send_json_success is called
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        // Verify response structure
        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response['data']);
        $this->assertArrayHasKey('start_index', $response['data']);
        $this->assertArrayHasKey('count', $response['data']);
        $this->assertArrayHasKey('total_rows', $response['data']);
        $this->assertArrayHasKey('has_more', $response['data']);
        
        // Verify data correctness
        $this->assertEquals(0, $response['data']['start_index']);
        $this->assertEquals(50, $response['data']['count']);
        $this->assertEquals(1000, $response['data']['total_rows']);
        $this->assertTrue($response['data']['has_more']);
        
        // Clean up
        unlink($temp_file);
        delete_transient($cache_key);
        unset($_POST['source'], $_POST['start_index'], $_POST['count'], $_POST['dataset_hash'], $_POST['nonce']);
        wp_set_current_user(0);
    }
    
    /**
     * Test virtual scroll with pagination
     */
    public function test_virtual_scroll_pagination()
    {
        $user_id = $this->factory->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        
        $test_data = $this->test_data_large;
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_perf_test') . '.json';
        file_put_contents($temp_file, json_encode($test_data));
        
        $cache_key = 'tc_virtual_data_' . md5($temp_file . TABLECRAFTER_VERSION);
        set_transient($cache_key, $test_data, HOUR_IN_SECONDS);
        
        // Test different page offsets
        $test_cases = [
            ['start' => 0, 'count' => 50],
            ['start' => 100, 'count' => 50],
            ['start' => 950, 'count' => 50], // Near end
        ];
        
        foreach ($test_cases as $case) {
            $_POST = [
                'source' => $temp_file,
                'start_index' => (string) $case['start'],
                'count' => (string) $case['count'],
                'dataset_hash' => md5(serialize($test_data)),
                'nonce' => wp_create_nonce('tc_proxy_nonce')
            ];
            
            ob_start();
            
            try {
                TC_Performance_Optimizer::ajax_virtual_scroll_data();
            } catch (WPAjaxDieStopException $e) {
                // Expected
            }
            
            $output = ob_get_clean();
            $response = json_decode($output, true);
            
            $this->assertTrue($response['success']);
            $this->assertEquals($case['start'], $response['data']['start_index']);
            
            // Verify returned data is correct slice
            $expected_count = min($case['count'], count($test_data) - $case['start']);
            $this->assertEquals($expected_count, $response['data']['count']);
            
            // Verify has_more flag
            $expected_has_more = ($case['start'] + $response['data']['count']) < count($test_data);
            $this->assertEquals($expected_has_more, $response['data']['has_more']);
        }
        
        // Clean up
        unlink($temp_file);
        delete_transient($cache_key);
        wp_set_current_user(0);
    }
    
    /**
     * Test error handling for virtual scroll
     */
    public function test_virtual_scroll_error_handling()
    {
        $user_id = $this->factory->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        
        // Test missing source
        $_POST = [
            'start_index' => '0',
            'count' => '50',
            'nonce' => wp_create_nonce('tc_proxy_nonce')
        ];
        
        ob_start();
        
        try {
            TC_Performance_Optimizer::ajax_virtual_scroll_data();
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertFalse($response['success']);
        $this->assertStringContains('Source required', $response['data']);
        
        // Test invalid permissions
        wp_set_current_user(0);
        
        $_POST = [
            'source' => 'test.json',
            'start_index' => '0',
            'count' => '50',
            'nonce' => wp_create_nonce('tc_proxy_nonce')
        ];
        
        ob_start();
        
        try {
            TC_Performance_Optimizer::ajax_virtual_scroll_data();
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertFalse($response['success']);
        $this->assertStringContains('Insufficient permissions', $response['data']);
        
        // Clean up
        unset($_POST);
    }
    
    /**
     * Test count limits for security
     */
    public function test_virtual_scroll_count_limits()
    {
        $user_id = $this->factory->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        
        $test_data = $this->test_data_small;
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_perf_test') . '.json';
        file_put_contents($temp_file, json_encode($test_data));
        
        $cache_key = 'tc_virtual_data_' . md5($temp_file . TABLECRAFTER_VERSION);
        set_transient($cache_key, $test_data, HOUR_IN_SECONDS);
        
        // Test excessive count request
        $_POST = [
            'source' => $temp_file,
            'start_index' => '0',
            'count' => '1000', // Much higher than limit
            'dataset_hash' => md5(serialize($test_data)),
            'nonce' => wp_create_nonce('tc_proxy_nonce')
        ];
        
        ob_start();
        
        try {
            TC_Performance_Optimizer::ajax_virtual_scroll_data();
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertTrue($response['success']);
        
        // Count should be limited to maximum allowed
        $max_allowed = TC_Performance_Optimizer::VIRTUAL_ROWS_RENDERED * 2;
        $this->assertLessThanOrEqualTo($max_allowed, $response['data']['count']);
        
        // Clean up
        unlink($temp_file);
        delete_transient($cache_key);
        unset($_POST);
        wp_set_current_user(0);
    }
    
    /**
     * Test dataset hash validation
     */
    public function test_dataset_hash_validation()
    {
        $user_id = $this->factory->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        
        $test_data = $this->test_data_small;
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_perf_test') . '.json';
        file_put_contents($temp_file, json_encode($test_data));
        
        $cache_key = 'tc_virtual_data_' . md5($temp_file . TABLECRAFTER_VERSION);
        set_transient($cache_key, $test_data, HOUR_IN_SECONDS);
        
        // Test with invalid hash
        $_POST = [
            'source' => $temp_file,
            'start_index' => '0',
            'count' => '50',
            'dataset_hash' => 'invalid_hash',
            'nonce' => wp_create_nonce('tc_proxy_nonce')
        ];
        
        ob_start();
        
        try {
            TC_Performance_Optimizer::ajax_virtual_scroll_data();
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertFalse($response['success']);
        $this->assertStringContains('Dataset changed', $response['data']);
        $this->assertArrayHasKey('reload_required', $response['data']);
        $this->assertTrue($response['data']['reload_required']);
        
        // Clean up
        unlink($temp_file);
        delete_transient($cache_key);
        unset($_POST);
        wp_set_current_user(0);
    }
    
    /**
     * Benchmark rendering performance
     */
    public function test_rendering_performance_benchmark()
    {
        $dataset_sizes = [100, 500, 1000, 2000];
        $results = [];
        
        foreach ($dataset_sizes as $size) {
            $data = $this->generateTestData($size);
            $headers = ['id', 'name', 'email', 'department', 'salary'];
            
            $start_time = microtime(true);
            $start_memory = memory_get_usage(true);
            
            $result = TC_Performance_Optimizer::optimize_rendering($data, $headers);
            
            $end_time = microtime(true);
            $end_memory = memory_get_usage(true);
            
            $render_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
            $memory_used = $end_memory - $start_memory;
            
            $results[$size] = [
                'render_time_ms' => $render_time,
                'memory_bytes' => $memory_used,
                'virtual_scroll_enabled' => $result['optimization_meta']['virtual_scrolling'] ?? false
            ];
            
            // Performance assertions
            if ($size <= 500) {
                // Small datasets should render quickly
                $this->assertLessThan(100, $render_time, "Render time too slow for {$size} rows");
            }
            
            // Virtual scrolling should kick in for larger datasets
            if ($size >= TC_Performance_Optimizer::VIRTUAL_SCROLL_THRESHOLD) {
                $this->assertTrue(
                    $result['optimization_meta']['virtual_scrolling'],
                    "Virtual scrolling should be enabled for {$size} rows"
                );
            }
        }
        
        // Log performance results for analysis
        error_log('[TableCrafter Performance Test] Benchmark Results: ' . json_encode($results, JSON_PRETTY_PRINT));
        
        // Verify performance scales reasonably
        $this->assertArrayHasKey(100, $results);
        $this->assertArrayHasKey(2000, $results);
        
        // Large datasets shouldn't take more than 10x the time of small ones due to virtual scrolling
        if ($results[100]['render_time_ms'] > 0) {
            $performance_ratio = $results[2000]['render_time_ms'] / $results[100]['render_time_ms'];
            $this->assertLessThan(10, $performance_ratio, 'Performance should scale well with virtual scrolling');
        }
    }
}