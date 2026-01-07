<?php
// Tests/ssrf_repro.php

// Mock necessary WP functions if they don't exist
if (!function_exists('wp_http_validate_url')) {
    function wp_http_validate_url($url)
    {
        // Simple mock of WP's validator for testing logic
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host)
            return false;

        // WP's actual validator calls wp_kses_bad_protocol, etc.
        // For this test, we care about IP rejection.
        if (in_array(strtolower($host), ['127.0.0.1', 'localhost', '::1']))
            return false;

        // Mock DNS Rebinding check (simulated)
        // In reality, wp_http_validate_url does gethostbynamel()
        if ($host === 'localtest.me') {
            // Simulate WP resolving this to 127.0.0.1 and blocking it
            return false;
        }

        return $url;
    }
}

// Mock other WP dependencies
if (!defined('ABSPATH'))
    define('ABSPATH', '/tmp/');
if (!defined('TABLECRAFTER_URL'))
    define('TABLECRAFTER_URL', 'http://example.com/wp-content/plugins/tablecrafter/');
if (!defined('TABLECRAFTER_PATH'))
    define('TABLECRAFTER_PATH', '/tmp/');
if (!defined('HOUR_IN_SECONDS'))
    define('HOUR_IN_SECONDS', 3600);
if (!function_exists('add_action')) {
    function add_action($h, $c, $p = 10, $a = 1)
    {
    }
}
if (!function_exists('add_shortcode')) {
    function add_shortcode($t, $c)
    {
    }
}
if (!function_exists('add_menu_page')) {
    function add_menu_page($a, $b, $c, $d, $e, $f, $g)
    {
    }
}
if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($f)
    {
        return 'http://example.com/';
    }
}
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($f)
    {
        return '/tmp/';
    }
}
if (!function_exists('wp_next_scheduled')) {
    function wp_next_scheduled($h)
    {
        return false;
    }
}
if (!function_exists('wp_schedule_event')) {
    function wp_schedule_event($t, $r, $h)
    {
    }
}
if (!function_exists('get_option')) {
    function get_option($o, $d = false)
    {
        return $d;
    }
}

require_once dirname(__FILE__) . '/../tablecrafter.php';

function test_validation()
{
    $tc = TableCrafter::get_instance();
    $reflection = new ReflectionClass($tc);
    $method = $reflection->getMethod('is_safe_url');
    $method->setAccessible(true);

    echo "Running SSRF Protection Tests:\n";
    echo "-----------------------------\n";

    // 1. Direct IP (Should be blocked)
    $ip_url = 'http://127.0.0.1/secret.json';
    $is_safe_ip = $method->invoke($tc, $ip_url);
    echo "1. Testing 127.0.0.1: " . ($is_safe_ip ? "FAIL (Allowed)" : "PASS (Blocked)") . "\n";

    // 2. Localhost (Should be blocked)
    $localhost = 'http://localhost/secret.json';
    $is_safe_local = $method->invoke($tc, $localhost);
    echo "2. Testing localhost: " . ($is_safe_local ? "FAIL (Allowed)" : "PASS (Blocked)") . "\n";

    // 3. DNS Rebinding simulation (Simulated Mock)
    $rebinding_url = 'http://localtest.me/secret.json';
    $is_safe_rebind = $method->invoke($tc, $rebinding_url);
    echo "3. Testing localtest.me (DNS Rebind): " . ($is_safe_rebind ? "FAIL (Allowed - VULNERABLE)" : "PASS (Blocked)") . "\n";

    // 4. Safe URL
    $safe_url = 'http://example.com/data.json';
    $is_safe_normal = $method->invoke($tc, $safe_url);
    echo "4. Testing example.com: " . ($is_safe_normal ? "PASS (Allowed)" : "FAIL (Blocked)") . "\n";
}

test_validation();
