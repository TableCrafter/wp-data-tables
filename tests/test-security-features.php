<?php
/**
 * Security Test Suite for TableCrafter v2.8.0
 * 
 * Comprehensive security tests covering SSRF protection, rate limiting,
 * input validation, and XSS prevention.
 */

class TableCrafterSecurityTest extends WP_UnitTestCase
{
    private $tablecrafter;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->tablecrafter = TableCrafter::get_instance();
    }
    
    /**
     * Test SSRF (Server Side Request Forgery) Protection
     */
    public function test_ssrf_protection()
    {
        $reflection = new ReflectionClass($this->tablecrafter);
        $safe_url_method = $reflection->getMethod('is_safe_url');
        $safe_url_method->setAccessible(true);
        
        // Test blocked URLs
        $blocked_urls = [
            'http://localhost/sensitive',
            'http://127.0.0.1/admin',
            'http://[::1]/internal',
            'http://10.0.0.1/private',
            'http://192.168.1.1/router',
            'http://172.16.0.1/network',
            'file:///etc/passwd',
            'ftp://internal.server/data'
        ];
        
        foreach ($blocked_urls as $url) {
            $is_safe = $safe_url_method->invoke($this->tablecrafter, $url);
            $this->assertFalse($is_safe, "URL should be blocked: {$url}");
        }
        
        // Test allowed URLs
        $allowed_urls = [
            'https://api.github.com/repos/user/repo',
            'https://jsonplaceholder.typicode.com/posts',
            'https://httpbin.org/json',
            'https://raw.githubusercontent.com/user/repo/main/data.json'
        ];
        
        foreach ($allowed_urls as $url) {
            $is_safe = $safe_url_method->invoke($this->tablecrafter, $url);
            $this->assertTrue($is_safe, "URL should be allowed: {$url}");
        }
    }
    
    /**
     * Test Directory Traversal Protection
     */
    public function test_directory_traversal_protection()
    {
        $reflection = new ReflectionClass($this->tablecrafter);
        $fetch_method = $reflection->getMethod('fetch_data_from_source');
        $fetch_method->setAccessible(true);
        
        // Test various directory traversal attempts
        $malicious_paths = [
            '../../../etc/passwd',
            '..\\..\\..\\windows\\system32\\config\\sam',
            './../../../../var/log/apache2/access.log',
            '/etc/shadow',
            '\\windows\\system.ini',
            'file:///etc/passwd',
            '../wp-config.php',
            '../../.htaccess'
        ];
        
        foreach ($malicious_paths as $path) {
            $result = $fetch_method->invoke($this->tablecrafter, $path);
            
            // Should return WP_Error or empty result, not actual file contents
            $this->assertTrue(
                is_wp_error($result) || empty($result),
                "Directory traversal should be blocked for: {$path}"
            );
        }
    }
    
    /**
     * Test Rate Limiting Functionality
     */
    public function test_rate_limiting_functionality()
    {
        $reflection = new ReflectionClass($this->tablecrafter);
        $rate_limit_method = $reflection->getMethod('is_rate_limited');
        $rate_limit_method->setAccessible(true);
        
        // Clear any existing rate limit
        $identifier = get_current_user_id() ?: 'test_ip';
        $transient_key = 'tc_rate_' . md5((string) $identifier);
        delete_transient($transient_key);
        
        // First few requests should be allowed
        for ($i = 0; $i < 5; $i++) {
            $is_limited = $rate_limit_method->invoke($this->tablecrafter);
            $this->assertFalse($is_limited, "Request {$i} should not be rate limited");
        }
        
        // Simulate hitting the rate limit by setting high count
        set_transient($transient_key, 35, 60); // Over the 30 limit
        
        $is_limited = $rate_limit_method->invoke($this->tablecrafter);
        $this->assertTrue($is_limited, "Should be rate limited after exceeding threshold");
        
        // Clean up
        delete_transient($transient_key);
    }
    
    /**
     * Test Rate Limiting Headers and Response
     */
    public function test_rate_limit_ajax_response()
    {
        // Create a test user and set up the scenario
        $user_id = $this->factory->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        
        // Set up POST data for AJAX request
        $_POST['url'] = 'https://jsonplaceholder.typicode.com/posts';
        $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
        
        // Simulate rate limit exceeded
        $identifier = get_current_user_id();
        $transient_key = 'tc_rate_' . md5((string) $identifier);
        set_transient($transient_key, 35, 60); // Over limit
        
        // Capture the AJAX response
        ob_start();
        
        try {
            $this->tablecrafter->ajax_proxy_fetch();
        } catch (WPAjaxDieStopException $e) {
            // Expected when wp_send_json_error is called
        }
        
        $output = ob_get_clean();
        
        // Should return error response
        $response = json_decode($output, true);
        if ($response) {
            $this->assertFalse($response['success']);
            $this->assertStringContains('rate limit', strtolower($response['data']));
        }
        
        // Clean up
        delete_transient($transient_key);
        unset($_POST['url'], $_POST['nonce']);
        wp_set_current_user(0);
    }
    
    /**
     * Test Input Sanitization and Validation
     */
    public function test_input_sanitization()
    {
        $malicious_inputs = [
            'source' => 'javascript:alert("xss")',
            'include' => '<script>alert("xss")</script>',
            'exclude' => 'DROP TABLE wp_users; --',
            'root' => '../../../etc/passwd',
            'sort' => 'name:asc; DROP TABLE wp_posts; --'
        ];
        
        $html = $this->tablecrafter->render_table($malicious_inputs);
        
        // Should not contain malicious content
        $this->assertStringNotContains('<script>', $html);
        $this->assertStringNotContains('javascript:', $html);
        $this->assertStringNotContains('DROP TABLE', $html);
        
        // Should sanitize attributes properly
        $this->assertStringNotContains('"javascript:alert', $html);
        
        // Should show error for invalid source
        $this->assertStringContains('Error: TableCrafter requires a "source" attribute', $html);
    }
    
    /**
     * Test XSS Prevention in Data Rendering
     */
    public function test_xss_prevention_data_rendering()
    {
        $malicious_data = [
            [
                'name' => '<script>alert("xss")</script>',
                'email' => 'user@test.com',
                'bio' => '<img src="x" onerror="alert(\'xss\')">'
            ],
            [
                'name' => 'javascript:alert("test")',
                'email' => '<svg onload="alert(1)">',
                'bio' => 'Normal text'
            ]
        ];
        
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_xss_test') . '.json';
        file_put_contents($temp_file, json_encode($malicious_data));
        
        $atts = ['source' => $temp_file];
        $html = $this->tablecrafter->render_table($atts);
        
        // Should escape malicious content
        $this->assertStringNotContains('<script>', $html);
        $this->assertStringNotContains('onerror=', $html);
        $this->assertStringNotContains('onload=', $html);
        $this->assertStringNotContains('javascript:', $html);
        
        // Should contain escaped content
        $this->assertStringContains('&lt;script&gt;', $html);
        $this->assertStringContains('&lt;img', $html);
        
        unlink($temp_file);
    }
    
    /**
     * Test SQL Injection Prevention (though TableCrafter doesn't use SQL directly)
     */
    public function test_sql_injection_prevention()
    {
        $sql_injection_attempts = [
            'source' => "'; DROP TABLE wp_users; --",
            'include' => "name'; DELETE FROM wp_options WHERE option_name LIKE '%'; --",
            'exclude' => "id' UNION SELECT user_login, user_pass FROM wp_users; --"
        ];
        
        $html = $this->tablecrafter->render_table($sql_injection_attempts);
        
        // Should not process SQL commands
        $this->assertStringNotContains('DROP TABLE', $html);
        $this->assertStringNotContains('DELETE FROM', $html);
        $this->assertStringNotContains('UNION SELECT', $html);
        
        // Should show appropriate error
        $this->assertStringContains('Error: TableCrafter requires a "source" attribute', $html);
    }
    
    /**
     * Test File Upload Security (CSV files)
     */
    public function test_file_upload_security()
    {
        // Test malicious file extensions
        $malicious_files = [
            'malicious.php' => '<?php echo "hacked"; ?>',
            'script.js' => 'alert("xss");',
            'shell.sh' => '#!/bin/bash\nrm -rf /',
            'config.ini' => '[database]\npassword=secret'
        ];
        
        foreach ($malicious_files as $filename => $content) {
            $temp_file = sys_get_temp_dir() . '/' . $filename;
            file_put_contents($temp_file, $content);
            
            $reflection = new ReflectionClass($this->tablecrafter);
            $method = $reflection->getMethod('fetch_data_from_source');
            $method->setAccessible(true);
            
            $result = $method->invoke($this->tablecrafter, $temp_file);
            
            // Should not process non-JSON/CSV files
            $this->assertTrue(
                is_wp_error($result) || empty($result),
                "Malicious file should be rejected: {$filename}"
            );
            
            unlink($temp_file);
        }
    }
    
    /**
     * Test Nonce Validation
     */
    public function test_nonce_validation()
    {
        $user_id = $this->factory->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        
        // Test invalid nonce
        $_POST['url'] = 'https://jsonplaceholder.typicode.com/posts';
        $_POST['nonce'] = 'invalid_nonce';
        
        ob_start();
        
        try {
            $this->tablecrafter->ajax_proxy_fetch();
            $this->fail('Should have failed with invalid nonce');
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        ob_end_clean();
        
        // Test missing nonce
        unset($_POST['nonce']);
        
        ob_start();
        
        try {
            $this->tablecrafter->ajax_proxy_fetch();
            $this->fail('Should have failed with missing nonce');
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        ob_end_clean();
        
        // Clean up
        unset($_POST['url']);
        wp_set_current_user(0);
    }
    
    /**
     * Test Authorization Requirements
     */
    public function test_authorization_requirements()
    {
        // Test with no user logged in
        wp_set_current_user(0);
        
        $_POST['url'] = 'https://jsonplaceholder.typicode.com/posts';
        $_POST['nonce'] = wp_create_nonce('tc_proxy_nonce');
        
        ob_start();
        
        try {
            $this->tablecrafter->ajax_proxy_fetch();
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        if ($response) {
            $this->assertFalse($response['success']);
            $this->assertStringContains('Unauthorized', $response['data']);
        }
        
        // Test with subscriber role (insufficient permissions)
        $subscriber_id = $this->factory->user->create(['role' => 'subscriber']);
        wp_set_current_user($subscriber_id);
        
        ob_start();
        
        try {
            $this->tablecrafter->ajax_proxy_fetch();
        } catch (WPAjaxDieStopException $e) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        if ($response) {
            $this->assertFalse($response['success']);
        }
        
        // Clean up
        unset($_POST['url'], $_POST['nonce']);
        wp_set_current_user(0);
    }
    
    /**
     * Test Content Security Policy Compliance
     */
    public function test_csp_compliance()
    {
        $test_data = [
            ['name' => 'Test User', 'script' => '<script>alert("test")</script>'],
            ['name' => 'Another User', 'image' => '<img src="javascript:alert(1)">']
        ];
        
        $temp_file = tempnam(sys_get_temp_dir(), 'tc_csp_test') . '.json';
        file_put_contents($temp_file, json_encode($test_data));
        
        $html = $this->tablecrafter->render_table(['source' => $temp_file]);
        
        // Should not contain inline JavaScript
        $this->assertStringNotContains('javascript:', $html);
        $this->assertStringNotContains('onclick=', $html);
        $this->assertStringNotContains('onload=', $html);
        $this->assertStringNotContains('<script>', $html);
        
        // All JavaScript should be in external files or properly escaped
        if (preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $html, $matches)) {
            foreach ($matches[1] as $script_content) {
                // Only allow JSON data scripts
                if (!empty(trim($script_content))) {
                    $this->assertTrue(
                        json_decode($script_content) !== null,
                        'Inline scripts should only contain JSON data'
                    );
                }
            }
        }
        
        unlink($temp_file);
    }
    
    /**
     * Test HTTP Header Injection Prevention
     */
    public function test_http_header_injection_prevention()
    {
        $malicious_urls = [
            "https://example.com\r\nX-Injected-Header: malicious",
            "https://example.com\nLocation: https://attacker.com",
            "https://example.com\r\n\r\n<script>alert('xss')</script>"
        ];
        
        $reflection = new ReflectionClass($this->tablecrafter);
        $safe_url_method = $reflection->getMethod('is_safe_url');
        $safe_url_method->setAccessible(true);
        
        foreach ($malicious_urls as $url) {
            $is_safe = $safe_url_method->invoke($this->tablecrafter, $url);
            $this->assertFalse($is_safe, "Header injection attempt should be blocked: {$url}");
        }
    }
    
    /**
     * Test Error Message Information Disclosure
     */
    public function test_error_message_information_disclosure()
    {
        // Test as non-admin user
        $user_id = $this->factory->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
        
        $atts = ['source' => 'https://nonexistent-domain-12345.com/data.json'];
        $html = $this->tablecrafter->render_table($atts);
        
        // Should not reveal detailed error information to non-admin users
        $this->assertStringNotContains('CURL Error:', $html);
        $this->assertStringNotContains('HTTP 404', $html);
        $this->assertStringNotContains('DNS', $html);
        
        // Should show generic error message
        $this->assertStringContains('Unable to load data', $html);
        
        // Test as admin user - should see detailed errors
        $admin_id = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($admin_id);
        
        $admin_html = $this->tablecrafter->render_table($atts);
        
        // Admin should see more detailed error information
        $this->assertTrue(
            strpos($admin_html, 'TableCrafter Setup Guide') !== false ||
            strpos($admin_html, 'Unable to load data') !== false
        );
        
        wp_set_current_user(0);
    }
    
    /**
     * Test Cache Poisoning Prevention
     */
    public function test_cache_poisoning_prevention()
    {
        $legitimate_url = TABLECRAFTER_URL . 'demo-data/users.json';
        $cache_key = 'tc_cache_' . md5($legitimate_url);
        
        // Set malicious cached data
        $malicious_data = [
            ['name' => '<script>alert("cached_xss")</script>', 'email' => 'test@test.com']
        ];
        set_transient($cache_key, $malicious_data, HOUR_IN_SECONDS);
        
        $html = $this->tablecrafter->render_table(['source' => $legitimate_url]);
        
        // Even cached malicious data should be escaped
        $this->assertStringNotContains('<script>alert', $html);
        $this->assertStringContains('&lt;script&gt;', $html);
        
        // Clean up
        delete_transient($cache_key);
    }
}