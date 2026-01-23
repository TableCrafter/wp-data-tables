<?php
/**
 * TableCrafter Airtable Source
 *
 * Handles fetching data from Airtable bases via REST API.
 *
 * @package TableCrafter
 * @since 3.5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class TC_Airtable_Source
 *
 * Fetches and normalizes data from Airtable API.
 */
class TC_Airtable_Source
{
    /**
     * Airtable API base URL
     */
    const API_URL = 'https://api.airtable.com/v0';

    /**
     * Maximum records per request (Airtable limit)
     */
    const MAX_RECORDS_PER_PAGE = 100;

    /**
     * Fetch data from Airtable
     *
     * @param string $base_id    Airtable Base ID (e.g., appXXXX)
     * @param string $table_name Table name or ID
     * @param string $token      Personal Access Token
     * @param array  $params     Optional query parameters
     * @return array|WP_Error    Normalized records or error
     */
    public static function fetch(string $base_id, string $table_name, string $token, array $params = [])
    {
        // Validate inputs
        if (empty($base_id) || empty($table_name) || empty($token)) {
            return new WP_Error(
                'airtable_invalid_params',
                __('Airtable Base ID, Table Name, and Token are required.', 'tablecrafter-wp-data-tables')
            );
        }

        // Build URL
        $url = self::API_URL . '/' . rawurlencode($base_id) . '/' . rawurlencode($table_name);

        // Add query parameters
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        // Fetch all records (handles pagination)
        $all_records = self::fetch_all_pages($url, $token);

        if (is_wp_error($all_records)) {
            return $all_records;
        }

        // Normalize records for table display
        return self::normalize_records($all_records);
    }

    /**
     * Fetch all pages of records (handles pagination)
     *
     * @param string $url   Base API URL
     * @param string $token PAT token
     * @return array|WP_Error All records or error
     */
    private static function fetch_all_pages(string $url, string $token)
    {
        $all_records = [];
        $offset = null;
        $max_iterations = 50; // Safety limit (5000 records max)
        $iteration = 0;

        do {
            $iteration++;
            if ($iteration > $max_iterations) {
                break;
            }

            // Add offset for pagination
            $request_url = $url;
            if ($offset) {
                $separator = (strpos($url, '?') !== false) ? '&' : '?';
                $request_url .= $separator . 'offset=' . urlencode($offset);
            }

            $response = self::make_request($request_url, $token);

            if (is_wp_error($response)) {
                return $response;
            }

            // Collect records
            if (isset($response['records']) && is_array($response['records'])) {
                $all_records = array_merge($all_records, $response['records']);
            }

            // Check for more pages
            $offset = isset($response['offset']) ? $response['offset'] : null;

        } while ($offset !== null);

        return $all_records;
    }

    /**
     * Make HTTP request to Airtable API
     *
     * @param string $url   Full API URL
     * @param string $token PAT token
     * @return array|WP_Error Decoded response or error
     */
    private static function make_request(string $url, string $token)
    {
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        // Handle error responses
        if ($code !== 200) {
            return self::handle_error_response($code, $body);
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'airtable_json_error',
                __('Failed to parse Airtable response.', 'tablecrafter-wp-data-tables')
            );
        }

        return $data;
    }

    /**
     * Handle Airtable API error responses
     *
     * @param int    $code HTTP status code
     * @param string $body Response body
     * @return WP_Error
     */
    private static function handle_error_response(int $code, string $body): WP_Error
    {
        $data = json_decode($body, true);
        $message = isset($data['error']['message']) ? $data['error']['message'] : '';

        switch ($code) {
            case 401:
                return new WP_Error(
                    'airtable_auth_error',
                    __('Invalid Airtable token. Please check your Personal Access Token.', 'tablecrafter-wp-data-tables')
                );

            case 403:
                return new WP_Error(
                    'airtable_permission_error',
                    __('Access denied. Your token may not have access to this base/table.', 'tablecrafter-wp-data-tables')
                );

            case 404:
                return new WP_Error(
                    'airtable_not_found',
                    __('Airtable base or table not found. Please check Base ID and Table Name.', 'tablecrafter-wp-data-tables')
                );

            case 422:
                return new WP_Error(
                    'airtable_invalid_request',
                    $message ?: __('Invalid request parameters.', 'tablecrafter-wp-data-tables')
                );

            case 429:
                return new WP_Error(
                    'airtable_rate_limit',
                    __('Airtable rate limit exceeded. Please try again in a few seconds.', 'tablecrafter-wp-data-tables')
                );

            case 500:
            case 502:
            case 503:
                return new WP_Error(
                    'airtable_server_error',
                    __('Airtable server error. Please try again later.', 'tablecrafter-wp-data-tables')
                );

            default:
                return new WP_Error(
                    'airtable_error',
                    sprintf(__('Airtable returned HTTP %d: %s', 'tablecrafter-wp-data-tables'), $code, $message)
                );
        }
    }

    /**
     * Normalize Airtable records for table display
     *
     * Flattens the nested `fields` object into a flat array.
     *
     * @param array $records Raw Airtable records
     * @return array Normalized records
     */
    public static function normalize_records(array $records): array
    {
        return array_map(function ($record) {
            $normalized = [
                '_airtable_id' => isset($record['id']) ? $record['id'] : '',
            ];

            // Flatten fields
            if (isset($record['fields']) && is_array($record['fields'])) {
                foreach ($record['fields'] as $key => $value) {
                    // Handle array values (linked records, attachments, etc.)
                    if (is_array($value)) {
                        $normalized[$key] = self::flatten_array_value($value);
                    } else {
                        $normalized[$key] = $value;
                    }
                }
            }

            return $normalized;
        }, $records);
    }

    /**
     * Flatten array values for display
     *
     * @param array $value Array value from Airtable
     * @return string Flattened string representation
     */
    private static function flatten_array_value(array $value): string
    {
        // Check if it's an attachments array
        if (isset($value[0]['url'])) {
            // Return first attachment URL for images
            return $value[0]['url'];
        }

        // Check if it's linked record IDs
        if (isset($value[0]) && is_string($value[0]) && strpos($value[0], 'rec') === 0) {
            return implode(', ', $value);
        }

        // Default: join with commas
        return implode(', ', array_map(function ($item) {
            return is_array($item) ? json_encode($item) : (string) $item;
        }, $value));
    }

    /**
     * Parse Airtable URL format
     *
     * Supports: airtable://{baseId}/{tableName}?token={pat}&view={viewId}
     *
     * @param string $url Airtable source URL
     * @return array|WP_Error Parsed components or error
     */
    public static function parse_url(string $url)
    {
        // Check for airtable:// protocol
        if (strpos($url, 'airtable://') === 0) {
            $url = substr($url, 11); // Remove protocol
        }

        // Empty URL
        if (empty($url)) {
            return new WP_Error(
                'airtable_invalid_url',
                __('Invalid Airtable URL format.', 'tablecrafter-wp-data-tables')
            );
        }

        // Parse using http:// prefix for proper URL parsing
        // In "airtable://appXXXX/Tasks?token=xxx", after stripping protocol:
        // - "appXXXX" becomes the host
        // - "/Tasks" becomes the path
        $parts = parse_url('http://' . $url);

        // Get base_id from host
        $base_id = isset($parts['host']) ? $parts['host'] : '';

        // Get table_name from path (remove leading slash)
        $table_name = '';
        if (isset($parts['path'])) {
            $path = trim($parts['path'], '/');
            $path_segments = explode('/', $path);
            $table_name = isset($path_segments[0]) ? $path_segments[0] : '';
        }

        // Validate required components
        if (empty($base_id) || empty($table_name)) {
            return new WP_Error(
                'airtable_invalid_url',
                __('Airtable URL must include Base ID and Table Name.', 'tablecrafter-wp-data-tables')
            );
        }

        $result = [
            'base_id' => $base_id,
            'table_name' => $table_name,
            'token' => '',
            'view' => '',
        ];

        // Parse query parameters
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
            $result['token'] = isset($query['token']) ? $query['token'] : '';
            $result['view'] = isset($query['view']) ? $query['view'] : '';
        }

        return $result;
    }
}
