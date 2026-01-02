# Changelog

All notable changes to TableCrafter will be documented in this file.

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
