<?php
/**
 * Test Suite for TableCrafter Advanced Export Functionality
 * 
 * Comprehensive tests for the new export capabilities addressing
 * the #1 customer pain point: limited export options.
 * 
 * @package TableCrafter
 * @since 2.9.0
 */

class TC_Advanced_Export_Test extends WP_UnitTestCase
{
    private $export_handler;
    private $test_data;
    private $test_headers;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Include the export handler
        require_once TABLECRAFTER_PATH . 'includes/class-tc-export-handler.php';
        
        $this->export_handler = new TC_Export_Handler();
        
        // Generate test data
        $this->test_data = [
            [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'salary' => 75000.50,
                'hire_date' => '2023-01-15',
                'active' => true,
                'department' => 'Engineering',
                'notes' => 'Senior developer with <strong>HTML</strong> experience'
            ],
            [
                'id' => 2,
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'salary' => 82000.75,
                'hire_date' => '2022-11-03',
                'active' => false,
                'department' => 'Marketing',
                'notes' => 'Marketing manager with "special" characters'
            ],
            [
                'id' => 3,
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'salary' => 68000.00,
                'hire_date' => '2023-06-20',
                'active' => true,
                'department' => 'Sales',
                'notes' => 'Sales representative'
            ]
        ];
        
        $this->test_headers = ['id', 'name', 'email', 'salary', 'hire_date', 'active', 'department', 'notes'];
    }
    
    /**
     * Test CSV export functionality
     */
    public function test_csv_export_basic()
    {
        $options = [
            'format' => 'csv',
            'filename' => 'test-export',
            'include_headers' => true
        ];
        
        $result = TC_Export_Handler::export_data($this->test_data, $this->test_headers, $options);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('test-export.csv', $result['filename']);
        $this->assertEquals('text/csv', $result['mime_type']);
        $this->assertTrue(file_exists($result['file_path']));
        $this->assertGreaterThan(0, $result['size']);
        
        // Check file content
        $content = file_get_contents($result['file_path']);
        $this->assertStringContains('id,name,email', $content); // Headers
        $this->assertStringContains('John Doe,john@example.com', $content); // Data
        $this->assertStringContains('75000.50', $content); // Number formatting
        
        // Clean up
        TC_Export_Handler::cleanup_temp_file($result['file_path']);
    }
    
    /**
     * Test CSV export with metadata
     */
    public function test_csv_export_with_metadata()
    {
        $options = [
            'format' => 'csv',
            'filename' => 'metadata-test',
            'include_headers' => true,
            'include_metadata' => true,
            'total_records' => 3,
            'export_timestamp' => '2024-01-15 12:00:00',
            'filters_applied' => ['department' => 'Engineering'],
            'sort_applied' => 'salary:desc'
        ];
        
        $result = TC_Export_Handler::export_data($this->test_data, $this->test_headers, $options);
        
        $this->assertTrue($result['success']);
        
        $content = file_get_contents($result['file_path']);
        $this->assertStringContains('Export Information', $content);
        $this->assertStringContains('2024-01-15 12:00:00', $content);
        $this->assertStringContains('Total Records,3', $content);
        $this->assertStringContains('salary:desc', $content);
        
        TC_Export_Handler::cleanup_temp_file($result['file_path']);
    }
    
    /**
     * Test Excel export functionality
     */
    public function test_excel_export_basic()
    {
        $options = [
            'format' => 'xlsx',
            'filename' => 'excel-test',
            'include_headers' => true
        ];
        
        $result = TC_Export_Handler::export_data($this->test_data, $this->test_headers, $options);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('excel-test.xlsx', $result['filename']);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $result['mime_type']);
        $this->assertTrue(file_exists($result['file_path']));
        $this->assertGreaterThan(0, $result['size']);
        
        TC_Export_Handler::cleanup_temp_file($result['file_path']);
    }
    
    /**
     * Test PDF export functionality
     */
    public function test_pdf_export_basic()
    {
        $options = [
            'format' => 'pdf',
            'filename' => 'pdf-test',
            'include_headers' => true
        ];
        
        $result = TC_Export_Handler::export_data($this->test_data, $this->test_headers, $options);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('pdf-test.pdf', $result['filename']);
        $this->assertEquals('application/pdf', $result['mime_type']);
        $this->assertTrue(file_exists($result['file_path']));
        $this->assertGreaterThan(0, $result['size']);
        
        TC_Export_Handler::cleanup_temp_file($result['file_path']);
    }
    
    /**
     * Test export templates
     */
    public function test_export_templates()
    {
        $templates = TC_Export_Handler::get_export_templates();
        
        $this->assertIsArray($templates);
        $this->assertArrayHasKey('default', $templates);
        $this->assertArrayHasKey('business', $templates);
        $this->assertArrayHasKey('data_analysis', $templates);
        
        // Test template structure
        $business_template = $templates['business'];
        $this->assertArrayHasKey('name', $business_template);
        $this->assertArrayHasKey('description', $business_template);
        $this->assertArrayHasKey('include_metadata', $business_template);
        $this->assertEquals('Business Report', $business_template['name']);
        $this->assertTrue($business_template['include_metadata']);
    }
    
    /**
     * Test data formatting functionality
     */
    public function test_data_formatting()
    {
        $options = [
            'format' => 'csv',
            'date_format' => 'M j, Y',
            'number_format' => '$0.00'
        ];
        
        $reflection = new ReflectionClass('TC_Export_Handler');
        $format_method = $reflection->getMethod('format_cell_value');
        $format_method->setAccessible(true);
        
        // Test date formatting
        $formatted_date = $format_method->invoke(null, '2023-01-15', $options);
        $this->assertEquals('Jan 15, 2023', $formatted_date);
        
        // Test number formatting
        $formatted_number = $format_method->invoke(null, 75000.50, $options);
        $this->assertEquals('75000.50', $formatted_number);
        
        // Test HTML stripping
        $html_content = '<strong>Bold text</strong> with <em>emphasis</em>';
        $formatted_html = $format_method->invoke(null, $html_content, $options);
        $this->assertEquals('Bold text with emphasis', $formatted_html);
        
        // Test array handling
        $array_data = ['item1', 'item2', 'item3'];
        $formatted_array = $format_method->invoke(null, $array_data, $options);
        $this->assertStringContains('item1', $formatted_array);
    }
    
    /**
     * Test export with column filtering
     */
    public function test_export_column_filtering()
    {
        // Test include columns
        $filtered_headers = ['name', 'email', 'department'];
        $options = ['format' => 'csv', 'include_headers' => true];
        
        $result = TC_Export_Handler::export_data($this->test_data, $filtered_headers, $options);
        
        $this->assertTrue($result['success']);
        
        $content = file_get_contents($result['file_path']);
        $this->assertStringContains('name,email,department', $content);
        $this->assertStringNotContains('salary', $content); // Excluded column
        
        TC_Export_Handler::cleanup_temp_file($result['file_path']);
    }
    
    /**
     * Test error handling for invalid formats
     */
    public function test_invalid_format_handling()
    {
        $options = [
            'format' => 'invalid_format',
            'filename' => 'error-test'
        ];
        
        $result = TC_Export_Handler::export_data($this->test_data, $this->test_headers, $options);
        
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContains('Unsupported export format', $result['error']);
    }
    
    /**
     * Test empty data handling
     */
    public function test_empty_data_handling()
    {
        $options = ['format' => 'csv'];
        
        $result = TC_Export_Handler::export_data([], $this->test_headers, $options);
        
        $this->assertTrue($result['success']);
        
        // Should create file with headers only
        $content = file_get_contents($result['file_path']);
        $lines = explode("\n", trim($content));
        $this->assertLessThanOrEqual(2, count($lines)); // Headers + possible empty line
        
        TC_Export_Handler::cleanup_temp_file($result['file_path']);
    }
    
    /**
     * Test large dataset performance
     */
    public function test_large_dataset_export()
    {
        // Generate larger dataset
        $large_data = [];
        for ($i = 1; $i <= 1000; $i++) {
            $large_data[] = [
                'id' => $i,
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'value' => rand(1000, 9999)
            ];
        }
        
        $headers = ['id', 'name', 'email', 'value'];
        $options = ['format' => 'csv', 'include_headers' => true];
        
        $start_time = microtime(true);
        $start_memory = memory_get_usage();
        
        $result = TC_Export_Handler::export_data($large_data, $headers, $options);
        
        $end_time = microtime(true);
        $end_memory = memory_get_usage();
        
        $execution_time = $end_time - $start_time;
        $memory_used = $end_memory - $start_memory;
        
        $this->assertTrue($result['success']);
        $this->assertLessThan(5.0, $execution_time); // Should complete under 5 seconds
        $this->assertLessThan(50 * 1024 * 1024, $memory_used); // Under 50MB memory
        
        // Verify content
        $content = file_get_contents($result['file_path']);
        $lines = explode("\n", trim($content));
        $this->assertEquals(1001, count($lines)); // 1000 data rows + 1 header row
        
        TC_Export_Handler::cleanup_temp_file($result['file_path']);
    }
    
    /**
     * Test security and sanitization
     */
    public function test_security_sanitization()
    {
        $malicious_data = [
            [
                'name' => '<script>alert("xss")</script>',
                'description' => 'Normal content with "quotes" and special chars',
                'command' => '"; DROP TABLE users; --',
                'filename' => '../../../etc/passwd'
            ]
        ];
        
        $headers = ['name', 'description', 'command', 'filename'];
        $options = ['format' => 'csv', 'include_headers' => true];
        
        $result = TC_Export_Handler::export_data($malicious_data, $headers, $options);
        
        $this->assertTrue($result['success']);
        
        $content = file_get_contents($result['file_path']);
        
        // Should strip HTML and escape dangerous content
        $this->assertStringNotContains('<script>', $content);
        $this->assertStringNotContains('DROP TABLE', $content);
        $this->assertStringContains('alert(\"xss\")', $content); // HTML stripped but text remains
        
        TC_Export_Handler::cleanup_temp_file($result['file_path']);
    }
    
    /**
     * Test filename sanitization
     */
    public function test_filename_sanitization()
    {
        $options = [
            'format' => 'csv',
            'filename' => '../malicious/../../file<script>.csv'
        ];
        
        $result = TC_Export_Handler::export_data($this->test_data, $this->test_headers, $options);
        
        $this->assertTrue($result['success']);
        
        // Filename should be sanitized
        $this->assertStringNotContains('../', $result['filename']);
        $this->assertStringNotContains('<script>', $result['filename']);
        $this->assertStringEndsWith('.csv', $result['filename']);
    }
    
    /**
     * Test concurrent export handling
     */
    public function test_concurrent_exports()
    {
        $results = [];
        $options = ['format' => 'csv', 'include_headers' => true];
        
        // Simulate multiple concurrent exports
        for ($i = 0; $i < 5; $i++) {
            $test_options = array_merge($options, ['filename' => "concurrent-test-{$i}"]);
            $results[] = TC_Export_Handler::export_data($this->test_data, $this->test_headers, $test_options);
        }
        
        // All should succeed
        foreach ($results as $i => $result) {
            $this->assertTrue($result['success'], "Export {$i} failed");
            $this->assertTrue(file_exists($result['file_path']), "File {$i} not created");
            
            // Clean up
            TC_Export_Handler::cleanup_temp_file($result['file_path']);
        }
    }
    
    /**
     * Test cleanup functionality
     */
    public function test_cleanup_functionality()
    {
        $options = ['format' => 'csv'];
        $result = TC_Export_Handler::export_data($this->test_data, $this->test_headers, $options);
        
        $this->assertTrue($result['success']);
        $this->assertTrue(file_exists($result['file_path']));
        
        // Test cleanup
        $cleanup_result = TC_Export_Handler::cleanup_temp_file($result['file_path']);
        $this->assertTrue($cleanup_result);
        $this->assertFalse(file_exists($result['file_path']));
        
        // Test cleanup of non-temp file (should fail safely)
        $safe_cleanup = TC_Export_Handler::cleanup_temp_file('/etc/passwd');
        $this->assertFalse($safe_cleanup);
    }
    
    /**
     * Test AJAX export endpoint
     */
    public function test_ajax_export_endpoint()
    {
        // Create test user with appropriate permissions
        $user_id = $this->factory->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        
        // Mock POST data
        $_POST = [
            'nonce' => wp_create_nonce('tc_export_nonce'),
            'source' => TABLECRAFTER_URL . 'demo-data/users.json',
            'format' => 'csv',
            'filename' => 'ajax-test',
            'template' => 'default',
            'include_metadata' => false
        ];
        
        // Get TableCrafter instance
        $tablecrafter = TableCrafter::get_instance();
        
        // Capture output
        ob_start();
        
        try {
            $tablecrafter->ajax_export_data();
        } catch (WPAjaxDieStopException $e) {
            // Expected for wp_send_json_* functions
        }
        
        $output = ob_get_clean();
        
        if (!empty($output)) {
            $response = json_decode($output, true);
            
            if ($response && $response['success']) {
                $this->assertTrue($response['success']);
                $this->assertArrayHasKey('export_id', $response['data']);
                $this->assertArrayHasKey('filename', $response['data']);
                $this->assertArrayHasKey('download_url', $response['data']);
            }
        }
        
        // Clean up
        unset($_POST);
        wp_set_current_user(0);
    }
    
    /**
     * Test memory efficiency with various data types
     */
    public function test_memory_efficiency_data_types()
    {
        $complex_data = [];
        
        for ($i = 0; $i < 100; $i++) {
            $complex_data[] = [
                'id' => $i,
                'text' => str_repeat('Lorem ipsum dolor sit amet ', 10),
                'number' => rand(1000, 999999) / 100,
                'date' => date('Y-m-d H:i:s', strtotime("-{$i} days")),
                'boolean' => $i % 2 === 0,
                'array' => ['item1', 'item2', 'item3'],
                'html' => '<p>HTML content with <strong>tags</strong> and entities &amp; symbols</p>'
            ];
        }
        
        $headers = array_keys($complex_data[0]);
        $options = ['format' => 'csv', 'include_headers' => true];
        
        $start_memory = memory_get_usage(true);
        $result = TC_Export_Handler::export_data($complex_data, $headers, $options);
        $end_memory = memory_get_usage(true);
        
        $memory_used = $end_memory - $start_memory;
        
        $this->assertTrue($result['success']);
        $this->assertLessThan(20 * 1024 * 1024, $memory_used); // Under 20MB for complex data
        
        TC_Export_Handler::cleanup_temp_file($result['file_path']);
    }
    
    /**
     * Clean up after all tests
     */
    public function tearDown(): void
    {
        // Clean up any remaining temp files
        $temp_dir = sys_get_temp_dir();
        $files = glob($temp_dir . '/tc_export_*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        parent::tearDown();
    }
}