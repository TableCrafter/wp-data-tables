<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap tc-welcome-wrap">
    <div class="tc-welcome-header"
        style="background: #fff; padding: 40px; margin: 20px 0 0; text-align: center; border: 1px solid #ccd0d4; box-shadow: 0 1px 4px rgba(0,0,0,0.05);">
        <h1 style="font-size: 32px; font-weight: 700; margin: 0 0 10px; color: #1d2327;">Welcome to TableCrafter</h1>
        <p style="font-size: 18px; color: #646970; margin: 0;">You're 30 seconds away from your first dynamic table.</p>
    </div>

    <div class="tc-welcome-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
        <div class="tc-welcome-main">
            <div class="card" style="padding: 0; overflow: hidden; max-width: none;">
                <div style="padding: 20px; border-bottom: 1px solid #f0f0f1;">
                    <h2 style="margin: 0; font-size: 18px;">🚀 Quick Start</h2>
                </div>
                <div style="padding: 30px; text-align: center;">
                    <p style="font-size: 16px; margin-bottom: 30px;">
                        Connect any JSON URL and visualize it instantly. No coding required.
                    </p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=tablecrafter-wp-data-tables')); ?>"
                        class="button button-primary button-hero">
                        Create Your First Table &rarr;
                    </a>
                    <p style="margin-top: 20px; font-size: 13px; color: #666;">
                        <em>Tip: Use the "Quick Demos" in the sidebar to test with sample data.</em>
                    </p>
                </div>
            </div>

            <div class="card" style="padding: 20px; max-width: none; margin-top: 20px;">
                <h3 style="margin-top: 0;">📚 Resources</h3>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><a href="https://github.com/TableCrafter/wp-data-tables" target="_blank">Documentation</a> -
                        Full usage guide.</li>
                    <li><a href="https://tastewp.org/plugins/tablecrafter-wp-data-tables" target="_blank">Live Demo</a>
                        - Try it in a sandbox.</li>
                    <li><a href="https://wordpress.org/support/plugin/tablecrafter-wp-data-tables/"
                            target="_blank">Support Forum</a> - Get help.</li>
                </ul>
            </div>
        </div>

        <div class="tc-welcome-sidebar">
            <div class="card" style="margin-top: 0; max-width: none; border-left: 4px solid #6366f1;">
                <h3 style="margin-top: 0;">Need More Power?</h3>
                <p><strong>Gravity Tables (Pro)</strong> allows you to turn Gravity Forms submissions into editable,
                    searchable tables on the frontend.</p>
                <ul style="margin: 10px 0 15px 20px; list-style: circle;">
                    <li>Edit Entries Frontend</li>
                    <li>Advanced Filtering</li>
                    <li>Row Deletion</li>
                </ul>
                <a href="https://checkout.freemius.com/plugin/20996/plan/35031/" target="_blank"
                    class="button button-secondary">Learn More</a>
            </div>

            <div class="card" style="max-width: none; background: #fafafa;">
                <p style="margin: 0; text-align: center; font-size: 12px; color: #888;">
                    Made with ❤️ by the TableCrafter Team
                </p>
            </div>
        </div>
    </div>
</div>