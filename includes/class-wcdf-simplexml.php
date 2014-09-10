<?php
/**
 * SimpleXML extension for Doofinder for WooCommerce
 *
 * @package WordPress
 * @subpackage WooCommerce_Doofinder
 * @author Doofinder
 * @category Admin
 * @version 0.1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Doofinder_SimpleXML' ) ) :

class WC_Doofinder_SimpleXML extends SimpleXMLElement {
    public function addCData( $string ) {
        $node = dom_import_simplexml( $this );
        $node->appendChild( $node->ownerDocument->createCDATASection( $string ) );
    }
}

endif;
