<?php
/**
 * TableCrafter Automated Test Suite
 * 
 * Run via: php tests/run-tests.php
 */

class TableCrafterTests {
    private $results = [];

    public function run() {
        echo "🚀 Starting TableCrafter Automated Tests...\n\n";

        $this->test_shortcode_parsing();
        $this->test_data_sanitization();
        $this->test_column_attributes();
        $this->test_security_escaping();
        $this->test_ssr_engine();
        $this->test_json_root_path();
        $this->test_block_registration();
        $this->test_live_search_logic();
        $this->test_pagination_logic();
        $this->test_security_hardening();
        $this->test_sorting_logic();
        $this->test_mobile_reflow_logic();
        $this->test_export_logic();
        $this->test_formatting_logic();
        $this->test_column_aliasing();
        $this->test_nested_data_logic();
        $this->test_version_consistency();
        $this->test_directory_structure();

        $this->report();
    }

    private function test_shortcode_parsing() {
        $test_name = "Shortcode Registration Check";
        $content = file_get_contents(__DIR__ . '/../tablecrafter.php');
        if (strpos($content, "add_shortcode('tablecrafter'") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "Shortcode [tablecrafter] not found in registration.");
        }
    }

    private function test_data_sanitization() {
        $test_name = "URL Sanitization Check";
        $content = file_get_contents(__DIR__ . '/../tablecrafter.php');
        if (strpos($content, "esc_url_raw") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "esc_url_raw not used for source attribute.");
        }
    }

    private function test_column_attributes() {
        $test_name = "Column Attribute Check (Include/Exclude)";
        $content = file_get_contents(__DIR__ . '/../tablecrafter.php');
        if (strpos($content, "'include'") !== false && strpos($content, "'exclude'") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "Shortcode lacks 'include' or 'exclude' support.");
        }
    }

    private function test_security_escaping() {
        $test_name = "Security Escaping (JS)";
        $content = file_get_contents(__DIR__ . '/../assets/js/tablecrafter.js');
        if (strpos($content, "escapeHTML") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "No escapeHTML function found in JS library.");
        }
    }

    private function test_ssr_engine() {
        $test_name = "SSR Engine Check (PHP)";
        $content = file_get_contents(__DIR__ . '/../tablecrafter.php');
        if (strpos($content, "fetch_and_render_php") !== false && strpos($content, "data-ssr=\"true\"") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "SSR Engine methods or data attributes missing.");
        }
    }

    private function test_json_root_path() {
        $test_name = "JSON Root Path Attribute Check";
        $content = file_get_contents(__DIR__ . '/../tablecrafter.php');
        if (strpos($content, "'root'") !== false && strpos($content, "['root']") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "Logic for 'root' attribute not found in shortcode handler.");
        }
    }

    private function test_block_registration() {
        $test_name = "Block Editor Registration Check";
        $content = file_get_contents(__DIR__ . '/../tablecrafter.php');
        if (strpos($content, "register_block_type") !== false || strpos($content, "init_block") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "No block registration logic found in the main plugin file.");
        }
    }

    private function test_live_search_logic() {
        $test_name = "Live Search Attribute Check";
        $content = file_get_contents(__DIR__ . '/../tablecrafter.php');
        if (strpos($content, "'search'") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "Logic for 'search' attribute not found in shortcode handler.");
        }
    }

    private function test_pagination_logic() {
        $test_name = "Pagination Attribute Check";
        $content = file_get_contents(__DIR__ . '/../tablecrafter.php');
        if (strpos($content, "'per_page'") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "Logic for 'per_page' attribute not found in shortcode handler.");
        }
    }

    private function test_security_hardening() {
        $test_name = "Security Hardening (SSRF & Auth)";
        $content = file_get_contents(__DIR__ . '/../tablecrafter.php');
        
        $has_safe_url = strpos($content, "is_safe_url") !== false;
        $has_cap_check = strpos($content, "current_user_can") !== false;
        
        if ($has_safe_url && $has_cap_check) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "Missing is_safe_url helper or current_user_can authorization checks.");
        }
    }

    private function test_sorting_logic() {
        $test_name = "Column Sorting Logic Check";
        $js_content = file_get_contents(__DIR__ . '/../assets/js/tablecrafter.js');
        if (strpos($js_content, "sortDirection") !== false || strpos($js_content, "sortData") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "Interactive sorting logic not found in tablecrafter.js.");
        }
    }

    private function test_mobile_reflow_logic() {
        $test_name = "Mobile Reflow Attribute Check";
        $js_content = file_get_contents(__DIR__ . '/../assets/js/tablecrafter.js');
        if (strpos($js_content, "data-tc-label") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "Mobile reflow labels (data-tc-label) not found in tablecrafter.js.");
        }
    }

    private function test_export_logic() {
        $test_name = "Export Logic Check";
        $js_content = file_get_contents(__DIR__ . '/../assets/js/tablecrafter.js');
        if (strpos($js_content, "exportCSV") !== false || strpos($js_content, "tc-export-btn") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "Export logic (exportCSV/tc-export-btn) not found in tablecrafter.js.");
        }
    }

    private function test_formatting_logic() {
        $test_name = "Smart Formatting Logic Check";
        $js_content = file_get_contents(__DIR__ . '/../assets/js/tablecrafter.js');
        if (strpos($js_content, "tc-badge tc-yes") !== false && strpos($js_content, "mailto:") !== false) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "Smart formatting logic (Badges/Mailto) not found in tablecrafter.js.");
        }
    }

    private function test_column_aliasing() {
        $test_name = "Column Aliasing Logic Check";
        
        // Check PHP Implementation
        $php_content = file_get_contents(__DIR__ . '/../tablecrafter.php');
        $php_check = strpos($php_content, "explode(':', \$item, 2)") !== false;

        // Check JS Implementation
        $js_content = file_get_contents(__DIR__ . '/../assets/js/tablecrafter.js');
        $js_check = strpos($js_content, "this.getAliasedHeaders()") !== false;

        if ($php_check && $js_check) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "Aliasing logic missing in PHP or JS.");
        }
    }

    private function test_version_consistency() {
        $test_name = "Version Number Sync";
        $php = file_get_contents(__DIR__ . '/../tablecrafter.php');
        $txt = file_get_contents(__DIR__ . '/../readme.txt');
        
        preg_match("/Version: ([\d\.]+)/", $php, $php_v);
        preg_match("/Stable tag: ([\d\.]+)/", $txt, $txt_v);

        if ($php_v[1] === $txt_v[1]) {
            $this->pass($test_name . " (" . $php_v[1] . ")");
        } else {
            $this->fail($test_name, "Version mismatch: PHP({$php_v[1]}) vs Readme({$txt_v[1]})");
        }
    }

    private function test_nested_data_logic() {
        $test_name = "Nested Data (Array/Object) Rendering Logic";
        
        $php_content = file_get_contents(__DIR__ . '/../tablecrafter.php');
        $js_content = file_get_contents(__DIR__ . '/../assets/js/tablecrafter.js');

        $check_php = strpos($php_content, "tc-tag-list") !== false && strpos($php_content, "is_array(\$val)") !== false;
        $check_js = strpos($js_content, "tc-tag-list") !== false && strpos($js_content, "Array.isArray(val)") !== false;

        if ($check_php && $check_js) {
            $this->pass($test_name);
        } else {
            $this->fail($test_name, "Nested data (Array/Object) logic not found in PHP or JS engine.");
        }
    }

    private function test_directory_structure() {
        $test_name = "Critical Assets Check";
        $files = [
            'assets/js/tablecrafter.js',
            'assets/js/frontend.js',
            'assets/css/tablecrafter.css'
        ];

        foreach ($files as $file) {
            if (!file_exists(__DIR__ . '/../' . $file)) {
                $this->fail($test_name, "Missing file: $file");
                return;
            }
        }
        $this->pass($test_name);
    }

    private function pass($name) {
        $this->results[] = ['name' => $name, 'status' => 'PASS'];
        echo "✅ PASS: $name\n";
    }

    private function fail($name, $reason) {
        $this->results[] = ['name' => $name, 'status' => 'FAIL', 'reason' => $reason];
        echo "❌ FAIL: $name ($reason)\n";
    }

    private function report() {
        $passed = count(array_filter($this->results, fn($r) => $r['status'] === 'PASS'));
        $total = count($this->results);
        
        echo "\n-----------------------------------\n";
        echo "Summary: $passed/$total Tests Passed\n";
        echo "-----------------------------------\n";
        
        if ($passed < $total) {
            exit(1);
        }
    }
}

$tests = new TableCrafterTests();
$tests->run();
