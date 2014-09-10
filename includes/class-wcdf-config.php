<?php
/**
 * Config class for Doofinder for WooCommerce
 *
 * @package WordPress
 * @subpackage WooCommerce_Doofinder
 * @author Doofinder
 * @category Admin
 * @version 0.1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Doofinder_Config' ) ) :

class WC_Doofinder_Config {

    public function render()
    {
        $language = strtoupper( substr( get_locale(), 0, 2 ) );

        $configuration = array(
            'platform' => array(
                'name' => 'WooCommerce',
                'version' => WC()->version,
                ),
            'module' => array(
                'version' => WCDoofinder()->version,
                'feed' => get_feed_link(WC_DOOFINDER_FEED_NAME),
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

        return json_encode( $configuration );
    }
}

endif;
