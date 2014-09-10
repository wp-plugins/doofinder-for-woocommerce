<?php
/**
 * Plugin Name: Doofinder for WooCommerce
 * Plugin URI: https://github.com/doofinder/woocommerce-doofinder
 * Description: Integrate Doofinder in your WooCommerce site with almost no effort.
 * Version: 0.1.2
 * Author: Doofinder <support@doofinder.com>
 * Author URI: http://www.doofinder.com
 * License: GPLv2
 *
 * Requires at least: 3.8
 * Tested up to: 4.0
 *
 * @package WordPress
 * @subpackage WooCommerce_Doofinder
 * @category Core
 * @author Doofinder
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Initialize only if WooCommerce is installed
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ):

    if ( ! class_exists( 'WC_Doofinder' ) ):

    /**
     * Main Plugin Class
     *
     * @class WC_Doofinder
     * @version 0.1.2
     */
    class WC_Doofinder {

        /**
         * @var string
         */
        public $version = '0.1.2';

        /** Main Instance *****************************************************/

        /**
         * The only instance of WC_Doofinder
         *
         * @var WC_Doofinder
         */
        protected static $_instance = null;

        /**
         * Returns the only instance of WC_Doofinder
         *
         * @return WC_Doofinder
         */
        public static function instance()
        {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /** Hacking is forbidden **********************************************/

        /**
         * Cloning is forbidden.
         *
         * @since 1.0
         */
        public function __clone()
        {
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-doofinder' ), '0.1' );
        }

        /**
         * Unserializing instances of this class is forbidden.
         *
         * @since 1.0
         */
        public function __wakeup()
        {
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'woocommerce-doofinder' ), '0.1' );
        }

        /** Initialization ****************************************************/

        /**
         * WC_Doofinder constructor
         *
         * @since 1.0
         */
        public function __construct()
        {

            // auto-load classes on demand
            if ( function_exists( "__autoload" ) ) {
                spl_autoload_register( "__autoload" );
            }
            spl_autoload_register( array( $this, 'autoload' ) );

            add_action( 'init', array( __CLASS__, 'add_routes' ) );

            define('WC_DOOFINDER_FEED_NAME', 'doofinder');

            if ( is_admin() ) {
                // add the settings page to the WooCommerce settings tabs
                add_filter( 'woocommerce_get_settings_pages', array($this, 'setup_admin_options') );
            } elseif ( get_option( 'woocommerce_doofinder_layer_enabled', 'yes' ) == 'yes' ) {
                // if the layer is enabled attach the code to the footer of the page
                // to ensure that the search box has been rendered
                add_action( 'wp_footer', array( $this, 'output_doofinder_layer_code' ) );
            }
        }

        /**
         * Autoloader to load custom classes
         *
         * @param  string $classname Name of the class to load
         * @return void
         */
        public function autoload( $classname )
        {
            $classname = strtolower( $classname );
            $file = str_replace('wc_doofinder', 'wcdf', $classname);
            $file = 'class-' . str_replace( '_', '-', $file ) . '.php';
            $path = $this->plugin_path() . '/includes/';

            if ( is_readable( $path . $file ) ) {
                include_once( $path . $file );
                return;
            }
        }

        /** Hooks *************************************************************/

        /**
         * Filter to add custom settings tabs to the WooCommerce settings panel
         *
         * @param  array $settings Settings array
         * @return array           Modified settings array
         */
        public function setup_admin_options( $settings )
        {
            $settings[] = include( 'includes/class-wcdf-settings.php' );
            return $settings;
        }

        /**
         * Action to display the Doofinder Layer Javascript code
         *
         * @return void
         */
        public function output_doofinder_layer_code()
        {
            echo stripslashes( get_option( 'woocommerce_doofinder_layer_code' ) );
        }

        /** Helpers ***********************************************************/

        /**
         * Get the plugin path.
         *
         * @return string
         */
        public function plugin_path() {
            return untrailingslashit( plugin_dir_path( __FILE__ ) );
        }

        /** Plugin (De)Activation Stuff ***************************************/

        public static function add_routes()
        {
            add_feed( WC_DOOFINDER_FEED_NAME, array( __CLASS__, 'load_feed_template' ) );
            add_feed( WC_DOOFINDER_FEED_NAME . '-config', array( __CLASS__, 'load_feed_config_template' ) );
        }

        public static function load_feed_template()
        {
            load_template( plugin_dir_path( __FILE__ ) . 'templates/feed.php' );
        }

        public static function load_feed_config_template()
        {
            load_template( plugin_dir_path( __FILE__ ) . 'templates/config.php' );
        }

        /**
         * Activation Hook to configure routes and so on
         *
         * @return void
         */
        public static function plugin_enabled()
        {
            self::add_routes();
            flush_rewrite_rules();
        }

        /**
         * Deactivation Hook to flush routes
         *
         * @return void
         */
        public static function plugin_disabled()
        {
            flush_rewrite_rules();
            delete_option('woocommerce_doofinder_layer_code');
            delete_option('woocommerce_doofinder_layer_enabled');
        }

    } // WC_Doofinder

    endif;


// Plugin activation/deactivation
register_activation_hook( __FILE__, array( 'WC_Doofinder', 'plugin_enabled' ) );
register_deactivation_hook( __FILE__, array( 'WC_Doofinder', 'plugin_disabled' ) );

add_action( 'plugins_loaded', array( 'WC_Doofinder', 'instance' ), 0 );

function WCDoofinder()
{
    return WC_Doofinder::instance();
}

// Backwards compatibility
$GLOBALS['df_woocommerce'] = WCDoofinder();

endif;
