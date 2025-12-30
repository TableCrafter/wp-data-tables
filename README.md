# TableCrafter – JSON Data Tables & API Data Viewer

**Contributors:** fahdi
**Tags:** table, json, api, data table, datatables
**Requires at least:** 5.0
**Tested up to:** 6.9
**Stable tag:** 1.2.1
**Requires PHP:** 7.4
**License:** GPLv2 or later
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html
**Donate link:** https://www.paypal.me/fahadmurtaza

Create dynamic, responsive HTML tables from any JSON API or file. A lightweight, no-code alternative to complex table plugins. Now with **Server-Side Rendering (SSR)** for maximum SEO and performance.

## Description

**TableCrafter** is the ultimate lightweight solution for displaying dynamic data in WordPress. 

Unlike heavy table builders that bloat your database, TableCrafter acts as a direct window to your data. Connect to any external API or JSON file, and we'll render a beautiful, responsive table instantly. 

### 🚀 Now with SSR Engine (v1.2.0)
TableCrafter now renders your tables on the server before the page even loads. 
*   **SEO Optimized:** Content is visible to search engine crawlers immediately.
*   **Zero Loading Flicker:** No more "Loading..." spinners for your users.
*   **Performance Caching:** Built-in transient caching reduces external API hits.

**Why choose TableCrafter?**
*   **🏎️ Blazing Fast:** Zero database bloat. Data is pre-rendered for instant viewing.
*   **🔗 Dynamic & Live:** Perfect for financial data, stock tickers, crypto prices, or live inventory.
*   **📱 Mobile Ready:** Automatically responsive tables that look great on phones.
*   **🛠️ Zero Config:** Smart column detection handles headers and formatting intelligently.

### 💡 Key Features

*   **Universal JSON Support:** Instant connection to any public API or `.json` dataset.
*   **Smart Auto-Formatting:** Automatically detects logos, product images, and clickable links in PHP and JS.
*   **Precision Curated Tables:** Only show the data that matters via `include`/`exclude` attributes.
*   **Bank-Grade Security:** Strict HTML escaping and sanitization for safe remote data handling.

## Installation

1.  Upload the `tablecrafter` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Navigate to the **TableCrafter** admin menu.
4.  Paste your JSON URL to generate a shortcode.
5.  Add `[tablecrafter source="YOUR_URL"]` to any page.

## Usage

The `[tablecrafter]` shortcode is highly flexible. Use the following attributes to customize your data display:

*   **source**: (Required) The URL to your JSON API or file.
*   **include**: (Optional) A comma-separated list of keys you want to show.
*   **exclude**: (Optional) A comma-separated list of keys you want to hide.
*   **id**: (Optional) A custom CSS ID for the table container.

### Examples:

**1. Basic Display**
Show all data from an API:
```text
[tablecrafter source="https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd"]
```

**2. Specific Columns (Curated View)**
Only show the name, price, and symbol:
```text
[tablecrafter source="..." include="name,current_price,symbol"]
```

## Changelog

### 1.2.1
* **Instant TTFB:** Implemented Stale-While-Revalidate (SWR) caching logic.
* **Resilience:** Systems now serve stale data while refreshing in the background, ensuring tables load instantly even if APIs are slow.
* **Smart Refresh:** Added non-blocking background refresh via WP-Cron/WP-Events.

### 1.2.0
* Major: Implemented Server-Side Rendering (SSR) Engine for instant page loads and SEO optimization.
* Feat: Added smart link and image detection in PHP to match frontend library.
* Feat: Integrated transient-based caching (1-hour) for rendered HTML fragments.
* Optimized: Balanced Hybrid loading - tables render on server, JS adds interactivity.

### 1.1.2
* Docs: Significantly expanded shortcode documentation with detailed attribute descriptions and examples.

### 1.1.1
* **Precision Curation:** Added the ability to include/exclude specific columns.
* **Visual Tables:** Added automatic rendering for images, logos, and links.
* **Hardened Security:** Implemented advanced security filters.
* **Performance Polish:** Optimized the core engine.

### 1.1.0
* Feat: Added Server-Side Proxy to bypass CORS restrictions.
* Feat: Added Automated Background Cache Warming via WP-Cron.
* Feat: Added WP-CLI support for cache management.

### 1.0.0
* Initial release.
