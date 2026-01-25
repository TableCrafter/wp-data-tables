<?php
/**
 * TDD GREEN Phase Test Runner
 * 
 * Tests should now PASS after implementing minimal code
 */

// Load the class
include 'includes/class-tc-data-source-health-monitor.php';

echo "🟢 TDD GREEN PHASE: Running tests that should PASS now...\n\n";

$passed = 0;
$total = 10;

// Test 1: Class should exist
if (class_exists('TC_Data_Source_Health_Monitor')) {
    echo "✅ PASS - TC_Data_Source_Health_Monitor class exists - Class exists\n";
    $passed++;
} else {
    echo "❌ FAIL - TC_Data_Source_Health_Monitor class exists - Class does not exist\n";
}

// Test 2: Singleton pattern
try {
    $instance1 = TC_Data_Source_Health_Monitor::get_instance();
    $instance2 = TC_Data_Source_Health_Monitor::get_instance();
    
    if ($instance1 === $instance2) {
        echo "✅ PASS - Singleton pattern implementation - Singleton working correctly\n";
        $passed++;
    } else {
        echo "❌ FAIL - Singleton pattern implementation - Multiple instances created\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL - Singleton pattern implementation - Exception: " . $e->getMessage() . "\n";
}

// Test 3: Register data source
try {
    $monitor = TC_Data_Source_Health_Monitor::get_instance();
    $result = $monitor->register_source('https://api.example.com/data.json', [
        'check_interval' => 300,
        'timeout' => 10,
        'expected_keys' => ['data', 'status']
    ]);
    
    if ($result === true) {
        echo "✅ PASS - Register data source for monitoring - Data source registered successfully\n";
        $passed++;
    } else {
        echo "❌ FAIL - Register data source for monitoring - Failed to register data source\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL - Register data source for monitoring - Exception: " . $e->getMessage() . "\n";
}

// Test 4: Check source health
try {
    $monitor = TC_Data_Source_Health_Monitor::get_instance();
    $health_result = $monitor->check_health('https://httpbin.org/json');
    
    if (is_array($health_result) && 
        isset($health_result['status']) && 
        isset($health_result['response_time']) &&
        isset($health_result['timestamp'])) {
        echo "✅ PASS - Check data source health - Health check returned proper format\n";
        $passed++;
    } else {
        echo "❌ FAIL - Check data source health - Invalid health check response format\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL - Check data source health - Exception: " . $e->getMessage() . "\n";
}

// Test 5: Get health status
try {
    $monitor = TC_Data_Source_Health_Monitor::get_instance();
    $all_status = $monitor->get_all_health_status();
    
    if (is_array($all_status)) {
        echo "✅ PASS - Get health status for all sources - Health status retrieved as array\n";
        $passed++;
    } else {
        echo "❌ FAIL - Get health status for all sources - Invalid health status format\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL - Get health status for all sources - Exception: " . $e->getMessage() . "\n";
}

// Test 6: Health history tracking
try {
    $monitor = TC_Data_Source_Health_Monitor::get_instance();
    $history = $monitor->get_health_history('https://httpbin.org/json', 24);
    
    if (is_array($history)) {
        echo "✅ PASS - Health history tracking - Health history retrieved\n";
        $passed++;
    } else {
        echo "❌ FAIL - Health history tracking - Invalid health history format\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL - Health history tracking - Exception: " . $e->getMessage() . "\n";
}

// Test 7: Notification system
try {
    $monitor = TC_Data_Source_Health_Monitor::get_instance();
    $notification_config = [
        'email' => ['admin@example.com'],
        'webhook' => 'https://hooks.slack.com/...',
        'threshold' => 2
    ];
    
    $result = $monitor->configure_notifications($notification_config);
    
    if ($result === true) {
        echo "✅ PASS - Notification system for failures - Notification configuration accepted\n";
        $passed++;
    } else {
        echo "❌ FAIL - Notification system for failures - Failed to configure notifications\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL - Notification system for failures - Exception: " . $e->getMessage() . "\n";
}

// Test 8: Webhook integration
try {
    $monitor = TC_Data_Source_Health_Monitor::get_instance();
    $webhook_result = $monitor->send_webhook_alert([
        'source_url' => 'https://api.example.com/data.json',
        'status' => 'failed',
        'error' => 'Connection timeout',
        'timestamp' => time()
    ]);
    
    if (is_bool($webhook_result)) {
        echo "✅ PASS - Webhook integration for alerts - Webhook alert method exists\n";
        $passed++;
    } else {
        echo "❌ FAIL - Webhook integration for alerts - Invalid webhook response\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL - Webhook integration for alerts - Exception: " . $e->getMessage() . "\n";
}

// Test 9: Admin dashboard data
try {
    $monitor = TC_Data_Source_Health_Monitor::get_instance();
    $dashboard_data = $monitor->get_dashboard_data();
    
    if (is_array($dashboard_data) && 
        isset($dashboard_data['summary']) &&
        isset($dashboard_data['sources']) &&
        isset($dashboard_data['recent_failures'])) {
        echo "✅ PASS - Admin dashboard data - Dashboard data has correct structure\n";
        $passed++;
    } else {
        echo "❌ FAIL - Admin dashboard data - Invalid dashboard data format\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL - Admin dashboard data - Exception: " . $e->getMessage() . "\n";
}

// Test 10: Cron job scheduling
try {
    $monitor = TC_Data_Source_Health_Monitor::get_instance();
    $cron_result = $monitor->schedule_health_checks();
    
    if ($cron_result === true) {
        echo "✅ PASS - Cron job scheduling - Cron jobs scheduled successfully\n";
        $passed++;
    } else {
        echo "❌ FAIL - Cron job scheduling - Failed to schedule cron jobs\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL - Cron job scheduling - Exception: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "🟢 TDD GREEN PHASE RESULTS\n";
echo str_repeat('=', 70) . "\n";
echo "Total Tests: $total\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: " . ($total - $passed) . "\n";
echo "Success Rate: " . round(($passed / $total) * 100, 1) . "%\n\n";

if ($passed === $total) {
    echo "🎉 GREEN PHASE COMPLETE! All tests now pass with minimal implementation.\n";
    echo "🔄 Next: REFACTOR phase - Improve code while keeping tests green.\n";
} else {
    echo "⚠️  Some tests still failing. Need to fix minimal implementation.\n";
}

echo "\n" . str_repeat('=', 70) . "\n";