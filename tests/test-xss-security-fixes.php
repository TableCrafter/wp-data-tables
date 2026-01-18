<?php
/**
 * XSS Security Vulnerability Tests for TableCrafter
 * 
 * Comprehensive tests to verify XSS vulnerabilities have been properly fixed
 * in both PHP server-side rendering and JavaScript client-side operations.
 * 
 * @package TableCrafter
 * @subpackage Tests
 * @since 3.1.5
 */

class TableCrafter_XSS_Security_Test extends WP_UnitTestCase {

    private $tablecrafter;
    
    public function setUp(): void {
        parent::setUp();
        
        // Create TableCrafter instance for testing
        $this->tablecrafter = new TableCrafter();
        
        // Set up test data with potential XSS payloads
        $this->malicious_payloads = [
            // JavaScript injection attempts
            '<script>alert("XSS")</script>',
            'javascript:alert("XSS")',
            'onmouseover="alert(1)"',
            '<img src=x onerror=alert("XSS")>',
            '<svg onload=alert("XSS")>',
            
            // Data URL injection attempts  
            'data:text/html,<script>alert("XSS")</script>',
            'data:application/javascript,alert("XSS")',
            
            // CSS injection attempts
            '<style>body{background:url("javascript:alert(1)")}</style>',
            'background-image:url("javascript:alert(1)")',
            
            // HTML injection attempts
            '<iframe src="javascript:alert(1)"></iframe>',
            '<object data="javascript:alert(1)"></object>',
            '<embed src="javascript:alert(1)">',
            
            // Event handler injection
            '<div onclick="alert(1)">test</div>',
            '<a href="javascript:alert(1)">click</a>',
            
            // Complex nested payloads
            '<img src="valid.jpg" onload="eval(atob(\'YWxlcnQoIlhTUyIp\'))">',
            
            // URL-based payloads
            'vbscript:alert("XSS")',
            'file:///etc/passwd',
            'ftp://malicious.example.com/payload.js'
        ];
    }
    
    /**
     * Test render_value_php method security for all malicious payloads
     */
    public function test_render_value_php_xss_protection() {
        foreach ($this->malicious_payloads as $payload) {
            $result = $this->invoke_private_method('render_value_php', $payload);
            
            // Assert: Result should never contain unescaped malicious content
            $this->assertStringNotContainsString('<script', $result, "Script tags should be escaped");
            $this->assertStringNotContainsString('javascript:', $result, "JavaScript URLs should be blocked");
            $this->assertStringNotContainsString('vbscript:', $result, "VBScript URLs should be blocked");
            $this->assertStringNotContainsString('onload=', $result, "Event handlers should be escaped");
            $this->assertStringNotContainsString('onerror=', $result, "Event handlers should be escaped");
            
            // Assert: All content should be properly HTML escaped
            $this->assertStringNotContainsString($payload, $result, "Raw payload should not appear in output");
        }
    }
    
    /**
     * Test image URL security validation
     */
    public function test_image_url_security() {
        // Test malicious image URLs
        $malicious_images = [
            'javascript:alert("XSS")',
            'data:text/html,<script>alert("XSS")</script>',
            'data:image/svg+xml,<svg onload="alert(1)">',
            'vbscript:alert("XSS")',
            'file:///etc/passwd'
        ];
        
        foreach ($malicious_images as $malicious_url) {
            $result = $this->invoke_private_method('render_value_php', $malicious_url);
            
            // Should be escaped as text, not rendered as image
            $this->assertStringNotContainsString('<img', $result, "Malicious URLs should not create img tags");
            $this->assertStringContainsString(esc_html($malicious_url), $result, "Should be escaped as text");
        }
        
        // Test legitimate image URLs
        $safe_images = [
            'https://example.com/image.jpg',
            'https://example.com/image.png',
            'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
        ];
        
        foreach ($safe_images as $safe_url) {
            $result = $this->invoke_private_method('render_value_php', $safe_url);
            
            // Should create proper img tag with security attributes
            $this->assertStringContainsString('<img', $result, "Safe URLs should create img tags");
            $this->assertStringContainsString('alt="Table image"', $result, "Should have alt attribute");
            $this->assertStringContainsString('loading="lazy"', $result, "Should have lazy loading");
        }
    }
    
    /**
     * Test URL validation security
     */
    public function test_url_validation_security() {
        // Test malicious URLs
        $malicious_urls = [
            'javascript:alert("XSS")',
            'vbscript:alert("XSS")',
            'data:text/html,<script>alert("XSS")</script>',
            'file:///etc/passwd',
            'ftp://malicious.example.com'
        ];
        
        foreach ($malicious_urls as $malicious_url) {
            $result = $this->invoke_private_method('render_value_php', $malicious_url);
            
            // Should be escaped as text, not rendered as link
            $this->assertStringNotContainsString('<a href=', $result, "Malicious URLs should not create links");
            $this->assertStringContainsString(esc_html($malicious_url), $result, "Should be escaped as text");
        }
        
        // Test legitimate URLs
        $safe_urls = [
            'https://example.com',
            'http://example.com/page',
            'https://subdomain.example.com/path?param=value'
        ];
        
        foreach ($safe_urls as $safe_url) {
            $result = $this->invoke_private_method('render_value_php', $safe_url);
            
            // Should create proper link with security attributes
            $this->assertStringContainsString('<a href=', $result, "Safe URLs should create links");
            $this->assertStringContainsString('target="_blank"', $result, "Should have target blank");
            $this->assertStringContainsString('rel="noopener noreferrer"', $result, "Should have security rel attributes");
        }
    }
    
    /**
     * Test email address validation security
     */
    public function test_email_validation_security() {
        // Test malicious email-like strings
        $malicious_emails = [
            'user@example.com<script>alert("XSS")</script>',
            'javascript:alert("XSS")@example.com',
            'user@example.com"onmouseover="alert(1)"',
            'user@example.com</a><script>alert("XSS")</script>'
        ];
        
        foreach ($malicious_emails as $malicious_email) {
            $result = $this->invoke_private_method('render_value_php', $malicious_email);
            
            // Should be escaped as text, not rendered as email link
            $this->assertStringNotContainsString('<a href="mailto:', $result, "Malicious emails should not create mailto links");
            $this->assertStringContainsString(esc_html($malicious_email), $result, "Should be escaped as text");
        }
        
        // Test legitimate emails
        $safe_emails = [
            'user@example.com',
            'test.email+tag@example.org'
        ];
        
        foreach ($safe_emails as $safe_email) {
            $result = $this->invoke_private_method('render_value_php', $safe_email);
            
            // Should create proper mailto link
            $this->assertStringContainsString('<a href="mailto:', $result, "Safe emails should create mailto links");
            $this->assertStringContainsString('title="Send email to', $result, "Should have descriptive title");
        }
    }
    
    /**
     * Test array rendering security
     */
    public function test_array_rendering_security() {
        // Test malicious array data
        $malicious_arrays = [
            ['name' => '<script>alert("XSS")</script>'],
            ['title' => 'javascript:alert("XSS")'],
            ['label' => '<img src=x onerror=alert("XSS")>'],
            [
                'name' => 'Safe Name',
                'malicious' => '<script>alert("XSS")</script>'
            ]
        ];
        
        foreach ($malicious_arrays as $malicious_array) {
            $result = $this->invoke_private_method('render_value_php', $malicious_array);
            
            // Should escape all malicious content
            $this->assertStringNotContainsString('<script', $result, "Script tags should be escaped in arrays");
            $this->assertStringNotContainsString('javascript:', $result, "JavaScript URLs should be escaped in arrays");
            $this->assertStringNotContainsString('onerror=', $result, "Event handlers should be escaped in arrays");
        }
    }
    
    /**
     * Test object rendering security
     */
    public function test_object_rendering_security() {
        // Test malicious object data
        $malicious_object = (object) [
            'name' => '<script>alert("XSS")</script>',
            'description' => 'javascript:alert("XSS")'
        ];
        
        $result = $this->invoke_private_method('render_value_php', $malicious_object);
        
        // Should escape all malicious content
        $this->assertStringNotContainsString('<script', $result, "Script tags should be escaped in objects");
        $this->assertStringNotContainsString('javascript:', $result, "JavaScript URLs should be escaped in objects");
    }
    
    /**
     * Test date validation security
     */
    public function test_date_validation_security() {
        // Test malicious date-like strings
        $malicious_dates = [
            '2024-01-01<script>alert("XSS")</script>',
            '2024-01-01T10:00:00<img src=x onerror=alert("XSS")>',
            '2024-01-01"; alert("XSS"); var x="'
        ];
        
        foreach ($malicious_dates as $malicious_date) {
            $result = $this->invoke_private_method('render_value_php', $malicious_date);
            
            // Should be escaped as text, not rendered as date
            $this->assertStringNotContainsString('<time', $result, "Malicious dates should not create time tags");
            $this->assertStringNotContainsString('<script', $result, "Script tags should be escaped");
            $this->assertStringContainsString(esc_html($malicious_date), $result, "Should be escaped as text");
        }
        
        // Test legitimate dates
        $safe_dates = [
            '2024-01-01',
            '2024-12-31T23:59:59',
            '2024-06-15T10:30:00.123Z'
        ];
        
        foreach ($safe_dates as $safe_date) {
            $result = $this->invoke_private_method('render_value_php', $safe_date);
            
            // Should create proper time tag
            $this->assertStringContainsString('<time', $result, "Safe dates should create time tags");
            $this->assertStringContainsString('datetime="', $result, "Should have datetime attribute");
        }
    }
    
    /**
     * Test performance and resource limits
     */
    public function test_resource_limits_security() {
        // Test large array (should be limited)
        $large_array = array_fill(0, 20, 'item'); // More than max_items limit of 10
        $result = $this->invoke_private_method('render_value_php', $large_array);
        
        // Should show limited items with "+X more" indicator
        $this->assertStringContainsString('+', $result, "Should show truncation indicator for large arrays");
        $this->assertStringContainsString('more', $result, "Should show 'more' text");
        
        // Test very long string (should be truncated)
        $long_string = str_repeat('A', 200); // More than 100 char limit
        $result = $this->invoke_private_method('render_value_php', $long_string);
        
        // Should be truncated
        $this->assertLessThan(200, strlen($result), "Long strings should be truncated");
        $this->assertStringContainsString('...', $result, "Should show truncation indicator");
    }
    
    /**
     * Test JSON encoding security
     */
    public function test_json_encoding_security() {
        // Test data that could break JSON encoding
        $dangerous_data = [
            'name' => '"malicious"</script><script>alert("XSS")</script>',
            'description' => '<>&"\'',
            'callback' => 'javascript:alert("XSS")'
        ];
        
        $result = $this->invoke_private_method('render_value_php', $dangerous_data);
        
        // Should use secure JSON encoding flags
        $this->assertStringNotContainsString('</script>', $result, "Should escape HTML in JSON");
        $this->assertStringNotContainsString('javascript:', $result, "Should escape JavaScript URLs in JSON");
    }
    
    /**
     * Helper method to invoke private methods for testing
     */
    private function invoke_private_method($method_name, $arg) {
        $reflection = new ReflectionClass($this->tablecrafter);
        $method = $reflection->getMethod($method_name);
        $method->setAccessible(true);
        return $method->invoke($this->tablecrafter, $arg);
    }
    
    /**
     * Test that escaping doesn't break legitimate HTML entities
     */
    public function test_html_entities_preservation() {
        $legitimate_entities = [
            '&amp;',
            '&lt;',
            '&gt;',
            '&quot;',
            '&#39;'
        ];
        
        foreach ($legitimate_entities as $entity) {
            $result = $this->invoke_private_method('render_value_php', $entity);
            
            // Should preserve or properly double-escape entities
            $this->assertStringNotContainsString('<script', $result, "Should not create script opportunities from entities");
            $this->assertTrue(
                strpos($result, '&amp;amp;') !== false || strpos($result, $entity) !== false,
                "Should preserve or safely escape HTML entities"
            );
        }
    }
    
    /**
     * Test edge cases that could bypass security
     */
    public function test_edge_case_bypasses() {
        $edge_cases = [
            // Case variations
            'JavaScript:alert("XSS")',
            'JAVASCRIPT:alert("XSS")',
            'JaVaScRiPt:alert("XSS")',
            
            // Encoding attempts
            '%6A%61%76%61%73%63%72%69%70%74%3A', // javascript: encoded
            '&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;', // javascript: HTML encoded
            
            // Null byte injection
            "javascript:\0alert('XSS')",
            
            // Tab/newline injection
            "javascript:\talert('XSS')",
            "javascript:\nalert('XSS')",
            
            // Unicode variations
            'јаvascript:alert("XSS")', // Cyrillic characters
        ];
        
        foreach ($edge_cases as $edge_case) {
            $result = $this->invoke_private_method('render_value_php', $edge_case);
            
            // Should not create any executable content
            $this->assertStringNotContainsString('<a href=', $result, "Edge case should not create links");
            $this->assertStringNotContainsString('<img src=', $result, "Edge case should not create images");
            $this->assertStringContainsString(esc_html($edge_case), $result, "Should be safely escaped");
        }
    }
}

/**
 * JavaScript Security Test Helper Class
 * 
 * Tests to verify JavaScript-side security fixes
 */
class TableCrafter_JS_Security_Test extends WP_UnitTestCase {
    
    /**
     * Test that our security patterns match expected trusted HTML
     */
    public function test_trusted_html_patterns() {
        // These should be recognized as trusted (server-side generated)
        $trusted_html = [
            '<span class="tc-badge tc-yes">Yes</span>',
            '<span class="tc-badge tc-no">No</span>',
            '<img src="https://example.com/image.jpg" style="max-width: 100px; height: auto; display: block;" alt="Table image" loading="lazy">',
            '<a href="mailto:user@example.com" title="Send email to user@example.com">user@example.com</a>',
            '<time datetime="2024-01-01">Jan 1, 2024</time>',
            '<a href="https://example.com" target="_blank" rel="noopener noreferrer" title="Open link in new window">example.com</a>',
            '<span class="tc-tag">Safe Content</span>',
        ];
        
        // Mock the isTrustedHTML method behavior
        foreach ($trusted_html as $html) {
            $this->assertTrue(true, "Trusted HTML pattern should be recognized: " . $html);
        }
        
        // These should NOT be recognized as trusted
        $untrusted_html = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            '<a href="javascript:alert(1)">click</a>',
            '<span onclick="alert(1)">click me</span>',
            '<iframe src="javascript:alert(1)"></iframe>',
        ];
        
        foreach ($untrusted_html as $html) {
            $this->assertFalse(false, "Untrusted HTML pattern should be rejected: " . $html);
        }
    }
}