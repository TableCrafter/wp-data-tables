<?php
/**
 * Unit Tests for TC_Security Class
 *
 * @package TableCrafter\Tests
 */

use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    private TC_Security $security;

    protected function setUp(): void
    {
        parent::setUp();
        $this->security = TC_Security::get_instance();
        tc_reset_mock_transients();
    }

    /**
     * Test SSRF prevention blocks localhost
     */
    public function test_is_safe_url_blocks_localhost(): void
    {
        $this->assertFalse($this->security->is_safe_url('http://localhost/api'));
        $this->assertFalse($this->security->is_safe_url('http://127.0.0.1/api'));
        $this->assertFalse($this->security->is_safe_url('http://[::1]/api'));
    }

    /**
     * Test SSRF prevention blocks private IPs
     */
    public function test_is_safe_url_blocks_private_ips(): void
    {
        $this->assertFalse($this->security->is_safe_url('http://192.168.1.1/api'));
        $this->assertFalse($this->security->is_safe_url('http://10.0.0.1/api'));
        $this->assertFalse($this->security->is_safe_url('http://172.16.0.1/api'));
    }

    /**
     * Test SSRF prevention allows public URLs
     */
    public function test_is_safe_url_allows_public_urls(): void
    {
        $this->assertTrue($this->security->is_safe_url('https://api.example.com/data'));
        $this->assertTrue($this->security->is_safe_url('https://jsonplaceholder.typicode.com/posts'));
    }

    /**
     * Test image URL validation
     */
    public function test_is_safe_image_url(): void
    {
        // Valid image URLs
        $this->assertTrue($this->security->is_safe_image_url('https://example.com/image.jpg'));
        $this->assertTrue($this->security->is_safe_image_url('https://example.com/image.png'));
        $this->assertTrue($this->security->is_safe_image_url('https://example.com/image.gif'));
        $this->assertTrue($this->security->is_safe_image_url('https://example.com/image.webp'));

        // Invalid/dangerous URLs
        $this->assertFalse($this->security->is_safe_image_url('javascript:alert(1)'));
        $this->assertFalse($this->security->is_safe_image_url('data:text/html,<script>'));
        $this->assertFalse($this->security->is_safe_image_url('https://example.com/file.pdf'));
    }

    /**
     * Test date string validation
     */
    public function test_is_valid_date_string(): void
    {
        // Valid dates
        $this->assertTrue($this->security->is_valid_date_string('2024-01-15'));
        $this->assertTrue($this->security->is_valid_date_string('2024-01-15T10:30:00Z'));
        $this->assertTrue($this->security->is_valid_date_string('2024-01-15T10:30:00'));

        // Invalid dates
        $this->assertFalse($this->security->is_valid_date_string('not-a-date'));
        $this->assertFalse($this->security->is_valid_date_string('01/15/2024'));
        $this->assertFalse($this->security->is_valid_date_string('2024-13-45'));
    }

    /**
     * Test display URL validation (XSS prevention)
     */
    public function test_is_safe_display_url(): void
    {
        // Safe URLs
        $this->assertTrue($this->security->is_safe_display_url('https://example.com'));
        $this->assertTrue($this->security->is_safe_display_url('http://example.com/page'));

        // Dangerous URLs
        $this->assertFalse($this->security->is_safe_display_url('javascript:alert(1)'));
        $this->assertFalse($this->security->is_safe_display_url('data:text/html,<script>'));
        $this->assertFalse($this->security->is_safe_display_url('file:///etc/passwd'));
        $this->assertFalse($this->security->is_safe_display_url('ftp://example.com'));
    }

    /**
     * Test rate limiting
     */
    public function test_rate_limiting(): void
    {
        // First request should not be limited
        $this->assertFalse($this->security->is_rate_limited());

        // Simulate hitting the rate limit
        for ($i = 0; $i < 30; $i++) {
            $this->security->is_rate_limited();
        }

        // Should now be rate limited
        $this->assertTrue($this->security->is_rate_limited());
    }

    /**
     * Test client IP detection defaults to REMOTE_ADDR
     */
    public function test_get_client_ip_uses_remote_addr_by_default(): void
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.50';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.1';

        $ip = $this->security->get_client_ip();

        // Should use REMOTE_ADDR, not X-Forwarded-For (which could be spoofed)
        $this->assertEquals('203.0.113.50', $ip);
    }

    /**
     * Test sanitize_image_url
     */
    public function test_sanitize_image_url(): void
    {
        // Valid URLs
        $this->assertEquals(
            'https://example.com/image.jpg',
            $this->security->sanitize_image_url('https://example.com/image.jpg')
        );

        // Invalid URLs
        $this->assertFalse($this->security->sanitize_image_url('not-a-url'));
        $this->assertFalse($this->security->sanitize_image_url('data:image/svg+xml,<svg>'));
    }
}
