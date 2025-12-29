=== TableCrafter – JSON Data Tables & API Data Viewer ===
Contributors: fahdi
Tags: table, json, api, data table, datatables
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.0.1
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

*   **Universal JSON Support:** Works with any public API or `.json` file.
*   **Smart Auto-Formatting:** Automatically detects headers and formats data intelligently.
*   **Live Admin Preview:** Test your API endpoints directly in the customized dashboard.
*   **Developer Friendly:** Vanilla JavaScript core with no jQuery dependencies.

### 💡 Powerful Use Cases

*   **Crypto & Finance Portals:** Display live Bitcoin/ETH prices from CoinGecko or Binance APIs.
*   **Company Intranets:** Show employee directories fetched from your internal HR systems.
*   **E-Commerce Stock:** Display real-time product availability from external suppliers.
*   **Open Data Projects:** Visualize government datasets, weather data, or sports statistics.
*   **Affiliate Marketers:** Create dynamic comparison tables that auto-update from a central JSON feed.

### ⚡ TableCrafter vs. The Giants

| Feature | TableCrafter | TablePress / wpDataTables |
| :--- | :--- | :--- |
| **Primary Data Source** | JSON / API (Dynamic) | Manual Entry / SQL / Excel |
| **Setup Time** | < 1 Minute | 10-30 Minutes |
| **Database Impact** | None (Zero Bloat) | High (Stores data in WP DB) |
| **Performance** | Instant Client-Side | Server-Side Heavy |
| **Cost** | 100% Free | Freemium / Expensive |

If you need a static table you edit manually, use TablePress. If you need a **live, dynamic data view** from an API, **TableCrafter is the only tool you need.**

== Installation ==

1.  Upload the `tablecrafter` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Navigate to the **TableCrafter** admin menu.
4.  Paste your JSON URL to generate a shortcode.
5.  Add `[tablecrafter source="YOUR_URL"]` to any page.

== Usage ==

**Basic Shortcode:**
`[tablecrafter source="https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd"]`

**With Custom ID:**
`[tablecrafter source="..." id="my-crypto-table"]`

== Frequently Asked Questions ==

= Does this store data in my WordPress database? =
No! That's the beauty of TableCrafter. It's a lightweight *viewer*. Your data stays in the JSON source, keeping your WordPress site fast and your database clean.

= My API has Cross-Origin (CORS) errors. What do I do? =
TableCrafter runs in the browser, so your API must allow requests from your website domain. Most public APIs allow this. If you control the API, simple add `Access-Control-Allow-Origin: *` to your headers.

= Can I use this for CSV files? =
TableCrafter is optimized for the modern web (JSON). However, we are exploring CSV support for future versions. For now, we recommend converting CSV to JSON for the best performance.

== Screenshots ==

1. **Admin Dashboard Preview** - The TableCrafter settings panel with live preview functionality. Enter any JSON URL and see your data rendered instantly in the WordPress admin.
2. **Frontend Table Display** - A clean, responsive data table as it appears on your website. Automatically formatted and mobile-friendly.

== Changelog ==

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

= 1.0.1 =
This version fixes text domain issues and improves script compliance for WordPress.org.
