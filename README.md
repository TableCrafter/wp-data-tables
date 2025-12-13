# TableCrafter for WordPress

**TableCrafter** is a lightweight WordPress plugin that turns raw JSON data into beautiful, responsive HTML tables. It is designed to be a thin wrapper around the TableCrafter JavaScript library.

## Features

*   **Single Data Source:** Loads data from a URL (JSON format).
*   **Native JavaScript:** No jQuery dependency.
*   **Responsive:** Handles basic overflow for mobile screens.
*   **Zero Configuration:** Just paste the shortcode.

## Usage

Use the shortcode in any post or page:

```shortcode
[tablecrafter source="https://api.example.com/data.json"]
```

## Attributes

*   `source` (required): The URL of the JSON data source. Access-Control-Allow-Origin (CORS) headers must be set on the source if it is on a different domain.
*   `id` (optional): Unique HTML ID for the table container.

## Development

Based on the [TableCrafter JS Library](https://github.com/TableCrafter/tablecrafter).
