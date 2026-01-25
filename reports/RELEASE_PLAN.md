# 📅 TableCrafter WordPress Plugin Release Plan

This document tracks the release schedule, planned features, and future ideas for the TableCrafter WordPress plugin.

## 📦 Recent Releases
### v2.4.2 (Rate Limiting Security) - Jan 14, 2026
- **Security:** Rate Limiting on AJAX Proxy (30 req/min per user/IP).
- **Security:** Proper HTTP 429 response with client identification.

### v2.4.1 (Lead Magnet) - Jan 14, 2026
- **Feature:** Lead Magnet Subscription handler.
- **Integration:** External API + Email Fallback.

### v2.4.0 (The "Data Everywhere" Update) - Jan 14, 2026
- **Feature:** Native Google Sheets Integration (Proxy Fetch + CSV Parsing).
- **Feature:** Frontend CORS Bypass.

### v2.3.14 (Hotfix) - Jan 14, 2026
- **Fix:** Critical Hydration fix for Embedded Data + SSR.

### v2.3.12 - v2.3.13 (Hotfixes)
- **Fix:** Frontend Sorting & Flash of Unstyled Content (FOUC).

### v2.3.4 - v2.3.11
- **Feature:** Accessibility (ADA Compliance).
- **Feature:** Skeleton Loading.
- **Feature:** Smart Data Formatting.

## 🚀 Upcoming Roadmap

### v2.5.0 (Robust Data & Quality)
**Focus:** Reliability, CSV Sourcing, and Visual Controls.
- **[High Priority] Native CSV Upload:** Upload CSV files directly to Media Library or Block.
- **[High Priority] Automated Testing:** Playwright E2E suite to prevent regressions.
- **[Medium Priority] Visual Column Toggle:** Simple UI to show/hide columns in the block inspector.

### v2.6.0 (The "Visual Builder" Update) - Q2 2026
- **Feature:** Visual Table Builder (Drag & Drop Columns).
- **Feature:** Advanced Custom Fields (ACF) Repeater Field Support.

## 💡 Future Ideas & Backlog

### 🎨 UI/UX Improvements
- **Theme Builder:** Allow users to create custom color themes for tables without CSS.
- **Dark Mode Toggle:** Native frontend dark mode switch for tables.

### 🛠 Technical Debt & Performance
- **Virtual Scrolling:** Implement virtual scrolling for 10,000+ row datasets.
- **Web Workers:** Offload heavy data processing (sorting/filtering) to a web worker.

## 📦 Release Process Checklist

1.  Update `TABLECRAFTER_VERSION` in `tablecrafter.php`.
2.  Update `readme.txt` (Stable tag and Changelog).
3.  Update `wpgravitytables/changelog.html`.
4.  Run full regression tests (Desktop & Mobile).
5.  Check for linting errors.
6.  Tag release in Git.
7.  Run `sync_svn.sh`.
8.  Commit SVN and Deploy Marketing Sites.
