<?php
/**
 * Plugin Name: TableCrafter – JSON Data Tables & API Data Viewer
 * Plugin URI: https://github.com/TableCrafter/wp-data-tables
 * Description: A lightweight WordPress wrapper for the TableCrafter JavaScript library. Creates dynamic data tables from a single data source.
 * Version: 1.0.1
 * Author: TableCrafter Team
 * Author URI: https://github.com/fahdi
 * License: GPLv2 or later
 * Text Domain: tablecrafter-wp-data-tables
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TABLECRAFTER_VERSION', '1.0.1');
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
        add_shortcode('tablecrafter-wp-data-tables', array($this, 'render_table'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
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
     * Shortcode to render the table container.
     * Usage: [tablecrafter source="/path/to/data.json"]
     * 
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render_table($atts) {
        $atts = shortcode_atts(array(
            'source' => '', // The single data source URL
            'id' => 'tc-' . uniqid()
        ), $atts, 'tablecrafter-wp-data-tables');
        
        // Sanitize the source URL
        $atts['source'] = esc_url_raw($atts['source']);
        
        if (empty($atts['source'])) {
            return '<p>' . esc_html__('Error: TableCrafter requires a "source" attribute.', 'tablecrafter-wp-data-tables') . '</p>';
        }
        
        // Enqueue assets only when shortcode is used
        wp_enqueue_script('tablecrafter-frontend');
        wp_enqueue_style('tablecrafter-style');
        
        // Output container
        ob_start();
        ?>
        <div id="<?php echo esc_attr($atts['id']); ?>" class="tablecrafter-container" data-source="<?php echo esc_url($atts['source']); ?>">
            <?php esc_html_e('Loading TableCrafter...', 'tablecrafter-wp-data-tables'); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize
TableCrafter::get_instance();
