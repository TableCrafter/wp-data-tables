# 🚀 TableCrafter: The SEO-First JSON Table Engine for WordPress

**Turn any JSON API or remote file into a high-performance, responsive table in seconds.** 

TableCrafter is not just another table plugin—it's a mission-critical bridge between your dynamic external data and your WordPress site. Built for speed, hardened for security, and optimized for search engines.

---

### 🔥 Why TableCrafter?

| Feature | The TableCrafter Advantage |
| :--- | :--- |
| **🚀 Instant Performance** | Powered by **SSR (Server-Side Rendering)** and **SWR (Stale-While-Revalidate)** caching for sub-100ms load times. |
| **🔍 SEO-Ready** | Data is rendered in PHP before the page loads, making every cell crawlable by Google. |
| **🛡️ Bank-Grade Security** | Automated sanitization and XSS protection for all remote data sources. |
| **📦 Zero Database Bloat** | Your data lives at the source. We provide the window without the overhead. |

---

### ✨ Key Features

*   **Universal JSON Connectivity:** Connect to any public API, crypto ticker, inventory feed, or `.json` file.
*   **Smart Auto-Formatting:** Intelligent detection of **Images**, **Logos**, and **Links**—automatically rendered as visual elements.
*   **Precision Data Curation:** Use `include` or `exclude` attributes to cherry-pick exactly what matters from messy API responses.
*   **JSON Root Path Support:** Target nested data arrays (e.g., `root="data.items"`) to support complex enterprise APIs.
*   **Built-in CORS Proxy:** Bypasses browser-level data restrictions automatically via server-side fetching.
*   **Mobile-First Design:** Responsive tables that look stunning on any device, right out of the box.

---

### 🕹️ How It Works

1.  **Install & Activate:** Up and running in under 60 seconds.
2.  **Paste & Preview:** Use the Admin Dashboard to test your API URL and see your data live.
3.  **Deploy:** Drop a shortcode anywhere: 
    ```text
    [tablecrafter source="https://api.example.com/data.json" include="name,price,status"]
    ```

---

### 🛠️ Advanced Usage & Shortcuts

The `[tablecrafter]` shortcode is your control center:

*   **`source`**: The JSON endpoint you want to visualize.
*   **`root`**: The JSON path to the data array (e.g., `root="products"` or `root="data.items"`).
*   **`include`**: Limit columns to a specific set (e.g., `include="name,price"`).
*   **`exclude`**: Hide sensitive or redundant fields (e.g., `exclude="id,metadata"`).
*   **`id`**: Apply a custom CSS ID for bespoke styling.

**Example: A Nested API Response**
```text
[tablecrafter source="https://api.example.com/api" root="data.products" include="title,price"]
```

---

### 📈 Technical Pedigree (v1.2.2)

*   **SSR Engine:** Server-side pre-rendering for instant TTFB.
*   **SWR Caching:** Serves stale data while refreshing the cache in the background. No more waiting on slow 3rd-party APIs.
*   **WP-CLI Ready:** Manage and warm your caches directly from the command line.
*   **Secure Proxy:** Nonce-protected AJAX endpoints for admin previews.

---

### 📃 Documentation & Metadata

**Contributors:** @fahdi  
**License:** GPLv2 or later  
**Stable tag:** 1.2.2  
**Requires PHP:** 7.4+

---

**Love TableCrafter?** [Support Development](https://www.paypal.me/fahadmurtaza) ☕
