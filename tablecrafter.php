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
            <h1 class="wp-heading-inline">TableCrafter</h1>
            <p>Generate dynamic HTML tables from any JSON data source.</p>
            <hr class="wp-header-end">

            <div class="tc-admin-layout" style="display: flex; gap: 20px; margin-top: 20px; align-items: flex-start;">
                
                <!-- Sidebar Controls -->
                <div class="tc-sidebar" style="flex: 0 0 350px;">
                    <!-- Configuration Card -->
                    <div class="card" style="margin: 0 0 20px 0; max-width: none;">
                        <h2>Settings</h2>
                        <div style="margin-bottom: 15px;">
                            <label for="tc-preview-url" style="font-weight: 600; display: block; margin-bottom: 5px;">Data Source URL</label>
                            <input type="text" id="tc-preview-url" class="widefat" placeholder="https://api.example.com/data.json">
                            <p class="description">Must be a publicly accessible JSON endpoint.</p>
                        </div>
                        
                        <div style="display: flex; gap: 10px; margin-top: 15px;">
                            <button id="tc-preview-btn" class="button button-primary button-large" style="flex: 1;">Preview Table</button>
                        </div>
                    </div>

                    <!-- Usage info -->
                     <div class="card" style="margin: 0 0 20px 0; max-width: none;">
                        <h2>Usage</h2>
                        <p>Copy the shortcode below to use this table:</p>
                        <code id="tc-shortcode-display" style="display: block; padding: 10px; background: #f0f0f1; margin: 10px 0;">[tablecrafter source="..."]</code>
                        <button id="tc-copy-shortcode" class="button button-secondary" style="width: 100%;">Copy Shortcode</button>
                     </div>

                    <!-- Demos -->
                    <div class="card" style="margin: 0; max-width: none;">
                        <h2>Quick Demos</h2>
                        <p>Click a dataset to load:</p>
                        <ul class="tc-demo-links" style="margin: 0;">
                            <li style="margin-bottom: 8px;"><a href="#" class="button" style="width: 100%; text-align: left;" data-url="<?php echo TABLECRAFTER_URL . 'demo-data/users.json'; ?>">👤 User Directory (JSON)</a></li>
                            <li style="margin-bottom: 8px;"><a href="#" class="button" style="width: 100%; text-align: left;" data-url="<?php echo TABLECRAFTER_URL . 'demo-data/products.json'; ?>">📦 Product Inventory (JSON)</a></li>
                            <li style="margin-bottom: 0;"><a href="#" class="button" style="width: 100%; text-align: left;" data-url="<?php echo TABLECRAFTER_URL . 'demo-data/metrics.json'; ?>">📈 Sales Metrics (JSON)</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Main Preview Area -->
                <div class="tc-preview-area" style="flex: 1; min-width: 0;">
                    <div class="card" style="margin: 0; max-width: none; min-height: 500px; display: flex; flex-direction: column;">
                        <h2 style="border-bottom: 1px solid #f0f0f1; padding-bottom: 15px; margin-bottom: 15px; margin-top: 0;">Live Preview</h2>
                        
                        <div id="tc-preview-wrap" style="flex: 1; overflow: auto; background: #fff;">
                            <div id="tc-preview-container" style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666;">
                                <div style="text-align: center;">
                                    <span class="dashicons dashicons-editor-table" style="font-size: 48px; width: 48px; height: 48px; color: #ddd;"></span>
                                    <p>Select a demo or enter a URL to generate a table.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const urlInput = document.getElementById('tc-preview-url');
                const previewBtn = document.getElementById('tc-preview-btn');
                const copyBtn = document.getElementById('tc-copy-shortcode');
                const shortcodeDisplay = document.getElementById('tc-shortcode-display');
                const container = document.getElementById('tc-preview-container');
                const demoLinks = document.querySelectorAll('.tc-demo-links a');

                // Update shortcode display on input
                urlInput.addEventListener('input', function() {
                    const url = this.value.trim() || 'URL';
                    shortcodeDisplay.innerText = `[tablecrafter source="${url}"]`;
                });

                // Load demo URL on click
                demoLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        urlInput.value = this.dataset.url;
                        // Trigger input event to update shortcode
                        urlInput.dispatchEvent(new Event('input'));
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
                    // Reset styling in case centered was used
                    container.style.display = 'block';
                    
                    if (typeof TableCrafter !== 'undefined') {
                        // Create a unique ID for the inner container
                        const tableId = 'tc-preview-' + Date.now();
                        container.innerHTML = `<div id="${tableId}" class="tablecrafter-container">Loading data from source...</div>`;
                        
                        // Init TableCrafter
                        new TableCrafter({
                            selector: '#' + tableId,
                            source: url
                        });
                    } else {
                        container.innerHTML = '<div class="notice notice-error inline"><p>TableCrafter library not loaded.</p></div>';
                    }
                });

                // Copy shortcode functionality
                copyBtn.addEventListener('click', function() {
                    const text = shortcodeDisplay.innerText;
                    
                    // Robust copy function with fallback
                    const copyToClipboard = async (text) => {
                        try {
                            if (navigator.clipboard && window.isSecureContext) {
                                await navigator.clipboard.writeText(text);
                            } else {
                                throw new Error('Clipboard API unavailable');
                            }
                        } catch (err) {
                            // Fallback for HTTP/non-secure contexts
                            const textArea = document.createElement("textarea");
                            textArea.value = text;
                            textArea.style.position = "fixed";
                            textArea.style.left = "-9999px";
                            document.body.appendChild(textArea);
                            textArea.focus();
                            textArea.select();
                            try {
                                document.execCommand('copy');
                                textArea.remove();
                            } catch (e) {
                                console.error('Copy failed', e);
                                textArea.remove();
                                alert('Failed to copy to clipboard. Please copy manually.');
                                return;
                            }
                        }
                        
                        // Success feedback
                        const originalText = copyBtn.innerText;
                        copyBtn.innerText = 'Copied to Clipboard!';
                        setTimeout(() => copyBtn.innerText = originalText, 2000);
                    };

                    copyToClipboard(text);
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
