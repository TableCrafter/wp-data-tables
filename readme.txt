=== TableCrafter – JSON Data Tables & API Data Viewer ===
Contributors: fahdi
Tags: table, json, api, data table, datatables
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.1.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.paypal.me/fahadmurtaza

Create dynamic, responsive HTML tables from any JSON API or file. A lightweight, no-code alternative to complex table plugins.

== Description ==

**TableCrafter** is the ultimate lightweight solution for displaying dynamic data in WordPress. 

Unlike heavy table builders that bloat your database, TableCrafter acts as a direct window to your data. Connect to any external API or JSON file, and we'll render a beautiful, responsive table instantly. 

We fill the gap between complex, expensive plugins like *wpDataTables/TablePress* and raw HTML tables. 

**Why choose TableCrafter?**

*   **🏎️ Blazing Fast:** Zero database bloat. Data is fetched on-the-fly via JavaScript.
*   **🔗 Dynamic & Live:** Perfect for financial data, stock tickers, crypto prices, or live inventory that changes every minute.
*   **📱 Mobile Ready:** Automatically responsive tables that look great on phones.
*   **🛠️ Zero Config:** Smart column detection means you just paste a URL, and we handle the rest.

### 🚀 Key Features

*   **Universal JSON Support:** Instant connection to any public API or `.json` dataset.
*   **Smart Auto-Formatting:** Beautiful tables out-of-the-box. We automatically detect logos, product images, and clickable links.
*   **Precision Curated Tables:** Only show the data that matters. Easily pick which columns to include or hide from messy API feeds.
*   **Bank-Grade Security:** Advanced protection built-in to safely handle data from third-party sources.
*   **Live Admin Preview:** Test your layouts in real-time before going live.

### 💡 Powerful Use Cases

*   **Crypto & Finance Portals:** Display live Bitcoin/ETH prices from CoinGecko or Binance APIs.
*   **Company Intranets:** Show employee directories fetched from your internal HR systems.
*   **E-Commerce Stock:** Display real-time product availability from external suppliers.
*   **Open Data Projects:** Visualize government datasets, weather data, or sports statistics.
*   **Affiliate Marketers:** Create dynamic comparison tables that auto-update from a central JSON feed.


== Installation ==

1.  Upload the `tablecrafter` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Navigate to the **TableCrafter** admin menu.
4.  Paste your JSON URL to generate a shortcode.
5.  Add `[tablecrafter source="YOUR_URL"]` to any page.

== Usage ==

**Basic Shortcode:**
`[tablecrafter source="https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd"]`

**Curated Columns (Include only specific fields):**
`[tablecrafter source="..." include="name,price,symbol"]`

**Slim View (Hide specific fields):**
`[tablecrafter source="..." exclude="description,id,meta"]`

== Frequently Asked Questions ==

= Does this store data in my WordPress database? =
No! That's the beauty of TableCrafter. It's a lightweight *viewer*. Your data stays in the JSON source, keeping your WordPress site fast and your database clean.

= My API has Cross-Origin (CORS) errors. What do I do? =
TableCrafter includes a built-in server-side proxy that automatically bypasses most CORS restrictions. If your browser blocks the request, the plugin will securely fetch the data via your WordPress server instead. This happens automatically—no configuration needed!

= Can I use this for CSV files? =
TableCrafter is optimized for the modern web (JSON). However, we are exploring CSV support for future versions. For now, we recommend converting CSV to JSON for the best performance.

== Screenshots ==

1. **Admin Dashboard Preview** - The TableCrafter settings panel with live preview functionality. Enter any JSON URL and see your data rendered instantly in the WordPress admin.
2. **Frontend Table Display** - A clean, responsive data table as it appears on your website. Automatically formatted and mobile-friendly.

== Changelog ==

= 1.1.1 =
* **Precision Curation:** Added the ability to include/exclude specific columns for cleaner tables.
* **Visual Tables:** Added automatic rendering for images, logos, and links.
* **Hardened Security:** Implemented advanced security filters for safer data handling.
* **Performance Polish:** Optimized the core engine for faster, smoother table rendering.

= 1.1.0 =
* Feat: Added Server-Side Proxy to bypass CORS restrictions.
* Feat: Added Automated Background Cache Warming via WP-Cron.
* Feat: Added WP-CLI support for cache management.
* Fixed: Resolved shortcode rendering issues in various theme environments.
* Fixed: Prevented "smart quote" conversion in documentation to ensure copy-paste reliability.
* Fixed: Optimized frontend initialization to prevent race conditions.
* Docs: Updated branding and donation links.
* Docs: Removed comparison section.

= 1.0.1 =
* Refactored script handling for full WP.org directory compliance.
* Moved all inline JavaScript to external files.
* Implemented wp_localize_script for safer data handling in admin.
* Optimized shortcode renderer to eliminate inline JS injection.

= 1.0.0 =
*   Initial release.
*   Added live admin previewer.
*   Released smart column detection.
== Upgrade Notice ==

= 1.1.1 =
Security and Feature Update: Adds XSS protection and column filtering support.

= 1.1.0 =
Major update: Includes CORS bypass proxy and background cache warming for better performance.

= 1.0.1 =
This version fixes text domain issues and improves script compliance for WordPress.org.
