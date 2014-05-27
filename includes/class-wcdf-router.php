<?php
/**
 * WooCommerce Router for Doofinder URLs
 *
 * @package WordPress
 * @subpackage WooCommerce_Doofinder
 * @author Doofinder
 * @category Core
 * @version 0.1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WC_Doofinder_Router
 */
class WC_Doofinder_Router {

    /**
     * Constructor
     */
    public function __construct()
    {
        // add filter to enable custom query vars
        add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

        // add filter to enable custom URLs
        add_filter( 'init', array( 'WC_Doofinder_Router', 'configure_routes' ), 0);

        // add action to handle requests
        add_action( 'parse_request', array( $this, 'handle_request' ) );
    }

    /**
     * Add custom query vars to those allowed by WordPress
     *
     * @param array $vars Current enabled query variables
     * @return array Modified vars
     */
    public function add_query_vars( $vars )
    {
        $vars[] = 'df-wc-route';

        if ( array_search( 'limit', $vars ) === false)
            $vars[] = 'limit';
        if ( array_search( 'offset', $vars ) === false)
            $vars[] = 'offset';

        return $vars;
    }

    /**
     * Setup our custom routes:
     *
     *   /doofinder/woocommerce/products
     *   /doofinder/woocommerce/config
     *
     * @return void
     */
    public static function configure_routes()
    {
        // TODO: Check if this rule exists (other doofinder plugin) and if not, add it.
        // add_rewrite_rule( '^doofinder\/woocommerce\/?$', 'index.php?df-wc-route=/', 'top');
        add_rewrite_rule( '^doofinder\/woocommerce\/([^/]*)/?$', 'index.php?df-wc-route=$matches[1]', 'top');
    }

    /**
     * Force the rewrite cache refresh
     *
     * @return void
     */
    public static function flush_routes()
    {
        flush_rewrite_rules();
    }

    /**
     * Analyze the request and handle it if the df-wc-route parameter is present
     * Makes use of classes from ./includes/responses
     *
     * @return void
     */
    public function handle_request()
    {
        global $wp;

        if ( ! empty( $_GET['limit'] ) ) {
            $wp->query_vars['limit'] = intval( $_GET['limit'] );

            if ( isset( $_GET['offset'] ) ) {
                $wp->query_vars['offset'] = intval( $_GET['offset'] );
            } else {
                $wp->query_vars['offset'] = 0;
            }
        }

        if ( ! empty( $wp->query_vars['df-wc-route'] ) )
        {
            $endpoint = $wp->query_vars['df-wc-route'];
            $endpoint = ucwords( str_replace( array( '-', '_' ), ' ', $endpoint ) );
            $endpoint = str_replace( ' ', '_', $endpoint );

            $classname = 'WC_Doofinder_' . $endpoint . '_Response';

            if ( class_exists( $classname ) ) {
                $handler = new $classname($wp->query_vars);
                $handler->serve_request();
            } else {
                include( get_query_template( '404' ) );
            }

            exit;
        }
    }

    /**
     * Translates the value of $df_wc_route into a URL of this plugin.
     *
     * @param  string  $df_wc_route A string like "products", "config" and so on
     * @param  boolean $ssl         Force SSL, default null
     * @return string               The final URL
     */
    public function get_request_url( $df_wc_route, $ssl = null )
    {
        if ( is_null( $ssl ) ) {
            $scheme = parse_url( get_option( 'home' ), PHP_URL_SCHEME );
        } elseif ( $ssl ) {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }

        if ( get_option('permalink_structure') ) {
            return esc_url_raw( trailingslashit( home_url( '/doofinder/woocommerce/' . $df_wc_route, $scheme ) ) );
        } else {
            return esc_url_raw( add_query_arg( 'df-wc-route', $df_wc_route, trailingslashit( home_url( '', $scheme ) ) ) );
        }
    }

}