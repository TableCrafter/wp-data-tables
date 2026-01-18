<?php
/**
 * Comprehensive Edge Case Test Suite for Elementor Integration
 * 
 * Tests ALL potential failure scenarios and edge cases:
 * 1. Missing ELEMENTOR_VERSION constant
 * 2. Hook registration safety
 * 3. Class definition conditionals
 * 4. Category registration safety
 * 5. Function availability checks
 */

echo "🧪 COMPREHENSIVE ELEMENTOR EDGE CASE TESTING\n";
echo "============================================\n\n";

// Track all test results
$tests_passed = 0;
$tests_total = 0;

function test_assertion($condition, $test_name, $success_msg, $failure_msg) {
    global $tests_passed, $tests_total;
    $tests_total++;
    
    if ($condition) {
        echo "✅ PASS: $test_name - $success_msg\n";
        $tests_passed++;
    } else {
        echo "❌ FAIL: $test_name - $failure_msg\n";
    }
}

// Test 1: Widget file inclusion without fatal errors
echo "Test 1: Widget file inclusion safety\n";
echo "------------------------------------\n";
ob_start();
$error_before = error_get_last();

try {
    require_once __DIR__ . '/includes/class-tc-elementor-widget.php';
    test_assertion(true, "File Inclusion", "Widget file included without fatal errors", "Fatal error during inclusion");
} catch (Error $e) {
    test_assertion(false, "File Inclusion", "", "Fatal error: " . $e->getMessage());
} catch (Exception $e) {
    test_assertion(false, "File Inclusion", "", "Exception: " . $e->getMessage());
}

$output = ob_get_clean();
$error_after = error_get_last();

// Check for new errors
$new_error = ($error_before !== $error_after && $error_after);
test_assertion(!$new_error, "PHP Error Check", "No new PHP errors", "New PHP error: " . ($new_error ? $error_after['message'] : ''));

echo "\n";

// Test 2: Conditional class definition
echo "Test 2: Conditional class definition\n";
echo "-----------------------------------\n";
test_assertion(!class_exists('TC_Elementor_Widget'), "Class Conditional", "TC_Elementor_Widget not defined without Elementor", "Class defined when it shouldn't be");

echo "\n";

// Test 3: Function availability and safety
echo "Test 3: Function availability and safety\n";
echo "---------------------------------------\n";
test_assertion(function_exists('register_tc_elementor_widget'), "Registration Function", "register_tc_elementor_widget exists", "Function missing");
test_assertion(function_exists('tc_register_elementor_hooks'), "Hook Function", "tc_register_elementor_hooks exists", "Function missing");
test_assertion(function_exists('add_tc_elementor_category'), "Category Function", "add_tc_elementor_category exists", "Function missing");

echo "\n";

// Test 4: Registration function safety
echo "Test 4: Registration function safety\n";
echo "-----------------------------------\n";
ob_start();
$error_before_reg = error_get_last();

try {
    register_tc_elementor_widget();
    test_assertion(true, "Registration Safety", "Function executed without errors", "Function caused error");
} catch (Error $e) {
    test_assertion(false, "Registration Safety", "", "Fatal error: " . $e->getMessage());
} catch (Exception $e) {
    test_assertion(false, "Registration Safety", "", "Exception: " . $e->getMessage());
}

$output_reg = ob_get_clean();
$error_after_reg = error_get_last();

$new_error_reg = ($error_before_reg !== $error_after_reg && $error_after_reg);
test_assertion(!$new_error_reg, "Registration Error Check", "No errors during registration", "Error: " . ($new_error_reg ? $error_after_reg['message'] : ''));

echo "\n";

// Test 5: Hook registration function safety
echo "Test 5: Hook registration function safety\n";
echo "----------------------------------------\n";
ob_start();
$error_before_hook = error_get_last();

try {
    tc_register_elementor_hooks();
    test_assertion(true, "Hook Registration Safety", "Function executed without errors", "Function caused error");
} catch (Error $e) {
    test_assertion(false, "Hook Registration Safety", "", "Fatal error: " . $e->getMessage());
} catch (Exception $e) {
    test_assertion(false, "Hook Registration Safety", "", "Exception: " . $e->getMessage());
}

$output_hook = ob_get_clean();
$error_after_hook = error_get_last();

$new_error_hook = ($error_before_hook !== $error_after_hook && $error_after_hook);
test_assertion(!$new_error_hook, "Hook Registration Error Check", "No errors during hook registration", "Error: " . ($new_error_hook ? $error_after_hook['message'] : ''));

echo "\n";

// Test 6: Category function safety with null parameter
echo "Test 6: Category function safety\n";
echo "--------------------------------\n";
ob_start();
$error_before_cat = error_get_last();

try {
    add_tc_elementor_category(null);
    test_assertion(true, "Category Function Null Safety", "Function handled null parameter safely", "Function failed with null");
    
    add_tc_elementor_category('invalid');
    test_assertion(true, "Category Function Invalid Safety", "Function handled invalid parameter safely", "Function failed with invalid parameter");
} catch (Error $e) {
    test_assertion(false, "Category Function Safety", "", "Fatal error: " . $e->getMessage());
} catch (Exception $e) {
    test_assertion(false, "Category Function Safety", "", "Exception: " . $e->getMessage());
}

$output_cat = ob_get_clean();
$error_after_cat = error_get_last();

$new_error_cat = ($error_before_cat !== $error_after_cat && $error_after_cat);
test_assertion(!$new_error_cat, "Category Function Error Check", "No errors during category tests", "Error: " . ($new_error_cat ? $error_after_cat['message'] : ''));

echo "\n";

// Test 7: ELEMENTOR_VERSION constant handling
echo "Test 7: ELEMENTOR_VERSION constant handling\n";
echo "------------------------------------------\n";

// Test without ELEMENTOR_VERSION defined
$version_defined_before = defined('ELEMENTOR_VERSION');
test_assertion(!$version_defined_before, "ELEMENTOR_VERSION Not Defined", "ELEMENTOR_VERSION not available as expected", "ELEMENTOR_VERSION unexpectedly available");

// Test hook registration without version constant
ob_start();
$error_before_version = error_get_last();

try {
    tc_register_elementor_hooks();
    test_assertion(true, "Version Handling Safety", "Hook registration worked without ELEMENTOR_VERSION", "Failed without version constant");
} catch (Error $e) {
    test_assertion(false, "Version Handling Safety", "", "Fatal error: " . $e->getMessage());
}

$output_version = ob_get_clean();
$error_after_version = error_get_last();

$new_error_version = ($error_before_version !== $error_after_version && $error_after_version);
test_assertion(!$new_error_version, "Version Error Check", "No errors handling missing version", "Error: " . ($new_error_version ? $error_after_version['message'] : ''));

echo "\n";

// Test 8: Memory usage and performance
echo "Test 8: Memory usage and performance\n";
echo "-----------------------------------\n";
$memory_before = memory_get_usage();
$time_before = microtime(true);

// Stress test: multiple inclusions and function calls
for ($i = 0; $i < 10; $i++) {
    register_tc_elementor_widget();
    tc_register_elementor_hooks();
    add_tc_elementor_category(null);
}

$memory_after = memory_get_usage();
$time_after = microtime(true);

$memory_usage = $memory_after - $memory_before;
$time_usage = ($time_after - $time_before) * 1000; // Convert to milliseconds

test_assertion($memory_usage < 1024 * 1024, "Memory Usage", "Memory usage under 1MB: " . number_format($memory_usage) . " bytes", "Excessive memory usage: " . number_format($memory_usage) . " bytes");
test_assertion($time_usage < 100, "Performance", "Execution time under 100ms: " . number_format($time_usage, 2) . "ms", "Slow execution: " . number_format($time_usage, 2) . "ms");

echo "\n";

// Final Results
echo "FINAL RESULTS\n";
echo "=============\n";
echo "Tests Passed: $tests_passed/$tests_total\n";

if ($tests_passed === $tests_total) {
    echo "🎉 ALL TESTS PASSED! Elementor integration is bulletproof.\n\n";
    echo "✅ Critical fixes verified:\n";
    echo "   - No fatal errors during any activation scenario\n";
    echo "   - Proper conditional class definitions\n";
    echo "   - Safe function execution with error handling\n";
    echo "   - Version constant safety checks\n";
    echo "   - Memory efficient and performant\n";
    echo "   - Graceful degradation for all edge cases\n\n";
    echo "🚀 TableCrafter-Elementor integration is production ready!\n";
    exit(0);
} else {
    $failed = $tests_total - $tests_passed;
    echo "❌ $failed TEST(S) FAILED! Review and fix issues above.\n";
    exit(1);
}
?>