=== TableCrafter ===
Contributors: fahdi
Tags: table, json, data table, api, dynamic table
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: MIT
License URI: https://opensource.org/licenses/MIT

A lightweight wrapper for the TableCrafter JavaScript library. Creates dynamic, responsive data tables from any single JSON data source.

== Description ==

**TableCrafter** is the simplest way to turn raw JSON data into beautiful, responsive HTML tables on your WordPress site. 

Designed as a lightweight wrapper around the TableCrafter JavaScript library, it allows you to display data from external APIs, local JSON files, or any publicly accessible JSON endpoint without writing a single line of code.

### 🚀 Key Features

*   **Single Data Source:** Just provide a URL to your JSON data.
*   **Zero Configuration:** Auto-detects headers and data types.
*   **Lightweight:** Built with Vanilla JavaScript – no heavy jQuery dependencies.
*   **Live Preview:** Test your data sources instantly in the WP Admin backend.
*   **Responsive:** Handles overflow gracefully on mobile devices.

### 💡 Use Cases

*   Displaying live crypto prices or stock tickers.
*   Showing employee directories from an internal API.
*   Listing product inventory from a simplified JSON feed.
*   Visualizing sales metrics or transparent public data.

== Installation ==

1.  Upload the `tablecrafter` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to the **TableCrafter** menu to preview your data and generate shortcodes.

== Usage ==

Use the shortcode in any post, page, or widget:

`[tablecrafter source="https://api.example.com/data.json"]`

### Attributes

*   `source` (required): The URL of the JSON data source.
*   `id` (optional): A unique HTML ID for the table container.

== frequently Asked Questions ==

= Does this support CSV or Excel? =

Currently, TableCrafter is designed specifically for JSON data sources. 

= My data isn't loading! =

Check the following:
1. Is the URL publicly accessible?
2. If the data is on a different domain, does it have CORS (Access-Control-Allow-Origin) headers enabled?

== Screenshots ==

1. **Admin Dashboard** - Live preview and shortcode generator.
2. **Frontend Table** - Clean, responsive table display.

== Changelog ==

= 1.0.0 =
*   Initial release.
