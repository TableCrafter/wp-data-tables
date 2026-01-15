<?php
/**
 * TableCrafter v2.8.0 Test Runner
 * 
 * Quick test runner for validating new features before release.
 * Run this file via WP-CLI or directly in WordPress admin.
 */

// Ensure we're in WordPress environment
if (!defined('ABSPATH')) {
    // Try to find WordPress
    $wp_load_paths = [
        __DIR__ . '/../../../../../../wp-load.php',
        __DIR__ . '/../../../../../wp-load.php',
        __DIR__ . '/../../../../wp-load.php'
    ];
    
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
    
    if (!defined('ABSPATH')) {
        die('WordPress environment not found. Run this from WordPress admin or WP-CLI.');
    }
}

/**
 * TableCrafter Quick Test Runner
 */
class TableCrafterTestRunner
{
    private $results = [];
    private $tablecrafter;
    
    public function __construct()
    {
        if (!class_exists('TableCrafter')) {
            die('TableCrafter plugin not found or not activated.');
        }
        
        $this->tablecrafter = TableCrafter::get_instance();
        echo "TableCrafter v2.8.0 Quick Test Runner\n";
        echo "=====================================\n\n";
    }
    
    /**
     * Run all quick tests
     */
    public function run_all_tests()
    {
        $this->test_version_consistency();
        $this->test_large_dataset_handling();
        $this->test_auto_refresh_attributes();
        $this->test_csv_processing();
        $this->test_security_features();
        $this->test_memory_usage();
        $this->test_sort_performance();
        
        $this->display_summary();
    }
    
    /**
     * Test version consistency
     */
    private function test_version_consistency()
    {
        echo "🔍 Testing Version Consistency...\n";
        
        $plugin_data = get_plugin_data(TABLECRAFTER_PATH . 'tablecrafter.php');
        $plugin_version = $plugin_data['Version'];
        $constant_version = TABLECRAFTER_VERSION;
        
        if ($plugin_version === '2.8.0' && $constant_version === '2.8.0') {
            $this->pass("Version consistency check");
        } else {
            $this->fail("Version mismatch: Plugin={$plugin_version}, Constant={$constant_version}");
        }
    }
    
    /**
     * Test large dataset handling
     */
    private function test_large_dataset_handling()
    {
        echo "📊 Testing Large Dataset Handling...\n";
        
        $large_data = $this->generate_test_data(2000);
        $temp_file = $this->create_temp_file($large_data, 'json');
        
        $start_time = microtime(true);
        $start_memory = memory_get_usage();
        
        $html = $this->tablecrafter->render_table([
            'source' => $temp_file,
            'per_page' => 50
        ]);
        
        $end_time = microtime(true);
        $end_memory = memory_get_usage();
        
        $execution_time = $end_time - $start_time;
        $memory_used = $end_memory - $start_memory;
        
        if ($execution_time < 3.0 && $memory_used < 50 * 1024 * 1024) {
            $this->pass("Large dataset handling (2000 records in " . number_format($execution_time, 2) . "s, " . $this->format_bytes($memory_used) . ")");
        } else {
            $this->fail("Large dataset performance issue: " . number_format($execution_time, 2) . "s, " . $this->format_bytes($memory_used));
        }
        
        unlink($temp_file);
    }
    
    /**
     * Test auto-refresh attributes
     */
    private function test_auto_refresh_attributes()
    {
        echo "🔄 Testing Auto-Refresh Features...\n";
        
        $html = $this->tablecrafter->render_table([
            'source' => TABLECRAFTER_URL . 'demo-data/users.json',
            'auto_refresh' => 'true',
            'refresh_interval' => '60000',
            'refresh_indicator' => 'false',
            'refresh_countdown' => 'true'
        ]);
        
        $checks = [
            'data-auto-refresh="true"',
            'data-refresh-interval="60000"', 
            'data-refresh-indicator="false"',
            'data-refresh-countdown="true"'
        ];
        
        $passed = true;
        foreach ($checks as $check) {
            if (strpos($html, $check) === false) {
                $passed = false;
                break;
            }
        }
        
        if ($passed) {
            $this->pass("Auto-refresh attributes");
        } else {
            $this->fail("Auto-refresh attributes missing");
        }
    }
    
    /**
     * Test CSV processing
     */
    private function test_csv_processing()
    {
        echo "📄 Testing CSV Processing...\n";
        
        $csv_data = "name,email,role\nJohn Doe,john@test.com,admin\nJane Smith,jane@test.com,editor";
        $temp_file = $this->create_temp_file($csv_data, 'csv');
        
        $reflection = new ReflectionClass($this->tablecrafter);
        $method = $reflection->getMethod('fetch_data_from_source');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->tablecrafter, $temp_file);
        
        if (is_array($result) && count($result) === 2 && isset($result[0]['name'])) {
            $this->pass("CSV processing");
        } else {
            $this->fail("CSV processing failed");
        }
        
        unlink($temp_file);
    }
    
    /**
     * Test security features
     */
    private function test_security_features()
    {
        echo "🔒 Testing Security Features...\n";
        
        $reflection = new ReflectionClass($this->tablecrafter);
        $safe_url_method = $reflection->getMethod('is_safe_url');
        $safe_url_method->setAccessible(true);
        
        // Test blocked URLs
        $blocked = $safe_url_method->invoke($this->tablecrafter, 'http://localhost/test');
        $blocked2 = $safe_url_method->invoke($this->tablecrafter, 'http://127.0.0.1/admin');
        
        // Test allowed URLs
        $allowed = $safe_url_method->invoke($this->tablecrafter, 'https://api.github.com/test');
        
        if (!$blocked && !$blocked2 && $allowed) {
            $this->pass("SSRF protection");
        } else {
            $this->fail("SSRF protection issues");
        }
    }
    
    /**
     * Test memory usage stability
     */
    private function test_memory_usage()
    {
        echo "💾 Testing Memory Usage Stability...\n";
        
        $initial_memory = memory_get_usage();
        $test_data = $this->generate_test_data(1000);
        $temp_file = $this->create_temp_file($test_data, 'json');
        
        // Run multiple renders
        for ($i = 0; $i < 10; $i++) {
            $this->tablecrafter->render_table(['source' => $temp_file]);
        }
        
        $final_memory = memory_get_usage();
        $memory_growth = $final_memory - $initial_memory;
        
        if ($memory_growth < 20 * 1024 * 1024) { // Under 20MB growth
            $this->pass("Memory stability (" . $this->format_bytes($memory_growth) . " growth)");
        } else {
            $this->fail("Memory growth too high: " . $this->format_bytes($memory_growth));
        }
        
        unlink($temp_file);
    }
    
    /**
     * Test sorting performance
     */
    private function test_sort_performance()
    {
        echo "⚡ Testing Sort Performance...\n";
        
        $reflection = new ReflectionClass($this->tablecrafter);
        $sort_method = $reflection->getMethod('sort_data');
        $sort_method->setAccessible(true);
        
        $data = $this->generate_test_data(5000);
        
        $start_time = microtime(true);
        $sorted = $sort_method->invoke($this->tablecrafter, $data, 'id', 'desc');
        $sort_time = microtime(true) - $start_time;
        
        if ($sort_time < 1.0 && $sorted[0]['id'] > $sorted[1]['id']) {
            $this->pass("Sort performance (5000 records in " . number_format($sort_time, 3) . "s)");
        } else {
            $this->fail("Sort performance issue: " . number_format($sort_time, 3) . "s");
        }
    }
    
    /**
     * Generate test data
     */
    private function generate_test_data(int $count): array
    {
        $data = [];
        for ($i = 1; $i <= $count; $i++) {
            $data[] = [
                'id' => $i,
                'name' => "User {$i}",
                'email' => "user{$i}@test.com",
                'score' => rand(1, 100),
                'active' => $i % 2 === 0
            ];
        }
        return $data;
    }
    
    /**
     * Create temporary file
     */
    private function create_temp_file($data, string $type): string
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_test') . ".{$type}";
        
        if ($type === 'json') {
            file_put_contents($temp_file, json_encode($data));
        } else {
            file_put_contents($temp_file, $data);
        }
        
        return $temp_file;
    }
    
    /**
     * Format bytes for display
     */
    private function format_bytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / (1024 * 1024), 2) . 'MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . 'KB';
        }
        return $bytes . 'B';
    }
    
    /**
     * Record test pass
     */
    private function pass(string $test): void
    {
        $this->results[] = ['test' => $test, 'status' => 'PASS'];
        echo "  ✅ {$test}\n";
    }
    
    /**
     * Record test failure
     */
    private function fail(string $test): void
    {
        $this->results[] = ['test' => $test, 'status' => 'FAIL'];
        echo "  ❌ {$test}\n";
    }
    
    /**
     * Display test summary
     */
    private function display_summary()
    {
        echo "\n📋 Test Summary\n";
        echo "===============\n";
        
        $total = count($this->results);
        $passed = count(array_filter($this->results, fn($r) => $r['status'] === 'PASS'));
        $failed = $total - $passed;
        
        echo "Total Tests: {$total}\n";
        echo "Passed: {$passed}\n";
        echo "Failed: {$failed}\n";
        
        if ($failed > 0) {
            echo "\n❌ Failed Tests:\n";
            foreach ($this->results as $result) {
                if ($result['status'] === 'FAIL') {
                    echo "  - {$result['test']}\n";
                }
            }
            echo "\n🚨 Release NOT recommended until failures are fixed.\n";
        } else {
            echo "\n🎉 All tests passed! TableCrafter v2.8.0 is ready for release.\n";
        }
    }
}

// Run tests if called directly
if (isset($_GET['run_tests']) || (defined('WP_CLI') && WP_CLI)) {
    $runner = new TableCrafterTestRunner();
    $runner->run_all_tests();
} else {
    echo '<h1>TableCrafter v2.8.0 Test Runner</h1>';
    echo '<p><a href="?run_tests=1" style="background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Run Tests</a></p>';
    echo '<p>Or run via WP-CLI: <code>wp eval-file ' . __FILE__ . '</code></p>';
}
?>