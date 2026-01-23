<?php
/**
 * PHPUnit Bootstrap for TableCrafter Tests
 *
 * @package TableCrafter
 */

// Define test constants
define('TABLECRAFTER_TESTING', true);

// Try to load WordPress test environment
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// Check if WordPress test framework is available
if (file_exists($_tests_dir . '/includes/functions.php')) {
    // WordPress integration tests
    require_once $_tests_dir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    function _manually_load_plugin() {
        require dirname(dirname(__DIR__)) . '/tablecrafter.php';
    }
    tests_add_filter('muplugins_loaded', '_manually_load_plugin');

    // Start up the WP testing environment.
    require $_tests_dir . '/includes/bootstrap.php';
} else {
    // Standalone unit tests (no WordPress required)
    echo "WordPress test framework not found. Running standalone unit tests.\n";
    echo "For integration tests, run: bin/install-wp-tests.sh\n\n";

    // Define WordPress constants for standalone testing
    if (!defined('ABSPATH')) {
        define('ABSPATH', '/tmp/wordpress/');
    }
    if (!defined('WPINC')) {
        define('WPINC', 'wp-includes');
    }

    // Define plugin constants
    define('TABLECRAFTER_VERSION', '3.4.0');
    define('TABLECRAFTER_URL', 'http://example.com/wp-content/plugins/tablecrafter-wp-data-tables/');
    define('TABLECRAFTER_PATH', dirname(dirname(__DIR__)) . '/');

    // Mock WordPress functions for standalone tests
    require_once __DIR__ . '/mocks/wordpress-mocks.php';

    // Load plugin classes (without full WordPress)
    require_once TABLECRAFTER_PATH . 'includes/class-tc-security.php';
    require_once TABLECRAFTER_PATH . 'includes/class-tc-cache.php';
}

// Load Composer autoloader if available
$composer_autoload = dirname(dirname(__DIR__)) . '/vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
}
