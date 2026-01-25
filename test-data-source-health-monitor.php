<?php
/**
 * TDD Test Suite for Data Source Health Monitor
 * 
 * Following TRUE Test-Driven Development:
 * 1. RED: Write failing tests first ❌
 * 2. GREEN: Write minimal code to pass ✅  
 * 3. REFACTOR: Improve while keeping tests green 🔄
 *
 * Business Problem: Enterprise customers have no proactive notification 
 * when external data sources fail, causing silent table breakage.
 *
 * @package TableCrafter
 * @since 3.5.3
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * TDD Test Suite for TC_Data_Source_Health_Monitor
 * 
 * These tests are written BEFORE implementation to define expected behavior
 */
class TC_Data_Source_Health_Monitor_TDD_Tests
{
    private $test_results = [];
    private $health_monitor;

    public function __construct()
    {
        // This will initially fail - that's the point of TDD RED phase
        if (class_exists('TC_Data_Source_Health_Monitor')) {
            $this->health_monitor = TC_Data_Source_Health_Monitor::get_instance();
        }
    }

    /**
     * Run all TDD tests (RED phase - these should fail initially)
     */
    public function run_tdd_tests(): array
    {
        echo "🔴 TDD RED PHASE: Running tests that should FAIL initially...\n\n";
        
        $this->test_results = [];

        // Core functionality tests (RED phase)
        $this->test_class_exists();
        $this->test_singleton_pattern();
        $this->test_register_data_source();
        $this->test_check_source_health();
        $this->test_get_health_status();
        $this->test_health_history_tracking();
        $this->test_notification_system();
        $this->test_webhook_integration();
        $this->test_admin_dashboard_data();
        $this->test_cron_job_scheduling();

        $this->display_tdd_results();
        return $this->test_results;
    }

    /**
     * TEST 1: Class should exist
     * RED: This will fail until we create the class
     */
    private function test_class_exists(): void
    {
        $test_name = 'TC_Data_Source_Health_Monitor class exists';
        
        if (class_exists('TC_Data_Source_Health_Monitor')) {
            $this->add_test_result($test_name, true, 'Class exists');
        } else {
            $this->add_test_result($test_name, false, 'Class does not exist - EXPECTED TO FAIL in RED phase');
        }
    }

    /**
     * TEST 2: Should implement singleton pattern
     * RED: Will fail until we implement get_instance()
     */
    private function test_singleton_pattern(): void
    {
        $test_name = 'Singleton pattern implementation';
        
        if (!$this->health_monitor) {
            $this->add_test_result($test_name, false, 'Cannot test - class not available - EXPECTED FAILURE');
            return;
        }

        try {
            $instance1 = TC_Data_Source_Health_Monitor::get_instance();
            $instance2 = TC_Data_Source_Health_Monitor::get_instance();
            
            if ($instance1 === $instance2) {
                $this->add_test_result($test_name, true, 'Singleton working correctly');
            } else {
                $this->add_test_result($test_name, false, 'Multiple instances created');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * TEST 3: Should be able to register data sources for monitoring
     * RED: Will fail until we implement register_source()
     */
    private function test_register_data_source(): void
    {
        $test_name = 'Register data source for monitoring';
        
        if (!$this->health_monitor) {
            $this->add_test_result($test_name, false, 'Cannot test - class not available - EXPECTED FAILURE');
            return;
        }

        try {
            $test_url = 'https://api.example.com/data.json';
            $result = $this->health_monitor->register_source($test_url, [
                'check_interval' => 300, // 5 minutes
                'timeout' => 10,
                'expected_keys' => ['data', 'status']
            ]);
            
            if ($result === true) {
                $this->add_test_result($test_name, true, 'Data source registered successfully');
            } else {
                $this->add_test_result($test_name, false, 'Failed to register data source');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * TEST 4: Should be able to check health of registered sources
     * RED: Will fail until we implement check_health()
     */
    private function test_check_source_health(): void
    {
        $test_name = 'Check data source health';
        
        if (!$this->health_monitor) {
            $this->add_test_result($test_name, false, 'Cannot test - class not available - EXPECTED FAILURE');
            return;
        }

        try {
            $test_url = 'https://httpbin.org/json';
            $health_result = $this->health_monitor->check_health($test_url);
            
            if (is_array($health_result) && 
                isset($health_result['status']) && 
                isset($health_result['response_time']) &&
                isset($health_result['timestamp'])) {
                $this->add_test_result($test_name, true, 'Health check returned proper format');
            } else {
                $this->add_test_result($test_name, false, 'Invalid health check response format');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * TEST 5: Should provide health status for all monitored sources
     * RED: Will fail until we implement get_all_health_status()
     */
    private function test_get_health_status(): void
    {
        $test_name = 'Get health status for all sources';
        
        if (!$this->health_monitor) {
            $this->add_test_result($test_name, false, 'Cannot test - class not available - EXPECTED FAILURE');
            return;
        }

        try {
            $all_status = $this->health_monitor->get_all_health_status();
            
            if (is_array($all_status)) {
                $this->add_test_result($test_name, true, 'Health status retrieved as array');
            } else {
                $this->add_test_result($test_name, false, 'Invalid health status format');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * TEST 6: Should track health history for trend analysis
     * RED: Will fail until we implement get_health_history()
     */
    private function test_health_history_tracking(): void
    {
        $test_name = 'Health history tracking';
        
        if (!$this->health_monitor) {
            $this->add_test_result($test_name, false, 'Cannot test - class not available - EXPECTED FAILURE');
            return;
        }

        try {
            $test_url = 'https://httpbin.org/json';
            $history = $this->health_monitor->get_health_history($test_url, 24); // Last 24 hours
            
            if (is_array($history)) {
                $this->add_test_result($test_name, true, 'Health history retrieved');
            } else {
                $this->add_test_result($test_name, false, 'Invalid health history format');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * TEST 7: Should send notifications when sources fail
     * RED: Will fail until we implement notification system
     */
    private function test_notification_system(): void
    {
        $test_name = 'Notification system for failures';
        
        if (!$this->health_monitor) {
            $this->add_test_result($test_name, false, 'Cannot test - class not available - EXPECTED FAILURE');
            return;
        }

        try {
            $notification_config = [
                'email' => ['admin@example.com'],
                'webhook' => 'https://hooks.slack.com/...',
                'threshold' => 2 // failures before notification
            ];
            
            $result = $this->health_monitor->configure_notifications($notification_config);
            
            if ($result === true) {
                $this->add_test_result($test_name, true, 'Notification configuration accepted');
            } else {
                $this->add_test_result($test_name, false, 'Failed to configure notifications');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * TEST 8: Should support webhook integration for real-time alerts
     * RED: Will fail until we implement webhook functionality
     */
    private function test_webhook_integration(): void
    {
        $test_name = 'Webhook integration for alerts';
        
        if (!$this->health_monitor) {
            $this->add_test_result($test_name, false, 'Cannot test - class not available - EXPECTED FAILURE');
            return;
        }

        try {
            $webhook_result = $this->health_monitor->send_webhook_alert([
                'source_url' => 'https://api.example.com/data.json',
                'status' => 'failed',
                'error' => 'Connection timeout',
                'timestamp' => time()
            ]);
            
            // Should return true even if webhook fails (fire-and-forget)
            if (is_bool($webhook_result)) {
                $this->add_test_result($test_name, true, 'Webhook alert method exists');
            } else {
                $this->add_test_result($test_name, false, 'Invalid webhook response');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * TEST 9: Should provide data for admin dashboard
     * RED: Will fail until we implement get_dashboard_data()
     */
    private function test_admin_dashboard_data(): void
    {
        $test_name = 'Admin dashboard data';
        
        if (!$this->health_monitor) {
            $this->add_test_result($test_name, false, 'Cannot test - class not available - EXPECTED FAILURE');
            return;
        }

        try {
            $dashboard_data = $this->health_monitor->get_dashboard_data();
            
            if (is_array($dashboard_data) && 
                isset($dashboard_data['summary']) &&
                isset($dashboard_data['sources']) &&
                isset($dashboard_data['recent_failures'])) {
                $this->add_test_result($test_name, true, 'Dashboard data has correct structure');
            } else {
                $this->add_test_result($test_name, false, 'Invalid dashboard data format');
            }
        } catch (Exception $e) {
            $this->add_test_result($test_name, false, 'Exception: ' . $e->getMessage());
        }
    }

    /**
     * TEST 10: Should integrate with WordPress cron for scheduled checks
     * RED: Will fail until we implement cron integration
     */
    private function test_cron_job_scheduling(): void
    {
        $test_name = 'Cron job scheduling';
        
        if (!$this->health_monitor) {
            $this->add_test_result($test_name, false, 'Cannot test - class not available - EXPECTED FAILURE');
            return;
        }

        try {
            $cron_result = $this->health_monitor->schedule_health_checks();
            
            if ($cron_result === true) {
                $this->add_test_result($test_name, true, 'Cron jobs scheduled successfully');
            } else {
                $this->add_test_result($test_name, false, 'Failed to schedule cron jobs');
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
     * Display TDD test results
     */
    private function display_tdd_results(): void
    {
        $total_tests = count($this->test_results);
        $passed_tests = array_filter($this->test_results, function($result) {
            return $result['passed'];
        });
        $passed_count = count($passed_tests);
        $failed_count = $total_tests - $passed_count;

        echo "\n" . str_repeat('=', 70) . "\n";
        echo "🔴 TDD RED PHASE RESULTS - FAILURES ARE EXPECTED\n";
        echo str_repeat('=', 70) . "\n";
        echo "Total Tests: $total_tests\n";
        echo "✅ Passed: $passed_count\n";
        echo "❌ Failed: $failed_count (EXPECTED - we haven't written code yet!)\n\n";

        foreach ($this->test_results as $result) {
            $status = $result['passed'] ? '✅ PASS' : '❌ FAIL';
            echo sprintf("%-50s %s - %s\n", $result['test'], $status, $result['message']);
        }

        if ($failed_count > 0) {
            echo "\n🔴 RED PHASE COMPLETE! Most tests failed as expected.\n";
            echo "📝 Next: GREEN phase - Write minimal code to make tests pass.\n";
        } else {
            echo "\n🤔 Unexpected: All tests passed. Check if implementation already exists.\n";
        }

        echo "\n" . str_repeat('=', 70) . "\n";
    }

    /**
     * Get test results for external processing
     */
    public function get_results(): array
    {
        return $this->test_results;
    }
}

// Auto-run TDD tests if accessed directly
if (php_sapi_name() === 'cli') {
    // CLI execution
    echo "🔴 Starting TDD RED Phase...\n";
    $test_suite = new TC_Data_Source_Health_Monitor_TDD_Tests();
    $test_suite->run_tdd_tests();
} elseif (defined('ABSPATH') && function_exists('is_admin') && is_admin() && isset($_GET['run_tdd_health_tests']) && current_user_can('manage_options')) {
    echo "<pre>";
    $test_suite = new TC_Data_Source_Health_Monitor_TDD_Tests();
    $test_suite->run_tdd_tests();
    echo "</pre>";
}