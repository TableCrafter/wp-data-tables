=== TableCrafter – WordPress Data Tables & Dynamic Content Plugin ===
Contributors: fahdi
Tags: table, json, api, data table, datatables
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.9.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.paypal.me/fahadmurtaza

Create dynamic, SEO-friendly HTML tables from any JSON API or file. Features native Gutenberg support, SSR, Live Search, and Pagination.

== Description ==

**TableCrafter** is the ultimate lightweight solution for displaying dynamic data in WordPress. 

Unlike heavy table builders that bloat your database, TableCrafter acts as a direct, high-performance window to your data. Connect to any external API or JSON file, and we'll render a beautiful, responsive table instantly. 

We fill the gap between complex, expensive plugins and raw HTML tables. 

**Why choose TableCrafter?**

*   **⚡ High Performance:** Powered by **SSR (Server-Side Rendering)** and **SWR (Stale-While-Revalidate)** caching for sub-100ms load times.
*   **🔍 SEO-First:** Data is rendered in PHP, making every cell crawlable by search engines like Google.
*   **🧱 Gutenberg Ready:** Native WordPress block with a visual sidebar and live preview.
*   **📄 Smart Pagination:** Effortlessly navigate thousands of rows with built-in client-side pagination.
*   **⚡ Live Search:** Real-time filtering allows users to find data instantly as they type.
*   **🛡️ Hardened Security:** Built-in SSRF protection and strict authorization for all remote data operations.

### 💡 Powerful Use Cases

*   **Crypto & Finance:** Display live Bitcoin/ETH prices from CoinGecko or Binance.
*   **E-Commerce Stock:** Show real-time availability from external supplier feeds.
*   **Company Directories:** Visualize employee lists fetched from internal HR platforms.
*   **Live Metrics:** Show sales data, weather stats, or sports scores in real-time.

== Installation ==

1.  Search for **TableCrafter** in your WordPress dashboard or upload the folder to `/wp-content/plugins/`.
2.  Activate the plugin.
3.  Go to **TableCrafter** in your admin menu to preview a table or use the **TableCrafter Block** in the Gutenberg editor.
4.  Optionally use the shortcode: `[tablecrafter source="YOUR_JSON_URL"]`.

== Usage ==

The `[tablecrafter]` shortcode is highly flexible:

*   `source`: (Required) The URL to your JSON API or file.
*   `root`: (Optional) Path to the data array in the JSON response (e.g., `root="data.results"`).
*   `search`: (Optional) Toggle the live search bar (`true` or `false`).
*   `per_page`: (Optional) Number of rows to show per page (e.g., `per_page="10"`).
*   `include`: (Optional) Comma-separated list of keys you want to show.
*   `exclude`: (Optional) Comma-separated list of keys you want to hide.

### Examples:

**1. Live Search & Pagination**
Enable interactive features for a large dataset:

    [tablecrafter source="..." search="true" per_page="10"]

**2. Specific Root Path**
Connect to an API where results are nested:

    [tablecrafter source="..." root="items.list"]

**3. Specific Columns (Curated View)**
Limit data from heavy APIs:

    [tablecrafter source="..." include="name,price,symbol"]


### 🚀 Upgrade to Pro: Gravity Tables

Unlock the full potential of your data with **[Advanced Data Tables for Gravity Forms](https://checkout.freemius.com/plugin/20996/plan/35031/?trial=paid)** — the ultimate solution for managing Gravity Forms entries.

**Why Upgrade?**
*   ✏️ **Frontend Editing:** Let users update their own entries directly from the table.
*   🛡️ **Role-Based Permissions:** Control exactly who can view, edit, or delete data.
*   ⚡ **Bulk Actions:** Delete, approve, or modify hundreds of entries in one click.
*   🔍 **Advanced Filtering:** Logic-based filters, date ranges, and multi-select dropdowns.
*   🎨 **Conditional Formatting:** Highlight rows or cells based on their values (e.g., "Status = Overdue").
*   📥 **Pro Export:** Export filtered views to Excel, CSV, or PDF.
*   ♾️ **Unlimited Freedom:** No limits on tables, columns, or rows.

**[Start your 7-day free trial today!](https://checkout.freemius.com/plugin/20996/plan/35031/?trial=paid)**

== Frequently Asked Questions ==

= Does this store data in my WordPress database? =
No. TableCrafter is a lightweight viewer. It fetches data dynamically and caches it temporarily (SWR) to keep your database clean and your site fast.

= How does the SWR caching work? =
TableCrafter serves "stale" (cached) data instantly while refreshing the source in the background. This ensures your tables load in milliseconds without waiting for slow third-party APIs.

= Is it secure? =
Yes. We implement SSRF protection to prevent access to internal networks and use strict WordPress capability checks for all proxy operations.

== Screenshots ==

1. **Gutenberg Block** - The native TableCrafter block with visual sidebar controls.
2. **Interactive Frontend** - A live table showing the search bar and pagination footer.
3. **Admin Dashboard** - The shortcode generator and preview playground.


== Changelog ==


== Changelog ==

= 2.0.0 =
*   **Engine Upgrade:** Unified plugin with `tablecrafter-core` 1.1.0 for improved stability and feature parity.
*   **New Feature:** Added Smart Hydration to prevent "loading flickers" on Server-Side Rendered (SSR) tables.
*   **Performance:** Background data fetching for smoother interactions.

= 1.9.2 =
*   **Performance:** Added intelligent debouncing to Live Search (300ms). Prevents UI freezing when typing rapidly, especially on large datasets.

= 1.9.1 =
*   **Performance:** Fixed API caching issue where the renderer ignored pre-warmed data. Now uses read-through caching for instant loads.
*   **Docs:** Added information about premium Gravity Tables addon.

= 1.9.0 =
* **Smart Nested Data Rendering:** Automatically handles Arrays and Objects. Nested items are now rendered as elegant tags or badges instead of `[object Object]`.
* **Plugin Rebranding:** Officially renamed to "TableCrafter – WordPress Data Tables & Dynamic Content Plugin" for better alignment with feature scope.

= 1.8.0 =
* **Custom Column Aliasing:** Rename headers directly in the shortcode using `include="key:My Label"` syntax.
* **Smart Export:** CSV exports now respect your custom column aliases.
* **Mobile Reflow:** Mobile card view now uses professional aliases for labels.

= 1.7.0 =
* **Smart Data Formatting:** Automatically detects and formats **Dates** (to locale string), **Booleans** (Yes/No badges), and **Emails** (mailto links).
* **UI Polish:** Added professional styles for Boolean badges and links.

= 1.6.0 =
* **Data Export:** Added toolbar with "Export to CSV" and "Copy to Clipboard" buttons.
* **Context-Aware:** Export features respect current search filters and column settings.
* **Settings:** New `export="true"` attribute for shortcode and Gutenberg block toggle.

= 1.5.1 =
* **Mobile-First Reflow:** Tables now intelligently transform into a "Card View" on small screens, eliminating the need for horizontal scrolling.
* **Semantic Accessibility:** Added `data-tc-label` attributes to ensure mobile views stay readable and data-heavy pages remain user-friendly.

= 1.5.0 =
* **Interactive Sorting:** Users can now click any column header to sort data in ascending or descending order.
* **Smart Sorting:** Logic automatically handles numeric, string, and alpha-numeric data types.
* **Visual Cues:** Added sort indicators (arrows) to table headers.
* **UX:** Sorting intelligently integrates with Pagination (resets to page 1) and Live Search (sorts filtered results).

= 1.4.1 =
* **Security Hardening:** Implemented SSRF (Server-Side Request Forgery) protection to block proxying of internal network IPs.
* **Authorization:** Added explicit capability checks (`current_user_can`) to the AJAX data proxy.
* **Code Quality:** Added PHP return types and improved method documentation across the codebase.

= 1.4.0 =
* **Pagination Support:** Added client-side pagination for smoother navigation of large datasets.
* **Control UI:** New Pagination footer with Previous/Next controls.
* **Smart Search Integration:** Live Search now intelligently resets and works across paginated result sets.
* **Data Settings:** Added 'per_page' attribute to both shortcode and block sidebar.

= 1.3.1 =
* **Instant Filtering:** Added Live Search support for real-time dataset filtering.
* **UX:** Added search bar toggle in Gutenberg block and shortcode attribute.
* **Architecture:** Enhanced hybrid hydration to support interactive features immediately.

= 1.3.0 =
* **Gutenberg Ready:** Added native WordPress Block support with live preview.
* **UX:** New TableCrafter block includes a sidebar with all configuration options (Source, Root, Include/Exclude).
* **SSR Integration:** Blocks use the high-performance SSR engine and SWR caching for instant previews.

= 1.2.2 =
* **Deep Connectivity:** Added 'root' attribute to support nested JSON structures. 
* **Compatibility:** Now supports APIs that wrap data (e.g., WordPress REST, Shopify, etc.).
* **Hybrid Sync:** Updated JS library to handle nested roots in preview and manual fetch modes.

= 1.2.1 =
* **Instant TTFB:** Implemented Stale-While-Revalidate (SWR) caching logic.
* **Resilience:** Systems now serve stale data while refreshing in the background, ensuring tables load instantly even if APIs are slow.
* **Smart Refresh:** Added non-blocking background refresh via WP-Cron/WP-Events.

= 1.2.0 =
* Major: Implemented Server-Side Rendering (SSR) Engine for instant page loads and SEO optimization.
* Feat: Added smart link and image detection in PHP to match frontend library.
* Feat: Integrated transient-based caching (1-hour) for rendered HTML fragments.
* Optimized: Balanced Hybrid loading - tables render on server, JS adds interactivity.

= 1.1.2 =
* Docs: Significantly expanded shortcode documentation with detailed attribute descriptions and examples.

= 1.1.1 =
* **Precision Curation:** Added the ability to include/exclude specific columns for cleaner tables.
* **Visual Tables:** Added automatic rendering for images, logos, and links.
* **Hardened Security:** Implemented advanced security filters for safer data handling.

= 1.1.0 =
* Feat: Added Server-Side Proxy to bypass CORS restrictions.
* Feat: Added Automated Background Cache Warming via WP-Cron.
* Feat: Added WP-CLI support for cache management.

= 1.0.1 =
* Refactored script handling for full WP.org directory compliance.
* Moved all inline JavaScript to external files.

= 1.0.0 =
*   Initial release.
*   Added live admin previewer.
*   Released smart column detection.

== Upgrade Notice ==

= 1.9.0 =
New Feature: You can now display tags and categories from nested JSON arrays! Plugin has been renamed for clarity.

= 1.8.0 =
Customize your table headers like a pro with new aliasing syntax: `include="id:Customer ID"`.

= 1.7.0 =
UX Update: Tables now look professional out-of-the-box with auto-formatting for Dates, Emails, and Statuses.

= 1.6.0 =
Feature Update: Adds Data Export tools (CSV/Clipboard) for users. Highly recommended.

= 1.5.1 =
Performance Update: Significantly improves mobile readability with a new Reflow layout. Highly recommended.

= 1.5.0 =
Major Update: Adds Interactive Column Sorting for better data analysis. Highly recommended.

= 1.4.1 =
Security Update: Hardens the data proxy against SSRF and unauthorized access. Highly recommended for all users.

= 1.4.0 =
Major Update: Adds Pagination support for better handling of large datasets. Recommended for all users.

= 1.3.1 =
Performance Update: Now includes Live Search for instant table filtering.

= 1.3.0 =
Gutenberg Update: tablecrafter is now a native WordPress block! You can now add and configure tables directly in the Block Editor.

= 1.2.2 =
Feature Update: Adds JSON root path support for better API compatibility.

= 1.2.1 =
Performance Update: Adds Stale-While-Revalidate caching for even faster page loads.

= 1.2.0 =
Major Update: TableCrafter now renders tables server-side! This improves SEO and eliminates the "Loading" flicker.
