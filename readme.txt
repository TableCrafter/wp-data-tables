=== TableCrafter – WordPress Data Tables & Dynamic Content Plugin ===
Contributors: fahdi
Tags: table, json, api, data table, datatables
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 2.2.12
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.paypal.me/fahadmurtaza

Create dynamic, SEO-friendly HTML tables from any JSON API or file. Features native Gutenberg support, SSR, Live Search, and Pagination.

== Description ==

**TableCrafter** is the ultimate lightweight solution for displaying dynamic data in WordPress. 

[Try Live Demo](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/TableCrafter/wp-data-tables/main/assets/blueprints/blueprint.json)

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

**[Start your 10-day free trial today!](https://checkout.freemius.com/plugin/20996/plan/35031/?trial=paid)**

### 🛠️ Custom Customization
Need a specific feature, a custom API integration, or a unique table layout? Contact me at **[info@fahdmurtaza.com](mailto:info@fahdmurtaza.com)** if you need to customise the plugin in any way for a fee. 


== Frequently Asked Questions ==

= Does this store data in my WordPress database? =
No. TableCrafter is a lightweight viewer. It fetches data dynamically and caches it temporarily (SWR) to keep your database clean and your site fast.

= How does the SWR caching work? =
TableCrafter serves "stale" (cached) data instantly while refreshing the source in the background. This ensures your tables load in milliseconds without waiting for slow third-party APIs.

= Is it secure? =
Yes. We implement SSRF protection to prevent access to internal networks and use strict WordPress capability checks for all proxy operations.

= Can I use this for password-protected APIs? =
The free version is designed for public or key-based APIs (where the key is in the URL). For advanced OAuth or header-based authentication, please check out the Pro version or contact us for a custom solution.

= How often does the data refresh? =
By default, TableCrafter uses Stale-While-Revalidate (SWR) caching. It serves cached data instantly and refreshes from the source in the background. You can control the cache duration via filters or wait for the Pro version which includes a visual cache manager.

= Is it possible to customize the table styling? =
Absolutely! TableCrafter uses standard HTML table structures. You can add your own CSS to your theme to override any styles. We also use CSS variables for many common properties like colors and padding.

= What if my API has CORS issues? =
TableCrafter includes a built-in Server-Side Proxy. When you use a remote URL as a source, the data is fetched by your server first, which completely bypasses any browser-based CORS restrictions.

= Do you offer custom development? =
Yes! If you need specific features, deep integrations, or custom designs, I am available for hire. Please contact me at **[info@fahdmurtaza.com](mailto:info@fahdmurtaza.com)** to discuss your project.

== Screenshots ==

1. **Gutenberg Block** - The native TableCrafter block with visual sidebar controls.
2. **Interactive Frontend** - A live table showing the search bar and pagination footer.
3. **Admin Dashboard** - The shortcode generator and preview playground.


== Changelog ==

= 2.2.12 =
* Fix: Addressed bad link for "Try Live Demo" which pointed to incorrect branch.

= 2.2.11 =
* Fix: Simplified Live Preview blueprint configuration for better compatibility.

= 2.2.11 =
Fix: Updated Live Preview blueprint for compatibility.

= 2.2.10 =
* Fix: Moved blueprint.json to correct assets/blueprints location for WordPress.org Live Preview support.

= 2.2.9 =
* Feature: Enabled "Live Preview" for WordPress Playground with a valid blueprint configuration.
* Docs: Added "Try Live Demo" badge to README.

= 2.2.8 =
* Fix: Resolved Live Preview data loading issue in admin dashboard - tables now properly load and display data from URLs.
* Fix: Improved data initialization logic to correctly handle empty arrays and URL-based data sources.
* Enhancement: Added comprehensive error handling and debugging logs for better troubleshooting.
* Fix: Enhanced permission checks to allow both edit_posts and manage_options capabilities for admin preview.
* Fix: Improved local file path resolution for demo data files in admin preview.

= 2.2.7 =
* Feature: Introduced "Data Resilience" mission - plugin now provides graceful fallbacks and diagnostics for broken data sources.
* UX: Added "Onboarding Guardrails" via an Admin Debug Helper that explains exactly why a data fetch failed (e.g., API Error, Path Error).
* Core: Added a frontend "Retry" mechanism to the TableCrafter library for handling network glitches seamlessly.
* Maintenance: Bumped core library to v1.4.2.

= 2.2.6 =
* Performance: Implemented "Zero-Latency Hydration" to eliminate redundant network requests.
* Optimization: Tables now become interactive instantly upon page load by utilizing embedded data payloads.
* Fix: Resolved a critical "Double Fetch" bug that wasted user bandwidth and server resources.
* Maintenance: Updated core TableCrafter library to v1.4.1.

= 2.2.5 =
* Feature: Added "Copy to Clipboard" export tool for quick spreadsheet integration.
* UX: Integrated Global Search directly into a unified filters area for a cleaner interface.
* Fix: Resolved an "Iframe Blindness" bug where TableCrafter couldn't initialize inside Gutenberg's iframes.
* Fix: Improved hydration logic to ensure all interactive tools (Search, Export, Filters) are fully functional on SSR-rendered tables.
* Maintenance: Updated the core TableCrafter library to v1.4.0.

= 2.2.4 =
* Fix: Resolved critical issue where Live Search and Export tools were hidden on SSR tables.
* Fix: Prevented cache collision when toggling block settings.
* Enhancement: Added library-level "Hydration" support for faster, flicker-free tool injection.

= 2.2.3 =
* Fix: Properly enabled "Live Search" and "Export Tools" toggles in the Gutenberg block.
* New: Added a Demo URL selector in the block settings to quickly test your table layout.
* UI: Improved block editor styles to match the frontend table appearance.
* Enhancement: Standardized attribute handling for better reliability across different table configurations.

= 2.2.1 =
*   **Docs:** Synchronized README.md and CHANGELOG.md with all recent engine upgrades and fixes.

= 2.2.0 =
*   **Docs:** Expanded the FAQ section with more common technical questions.
*   **Docs:** Converted contact email to a mailto link.
*   **Core:** Updated `tablecrafter-core` to v1.3.0.

= 2.1.9 =
*   **Docs:** Added contact information for custom plugin customization requests.

= 2.1.8 =
*   **Fix:** Forced SVN refresh and updated Stable Tag to resolve WordPress.org display issues.
*   **Core:** Updated `tablecrafter-core` to v1.2.7.

= 2.1.7 =
*   **Fix:** Updated Stable Tag to ensure changelog and updates are visible on WordPress.org.
*   **Core:** Updated `tablecrafter-core` to v1.2.6.

= 2.1.6 =
*   **Hotfix:** Resolved a ReferenceError (container is not defined) in the multiselect filter logic.
*   **Core:** Updated `tablecrafter-core` to v1.2.5.

= 2.1.5 =
*   **Hotfix:** Fixed a critical RangeError (Maximum call stack size exceeded) that occurred when initializing multiselect filters.
*   **Core:** Updated `tablecrafter-core` to v1.2.4.

= 2.1.4 =
*   **Demo Data:** Updated Sales Metrics year to 2026 for a better filtering experience.

= 2.1.3 =
*   **Fix:** Nuked legacy multiselect container styles that were causing inconsistent shadows and borders.
*   **Core:** Updated `tablecrafter-core` to v1.2.3.

= 2.1.2 =
*   **UI:** Removed unnecessary container from Multiselect dropdowns for perfect DOM consistency.
*   **Core:** Updated `tablecrafter-core` to v1.2.2.

= 2.1.1 =
*   **UI:** Perfected filter alignment and shadow behavior across all types.
*   **Consistency:** Removed redundant styles and unified height, padding, and focus effects.
*   **Core:** Updated `tablecrafter-core` to v1.2.1.

= 2.1.0 =
*   **UI:** Refined dropdown filter styling to perfectly match standard text inputs. 
*   **Core:** Updated `tablecrafter-core` to v1.2.0.

= 2.0.9 =
*   **Fix:** Improved Date detection heuristic to prevent SKUs and common ID patterns from being incorrectly identified as Dates.
*   **Core:** Updated `tablecrafter-core` to v1.1.9.

= 2.0.8 =
*   **UI:** Balanced 50/50 split for Range Filters (Min/Max).
*   **Core:** Updated `tablecrafter-core` to v1.1.8.

= 2.0.7 =
*   **UI:** Compact horizontal layout for Range Filters.
*   **Core:** Updated `tablecrafter-core` to v1.1.7.

= 2.0.6 =
*   **Fix:** Resolved issue where Dropdown Filters would not open on small tables.
*   **Core:** Updated `tablecrafter-core` to v1.1.6.

= 2.0.5 =
*   **Improvement:** Enhanced filter type detection to prevent Names/Emails from becoming dropdowns.
*   **Core:** Updated `tablecrafter-core` to v1.1.5.

= 2.0.4 =
*   **Fix:** Resolved numeric ID date mismatch bug.
*   **Core:** Updated `tablecrafter-core` to v1.1.4.

= 2.0.3 =
*   **CRITICAL UPDATE:** Fixed missing styles for filters and resolved "Invisible Table" bug.
*   **Core:** Updated `tablecrafter-core` to v1.1.3.

= 2.0.2 =
*   **Bug Fix:** Resolved "Invisible Table" issue via column auto-discovery.
*   **Core:** Updated `tablecrafter-core` to v1.1.2.

= 2.0.1 =
*   **Bug Fix:** Resolved URL data fetching issue.
*   **Core:** Updated `tablecrafter-core` to v1.1.1.

= 2.0.0 =
*   **Engine Upgrade:** Unified with `tablecrafter-core` 1.1.0.
*   **New Feature:** Added Smart Hydration for SSR tables.

= 1.9.2 =
*   **Performance:** Added intelligent debouncing to Live Search (300ms).

= 1.9.1 =
*   **Performance:** Fixed API caching issue.

= 1.9.0 =
* **Smart Nested Data Rendering:** Automatically handles Arrays and Objects. 

= 1.8.0 =
* **Custom Column Aliasing:** Rename headers directly via `include` attribute.

= 1.7.0 =
* **Smart Data Formatting:** Dates, Booleans, and Emails.

= 1.6.0 =
* **Data Export:** Added CSV and Clipboard export tools.

= 1.5.1 =
* **Mobile-First Reflow:** Tables transform into Card View on small screens.

= 1.5.0 =
* **Interactive Sorting:** Numerical and alphabetical sorting.

= 1.4.1 =
* **Security Hardening:** SSRF protection for data proxy.

= 1.4.0 =
* **Pagination Support:** Client-side pagination for large datasets.

= 1.3.1 =
* **Instant Filtering:** Added Live Search support.

= 1.3.0 =
* **Gutenberg Ready:** Added native WordPress Block support.

= 1.2.2 =
* **Deep Connectivity:** Added 'root' attribute for nested JSON.

= 1.2.1 =
* **Instant TTFB:** Implemented SWR caching logic.

= 1.2.0 =
* **Major:** Implemented Server-Side Rendering (SSR) Engine.

= 1.1.2 =
* Docs: Expanded shortcode documentation.

= 1.1.1 =
* Feat: Added column filtering (include/exclude).

= 1.1.0 =
* Feat: Added Server-Side Proxy to bypass CORS.

= 1.0.1 =
* Refactored script handling for WP.org compliance.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 2.2.9 =
Feature: Enabled "Live Preview" for WordPress Playground.

= 2.2.8 =
Fix: Live Preview in admin dashboard now works correctly. Data loads and displays properly from any URL source.

= 2.2.7 =
Feature: Data Resilience & Onboarding Guardrails. TableCrafter now helps you fix your configuration with intelligent error diagnostics.

= 2.2.6 =
Performance: Implemented "Zero-Latency Hydration" to eliminate redundant network requests.

= 2.2.1 =
Docs: Synchronized README.md and CHANGELOG.md.

= 1.9.0 =
New Feature: Display tags from nested JSON arrays! 

= 1.2.0 =
Major Update: TableCrafter now renders tables server-side! 
