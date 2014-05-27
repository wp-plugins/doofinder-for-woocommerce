<?php
/**
 * Class that exposes JSON configuration info for Doofinder
 *
 * @package WordPress
 * @subpackage WooCommerce_Doofinder
 * @author Doofinder
 * @category Core
 * @version 0.1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WC_Doofinder_Config_Response
 */
class WC_Doofinder_Config_Response extends WC_Doofinder_Base_Response {

    /**
     * Serves the request directly to the output.
     *
     * @return void
     */
    public function serve_request()
    {
        $this->send_header('Content-Type', 'application/json; charset=utf-8');

        $language = strtoupper( substr( get_locale(), 0, 2 ) );

        $configuration = array(
            'platform' => array(
                'name' => 'WooCommerce',
                'version' => WC()->version,
                ),
            'module' => array(
                'version' => WCDoofinder()->version,
                'feed' => WCDoofinder()->router->get_request_url( 'products' ),
                'options' => array(
                    'language' => array($language),
                    'currency' => array_keys( get_woocommerce_currencies() ),
                    ),
                'configuration' => array(
                    $language => array(
                        'language' => $language,
                        'currency' => get_woocommerce_currency(),
                        'prices' => true, // TODO: Let the user configure whether to dump or not the prices.
                        'taxes' => (get_option( 'woocommerce_tax_display_shop' ) === 'incl'),
                        ),
                    ),
                'page_size' => null, // TODO: Let the user decide whether to paginate the feed or not.
                ),
            );

        echo json_encode( $configuration );
    }

}