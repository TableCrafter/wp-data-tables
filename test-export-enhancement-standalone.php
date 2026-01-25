<?php
/**
 * Standalone Export Enhancement Test
 * Tests the enhanced export functionality without WordPress dependencies
 *
 * @package TableCrafter
 * @since 3.5.3
 */

// Simulate WordPress environment
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/');
}

if (!function_exists('admin_url')) {
    function admin_url($path) {
        return 'https://example.com/wp-admin/' . $path;
    }
}

if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir() {
        return [
            'basedir' => '/tmp/uploads',
            'url' => 'https://example.com/wp-content/uploads'
        ];
    }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($target) {
        if (is_dir($target)) return true;
        return mkdir($target, 0755, true);
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action) {
        return 'test_nonce_' . md5($action);
    }
}

// Mock WordPress functions for testing
if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data) {
        echo json_encode(['success' => false, 'data' => $data]);
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true; // For testing
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $args = 1) {
        // Mock for testing
    }
}

if (!function_exists('wp_next_scheduled')) {
    function wp_next_scheduled($hook) {
        return false;
    }
}

if (!function_exists('wp_schedule_event')) {
    function wp_schedule_event($timestamp, $recurrence, $hook, $args = array()) {
        return true;
    }
}

// Include the enhanced export handler
require_once __DIR__ . '/includes/class-tc-export-handler-enhanced.php';

/**
 * Test the Enhanced Export Handler
 */
class ExportEnhancementTest
{
    private $export_handler;
    private $test_data;
    private $test_columns;
    
    public function __construct()
    {
        $this->export_handler = TC_Export_Handler_Enhanced::get_instance();
        
        // Test data
        $this->test_data = [
            ['name' => 'John Doe', 'age' => 30, 'city' => 'New York'],
            ['name' => 'Jane Smith', 'age' => 25, 'city' => 'Los Angeles'],
            ['name' => 'Bob Johnson', 'age' => 35, 'city' => 'Chicago']
        ];
        
        $this->test_columns = [
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'age', 'label' => 'Age'],
            ['field' => 'city', 'label' => 'City']
        ];
    }
    
    public function run_tests()
    {
        $tests = [
            'test_singleton_pattern',
            'test_excel_export',
            'test_pdf_export',
            'test_csv_export',
            'test_error_handling',
            'test_filename_sanitization',
            'test_large_dataset'
        ];
        
        $passed = 0;
        $total = count($tests);
        
        echo "🧪 Running Enhanced Export Tests\n";
        echo str_repeat('=', 50) . "\n";
        
        foreach ($tests as $test) {
            try {
                $result = $this->$test();
                if ($result) {
                    echo "✅ {$test}: PASSED\n";
                    $passed++;
                } else {
                    echo "❌ {$test}: FAILED\n";
                }
            } catch (Exception $e) {
                echo "❌ {$test}: ERROR - " . $e->getMessage() . "\n";
            }
        }
        
        echo str_repeat('=', 50) . "\n";
        echo "📊 Results: {$passed}/{$total} tests passed\n";
        
        if ($passed === $total) {
            echo "🎉 All tests passed! Export enhancement is working correctly.\n";
            return true;
        } else {
            echo "⚠️ Some tests failed. Please review the implementation.\n";
            return false;
        }
    }
    
    private function test_singleton_pattern()
    {
        $instance1 = TC_Export_Handler_Enhanced::get_instance();
        $instance2 = TC_Export_Handler_Enhanced::get_instance();
        
        return $instance1 === $instance2;
    }
    
    private function test_excel_export()
    {
        $result = $this->export_handler->export_to_excel(
            $this->test_data, 
            $this->test_columns, 
            'test-excel'
        );
        
        return $result['success'] && 
               isset($result['filename']) && 
               str_ends_with($result['filename'], '.xlsx') &&
               $result['mime_type'] === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }
    
    private function test_pdf_export()
    {
        $result = $this->export_handler->export_to_pdf(
            $this->test_data, 
            $this->test_columns, 
            'test-pdf'
        );
        
        return $result['success'] && 
               isset($result['filename']) && 
               str_ends_with($result['filename'], '.pdf') &&
               $result['mime_type'] === 'application/pdf';
    }
    
    private function test_csv_export()
    {
        // Use reflection to access private method
        $reflection = new ReflectionClass($this->export_handler);
        $method = $reflection->getMethod('export_to_csv');
        $method->setAccessible(true);
        
        $result = $method->invoke(
            $this->export_handler,
            $this->test_data, 
            $this->test_columns, 
            'test-csv'
        );
        
        return $result['success'] && 
               isset($result['filename']) && 
               str_ends_with($result['filename'], '.csv') &&
               $result['mime_type'] === 'text/csv';
    }
    
    private function test_error_handling()
    {
        // Test with invalid data
        $result1 = $this->export_handler->export_to_excel([], $this->test_columns, 'test');
        $result2 = $this->export_handler->export_to_excel($this->test_data, [], 'test');
        
        return !$result1['success'] && !$result2['success'] &&
               isset($result1['error']) && isset($result2['error']);
    }
    
    private function test_filename_sanitization()
    {
        $dangerous_filename = '../../../evil/file';
        
        $result = $this->export_handler->export_to_excel(
            $this->test_data, 
            $this->test_columns, 
            $dangerous_filename
        );
        
        // Should succeed but filename should be sanitized
        return $result['success'] && 
               !str_contains($result['filename'], '..') &&
               !str_contains($result['filename'], '/');
    }
    
    private function test_large_dataset()
    {
        // Generate larger dataset
        $large_data = [];
        for ($i = 0; $i < 1000; $i++) {
            $large_data[] = [
                'id' => $i + 1,
                'name' => 'User ' . ($i + 1),
                'email' => 'user' . ($i + 1) . '@example.com'
            ];
        }
        
        $large_columns = [
            ['field' => 'id', 'label' => 'ID'],
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'email', 'label' => 'Email']
        ];
        
        $start_memory = memory_get_usage();
        
        $result = $this->export_handler->export_to_excel(
            $large_data, 
            $large_columns, 
            'large-dataset'
        );
        
        $memory_used = memory_get_usage() - $start_memory;
        
        // Should handle large datasets without excessive memory usage (< 10MB)
        return $result['success'] && $memory_used < (10 * 1024 * 1024);
    }
}

// Run the tests
$tester = new ExportEnhancementTest();
$success = $tester->run_tests();

exit($success ? 0 : 1);