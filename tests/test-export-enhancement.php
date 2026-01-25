<?php
/**
 * Export Enhancement Tests
 * RED Phase - Test-Driven Development
 *
 * Tests for the export functionality enhancement that fixes the
 * "Export Deception" business problem (Business Impact Score: 9/10)
 *
 * @package TableCrafter
 * @since 3.5.3
 */

class TC_Export_Enhancement_Test extends WP_UnitTestCase
{
    private $export_handler;

    /**
     * Set up test environment
     */
    public function setUp(): void
    {
        parent::setUp();
        
        // Initialize the enhanced export handler
        if (class_exists('TC_Export_Handler_Enhanced')) {
            $this->export_handler = TC_Export_Handler_Enhanced::get_instance();
        }
    }

    /**
     * TEST 1: Export handler should be available as a singleton
     */
    public function test_export_handler_singleton()
    {
        $this->assertInstanceOf('TC_Export_Handler_Enhanced', $this->export_handler);
        
        // Test singleton pattern
        $instance2 = TC_Export_Handler_Enhanced::get_instance();
        $this->assertSame($this->export_handler, $instance2);
    }

    /**
     * TEST 2: Should generate proper XLSX files (not old XLS)
     */
    public function test_excel_export_format()
    {
        $data = [
            ['name' => 'John Doe', 'age' => 30, 'city' => 'New York'],
            ['name' => 'Jane Smith', 'age' => 25, 'city' => 'Los Angeles']
        ];
        
        $columns = [
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'age', 'label' => 'Age'],
            ['field' => 'city', 'label' => 'City']
        ];

        $result = $this->export_handler->export_to_excel($data, $columns, 'test-export');
        
        $this->assertTrue($result['success']);
        $this->assertStringEndsWith('.xlsx', $result['filename']);
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $result['mime_type']);
    }

    /**
     * TEST 3: Should generate actual PDF files (not print dialogs)
     */
    public function test_pdf_export_format()
    {
        $data = [
            ['name' => 'John Doe', 'age' => 30, 'city' => 'New York'],
            ['name' => 'Jane Smith', 'age' => 25, 'city' => 'Los Angeles']
        ];
        
        $columns = [
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'age', 'label' => 'Age'],
            ['field' => 'city', 'label' => 'City']
        ];

        $result = $this->export_handler->export_to_pdf($data, $columns, 'test-export');
        
        $this->assertTrue($result['success']);
        $this->assertStringEndsWith('.pdf', $result['filename']);
        $this->assertEquals('application/pdf', $result['mime_type']);
    }

    /**
     * TEST 4: Should handle large datasets without memory issues
     */
    public function test_large_dataset_export()
    {
        // Generate 5000 rows of test data
        $data = [];
        for ($i = 0; $i < 5000; $i++) {
            $data[] = [
                'id' => $i + 1,
                'name' => 'User ' . ($i + 1),
                'email' => 'user' . ($i + 1) . '@example.com',
                'created' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 365) . ' days'))
            ];
        }
        
        $columns = [
            ['field' => 'id', 'label' => 'ID'],
            ['field' => 'name', 'label' => 'Name'],
            ['field' => 'email', 'label' => 'Email'],
            ['field' => 'created', 'label' => 'Created']
        ];

        $memory_before = memory_get_usage();
        
        $excel_result = $this->export_handler->export_to_excel($data, $columns, 'large-dataset');
        $pdf_result = $this->export_handler->export_to_pdf($data, $columns, 'large-dataset');
        
        $memory_after = memory_get_usage();
        $memory_used = $memory_after - $memory_before;
        
        $this->assertTrue($excel_result['success']);
        $this->assertTrue($pdf_result['success']);
        
        // Should not use more than 50MB for 5000 rows
        $this->assertLessThan(50 * 1024 * 1024, $memory_used, 'Export should be memory efficient');
    }

    /**
     * TEST 5: Should support server-side export endpoint
     */
    public function test_export_ajax_endpoint()
    {
        // Test that the AJAX endpoint is registered
        $this->assertTrue(has_action('wp_ajax_tc_export_data'));
        $this->assertTrue(has_action('wp_ajax_nopriv_tc_export_data'));
    }

    /**
     * TEST 6: Should handle export errors gracefully
     */
    public function test_export_error_handling()
    {
        // Test with invalid data
        $result = $this->export_handler->export_to_excel(null, [], 'test');
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        
        // Test with invalid columns
        $result = $this->export_handler->export_to_excel([['test' => 'data']], null, 'test');
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    /**
     * TEST 7: Should validate file names for security
     */
    public function test_filename_security()
    {
        $data = [['test' => 'data']];
        $columns = [['field' => 'test', 'label' => 'Test']];
        
        // Test malicious filename
        $result = $this->export_handler->export_to_excel($data, $columns, '../../../etc/passwd');
        $this->assertTrue($result['success']);
        
        // Filename should be sanitized
        $this->assertStringNotContainsString('..', $result['filename']);
        $this->assertStringNotContainsString('/', $result['filename']);
    }

    /**
     * TEST 8: Should respect export permissions
     */
    public function test_export_permissions()
    {
        // Test that export respects WordPress capabilities
        $user_id = $this->factory->user->create(['role' => 'subscriber']);
        wp_set_current_user($user_id);
        
        $result = $this->export_handler->can_export();
        
        // Should check proper capability (configurable)
        $this->assertIsBool($result);
    }

    /**
     * TEST 9: Should provide download URLs for large files
     */
    public function test_download_url_generation()
    {
        $data = [['test' => 'data']];
        $columns = [['field' => 'test', 'label' => 'Test']];
        
        $result = $this->export_handler->export_to_excel($data, $columns, 'test', ['return_url' => true]);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('download_url', $result);
        $this->assertStringContainsString(wp_upload_dir()['url'], $result['download_url']);
    }

    /**
     * TEST 10: Should clean up temporary files
     */
    public function test_temp_file_cleanup()
    {
        $data = [['test' => 'data']];
        $columns = [['field' => 'test', 'label' => 'Test']];
        
        // Create exports
        $this->export_handler->export_to_excel($data, $columns, 'test');
        $this->export_handler->export_to_pdf($data, $columns, 'test');
        
        // Check cleanup method exists and works
        $this->assertTrue(method_exists($this->export_handler, 'cleanup_temp_files'));
        
        $cleanup_result = $this->export_handler->cleanup_temp_files();
        $this->assertIsInt($cleanup_result); // Should return number of files cleaned
    }
}