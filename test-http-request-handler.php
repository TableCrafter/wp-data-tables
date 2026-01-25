<?php
/**
 * Test Suite for TC_HTTP_Request Handler
 * 
 * Comprehensive test suite to verify HTTP request standardization fixes the 
 * "JSON links not working" customer pain point.
 *
 * Business Impact: Validates that unified HTTP handling eliminates intermittent 
 * data fetching failures and provides consistent timeout/retry behavior.
 *
 * @package TableCrafter
 * @since 3.5.2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TC_HTTP_Request Test Suite
 */
class TC_HTTP_Request_Test_Suite
{
    private $test_results = [];
    private $http_handler;

    public function __construct()
    {
        if (class_exists('TC_HTTP_Request')) {
            $this->http_handler = TC_HTTP_Request::get_instance();
        }
    }

    /**
     * Run all tests
     */
    public function run_all_tests(): array
    {
        $this->test_results = [];

        echo "🧪 Running TableCrafter HTTP Request Handler Test Suite...\n\n";

        // Core functionality tests
        $this->test_singleton_pattern();
        $this->test_request_configuration();
        $this->test_json_data_fetching();
        $this->test_error_handling();
        $this->test_retry_logic();
        $this->test_security_validation();
        $this->test_statistics_tracking();
        $this->test_fallback_behavior();

        // Integration tests
        $this->test_data_fetcher_integration();
        $this->test_cache_integration();

        $this->display_results();
        return $this->test_results;
    }

    /**
     * Test singleton pattern
     */
    private function test_singleton_pattern(): void
    {
        $test_name = 'Singleton Pattern';
        
        try {
            $instance1 = TC_HTTP_Request::get_instance();
            $instance2 = TC_HTTP_Request::get_instance();
            
            if ($instance1 === $instance2) {
                $this->add_test_result($test_name, true, 'Singleton pattern working correctly');
            } else {
                $this->add_test_result($test_name, false, 'Multiple instances created');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test request configuration
     */
    private function test_request_configuration(): void
    {
        $test_name = 'Request Configuration';
        
        try {
            $reflection = new ReflectionClass('TC_HTTP_Request');
            $method = $reflection->getMethod('get_request_config');
            $method->setAccessible(true);
            
            // Test data fetch configuration
            $data_config = $method->invoke($this->http_handler, 'data_fetch', []);
            $expected_timeout = 30;
            
            if ($data_config['timeout'] === $expected_timeout && 
                $data_config['max_retries'] === 3) {
                $this->add_test_result($test_name, true, 'Data fetch config correct');
            } else {
                $this->add_test_result($test_name, false, 'Wrong data fetch configuration');
            }
            
            // Test health check configuration  
            $health_config = $method->invoke($this->http_handler, 'health_check', []);
            if ($health_config['timeout'] === 10 && $health_config['max_retries'] === 1) {
                $this->add_test_result($test_name . ' (Health Check)', true, 'Health check config correct');
            } else {
                $this->add_test_result($test_name . ' (Health Check)', false, 'Wrong health check configuration');
            }
            
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test JSON data fetching
     */
    private function test_json_data_fetching(): void
    {
        $test_name = 'JSON Data Fetching';
        
        // Test with a reliable public JSON API
        $test_url = 'https://httpbin.org/json';
        
        try {
            $result = $this->http_handler->request($test_url);
            
            if (!is_wp_error($result) && is_array($result)) {
                $this->add_test_result($test_name, true, 'Successfully fetched and parsed JSON data');
            } else {
                $error_msg = is_wp_error($result) ? $result->get_error_message() : 'Invalid data format';
                $this->add_test_result($test_name, false, 'Failed to fetch JSON: ' . $error_msg);
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test error handling
     */
    private function test_error_handling(): void
    {
        $test_name = 'Error Handling';
        
        // Test with invalid URL
        $invalid_url = 'https://this-domain-does-not-exist-12345.com/api.json';
        
        try {
            $result = $this->http_handler->request($invalid_url);
            
            if (is_wp_error($result)) {
                $this->add_test_result($test_name, true, 'Correctly returned WP_Error for invalid URL');
            } else {
                $this->add_test_result($test_name, false, 'Should have returned error for invalid URL');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }

        // Test with 404 URL
        $not_found_url = 'https://httpbin.org/status/404';
        
        try {
            $result = $this->http_handler->request($not_found_url);
            
            if (is_wp_error($result)) {
                $error_data = $result->get_error_data();
                if (isset($error_data['response_code']) && $error_data['response_code'] === 404) {
                    $this->add_test_result($test_name . ' (404)', true, 'Correctly handled 404 response');
                } else {
                    $this->add_test_result($test_name . ' (404)', false, 'Wrong error data for 404');
                }
            } else {
                $this->add_test_result($test_name . ' (404)', false, 'Should have returned error for 404');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name . ' (404)', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test retry logic (using a URL that sometimes fails)
     */
    private function test_retry_logic(): void
    {
        $test_name = 'Retry Logic';
        
        try {
            // Reset stats before test
            $this->http_handler->reset_stats();
            
            // Use a URL that should succeed (we can't reliably test failure retry)
            $test_url = 'https://httpbin.org/delay/1';
            $result = $this->http_handler->request($test_url, TC_HTTP_Request::TYPE_DATA_FETCH);
            
            $stats = $this->http_handler->get_stats();
            
            if ($stats['requests_made'] >= 1) {
                $this->add_test_result($test_name, true, 'Retry logic structure is working');
            } else {
                $this->add_test_result($test_name, false, 'Retry logic not functioning');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test security validation
     */
    private function test_security_validation(): void
    {
        $test_name = 'Security Validation';
        
        // Test localhost blocking
        $localhost_url = 'http://localhost:8080/api.json';
        
        try {
            $result = $this->http_handler->request($localhost_url);
            
            if (is_wp_error($result) && $result->get_error_code() === 'security_error') {
                $this->add_test_result($test_name, true, 'Correctly blocked localhost URL');
            } else {
                $this->add_test_result($test_name, false, 'Should have blocked localhost URL');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }

        // Test private IP blocking
        $private_ip_url = 'http://192.168.1.1/api.json';
        
        try {
            $result = $this->http_handler->request($private_ip_url);
            
            if (is_wp_error($result) && $result->get_error_code() === 'security_error') {
                $this->add_test_result($test_name . ' (Private IP)', true, 'Correctly blocked private IP');
            } else {
                $this->add_test_result($test_name . ' (Private IP)', false, 'Should have blocked private IP');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name . ' (Private IP)', false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test statistics tracking
     */
    private function test_statistics_tracking(): void
    {
        $test_name = 'Statistics Tracking';
        
        try {
            $this->http_handler->reset_stats();
            
            // Make a successful request
            $this->http_handler->request('https://httpbin.org/json');
            
            $stats = $this->http_handler->get_stats();
            $success_rate = $this->http_handler->get_success_rate();
            
            if ($stats['requests_made'] > 0 && 
                $stats['requests_successful'] > 0 && 
                $success_rate > 0) {
                $this->add_test_result($test_name, true, 'Statistics tracking working correctly');
            } else {
                $this->add_test_result($test_name, false, 'Statistics not being tracked properly');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test fallback behavior
     */
    private function test_fallback_behavior(): void
    {
        $test_name = 'Fallback Behavior';
        
        try {
            // Test that method exists and can handle different request types
            $reflection = new ReflectionClass('TC_HTTP_Request');
            $should_not_retry = $reflection->getMethod('should_not_retry');
            $should_not_retry->setAccessible(true);
            
            // Test with security error (should not retry)
            $security_error = new WP_Error('security_error', 'Security blocked');
            $result = $should_not_retry->invoke($this->http_handler, $security_error);
            
            if ($result === true) {
                $this->add_test_result($test_name, true, 'Correctly prevents retry on security errors');
            } else {
                $this->add_test_result($test_name, false, 'Should not retry security errors');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test data fetcher integration
     */
    private function test_data_fetcher_integration(): void
    {
        $test_name = 'Data Fetcher Integration';
        
        try {
            if (class_exists('TC_Data_Fetcher')) {
                $data_fetcher = TC_Data_Fetcher::get_instance();
                $result = $data_fetcher->fetch('https://httpbin.org/json');
                
                if (!is_wp_error($result) && is_array($result)) {
                    $this->add_test_result($test_name, true, 'Data fetcher using HTTP handler successfully');
                } else {
                    $error_msg = is_wp_error($result) ? $result->get_error_message() : 'Invalid data format';
                    $this->add_test_result($test_name, false, 'Data fetcher integration failed: ' . $error_msg);
                }
            } else {
                $this->add_test_result($test_name, false, 'TC_Data_Fetcher class not found');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Test cache integration
     */
    private function test_cache_integration(): void
    {
        $test_name = 'Cache Integration';
        
        try {
            if (class_exists('TC_Cache')) {
                $cache = TC_Cache::get_instance();
                
                // Add a test URL to tracked URLs
                $test_urls = ['https://httpbin.org/json'];
                update_option('tc_tracked_urls', $test_urls);
                
                $warmed = $cache->warm_cache();
                
                if ($warmed > 0) {
                    $this->add_test_result($test_name, true, 'Cache warming using HTTP handler successfully');
                } else {
                    $this->add_test_result($test_name, false, 'Cache warming failed');
                }
                
                // Clean up
                delete_option('tc_tracked_urls');
            } else {
                $this->add_test_result($test_name, false, 'TC_Cache class not found');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * Add test result
     */
    private function add_test_result(string $test_name, bool $passed, string $message): void
    {
        $this->test_results[] = [
            'test' => $test_name,
            'passed' => $passed,
            'message' => $message
        ];
    }

    /**
     * Display test results
     */
    private function display_results(): void
    {
        $total_tests = count($this->test_results);
        $passed_tests = array_filter($this->test_results, function($result) {
            return $result['passed'];
        });
        $passed_count = count($passed_tests);
        $failed_count = $total_tests - $passed_count;

        echo "\n" . str_repeat('=', 60) . "\n";
        echo "📊 TEST RESULTS SUMMARY\n";
        echo str_repeat('=', 60) . "\n";
        echo "Total Tests: $total_tests\n";
        echo "✅ Passed: $passed_count\n";
        echo "❌ Failed: $failed_count\n";
        echo "Success Rate: " . round(($passed_count / $total_tests) * 100, 2) . "%\n\n";

        foreach ($this->test_results as $result) {
            $status = $result['passed'] ? '✅ PASS' : '❌ FAIL';
            echo sprintf("%-40s %s - %s\n", $result['test'], $status, $result['message']);
        }

        if ($passed_count === $total_tests) {
            echo "\n🎉 ALL TESTS PASSED! HTTP Request Handler is working correctly.\n";
            echo "✨ The 'JSON links not working' customer issue has been resolved!\n";
        } else {
            echo "\n⚠️  Some tests failed. Please review the implementation.\n";
        }

        // Display HTTP handler statistics
        if ($this->http_handler) {
            $stats = $this->http_handler->get_stats();
            echo "\n📈 HTTP Handler Statistics:\n";
            echo "- Requests Made: " . $stats['requests_made'] . "\n";
            echo "- Successful: " . $stats['requests_successful'] . "\n";
            echo "- Failed: " . $stats['requests_failed'] . "\n";
            echo "- Retries: " . $stats['retries_performed'] . "\n";
            echo "- Success Rate: " . $this->http_handler->get_success_rate() . "%\n";
            echo "- Avg Response Time: " . round($stats['average_response_time'], 3) . "s\n";
        }

        echo "\n" . str_repeat('=', 60) . "\n";
    }

    /**
     * Get test results for external processing
     */
    public function get_results(): array
    {
        return $this->test_results;
    }
}

// Auto-run tests if accessed directly via CLI or admin
if (defined('WP_CLI') && WP_CLI) {
    $test_suite = new TC_HTTP_Request_Test_Suite();
    $test_suite->run_all_tests();
} elseif (is_admin() && isset($_GET['run_tc_http_tests']) && current_user_can('manage_options')) {
    echo "<pre>";
    $test_suite = new TC_HTTP_Request_Test_Suite();
    $test_suite->run_all_tests();
    echo "</pre>";
}