<?php
/**
 * TableCrafter Kit.com Bridge
 * 
 * A standalone-ready class for handling interactions with the Kit.com (ConvertKit) API.
 * 
 * @package TableCrafter
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class TableCrafter_Kit_Bridge
{
    /**
     * Singleton instance.
     * @var TableCrafter_Kit_Bridge|null
     */
    private static $instance = null;

    /**
     * API Credentials
     */
    private $api_secret;
    private $form_id;
    private $fallback_email;

    /**
     * Get singleton instance.
     * 
     * @return TableCrafter_Kit_Bridge
     */
    public static function get_instance(): TableCrafter_Kit_Bridge
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        // Default credentials (can be overridden via initialization)
        $this->api_secret = 'dRZ4dGm1KseJ4DF5kvdPrB0iZwBlrHe87WaYk1-wF3U';
        $this->form_id = '8953939';
        $this->fallback_email = 'info@fahdmurtaza.com';
    }

    /**
     * Set API credentials.
     * 
     * @param string $api_secret
     * @param string $form_id
     * @return void
     */
    public function set_credentials(string $api_secret, string $form_id): void
    {
        $this->api_secret = $api_secret;
        $this->form_id = $form_id;
    }

    /**
     * Set fallback email.
     * 
     * @param string $email
     * @return void
     */
    public function set_fallback_email(string $email): void
    {
        $this->fallback_email = $email;
    }

    /**
     * Subscribe an email to a Kit.com form.
     * 
     * @param string $email
     * @param array $fields Additional custom fields
     * @return array|WP_Error Success message or WP_Error
     */
    public function subscribe(string $email, array $fields = [])
    {
        if (!is_email($email)) {
            return new WP_Error('invalid_email', 'Invalid email address');
        }

        $response = wp_remote_post("https://api.convertkit.com/v3/forms/{$this->form_id}/subscribe", array(
            'body' => wp_json_encode(array(
                'api_secret' => $this->api_secret,
                'email' => $email,
                'fields' => $fields
            )),
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            $this->send_fallback_email($email, $response->get_error_message());
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($body['subscription'])) {
            $error_msg = isset($body['message']) ? $body['message'] : 'Unknown API error';
            $this->send_fallback_email($email, $error_msg);
            return new WP_Error('api_error', $error_msg);
        }

        return array(
            'success' => true,
            'message' => 'Subscription successful',
            'data' => $body
        );
    }

    /**
     * Send a fallback email if the API fails.
     * 
     * @param string $email
     * @param string $error
     * @return void
     */
    private function send_fallback_email(string $email, string $error): void
    {
        if (empty($this->fallback_email)) {
            return;
        }

        wp_mail(
            $this->fallback_email,
            'Kit Bridge Lead (API Failed): ' . $email,
            sprintf(
                "Kit.com API failed to process a new lead.\n\nEmail: %s\nSite: %s\nError: %s",
                $email,
                get_site_url(),
                $error
            )
        );
    }
}
