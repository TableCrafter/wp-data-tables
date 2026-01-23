<?php
/**
 * TableCrafter Security Handler
 *
 * Handles security-related functionality including:
 * - SSRF prevention
 * - Rate limiting
 * - IP address handling
 * - URL validation
 *
 * @package TableCrafter
 * @since 3.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TC_Security
{
    /**
     * Rate limiting constants
     */
    const RATE_LIMIT_MAX_REQUESTS = 30;
    const RATE_LIMIT_WINDOW_SECONDS = 60;

    /**
     * Singleton instance
     * @var TC_Security|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return TC_Security
     */
    public static function get_instance(): TC_Security
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
    }

    /**
     * SSRF (Server Side Request Forgery) Prevention
     *
     * Blocks private ranges and localhost to secure the proxy.
     *
     * @param string $url The URL to validate.
     * @return bool True if safe, false if blocked.
     */
    public function is_safe_url(string $url): bool
    {
        // Use WordPress Core's robust validation
        if (function_exists('wp_http_validate_url')) {
            return (bool) wp_http_validate_url($url);
        }

        // Fallback for very old WP versions
        $host = wp_parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return false;
        }

        if (in_array(strtolower($host), array('localhost', '127.0.0.1', '[::1]'))) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $is_private = !filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
            if ($is_private) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate image URLs safely
     *
     * @param string $url URL to validate
     * @return bool True if safe image URL
     */
    public function is_safe_image_url(string $url): bool
    {
        // Prevent javascript: and data: schemes except safe data:image
        if (preg_match('/^(javascript|vbscript|data:(?!image\/)):/i', $url)) {
            return false;
        }

        // Check for image file extensions
        if (preg_match('/\.(jpeg|jpg|gif|png|webp|bmp)$/i', $url)) {
            return true;
        }

        // Allow safe data:image URLs (but not SVG due to XSS risks)
        if (preg_match('/^data:image\/(jpeg|jpg|gif|png|webp|bmp);base64,/i', $url)) {
            return true;
        }

        return false;
    }

    /**
     * Sanitize image URLs
     *
     * @param string $url URL to sanitize
     * @return string|false Sanitized URL or false if invalid
     */
    public function sanitize_image_url(string $url)
    {
        if (strpos($url, 'data:image') === 0) {
            if (preg_match('/^data:image\/(jpeg|jpg|gif|png|webp|bmp);base64,[A-Za-z0-9+\/=]+$/i', $url)) {
                return $url;
            }
            return false;
        }

        $clean_url = filter_var($url, FILTER_VALIDATE_URL);
        return $clean_url ? $clean_url : false;
    }

    /**
     * Validate date strings safely
     *
     * @param string $str String to validate
     * @return bool True if valid date string
     */
    public function is_valid_date_string(string $str): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}:\d{2}(\.\d{3})?Z?)?$/', $str)) {
            return false;
        }
        return (bool) strtotime($str);
    }

    /**
     * Validate URLs safely for display (XSS prevention)
     *
     * @param string $str URL string to validate
     * @return bool True if safe for display
     */
    public function is_safe_display_url(string $str): bool
    {
        if (preg_match('/^(javascript|vbscript|data|file|ftp):/i', $str)) {
            return false;
        }

        if (!preg_match('/^https?:\/\//i', $str)) {
            return false;
        }

        return filter_var($str, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Check if rate limit is exceeded
     *
     * @return bool True if rate limit exceeded
     */
    public function is_rate_limited(): bool
    {
        $identifier = get_current_user_id();
        if ($identifier === 0) {
            $identifier = $this->get_client_ip();
        }

        $transient_key = 'tc_rate_' . md5((string) $identifier);
        $current_count = get_transient($transient_key);

        if ($current_count === false) {
            set_transient($transient_key, 1, self::RATE_LIMIT_WINDOW_SECONDS);
            return false;
        }

        if ((int) $current_count >= self::RATE_LIMIT_MAX_REQUESTS) {
            $this->log_error('Rate Limit Exceeded', array(
                'identifier' => $identifier,
                'count' => $current_count
            ));
            return true;
        }

        set_transient($transient_key, (int) $current_count + 1, self::RATE_LIMIT_WINDOW_SECONDS);
        return false;
    }

    /**
     * Get Client IP Address
     *
     * Handles proxies and load balancers safely.
     * SECURITY: Only trusts proxy headers when explicitly configured via filter.
     *
     * @return string The client IP address
     */
    public function get_client_ip(): string
    {
        $trusted_headers = apply_filters('tablecrafter_trusted_ip_headers', array());

        $proxy_headers = array(
            'cloudflare' => 'HTTP_CF_CONNECTING_IP',
            'forwarded' => 'HTTP_X_FORWARDED_FOR',
            'real_ip' => 'HTTP_X_REAL_IP',
        );

        foreach ($trusted_headers as $header_key) {
            if (isset($proxy_headers[$header_key])) {
                $header = $proxy_headers[$header_key];
                if (!empty($_SERVER[$header])) {
                    $ip = explode(',', sanitize_text_field(wp_unslash($_SERVER[$header])))[0];
                    $ip = trim($ip);

                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return 'unknown_' . wp_hash(wp_json_encode($_SERVER));
    }

    /**
     * Verify and sanitize nonce
     *
     * @param string $action Nonce action
     * @param string $nonce_key POST/GET key containing nonce
     * @param string $method HTTP method ('POST' or 'GET')
     * @return bool True if valid nonce
     */
    public function verify_nonce(string $action, string $nonce_key = 'nonce', string $method = 'POST'): bool
    {
        $source = $method === 'POST' ? $_POST : $_GET;
        $nonce = isset($source[$nonce_key]) ? sanitize_text_field(wp_unslash($source[$nonce_key])) : '';

        if (empty($nonce)) {
            return false;
        }

        return (bool) wp_verify_nonce($nonce, $action);
    }

    /**
     * Log security-related errors
     *
     * @param string $message Error message
     * @param array $context Additional context
     * @return void
     */
    public function log_error(string $message, array $context = array()): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log = sprintf('[TableCrafter Security] %s | Context: %s', $message, json_encode($context));
            error_log($log);
        }
    }

    /**
     * Encrypt a token for secure storage
     *
     * Uses WordPress salt for encryption key.
     *
     * @param string $token Plain text token
     * @return string Encrypted token (base64 encoded)
     */
    public function encrypt_token(string $token): string
    {
        if (empty($token)) {
            return '';
        }

        $key = $this->get_encryption_key();
        $iv = openssl_random_pseudo_bytes(16);

        $encrypted = openssl_encrypt(
            $token,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            return '';
        }

        // Combine IV and encrypted data, then base64 encode
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a stored token
     *
     * @param string $encrypted_token Base64 encoded encrypted token
     * @return string Decrypted token
     */
    public function decrypt_token(string $encrypted_token): string
    {
        if (empty($encrypted_token)) {
            return '';
        }

        $key = $this->get_encryption_key();
        $data = base64_decode($encrypted_token);

        if ($data === false || strlen($data) < 17) {
            return '';
        }

        // Extract IV (first 16 bytes) and encrypted data
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $decrypted !== false ? $decrypted : '';
    }

    /**
     * Get encryption key from WordPress salts
     *
     * @return string 32-byte encryption key
     */
    private function get_encryption_key(): string
    {
        // Use WordPress AUTH_KEY salt, hash to 32 bytes for AES-256
        $salt = defined('AUTH_KEY') ? AUTH_KEY : 'tablecrafter-default-salt';
        return hash('sha256', $salt, true);
    }
}
