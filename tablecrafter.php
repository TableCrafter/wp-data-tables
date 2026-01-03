<?php
/**
 * Plugin Name: TableCrafter – WordPress Data Tables & Dynamic Content Plugin
 * Plugin URI: https://github.com/TableCrafter/wp-data-tables
 * Description: A lightweight WordPress wrapper for the TableCrafter JavaScript library. Creates dynamic data tables from a single data source.
 * Version: 2.0.7
 * Author: TableCrafter Team
 * Author URI: https://github.com/fahdi
 * License: GPLv2 or later
 * Text Domain: tablecrafter-wp-data-tables
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Global Constants
 */
define('TABLECRAFTER_VERSION', '2.0.7');
define('TABLECRAFTER_URL', plugin_dir_url(__FILE__));
define('TABLECRAFTER_PATH', plugin_dir_path(__FILE__));

/**
 * Main TableCrafter Class
 * 
 * Handles registration, rendering, security, and caching for the TableCrafter plugin.
 */
class TableCrafter {
    
    /**
     * Singleton instance.
     * @var TableCrafter|null
     */
    private static $instance = null;
    
    /**
     * Get singleton instance.
     * 
     * @return TableCrafter The single instance of the class.
     */
    public static function get_instance(): TableCrafter {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor.
     * 
     * Initializes all WordPress hooks, shortcodes, and cron schedules.
     */
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('init', array($this, 'register_block'));
        add_shortcode('tablecrafter', array($this, 'render_table'));
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // AJAX Proxy Handlers for frontend and admin
        add_action('wp_ajax_tc_proxy_fetch', array($this, 'ajax_proxy_fetch'));
        add_action('wp_ajax_nopriv_tc_proxy_fetch', array($this, 'ajax_proxy_fetch'));

        // Background Caching & Cron Logic
        add_action('tc_refresher_cron', array($this, 'automated_cache_refresh'));
        add_action('tc_refresh_single_source', array($this, 'refresh_source_cache'), 10, 1);
        
        if (!wp_next_scheduled('tc_refresher_cron')) {
            wp_schedule_event(time(), 'hourly', 'tc_refresher_cron');
        }

        // WP-CLI Integration
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('tablecrafter', array($this, 'cli_commands'));
        }
    }

    /**
     * Add admin menu page under the 'Tools' or 'Settings' category.
     * 
     * @return void
     */
    public function add_admin_menu(): void {
        add_menu_page(
            __('TableCrafter', 'tablecrafter-wp-data-tables'),
            __('TableCrafter', 'tablecrafter-wp-data-tables'),
            'manage_options',
            'tablecrafter-wp-data-tables',
            array($this, 'render_admin_page'),
            'dashicons-editor-table',
            20
        );
    }

    /**
     * Render the admin dashboard page.
     * 
     * Displays settings, shortcode generator, and live-preview playground.
     * 
     * @return void
     */
    public function render_admin_page(): void {
        // Enqueue preview assets
        wp_enqueue_script('tablecrafter-lib');
        wp_enqueue_style('tablecrafter-style');
        
        $users_url = TABLECRAFTER_URL . 'demo-data/users.json';
        $products_url = TABLECRAFTER_URL . 'demo-data/products.json';
        $metrics_url = TABLECRAFTER_URL . 'demo-data/metrics.json';
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('TableCrafter', 'tablecrafter-wp-data-tables'); ?></h1>
            <p><?php esc_html_e('Generate dynamic HTML tables from any JSON data source.', 'tablecrafter-wp-data-tables'); ?></p>
            <hr class="wp-header-end">

            <div class="tc-admin-layout" style="display: flex; gap: 20px; margin-top: 20px; align-items: flex-start;">
                
                <div class="tc-sidebar" style="flex: 0 0 350px;">
                    <div class="card" style="margin: 0 0 20px 0; max-width: none;">
                        <h2><?php esc_html_e('Settings', 'tablecrafter-wp-data-tables'); ?></h2>
                        <div style="margin-bottom: 15px;">
                            <label for="tc-preview-url" style="font-weight: 600; display: block; margin-bottom: 5px;"><?php esc_html_e('Data Source URL', 'tablecrafter-wp-data-tables'); ?></label>
                            <input type="text" id="tc-preview-url" class="widefat" placeholder="https://api.example.com/data.json">
                            <p class="description"><?php esc_html_e('Must be a publicly accessible JSON endpoint.', 'tablecrafter-wp-data-tables'); ?></p>
                        </div>
                        
                        <div style="display: flex; gap: 10px; margin-top: 15px;">
                            <button id="tc-preview-btn" class="button button-primary button-large" style="flex: 1;"><?php esc_html_e('Preview Table', 'tablecrafter-wp-data-tables'); ?></button>
                        </div>
                    </div>

                     <div class="card" style="margin: 0 0 20px 0; max-width: none;">
                        <h2><?php esc_html_e('Usage', 'tablecrafter-wp-data-tables'); ?></h2>
                        <p><?php esc_html_e('Copy the shortcode below to use this table:', 'tablecrafter-wp-data-tables'); ?></p>
                        <code id="tc-shortcode-display" style="display: block; padding: 10px; background: #f0f0f1; margin: 10px 0;">[tablecrafter source="..."]</code>
                        <button id="tc-copy-shortcode" class="button button-secondary" style="width: 100%;"><?php esc_html_e('Copy Shortcode', 'tablecrafter-wp-data-tables'); ?></button>
                     </div>

                    <div class="card" style="margin: 0; max-width: none;">
                        <h2><?php esc_html_e('Quick Demos', 'tablecrafter-wp-data-tables'); ?></h2>
                        <p><?php esc_html_e('Click a dataset to load:', 'tablecrafter-wp-data-tables'); ?></p>
                        <ul class="tc-demo-links" style="margin: 0;">
                            <li style="margin-bottom: 8px;"><a href="#" class="button" style="width: 100%; text-align: left;" data-url="<?php echo esc_url($users_url); ?>">👤 <?php esc_html_e('User Directory (JSON)', 'tablecrafter-wp-data-tables'); ?></a></li>
                            <li style="margin-bottom: 8px;"><a href="#" class="button" style="width: 100%; text-align: left;" data-url="<?php echo esc_url($products_url); ?>">📦 <?php esc_html_e('Product Inventory (JSON)', 'tablecrafter-wp-data-tables'); ?></a></li>
                            <li style="margin-bottom: 0;"><a href="#" class="button" style="width: 100%; text-align: left;" data-url="<?php echo esc_url($metrics_url); ?>">📈 <?php esc_html_e('Sales Metrics (JSON)', 'tablecrafter-wp-data-tables'); ?></a></li>
                        </ul>
                    </div>
                </div>

                <div class="tc-preview-area" style="flex: 1; min-width: 0;">
                    <div class="card" style="margin: 0; max-width: none; min-height: 500px; display: flex; flex-direction: column;">
                        <h2 style="border-bottom: 1px solid #f0f0f1; padding-bottom: 15px; margin-bottom: 15px; margin-top: 0;"><?php esc_html_e('Live Preview', 'tablecrafter-wp-data-tables'); ?></h2>
                        
                        <div id="tc-preview-wrap" style="flex: 1; overflow: auto; background: #fff;">
                            <div id="tc-preview-container" style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666;">
                                <div style="text-align: center;">
                                    <span class="dashicons dashicons-editor-table" style="font-size: 48px; width: 48px; height: 48px; color: #ddd;"></span>
                                    <p><?php esc_html_e('Select a demo or enter a URL to generate a table.', 'tablecrafter-wp-data-tables'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Register frontend assets (JS/CSS).
     * 
     * Registers the core TableCrafter library and frontend initialization scripts.
     * 
     * @return void
     */
    public function register_assets(): void {
        wp_register_script(
            'tablecrafter-lib',
            TABLECRAFTER_URL . 'assets/js/tablecrafter.js',
            array(),
            TABLECRAFTER_VERSION,
            true
        );

        wp_register_script(
            'tablecrafter-frontend',
            TABLECRAFTER_URL . 'assets/js/frontend.js',
            array('tablecrafter-lib'),
            TABLECRAFTER_VERSION,
            true
        );

        wp_localize_script('tablecrafter-frontend', 'tablecrafterData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('tc_proxy_nonce')
        ));
        
        wp_register_style(
            'tablecrafter-style',
            TABLECRAFTER_URL . 'assets/css/tablecrafter.css',
            array(),
            TABLECRAFTER_VERSION
        );
    }

    /**
     * Enqueue admin assets.
     * 
     * @param string $hook The current admin page hook.
     * @return void
     */
    public function enqueue_admin_assets($hook): void {
        if (strpos($hook, 'tablecrafter-wp-data-tables') === false) {
            return;
        }

        wp_enqueue_script(
            'tablecrafter-admin',
            TABLECRAFTER_URL . 'assets/js/admin.js',
            array('jquery'),
            TABLECRAFTER_VERSION,
            true
        );

        wp_localize_script('tablecrafter-admin', 'tablecrafterAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('tc_proxy_nonce'),
            'i18n' => array(
                'enterUrl'   => __('Please enter a valid URL', 'tablecrafter-wp-data-tables'),
                'loading'    => __('Loading data from source...', 'tablecrafter-wp-data-tables'),
                'libMissing' => __('TableCrafter library not loaded.', 'tablecrafter-wp-data-tables'),
                'copyFailed' => __('Failed to copy to clipboard. Please copy manually.', 'tablecrafter-wp-data-tables'),
                'copied'     => __('Copied to Clipboard!', 'tablecrafter-wp-data-tables')
            )
        ));

        $this->register_assets();
        wp_enqueue_script('tablecrafter-lib');
        wp_enqueue_style('tablecrafter-style');
    }
    
    /**
     * Register Gutenberg Native Block.
     * 
     * Registers 'tablecrafter/data-table' with native sidebar attributes.
     * 
     * @return void
     */
    public function register_block(): void {
        if (!function_exists('register_block_type')) {
            return;
        }

        wp_register_script(
            'tablecrafter-block',
            TABLECRAFTER_URL . 'assets/js/block.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-server-side-render'),
            TABLECRAFTER_VERSION
        );

        register_block_type('tablecrafter/data-table', array(
            'editor_script' => 'tablecrafter-block',
            'render_callback' => array($this, 'render_block_callback'),
            'attributes' => array(
                'source'  => array('type' => 'string', 'default' => ''),
                'root'    => array('type' => 'string', 'default' => ''),
                'include' => array('type' => 'string', 'default' => ''),
                'exclude' => array('type' => 'string', 'default' => ''),
                'search'  => array('type' => 'boolean', 'default' => false),
                'per_page'=> array('type' => 'number', 'default' => 0),
                'export'  => array('type' => 'boolean', 'default' => false),
                'id'      => array('type' => 'string', 'default' => ''),
            ),
        ));
    }

    /**
     * Block Render Callback.
     * 
     * Bridge between Gutenberg block engine and PHP shortcode engine.
     * 
     * @param array $attributes Block attributes.
     * @return string Rendered HTML.
     */
    public function render_block_callback($attributes): string {
        if (empty($attributes['id'])) {
            $attributes['id'] = 'tc-block-' . uniqid();
        }
        return $this->render_table($attributes);
    }

    /**
     * Shortcode: [tablecrafter]
     * 
     * Main entry point for frontend rendering. Handles SWR caching (Stale-While-Revalidate).
     * 
     * @param array $atts User-defined attributes.
     * @return string HTML table container.
     */
    public function render_table($atts): string {
        $atts = shortcode_atts(array(
            'source'  => '',
            'id'      => 'tc-' . uniqid(),
            'include' => '',
            'exclude' => '',
            'root'    => '',
            'search'  => 'false',
            'export'  => 'false',
            'per_page' => 0
        ), $atts, 'tablecrafter');
        
        $atts['source'] = esc_url_raw($atts['source']);
        
        if (empty($atts['source'])) {
            return '<p>' . esc_html__('Error: TableCrafter requires a "source" attribute.', 'tablecrafter-wp-data-tables') . '</p>';
        }

        // SWR (Stale-While-Revalidate) Logic
        $cache_key = 'tc_html_' . md5($atts['source'] . $atts['include'] . $atts['exclude']);
        $cache_data = get_transient($cache_key);
        $html_content = '';

        if ($cache_data !== false) {
            $html_content = isset($cache_data['html']) ? $cache_data['html'] : '';
            $timestamp = isset($cache_data['time']) ? $cache_data['time'] : 0;
            
            // Trigger invisible refresh if cache is older than 5 mins
            if (time() - $timestamp > (5 * MINUTE_IN_SECONDS)) {
                if (!wp_next_scheduled('tc_refresh_single_source', array($atts))) {
                    wp_schedule_single_event(time(), 'tc_refresh_single_source', array($atts));
                }
            }
        } else {
            // First time render (Synch)
            $html_content = $this->fetch_and_render_php($atts);
            if ($html_content) {
                set_transient($cache_key, array(
                    'html' => $html_content,
                    'time' => time()
                ), HOUR_IN_SECONDS);
            }
        }
        
        $this->register_assets();
        wp_enqueue_script('tablecrafter-frontend');
        wp_enqueue_style('tablecrafter-style');
        
        ob_start();
        ?>
        <div id="<?php echo esc_attr($atts['id']); ?>" 
             class="tablecrafter-container" 
             data-source="<?php echo esc_url($atts['source']); ?>"
             data-include="<?php echo esc_attr($atts['include']); ?>"
             data-exclude="<?php echo esc_attr($atts['exclude']); ?>"
             data-root="<?php echo esc_attr($atts['root']); ?>"
             data-search="<?php echo esc_attr($atts['search']); ?>"
             data-export="<?php echo esc_attr($atts['export']); ?>"
             data-per-page="<?php echo esc_attr($atts['per_page']); ?>"
             data-ssr="true">
            <?php echo $html_content ? $html_content : '<div class="tc-loading">' . esc_html__('Loading TableCrafter...', 'tablecrafter-wp-data-tables') . '</div>'; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Server-Side Fetcher & Renderer.
     * 
     * Performs remote API hit and converts JSON into crawlable HTML table.
     * 
     * @param array $atts Configuration attributes.
     * @return string|false HTML or false on failure.
     */
    private function fetch_and_render_php($atts) {
        // 1. Try Cache First
        $cache_key = 'tc_cache_' . md5($atts['source']);
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            $data = $cached_data;
        } else {
            // 2. Cache Miss: Fetch from Source
            $response = wp_remote_get($atts['source'], array('timeout' => 15));
            if (is_wp_error($response)) return false;

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            // 3. Set Cache for Next Time (Read-Through)
            if ($data) {
                set_transient($cache_key, $data, HOUR_IN_SECONDS);
            }
        }
        
        // $data is already decoded array here
        // $body line is removed since we have $data from cache or decode


        if (!is_array($data) || empty($data)) return false;

        if (!empty($atts['root'])) {
            $path = explode('.', $atts['root']);
            foreach ($path as $segment) {
                if (isset($data[$segment])) {
                    $data = $data[$segment];
                } else {
                    return false;
                }
            }
        }

        if (!is_array($data) || empty($data) || !is_array(reset($data))) return false;

        $include_raw = !empty($atts['include']) ? array_map('trim', explode(',', $atts['include'])) : array();
        $exclude = !empty($atts['exclude']) ? array_map('trim', explode(',', $atts['exclude'])) : array();

        // Aliasing Logic
        $header_map = array();
        $include_keys = array();

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

        $headers = array_keys(reset($data));
        
        if (!empty($include_keys)) {
            $headers = array_intersect($headers, $include_keys);
            // Re-sort headers to match the order in 'include'
            $sorted_headers = array();
            foreach ($include_keys as $k) {
                if (in_array($k, $headers)) {
                    $sorted_headers[] = $k;
                }
            }
            $headers = $sorted_headers;
        }

        if (!empty($exclude)) {
            $headers = array_diff($headers, $exclude);
        }

        if (empty($headers)) return false;

        $html = '<table class="tc-table">';
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $label = isset($header_map[$header]) ? $header_map[$header] : $this->format_header_php($header);
            $html .= '<th>' . esc_html($label) . '</th>';
        }
        $html .= '</tr></thead>';
        $html .= '<tbody>';
        
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($headers as $header) {
                $val = isset($row[$header]) ? $row[$header] : '';
                $label = isset($header_map[$header]) ? $header_map[$header] : $this->format_header_php($header);
                $html .= '<td data-tc-label="' . esc_attr($label) . '">' . $this->render_value_php($val) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Header Formatter.
     * 
     * @param string $str Raw key.
     * @return string Title Case string.
     */
    private function format_header_php(string $str): string {
        return ucwords(str_replace('_', ' ', $str));
    }

    /**
     * Smart Value Renderer.
     * 
     * @param mixed $val Raw data.
     * @return string Sanitized HTML/Value.
     */
    private function render_value_php($val): string {
        $str = trim((string)$val);
        $lower = strtolower($str);

        // 1. Boolean
        if ($val === true || $lower === 'true') {
            return '<span class="tc-badge tc-yes">Yes</span>';
        }
        if ($val === false || $lower === 'false') {
            return '<span class="tc-badge tc-no">No</span>';
        }

        // 2. Images
        if (preg_match('/\.(jpeg|jpg|gif|png|webp|svg|bmp)$/i', $str) || strpos($str, 'data:image') === 0) {
            return sprintf('<img src="%s" style="max-width: 100px; height: auto; display: block;">', esc_url($str));
        }

        // 3. Email Addresses
        if (filter_var($str, FILTER_VALIDATE_EMAIL)) {
            return sprintf('<a href="mailto:%s">%s</a>', esc_attr($str), esc_html($str));
        }

        // 4. ISO Dates (YYYY-MM-DD)
        if (preg_match('/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}:\d{2})?/', $str) && strtotime($str)) {
             try {
                 $date = new DateTime($str);
                 return $date->format('M j, Y');
             } catch (Exception $e) {
                 // Fallback
             }
        }

        // 5. URLs
        if (strpos($str, 'http://') === 0 || strpos($str, 'https://') === 0) {
            return sprintf('<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url($str), esc_html($str));
        }

        // 6. Arrays (Tags UI)
        if (is_array($val)) {
            if (empty($val)) return '';
            
            // Check if it's an associative array (Object-like)
            if (array_keys($val) !== range(0, count($val) - 1)) {
                $display = isset($val['name']) ? $val['name'] : (isset($val['title']) ? $val['title'] : (isset($val['label']) ? $val['label'] : json_encode($val)));
                return sprintf('<span class="tc-tag">%s</span>', esc_html((string)$display));
            }

            $tags = array_map(function($item) {
                $display = $item;
                if (is_array($item)) {
                    $display = isset($item['name']) ? $item['name'] : (isset($item['title']) ? $item['title'] : (isset($item['label']) ? $item['label'] : json_encode($item)));
                }
                return sprintf('<span class="tc-tag">%s</span>', esc_html((string)$display));
            }, $val);
            
            return '<div class="tc-tag-list">' . implode('', $tags) . '</div>';
        }

        return esc_html($str);
    }

    /**
     * Secure AJAX Data Proxy.
     * 
     * Bypasses CORS and provides caching for frontend requests.
     * 
     * @return void
     */
    public function ajax_proxy_fetch(): void {
        check_ajax_referer('tc_proxy_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Unauthorized: You do not have permission to fetch remote data.', 'tablecrafter-wp-data-tables'));
        }

        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        
        if (empty($url) || !$this->is_safe_url($url)) {
            wp_send_json_error(__('TableCrafter Error: Invalid or unsafe URL provided.', 'tablecrafter-wp-data-tables'));
        }

        $cache_key = 'tc_cache_' . md5($url);
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            wp_send_json_success($cached_data);
        }

        $response = wp_remote_get($url, array('timeout' => 15));

        if (is_wp_error($response)) {
            wp_send_json_error('TableCrafter Proxy Error: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if ($data === null) {
            wp_send_json_error('TableCrafter Proxy Error: Invalid JSON response from source.');
        }

        set_transient($cache_key, $data, HOUR_IN_SECONDS);
        $this->track_url($url);

        wp_send_json_success($data);
    }

    /**
     * URL Analytics & Tracking.
     * 
     * @param string $url URL to track for background warming.
     * @return void
     */
    private function track_url(string $url): void {
        $urls = get_option('tc_tracked_urls', array());
        if (!is_array($urls)) $urls = array();
        
        if (!in_array($url, $urls)) {
            $urls[] = $url;
            update_option('tc_tracked_urls', array_slice($urls, -50));
        }
    }

    /**
     * Background Source Refresher.
     * 
     * @param array $atts Configuration to refresh.
     * @return void
     */
    public function refresh_source_cache(array $atts): void {
        $html = $this->fetch_and_render_php($atts);
        if ($html) {
            $cache_key = 'tc_html_' . md5($atts['source'] . $atts['include'] . $atts['exclude']);
            set_transient($cache_key, array(
                'html' => $html,
                'time' => time()
            ), HOUR_IN_SECONDS);
        }
    }

    /**
     * Hourly Automated Cache Warming (Cron).
     * 
     * @return void
     */
    public function automated_cache_refresh(): void {
        $urls = get_option('tc_tracked_urls', array());
        foreach ($urls as $url) {
            $response = wp_remote_get($url, array('timeout' => 10));
            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body);
                if ($data) {
                    set_transient('tc_cache_' . md5($url), $data, HOUR_IN_SECONDS);
                }
            }
        }
    }

    /**
     * WP-CLI Utility Commands.
     * 
     * Usage: wp tablecrafter [clear-cache|warm-cache]
     * 
     * @param array $args Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function cli_commands(array $args, array $assoc_args): void {
        $action = isset($args[0]) ? $args[0] : '';

        if ($action === 'clear-cache') {
            global $wpdb;
            $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_tc_cache_%'");
            WP_CLI::success('TableCrafter cache cleared.');
        } elseif ($action === 'warm-cache') {
            $this->automated_cache_refresh();
            WP_CLI::success('TableCrafter cache warmed for all tracked URLs.');
        } else {
            WP_CLI::error('Usage: wp tablecrafter [clear-cache|warm-cache]');
        }
    }

    /**
     * SSRF (Server Side Request Forgery) Prevention Helper.
     * 
     * Blocks private ranges and localhost to secure the proxy.
     * 
     * @param string $url The URL to validate.
     * @return bool True if safe, false if blocked.
     */
    private function is_safe_url(string $url): bool {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) return false;

        if (in_array(strtolower($host), array('localhost', '127.0.0.1', '[::1]'))) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $is_private = !filter_var(
                $host, 
                FILTER_VALIDATE_IP, 
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
            if ($is_private) return false;
        }

        return true;
    }
}

/**
 * Initialize TableCrafter.
 */
TableCrafter::get_instance();
