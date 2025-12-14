# TableCrafter WordPress.org Assets

This directory contains assets for the WordPress.org plugin directory page.

## Files

- `banner-772x250.png` - Plugin header banner (772x250px)
- `icon-256x256.png` - Plugin icon (256x256px)

## Usage

After your plugin is approved on WordPress.org, you'll receive SVN access. Upload these files to the `/assets/` directory in your SVN repository:

```bash
svn co https://plugins.svn.wordpress.org/tablecrafter
cd tablecrafter
mkdir assets
cp /path/to/banner-772x250.png assets/
cp /path/to/icon-256x256.png assets/
svn add assets/*
svn ci -m "Add plugin banner and icon"
```

WordPress.org will automatically display these on your plugin page.
