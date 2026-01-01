# 🚀 TableCrafter: WordPress Data Tables & Dynamic Content Plugin

**Turn any JSON API or remote file into a high-performance, responsive table in seconds.** 

## 🚀 Upgrade to Pro: Gravity Tables
Looking for more power? Check out our premium form data solution: **[Advanced Data Tables for Gravity Forms](https://github.com/ajstrucking/gravity-tables)**.
- **Frontend Editing:** Click-to-edit interface.
- **Bulk Actions:** Manage multiple entries instantly.
- **Advanced Export:** CSV, Excel, PDF.

TableCrafter is a mission-critical bridge between your dynamic external data and your WordPress site. Built for speed, hardened for security, and optimized for search engines. Now with **Smart Formatting**, **CSV Export**, **Mobile Reflow**, **Pagination**, and **Live Search**.

---

### 🔥 Why TableCrafter?

| Feature | The TableCrafter Advantage |
| :--- | :--- |
| **🚀 Instant Performance** | Powered by **SSR (Server-Side Rendering)** and **SWR (Stale-While-Revalidate)** caching for sub-100ms load times. |
| **✨ Smart Formatting** | Automatically turns **Dates**, **URLs**, **Emails**, and **Booleans** into professional UI elements. |
| **💾 Data Export** | One-click export to **CSV** or **Clipboard** for further analysis in Excel/Sheets. |
| **🔍 SEO-Ready** | Data is rendered in PHP before the page loads, making every cell crawlable by Google. |
| **📱 Mobile Reflow** | Automatically transforms wide tables into a responsive "Card View" for phones. |
| **🛡️ Hardened Security** | Built-in SSRF protection and strict capability checks for all remote data operations. |
| **📄 Smart Pagination** | Effortlessly navigate huge datasets with built-in client-side pagination. |
| **⚡ Live Search** | Filter thousands of rows instantly with ultra-fast client-side search. |
| **↕️ Interactive Sorting** | Click any header to sort numerically or alphabetically with smart type detection. |
| **🧱 Block Editor First** | Native Gutenberg support. Build and preview your tables visually in seconds. |

---

### ✨ Key Features

*   **Native Gutenberg Block:** Add tables visually with a live preview directly in the WordPress editor.
*   **Smart Auto-Formatting:** Intelligent detection of **Images**, **Emails**, **Dates**, and **Booleans**.
*   **Data Export Suite:** Enable CSV download and Copy-to-Clipboard buttons for your users.
*   **Mobile-First Design:** Smart reflow layout makes data readable on any device.
*   **Safe Data Proxy:** Securely fetch remote JSON to bypass CORS while protecting your server from SSRF.
*   **Data Pagination:** Keep your pages clean and fast by showing 10, 25, or 50 rows at a time.
*   **Interactive Header Sorting:** Toggle between ASC/DESC for any column instantly.
*   **Live Table Search:** Toggle a real-time search bar that filters data as you type.
*   **Precision Data Curation:** Use `include` or `exclude` attributes to cherry-pick exactly what matters.

---

### 🕹️ How It Works

1.  **Install & Activate:** Up and running in under 60 seconds.
2.  **Add the Block:** In the Block Editor, search for "TableCrafter" and add it to your page.
3.  **Configure Visually:** Enter your JSON URL, toggle "Enable Export", and watch the table render instantly.
4.  **Secure by Default:** All remote requests are validated and authorized automatically.

---

### 🛠️ Shortcode Attributes

The `[tablecrafter]` shortcode remains fully supported:

*   **`source`**: The JSON endpoint you want to visualize.
*   **`export`**: Enable export buttons (`true` or `false`).
*   **`per_page`**: Number of rows to show per page (e.g., `per_page="10"`).
*   **`search`**: Toggle the search bar (`true` or `false`).
*   **`root`**: The JSON path to the data array (e.g., `root="products"`).
*   **`include`**: Limit columns and rename them (e.g., `include="id:ID, name:Full Name"`).
*   **`exclude`**: Hide fields (e.g., `exclude="id,metadata"`).

---

### 📈 Technical Pedigree (v1.9.0)

*   **Smart Nested Rendering:** Auto-detection and Tag/Badge rendering for Arrays and Objects.
*   **Custom Column Aliasing:** Advanced parsing logic for `key:Alias` headers in PHP and JS.
*   **Smart Export:** CSV exports respect custom aliases and current sort/filter state.
*   **Smart Formatting Engine:** Regex-based type detection (ISO 8601/Email/Boolean) in both PHP and JS.
*   **Client-Side Export:** Generates CSV blobs in-browser, reducing server load.
*   **Mobile Reflow:** CSS-driven layout transformation using semantic metadata.
*   **Interactive Sorting:** Smart-type data sorting engine (Numeric/String/Alpha).
*   **Security Architecture:** Implemented SSRF protection and WP capability-based authorization.
*   **Code Quality:** Modern PHP type-hinting and optimized method structures for long-term stability.
*   **Pagination Engine:** Client-side state management for fast page switching.
*   **SSR/SWR Engine:** High-performance caching and rendering.

---

### 📃 Documentation & Metadata

**Contributors:** @fahdi  
**License:** GPLv2 or later  
**Stable tag:** 1.9.0  
**Requires PHP:** 7.4+

---

**Love TableCrafter?** [Support Development](https://www.paypal.me/fahadmurtaza) ☕
