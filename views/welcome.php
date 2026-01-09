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
                <div style="padding: 20px 24px; border-bottom: 1px solid #f0f0f1;">
                    <h2 style="margin: 0; font-size: 18px; font-weight: 600;">🚀 Quick Start</h2>
                </div>
                <div style="padding: 32px 24px; text-align: center;">
                    <p style="font-size: 16px; margin: 0 0 24px; line-height: 1.6; color: #1e293b;">
                        Connect any JSON URL and visualize it instantly. No coding required.
                    </p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=tablecrafter-wp-data-tables')); ?>"
                        class="button button-primary button-hero"
                        style="background: #10b981; border-color: #10b981; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4); font-size: 16px;">
                        🚀 Create Your First Table Now
                    </a>
                    <p style="margin: 20px 0 0; font-size: 13px; color: #64748b; line-height: 1.5;">
                        <em>Tip: Use the "Quick Demos" in the sidebar to test with sample data.</em>
                    </p>
                </div>
            </div>

            <div class="card" style="padding: 24px; max-width: none; margin-top: 20px;">
                <h3 style="margin: 0 0 16px; font-size: 16px; font-weight: 600;">📚 Resources</h3>
                <ul style="list-style: disc; margin: 0 0 0 20px; padding: 0;">
                    <li style="margin-bottom: 10px; line-height: 1.6;"><a
                            href="https://github.com/TableCrafter/wp-data-tables" target="_blank">Documentation</a> -
                        Full usage guide.</li>
                    <li style="margin-bottom: 10px; line-height: 1.6;"><a
                            href="https://tastewp.org/plugins/tablecrafter-wp-data-tables" target="_blank">Live Demo</a>
                        - Try it in a sandbox.</li>
                    <li style="line-height: 1.6;"><a
                            href="https://wordpress.org/support/plugin/tablecrafter-wp-data-tables/"
                            target="_blank">Support Forum</a> - Get help.</li>
                </ul>
            </div>
        </div>

        <div class="tc-welcome-sidebar">
            <!-- Lead Magnet -->
            <div class="card"
                style="margin-top: 0; max-width: none; border-left: 4px solid #2563eb; background: #f8fafc; padding: 24px; display: none;">
                <div style="text-align: center; margin-bottom: 24px;">
                    <div style="font-size: 36px; margin-bottom: 12px; line-height: 1;">🎁</div>
                    <h3 style="margin: 0 0 8px; font-size: 17px; font-weight: 700; color: #1e293b;">Get 50 Free Data
                        Sources</h3>
                    <p style="margin: 0; font-size: 13px; color: #dc2626; line-height: 1.6; font-weight: 600;">Limited
                        time • Instant access</p>
                </div>
                <form id="tc-lead-form">
                    <input type="email" id="tc-email" placeholder="Your email" required
                        style="width: 100%; padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px; margin-bottom: 12px; box-sizing: border-box;">
                    <div style="text-align: center;">
                        <button type="submit" class="button button-primary"
                            style="padding: 12px 24px; font-size: 14px; font-weight: 700; justify-content: center; height: auto; background: #dc2626; border-color: #dc2626; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3); text-transform: uppercase; letter-spacing: 0.5px;">
                            Claim Free Guide Now →
                        </button>
                    </div>
                    <p style="margin: 14px 0 0; font-size: 11px; color: #94a3b8; text-align: center; line-height: 1.4;">
                        No spam • Unsubscribe anytime
                    </p>
                </form>
                <div id="tc-lead-success"
                    style="display: none; padding: 20px; background: #dcfce7; border-radius: 6px; text-align: center; margin-top: 16px;">
                    <p style="margin: 0; font-size: 14px; font-weight: 600; color: #166534;">✅ Check your email!</p>
                    <p style="margin: 8px 0 0; font-size: 12px; color: #15803d; line-height: 1.5;">Your guide is on its
                        way</p>
                </div>
            </div>

            <script>
                document.getElementById('tc-lead-form').addEventListener('submit', function (e) {
                    e.preventDefault();
                    var email = document.getElementById('tc-email').value;

                    fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=tc_subscribe_lead&email=' + encodeURIComponent(email) + '&nonce=<?php echo wp_create_nonce('tc_lead_nonce'); ?>'
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('tc-lead-form').style.display = 'none';
                                document.getElementById('tc-lead-success').style.display = 'block';
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            </script>

            <div class="card" style="max-width: none; border-left: 4px solid #6366f1; padding: 24px;">
                <h3 style="margin: 0 0 12px; font-size: 16px; font-weight: 600;">Need More Power?</h3>
                <p style="margin: 0 0 16px; line-height: 1.6;"><strong>Gravity Tables (Pro)</strong> allows you to turn
                    Gravity Forms submissions into editable,
                    searchable tables on the frontend.</p>
                <ul style="margin: 0 0 20px 20px; padding: 0; list-style: circle;">
                    <li style="margin-bottom: 6px;">Edit Entries Frontend</li>
                    <li style="margin-bottom: 6px;">Advanced Filtering</li>
                    <li>Row Deletion</li>
                </ul>
                <a href="https://checkout.freemius.com/plugin/20996/plan/35031/" target="_blank"
                    class="button button-secondary"
                    style="width: 100%; text-align: center; box-sizing: border-box;">Learn More</a>
            </div>

            <div class="card" style="max-width: none; background: #fafafa; padding: 16px;">
                <p style="margin: 0; text-align: center; font-size: 12px; color: #888;">
                    Made with ❤️ by the TableCrafter Team
                </p>
            </div>
        </div>
    </div>
</div>