<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TC_CSV_Source
 * 
 * Handles fetching and parsing of CSV data sources (Remote URL, Local File, Google Sheets).
 */
class TC_CSV_Source
{
    /**
     * Fetch and parse CSV from a given URL.
     *
     * @param string $url The source URL.
     * @return array|WP_Error parsed data or error.
     */
    public static function fetch(string $url)
    {
        // 1. Google Sheets Normalization
        if (preg_match('/docs\.google\.com\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
            $sheet_id = $matches[1];
            $original_url = $url;
            $url = "https://docs.google.com/spreadsheets/d/{$sheet_id}/export?format=csv";

            // Preserve GID if present
            $url_parts = parse_url($original_url);
            if (isset($url_parts['query'])) {
                parse_str($url_parts['query'], $query_params);
                if (isset($query_params['gid'])) {
                    $url .= "&gid=" . $query_params['gid'];
                }
            }
        }

        // 2. Fetch Content (Reusing raw cURL for consistency with main plugin, 
        // effectively duplicating the logic for now to ensure this class is self-contained 
        // or we can pass a fetcher. For now, self-contained is safer for isolation).
        // Actually, we should probably use the same fetch logic. 
        // Let's rely on standard WP HTTP- API but with the fixes? 
        // The user's main plugin uses raw cURL because WP API failed for Google logic.
        // Let's stick to the raw cURL for robustness here too.

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        // SSL verification - enabled for security (prevents MITM attacks)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        // Use WordPress bundled CA certificates if available
        $ca_bundle = ABSPATH . WPINC . '/certificates/ca-bundle.crt';
        if (file_exists($ca_bundle)) {
            curl_setopt($ch, CURLOPT_CAINFO, $ca_bundle);
        }

        curl_setopt($ch, CURLOPT_USERAGENT, 'TableCrafter/' . TABLECRAFTER_VERSION . ' (WordPress Plugin)');
        curl_setopt($ch, CURLOPT_COOKIEFILE, "");

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            return new WP_Error('http_error', 'CURL Error: ' . $error);
        }

        if ($code !== 200) {
            return new WP_Error('http_error', 'Source returned HTTP ' . $code);
        }

        // 3. Parse CSV
        return self::parse($body);
    }

    /**
     * Parse CSV string to JSON-compatible array.
     *
     * @param string $csv_string
     * @return array
     */
    public static function parse(string $csv_string): array
    {
        $lines = explode("\n", trim($csv_string));
        if (empty($lines)) {
            return [];
        }

        // Parse headers (Row 1)
        // Fix for PHP 8.4+ deprecation: explicit escape parameter
        $headers = str_getcsv(array_shift($lines), ",", "\"", "\\");

        $data = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $row = str_getcsv($line, ",", "\"", "\\");

            // Skip rows that don't match header count
            if (count($row) !== count($headers)) {
                continue;
            }

            // Combine into associative array
            $item = array();
            foreach ($headers as $index => $key) {
                // Remove BOM if present in first key
                if ($index === 0) {
                    $key = preg_replace('/^\xEF\xBB\xBF/', '', $key);
                }
                $item[$key] = isset($row[$index]) ? $row[$index] : null;
            }
            $data[] = $item;
        }

        return $data;
    }
}
