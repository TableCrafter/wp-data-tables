<?php
/**
 * WordPress Function Mocks for Standalone Testing
 *
 * These mock functions allow unit tests to run without a full WordPress installation.
 *
 * @package TableCrafter\Tests
 */

if (!function_exists('wp_parse_url')) {
    function wp_parse_url($url, $component = -1) {
        return parse_url($url, $component);
    }
}

if (!function_exists('wp_http_validate_url')) {
    function wp_http_validate_url($url) {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return false;
        }

        $host = $parsed['host'];

        // Block localhost
        if (in_array(strtolower($host), array('localhost', '127.0.0.1', '[::1]'))) {
            return false;
        }

        // Block private IPs
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $is_private = !filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
            if ($is_private) {
                return false;
            }
        }

        return $url;
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('wp_unslash')) {
    function wp_unslash($value) {
        return stripslashes($value);
    }
}

if (!function_exists('wp_hash')) {
    function wp_hash($data, $scheme = 'auth') {
        return md5($data);
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512) {
        return json_encode($data, $options, $depth);
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return 0;
    }
}

if (!function_exists('get_transient')) {
    $GLOBALS['_tc_transients'] = array();

    function get_transient($transient) {
        return isset($GLOBALS['_tc_transients'][$transient])
            ? $GLOBALS['_tc_transients'][$transient]['value']
            : false;
    }
}

if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0) {
        $GLOBALS['_tc_transients'][$transient] = array(
            'value' => $value,
            'expiration' => $expiration,
            'time' => time()
        );
        return true;
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient($transient) {
        if (isset($GLOBALS['_tc_transients'][$transient])) {
            unset($GLOBALS['_tc_transients'][$transient]);
            return true;
        }
        return false;
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value, ...$args) {
        return $value;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) {
        return true;
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {
        // For testing, accept a specific test nonce
        return $nonce === 'test_nonce_' . $action;
    }
}

/**
 * Reset all mocked transients (useful between tests)
 */
function tc_reset_mock_transients() {
    $GLOBALS['_tc_transients'] = array();
}
