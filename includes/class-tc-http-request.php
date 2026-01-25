<?php
/**
 * TableCrafter HTTP Request Handler
 *
 * Provides unified, consistent HTTP request handling across all TableCrafter components.
 * Eliminates inconsistencies between curl and wp_remote_get usage that cause 
 * intermittent data fetching failures.
 *
 * Business Impact: Solves "JSON links not working" customer complaints and 
 * provides reliable data source connectivity.
 *
 * @package TableCrafter
 * @since 3.5.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class TC_HTTP_Request
{
    /**
     * Request type constants
     */
    const TYPE_DATA_FETCH = 'data_fetch';
    const TYPE_HEALTH_CHECK = 'health_check';
    const TYPE_CACHE_WARMUP = 'cache_warmup';

    /**
     * Configuration constants
     */
    const DEFAULT_TIMEOUT = 30;
    const HEALTH_CHECK_TIMEOUT = 10;
    const CONNECT_TIMEOUT = 10;
    const MAX_RETRIES = 3;
    const RETRY_DELAY_BASE = 1; // seconds

    /**
     * Singleton instance
     * @var TC_HTTP_Request|null
     */
    private static $instance = null;

    /**
     * Security handler
     * @var TC_Security
     */
    private $security;

    /**
     * Request statistics for monitoring
     * @var array
     */
    private $stats = [
        'requests_made' => 0,
        'requests_successful' => 0,
        'requests_failed' => 0,
        'retries_performed' => 0,
        'average_response_time' => 0
    ];

    /**
     * Get singleton instance
     *
     * @return TC_HTTP_Request
     */
    public static function get_instance(): TC_HTTP_Request
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton
     */
    private function __construct()
    {
        if (class_exists('TC_Security')) {
            $this->security = TC_Security::get_instance();
        }
    }

    /**
     * Make HTTP request with unified handling
     *
     * @param string $url Target URL
     * @param string $type Request type (data_fetch, health_check, cache_warmup)
     * @param array $options Additional options
     * @return array|WP_Error Response data or error
     */
    public function request(string $url, string $type = self::TYPE_DATA_FETCH, array $options = [])
    {
        $start_time = microtime(true);
        $this->stats['requests_made']++;

        // Validate URL security
        if ($this->security && !$this->security->is_safe_url($url)) {
            $this->log_request_error('Security check failed', $url, [], 0, 'SSRF_BLOCKED');
            return new WP_Error('security_error', 'The provided URL is blocked for safety.');
        }

        // Configure request parameters based on type
        $config = $this->get_request_config($type, $options);

        // Attempt request with retry logic
        $last_error = null;
        for ($attempt = 0; $attempt <= $config['max_retries']; $attempt++) {
            if ($attempt > 0) {
                $this->stats['retries_performed']++;
                $delay = pow(self::RETRY_DELAY_BASE, $attempt);
                $this->log_request_info("Retrying request (attempt $attempt) after {$delay}s", $url);
                sleep($delay);
            }

            $result = $this->make_single_request($url, $config);
            
            if (!is_wp_error($result)) {
                $response_time = microtime(true) - $start_time;
                $this->update_response_time_stats($response_time);
                $this->stats['requests_successful']++;
                
                $this->log_request_success($url, $response_time, $attempt);
                return $result;
            }

            $last_error = $result;
            
            // Don't retry on certain types of errors
            if ($this->should_not_retry($result)) {
                break;
            }
        }

        // All attempts failed
        $this->stats['requests_failed']++;
        $this->log_request_error(
            'Request failed after all retry attempts',
            $url,
            $config,
            $attempt,
            $last_error->get_error_code()
        );

        return $last_error;
    }

    /**
     * Get request configuration based on type
     *
     * @param string $type Request type
     * @param array $user_options User-provided options
     * @return array Configuration array
     */
    private function get_request_config(string $type, array $user_options): array
    {
        $defaults = [
            'timeout' => self::DEFAULT_TIMEOUT,
            'connect_timeout' => self::CONNECT_TIMEOUT,
            'max_retries' => self::MAX_RETRIES,
            'headers' => [],
            'user_agent' => 'TableCrafter/' . TABLECRAFTER_VERSION . ' (WordPress Plugin)',
            'ssl_verify' => true,
            'follow_redirects' => true
        ];

        // Adjust defaults based on request type
        switch ($type) {
            case self::TYPE_HEALTH_CHECK:
                $defaults['timeout'] = self::HEALTH_CHECK_TIMEOUT;
                $defaults['max_retries'] = 1;
                break;
            case self::TYPE_CACHE_WARMUP:
                $defaults['timeout'] = self::HEALTH_CHECK_TIMEOUT;
                $defaults['max_retries'] = 2;
                break;
        }

        return array_merge($defaults, $user_options);
    }

    /**
     * Make a single HTTP request
     *
     * @param string $url Target URL
     * @param array $config Request configuration
     * @return array|WP_Error Response or error
     */
    private function make_single_request(string $url, array $config)
    {
        // Use WordPress HTTP API as primary method (more reliable than curl)
        $wp_args = [
            'timeout' => $config['timeout'],
            'redirection' => $config['follow_redirects'] ? 5 : 0,
            'httpversion' => '1.1',
            'user-agent' => $config['user_agent'],
            'blocking' => true,
            'headers' => $config['headers'],
            'cookies' => [],
            'body' => null,
            'compress' => false,
            'decompress' => true,
            'sslverify' => $config['ssl_verify'],
            'stream' => false,
            'filename' => null
        ];

        $response = wp_remote_get($url, $wp_args);

        if (is_wp_error($response)) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        // Handle non-200 responses
        if ($response_code !== 200) {
            return new WP_Error(
                'http_error',
                sprintf('HTTP %d: %s', $response_code, wp_remote_retrieve_response_message($response)),
                ['response_code' => $response_code, 'url' => $url]
            );
        }

        // Validate JSON if expected
        if (strpos(wp_remote_retrieve_header($response, 'content-type'), 'json') !== false || 
            $this->looks_like_json($body)) {
            $data = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new WP_Error(
                    'json_parse_error',
                    'Invalid JSON response: ' . json_last_error_msg(),
                    ['url' => $url, 'body_preview' => substr($body, 0, 200)]
                );
            }
            
            return $data;
        }

        // Return raw body for non-JSON responses (CSV, etc.)
        return ['body' => $body, 'headers' => wp_remote_retrieve_headers($response)];
    }

    /**
     * Check if content looks like JSON
     *
     * @param string $content Content to check
     * @return bool True if looks like JSON
     */
    private function looks_like_json(string $content): bool
    {
        $trimmed = trim($content);
        return (strlen($trimmed) > 0 && 
                ($trimmed[0] === '{' || $trimmed[0] === '['));
    }

    /**
     * Determine if error should not be retried
     *
     * @param WP_Error $error Error object
     * @return bool True if should not retry
     */
    private function should_not_retry(WP_Error $error): bool
    {
        $no_retry_codes = ['security_error', 'json_parse_error'];
        $error_code = $error->get_error_code();
        
        // Don't retry security errors or JSON parse errors
        if (in_array($error_code, $no_retry_codes, true)) {
            return true;
        }

        // Don't retry 4xx client errors (except 429 rate limit)
        $error_data = $error->get_error_data();
        if (isset($error_data['response_code'])) {
            $code = $error_data['response_code'];
            return ($code >= 400 && $code < 500 && $code !== 429);
        }

        return false;
    }

    /**
     * Update average response time statistics
     *
     * @param float $response_time Response time in seconds
     */
    private function update_response_time_stats(float $response_time): void
    {
        $total_requests = $this->stats['requests_successful'];
        $current_average = $this->stats['average_response_time'];
        
        // Calculate running average
        $this->stats['average_response_time'] = 
            (($current_average * ($total_requests - 1)) + $response_time) / $total_requests;
    }

    /**
     * Log successful request
     *
     * @param string $url Request URL
     * @param float $response_time Response time
     * @param int $attempts Number of attempts
     */
    private function log_request_success(string $url, float $response_time, int $attempts): void
    {
        $message = sprintf(
            'HTTP request successful: %s (%.2fs, %d attempts)',
            $this->sanitize_url_for_log($url),
            $response_time,
            $attempts + 1
        );
        
        $this->log_request_info($message, $url);
    }

    /**
     * Log request error
     *
     * @param string $message Error message
     * @param string $url Request URL
     * @param array $config Request configuration
     * @param int $attempts Number of attempts made
     * @param string $error_code Error code
     */
    private function log_request_error(string $message, string $url, array $config, int $attempts, string $error_code): void
    {
        $log_data = [
            'url' => $this->sanitize_url_for_log($url),
            'message' => $message,
            'error_code' => $error_code,
            'attempts' => $attempts,
            'timeout' => $config['timeout'] ?? 'unknown',
            'stats' => $this->stats
        ];

        if (function_exists('error_log')) {
            error_log('TableCrafter HTTP Error: ' . wp_json_encode($log_data));
        }
    }

    /**
     * Log informational message
     *
     * @param string $message Log message
     * @param string $url Request URL
     */
    private function log_request_info(string $message, string $url): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            $log_data = [
                'url' => $this->sanitize_url_for_log($url),
                'message' => $message
            ];
            error_log('TableCrafter HTTP Info: ' . wp_json_encode($log_data));
        }
    }

    /**
     * Sanitize URL for logging (remove sensitive parameters)
     *
     * @param string $url Original URL
     * @return string Sanitized URL
     */
    private function sanitize_url_for_log(string $url): string
    {
        $parsed = wp_parse_url($url);
        if (!$parsed) {
            return '[Invalid URL]';
        }

        $sanitized = ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? 'unknown');
        
        if (isset($parsed['port'])) {
            $sanitized .= ':' . $parsed['port'];
        }
        
        if (isset($parsed['path'])) {
            $sanitized .= $parsed['path'];
        }

        // Hide query parameters that might contain sensitive data
        if (isset($parsed['query'])) {
            $sanitized .= '?[' . substr_count($parsed['query'], '&') + 1 . ' parameters]';
        }

        return $sanitized;
    }

    /**
     * Get request statistics for monitoring
     *
     * @return array Statistics array
     */
    public function get_stats(): array
    {
        return $this->stats;
    }

    /**
     * Reset statistics
     */
    public function reset_stats(): void
    {
        $this->stats = [
            'requests_made' => 0,
            'requests_successful' => 0,
            'requests_failed' => 0,
            'retries_performed' => 0,
            'average_response_time' => 0
        ];
    }

    /**
     * Get success rate percentage
     *
     * @return float Success rate (0-100)
     */
    public function get_success_rate(): float
    {
        if ($this->stats['requests_made'] === 0) {
            return 100.0;
        }
        
        return ($this->stats['requests_successful'] / $this->stats['requests_made']) * 100;
    }
}