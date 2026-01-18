<?php
/**
 * Quick Test for Elementor Activation Fatal Error Fix
 * 
 * This simulates the exact scenario that was causing fatal errors:
 * Loading the widget file when Elementor classes don't exist.
 */

echo "Testing Elementor Widget File Loading Without Elementor Classes...\n";

// Simulate the environment where TableCrafter is active but Elementor is being activated
// In this case, Elementor's classes are not yet available

// Test 1: Include the widget file directly (this should not cause fatal errors)
echo "Test 1: Including widget file without Elementor classes...\n";
ob_start();
$error_before = error_get_last();

try {
    require_once __DIR__ . '/includes/class-tc-elementor-widget.php';
    echo "✅ SUCCESS: Widget file included without fatal errors\n";
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    exit(1);
}

$output = ob_get_clean();
$error_after = error_get_last();

// Check for new errors
if ($error_before !== $error_after && $error_after) {
    echo "❌ PHP ERROR DETECTED: " . $error_after['message'] . "\n";
    exit(1);
}

// Test 2: Check that the class is NOT defined when Elementor is missing
echo "\nTest 2: Verifying conditional class definition...\n";
if (class_exists('TC_Elementor_Widget')) {
    echo "❌ ERROR: TC_Elementor_Widget should not be defined without Elementor\n";
    exit(1);
} else {
    echo "✅ SUCCESS: TC_Elementor_Widget properly not defined without Elementor\n";
}

// Test 3: Check that registration function exists and handles missing classes gracefully
echo "\nTest 3: Testing registration function safety...\n";
if (function_exists('register_tc_elementor_widget')) {
    ob_start();
    $error_before_reg = error_get_last();
    
    try {
        register_tc_elementor_widget();
        echo "✅ SUCCESS: Registration function executed without errors\n";
    } catch (Error $e) {
        echo "❌ FATAL ERROR in registration: " . $e->getMessage() . "\n";
        exit(1);
    } catch (Exception $e) {
        echo "❌ EXCEPTION in registration: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    $output_reg = ob_get_clean();
    $error_after_reg = error_get_last();
    
    // Check for new errors in registration
    if ($error_before_reg !== $error_after_reg && $error_after_reg) {
        echo "❌ PHP ERROR in registration: " . $error_after_reg['message'] . "\n";
        exit(1);
    }
} else {
    echo "❌ ERROR: register_tc_elementor_widget function not found\n";
    exit(1);
}

echo "\n🎉 ALL TESTS PASSED! The Elementor activation fatal error fix is working correctly.\n";
echo "\nSummary:\n";
echo "- Widget file can be included safely without Elementor\n";
echo "- Class definition is conditional and safe\n"; 
echo "- Registration functions handle missing dependencies gracefully\n";
echo "- No fatal errors, exceptions, or PHP errors generated\n";

echo "\n✅ CRITICAL FIX VERIFIED: TableCrafter will no longer cause fatal errors when Elementor is activated after TableCrafter.\n";
?>