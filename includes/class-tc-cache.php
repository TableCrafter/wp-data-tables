<?php
/**
 * TableCrafter Cache Handler
 *
 * Handles caching functionality including:
 * - HTML cache (rendered tables)
 * - Data cache (fetched data)
 * - SWR (Stale-While-Revalidate) pattern
 * - Cache invalidation
 *
 * @package TableCrafter
 * @since 3.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TC_Cache
{
    /**
     * Cache TTL constants (in seconds)
     */
    const HTML_CACHE_TTL = HOUR_IN_SECONDS;
    const DATA_CACHE_TTL = HOUR_IN_SECONDS;
    const STALE_THRESHOLD = 300; // 5 minutes

    /**
     * Cache key prefixes
     */
    const PREFIX_HTML = 'tc_html_';
    const PREFIX_DATA = 'tc_cache_';
    const PREFIX_EXPORT = 'tc_export_';
    const PREFIX_RATE = 'tc_rate_';

    /**
     * Singleton instance
     * @var TC_Cache|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return TC_Cache
     */
    public static function get_instance(): TC_Cache
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton
     */
    private function __construct()
    {
    }

    /**
     * Generate a cache key for HTML content
     *
     * @param array $atts Shortcode attributes
     * @return string Cache key
     */
    public function get_html_cache_key(array $atts): string
    {
        $key_parts = array(
            $atts['source'] ?? '',
            $atts['include'] ?? '',
            $atts['exclude'] ?? '',
            ($atts['search'] ?? false) ? '1' : '0',
            ($atts['filters'] ?? false) ? '1' : '0',
            ($atts['export'] ?? false) ? '1' : '0',
            $atts['per_page'] ?? '0',
            $atts['sort'] ?? '',
            TABLECRAFTER_VERSION
        );

        return self::PREFIX_HTML . md5(implode('|', $key_parts));
    }

    /**
     * Generate a cache key for raw data
     *
     * @param string $source Source URL
     * @return string Cache key
     */
    public function get_data_cache_key(string $source): string
    {
        return self::PREFIX_DATA . md5($source . TABLECRAFTER_VERSION);
    }

    /**
     * Get cached HTML content
     *
     * @param string $cache_key Cache key
     * @return array|false Cached data array or false if not found
     */
    public function get_html_cache(string $cache_key)
    {
        return get_transient($cache_key);
    }

    /**
     * Set HTML cache content
     *
     * @param string $cache_key Cache key
     * @param string $html HTML content
     * @param array $data Data array
     * @return bool Success
     */
    public function set_html_cache(string $cache_key, string $html, array $data): bool
    {
        return set_transient($cache_key, array(
            'html' => $html,
            'data' => $data,
            'time' => time()
        ), self::HTML_CACHE_TTL);
    }

    /**
     * Get cached data
     *
     * @param string $cache_key Cache key
     * @return mixed Cached data or false if not found
     */
    public function get_data_cache(string $cache_key)
    {
        return get_transient($cache_key);
    }

    /**
     * Set data cache
     *
     * @param string $cache_key Cache key
     * @param mixed $data Data to cache
     * @param int $ttl Cache TTL in seconds (optional)
     * @return bool Success
     */
    public function set_data_cache(string $cache_key, $data, int $ttl = self::DATA_CACHE_TTL): bool
    {
        return set_transient($cache_key, $data, $ttl);
    }

    /**
     * Check if cache is stale (for SWR pattern)
     *
     * @param array $cache_data Cached data with 'time' key
     * @return bool True if cache is stale
     */
    public function is_cache_stale(array $cache_data): bool
    {
        if (!isset($cache_data['time'])) {
            return true;
        }

        return (time() - $cache_data['time']) > self::STALE_THRESHOLD;
    }

    /**
     * Clear all TableCrafter caches
     *
     * @return int Number of transients cleared
     */
    public function clear_all(): int
    {
        global $wpdb;
        $cleared = 0;

        $patterns = array(
            self::PREFIX_HTML,
            self::PREFIX_DATA,
            self::PREFIX_EXPORT,
            self::PREFIX_RATE,
        );

        foreach ($patterns as $prefix) {
            $transient_names = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                    $wpdb->esc_like('_transient_' . $prefix) . '%'
                )
            );

            foreach ($transient_names as $transient_name) {
                $name = str_replace('_transient_', '', $transient_name);
                if (delete_transient($name)) {
                    $cleared++;
                }
            }
        }

        return $cleared;
    }

    /**
     * Clear cache for a specific source URL
     *
     * @param string $source Source URL
     * @return bool Success
     */
    public function clear_source(string $source): bool
    {
        $data_key = $this->get_data_cache_key($source);
        return delete_transient($data_key);
    }

    /**
     * Track URL for cache warming
     *
     * @param string $url URL to track
     * @return void
     */
    public function track_url(string $url): void
    {
        $urls = get_option('tc_tracked_urls', array());
        if (!is_array($urls)) {
            $urls = array();
        }

        if (!in_array($url, $urls)) {
            $urls[] = $url;
            update_option('tc_tracked_urls', array_slice($urls, -50));
        }
    }

    /**
     * Get tracked URLs for cache warming
     *
     * @return array List of tracked URLs
     */
    public function get_tracked_urls(): array
    {
        $urls = get_option('tc_tracked_urls', array());
        return is_array($urls) ? $urls : array();
    }

    /**
     * Warm cache for all tracked URLs
     *
     * @return int Number of URLs warmed
     */
    public function warm_cache(): int
    {
        $urls = $this->get_tracked_urls();
        $warmed = 0;

        foreach ($urls as $url) {
            $response = wp_remote_get($url, array('timeout' => 10));
            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
                if ($data && json_last_error() === JSON_ERROR_NONE) {
                    $cache_key = $this->get_data_cache_key($url);
                    $this->set_data_cache($cache_key, $data);
                    $warmed++;
                }
            }
        }

        return $warmed;
    }
}
