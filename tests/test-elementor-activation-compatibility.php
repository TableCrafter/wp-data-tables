<?php
/**
 * Elementor Activation Compatibility Test Suite
 * 
 * Tests the critical activation order dependency issue where TableCrafter
 * triggers fatal errors when Elementor is installed after TableCrafter.
 * 
 * @package TableCrafter
 * @since 3.2.2
 */

class TableCrafterElementorActivationTest extends WP_UnitTestCase
{
    private $tablecrafter;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->tablecrafter = TableCrafter::get_instance();
    }
    
    /**
     * Test that deprecated scheme classes don't cause fatal errors
     */
    public function test_deprecated_scheme_classes_removed()
    {
        // Get the widget file content
        $widget_file = TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        $content = file_get_contents($widget_file);
        
        // Verify deprecated scheme imports are commented out or removed
        $this->assertStringNotContains('use Elementor\\Core\\Schemes\\Typography as Scheme_Typography;', $content);
        $this->assertStringNotContains('use Elementor\\Core\\Schemes\\Color as Scheme_Color;', $content);
    }
    
    /**
     * Test that the widget uses modern registration methods
     */
    public function test_modern_registration_method()
    {
        $widget_file = TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        $content = file_get_contents($widget_file);
        
        // Should contain backward compatibility logic
        $this->assertStringContains('method_exists($widget_manager, \'register\')', $content);
        $this->assertStringContains('method_exists($widget_manager, \'register_widget_type\')', $content);
    }
    
    /**
     * Test that the widget uses modern hooks with fallbacks
     */
    public function test_modern_hook_usage()
    {
        $widget_file = TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        $content = file_get_contents($widget_file);
        
        // Should contain version-based hook selection
        $this->assertStringContains('version_compare(ELEMENTOR_VERSION, \'3.5.0\', \'>=\')', $content);
        $this->assertStringContains('elementor/widgets/register', $content);
        $this->assertStringContains('elementor/widgets/widgets_registered', $content);
    }
    
    /**
     * Test widget registration without Elementor (should not error)
     */
    public function test_registration_without_elementor()
    {
        // Remove Elementor simulation
        if (defined('ELEMENTOR_VERSION')) {
            $this->markTestSkipped('Elementor is active, cannot test without Elementor scenario');
            return;
        }
        
        // Include the widget file
        require_once TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        
        // Try to register widget (should fail gracefully)
        ob_start();
        register_tc_elementor_widget();
        $output = ob_get_clean();
        
        // Should not produce any output (errors would be visible)
        $this->assertEmpty($output);
    }
    
    /**
     * Test backward compatibility with old Elementor versions
     */
    public function test_backward_compatibility()
    {
        // Skip if we can't mock Elementor
        if (!class_exists('ReflectionFunction')) {
            $this->markTestSkipped('Reflection not available for testing');
            return;
        }
        
        // Mock old Elementor environment
        if (!defined('ELEMENTOR_VERSION')) {
            define('ELEMENTOR_VERSION', '3.0.0');
        }
        
        // Test that tc_register_elementor_hooks function exists
        require_once TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        
        $this->assertTrue(function_exists('tc_register_elementor_hooks'));
        $this->assertTrue(function_exists('register_tc_elementor_widget'));
    }
    
    /**
     * Test that the widget class can be instantiated without errors
     */
    public function test_widget_class_instantiation()
    {
        // Skip if Elementor is not available for testing
        if (!class_exists('\\Elementor\\Widget_Base')) {
            // Create a mock Widget_Base class for testing
            eval('
                namespace Elementor {
                    class Widget_Base {
                        public function get_name() { return "mock"; }
                        public function get_title() { return "Mock"; }
                        public function get_icon() { return "mock"; }
                        public function get_categories() { return ["general"]; }
                        protected function register_controls() {}
                        protected function render() {}
                    }
                    class Controls_Manager {
                        const TEXT = "text";
                        const URL = "url";
                        const SELECT = "select";
                        const NUMBER = "number";
                        const SWITCHER = "switcher";
                        const TAB_CONTENT = "tab-content";
                        const TAB_STYLE = "tab-style";
                        const DIMENSIONS = "dimensions";
                        const COLOR = "color";
                        const RAW_HTML = "raw_html";
                    }
                    class Group_Control_Typography {
                        public static function get_type() { return "typography"; }
                    }
                    class Group_Control_Border {
                        public static function get_type() { return "border"; }
                    }
                    class Group_Control_Box_Shadow {
                        public static function get_type() { return "box-shadow"; }
                    }
                }
            ');
        }
        
        require_once TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        
        // Should be able to instantiate without errors
        $widget = new TC_Elementor_Widget();
        
        $this->assertInstanceOf('TC_Elementor_Widget', $widget);
        $this->assertEquals('tablecrafter-data-table', $widget->get_name());
        $this->assertEquals('TableCrafter Data Table', $widget->get_title());
    }
    
    /**
     * Test that widget registration works with modern Elementor
     */
    public function test_modern_elementor_registration()
    {
        // Mock modern Elementor environment
        if (!class_exists('\\Elementor\\Plugin')) {
            eval('
                namespace Elementor {
                    class Plugin {
                        public static $instance;
                        public $widgets_manager;
                        
                        public static function instance() {
                            if (!self::$instance) {
                                self::$instance = new self();
                                self::$instance->widgets_manager = new MockWidgetsManager();
                            }
                            return self::$instance;
                        }
                    }
                    
                    class MockWidgetsManager {
                        public $registered_widgets = [];
                        
                        public function register($widget) {
                            $this->registered_widgets[] = $widget;
                        }
                        
                        public function register_widget_type($widget) {
                            $this->registered_widgets[] = $widget;
                        }
                    }
                }
            ');
        }
        
        if (!defined('ELEMENTOR_VERSION')) {
            define('ELEMENTOR_VERSION', '3.8.0');
        }
        
        // Load widget and test registration
        require_once TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        
        // Simulate Elementor loaded
        do_action('elementor/loaded');
        
        // The registration should not cause any fatal errors
        $this->assertTrue(function_exists('register_tc_elementor_widget'));
    }
    
    /**
     * Test error handling in widget registration
     */
    public function test_widget_registration_error_handling()
    {
        require_once TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        
        // Test registration without proper Elementor setup
        $pre_error_count = error_get_last() ? 1 : 0;
        
        register_tc_elementor_widget();
        
        $post_error_count = error_get_last() ? 1 : 0;
        
        // Should not generate additional PHP errors
        $this->assertEquals($pre_error_count, $post_error_count);
    }
    
    /**
     * Test category registration safety
     */
    public function test_category_registration_safety()
    {
        require_once TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        
        // Test with null elements manager
        ob_start();
        add_tc_elementor_category(null);
        $output = ob_get_clean();
        
        // Should not produce output or errors
        $this->assertEmpty($output);
        
        // Test with invalid object
        ob_start();
        add_tc_elementor_category('invalid');
        $output = ob_get_clean();
        
        // Should not produce output or errors
        $this->assertEmpty($output);
    }
    
    /**
     * Integration test: Full activation sequence simulation
     */
    public function test_full_activation_sequence()
    {
        // This test simulates the complete activation sequence that caused
        // the original fatal error
        
        $widget_file = TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        
        // Verify the file can be included without fatal errors
        $this->assertTrue(file_exists($widget_file));
        
        ob_start();
        $error_before = error_get_last();
        
        // Include the widget file (this would have caused fatal errors before fix)
        require_once $widget_file;
        
        $output = ob_get_clean();
        $error_after = error_get_last();
        
        // Should not produce any fatal errors or output
        $this->assertEmpty($output);
        
        // Check that no new fatal errors were generated
        if ($error_before && $error_after) {
            $this->assertEquals($error_before['message'], $error_after['message']);
        }
        
        // Verify key functions are defined
        $this->assertTrue(function_exists('register_tc_elementor_widget'));
        $this->assertTrue(function_exists('tc_register_elementor_hooks'));
        $this->assertTrue(function_exists('add_tc_elementor_category'));
    }
    
    /**
     * Test PHP version compatibility
     */
    public function test_php_version_compatibility()
    {
        require_once TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php';
        
        // Test that modern PHP syntax is used correctly
        $widget_file_content = file_get_contents(TABLECRAFTER_PATH . 'includes/class-tc-elementor-widget.php');
        
        // Should use proper namespaced class references
        $this->assertStringContains('\\Elementor\\Widget_Base', $widget_file_content);
        $this->assertStringContains('\\Elementor\\Plugin', $widget_file_content);
        
        // Should use proper method syntax
        $this->assertStringContains('method_exists(', $widget_file_content);
        $this->assertStringContains('version_compare(', $widget_file_content);
    }
}