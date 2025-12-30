# 🚀 TableCrafter: The SEO-First JSON Table Engine for WordPress

**Turn any JSON API or remote file into a high-performance, responsive table in seconds.** 

TableCrafter is a mission-critical bridge between your dynamic external data and your WordPress site. Built for speed, hardened for security, and optimized for search engines. Now fully compatible with the **Gutenberg Block Editor**.

---

### 🔥 Why TableCrafter?

| Feature | The TableCrafter Advantage |
| :--- | :--- |
| **🚀 Instant Performance** | Powered by **SSR (Server-Side Rendering)** and **SWR (Stale-While-Revalidate)** caching for sub-100ms load times. |
| **🔍 SEO-Ready** | Data is rendered in PHP before the page loads, making every cell crawlable by Google. |
| **🧱 Block Editor First** | Native Gutenberg support. Build and preview your tables visually without touching a line of code. |
| **🔒 Bank-Grade Security** | Automated sanitization and XSS protection for all remote data sources. |
| **🗄️ Zero Database Bloat** | Your data lives at the source. We provide the window without the overhead. |

---

### ✨ Key Features

*   **Native Gutenberg Block:** Add tables visually with a live preview directly in the WordPress editor.
*   **Universal JSON Connectivity:** Connect to any public API, crypto ticker, inventory feed, or `.json` file.
*   **Smart Auto-Formatting:** Intelligent detection of **Images**, **Logos**, and **Links**—automatically rendered as visual elements.
*   **Precision Data Curation:** Use `include` or `exclude` attributes to cherry-pick exactly what matters from messy API responses.
*   **JSON Root Path Support:** Target nested data arrays (e.g., `root="data.items"`) to support complex enterprise APIs.
*   **Built-in CORS Proxy:** Bypasses browser-level data restrictions automatically via server-side fetching.

---

### 🕹️ How It Works

1.  **Install & Activate:** Up and running in under 60 seconds.
2.  **Add the Block:** In the Block Editor, search for "TableCrafter" and add it to your page.
3.  **Configure Visually:** Enter your JSON URL in the sidebar and watch the table render instantly.
4.  *(Optional)* **Shortcode:** Still prefer shortcodes? Use `[tablecrafter source="..."]` anywhere.

---

### 🛠️ Shortcode Attributes

The `[tablecrafter]` shortcode remains fully supported:

*   **`source`**: The JSON endpoint you want to visualize.
*   **`root`**: The JSON path to the data array (e.g., `root="products"` or `root="data.items"`).
*   **`include`**: Limit columns to a specific set (e.g., `include="name,price"`).
*   **`exclude`**: Hide sensitive or redundant fields (e.g., `exclude="id,metadata"`).
*   **`id`**: Apply a custom CSS ID for bespoke styling.

---

### 📈 Technical Pedigree (v1.3.0)

*   **SSR Engine:** Server-side pre-rendering for instant TTFB.
*   **SWR Caching:** Serves stale data while refreshing the cache in the background. No more waiting on slow 3rd-party APIs.
*   **Gutenberg Ready:** Native block support using `ServerSideRender` for a seamless editing experience.
*   **WP-CLI Ready:** Manage and warm your caches directly from the command line.

---

### 📃 Documentation & Metadata

**Contributors:** @fahdi  
**License:** GPLv2 or later  
**Stable tag:** 1.3.0  
**Requires PHP:** 7.4+

---

**Love TableCrafter?** [Support Development](https://www.paypal.me/fahadmurtaza) ☕
