<?php
/**
 * Class with common tools for responses.
 *
 * TODO: Refactor from WC_Doofinder_Products_Response to this class if possible.
 *
 * @package WordPress
 * @subpackage WooCommerce_Doofinder
 * @author Doofinder
 * @category Core
 * @version 0.1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WC_Doofinder_Base_Response
 */
class WC_Doofinder_Base_Response {

    public function serve_request() {}

    /** Helper Functions ******************************************************/

    /**
     * Send a HTTP header
     *
     * @param  string  $key     Header to send
     * @param  string  $value   Value to send
     * @param  boolean $replace Replace previous headers of the same type
     * @return void
     */
    public function send_header( $key, $value, $replace = true )
    {
        header( sprintf( '%s: %s', $key, $value ), $replace );
    }
}