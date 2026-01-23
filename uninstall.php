<?php
/**
 * TableCrafter Uninstall
 *
 * Cleans up all plugin data when uninstalled via WordPress admin.
 * This file is called automatically by WordPress when the plugin is deleted.
 *
 * @package TableCrafter
 * @since 3.4.0
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Clean up all TableCrafter data
 *
 * Removes:
 * - Plugin options
 * - Transients (caches)
 * - Scheduled cron events
 * - Export temporary files
 */
function tablecrafter_uninstall_cleanup() {
    global $wpdb;

    // 1. Delete plugin options
    $options_to_delete = array(
        'tc_do_activation_redirect',
        'tc_tracked_urls',
        'tablecrafter_version',
        'tablecrafter_settings',
    );

    foreach ($options_to_delete as $option) {
        delete_option($option);
    }

    // 2. Delete all transients
    $transient_patterns = array(
        '_transient_tc_cache_%',
        '_transient_tc_html_%',
        '_transient_tc_export_%',
        '_transient_tc_rate_%',
        '_transient_timeout_tc_cache_%',
        '_transient_timeout_tc_html_%',
        '_transient_timeout_tc_export_%',
        '_transient_timeout_tc_rate_%',
    );

    foreach ($transient_patterns as $pattern) {
        $transients = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like($pattern)
            )
        );

        foreach ($transients as $transient) {
            delete_option($transient);
        }
    }

    // 3. Clear scheduled cron events
    $cron_hooks = array(
        'tc_refresher_cron',
        'tc_refresh_single_source',
        'tc_cleanup_exports',
    );

    foreach ($cron_hooks as $hook) {
        $timestamp = wp_next_scheduled($hook);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $hook);
        }
        // Also clear any with arguments
        wp_unschedule_hook($hook);
    }

    // 4. Clean up export temporary directory
    $upload_dir = wp_upload_dir();
    $export_dir = trailingslashit($upload_dir['basedir']) . 'tablecrafter-exports/';

    if (is_dir($export_dir)) {
        // Remove all files in the directory
        $files = glob($export_dir . '*');
        if ($files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        // Remove the directory itself
        rmdir($export_dir);
    }

    // 5. Clean up any user meta (if applicable)
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
            $wpdb->esc_like('tablecrafter_') . '%'
        )
    );

    // 6. Clean up any post meta (if applicable)
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
            $wpdb->esc_like('_tablecrafter_') . '%'
        )
    );
}

// Run cleanup
tablecrafter_uninstall_cleanup();
