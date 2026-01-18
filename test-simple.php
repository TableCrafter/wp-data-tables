<?php
// Simple test to check what's happening

echo "Testing basic conditionals...\n";

if (class_exists('\Elementor\Widget_Base')) {
    echo "Widget_Base exists - This shouldn't happen\n";
} else {
    echo "✅ Widget_Base doesn't exist - Correct\n";
}

echo "Testing function_exists...\n";

if (function_exists('add_action')) {
    echo "add_action exists - unexpected in CLI\n";
} else {
    echo "✅ add_action doesn't exist - Correct for CLI\n";
}

if (function_exists('did_action')) {
    echo "did_action exists - unexpected in CLI\n";
} else {
    echo "✅ did_action doesn't exist - Correct for CLI\n";
}

echo "✅ Basic conditionals working correctly\n";
?>