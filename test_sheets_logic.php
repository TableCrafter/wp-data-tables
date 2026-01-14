<?php
// Mock WP functions
function wp_safe_remote_get($url, $args = array())
{
    echo "Fetching URL: $url\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $args['user-agent'] ?? 'WordPress');
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return array(
        'body' => $response,
        'response' => array('code' => $code, 'message' => 'OK')
    );
}
function wp_remote_retrieve_response_code($response)
{
    return $response['response']['code'];
}
function wp_remote_retrieve_body($response)
{
    return $response['body'];
}
class WP_Error
{
    public function __construct($code, $message)
    {
        echo "WP_Error: $message\n";
    }
}

// Logic to test
function test_fetch($url)
{
    $original_url = $url;
    $is_csv = false;

    // 1. Detection Logic
    if (preg_match('/docs\.google\.com\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches)) {
        $sheet_id = $matches[1];
        $url = "https://docs.google.com/spreadsheets/d/{$sheet_id}/export?format=csv";

        $url_parts = parse_url($original_url);
        if (isset($url_parts['query'])) {
            parse_str($url_parts['query'], $query_params);
            if (isset($query_params['gid'])) {
                $url .= "&gid=" . $query_params['gid'];
            }
        }
        $is_csv = true;
    }

    echo "Transformed URL: $url\n";

    $response = wp_safe_remote_get($url, array('user-agent' => 'TableCrafter/Test'));
    $code = wp_remote_retrieve_response_code($response);
    echo "Response Code: $code\n";

    $body = wp_remote_retrieve_body($response);
    // echo "Body: " . substr($body, 0, 100) . "...\n";

    if ($is_csv) {
        $json = parse_csv_to_json($body);
        echo "Parsed JSON count: " . count($json) . "\n";
        print_r(array_slice($json, 0, 2));
    }
}

function parse_csv_to_json($csv_string)
{
    $lines = explode("\n", trim($csv_string));
    if (empty($lines))
        return [];
    $headers = str_getcsv(array_shift($lines));
    $data = [];
    foreach ($lines as $line) {
        if (empty(trim($line)))
            continue;
        $row = str_getcsv($line);
        if (count($row) !== count($headers))
            continue;
        $item = array();
        foreach ($headers as $index => $key) {
            $item[$key] = isset($row[$index]) ? $row[$index] : null;
        }
        $data[] = $item;
    }
    return $data;
}

// Test Run
test_fetch('https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit?gid=0#gid=0');
