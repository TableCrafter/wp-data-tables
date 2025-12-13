=== TableCrafter ===
Contributors: fahdi
Tags: table, json, data table, api, dynamic table, chart, csv, datatable, table builder
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: MIT
License URI: https://opensource.org/licenses/MIT

Create dynamic, responsive data tables from JSON data sources. A lightweight alternative for developers and no-code users.

== Description ==

**TableCrafter** is a modern, lightweight WordPress table plugin designed to instantly transform JSON data into beautiful, responsive HTML tables. 

Unlike heavy table builders that require manual data entry, TableCrafter connects directly to your data source (API, JSON file, or URL), making it the perfect tool for displaying dynamic content that updates automatically.

### 🚀 Why TableCrafter?

We built TableCrafter to be the **fastest way to display external data** in WordPress.

*   **No Complex Setup:** Just paste a URL. No mapping columns or configuring SQL queries.
*   **Performance First:** Built with Vanilla JavaScript. No jQuery heavy lifting.
*   **Zero Maintenance:** When your JSON source updates, your table updates instantly.

### 🌟 Key Features

*   **Single Source of Truth:** Connect to any publicly accessible JSON URL.
*   **Auto-Adaptive Columns:** Automatically detects headers and formats data.
*   **Mobile Responsive:** Smart scrolling for small screens.
*   **Developer Friendly:** Minimal codebase that won't bloat your site.
*   **Live Preview:** Test your APIs directly in the WordPress dashboard before publishing.

### 💡 Perfect For...

*   **Crypto & Finance:** Display live coin prices or stock tickers.
*   **Company Directories:** Sync employee lists from internal HR systems.
*   **E-Commerce Inventory:** Show product stock levels from external feeds.
*   **Public Data:** Visualize government or open-source datasets.
*   **Sports Stats:** Show league standings or player statistics.

### ⚡ vs. The Competition

While plugins like **wpDataTables** are excellent for complex, multi-source data visualization (Excel, CSV, MySQL), **TableCrafter** focuses on doing one thing perfectly: **Rendering JSON data effortlessly.**

If you need charts, complex editing, or MySQL queries, we recommend checking out premium alternatives. But if you need **speed, simplicity, and JSON support**, TableCrafter is for you.

== Installation ==

1.  Upload the `tablecrafter` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to the **TableCrafter** menu to preview your data and generate different shortcodes.

== Usage ==

Use the shortcode in any post, page, or widget:

`[tablecrafter source="https://api.example.com/data.json"]`

### Attributes

*   `source` (required): The URL of the JSON data source.
*   `id` (optional): A unique HTML ID for the table container.

== Frequently Asked Questions ==

= Does this support CSV or Excel files? =

Currently, TableCrafter is optimized specifically for JSON data. For CSV or Excel support, we recommend converting your data to JSON or using a heavier plugin like wpDataTables.

= My data isn't loading! =

Please check:
1.  Is the URL publicly accessible?
2.  Does the API endpoint allow Cross-Origin (CORS) requests?
3.  Is the JSON formatted as an array of objects? (e.g., `[{"id":1,"name":"Test"},...]`)

= Can I style the tables? =

Yes! TableCrafter uses minimal CSS variables that inherit from your theme. You can also target the `.tablecrafter-container` class in your custom CSS.

== Screenshots ==

1. **Live Preview Dashboard** - Instantly test your JSON data and copy shortcodes.
2. **Frontend Table** - Clean, responsive data display on your website.

== Changelog ==

= 1.0.0 =
*   Initial release.
