# Changelog

## [2.3.3] - 2026-01-13
### Added
- **Smart Data Formatting:** New engine automatically detects and formats raw data types (Dates, URLs, Emails, Booleans) into user-friendly HTML.
- **Accessibility:** Full Keyboard Navigation (tab sorting) and ARIA attributes for inclusive table interaction.
- **UX:** Screen reader optimizations including labeled inputs and focus management.

## [2.3.2] - 2026-01-13
### Added
- **Reliability:** Server-side error logging (`TC_Logger`) for easier troubleshooting of API failures.
- **UX:** Visible "Data Unavailable" state for end-users when data sources fail (e.g., 404, 500, Bad JSON).

## [2.3.1] - 2026-01-13
### Security
- **Critical Fix:** Resolved Arbitrary File Read vulnerability in local file resolution logic.
- **Hardening:** Added strict whitelist for allowed paths (ABSPATH, Plugin Dir) and extension validation (.json).

All notable changes to TableCrafter will be documented in this file.

## [2.3.0] - 2026-01-11
### Added
- **Gutenberg Block:** Full proxy support and reactive settings (Search, Filter, Export).
- **SSR Robustness:** Consolidated fixes for `stdClass` and `TypeError` crashes.
- **Data Conversion:** Auto-conversion of simple lists to single-column tables.
- **Admin UI:** Dynamic Red/Green "Preview Table" button and auto-preview on toggle.

## [2.2.34] - 2026-01-11
### Fixed
- **Hotfix:** Resolved persistent `stdClass` array access error in the main SSR rendering loop.

## [2.2.33] - 2026-01-11
### Fixed
- **Hotfix:** Resolved critical `TypeError` in `array_keys()` during SSR rendering when data rows are objects.

## [2.2.32] - 2026-01-11
### Added
- **Block Builder:** Fixed re-initialization and proxy support for the Gutenberg editor.
- **Robustness:** Added auto-conversion for simple lists to prevent "Rendering Error".
- **Core:** Improved SSR compatibility for varied data.

## [2.2.31] - 2026-01-11
### Added
- **UI:** Dynamic preview button states (Red/Green) based on configuration changes.
- **UX:** Auto-preview functionality for checkboxes.
- **Styling:** Premium margins and padding for Global Search and Clear Filters.
- **Core:** Core library updated to v1.4.3.

## [2.2.27] - 2026-01-11
### Added
- **Utility:** Visual Shortcode Builder in the admin dashboard. Generate complex [tablecrafter] tags with real-time UI toggles.
- **UI:** One-Click Copy functionality for generated shortcodes with instant clipboard feedback.
- **Branding:** Transitioned to "Data to Beautiful Tables" to align with the format-agnostic "Crafter Suite" vision.
- **Preview:** Enhanced Admin Preview that respects all custom Shortcode Builder parameters (Data Root, Rows Per Page, etc.).

## [2.2.18] - 2026-01-08
### Enhanced
- **Docs:** Completely rewrote README.md with comprehensive technical documentation, API reference, and developer hooks
- **SEO:** Enhanced plugin description and screenshot descriptions with targeted keywords and use cases
- **Screenshots:** Updated with new contextual images showcasing Gutenberg block, admin dashboard, and interactive features

### Fixed
- **Critical:** Resolved Live Search toggle not working in Gutenberg block editor due to hydration bug in renderFilters method
- **UI:** Updated Gutenberg block icon to match WordPress design standards with proper branding

### Added
- **Documentation:** Extensive developer documentation including PHP hooks, JavaScript events, and architecture details
- **Badges:** WordPress.org plugin badges showing version, downloads, and ratings
- **Examples:** Complete code examples for shortcodes, hooks, and troubleshooting

## [2.2.17] - 2026-01-07
### Fixed
- **Docs:** Added proper "== Description ==" section header for better WordPress.org formatting and compliance.

## [2.2.16] - 2026-01-07
### Fixed
- **Docs:** Cleaned up readme.txt formatting issues - removed duplicate changelog entries and misplaced product description from v2.2.13 section.

## [2.2.21] - 2026-01-08
### Changed
- **UI:** Global search is now disabled by default for cleaner initial appearance.
- **UI:** Moved "Clear All Filters" button below filter inputs and added top spacing.

## [2.2.20] - 2026-01-08
### Added
- **Onboarding:** Implemented "Welcome Screen" with activation redirect to guide new users to create their first table.

## [2.2.19] - 2026-01-08
### Fixed
- **Repo:** Shortened plugin description to <150 characters to fix WordPress.org import warning.

## [2.2.15] - 2026-01-08
### Fixed
- **Playground:** Updated WordPress Playground blueprint.json with required meta fields (title, author) and preferredVersions (PHP, WordPress) for Live Preview compatibility.

## [2.2.14] - 2026-01-08
### Security
- **Critical Fix:** Patched a Blind SSRF vulnerability in `ajax_proxy_fetch`. Implemented `wp_safe_remote_get()` and `wp_http_validate_url()` to prevent DNS rebinding and internal network scanning.

## [2.2.13] - 2026-01-07
### Changed
- **Docs:** Switched "Live Demo" link to verify via **TasteWP** for guaranteed stability while WordPress Playground integration is being debugged.

## [2.2.12] - 2026-01-07
### Fixed
- **Docs:** Fixed "Try Live Demo" link to point to the correct GitHub `main` branch instead of `trunk`.

## [2.2.11] - 2026-01-07
### Fixed
- **Playground:** Simplified `blueprint.json` configuration to ensure maximum compatibility with WordPress.org Live Preview validator.

## [2.2.10] - 2026-01-07
### Fixed
- **Playground:** Moved `blueprint.json` to `assets/blueprints/` directory to fix validity error and comply with WordPress.org standards.

## [2.2.9] - 2026-01-07
### Added
- **Preview:** Enabled "Live Preview" for WordPress Playground with a valid blueprint configuration.
- **Docs:** Added "Try Live Demo" badge to README.

## [2.2.8] - 2026-01-06
### Fixed
- **Admin Preview:** Fixed Live Preview not loading data in the admin dashboard. Resolved data initialization logic to properly handle empty arrays and URL-based data sources.
- **Data Loading:** Improved error handling and logging for better debugging when data fails to load.
- **Container Rendering:** Enhanced container clearing and rendering flow to ensure tables display correctly after data loads.
- **Permissions:** Fixed permission checks to allow both `edit_posts` and `manage_options` capabilities for admin preview.
- **Local Files:** Improved local file path resolution for demo data files to work correctly in admin preview.

## [2.2.7] - 2026-01-06
### Added
- **Resilience:** Introduced "Data Resilience" diagnostics. The engine now returns descriptive error codes instead of silent failures.
- **Onboarding:** Added an "Admin Error Helper" UI that appears when a data source is misconfigured, providing troubleshooting tips.
- **UX:** Implemented a "Retry" button on the frontend for resilient data fetching in unstable network environments.
- **Core:** Core library updated to v1.4.2 with enhanced error states.

## [2.2.6] - 2026-01-06
### Added
- **Preview:** Integrated **WordPress Playground Blueprint** to enable the "Live Preview" feature on WordPress.org. Users can now test TableCrafter instantly in their browser!
- **Performance:** Implemented "Zero-Latency Hydration" to eliminate redundant network requests.
- **Optimization:** Tables now become interactive instantly upon page load by utilizing embedded data payloads.

### Fixed
- **Performance:** Resolved a critical "Double Fetch" bug that wasted user bandwidth and server resources.

## [2.2.5] - 2026-01-05
### Added
- **Export:** Added "Copy to Clipboard" export tool for quick spreadsheet integration with tab-separated values.
- **UI:** Integrated Global Search directly into a unified filters area for a cleaner interface.

### Fixed
- **Block:** Resolved an "Iframe Blindness" bug where TableCrafter couldn't initialize inside Gutenberg's iframes.
- **Core:** Improved hydration logic to ensure all interactive tools (Search, Export, Filters) are fully functional on SSR-rendered tables.

## [2.2.4] - 2026-01-05
### Fixed
- **Core:** Implemented "Hydration Mode" to support injecting filters and export tools into server-side rendered (SSR) tables without flickering.
- **WP Plugin:** Fixed cache key collision bug where toggling Search/Export settings in Gutenberg was not updating the table output.
- **WP Plugin:** Improved attribute normalization for robust boolean parsing.

## [2.2.3] - 2026-01-05
### Added
- **Block:** New "Demo URL" selector in Gutenberg block settings for quick testing with sample datasets.
- **Block:** Integrated TableCrafter CSS into the block editor for a true "What You See Is What You Get" experience.

### Fixed
- **Block:** Resolved issue where "Enable Live Search" and "Enable Export Tools" toggles in the Gutenberg block were not correctly passing settings to the frontend.
- **Frontend:** Improved attribute parsing in `frontend.js` to ensure boolean settings are correctly interpreted.
- **Security:** Standardized data passing to the block script via `wp_localize_script`.

## [2.2.0] - 2026-01-04
### Added
- **Docs:** Expanded the FAQ section with more common technical questions.
- **Docs:** Converted contact email to a mailto link.
- **Core:** Updated `tablecrafter-core` to v1.3.0.

## [2.1.9] - 2026-01-04
### Added
- **Docs:** Added contact information for custom plugin customization requests.

## [2.1.8] - 2026-01-04
### Fixed
- **Deployment:** Forced SVN refresh and updated Stable Tag to resolve WordPress.org display issues.
- **Core:** Updated `tablecrafter-core` to v1.2.7.

## [2.1.7] - 2026-01-04
### Fixed
- **Deployment:** Updated Stable Tag to ensure changelog and updates are visible on WordPress.org.
- **Core:** Updated `tablecrafter-core` to v1.2.6.

## [2.1.6] - 2026-01-04
### Fixed
- **Hotfix:** Resolved a ReferenceError (container is not defined) in the multiselect filter logic.
- **Core:** Updated `tablecrafter-core` to v1.2.5.

## [2.1.5] - 2026-01-04
### Fixed
- **Hotfix:** Fixed a critical RangeError (Maximum call stack size exceeded) during multiselect initialization.
- **Core:** Updated `tablecrafter-core` to v1.2.4.

## [2.1.4] - 2026-01-04
### Changed
- **Demo Data:** Updated Sales Metrics year to 2026 for a better filtering experience.

## [2.1.3] - 2026-01-04
### Fixed
- **UI:** Nuked legacy multiselect container styles that were causing inconsistent shadows and borders.
- **Core:** Updated `tablecrafter-core` to v1.2.3.

## [2.1.2] - 2026-01-04
### Fixed
- **UI:** Removed unnecessary container from Multiselect dropdowns for perfect DOM consistency.
- **Core:** Updated `tablecrafter-core` to v1.2.2.

## [2.1.1] - 2026-01-04
### Fixed
- **UI:** Perfected filter alignment and shadow behavior across all types.
- **Consistency:** Unified height, padding, and focus effects for all inputs.
- **Core:** Updated `tablecrafter-core` to v1.2.1.

## [2.1.0] - 2026-01-04
### Fixed
- **UI:** Refined dropdown filter styling to perfectly match standard text inputs.
- **Core:** Updated `tablecrafter-core` to v1.2.0.

## [2.0.9] - 2026-01-04
### Fixed
- **Logic:** Improved Date detection heuristic to prevent SKUs/IDs from being identified as Dates.
- **Core:** Updated `tablecrafter-core` to v1.1.9.

## [2.0.8] - 2026-01-04
### Fixed
- **UI:** Balanced 50/50 split for Range Filters.
- **Core:** Updated `tablecrafter-core` to v1.1.8.

## [2.0.7] - 2026-01-04
### Fixed
- **UI:** Compact horizontal layout for Range Filters.
- **Core:** Updated `tablecrafter-core` to v1.1.7.

## [2.0.6] - 2026-01-04
### Fixed
- **Logic:** Resolved dropdown clipping issue via Fixed Positioning.
- **Core:** Updated `tablecrafter-core` to v1.1.6.

## [2.0.5] - 2026-01-04
### Fixed
- **Logic:** Enhanced filter type detection to prevent false positives on small datasets.
- **Core:** Updated `tablecrafter-core` to v1.1.5.

## [2.0.4] - 2026-01-04
### Fixed
- **Logic:** Numeric IDs no longer incorrectly treated as dates.
- **Core:** Updated `tablecrafter-core` to v1.1.4.

## [2.0.3] - 2026-01-03
### Fixed
- **CRITICAL:** Fixed missing CSS deployment and "Invisible Table" bug.
- **Core:** Updated `tablecrafter-core` to v1.1.3.

## [2.0.2] - 2026-01-03
### Fixed
- **Bug:** Resolved "Invisible Table" issue via column auto-discovery.
- **Core:** Updated `tablecrafter-core` to v1.1.2.

## [2.0.1] - 2026-01-03
### Fixed
- **Bug:** Resolved "Loading..." issue for remote sources.
- **Admin:** Updated Admin Preview API initialization.
- **Core:** Updated `tablecrafter-core` to v1.1.1.

## [2.0.0] - 2026-01-03
### Added
- **Engine Upgrade:** Unified with `tablecrafter-core` 1.1.0.
- **Hydration:** Added Smart Hydration for SSR tables.

## [1.9.2] - 2026-01-02
### Changed
- **Performance:** Added intelligent debouncing (300ms) to Live Search. Prevents UI freezing when typing rapidly in large datasets.

## [1.9.1] - 2026-01-01
### Fixed
- Fixed API caching issue where the renderer ignored pre-warmed data. Now uses read-through caching for instant loads.
### Added
- Added documentation for premium Gravity Tables addon.

## [1.9.0] - 2026-01-01
### Added
- **Smart Nested Data Rendering:** Automatically handles Arrays and Objects. Nested items are now rendered as elegant tags or badges instead of `[object Object]`.
- **Plugin Rebranding:** Officially renamed to "TableCrafter – WordPress Data Tables & Dynamic Content Plugin" for better alignment with feature scope.

## [1.8.0] - 2026-01-01
### Added
- **Custom Column Aliasing:** Rename headers directly in the shortcode using `include="key:My Label"` syntax.
- **Smart Export:** CSV exports now respect your custom column aliases.
- **Mobile Reflow:** Mobile card view now uses professional aliases for labels.

## [1.7.0] - 2025-12-31
### Added
- **Smart Data Formatting:** Automatically detects and formats **Dates** (to locale string), **Booleans** (Yes/No badges), and **Emails** (mailto links).
- **UI Polish:** Added professional styles for Boolean badges and links.

## [1.6.0] - 2025-12-29
### Added
- **Data Export:** Added toolbar with "Export to CSV" and "Copy to Clipboard" buttons.
- **Context-Aware:** Export features respect current search filters and column settings.
- **Settings:** New `export="true"` attribute for shortcode and Gutenberg block toggle.

## [1.5.1] - 2025-12-25
### Added
- **Mobile-First Reflow:** Tables now intelligently transform into a "Card View" on small screens.
- **Semantic Accessibility:** Added `data-tc-label` attributes for mobile views.

## [1.5.0] - 2025-12-20
### Added
- **Interactive Sorting:** Click column headers to sort numerically or alphabetically.
- **Smart Type Detection:** Automatic handling of string and number sorting.
- **Visual Sorters:** Arrows added to headers.

## [1.4.1] - 2025-12-15
### Fixed
- **Security Hardening:** SSRF (Server-Side Request Forgery) protection for data proxy.
- **Authorization:** Standardized `current_user_can` checks.

## [1.4.0] - 2025-12-10
### Added
- **Pagination Engine:** Client-side pagination for large datasets.
- **Control UI:** Previous/Next footer controls.

## [1.3.1] - 2025-12-05
### Added
- **Live Search:** Real-time filtering search bar.

## [1.3.0] - 2025-12-01
### Added
- **Gutenberg Support:** Native WordPress Block with visual sidebar.
- **Live Preview:** Instant rendering in the Block Editor.

## [1.2.2] - 2025-11-25
### Added
- **Root Path Selection:** Support for nested JSON data arrays via `root` attribute.

## [1.2.1] - 2025-11-20
### Added
- **SWR Caching:** Stale-While-Revalidate caching for faster TTFB.

## [1.2.0] - 2025-11-15
### Added
- **SSR Engine:** Server-Side Rendering for SEO and elimination of loading flicker.

## [1.1.2] - 2025-11-10
### Added
- Expanded shortcode documentation and examples.

## [1.1.1] - 2025-11-05
### Added
- **Column Filtering:** Support for `include` and `exclude` attributes.
- **Visual Rendering:** Auto-detection for images and links.

## [1.1.0] - 2025-11-01
### Added
- **Data Proxy:** Bypasses CORS restrictions.
- **WP-CLI Support:** Cache management via command line.
- **Automated Refresh:** Background cache warming (WP-Cron).

## [1.0.1] - 2025-10-25
### Fixed
- Refactored script handling for WP.org compliance.

## [1.0.0] - 2025-10-20
### Added
- Initial release.
- Live admin previewer.
- Smart column detection.
