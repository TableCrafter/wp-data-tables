# TableCrafter WordPress.org Assets

This directory contains assets for the WordPress.org plugin directory page.

## 🎨 Brand Assets

- `banner-772x250.png` - Plugin header banner (772x250px)
- `banner-1544x500.png` - High-resolution (Retina) header banner (1544x500px)
- `icon-256x256.png` - Plugin icon (256x256px)
- `banner_source_v2.png` - Original high-resolution design source

## 🛠 Asset Management

These files are synchronized with the WordPress.org SVN repository `assets/` directory.

### SVN Repository
`https://plugins.svn.wordpress.org/tablecrafter-wp-data-tables`

### SVN Upload Workflow
1. Move/Copy files to the SVN checkout's `assets/` folder.
2. Register icons (must be `icon-128x128.png` and/or `icon-256x256.png`).
3. Register banners (must be `banner-772x250.png` and/or `banner-1544x500.png`).
4. Commit:
   ```bash
   svn add assets/*
   svn ci -m "Update plugin branding assets"
   ```

## 📋 Asset Requirements
WordPress.org automatically detects these filenames:
- **Icon:** Square (128x128 or 256x256)
- **Banner:** 772x250 (Normal) or 1544x500 (Retina)
- **Screenshots:** `screenshot-1.png`, `screenshot-2.png`, etc.
