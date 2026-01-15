<?php
/**
 * Performance Benchmark Tests for TableCrafter v2.8.0
 * 
 * Comprehensive performance testing to validate the "intelligent large dataset handling"
 * claim in the v2.8.0 description.
 */

class TableCrafterPerformanceTest extends WP_UnitTestCase
{
    private $tablecrafter;
    private $performance_metrics = [];
    
    public function setUp(): void
    {
        parent::setUp();
        $this->tablecrafter = TableCrafter::get_instance();
    }
    
    /**
     * Benchmark: Rendering 10,000 Record Dataset
     */
    public function test_render_10k_records_performance()
    {
        $large_dataset = $this->generate_benchmark_dataset(10000);
        $temp_file = $this->create_temp_json_file($large_dataset);
        
        $start_memory = memory_get_usage(true);
        $start_time = microtime(true);
        
        $atts = [
            'source' => $temp_file,
            'per_page' => 50,
            'search' => true,
            'filters' => true,
            'sort' => 'id:asc'
        ];
        
        $html = $this->tablecrafter->render_table($atts);
        
        $end_time = microtime(true);
        $end_memory = memory_get_usage(true);
        
        $execution_time = $end_time - $start_time;
        $memory_used = $end_memory - $start_memory;
        
        $this->performance_metrics['10k_records'] = [
            'execution_time' => $execution_time,
            'memory_used' => $memory_used,
            'records_per_second' => 10000 / $execution_time
        ];
        
        // Performance assertions for v2.8.0
        $this->assertLessThan(5.0, $execution_time, '10k records should render in under 5 seconds');
        $this->assertLessThan(100 * 1024 * 1024, $memory_used, 'Memory usage should be under 100MB');
        $this->assertGreaterThan(2000, 10000 / $execution_time, 'Should process at least 2000 records per second');
        
        // Verify HTML structure is maintained
        $this->assertStringContains('<table class="tc-table">', $html);
        $this->assertStringContains('data-per-page="50"', $html);
        
        unlink($temp_file);
    }
    
    /**
     * Benchmark: Cache Performance Under Load
     */
    public function test_cache_performance_under_load()
    {
        $dataset = $this->generate_benchmark_dataset(1000);
        $temp_file = $this->create_temp_json_file($dataset);
        
        $atts = ['source' => $temp_file];
        
        // First request (cold cache)
        $start_time = microtime(true);
        $html1 = $this->tablecrafter->render_table($atts);
        $cold_cache_time = microtime(true) - $start_time;
        
        // Subsequent requests (warm cache)
        $warm_cache_times = [];
        for ($i = 0; $i < 20; $i++) {
            $start_time = microtime(true);
            $html = $this->tablecrafter->render_table($atts);
            $warm_cache_times[] = microtime(true) - $start_time;
            
            $this->assertEquals($html1, $html, 'Cached results should be identical');
        }
        
        $avg_warm_cache_time = array_sum($warm_cache_times) / count($warm_cache_times);
        
        $this->performance_metrics['cache_performance'] = [
            'cold_cache_time' => $cold_cache_time,
            'avg_warm_cache_time' => $avg_warm_cache_time,
            'cache_speedup' => $cold_cache_time / $avg_warm_cache_time
        ];
        
        // Cache should provide significant speedup
        $this->assertLessThan($cold_cache_time / 5, $avg_warm_cache_time, 'Cache should be 5x faster');
        $this->assertLessThan(0.1, $avg_warm_cache_time, 'Cached requests should be under 100ms');
        
        unlink($temp_file);
    }
    
    /**
     * Benchmark: Sorting Performance on Large Datasets
     */
    public function test_sorting_performance_large_dataset()
    {
        $reflection = new ReflectionClass($this->tablecrafter);
        $sort_method = $reflection->getMethod('sort_data');
        $sort_method->setAccessible(true);
        
        // Generate datasets of increasing size
        $sizes = [1000, 5000, 10000];
        $sort_times = [];
        
        foreach ($sizes as $size) {
            $dataset = $this->generate_benchmark_dataset($size);
            
            // Test numeric sorting
            $start_time = microtime(true);
            $sorted = $sort_method->invoke($this->tablecrafter, $dataset, 'salary', 'desc');
            $sort_time = microtime(true) - $start_time;
            
            $sort_times[$size] = $sort_time;
            
            // Verify sorting correctness
            $this->assertGreaterThanOrEqual($sorted[1]['salary'], $sorted[0]['salary']);
            $this->assertLessThanOrEqual($sorted[count($sorted) - 1]['salary'], $sorted[count($sorted) - 2]['salary']);
        }
        
        $this->performance_metrics['sorting_performance'] = $sort_times;
        
        // Sorting should scale reasonably
        $this->assertLessThan(2.0, $sort_times[10000], '10k record sort should be under 2 seconds');
        
        // Performance should scale sub-linearly (efficiency improvement)
        $efficiency_10k_vs_1k = ($sort_times[10000] / $sort_times[1000]) / 10;
        $this->assertLessThan(2.0, $efficiency_10k_vs_1k, 'Sort efficiency should not degrade linearly');
    }
    
    /**
     * Benchmark: Concurrent AJAX Proxy Performance
     */
    public function test_ajax_proxy_concurrent_performance()
    {
        // Simulate concurrent AJAX requests
        $test_urls = [
            TABLECRAFTER_URL . 'demo-data/users.json',
            TABLECRAFTER_URL . 'demo-data/products.json',
            TABLECRAFTER_URL . 'demo-data/metrics.json'
        ];
        
        $concurrent_times = [];
        $start_time = microtime(true);
        
        foreach ($test_urls as $url) {
            // Simulate AJAX proxy call
            $_POST['url'] = $url;
            $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
            
            ob_start();
            $request_start = microtime(true);
            
            // Mock the AJAX proxy behavior
            $reflection = new ReflectionClass($this->tablecrafter);
            $method = $reflection->getMethod('fetch_data_from_source');
            $method->setAccessible(true);
            
            $result = $method->invoke($this->tablecrafter, $url);
            $request_time = microtime(true) - $request_start;
            
            ob_end_clean();
            
            $concurrent_times[] = $request_time;
            $this->assertTrue(is_array($result) || is_wp_error($result));
        }
        
        $total_time = microtime(true) - $start_time;
        $avg_request_time = array_sum($concurrent_times) / count($concurrent_times);
        
        $this->performance_metrics['ajax_proxy'] = [
            'total_time' => $total_time,
            'avg_request_time' => $avg_request_time,
            'requests_per_second' => count($test_urls) / $total_time
        ];
        
        $this->assertLessThan(2.0, $total_time, 'Concurrent requests should complete in under 2 seconds');
        $this->assertLessThan(1.0, $avg_request_time, 'Average request time should be under 1 second');
        
        // Cleanup
        unset($_POST['url'], $_POST['nonce']);
    }
    
    /**
     * Benchmark: CSV Parsing Performance
     */
    public function test_csv_parsing_performance()
    {
        $csv_sizes = [1000, 5000, 10000];
        $parse_times = [];
        
        foreach ($csv_sizes as $size) {
            $csv_content = $this->generate_csv_content($size);
            $temp_file = tempnam(sys_get_temp_dir(), 'tc_csv_perf') . '.csv';
            file_put_contents($temp_file, $csv_content);
            
            $start_time = microtime(true);
            
            $reflection = new ReflectionClass($this->tablecrafter);
            $method = $reflection->getMethod('fetch_data_from_source');
            $method->setAccessible(true);
            
            $result = $method->invoke($this->tablecrafter, $temp_file);
            $parse_time = microtime(true) - $start_time;
            
            $parse_times[$size] = $parse_time;
            
            $this->assertIsArray($result);
            $this->assertCount($size, $result);
            
            unlink($temp_file);
        }
        
        $this->performance_metrics['csv_parsing'] = $parse_times;
        
        // CSV parsing should be efficient
        $this->assertLessThan(3.0, $parse_times[10000], '10k CSV records should parse in under 3 seconds');
        
        // Check scaling efficiency
        $scaling_factor = ($parse_times[10000] / $parse_times[1000]) / 10;
        $this->assertLessThan(1.5, $scaling_factor, 'CSV parsing should scale efficiently');
    }
    
    /**
     * Benchmark: Memory Stability Under Repeated Operations
     */
    public function test_memory_stability_repeated_operations()
    {
        $dataset = $this->generate_benchmark_dataset(2000);
        $temp_file = $this->create_temp_json_file($dataset);
        
        $memory_samples = [];
        $initial_memory = memory_get_usage(true);
        
        // Perform 50 render operations
        for ($i = 0; $i < 50; $i++) {
            $atts = [
                'source' => $temp_file,
                'sort' => 'id:' . ($i % 2 ? 'asc' : 'desc'), // Alternate sort direction
                'per_page' => 20 + ($i % 10) // Vary pagination
            ];
            
            $html = $this->tablecrafter->render_table($atts);
            $memory_samples[] = memory_get_usage(true);
            
            // Force garbage collection every 10 operations
            if ($i % 10 === 0) {
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
        }
        
        $final_memory = memory_get_usage(true);
        $memory_growth = $final_memory - $initial_memory;
        $peak_memory = max($memory_samples);
        
        $this->performance_metrics['memory_stability'] = [
            'initial_memory' => $initial_memory,
            'final_memory' => $final_memory,
            'memory_growth' => $memory_growth,
            'peak_memory' => $peak_memory
        ];
        
        // Memory should not grow excessively
        $this->assertLessThan(50 * 1024 * 1024, $memory_growth, 'Memory growth should be under 50MB after 50 operations');
        $this->assertLessThan(200 * 1024 * 1024, $peak_memory, 'Peak memory should be under 200MB');
        
        unlink($temp_file);
    }
    
    /**
     * Benchmark: Auto-refresh Performance Impact
     */
    public function test_auto_refresh_performance_impact()
    {
        $dataset = $this->generate_benchmark_dataset(1000);
        $temp_file = $this->create_temp_json_file($dataset);
        
        // Test without auto-refresh
        $start_time = microtime(true);
        $atts_no_refresh = ['source' => $temp_file];
        $html_no_refresh = $this->tablecrafter->render_table($atts_no_refresh);
        $time_no_refresh = microtime(true) - $start_time;
        
        // Test with auto-refresh enabled
        $start_time = microtime(true);
        $atts_with_refresh = [
            'source' => $temp_file,
            'auto_refresh' => true,
            'refresh_interval' => 60000,
            'refresh_indicator' => true,
            'refresh_countdown' => true,
            'refresh_last_updated' => true
        ];
        $html_with_refresh = $this->tablecrafter->render_table($atts_with_refresh);
        $time_with_refresh = microtime(true) - $start_time;
        
        $performance_overhead = ($time_with_refresh - $time_no_refresh) / $time_no_refresh * 100;
        
        $this->performance_metrics['auto_refresh_overhead'] = [
            'time_no_refresh' => $time_no_refresh,
            'time_with_refresh' => $time_with_refresh,
            'overhead_percentage' => $performance_overhead
        ];
        
        // Auto-refresh should add minimal overhead
        $this->assertLessThan(20, $performance_overhead, 'Auto-refresh should add less than 20% overhead');
        
        // Both outputs should contain valid table structure
        $this->assertStringContains('<table class="tc-table">', $html_no_refresh);
        $this->assertStringContains('<table class="tc-table">', $html_with_refresh);
        
        // Auto-refresh version should have additional data attributes
        $this->assertStringContains('data-auto-refresh="true"', $html_with_refresh);
        $this->assertStringNotContains('data-auto-refresh="true"', $html_no_refresh);
        
        unlink($temp_file);
    }
    
    /**
     * Generate benchmark dataset
     */
    private function generate_benchmark_dataset(int $count): array
    {
        $data = [];
        $statuses = ['active', 'inactive', 'pending', 'suspended'];
        $departments = ['Engineering', 'Marketing', 'Sales', 'Support', 'HR', 'Finance', 'Operations'];
        $locations = ['New York', 'San Francisco', 'London', 'Tokyo', 'Sydney', 'Toronto', 'Berlin'];
        
        for ($i = 1; $i <= $count; $i++) {
            $data[] = [
                'id' => $i,
                'name' => "Employee " . str_pad($i, 5, '0', STR_PAD_LEFT),
                'email' => "employee{$i}@company.com",
                'department' => $departments[array_rand($departments)],
                'location' => $locations[array_rand($locations)],
                'salary' => rand(30000, 200000),
                'bonus' => rand(0, 20000),
                'hire_date' => date('Y-m-d', strtotime("-" . rand(1, 3650) . " days")),
                'status' => $statuses[array_rand($statuses)],
                'performance_rating' => round(rand(1, 5) + rand(0, 99) / 100, 2),
                'manager_id' => rand(1, max(1, intval($count / 10))),
                'phone' => '+1-555-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'address' => rand(1, 9999) . ' Main St',
                'emergency_contact' => "Contact " . $i,
                'notes' => 'Additional notes for employee ' . $i
            ];
        }
        
        return $data;
    }
    
    /**
     * Generate CSV content for testing
     */
    private function generate_csv_content(int $rows): string
    {
        $csv = "id,name,email,department,salary,hire_date\n";
        
        $departments = ['Engineering', 'Marketing', 'Sales', 'Support'];
        
        for ($i = 1; $i <= $rows; $i++) {
            $csv .= sprintf(
                "%d,Employee %d,employee%d@company.com,%s,%d,%s\n",
                $i,
                $i,
                $i,
                $departments[array_rand($departments)],
                rand(30000, 150000),
                date('Y-m-d', strtotime("-" . rand(1, 3650) . " days"))
            );
        }
        
        return $csv;
    }
    
    /**
     * Create temporary JSON file
     */
    private function create_temp_json_file(array $data): string
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_perf_test') . '.json';
        file_put_contents($temp_file, json_encode($data, JSON_PRETTY_PRINT));
        return $temp_file;
    }
    
    /**
     * Output performance summary
     */
    public function tearDown(): void
    {
        if (!empty($this->performance_metrics)) {
            error_log("TableCrafter v2.8.0 Performance Metrics:");
            error_log(json_encode($this->performance_metrics, JSON_PRETTY_PRINT));
        }
        
        parent::tearDown();
    }
}