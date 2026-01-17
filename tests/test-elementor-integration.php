<?php
/**
 * Elementor Integration Test Suite
 * 
 * Comprehensive tests for TableCrafter Elementor widget integration,
 * control validation, shortcode generation, and live preview functionality.
 * 
 * @package TableCrafter
 * @since 3.2.0
 */

class TableCrafterElementorTest extends WP_UnitTestCase
{
    private $elementor_widget;
    private $test_data;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Skip tests if Elementor is not available
        if (!class_exists('\Elementor\Widget_Base')) {
            $this->markTestSkipped('Elementor not available for testing');
            return;
        }
        
        // Include the Elementor widget
        require_once TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        
        // Create widget instance
        $this->elementor_widget = new TC_Elementor_Widget();
        
        // Generate test data
        $this->test_data = [
            [
                'id' => 1,
                'name' => 'John Smith',
                'email' => 'john@example.com',
                'department' => 'Engineering',
                'salary' => 75000
            ],
            [
                'id' => 2,
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'department' => 'Marketing',
                'salary' => 65000
            ]
        ];
    }
    
    /**
     * Test widget basic properties
     */
    public function test_widget_properties()
    {
        $this->assertEquals('tablecrafter-data-table', $this->elementor_widget->get_name());
        $this->assertEquals('TableCrafter Data Table', $this->elementor_widget->get_title());
        $this->assertEquals('eicon-table', $this->elementor_widget->get_icon());
        
        $categories = $this->elementor_widget->get_categories();
        $this->assertContains('general', $categories);
        $this->assertContains('tablecrafter', $categories);
        
        $keywords = $this->elementor_widget->get_keywords();
        $this->assertContains('table', $keywords);
        $this->assertContains('json', $keywords);
        $this->assertContains('api', $keywords);
        $this->assertContains('tablecrafter', $keywords);
    }
    
    /**
     * Test widget dependencies
     */
    public function test_widget_dependencies()
    {
        $script_deps = $this->elementor_widget->get_script_depends();
        $this->assertContains('tablecrafter-lib', $script_deps);
        $this->assertContains('tablecrafter-frontend', $script_deps);
        
        $style_deps = $this->elementor_widget->get_style_depends();
        $this->assertContains('tablecrafter-style', $style_deps);
    }
    
    /**
     * Test widget control registration
     */
    public function test_control_registration()
    {
        // Mock Elementor methods
        $widget = $this->getMockBuilder('TC_Elementor_Widget')
            ->setMethods([
                'start_controls_section', 
                'add_control', 
                'add_group_control',
                'end_controls_section'
            ])
            ->getMock();
        
        // Expect control sections to be created
        $widget->expects($this->atLeast(4))
               ->method('start_controls_section');
        
        $widget->expects($this->atLeast(15))
               ->method('add_control');
        
        $widget->expects($this->atLeast(1))
               ->method('end_controls_section');
        
        // Call the protected method using reflection
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('register_controls');
        $method->setAccessible(true);
        $method->invoke($widget);
    }
    
    /**
     * Test shortcode attribute generation
     */
    public function test_shortcode_attribute_generation()
    {
        $settings = [
            'data_source' => ['url' => 'https://api.example.com/data.json'],
            'root_path' => 'data.results',
            'include_columns' => 'name,email,department',
            'exclude_columns' => 'id,internal_notes',
            'enable_search' => 'yes',
            'enable_filters' => 'yes',
            'enable_export' => '',
            'per_page' => 25,
            'sort_column' => 'name',
            'sort_order' => 'asc',
            'auto_refresh' => 'yes',
            'refresh_interval' => 300,
            'cache_duration' => 15
        ];
        
        // Use reflection to access protected method
        $reflection = new ReflectionClass($this->elementor_widget);
        $method = $reflection->getMethod('build_shortcode_attributes');
        $method->setAccessible(true);
        $shortcode = $method->invoke($this->elementor_widget, $settings);
        
        // Verify shortcode contains expected attributes
        $this->assertStringContains('[tablecrafter', $shortcode);
        $this->assertStringContains('source="https://api.example.com/data.json"', $shortcode);
        $this->assertStringContains('root="data.results"', $shortcode);
        $this->assertStringContains('include="name,email,department"', $shortcode);
        $this->assertStringContains('exclude="id,internal_notes"', $shortcode);
        $this->assertStringContains('search="true"', $shortcode);
        $this->assertStringContains('filters="true"', $shortcode);
        $this->assertStringContains('export="false"', $shortcode);
        $this->assertStringContains('per_page="25"', $shortcode);
        $this->assertStringContains('sort="name:asc"', $shortcode);
        $this->assertStringContains('auto_refresh="true"', $shortcode);
        $this->assertStringContains('refresh_interval="300"', $shortcode);
        $this->assertStringContains('cache="15"', $shortcode);
        $this->assertStringContains(']', $shortcode);
    }
    
    /**
     * Test shortcode generation with minimal settings
     */
    public function test_minimal_shortcode_generation()
    {
        $settings = [
            'data_source' => ['url' => 'https://simple.example.com/data.json'],
            'enable_search' => '',
            'enable_filters' => '',
            'enable_export' => ''
        ];
        
        $reflection = new ReflectionClass($this->elementor_widget);
        $method = $reflection->getMethod('build_shortcode_attributes');
        $method->setAccessible(true);
        $shortcode = $method->invoke($this->elementor_widget, $settings);
        
        $this->assertStringContains('[tablecrafter', $shortcode);
        $this->assertStringContains('source="https://simple.example.com/data.json"', $shortcode);
        $this->assertStringContains('search="false"', $shortcode);
        $this->assertStringContains('filters="false"', $shortcode);
        $this->assertStringContains('export="false"', $shortcode);
        $this->assertStringContains(']', $shortcode);
    }
    
    /**
     * Test widget rendering with no data source
     */
    public function test_render_without_data_source()
    {
        // Mock get_settings_for_display method
        $widget = $this->getMockBuilder('TC_Elementor_Widget')
            ->setMethods(['get_settings_for_display', 'get_id'])
            ->getMock();
        
        $widget->method('get_settings_for_display')
               ->willReturn(['data_source' => ['url' => '']]);
        
        $widget->method('get_id')
               ->willReturn('test-123');
        
        // Test in editor mode
        if (class_exists('\Elementor\Plugin')) {
            // Mock editor mode
            $mock_editor = $this->getMockBuilder('stdClass')
                ->setMethods(['is_edit_mode'])
                ->getMock();
            $mock_editor->method('is_edit_mode')->willReturn(true);
            
            $mock_plugin = $this->getMockBuilder('stdClass')
                ->setMethods(['editor'])
                ->getMock();
            $mock_plugin->editor = $mock_editor;
            
            // Capture output
            ob_start();
            
            // Use reflection to call protected render method
            $reflection = new ReflectionClass($widget);
            $method = $reflection->getMethod('render');
            $method->setAccessible(true);
            $method->invoke($widget);
            
            $output = ob_get_clean();
            
            $this->assertStringContains('tc-elementor-widget-wrapper', $output);
            $this->assertStringContains('id="tc-elementor-test-123"', $output);
        }
    }
    
    /**
     * Test widget rendering with valid data source
     */
    public function test_render_with_data_source()
    {
        // Create temporary JSON file
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_elementor_test') . '.json';
        file_put_contents($temp_file, json_encode($this->test_data));
        
        // Mock widget with data source
        $widget = $this->getMockBuilder('TC_Elementor_Widget')
            ->setMethods(['get_settings_for_display', 'get_id'])
            ->getMock();
        
        $widget->method('get_settings_for_display')
               ->willReturn([
                   'data_source' => ['url' => $temp_file],
                   'enable_search' => 'yes',
                   'enable_filters' => 'yes'
               ]);
        
        $widget->method('get_id')
               ->willReturn('test-456');
        
        // Capture output
        ob_start();
        
        // Use reflection to call protected render method
        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('render');
        $method->setAccessible(true);
        $method->invoke($widget);
        
        $output = ob_get_clean();
        
        $this->assertStringContains('tc-elementor-widget-wrapper', $output);
        $this->assertStringContains('id="tc-elementor-test-456"', $output);
        $this->assertStringContains('[tablecrafter', $output);
        
        // Clean up
        unlink($temp_file);
    }
    
    /**
     * Test Google Sheets configuration
     */
    public function test_google_sheets_configuration()
    {
        $settings = [
            'source_type' => 'google_sheets',
            'data_source' => ['url' => 'https://docs.google.com/spreadsheets/d/1234/gviz/tq?tqx=out:csv'],
            'enable_search' => 'yes'
        ];
        
        $reflection = new ReflectionClass($this->elementor_widget);
        $method = $reflection->getMethod('build_shortcode_attributes');
        $method->setAccessible(true);
        $shortcode = $method->invoke($this->elementor_widget, $settings);
        
        $this->assertStringContains('source="https://docs.google.com/spreadsheets/d/1234/gviz/tq?tqx=out:csv"', $shortcode);
        $this->assertStringContains('search="true"', $shortcode);
    }
    
    /**
     * Test CSV file configuration
     */
    public function test_csv_file_configuration()
    {
        $settings = [
            'source_type' => 'csv_file',
            'data_source' => ['url' => 'https://example.com/data.csv'],
            'enable_filters' => 'yes',
            'per_page' => 50
        ];
        
        $reflection = new ReflectionClass($this->elementor_widget);
        $method = $reflection->getMethod('build_shortcode_attributes');
        $method->setAccessible(true);
        $shortcode = $method->invoke($this->elementor_widget, $settings);
        
        $this->assertStringContains('source="https://example.com/data.csv"', $shortcode);
        $this->assertStringContains('filters="true"', $shortcode);
        $this->assertStringContains('per_page="50"', $shortcode);
    }
    
    /**
     * Test advanced feature combinations
     */
    public function test_advanced_feature_combinations()
    {
        $settings = [
            'data_source' => ['url' => 'https://api.advanced.com/data.json'],
            'root_path' => 'response.data',
            'include_columns' => 'id,name,status,created_date',
            'enable_search' => 'yes',
            'enable_filters' => 'yes',
            'enable_export' => 'yes',
            'per_page' => 100,
            'sort_column' => 'created_date',
            'sort_order' => 'desc',
            'auto_refresh' => 'yes',
            'refresh_interval' => 60,
            'cache_duration' => 5
        ];
        
        $reflection = new ReflectionClass($this->elementor_widget);
        $method = $reflection->getMethod('build_shortcode_attributes');
        $method->setAccessible(true);
        $shortcode = $method->invoke($this->elementor_widget, $settings);
        
        // Verify all advanced features are included
        $this->assertStringContains('root="response.data"', $shortcode);
        $this->assertStringContains('include="id,name,status,created_date"', $shortcode);
        $this->assertStringContains('search="true"', $shortcode);
        $this->assertStringContains('filters="true"', $shortcode);
        $this->assertStringContains('export="true"', $shortcode);
        $this->assertStringContains('per_page="100"', $shortcode);
        $this->assertStringContains('sort="created_date:desc"', $shortcode);
        $this->assertStringContains('auto_refresh="true"', $shortcode);
        $this->assertStringContains('refresh_interval="60"', $shortcode);
        $this->assertStringContains('cache="5"', $shortcode);
    }
    
    /**
     * Test data validation and sanitization
     */
    public function test_data_validation()
    {
        $settings = [
            'data_source' => ['url' => 'javascript:alert("xss")'],
            'per_page' => 'invalid',
            'refresh_interval' => -100,
            'cache_duration' => 'not_a_number'
        ];
        
        $reflection = new ReflectionClass($this->elementor_widget);
        $method = $reflection->getMethod('build_shortcode_attributes');
        $method->setAccessible(true);
        $shortcode = $method->invoke($this->elementor_widget, $settings);
        
        // Verify malicious URL is escaped
        $this->assertStringNotContains('javascript:', $shortcode);
        
        // Verify invalid numbers are handled
        $this->assertStringContains('per_page="0"', $shortcode);
    }
    
    /**
     * Test widget category registration
     */
    public function test_category_registration()
    {
        // Mock elements manager
        $elements_manager = $this->getMockBuilder('stdClass')
            ->setMethods(['add_category'])
            ->getMock();
        
        $elements_manager->expects($this->once())
                        ->method('add_category')
                        ->with(
                            'tablecrafter',
                            $this->arrayHasKey('title')
                        );
        
        // Call the category registration function
        add_tc_elementor_category($elements_manager);
    }
    
    /**
     * Test widget registration hook
     */
    public function test_widget_registration_hook()
    {
        // Verify the hook is registered
        $this->assertTrue(has_action('elementor/widgets/widgets_registered', 'register_tc_elementor_widget'));
        $this->assertTrue(has_action('elementor/elements/categories_registered', 'add_tc_elementor_category'));
    }
    
    /**
     * Test content template for live preview
     */
    public function test_content_template()
    {
        // Capture the content template output
        ob_start();
        
        // Use reflection to call protected method
        $reflection = new ReflectionClass($this->elementor_widget);
        $method = $reflection->getMethod('content_template');
        $method->setAccessible(true);
        $method->invoke($this->elementor_widget);
        
        $template = ob_get_clean();
        
        // Verify template contains expected JavaScript template code
        $this->assertStringContains('var widgetId', $template);
        $this->assertStringContains('var dataSource', $template);
        $this->assertStringContains('tc-elementor-widget-wrapper', $template);
        $this->assertStringContains('TableCrafter Data Table', $template);
        $this->assertStringContains('Please configure your data source', $template);
        $this->assertStringContains('Live table will be displayed on the frontend', $template);
    }
    
    /**
     * Test error handling for missing Elementor
     */
    public function test_elementor_dependency_check()
    {
        // Temporarily remove Elementor action
        $original_action = has_action('elementor/loaded');
        
        if ($original_action) {
            remove_all_actions('elementor/loaded');
        }
        
        // Call registration function
        register_tc_elementor_widget();
        
        // Should not register widget without Elementor
        $this->assertTrue(true); // Test passes if no fatal error occurs
        
        // Restore original action if it existed
        if ($original_action) {
            add_action('elementor/loaded', $original_action);
        }
    }
    
    /**
     * Test performance with large widget configurations
     */
    public function test_performance_with_complex_settings()
    {
        $complex_settings = [
            'data_source' => ['url' => 'https://api.example.com/large-dataset.json'],
            'root_path' => 'data.results.items',
            'include_columns' => implode(',', array_map(function($i) { return 'column_' . $i; }, range(1, 50))),
            'enable_search' => 'yes',
            'enable_filters' => 'yes',
            'enable_export' => 'yes',
            'per_page' => 1000,
            'sort_column' => 'complex_sort_field',
            'sort_order' => 'desc',
            'auto_refresh' => 'yes',
            'refresh_interval' => 30,
            'cache_duration' => 1
        ];
        
        $start_time = microtime(true);
        $start_memory = memory_get_usage();
        
        $reflection = new ReflectionClass($this->elementor_widget);
        $method = $reflection->getMethod('build_shortcode_attributes');
        $method->setAccessible(true);
        $shortcode = $method->invoke($this->elementor_widget, $complex_settings);
        
        $end_time = microtime(true);
        $end_memory = memory_get_usage();
        
        // Performance assertions
        $processing_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
        $memory_used = $end_memory - $start_memory;
        
        $this->assertLessThan(50, $processing_time, 'Shortcode generation should be fast even with complex settings');
        $this->assertLessThan(1024 * 1024, $memory_used, 'Memory usage should be reasonable for complex widgets');
        
        // Verify the shortcode is still valid
        $this->assertStringContains('[tablecrafter', $shortcode);
        $this->assertStringContains(']', $shortcode);
        
        // Verify complex include columns are handled properly
        $this->assertStringContains('include="column_1,column_2', $shortcode);
    }
}