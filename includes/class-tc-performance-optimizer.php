<?php
/**
 * TableCrafter Performance Optimizer
 * 
 * Handles virtual scrolling, lazy loading, and performance optimizations
 * for large datasets to improve rendering speed and user experience.
 * 
 * @package TableCrafter
 * @since 3.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TC_Performance_Optimizer
{
    /**
     * Virtual scrolling threshold - datasets larger than this use virtual scrolling
     */
    const VIRTUAL_SCROLL_THRESHOLD = 500;
    
    /**
     * Number of rows to render in viewport
     */
    const VIRTUAL_ROWS_RENDERED = 50;
    
    /**
     * Buffer rows above and below viewport
     */
    const VIRTUAL_BUFFER_ROWS = 10;
    
    /**
     * Initialize performance optimizations
     */
    public static function init()
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_performance_assets']);
        add_filter('tablecrafter_render_data', [self::class, 'optimize_rendering'], 10, 3);
    }
    
    /**
     * Enqueue performance optimization assets
     */
    public static function enqueue_performance_assets()
    {
        wp_enqueue_script(
            'tc-performance-optimizer',
            TABLECRAFTER_URL . 'assets/js/performance-optimizer.js',
            ['tablecrafter-lib'],
            TABLECRAFTER_VERSION,
            true
        );
        
        wp_localize_script('tc-performance-optimizer', 'tcPerformance', [
            'virtualScrollThreshold' => self::VIRTUAL_SCROLL_THRESHOLD,
            'virtualRowsRendered' => self::VIRTUAL_ROWS_RENDERED,
            'virtualBufferRows' => self::VIRTUAL_BUFFER_ROWS,
            'enableVirtualScrolling' => true,
            'enableLazyImages' => true,
            'enableDeferredRendering' => true
        ]);
    }
    
    /**
     * Optimize data rendering based on dataset size and type
     * 
     * @param array $data The data to render
     * @param array $headers The table headers
     * @param array $options Rendering options
     * @return array Optimized rendering data
     */
    public static function optimize_rendering($data, $headers, $options = [])
    {
        if (empty($data) || !is_array($data)) {
            return $data;
        }
        
        $dataset_size = count($data);
        $optimization_meta = [];
        
        // Determine if virtual scrolling should be enabled
        $use_virtual_scroll = $dataset_size > self::VIRTUAL_SCROLL_THRESHOLD;
        
        if ($use_virtual_scroll) {
            $optimization_meta = [
                'virtual_scrolling' => true,
                'total_rows' => $dataset_size,
                'rendered_rows' => self::VIRTUAL_ROWS_RENDERED,
                'buffer_rows' => self::VIRTUAL_BUFFER_ROWS,
                'estimated_row_height' => self::estimate_row_height($data, $headers),
                'performance_mode' => 'virtual_scroll'
            ];
            
            // For virtual scrolling, only return metadata and first batch
            return [
                'data' => array_slice($data, 0, self::VIRTUAL_ROWS_RENDERED + self::VIRTUAL_BUFFER_ROWS),
                'optimization_meta' => $optimization_meta,
                'full_dataset_hash' => md5(serialize($data)) // For cache validation
            ];
        }
        
        // For smaller datasets, apply lightweight optimizations
        $optimized_data = self::apply_lightweight_optimizations($data, $headers, $options);
        
        return [
            'data' => $optimized_data,
            'optimization_meta' => [
                'virtual_scrolling' => false,
                'total_rows' => $dataset_size,
                'performance_mode' => 'standard',
                'optimizations_applied' => [
                    'lazy_images' => true,
                    'deferred_formatting' => true,
                    'memory_efficient_rendering' => true
                ]
            ]
        ];
    }
    
    /**
     * Estimate average row height for virtual scrolling calculations
     * 
     * @param array $data Sample data
     * @param array $headers Table headers
     * @return int Estimated row height in pixels
     */
    private static function estimate_row_height($data, $headers)
    {
        // Base row height
        $base_height = 45; // pixels
        
        // Sample first few rows to estimate content density
        $sample_rows = array_slice($data, 0, min(5, count($data)));
        $max_content_length = 0;
        $has_images = false;
        
        foreach ($sample_rows as $row) {
            foreach ($headers as $header) {
                if (isset($row[$header])) {
                    $value = $row[$header];
                    
                    // Check for images
                    if (is_string($value) && self::is_image_url($value)) {
                        $has_images = true;
                    }
                    
                    // Check content length
                    $content_length = strlen((string) $value);
                    $max_content_length = max($max_content_length, $content_length);
                }
            }
        }
        
        // Adjust height based on content characteristics
        if ($has_images) {
            $base_height += 20; // Extra height for images
        }
        
        if ($max_content_length > 100) {
            $base_height += 15; // Extra height for longer content
        }
        
        return $base_height;
    }
    
    /**
     * Apply lightweight optimizations for standard rendering
     * 
     * @param array $data The data to optimize
     * @param array $headers The table headers
     * @param array $options Rendering options
     * @return array Optimized data
     */
    private static function apply_lightweight_optimizations($data, $headers, $options)
    {
        return array_map(function($row) use ($headers) {
            $optimized_row = [];
            
            foreach ($headers as $header) {
                if (isset($row[$header])) {
                    $value = $row[$header];
                    
                    // Optimize image URLs for lazy loading
                    if (is_string($value) && self::is_image_url($value)) {
                        $optimized_row[$header] = [
                            'type' => 'lazy_image',
                            'src' => $value,
                            'placeholder' => self::generate_image_placeholder($value)
                        ];
                    }
                    // Mark long text content for deferred rendering
                    elseif (is_string($value) && strlen($value) > 200) {
                        $optimized_row[$header] = [
                            'type' => 'long_text',
                            'preview' => substr($value, 0, 150) . '...',
                            'full_content' => $value
                        ];
                    }
                    // Standard value
                    else {
                        $optimized_row[$header] = $value;
                    }
                } else {
                    $optimized_row[$header] = '';
                }
            }
            
            return $optimized_row;
        }, $data);
    }
    
    /**
     * Check if a string is an image URL
     * 
     * @param string $url URL to check
     * @return bool True if image URL
     */
    private static function is_image_url($url)
    {
        if (!is_string($url)) {
            return false;
        }
        
        $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        
        return in_array($extension, $image_extensions) || strpos($url, 'data:image') === 0;
    }
    
    /**
     * Generate a placeholder for lazy-loaded images
     * 
     * @param string $image_url Original image URL
     * @return string Placeholder data URL
     */
    private static function generate_image_placeholder($image_url)
    {
        // Generate a simple gray placeholder
        return 'data:image/svg+xml;base64,' . base64_encode(
            '<svg width="100" height="60" xmlns="http://www.w3.org/2000/svg">' .
            '<rect width="100%" height="100%" fill="#f0f0f0"/>' .
            '<text x="50%" y="50%" fill="#999" text-anchor="middle" dy=".3em" font-size="12">Loading...</text>' .
            '</svg>'
        );
    }
    
    /**
     * Get performance metrics for monitoring
     * 
     * @return array Performance metrics
     */
    public static function get_performance_metrics()
    {
        return [
            'virtual_scroll_threshold' => self::VIRTUAL_SCROLL_THRESHOLD,
            'memory_usage' => memory_get_usage(true),
            'peak_memory_usage' => memory_get_peak_usage(true),
            'php_memory_limit' => ini_get('memory_limit'),
            'time_limit' => ini_get('max_execution_time')
        ];
    }
    
    /**
     * AJAX handler for virtual scroll data requests
     */
    public static function ajax_virtual_scroll_data()
    {
        check_ajax_referer('tc_proxy_nonce', 'nonce');
        
        if (!current_user_can('read')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $source = sanitize_text_field($_POST['source'] ?? '');
        $start_index = intval($_POST['start_index'] ?? 0);
        $count = intval($_POST['count'] ?? self::VIRTUAL_ROWS_RENDERED);
        $dataset_hash = sanitize_text_field($_POST['dataset_hash'] ?? '');
        
        if (empty($source)) {
            wp_send_json_error('Source required');
        }
        
        // Validate count to prevent abuse
        $count = min($count, self::VIRTUAL_ROWS_RENDERED * 2);
        
        // Fetch full dataset (from cache if available)
        $cache_key = 'tc_virtual_data_' . md5($source . TABLECRAFTER_VERSION);
        $full_data = get_transient($cache_key);
        
        if ($full_data === false) {
            // Need to refetch data - this should be rare
            $tablecrafter = TableCrafter::get_instance();
            $reflection = new ReflectionClass($tablecrafter);
            $method = $reflection->getMethod('fetch_data_from_source');
            $method->setAccessible(true);
            
            $result = $method->invoke($tablecrafter, $source);
            
            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }
            
            $full_data = $result;
            set_transient($cache_key, $full_data, HOUR_IN_SECONDS);
        }
        
        // Validate dataset hasn't changed
        if ($dataset_hash && md5(serialize($full_data)) !== $dataset_hash) {
            wp_send_json_error('Dataset changed', ['reload_required' => true]);
        }
        
        // Return requested slice
        $data_slice = array_slice($full_data, $start_index, $count);
        
        wp_send_json_success([
            'data' => $data_slice,
            'start_index' => $start_index,
            'count' => count($data_slice),
            'total_rows' => count($full_data),
            'has_more' => ($start_index + count($data_slice)) < count($full_data)
        ]);
    }
}

// Initialize the performance optimizer
TC_Performance_Optimizer::init();

// Register AJAX handlers
add_action('wp_ajax_tc_virtual_scroll_data', [TC_Performance_Optimizer::class, 'ajax_virtual_scroll_data']);
add_action('wp_ajax_nopriv_tc_virtual_scroll_data', [TC_Performance_Optimizer::class, 'ajax_virtual_scroll_data']);