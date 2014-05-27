<?php
/**
 * WooCommerce Settings Page/Tab for Doofinder
 *
 * @package WordPress
 * @subpackage WooCommerce_Doofinder
 * @author Doofinder
 * @category Admin
 * @version 0.1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Doofinder_Settings' ) ) :

/**
 * WC_Doofinder_Settings
 */
class WC_Doofinder_Settings extends WC_Settings_Page
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->id    = 'doofinder';
        $this->label = __( 'Doofinder', 'woocommerce-doofinder' );

        add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings()
    {
        $settings = array(
            // array( 'title' => __( 'Feed Options', 'woocommerce-doofinder' ), 'type' => 'title', 'desc' => '', 'id' => 'feed_options' ),
            // TODO: Enable custom attributes export option
            // array( 'type' => 'sectionend', 'id' => 'feed_options'),

            array( 'title' => __( 'Layer Options', 'woocommerce-doofinder' ), 'type' => 'title', 'desc' => '', 'id' => 'layer_options' ),
            array(
                'title'   => __( 'Enable the Layer', 'woocommerce-doofinder' ),
                'desc'    => '',
                'id'      => 'woocommerce_doofinder_layer_enabled',
                'type'    => 'checkbox',
                'default' => 'yes',
            ),
            array(
                'title'   => __( 'Layer Javascript Code', 'woocommerce-doofinder' ),
                'desc'    => __( 'Paste here the Javascript code you will find inside <em><strong>Configuration > Installation Scripts > Doofinder Layer</strong></em> in your <a href="https://app.doofinder.com/admin/config/scripts/" target="_blank">Doofinder Control Panel</a>.', 'woocommerce-doofinder' ),
                'id'      => 'woocommerce_doofinder_layer_code',
                'css'     => 'margin-top: 5px; width: 100%; height: 500px; font-family: Consolas,Monaco,monospace;',
                'type'    => 'textarea',
                'default' => '',
                ),
            array( 'type' => 'sectionend', 'id' => 'layer_options'),
            );

        return $settings;
    }

    /**
     * Save settings
     */
    public function save()
    {
        $settings = $this->get_settings();
        WC_Admin_Settings::save_fields( $settings );

        // re-save the script directly. WordPress will add slashes to the code.
        // this way we ensure that the <script> tags are saved.
        if ( isset( $_POST['woocommerce_doofinder_layer_code'] ) ) {
            update_option( 'woocommerce_doofinder_layer_code', $_POST['woocommerce_doofinder_layer_code'] );
        }
    }
}

endif;

return new WC_Doofinder_Settings();