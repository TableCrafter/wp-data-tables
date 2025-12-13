<?php
/**
 * Plugin Name: TableCrafter
 * Plugin URI: https://github.com/TableCrafter/tablecrafter-wp
 * Description: A lightweight WordPress wrapper for the TableCrafter JavaScript library. Creates dynamic data tables from a single data source.
 * Version: 1.0.0
 * Author: TableCrafter Team
 * License: MIT
 * Text Domain: tablecrafter
 */

if (!defined('ABSPATH')) {
    exit;
}

define('TABLECRAFTER_VERSION', '1.0.0');
define('TABLECRAFTER_URL', plugin_dir_url(__FILE__));
define('TABLECRAFTER_PATH', plugin_dir_path(__FILE__));

class TableCrafter {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('admin_enqueue_scripts', array($this, 'register_assets')); // Register for admin too
        add_shortcode('tablecrafter', array($this, 'render_table'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'TableCrafter',
            'TableCrafter',
            'manage_options',
            'tablecrafter',
            array($this, 'render_admin_page'),
            'dashicons-editor-table',
            20
        );
    }

    public function render_admin_page() {
        // Enqueue assets for the preview
        wp_enqueue_script('tablecrafter-lib');
        wp_enqueue_style('tablecrafter-style');
        ?>
        <div class="wrap">
            <h1>TableCrafter</h1>
            <p>Welcome to TableCrafter! Use the shortcode below to display tables.</p>
            
            <div class="card">
                <h2>Live Preview</h2>
                <p>Enter a JSON data URL to preview the table:</p>
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <input type="text" id="tc-preview-url" placeholder="https://api.example.com/data.json" style="flex: 1; padding: 8px;">
                    <button id="tc-preview-btn" class="button button-primary">Preview Table</button>
                    <button id="tc-copy-shortcode" class="button">Copy Shortcode</button>
                </div>
                
                <div id="tc-preview-container" style="min-height: 200px; border: 1px dashed #ccc; padding: 20px; display: flex; align-items: center; justify-content: center;">
                    <p style="color: #999;">Table preview will appear here...</p>
                </div>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h2>Demo Data</h2>
                <p>Try these built-in demo files (click to fill preview):</p>
                <ul class="tc-demo-links">
                    <li><a href="#" data-url="<?php echo TABLECRAFTER_URL . 'demo-data/users.json'; ?>"><?php echo TABLECRAFTER_URL . 'demo-data/users.json'; ?></a> (Users)</li>
                    <li><a href="#" data-url="<?php echo TABLECRAFTER_URL . 'demo-data/products.json'; ?>"><?php echo TABLECRAFTER_URL . 'demo-data/products.json'; ?></a> (Products)</li>
                    <li><a href="#" data-url="<?php echo TABLECRAFTER_URL . 'demo-data/metrics.json'; ?>"><?php echo TABLECRAFTER_URL . 'demo-data/metrics.json'; ?></a> (Metrics)</li>
                </ul>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const urlInput = document.getElementById('tc-preview-url');
                const previewBtn = document.getElementById('tc-preview-btn');
                const copyBtn = document.getElementById('tc-copy-shortcode');
                const container = document.getElementById('tc-preview-container');
                const demoLinks = document.querySelectorAll('.tc-demo-links a');

                // Load demo URL on click
                demoLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        urlInput.value = this.dataset.url;
                        previewBtn.click();
                    });
                });

                // Preview functionality
                previewBtn.addEventListener('click', function() {
                    const url = urlInput.value.trim();
                    if (!url) {
                        alert('Please enter a valid URL');
                        return;
                    }

                    // Reset container
                    container.innerHTML = '';
                    
                    if (typeof TableCrafter !== 'undefined') {
                        // Create a unique ID for the inner container
                        const tableId = 'tc-preview-' + Date.now();
                        container.innerHTML = `<div id="${tableId}" class="tablecrafter-container">Loading...</div>`;
                        
                        // Init TableCrafter
                        new TableCrafter({
                            selector: '#' + tableId,
                            source: url
                        });
                    } else {
                        container.innerHTML = '<p style="color: red;">TableCrafter library not loaded.</p>';
                    }
                });

                // Copy shortcode functionality
                copyBtn.addEventListener('click', function() {
                    const url = urlInput.value.trim();
                    if (!url) {
                        alert('Please enter a URL first');
                        return;
                    }
                    const shortcode = `[tablecrafter source="${url}"]`;
                    navigator.clipboard.writeText(shortcode).then(function() {
                        const originalText = copyBtn.innerText;
                        copyBtn.innerText = 'Copied!';
                        setTimeout(() => copyBtn.innerText = originalText, 2000);
                    });
                });
            });
            </script>
        </div>
        <?php
    }
    
    /**
     * Register frontend assets
     */
    public function register_assets() {
        wp_register_script(
            'tablecrafter-lib',
            TABLECRAFTER_URL . 'assets/js/tablecrafter.js',
            array(), // Dependencies (none for now, purely native JS requested)
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
     * Shortcode to render the table container
     * Usage: [tablecrafter source="/path/to/data.json"]
     */
    public function render_table($atts) {
        $atts = shortcode_atts(array(
            'source' => '', // The single data source URL
            'id' => 'tc-' . uniqid()
        ), $atts, 'tablecrafter');
        
        if (empty($atts['source'])) {
            return '<p>Error: TableCrafter requires a "source" attribute.</p>';
        }
        
        // Enqueue assets only when shortcode is used
        wp_enqueue_script('tablecrafter-lib');
        wp_enqueue_style('tablecrafter-style');
        
        // Output container
        ob_start();
        ?>
        <div id="<?php echo esc_attr($atts['id']); ?>" class="tablecrafter-container" data-source="<?php echo esc_url($atts['source']); ?>">
            Loading TableCrafter...
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof TableCrafter !== 'undefined') {
                    new TableCrafter({
                        selector: '#<?php echo esc_js($atts['id']); ?>',
                        source: '<?php echo esc_js($atts['source']); ?>'
                    });
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }
}

// Initialize
TableCrafter::get_instance();
