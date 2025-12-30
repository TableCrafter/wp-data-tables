<?php
/**
 * Plugin Name: TableCrafter – JSON Data Tables & API Data Viewer
 * Plugin URI: https://github.com/TableCrafter/wp-data-tables
 * Description: A lightweight WordPress wrapper for the TableCrafter JavaScript library. Creates dynamic data tables from a single data source.
 * Version: 1.4.0
 * Author: TableCrafter Team
 * Author URI: https://github.com/fahdi
 * License: GPLv2 or later
 * Text Domain: tablecrafter-wp-data-tables
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TABLECRAFTER_VERSION', '1.4.0');
define('TABLECRAFTER_URL', plugin_dir_url(__FILE__));
define('TABLECRAFTER_PATH', plugin_dir_path(__FILE__));

class TableCrafter {
    
    private static $instance = null;
    
    /**
     * Get singleton instance.
     * 
     * @return TableCrafter
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor.
     */
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('init', array($this, 'register_block'));
        add_shortcode('tablecrafter', array($this, 'render_table'));
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // AJAX Proxy Handlers
        add_action('wp_ajax_tc_proxy_fetch', array($this, 'ajax_proxy_fetch'));
        add_action('wp_ajax_nopriv_tc_proxy_fetch', array($this, 'ajax_proxy_fetch'));

        // Automated Cron
        add_action('tc_refresher_cron', array($this, 'automated_cache_refresh'));
        add_action('tc_refresh_single_source', array($this, 'refresh_source_cache'), 10, 1);
        
        if (!wp_next_scheduled('tc_refresher_cron')) {
            wp_schedule_event(time(), 'hourly', 'tc_refresher_cron');
        }

        // WP-CLI Support
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('tablecrafter', array($this, 'cli_commands'));
        }
    }

    /**
     * Add admin menu page.
     */
    public function add_admin_menu() {
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
     */
    public function render_admin_page() {
        // Enqueue assets for the preview
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
                
                <!-- Sidebar Controls -->
                <div class="tc-sidebar" style="flex: 0 0 350px;">
                    <!-- Configuration Card -->
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

                    <!-- Usage info -->
                     <div class="card" style="margin: 0 0 20px 0; max-width: none;">
                        <h2><?php esc_html_e('Usage', 'tablecrafter-wp-data-tables'); ?></h2>
                        <p><?php esc_html_e('Copy the shortcode below to use this table:', 'tablecrafter-wp-data-tables'); ?></p>
                        <code id="tc-shortcode-display" style="display: block; padding: 10px; background: #f0f0f1; margin: 10px 0;">[tablecrafter source="..."]</code>
                        <button id="tc-copy-shortcode" class="button button-secondary" style="width: 100%;"><?php esc_html_e('Copy Shortcode', 'tablecrafter-wp-data-tables'); ?></button>
                     </div>

                    <!-- Demos -->
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

                <!-- Main Preview Area -->
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
            
            <!-- Admin JS is now enqueued via enqueue_admin_assets -->
        </div>
        <?php
    }
    
    /**
     * Register frontend assets.
     */
    public function register_assets() {
        wp_register_script(
            'tablecrafter-lib',
            TABLECRAFTER_URL . 'assets/js/tablecrafter.js',
            array(), // Dependencies (none for now, purely native JS requested)
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
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our settings page
        if (strpos($hook, 'tablecrafter-wp-data-tables') === false) {
            return;
        }

        wp_enqueue_script(
            'tablecrafter-admin',
            TABLECRAFTER_URL . 'assets/js/admin.js',
            array('jquery'), // Basic dependency
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

        // We also need the frontend lib for the preview
        $this->register_assets();
        wp_enqueue_script('tablecrafter-lib');
        wp_enqueue_style('tablecrafter-style');
    }
    


    /**
     * Register Gutenberg Block
     */
    public function register_block() {
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
                'id'      => array('type' => 'string', 'default' => ''),
            ),
        ));
    }

    /**
     * Block Render Callback (Reuses Shortcode Logic)
     */
    public function render_block_callback($attributes) {
        // Ensure ID is set
        if (empty($attributes['id'])) {
            $attributes['id'] = 'tc-block-' . uniqid();
        }
        return $this->render_table($attributes);
    }

    /**
     * Shortcode to render the table container.
     * Usage: [tablecrafter source="/path/to/data.json"]
     * 
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render_table($atts) {
        $atts = shortcode_atts(array(
            'source'  => '', // The single data source URL
            'id'      => 'tc-' . uniqid(),
            'include' => '', // Comma-separated list of keys to include
            'exclude' => '', // Comma-separated list of keys to exclude
            'root'    => '', // Path to the data array in the JSON response
            'search'  => 'false', // Whether to show the search bar
            'per_page' => 0    // Rows per page (0 for all)
        ), $atts, 'tablecrafter');
        
        // Sanitize the source URL
        $atts['source'] = esc_url_raw($atts['source']);
        
        if (empty($atts['source'])) {
            return '<p>' . esc_html__('Error: TableCrafter requires a "source" attribute.', 'tablecrafter-wp-data-tables') . '</p>';
        }

        // --- SSR ENGINE START (SWR Implementation) ---
        $cache_key = 'tc_html_' . md5($atts['source'] . $atts['include'] . $atts['exclude']);
        $cache_data = get_transient($cache_key);
        $html_content = '';

        if ($cache_data !== false) {
            // We have a cache! Extract HTML and check if it's "stale"
            $html_content = isset($cache_data['html']) ? $cache_data['html'] : '';
            $timestamp = isset($cache_data['time']) ? $cache_data['time'] : 0;
            
            // If cache is older than 5 minutes, trigger a background refresh
            if (time() - $timestamp > (5 * MINUTE_IN_SECONDS)) {
                if (!wp_next_scheduled('tc_refresh_single_source', array($atts))) {
                    wp_schedule_single_event(time(), 'tc_refresh_single_source', array($atts));
                }
            }
        } else {
            // No cache at all - perform synchronous fetch for the first time
            $html_content = $this->fetch_and_render_php($atts);
            if ($html_content) {
                set_transient($cache_key, array(
                    'html' => $html_content,
                    'time' => time()
                ), HOUR_IN_SECONDS);
            }
        }
        // --- SSR ENGINE END ---
        
        // Enqueue assets
        $this->register_assets();
        wp_enqueue_script('tablecrafter-frontend');
        wp_enqueue_style('tablecrafter-style');
        
        // Output container
        ob_start();
        ?>
        <div id="<?php echo esc_attr($atts['id']); ?>" 
             class="tablecrafter-container" 
             data-source="<?php echo esc_url($atts['source']); ?>"
             data-include="<?php echo esc_attr($atts['include']); ?>"
             data-exclude="<?php echo esc_attr($atts['exclude']); ?>"
             data-root="<?php echo esc_attr($atts['root']); ?>"
             data-search="<?php echo esc_attr($atts['search']); ?>"
             data-per-page="<?php echo esc_attr($atts['per_page']); ?>"
             data-ssr="true">
            <?php echo $html_content ? $html_content : '<div class="tc-loading">' . esc_html__('Loading TableCrafter...', 'tablecrafter-wp-data-tables') . '</div>'; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Server-Side Fetch and Render
     */
    private function fetch_and_render_php($atts) {
        $response = wp_remote_get($atts['source'], array('timeout' => 15));
        if (is_wp_error($response)) return false;

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!is_array($data) || empty($data)) return false;

        // Navigate to the root path if provided
        if (!empty($atts['root'])) {
            $path = explode('.', $atts['root']);
            foreach ($path as $segment) {
                if (isset($data[$segment])) {
                    $data = $data[$segment];
                } else {
                    return false; // Path not found
                }
            }
        }

        // Ensure we ended up with an array of items
        if (!is_array($data) || empty($data) || !is_array(reset($data))) return false;

        // Handle Include/Exclude
        $include = !empty($atts['include']) ? array_map('trim', explode(',', $atts['include'])) : array();
        $exclude = !empty($atts['exclude']) ? array_map('trim', explode(',', $atts['exclude'])) : array();

        $headers = array_keys(reset($data));
        if (!empty($include)) {
            $headers = array_intersect($headers, $include);
        }
        if (!empty($exclude)) {
            $headers = array_diff($headers, $exclude);
        }

        if (empty($headers)) return false;

        // Build HTML
        $html = '<table class="tc-table">';
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . esc_html($this->format_header_php($header)) . '</th>';
        }
        $html .= '</tr></thead>';
        $html .= '<tbody>';
        
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($headers as $header) {
                $val = isset($row[$header]) ? $row[$header] : '';
                $html .= '<td>' . $this->render_value_php($val) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }

    private function format_header_php($str) {
        return ucwords(str_replace('_', ' ', $str));
    }

    private function render_value_php($val) {
        if (is_null($val) || is_bool($val)) return '';
        if (is_array($val) || is_object($val)) return '[Data]';

        $str = trim((string)$val);

        // Detect Images
        if (preg_match('/\.(jpeg|jpg|gif|png|webp|svg|bmp)$/i', $str) || strpos($str, 'data:image') === 0) {
            return sprintf('<img src="%s" style="max-width: 100px; height: auto; display: block;">', esc_url($str));
        }

        // Detect Links
        if (strpos($str, 'http://') === 0 || strpos($str, 'https://') === 0) {
            return sprintf('<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url($str), esc_html($str));
        }

        return esc_html($str);
    }

    /**
     * AJAX Proxy Fetch Handler
     * 
     * Fetches remote JSON data server-side to bypass CORS and implement caching.
     */
    public function ajax_proxy_fetch() {
        check_ajax_referer('tc_proxy_nonce', 'nonce');

        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        
        if (empty($url)) {
            wp_send_json_error('Invalid URL');
        }

        // Cache parameters
        $cache_key = 'tc_cache_' . md5($url);
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            wp_send_json_success($cached_data);
        }

        // Fetch fresh data
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'sslverify' => false // For compatibility with some older servers
        ));

        if (is_wp_error($response)) {
            wp_send_json_error('TableCrafter Proxy Error: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if ($data === null) {
            wp_send_json_error('TableCrafter Proxy Error: Invalid JSON response from source.');
        }

        // Cache for 1 hour
        set_transient($cache_key, $data, HOUR_IN_SECONDS);

        // Automated Tracking: Keep track of used URLs for background warming
        $this->track_url($url);

        wp_send_json_success($data);
    }

    /**
     * Automated URL Tracking
     */
    private function track_url($url) {
        $urls = get_option('tc_tracked_urls', array());
        if (!is_array($urls)) $urls = array();
        
        if (!in_array($url, $urls)) {
            $urls[] = $url;
            update_option('tc_tracked_urls', array_slice($urls, -50)); // Keep last 50
        }
    }

    /**
     * Refresh a specific source cache (Triggered by SWR)
     */
    public function refresh_source_cache($atts) {
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
     * Automated Cache Refresher (WP-Cron Task)
     */
    public function automated_cache_refresh() {
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
     * WP-CLI Commands for Automation
     */
    public function cli_commands($args, $assoc_args) {
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
}

// Initialize
TableCrafter::get_instance();
