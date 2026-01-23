<?php
/**
 * Unit Tests for TC_Cache Class
 *
 * @package TableCrafter\Tests
 */

use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
    private TC_Cache $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = TC_Cache::get_instance();
        tc_reset_mock_transients();
    }

    /**
     * Test HTML cache key generation is consistent
     */
    public function test_get_html_cache_key_is_consistent(): void
    {
        $atts = array(
            'source' => 'https://api.example.com/data.json',
            'include' => 'name,email',
            'exclude' => 'password',
            'search' => true,
            'filters' => true,
            'export' => false,
            'per_page' => 25,
            'sort' => 'name:asc'
        );

        $key1 = $this->cache->get_html_cache_key($atts);
        $key2 = $this->cache->get_html_cache_key($atts);

        $this->assertEquals($key1, $key2);
        $this->assertStringStartsWith('tc_html_', $key1);
    }

    /**
     * Test HTML cache key changes with different attributes
     */
    public function test_get_html_cache_key_differs_with_different_atts(): void
    {
        $atts1 = array(
            'source' => 'https://api.example.com/data.json',
            'search' => true
        );

        $atts2 = array(
            'source' => 'https://api.example.com/data.json',
            'search' => false
        );

        $key1 = $this->cache->get_html_cache_key($atts1);
        $key2 = $this->cache->get_html_cache_key($atts2);

        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test data cache key generation
     */
    public function test_get_data_cache_key(): void
    {
        $source = 'https://api.example.com/data.json';
        $key = $this->cache->get_data_cache_key($source);

        $this->assertStringStartsWith('tc_cache_', $key);
        $this->assertEquals(
            $key,
            $this->cache->get_data_cache_key($source)
        );
    }

    /**
     * Test HTML cache set and get
     */
    public function test_html_cache_set_and_get(): void
    {
        $cache_key = 'tc_html_test123';
        $html = '<table><tr><td>Test</td></tr></table>';
        $data = array(array('col' => 'value'));

        $this->cache->set_html_cache($cache_key, $html, $data);

        $cached = $this->cache->get_html_cache($cache_key);

        $this->assertIsArray($cached);
        $this->assertEquals($html, $cached['html']);
        $this->assertEquals($data, $cached['data']);
        $this->assertArrayHasKey('time', $cached);
    }

    /**
     * Test data cache set and get
     */
    public function test_data_cache_set_and_get(): void
    {
        $cache_key = 'tc_cache_test123';
        $data = array(
            array('id' => 1, 'name' => 'Test'),
            array('id' => 2, 'name' => 'Example')
        );

        $this->cache->set_data_cache($cache_key, $data);
        $cached = $this->cache->get_data_cache($cache_key);

        $this->assertEquals($data, $cached);
    }

    /**
     * Test cache miss returns false
     */
    public function test_cache_miss_returns_false(): void
    {
        $this->assertFalse($this->cache->get_html_cache('nonexistent_key'));
        $this->assertFalse($this->cache->get_data_cache('nonexistent_key'));
    }

    /**
     * Test is_cache_stale with fresh cache
     */
    public function test_is_cache_stale_fresh(): void
    {
        $cache_data = array(
            'html' => '<table></table>',
            'time' => time()
        );

        $this->assertFalse($this->cache->is_cache_stale($cache_data));
    }

    /**
     * Test is_cache_stale with old cache
     */
    public function test_is_cache_stale_old(): void
    {
        $cache_data = array(
            'html' => '<table></table>',
            'time' => time() - 600 // 10 minutes ago
        );

        $this->assertTrue($this->cache->is_cache_stale($cache_data));
    }

    /**
     * Test is_cache_stale with missing time
     */
    public function test_is_cache_stale_missing_time(): void
    {
        $cache_data = array(
            'html' => '<table></table>'
        );

        $this->assertTrue($this->cache->is_cache_stale($cache_data));
    }

    /**
     * Test clear all caches
     */
    public function test_clear_all(): void
    {
        // Set some caches
        $this->cache->set_data_cache('tc_cache_test1', array('data'));
        $this->cache->set_html_cache('tc_html_test1', '<table>', array());

        // Clear all
        $cleared = $this->cache->clear_all();

        // Verify cleared
        $this->assertFalse($this->cache->get_data_cache('tc_cache_test1'));
        $this->assertFalse($this->cache->get_html_cache('tc_html_test1'));
    }
}
