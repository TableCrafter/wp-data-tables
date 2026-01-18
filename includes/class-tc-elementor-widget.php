<?php
/**
 * TableCrafter Elementor Widget
 * 
 * Native Elementor widget providing seamless integration with live preview,
 * visual controls, and professional workflow for the 12+ million Elementor users.
 * 
 * @package TableCrafter
 * @since 3.2.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * TableCrafter Elementor Widget Class
 * 
 * Provides native Elementor integration with live preview and visual controls
 */

// Only define the widget class if Elementor's Widget_Base is available
if (class_exists('\Elementor\Widget_Base')) {

class TC_Elementor_Widget extends \Elementor\Widget_Base
{
    /**
     * Widget name
     */
    public function get_name()
    {
        return 'tablecrafter-data-table';
    }

    /**
     * Widget title
     */
    public function get_title()
    {
        return esc_html__('TableCrafter Data Table', 'tablecrafter-wp-data-tables');
    }

    /**
     * Widget icon
     */
    public function get_icon()
    {
        return 'eicon-table';
    }

    /**
     * Widget categories
     */
    public function get_categories()
    {
        return ['general', 'tablecrafter'];
    }

    /**
     * Widget keywords
     */
    public function get_keywords()
    {
        return ['table', 'data', 'json', 'api', 'csv', 'google sheets', 'tablecrafter'];
    }

    /**
     * Widget dependencies
     */
    public function get_script_depends()
    {
        return ['tablecrafter-lib', 'tablecrafter-frontend', 'tc-elementor-preview'];
    }

    /**
     * Widget styles
     */
    public function get_style_depends()
    {
        return ['tablecrafter-style'];
    }

    /**
     * Register widget controls
     */
    protected function register_controls()
    {
        $this->register_data_source_controls();
        $this->register_display_controls();
        $this->register_advanced_controls();
        $this->register_style_controls();
    }

    /**
     * Data Source Controls Section
     */
    protected function register_data_source_controls()
    {
        $this->start_controls_section(
            'section_data_source',
            [
                'label' => esc_html__('Data Source', 'tablecrafter-wp-data-tables'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'source_type',
            [
                'label' => esc_html__('Source Type', 'tablecrafter-wp-data-tables'),
                'type' => Controls_Manager::SELECT,
                'default' => 'url',
                'options' => [
                    'url' => esc_html__('API URL / JSON File', 'tablecrafter-wp-data-tables'),
                    'google_sheets' => esc_html__('Google Sheets', 'tablecrafter-wp-data-tables'),
                    'csv_file' => esc_html__('CSV File', 'tablecrafter-wp-data-tables'),
                ],
            ]
        );

        $this->add_control(
            'data_source',
            [
                'label' => esc_html__('Data Source URL', 'tablecrafter-wp-data-tables'),
                'type' => Controls_Manager::URL,
                'placeholder' => 'https://api.example.com/data.json',
                'description' => esc_html__('Enter the URL of your JSON API, CSV file, or Google Sheets public URL.', 'tablecrafter-wp-data-tables'),
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'google_sheets_help',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => '<div style="background: #e3f2fd; padding: 10px; border-radius: 4px; border-left: 4px solid #2196f3; margin: 10px 0;">
                    <strong>Google Sheets Setup:</strong><br>
                    1. Make your sheet public<br>
                    2. Use format: <code>https://docs.google.com/spreadsheets/d/[ID]/gviz/tq?tqx=out:csv</code><br>
                    <a href="https://tablecrafter.com/docs/google-sheets" target="_blank">View detailed guide →</a>
                </div>',
                'condition' => [
                    'source_type' => 'google_sheets',
                ],
            ]
        );

        $this->add_control(
            'root_path',
            [
                'label' => esc_html__('JSON Root Path', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'data.results',
                'description' => esc_html__('If your data is nested in JSON, specify the path (e.g., "data.results").', 'tablecrafter-wp-data-tables'),
                'condition' => [
                    'source_type' => ['url'],
                ],
            ]
        );

        $this->add_control(
            'include_columns',
            [
                'label' => esc_html__('Include Columns', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'name,email,date',
                'description' => esc_html__('Comma-separated list of columns to include. Leave empty to show all.', 'tablecrafter-wp-data-tables'),
            ]
        );

        $this->add_control(
            'exclude_columns',
            [
                'label' => esc_html__('Exclude Columns', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'id,internal_notes',
                'description' => esc_html__('Comma-separated list of columns to exclude.', 'tablecrafter-wp-data-tables'),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Display Controls Section
     */
    protected function register_display_controls()
    {
        $this->start_controls_section(
            'section_display',
            [
                'label' => esc_html__('Display Options', 'tablecrafter-wp-data-tables'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'enable_search',
            [
                'label' => esc_html__('Enable Global Search', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'description' => esc_html__('Add a search box above the table for filtering data.', 'tablecrafter-wp-data-tables'),
            ]
        );

        $this->add_control(
            'enable_filters',
            [
                'label' => esc_html__('Enable Advanced Filters', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'description' => esc_html__('Automatic column-based filters (dropdowns, date ranges, etc.).', 'tablecrafter-wp-data-tables'),
            ]
        );

        $this->add_control(
            'enable_export',
            [
                'label' => esc_html__('Enable Data Export', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => '',
                'description' => esc_html__('Allow users to export table data as CSV, Excel, or PDF.', 'tablecrafter-wp-data-tables'),
            ]
        );

        $this->add_control(
            'per_page',
            [
                'label' => esc_html__('Rows Per Page', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 25,
                'min' => 1,
                'max' => 1000,
                'description' => esc_html__('Number of rows to show per page. Set to 0 to disable pagination.', 'tablecrafter-wp-data-tables'),
            ]
        );

        $this->add_control(
            'sort_column',
            [
                'label' => esc_html__('Default Sort Column', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'name',
                'description' => esc_html__('Column name to sort by initially.', 'tablecrafter-wp-data-tables'),
            ]
        );

        $this->add_control(
            'sort_order',
            [
                'label' => esc_html__('Sort Order', 'tablecrafter-wp-data-tables'),
                'type' => Controls_Manager::SELECT,
                'default' => 'asc',
                'options' => [
                    'asc' => esc_html__('Ascending (A-Z, 1-9)', 'tablecrafter-wp-data-tables'),
                    'desc' => esc_html__('Descending (Z-A, 9-1)', 'tablecrafter-wp-data-tables'),
                ],
                'condition' => [
                    'sort_column!' => '',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Advanced Controls Section
     */
    protected function register_advanced_controls()
    {
        $this->start_controls_section(
            'section_advanced',
            [
                'label' => esc_html__('Advanced Features', 'tablecrafter-wp-data-tables'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'auto_refresh',
            [
                'label' => esc_html__('Auto-Refresh Data', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => '',
                'description' => esc_html__('Automatically refresh data from the source at specified intervals.', 'tablecrafter-wp-data-tables'),
            ]
        );

        $this->add_control(
            'refresh_interval',
            [
                'label' => esc_html__('Refresh Interval (seconds)', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 300,
                'min' => 30,
                'max' => 86400,
                'description' => esc_html__('How often to refresh data (30 seconds to 24 hours).', 'tablecrafter-wp-data-tables'),
                'condition' => [
                    'auto_refresh' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'cache_duration',
            [
                'label' => esc_html__('Cache Duration (minutes)', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 15,
                'min' => 0,
                'max' => 1440,
                'description' => esc_html__('How long to cache data for performance. Set to 0 to disable caching.', 'tablecrafter-wp-data-tables'),
            ]
        );

        $this->add_control(
            'enable_live_preview',
            [
                'label' => esc_html__('Enable Live Preview', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'description' => esc_html__('Show live table data in Elementor editor. Disable if you have performance issues.', 'tablecrafter-wp-data-tables'),
            ]
        );

        $this->add_control(
            'preview_rows',
            [
                'label' => esc_html__('Preview Rows', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 1,
                'max' => 25,
                'description' => esc_html__('Number of rows to show in live preview (for performance).', 'tablecrafter-wp-data-tables'),
                'condition' => [
                    'enable_live_preview' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'loading_message',
            [
                'label' => esc_html__('Loading Message', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Loading data...',
                'description' => esc_html__('Message shown while data is being fetched.', 'tablecrafter-wp-data-tables'),
            ]
        );

        $this->add_control(
            'error_message',
            [
                'label' => esc_html__('Error Message', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Unable to load data. Please try again.',
                'description' => esc_html__('Message shown when data loading fails.', 'tablecrafter-wp-data-tables'),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Style Controls Section
     */
    protected function register_style_controls()
    {
        // Table Styling
        $this->start_controls_section(
            'section_table_style',
            [
                'label' => esc_html__('Table Styling', 'tablecrafter-wp-data-tables'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'table_typography',
                'selector' => '{{WRAPPER}} .tc-table',
            ]
        );

        $this->add_control(
            'table_background',
            [
                'label' => esc_html__('Background Color', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .tc-table-container' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'table_border',
                'selector' => '{{WRAPPER}} .tc-table-container',
            ]
        );

        $this->add_control(
            'table_border_radius',
            [
                'label' => esc_html__('Border Radius', 'tablecrafter-wp-data-tables'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .tc-table-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'table_shadow',
                'selector' => '{{WRAPPER}} .tc-table-container',
            ]
        );

        $this->end_controls_section();

        // Header Styling
        $this->start_controls_section(
            'section_header_style',
            [
                'label' => esc_html__('Header Styling', 'tablecrafter-wp-data-tables'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'header_typography',
                'selector' => '{{WRAPPER}} .tc-table th',
            ]
        );

        $this->add_control(
            'header_background',
            [
                'label' => esc_html__('Background Color', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f8f9fa',
                'selectors' => [
                    '{{WRAPPER}} .tc-table th' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'header_color',
            [
                'label' => esc_html__('Text Color', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#495057',
                'selectors' => [
                    '{{WRAPPER}} .tc-table th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'header_padding',
            [
                'label' => esc_html__('Padding', 'tablecrafter-wp-data-tables'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'default' => [
                    'top' => '12',
                    'right' => '16',
                    'bottom' => '12',
                    'left' => '16',
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tc-table th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Rows Styling
        $this->start_controls_section(
            'section_rows_style',
            [
                'label' => esc_html__('Rows Styling', 'tablecrafter-wp-data-tables'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'row_background',
            [
                'label' => esc_html__('Row Background', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .tc-table td' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'row_hover_background',
            [
                'label' => esc_html__('Row Hover Background', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f8f9fa',
                'selectors' => [
                    '{{WRAPPER}} .tc-table tr:hover td' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'row_color',
            [
                'label' => esc_html__('Text Color', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .tc-table td' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'row_padding',
            [
                'label' => esc_html__('Cell Padding', 'tablecrafter-wp-data-tables'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'default' => [
                    'top' => '12',
                    'right' => '16',
                    'bottom' => '12',
                    'left' => '16',
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .tc-table td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'row_border',
            [
                'label' => esc_html__('Row Border', 'tablecrafter-wp-data-tables'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e1e5e9',
                'selectors' => [
                    '{{WRAPPER}} .tc-table td' => 'border-bottom: 1px solid {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the widget output on the frontend
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        
        // Generate unique ID for this widget instance
        $widget_id = 'tc-elementor-' . $this->get_id();
        
        // Build shortcode attributes
        $shortcode_atts = $this->build_shortcode_attributes($settings);
        
        // Output the table
        echo '<div class="tc-elementor-widget-wrapper" id="' . esc_attr($widget_id) . '">';
        
        if (empty($settings['data_source']['url'])) {
            // Show configuration prompt in editor
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div style="padding: 40px; text-align: center; background: #f8f9fa; border: 2px dashed #ddd; border-radius: 8px;">';
                echo '<h3 style="margin: 0 0 10px; color: #666;">TableCrafter Data Table</h3>';
                echo '<p style="margin: 0; color: #999;">Please configure your data source in the widget settings.</p>';
                echo '</div>';
            } else {
                echo '<p>Please configure the TableCrafter data source.</p>';
            }
        } else {
            // Render the actual table
            echo do_shortcode($shortcode_atts);
        }
        
        echo '</div>';
    }

    /**
     * Build shortcode attributes from widget settings
     */
    protected function build_shortcode_attributes($settings)
    {
        $atts = ['[tablecrafter'];
        
        // Data source
        if (!empty($settings['data_source']['url'])) {
            $atts[] = 'source="' . esc_attr($settings['data_source']['url']) . '"';
        }
        
        // Root path for JSON
        if (!empty($settings['root_path'])) {
            $atts[] = 'root="' . esc_attr($settings['root_path']) . '"';
        }
        
        // Column filters
        if (!empty($settings['include_columns'])) {
            $atts[] = 'include="' . esc_attr($settings['include_columns']) . '"';
        }
        
        if (!empty($settings['exclude_columns'])) {
            $atts[] = 'exclude="' . esc_attr($settings['exclude_columns']) . '"';
        }
        
        // Display options
        $atts[] = 'search="' . ($settings['enable_search'] === 'yes' ? 'true' : 'false') . '"';
        $atts[] = 'filters="' . ($settings['enable_filters'] === 'yes' ? 'true' : 'false') . '"';
        $atts[] = 'export="' . ($settings['enable_export'] === 'yes' ? 'true' : 'false') . '"';
        
        // Pagination
        if (!empty($settings['per_page'])) {
            $atts[] = 'per_page="' . intval($settings['per_page']) . '"';
        }
        
        // Sorting
        if (!empty($settings['sort_column'])) {
            $sort_order = !empty($settings['sort_order']) ? $settings['sort_order'] : 'asc';
            $atts[] = 'sort="' . esc_attr($settings['sort_column']) . ':' . esc_attr($sort_order) . '"';
        }
        
        // Auto-refresh
        if ($settings['auto_refresh'] === 'yes') {
            $atts[] = 'auto_refresh="true"';
            if (!empty($settings['refresh_interval'])) {
                $atts[] = 'refresh_interval="' . intval($settings['refresh_interval']) . '"';
            }
        }
        
        // Cache duration
        if (isset($settings['cache_duration'])) {
            $atts[] = 'cache="' . intval($settings['cache_duration']) . '"';
        }
        
        // Preview mode for Elementor editor
        if (isset($settings['preview_rows']) && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            $atts[] = 'elementor_preview="true"';
            $atts[] = 'preview_rows="' . intval($settings['preview_rows']) . '"';
        }
        
        $atts[] = ']';
        
        return implode(' ', $atts);
    }

    /**
     * Render widget output in the editor (live preview)
     */
    protected function content_template()
    {
        ?>
        <#
        var widgetId = 'tc-elementor-' + view.getID();
        var dataSource = settings.data_source ? settings.data_source.url : '';
        var enableLivePreview = settings.enable_live_preview === 'yes';
        var previewRows = settings.preview_rows || 5;
        
        // Build configuration object for preview
        var previewConfig = {
            source: dataSource,
            root: settings.root_path || '',
            include: settings.include_columns || '',
            exclude: settings.exclude_columns || '',
            enableSearch: settings.enable_search === 'yes',
            enableFilters: settings.enable_filters === 'yes',
            enableExport: settings.enable_export === 'yes',
            perPage: previewRows,
            sortColumn: settings.sort_column || '',
            sortOrder: settings.sort_order || 'asc'
        };
        #>
        
        <div class="tc-elementor-widget-wrapper tc-elementor-preview" id="{{ widgetId }}" data-preview-config="{{ JSON.stringify(previewConfig) }}">
            <# if ( ! dataSource ) { #>
                <div class="tc-preview-placeholder" style="padding: 40px; text-align: center; background: #f8f9fa; border: 2px dashed #ddd; border-radius: 8px;">
                    <div class="tc-preview-icon" style="font-size: 48px; color: #ddd; margin-bottom: 15px;">📊</div>
                    <h3 style="margin: 0 0 10px; color: #666; font-size: 18px;">TableCrafter Data Table</h3>
                    <p style="margin: 0; color: #999; font-size: 14px;">Please configure your data source in the widget settings.</p>
                </div>
            <# } else if ( enableLivePreview ) { #>
                <div class="tc-live-preview-container">
                    <div class="tc-preview-header" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f8f9fa; border-radius: 8px 8px 0 0; border: 1px solid #e1e5e9; margin-bottom: 0;">
                        <div class="tc-preview-info">
                            <strong style="color: #1976d2; font-size: 14px;">📊 Live Preview</strong>
                            <span style="color: #666; font-size: 12px; margin-left: 8px;">{{ previewRows }} rows • {{ dataSource.replace(/^https?:\/\//, '') }}</span>
                        </div>
                        <div class="tc-preview-features" style="font-size: 12px; color: #666;">
                            <# if ( settings.enable_search === 'yes' ) { #><span class="tc-feature-badge">🔍 Search</span> <# } #>
                            <# if ( settings.enable_filters === 'yes' ) { #><span class="tc-feature-badge">⚡ Filters</span> <# } #>
                            <# if ( settings.enable_export === 'yes' ) { #><span class="tc-feature-badge">📥 Export</span> <# } #>
                            <# if ( settings.auto_refresh === 'yes' ) { #><span class="tc-feature-badge">🔄 Auto-refresh</span> <# } #>
                        </div>
                    </div>
                    
                    <div class="tc-preview-content" id="tc-preview-{{ widgetId }}" style="min-height: 200px; border: 1px solid #e1e5e9; border-top: none; border-radius: 0 0 8px 8px; position: relative;">
                        <div class="tc-preview-loading" style="display: flex; align-items: center; justify-content: center; height: 200px; color: #666;">
                            <div style="text-align: center;">
                                <div class="tc-spinner" style="border: 3px solid #f3f3f3; border-top: 3px solid #2196f3; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin: 0 auto 10px;"></div>
                                <p style="margin: 0; font-size: 14px;">Loading table preview...</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tc-preview-footer" style="padding: 8px 16px; background: #fafafa; border-radius: 0 0 8px 8px; font-size: 11px; color: #999; text-align: center;">
                        Full table with all features will be displayed on the frontend
                    </div>
                </div>
            <# } else { #>
                <div class="tc-preview-disabled" style="padding: 20px; text-align: center; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px;">
                    <h4 style="margin: 0 0 10px; color: #856404; font-size: 16px;">📊 TableCrafter Data Table</h4>
                    <p style="margin: 0 0 10px; font-size: 14px; color: #856404;">
                        <strong>Source:</strong> {{ dataSource.replace(/^https?:\/\//, '') }}
                    </p>
                    <p style="margin: 0; font-size: 12px; color: #6c757d;">
                        Live preview disabled. Enable in Advanced settings or view on frontend.
                    </p>
                </div>
            <# } #>
        </div>
        
        <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .tc-feature-badge {
            background: #e3f2fd;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 10px;
            margin-right: 4px;
            display: inline-block;
        }
        
        .tc-preview-content .tc-table {
            margin: 0;
            border-radius: 0;
        }
        
        .tc-preview-content .tc-table th:first-child {
            border-radius: 0;
        }
        
        .tc-preview-content .tc-table th:last-child {
            border-radius: 0;
        }
        
        .tc-elementor-preview .tc-table-container {
            border: none;
            border-radius: 0;
        }
        </style>
        <?php
    }
}

} // End if (class_exists('\\Elementor\\Widget_Base'))

/**
 * Register TableCrafter Elementor Widget with backward compatibility
 * @param object $widgets_manager Optional widget manager instance for new hook
 */
function register_tc_elementor_widget($widgets_manager = null)
{
    // Debug logging in WordPress environment
    if (function_exists('error_log')) {
        error_log('TableCrafter: Attempting to register Elementor widget');
    }
    
    // Make sure Elementor classes are available
    if (!class_exists('\Elementor\Plugin') || !class_exists('\Elementor\Widget_Base')) {
        if (function_exists('error_log')) {
            error_log('TableCrafter: Elementor classes not available');
        }
        return;
    }

    // Check if our widget class is available (only defined if Elementor is properly loaded)
    if (!class_exists('TC_Elementor_Widget')) {
        if (function_exists('error_log')) {
            error_log('TableCrafter: TC_Elementor_Widget class not available');
        }
        return;
    }

    // Use provided widget manager or get instance
    if (!$widgets_manager) {
        $widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
    }
    
    if (function_exists('error_log')) {
        error_log('TableCrafter: Creating widget instance');
    }
    
    $widget = new TC_Elementor_Widget();

    // Backward compatibility for Elementor versions
    if (method_exists($widgets_manager, 'register')) {
        // Elementor 3.5+ - Use new register method
        $widgets_manager->register($widget);
        if (function_exists('error_log')) {
            error_log('TableCrafter: Widget registered using new method (register)');
        }
    } elseif (method_exists($widgets_manager, 'register_widget_type')) {
        // Elementor < 3.5 - Use deprecated method for backward compatibility
        $widgets_manager->register_widget_type($widget);
        if (function_exists('error_log')) {
            error_log('TableCrafter: Widget registered using deprecated method (register_widget_type)');
        }
    } else {
        if (function_exists('error_log')) {
            error_log('TableCrafter: No valid registration method found on widget manager');
        }
    }
}

/**
 * Register widget using appropriate hook based on Elementor version
 */
function tc_register_elementor_hooks()
{
    // Use the most compatible registration approach
    // For Elementor 3.5+ use the new hook, otherwise use the deprecated one
    if (defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '3.5.0', '>=')) {
        // New method for Elementor 3.5+
        add_action('elementor/widgets/register', 'register_tc_elementor_widget');
    } else {
        // Fallback for older versions
        add_action('elementor/widgets/widgets_registered', 'register_tc_elementor_widget');
    }
}

// Register widget hooks - this file is loaded via elementor/loaded hook
// Only register if we're in a WordPress environment
// NOTE: Hook registration is now done externally to avoid issues during testing

/**
 * Add TableCrafter category to Elementor
 */
function add_tc_elementor_category($elements_manager)
{
    // Safety check
    if (!$elements_manager || !is_object($elements_manager)) {
        return;
    }

    $elements_manager->add_category(
        'tablecrafter',
        [
            'title' => esc_html__('TableCrafter', 'tablecrafter-wp-data-tables'),
            'icon' => 'eicon-table',
        ]
    );
}

// Register category - this file is loaded after Elementor is available
// NOTE: Category registration is now done externally to avoid issues during testing
?>