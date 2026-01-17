<?php
/**
 * Simple validation script for performance optimization features
 * 
 * This script validates that our performance optimization code is syntactically correct
 * and has the expected structure without requiring a full WordPress environment.
 */

// Include the performance optimizer
$performance_optimizer_path = dirname(__DIR__) . '/includes/class-tc-performance-optimizer.php';

if (!file_exists($performance_optimizer_path)) {
    die("ERROR: Performance optimizer file not found at: $performance_optimizer_path\n");
}

// Check if file is syntactically valid PHP
$check_syntax = shell_exec("php -l " . escapeshellarg($performance_optimizer_path) . " 2>&1");
if (strpos($check_syntax, 'No syntax errors') === false) {
    die("ERROR: Syntax error in performance optimizer:\n$check_syntax\n");
}

echo "✓ Performance optimizer PHP syntax is valid\n";

// Check JavaScript file
$js_optimizer_path = dirname(__DIR__) . '/assets/js/performance-optimizer.js';

if (!file_exists($js_optimizer_path)) {
    die("ERROR: Performance optimizer JS file not found at: $js_optimizer_path\n");
}

echo "✓ Performance optimizer JS file exists\n";

// Read and analyze the PHP file
$php_content = file_get_contents($performance_optimizer_path);

// Check for required class and methods
$required_elements = [
    'class TC_Performance_Optimizer' => 'Performance optimizer class',
    'const VIRTUAL_SCROLL_THRESHOLD' => 'Virtual scroll threshold constant',
    'const VIRTUAL_ROWS_RENDERED' => 'Virtual rows rendered constant',
    'public static function init()' => 'Initialization method',
    'public static function optimize_rendering(' => 'Main optimization method',
    'public static function ajax_virtual_scroll_data()' => 'AJAX handler for virtual scroll',
    'private static function estimate_row_height(' => 'Row height estimation method',
    'private static function apply_lightweight_optimizations(' => 'Lightweight optimizations method'
];

foreach ($required_elements as $element => $description) {
    if (strpos($php_content, $element) === false) {
        die("ERROR: Missing $description ($element) in performance optimizer\n");
    }
    echo "✓ Found $description\n";
}

// Read and analyze the JavaScript file
$js_content = file_get_contents($js_optimizer_path);

$required_js_elements = [
    'class VirtualScrollManager' => 'Virtual scroll manager class',
    'class PerformanceMonitor' => 'Performance monitor class',
    'createVirtualContainer()' => 'Virtual container creation method',
    'handleScroll()' => 'Scroll handling method',
    'renderVisibleRows()' => 'Row rendering method',
    'setupLazyLoading()' => 'Lazy loading setup method',
    'window.TableCrafterPerf' => 'Global performance API'
];

foreach ($required_js_elements as $element => $description) {
    if (strpos($js_content, $element) === false) {
        die("ERROR: Missing $description ($element) in JS performance optimizer\n");
    }
    echo "✓ Found $description\n";
}

// Test data generation function
function generateTestData($count) {
    $data = [];
    for ($i = 0; $i < $count; $i++) {
        $data[] = [
            'id' => $i + 1,
            'name' => 'User ' . ($i + 1),
            'email' => 'user' . ($i + 1) . '@example.com',
            'department' => ['Engineering', 'Marketing', 'Sales'][$i % 3],
            'salary' => 30000 + ($i * 1000),
            'bio' => str_repeat('Lorem ipsum ', rand(5, 50)),
            'avatar' => 'https://example.com/avatar' . ($i % 10) . '.jpg'
        ];
    }
    return $data;
}

// Test basic functionality without WordPress dependencies
echo "\n--- Testing Core Logic ---\n";

// Mock WordPress functions that might be called
if (!function_exists('add_action')) {
    function add_action($hook, $callback) { return true; }
}
if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script() { return true; }
}
if (!function_exists('wp_localize_script')) {
    function wp_localize_script() { return true; }
}

// Test performance threshold logic
$small_data = generateTestData(50);
$large_data = generateTestData(1500);

echo "✓ Generated test datasets (50 and 1500 rows)\n";

// Test virtual scroll threshold constant
if (class_exists('TC_Performance_Optimizer', false)) {
    $reflection = new ReflectionClass('TC_Performance_Optimizer');
    $threshold = $reflection->getConstant('VIRTUAL_SCROLL_THRESHOLD');
    
    if ($threshold && is_numeric($threshold)) {
        echo "✓ Virtual scroll threshold: $threshold\n";
        
        // Verify logic
        $should_use_virtual_small = count($small_data) > $threshold;
        $should_use_virtual_large = count($large_data) > $threshold;
        
        if (!$should_use_virtual_small && $should_use_virtual_large) {
            echo "✓ Threshold logic works correctly\n";
        } else {
            echo "ERROR: Threshold logic failed - small: $should_use_virtual_small, large: $should_use_virtual_large\n";
        }
    }
}

// Test test file structure
$test_files = [
    'tests/test-performance-optimization.php' => 'Performance optimization unit tests',
    'tests/e2e/performance-optimization.spec.js' => 'End-to-end performance tests'
];

foreach ($test_files as $file => $description) {
    $full_path = dirname(__DIR__) . '/' . $file;
    if (file_exists($full_path)) {
        echo "✓ Found $description\n";
        
        // Check file size to ensure it's not empty
        $file_size = filesize($full_path);
        if ($file_size > 1000) { // At least 1KB
            echo "  └─ File size: " . number_format($file_size) . " bytes\n";
        } else {
            echo "  └─ WARNING: File seems small ($file_size bytes)\n";
        }
    } else {
        echo "ERROR: Missing $description at $full_path\n";
    }
}

// Test CSS integration points
$css_file = dirname(__DIR__) . '/assets/css/tablecrafter.css';
if (file_exists($css_file)) {
    $css_content = file_get_contents($css_file);
    
    // Check for performance-related CSS classes
    $css_classes = [
        '.tc-virtual-container',
        '.tc-lazy-image',
        '.tc-expandable-text',
        '.tc-skeleton'
    ];
    
    $existing_classes = [];
    foreach ($css_classes as $class) {
        if (strpos($css_content, $class) !== false) {
            $existing_classes[] = $class;
        }
    }
    
    if (count($existing_classes) > 0) {
        echo "✓ Found performance CSS classes: " . implode(', ', $existing_classes) . "\n";
    } else {
        echo "NOTE: No performance-specific CSS classes found (may need to be added)\n";
    }
}

// Validate memory and performance expectations
$memory_limit = ini_get('memory_limit');
$max_execution_time = ini_get('max_execution_time');

echo "\n--- Environment Check ---\n";
echo "✓ PHP Memory Limit: $memory_limit\n";
echo "✓ Max Execution Time: $max_execution_time seconds\n";

// Test memory usage estimation
$estimated_memory_per_row = 1024; // 1KB per row estimate
$max_rows_before_memory_issue = (1024 * 1024 * 64) / $estimated_memory_per_row; // 64MB worth

echo "✓ Estimated max rows before memory issues: " . number_format($max_rows_before_memory_issue) . "\n";

if ($reflection && $reflection->getConstant('VIRTUAL_SCROLL_THRESHOLD') < $max_rows_before_memory_issue) {
    echo "✓ Virtual scroll threshold is safely below memory limit\n";
}

echo "\n--- Integration Checks ---\n";

// Check if main plugin file includes the performance optimizer
$main_plugin_file = dirname(__DIR__) . '/tablecrafter.php';
if (file_exists($main_plugin_file)) {
    $main_content = file_get_contents($main_plugin_file);
    
    if (strpos($main_content, 'class-tc-performance-optimizer.php') !== false) {
        echo "✓ Main plugin includes performance optimizer\n";
    } else {
        echo "ERROR: Main plugin does not include performance optimizer\n";
    }
    
    if (strpos($main_content, "define('TABLECRAFTER_VERSION', '3.1.0')") !== false) {
        echo "✓ Version updated to 3.1.0\n";
    } else {
        echo "WARNING: Version may not be updated to 3.1.0\n";
    }
}

echo "\n--- Validation Complete ---\n";
echo "Performance optimization implementation appears to be structurally sound.\n";
echo "Next steps: Run full WordPress unit tests and E2E tests in proper environment.\n";