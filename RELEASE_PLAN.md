# 📅 TableCrafter WordPress Plugin Release Plan

This document tracks the release schedule, planned features, and future ideas for the TableCrafter WordPress plugin.

## 📦 Recent Releases
### v2.3.14 (Hotfix) - Jan 14, 2026
- **Fix:** Critical Hydration fix for Embedded Data + SSR (Dead Interaction Bug).

### v2.3.12 - v2.3.13 (Hotfixes)
- **Fix:** Frontend Sorting & Flash of Unstyled Content (FOUC).

### v2.3.4 - v2.3.11
- **Feature:** Accessibility (ADA Compliance).
- **Feature:** Skeleton Loading.
- **Feature:** Smart Data Formatting.

## 🚀 Upcoming Roadmap

### v2.4.0 (The "Data Everywhere" Update) - Q1 2026
**Target Audience:** Non-technical users (Marketers, PMs).
- **[High Priority] Google Sheets Integration:** Paste a Google Sheet URL, get a table. No API keys, no JSON conversion.
- **[Medium Priority] CSV File Support:** Direct upload or URL support (auto-parsing CSV to JSON buffers).
- **[Low Priority] Local Caching Strategy:** "Stale-While-Revalidate" with persistent fallback (if source fails, show last good cache).

### v2.5.0 (The "Visual Builder" Update) - Q2 2026
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
