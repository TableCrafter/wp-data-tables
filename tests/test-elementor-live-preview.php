<?php
/**
 * Elementor Live Preview Test Suite
 * 
 * Comprehensive tests for TableCrafter Elementor live preview functionality,
 * AJAX endpoints, JavaScript integration, and performance optimization.
 * 
 * @package TableCrafter
 * @since 3.1.1
 */

class TableCrafterElementorLivePreviewTest extends WP_UnitTestCase
{
    private $tablecrafter;
    private $test_data;
    private $temp_json_file;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Get TableCrafter instance
        $this->tablecrafter = TableCrafter::get_instance();
        
        // Generate test data
        $this->test_data = [
            [
                'id' => 1,
                'name' => 'John Smith',
                'email' => 'john@example.com',
                'department' => 'Engineering',
                'salary' => 75000,
                'active' => true,
                'hire_date' => '2023-01-15'
            ],
            [
                'id' => 2,
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'department' => 'Marketing',
                'salary' => 65000,
                'active' => true,
                'hire_date' => '2023-02-20'
            ],
            [
                'id' => 3,
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'department' => 'Sales',
                'salary' => 55000,
                'active' => false,
                'hire_date' => '2022-11-10'
            ],
            [
                'id' => 4,
                'name' => 'Alice Brown',
                'email' => 'alice@example.com',
                'department' => 'Engineering',
                'salary' => 80000,
                'active' => true,
                'hire_date' => '2023-03-05'
            ],
            [
                'id' => 5,
                'name' => 'Charlie Wilson',
                'email' => 'charlie@example.com',
                'department' => 'HR',
                'salary' => 60000,
                'active' => true,
                'hire_date' => '2023-01-30'
            ]
        ];
        
        // Create temporary JSON file
        $this->temp_json_file = tempnam(sys_get_temp_dir(), 'tc_live_preview_test') . '.json';
        file_put_contents($this->temp_json_file, json_encode($this->test_data));
    }
    
    public function tearDown(): void
    {
        // Clean up temporary file
        if (file_exists($this->temp_json_file)) {
            unlink($this->temp_json_file);
        }
        
        parent::tearDown();
    }
    
    /**
     * Test AJAX preview endpoint basic functionality
     */
    public function test_ajax_elementor_preview_basic()
    {
        // Set up user with appropriate permissions
        $user_id = $this->factory->user->create(array('role' => 'editor'));
        wp_set_current_user($user_id);
        
        // Prepare AJAX request
        $_POST['action'] = 'tc_elementor_preview';
        $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
        $_POST['source'] = $this->temp_json_file;
        $_POST['preview_rows'] = 3;
        
        // Mock AJAX environment
        add_action('wp_ajax_tc_elementor_preview', array($this->tablecrafter, 'ajax_elementor_preview'));
        
        // Capture output
        ob_start();
        
        try {
            do_action('wp_ajax_tc_elementor_preview');
        } catch (WPAjaxDieStopException $e) {
            // Expected for successful AJAX
        }
        
        $output = ob_get_clean();
        
        // Parse JSON response
        $response = json_decode($output, true);
        
        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertIsArray($response['data']);
        $this->assertCount(3, $response['data']); // Should limit to 3 rows
        $this->assertEquals('John Smith', $response['data'][0]['name']);
    }
    
    /**
     * Test AJAX preview with column filtering
     */
    public function test_ajax_preview_column_filtering()
    {
        // Set up user with appropriate permissions
        $user_id = $this->factory->user->create(array('role' => 'administrator'));
        wp_set_current_user($user_id);
        
        // Prepare AJAX request with column filtering
        $_POST['action'] = 'tc_elementor_preview';
        $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
        $_POST['source'] = $this->temp_json_file;
        $_POST['include'] = 'name,email,department';
        $_POST['exclude'] = 'id,salary';
        $_POST['preview_rows'] = 2;
        
        // Capture output
        ob_start();
        
        try {
            do_action('wp_ajax_tc_elementor_preview');
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertTrue($response['success']);
        $this->assertCount(2, $response['data']);
        
        // Check that filtering worked
        $first_row = $response['data'][0];
        $this->assertArrayHasKey('name', $first_row);
        $this->assertArrayHasKey('email', $first_row);
        $this->assertArrayHasKey('department', $first_row);
        $this->assertArrayNotHasKey('id', $first_row);
        $this->assertArrayNotHasKey('salary', $first_row);
    }
    
    /**
     * Test AJAX preview security and permissions
     */
    public function test_ajax_preview_security()
    {
        // Test without user login
        wp_set_current_user(0);
        
        $_POST['action'] = 'tc_elementor_preview';
        $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
        $_POST['source'] = $this->temp_json_file;
        
        ob_start();
        
        try {
            do_action('wp_ajax_tc_elementor_preview');
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertFalse($response['success']);
        $this->assertStringContains('permissions', $response['data']);
    }
    
    /**
     * Test AJAX preview with invalid nonce
     */
    public function test_ajax_preview_invalid_nonce()
    {
        $user_id = $this->factory->user->create(array('role' => 'editor'));
        wp_set_current_user($user_id);
        
        $_POST['action'] = 'tc_elementor_preview';
        $_POST['nonce'] = 'invalid_nonce';
        $_POST['source'] = $this->temp_json_file;
        
        ob_start();
        
        try {
            do_action('wp_ajax_tc_elementor_preview');
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertFalse($response['success']);
        $this->assertEquals('Invalid nonce', $response['data']);
    }
    
    /**
     * Test AJAX preview with missing source
     */
    public function test_ajax_preview_missing_source()
    {
        $user_id = $this->factory->user->create(array('role' => 'editor'));
        wp_set_current_user($user_id);
        
        $_POST['action'] = 'tc_elementor_preview';
        $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
        // No source provided
        
        ob_start();
        
        try {
            do_action('wp_ajax_tc_elementor_preview');
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertFalse($response['success']);
        $this->assertEquals('Source URL is required', $response['data']);
    }
    
    /**
     * Test preview row limiting
     */
    public function test_preview_row_limiting()
    {
        $user_id = $this->factory->user->create(array('role' => 'editor'));
        wp_set_current_user($user_id);
        
        // Test with excessive preview rows
        $_POST['action'] = 'tc_elementor_preview';
        $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
        $_POST['source'] = $this->temp_json_file;
        $_POST['preview_rows'] = 100; // Should be limited to 25
        
        ob_start();
        
        try {
            do_action('wp_ajax_tc_elementor_preview');
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertTrue($response['success']);
        // Should return all 5 test rows (limited by data, not by 25 limit)
        $this->assertCount(5, $response['data']);
    }
    
    /**
     * Test preview with nested JSON structure
     */
    public function test_preview_nested_json()
    {
        // Create nested test data
        $nested_data = [
            'response' => [
                'status' => 'success',
                'data' => [
                    'items' => $this->test_data
                ]
            ]
        ];
        
        $nested_file = tempnam(sys_get_temp_dir(), 'tc_nested_test') . '.json';
        file_put_contents($nested_file, json_encode($nested_data));
        
        $user_id = $this->factory->user->create(array('role' => 'editor'));
        wp_set_current_user($user_id);
        
        $_POST['action'] = 'tc_elementor_preview';
        $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
        $_POST['source'] = $nested_file;
        $_POST['root'] = 'response.data.items';
        $_POST['preview_rows'] = 2;
        
        ob_start();
        
        try {
            do_action('wp_ajax_tc_elementor_preview');
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertTrue($response['success']);
        $this->assertCount(2, $response['data']);
        $this->assertEquals('John Smith', $response['data'][0]['name']);
        
        // Clean up
        unlink($nested_file);
    }
    
    /**
     * Test preview performance with large dataset
     */
    public function test_preview_performance()
    {
        // Generate large dataset
        $large_data = [];
        for ($i = 1; $i <= 1000; $i++) {
            $large_data[] = [
                'id' => $i,
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@example.com',
                'department' => 'Dept ' . ($i % 10),
                'data' => str_repeat('x', 100) // Add some bulk
            ];
        }
        
        $large_file = tempnam(sys_get_temp_dir(), 'tc_large_test') . '.json';
        file_put_contents($large_file, json_encode($large_data));
        
        $user_id = $this->factory->user->create(array('role' => 'editor'));
        wp_set_current_user($user_id);
        
        $_POST['action'] = 'tc_elementor_preview';
        $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
        $_POST['source'] = $large_file;
        $_POST['preview_rows'] = 5;
        
        $start_time = microtime(true);
        $start_memory = memory_get_usage();
        
        ob_start();
        
        try {
            do_action('wp_ajax_tc_elementor_preview');
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $end_time = microtime(true);
        $end_memory = memory_get_usage();
        
        $response = json_decode($output, true);
        
        // Performance assertions
        $processing_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
        $memory_used = $end_memory - $start_memory;
        
        $this->assertTrue($response['success']);
        $this->assertCount(5, $response['data']); // Should limit to 5 rows
        $this->assertLessThan(2000, $processing_time, 'Preview should process large dataset quickly');
        $this->assertLessThan(10 * 1024 * 1024, $memory_used, 'Memory usage should be reasonable');
        
        // Clean up
        unlink($large_file);
    }
    
    /**
     * Test enhanced Elementor widget controls
     */
    public function test_elementor_widget_enhanced_controls()
    {
        // Skip if Elementor not available
        if (!class_exists('\Elementor\Widget_Base')) {
            $this->markTestSkipped('Elementor not available');
            return;
        }
        
        require_once TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        $widget = new TC_Elementor_Widget();
        
        // Test that the widget includes preview dependencies
        $script_deps = $widget->get_script_depends();
        $this->assertContains('tc-elementor-preview', $script_deps);
        $this->assertContains('tablecrafter-lib', $script_deps);
        $this->assertContains('tablecrafter-frontend', $script_deps);
    }
    
    /**
     * Test shortcode attribute generation with preview mode
     */
    public function test_shortcode_generation_with_preview()
    {
        if (!class_exists('\Elementor\Widget_Base')) {
            $this->markTestSkipped('Elementor not available');
            return;
        }
        
        require_once TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        
        // Mock Elementor editor mode
        $mock_editor = $this->getMockBuilder('stdClass')
            ->setMethods(['is_edit_mode'])
            ->getMock();
        $mock_editor->method('is_edit_mode')->willReturn(true);
        
        $mock_plugin = $this->getMockBuilder('stdClass')
            ->setMethods(['editor'])
            ->getMock();
        $mock_plugin->editor = $mock_editor;
        
        // Mock Elementor Plugin instance
        if (!class_exists('\Elementor\Plugin')) {
            eval('namespace Elementor { class Plugin { public static $instance; } }');
        }
        \Elementor\Plugin::$instance = $mock_plugin;
        
        $widget = new TC_Elementor_Widget();
        
        $settings = [
            'data_source' => ['url' => 'https://api.example.com/data.json'],
            'enable_live_preview' => 'yes',
            'preview_rows' => 10,
            'enable_search' => 'yes'
        ];
        
        // Use reflection to access protected method
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('build_shortcode_attributes');
        $method->setAccessible(true);
        $shortcode = $method->invoke($widget, $settings);
        
        // Verify preview attributes are included in editor mode
        $this->assertStringContains('elementor_preview="true"', $shortcode);
        $this->assertStringContains('preview_rows="10"', $shortcode);
    }
    
    /**
     * Test JavaScript asset registration
     */
    public function test_javascript_asset_registration()
    {
        // Call register_assets method
        $this->tablecrafter->register_assets();
        
        // Check that the preview script is registered
        global $wp_scripts;
        
        $this->assertTrue($wp_scripts->query('tc-elementor-preview', 'registered'));
        
        // Verify dependencies
        $script = $wp_scripts->query('tc-elementor-preview', 'registered');
        $this->assertContains('jquery', $script->deps);
        $this->assertContains('tablecrafter-lib', $script->deps);
        
        // Verify localized data
        $this->assertTrue(isset($wp_scripts->registered['tc-elementor-preview']->extra['data']));
    }
    
    /**
     * Test error handling for malformed JSON
     */
    public function test_preview_malformed_json_handling()
    {
        // Create file with malformed JSON
        $malformed_file = tempnam(sys_get_temp_dir(), 'tc_malformed_test') . '.json';
        file_put_contents($malformed_file, '{ "invalid": json syntax }');
        
        $user_id = $this->factory->user->create(array('role' => 'editor'));
        wp_set_current_user($user_id);
        
        $_POST['action'] = 'tc_elementor_preview';
        $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
        $_POST['source'] = $malformed_file;
        
        ob_start();
        
        try {
            do_action('wp_ajax_tc_elementor_preview');
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertFalse($response['success']);
        $this->assertStringContains('valid JSON', $response['data']);
        
        // Clean up
        unlink($malformed_file);
    }
    
    /**
     * Test preview with empty dataset
     */
    public function test_preview_empty_dataset()
    {
        // Create file with empty array
        $empty_file = tempnam(sys_get_temp_dir(), 'tc_empty_test') . '.json';
        file_put_contents($empty_file, '[]');
        
        $user_id = $this->factory->user->create(array('role' => 'editor'));
        wp_set_current_user($user_id);
        
        $_POST['action'] = 'tc_elementor_preview';
        $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
        $_POST['source'] = $empty_file;
        
        ob_start();
        
        try {
            do_action('wp_ajax_tc_elementor_preview');
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        $this->assertFalse($response['success']);
        $this->assertStringContains('Empty', $response['data']);
        
        // Clean up
        unlink($empty_file);
    }
    
    /**
     * Test widget rendering with live preview enabled
     */
    public function test_widget_render_with_live_preview()
    {
        if (!class_exists('\Elementor\Widget_Base')) {
            $this->markTestSkipped('Elementor not available');
            return;
        }
        
        require_once TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        
        $widget = $this->getMockBuilder('TC_Elementor_Widget')
            ->setMethods(['get_settings_for_display', 'get_id'])
            ->getMock();
        
        $widget->method('get_settings_for_display')
               ->willReturn([
                   'data_source' => ['url' => 'https://api.example.com/data.json'],
                   'enable_live_preview' => 'yes',
                   'preview_rows' => 5,
                   'enable_search' => 'yes'
               ]);
        
        $widget->method('get_id')
               ->willReturn('test-preview-123');
        
        // Capture output
        ob_start();
        
        // Use reflection to call protected render method
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);
        $method->invoke($widget);
        
        $output = ob_get_clean();
        
        $this->assertStringContains('tc-elementor-widget-wrapper', $output);
        $this->assertStringContains('tc-elementor-preview', $output);
        $this->assertStringContains('id="tc-elementor-test-preview-123"', $output);
    }
}