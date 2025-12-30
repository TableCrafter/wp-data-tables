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
