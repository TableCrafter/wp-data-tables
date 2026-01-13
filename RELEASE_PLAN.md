# 📅 TableCrafter WordPress Plugin Release Plan

This document tracks the release schedule, planned features, and future ideas for the TableCrafter WordPress plugin.

## 🚀 Upcoming Releases

### v2.4.0 (Planned: Q1 2026)
- **Feature:** Advanced Custom Fields (ACF) Integration
- **Feature:** Server-side caching improvements
- **Fix:** Improved mobile table rendering for nested data

### v2.5.0 (Planned: Q2 2026)
- **Feature:** Visual Table Builder (Drag & Drop)
- **Feature:** Multi-language support (WPML/Polylang)

## 💡 Future Ideas & Backlog

> *Document new ideas here to ensure they are not lost.*

### 🎨 UI/UX Improvements
- **Theme Builder:** Allow users to create custom color themes for tables without CSS.
- **Dark Mode Toggle:** Native frontend dark mode switch for tables.
- **Skeleton Loading:** Improved loading states with shimmer effects.

### 🔌 Integrations
- **WooCommerce:** Display product tables with "Add to Cart" buttons.
- **Google Sheets:** Direct 2-way sync with Google Sheets.
- **AirTable:** Native integration for AirTable bases.

### 🛠 Technical Debt & Performance
- **Virtual Scrolling:** Implement virtual scrolling for 10,000+ row datasets.
- **Web Workers:** Offload heavy data processing (sorting/filtering) to a web worker.

## 📦 Release Process Checklist

1.  Update `TABLECRAFTER_VERSION` in `tablecrafter.php`.
2.  Update `readme.txt` (Stable tag and Changelog).
3.  Update `CHANGELOG.md`.
4.  Run full regression tests (Desktop & Mobile).
5.  Check for linting errors.
6.  Tag release in Git.
7.  Deploy to WordPress SVN.
