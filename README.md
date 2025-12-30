# 🚀 TableCrafter: The SEO-First JSON Table Engine for WordPress

**Turn any JSON API or remote file into a high-performance, responsive table in seconds.** 

TableCrafter is a mission-critical bridge between your dynamic external data and your WordPress site. Built for speed, hardened for security, and optimized for search engines. Now with **Pagination**, **Live Search** and **Gutenberg Support**.

---

### 🔥 Why TableCrafter?

| Feature | The TableCrafter Advantage |
| :--- | :--- |
| **🚀 Instant Performance** | Powered by **SSR (Server-Side Rendering)** and **SWR (Stale-While-Revalidate)** caching for sub-100ms load times. |
| **🔍 SEO-Ready** | Data is rendered in PHP before the page loads, making every cell crawlable by Google. |
| **📄 Smart Pagination** | Effortlessly navigate huge datasets with built-in client-side pagination. |
| **⚡ Live Search** | Filter thousands of rows instantly with ultra-fast client-side search. |
| **🧱 Block Editor First** | Native Gutenberg support. Build and preview your tables visually without touching a line of code. |
| **🔒 Bank-Grade Security** | Automated sanitization and XSS protection for all remote data sources. |

---

### ✨ Key Features

*   **Native Gutenberg Block:** Add tables visually with a live preview directly in the WordPress editor.
*   **Data Pagination:** Keep your pages clean and fast by showing 10, 25, or 50 rows at a time.
*   **Live Table Search:** Toggle a real-time search bar that filters data as you type.
*   **Universal JSON Connectivity:** Connect to any public API, crypto ticker, inventory feed, or `.json` file.
*   **Smart Auto-Formatting:** Intelligent detection of **Images**, **Logos**, and **Links**—automatically rendered as visual elements.
*   **Precision Data Curation:** Use `include` or `exclude` attributes to cherry-pick exactly what matters.
*   **JSON Root Path Support:** Target nested data arrays (e.g., `root="data.items"`) for enterprise APIs.

---

### 🕹️ How It Works

1.  **Install & Activate:** Up and running in under 60 seconds.
2.  **Add the Block:** In the Block Editor, search for "TableCrafter" and add it to your page.
3.  **Configure Rows:** Set "Rows Per Page" in the sidebar to enable pagination.
4.  **Enable Search:** Toggle "Live Search" in the block sidebar for interactive datasets.

---

### 🛠️ Shortcode Attributes

The `[tablecrafter]` shortcode remains fully supported:

*   **`source`**: The JSON endpoint you want to visualize.
*   **`per_page`**: Number of rows to show per page (e.g., `per_page="10"`).
*   **`search`**: Toggle the search bar (`true` or `false`).
*   **`root`**: The JSON path to the data array (e.g., `root="products"`).
*   **`include`**: Limit columns to a specific set (e.g., `include="name,price"`).
*   **`exclude`**: Hide fields (e.g., `exclude="id,metadata"`).

---

### 📈 Technical Pedigree (v1.4.0)

*   **Pagination Engine:** Client-side state management for fast page switching.
*   **SSR/SWR Engine:** High-performance caching and rendering.
*   **Block Engine:** Native Gutenberg components for seamless editing.
*   **Interactive Layer:** Vanilla JS live-filtering for superior mobile and desktop UX.

---

### 📃 Documentation & Metadata

**Contributors:** @fahdi  
**License:** GPLv2 or later  
**Stable tag:** 1.4.0  
**Requires PHP:** 7.4+

---

**Love TableCrafter?** [Support Development](https://www.paypal.me/fahadmurtaza) ☕
