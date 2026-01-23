<?php
/**
 * TableCrafter Data Fetcher
 *
 * Handles data fetching from various sources:
 * - Remote JSON APIs
 * - Local JSON files
 * - Google Sheets
 * - CSV files
 *
 * @package TableCrafter
 * @since 3.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TC_Data_Fetcher
{
    /**
     * Singleton instance
     * @var TC_Data_Fetcher|null
     */
    private static $instance = null;

    /**
     * Security handler
     * @var TC_Security
     */
    private $security;

    /**
     * Cache handler
     * @var TC_Cache
     */
    private $cache;

    /**
     * Get singleton instance
     *
     * @return TC_Data_Fetcher
     */
    public static function get_instance(): TC_Data_Fetcher
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
        $this->security = TC_Security::get_instance();
        $this->cache = TC_Cache::get_instance();
    }

    /**
     * Fetch data from source URL
     *
     * Handles local files, remote APIs, Google Sheets, and CSV files.
     *
     * @param string $url Source URL
     * @param bool $use_cache Whether to use cached data
     * @return array|WP_Error Parsed data or error
     */
    public function fetch(string $url, bool $use_cache = true)
    {
        // Check cache first
        if ($use_cache) {
            $cache_key = $this->cache->get_data_cache_key($url);
            $cached_data = $this->cache->get_data_cache($cache_key);
            if ($cached_data !== false) {
                return $cached_data;
            }
        }

        // Try local file resolution first
        $local_result = $this->fetch_local($url);
        if ($local_result !== false) {
            if ($use_cache && !is_wp_error($local_result)) {
                $this->cache->set_data_cache($this->cache->get_data_cache_key($url), $local_result);
            }
            return $local_result;
        }

        // Security check for remote URLs
        if (!$this->security->is_safe_url($url)) {
            $this->log_error('Security Block', array('url' => $url));
            return new WP_Error('security_error', 'The provided URL is blocked for safety (Local/Private IP).');
        }

        // Check for Airtable source (airtable:// protocol)
        if (strpos($url, 'airtable://') === 0) {
            $result = $this->fetch_airtable($url);
        }
        // Check for CSV / Google Sheets
        elseif (preg_match('/docs\.google\.com\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url) || substr($url, -4) === '.csv') {
            $result = TC_CSV_Source::fetch($url);
        } else {
            $result = $this->fetch_remote_json($url);
        }

        // Cache successful results
        if ($use_cache && !is_wp_error($result) && !empty($result)) {
            $this->cache->set_data_cache($this->cache->get_data_cache_key($url), $result);
            $this->cache->track_url($url);
        }

        return $result;
    }

    /**
     * Try to fetch data from local file
     *
     * @param string $url URL that might be local
     * @return array|false|WP_Error Data array, false if not local, or error
     */
    private function fetch_local(string $url)
    {
        $site_url = site_url();
        $home_url = home_url();
        $plugin_url = TABLECRAFTER_URL;

        // Check if URL is local
        if (
            strpos($url, $site_url) !== 0 &&
            strpos($url, $home_url) !== 0 &&
            strpos($url, $plugin_url) !== 0
        ) {
            return false;
        }

        $relative_path = str_replace(array($site_url, $home_url, $plugin_url), '', $url);
        $relative_path = ltrim($relative_path, '/');

        $possible_paths = array(
            ABSPATH . $relative_path,
            rtrim(ABSPATH, '/') . '/' . ltrim($relative_path, '/'),
            WP_CONTENT_DIR . '/' . $relative_path,
        );

        // Handle plugin-specific paths
        if (strpos($relative_path, 'wp-content/plugins/tablecrafter-wp-data-tables/') === 0) {
            $plugin_relative = str_replace('wp-content/plugins/tablecrafter-wp-data-tables/', '', $relative_path);
            $possible_paths[] = TABLECRAFTER_PATH . $plugin_relative;
        } elseif (strpos($relative_path, 'tablecrafter-wp-data-tables/') !== false) {
            $parts = explode('tablecrafter-wp-data-tables/', $relative_path, 2);
            if (isset($parts[1])) {
                $possible_paths[] = TABLECRAFTER_PATH . $parts[1];
            }
        }

        foreach ($possible_paths as $abs_path) {
            $real_path = realpath($abs_path);

            // Security: whitelist allowed paths
            $allowed_paths = array(
                realpath(ABSPATH),
                realpath(WP_CONTENT_DIR),
                realpath(TABLECRAFTER_PATH)
            );

            if (!$real_path || !file_exists($real_path) || !is_readable($real_path)) {
                continue;
            }

            // Check if path is in allowed directory
            $is_allowed = false;
            foreach ($allowed_paths as $base_path) {
                if ($base_path && strpos($real_path, $base_path) === 0) {
                    $is_allowed = true;
                    break;
                }
            }

            if (!$is_allowed) {
                continue;
            }

            $ext = pathinfo($real_path, PATHINFO_EXTENSION);

            // Handle JSON files
            if ($ext === 'json') {
                $content = @file_get_contents($real_path);
                if ($content !== false) {
                    $data = json_decode($content, true);
                    if ($data !== null && json_last_error() === JSON_ERROR_NONE) {
                        return $data;
                    }
                }
            }

            // Handle CSV files
            if ($ext === 'csv') {
                $content = @file_get_contents($real_path);
                if ($content !== false) {
                    return TC_CSV_Source::parse($content);
                }
            }
        }

        return false;
    }

    /**
     * Fetch data from Airtable source
     *
     * Parses airtable:// URL and delegates to TC_Airtable_Source.
     *
     * @param string $url Airtable URL (airtable://baseId/tableName?token=xxx)
     * @return array|WP_Error Parsed data or error
     */
    private function fetch_airtable(string $url)
    {
        // Parse the Airtable URL
        $parsed = TC_Airtable_Source::parse_url($url);

        if (is_wp_error($parsed)) {
            $this->log_error('Airtable URL Parse Error', array('url' => $url));
            return $parsed;
        }

        // Token is required
        if (empty($parsed['token'])) {
            // Try to get token from saved settings
            $saved_token = get_option('tablecrafter_airtable_token', '');
            if (!empty($saved_token)) {
                $parsed['token'] = $this->security->decrypt_token($saved_token);
            }
        }

        if (empty($parsed['token'])) {
            return new WP_Error(
                'airtable_no_token',
                __('Airtable Personal Access Token is required.', 'tablecrafter-wp-data-tables')
            );
        }

        // Build optional params
        $params = [];
        if (!empty($parsed['view'])) {
            $params['view'] = $parsed['view'];
        }

        // Fetch from Airtable
        $result = TC_Airtable_Source::fetch(
            $parsed['base_id'],
            $parsed['table_name'],
            $parsed['token'],
            $params
        );

        if (is_wp_error($result)) {
            $this->log_error('Airtable Fetch Error', array(
                'base_id' => $parsed['base_id'],
                'table' => $parsed['table_name'],
                'error' => $result->get_error_message()
            ));
        }

        return $result;
    }

    /**
     * Fetch JSON data from remote URL
     *
     * @param string $url Remote URL
     * @return array|WP_Error Parsed JSON data or error
     */
    private function fetch_remote_json(string $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        // SSL verification enabled for security
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        // Use WordPress bundled CA certificates if available
        $ca_bundle = ABSPATH . WPINC . '/certificates/ca-bundle.crt';
        if (file_exists($ca_bundle)) {
            curl_setopt($ch, CURLOPT_CAINFO, $ca_bundle);
        }

        curl_setopt($ch, CURLOPT_USERAGENT, 'TableCrafter/' . TABLECRAFTER_VERSION . ' (WordPress Plugin)');
        curl_setopt($ch, CURLOPT_COOKIEFILE, "");

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            $this->log_error('CURL Fetch Failed', array('url' => $url, 'error' => $error));
            return new WP_Error('http_error', 'CURL Error: ' . $error);
        }

        if ($code !== 200) {
            $this->log_error('HTTP Error Code', array('url' => $url, 'code' => $code));
            return new WP_Error('http_error', 'Source returned HTTP ' . $code);
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->log_error('JSON Parse Error', array('url' => $url, 'error' => json_last_error_msg()));
            return new WP_Error('json_error', 'The source did not return a valid JSON structure.');
        }

        return $data;
    }

    /**
     * Extract data from nested path
     *
     * @param array $data Source data
     * @param string $root Dot-notation path (e.g., "data.items")
     * @return array|WP_Error Extracted data or error
     */
    public function extract_from_root(array $data, string $root)
    {
        if (empty($root)) {
            return $data;
        }

        $path = explode('.', $root);
        foreach ($path as $segment) {
            if (isset($data[$segment])) {
                $data = $data[$segment];
            } else {
                return new WP_Error('path_error', "Path Error: Key '$segment' not found in data structure.");
            }
        }

        return $data;
    }

    /**
     * Process include/exclude columns
     *
     * @param array $data Source data
     * @param string $include Comma-separated columns to include
     * @param string $exclude Comma-separated columns to exclude
     * @return array Processed headers and header map
     */
    public function process_columns(array $data, string $include = '', string $exclude = ''): array
    {
        $include_raw = !empty($include) ? array_map('trim', explode(',', $include)) : array();
        $exclude_cols = !empty($exclude) ? array_map('trim', explode(',', $exclude)) : array();

        $header_map = array();
        $include_keys = array();

        // Process aliasing in include
        if (!empty($include_raw)) {
            foreach ($include_raw as $item) {
                if (strpos($item, ':') !== false) {
                    list($key, $alias) = explode(':', $item, 2);
                    $key = trim($key);
                    $include_keys[] = $key;
                    $header_map[$key] = trim($alias);
                } else {
                    $include_keys[] = $item;
                }
            }
        }

        // Get headers from first row
        $headers = array_keys((array) reset($data));

        // Apply include filter
        if (!empty($include_keys)) {
            $headers = array_intersect($headers, $include_keys);
            // Maintain include order
            $sorted_headers = array();
            foreach ($include_keys as $k) {
                if (in_array($k, $headers)) {
                    $sorted_headers[] = $k;
                }
            }
            $headers = $sorted_headers;
        }

        // Apply exclude filter
        if (!empty($exclude_cols)) {
            $headers = array_diff($headers, $exclude_cols);
        }

        return array(
            'headers' => array_values($headers),
            'header_map' => $header_map
        );
    }

    /**
     * Sort data array by field
     *
     * @param array $data Data to sort
     * @param string $field Field to sort by
     * @param string $direction Sort direction (asc/desc)
     * @return array Sorted data
     */
    public function sort_data(array $data, string $field, string $direction): array
    {
        if (empty($data) || empty($field)) {
            return $data;
        }

        $is_ascending = in_array(strtolower($direction), array('asc', 'ascending'));

        usort($data, function ($a, $b) use ($field, $is_ascending) {
            $a = (array) $a;
            $b = (array) $b;

            $val_a = isset($a[$field]) ? $a[$field] : '';
            $val_b = isset($b[$field]) ? $b[$field] : '';

            if (is_numeric($val_a) && is_numeric($val_b)) {
                $result = floatval($val_a) <=> floatval($val_b);
            } else {
                $result = strcasecmp(strval($val_a), strval($val_b));
            }

            return $is_ascending ? $result : -$result;
        });

        return $data;
    }

    /**
     * Log errors
     *
     * @param string $message Error message
     * @param array $context Additional context
     * @return void
     */
    private function log_error(string $message, array $context = array()): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log = sprintf('[TableCrafter DataFetcher] %s | Context: %s', $message, json_encode($context));
            error_log($log);
        }
    }
}
