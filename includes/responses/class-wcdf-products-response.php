<?php
/**
 * Class that outputs the data feed
 *
 * @package WordPress
 * @subpackage WooCommerce_Doofinder
 * @author Doofinder
 * @category Core
 * @version 0.1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Doofinder_Products_Response extends WC_Doofinder_Base_Response {
    const CATEGORY_SEPARATOR = ' > ';
    const FACET_SEPARATOR = '/';

    protected $output;
    protected $padding = "";

    protected $loop = null;
    protected $cached_terms = null;

    protected $paged = false;
    protected $limit = null;
    protected $offset = null;

    protected $prices_inc_taxes;

    public function __construct( $query_vars )
    {
        if ( isset( $query_vars['limit'] ) && intval( $query_vars['limit'] ) > 0 )
        {
            $this->limit = intval( $query_vars['limit'] );

            if ( isset( $query_vars['offset'] ) )
            {
                $this->offset = intval( $query_vars['offset'] );
            } else {
                $this->offset = 0;
            }

            $this->paged = true;
        }

        $this->prices_inc_taxes = (get_option('woocommerce_tax_display_shop') === 'incl');
    }

    public function serve_request()
    {
        $this->load_products();

        if ( $this->loop->have_posts() ) {

            $this->send_header( 'Cache-Control', 'must-revalidate, post-check=0, pre-check=0' );
            $this->send_header( 'Content-Type', 'application/xml; charset=utf-8' );
            $this->send_header( 'Expires', '0' );
            $this->send_header( 'Pragma', 'public' );

            $this->start_response();

            if ( $this->is_first_page() ) {
                $this->feed_header();
            }

            while ( $this->loop->have_posts() ) {
                $post = $this->loop->next_post();
                $this->feed_item( get_product( $post->ID ) );
            }

            if ( $this->is_last_page() )
            $this->feed_footer();
            $this->end_response();

        }
    }

    protected function load_products()
    {
        // obtain products
        $args = array(
                'post_type' => 'product',
                'post_status' => 'publish',

                'meta_query' => array(
                                    array(
                                        'key' => '_visibility',
                                        'value' => array('search', 'visible'),
                                        'compare' => 'IN',
                                    ),
                                ),

                'orderby' => 'ID',
                'order' => 'ASC',

                'cache_results' => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                );

        if ( $this->paged ) {
            $args['posts_per_page'] = $this->limit;
            $args['offset'] = $this->offset;
        } else {
            $args['nopaging'] = true;
        }

        $this->loop = new WP_Query( $args );

        // cache terms
        $this->cache_terms( 'product_cat', true );
        $this->cache_terms( 'product_tag', false );
    }

    /** Output Handling *******************************************************/

    protected function start_response()
    {
        $this->end_response();
        $this->output = fopen( 'php://output', 'w' );
    }

    protected function end_response()
    {
        @fclose($this->output);
    }

    protected function write_response($text, $EOL = true)
    {
        fwrite($this->output, $this->padding . $text . ($EOL ? PHP_EOL : ""));
    }

    /** Feed Output ***********************************************************/

    protected function feed_header()
    {
        $this->write_response( '<?xml version="1.0"?>' );
        $this->write_response( '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' );

        $this->indent();
        $this->write_response( '<channel>' );

        $this->indent();
        $this->write_response( $this->xml_tag( 'title', get_bloginfo('name') ) );
        $this->write_response( $this->xml_tag( 'link', get_bloginfo( 'url' ), false ) );
        $this->write_response( $this->xml_tag( 'description', get_bloginfo('description') ) );
    }

    protected function feed_footer()
    {
        $this->unindent();
        $this->write_response( '</channel>' );

        $this->unindent();
        $this->write_response( '</rss>' );
    }

    protected function feed_item( $product )
    {
        $this->write_response('<item>');
        $this->indent();

        $this->write_response( $this->xml_tag( 'id', $product->id, false ) );
        $this->write_response( $this->xml_tag( 'title', $product->get_title() ) );
        $this->write_response( $this->xml_tag( 'link', $product->get_permalink(), false ) );
        $this->write_response( $this->xml_tag( 'image_link', $this->get_product_image_link( $product ), false ) );

        // descriptions
        $description = trim( apply_filters( 'the_content', $product->get_post_data()->post_content ) );
        $short_description = trim( apply_filters( 'woocommerce_short_description', $product->get_post_data()->post_excerpt ) );

        if ( empty( $description ) ) {
            $description = $short_description;
            $short_description = null;
        }

        if ( ! empty( $description ) ) {
            $this->write_response( $this->xml_tag( 'description', $description ) );
        }

        if ( ! empty( $short_description ) ) {
            $this->write_response( $this->xml_tag( 'short_description', $short_description ) );
        }

        // availability
        $availability = $product->is_purchasable() && $product->is_in_stock() ? 'in stock' : 'out of stock';
        $this->write_response( $this->xml_tag( 'availability', $availability, false) );

        // MPN
        if ( $product->get_sku() ) {
            $this->write_response( $this->xml_tag( 'mpn', $product->get_sku() ) );
        }

        // prices
        $price = $this->get_product_price_with_taxes( $product, $product->get_regular_price() );
        $this->write_response( $this->xml_tag( 'price', $price, false ) );

        if ( $product->is_on_sale() && $product->get_regular_price() > $product->get_sale_price() )
        {
            $sale_price = $this->get_product_price_with_taxes( $product, $product->get_sale_price() );
            $this->write_response( $this->xml_tag( 'sale_price', $sale_price, false ) );
        }

        // categories
        foreach ( $this->get_product_terms( $product, 'product_cat', true ) as $category ) {
            $this->write_response( $this->xml_tag( 'product_type', $category ) );
        }

        // tags
        $tags = $this->get_product_terms( $product, 'product_tag' );
        if ( count( $tags ) > 0 ) {
            $this->write_response( $this->xml_tag( 'tags', implode( self::FACET_SEPARATOR, $tags ) ) );
        }

        $this->unindent();
        $this->write_response('</item>');
    }

    /** XML Output ************************************************************/

    protected function xml_tag( $tag, $value = null, $cdata = true )
    {
        return sprintf("<%s>%s</%s>", $tag, ( $cdata ? $this->cdata( $value ) : $value ), $tag);
    }

    protected function cdata( $value ) {
        return is_numeric( $value ) ? $value : sprintf( "<![CDATA[%s]]>", html_entity_decode( $value, ENT_COMPAT, 'UTF-8' ) );
    }

    protected function indent() {
        $this->padding = sprintf("  %s", $this->padding);
    }

    protected function unindent() {
        $this->padding = substr($this->padding, 2);
    }

    /** Pagination Helpers ****************************************************/

    protected function is_first_page()
    {
        return ( ! $this->paged || $this->offset === 0);
    }

    protected function is_last_page()
    {
        return ( ! $this->paged || $this->offset + $this->limit > $this->loop->found_posts );
    }

    /** Content Helpers *******************************************************/

    protected function get_product_image_link( $product )
    {
        if ( $image_id = $product->get_image_id() ) {
            $image = wp_get_attachment_image_src( $image_id, 'thumbnail-size', true);
            return $image[0];
        }
    }

    protected function get_product_price_with_taxes( $product, $price )
    {
        if ( $this->prices_inc_taxes ) {
            return $product->get_price_including_tax( 1, $price );
        } else {
            return $product->get_price_excluding_tax( 1, $price );
        }
    }

    protected function get_product_terms( $product, $taxonomy, $unique = false )
    {
        $results = array();

        if ( isset( $this->cached_terms[$taxonomy] ) ) {

            $terms = get_the_terms( $product->get_post_data()->ID, $taxonomy );

            if ( $terms ) {

                foreach ( $terms as $term ) {
                    $results[] = $this->cached_terms[$taxonomy][$term->term_id];
                }

                if ( $unique ) {
                    sort($results);
                    $results = array_reverse( $results );
                    $test = null;

                    foreach ( $results as $key => $value ) {
                        if ( empty( $test ) ) {
                            $test = $value;
                        } elseif ( strpos( $test, $value ) == 0 ) {
                            unset( $results[$key] );
                        } else {
                            $test = $value;
                        }
                    }
                } else {
                    $results = array_unique( $results );
                }
            }

        }

        return $results;
    }

    /** Caching ***************************************************************/

    protected function cache_terms( $taxonomy, $cached_as_path = false )
    {
        if ( isset( $this->cached_terms[$taxonomy] ) ) return;

        $args = array(
            'hide_empty' => 1,
            );

        foreach ( get_terms( $taxonomy, $args ) as $key => $term ) {
            $this->cached_terms[$taxonomy][intval( $term->term_id )] = $term;
        }

        if ( $cached_as_path ) {
            $terms_tree = array();

            foreach ( $this->cached_terms[$taxonomy] as $term_id => $term ) {
                $terms_tree[intval( $term_id )] = $this->get_flattened_path( $term, $this->cached_terms[$taxonomy] );
            }

            $this->cached_terms[$taxonomy] = $terms_tree;
        } else {
            foreach ( $this->cached_terms[$taxonomy] as $term_id => $term ) {
                $this->cached_terms[$taxonomy][intval( $term_id )] = $term->name;
            }
        }
    }

    protected function get_flattened_path( $term, $terms_tree )
    {
        $term_id = intval( $term->term_id );
        $taxonomy = $term->taxonomy;

        $path = array( $term->name );

        while ( $term->parent != 0 ) {
            $term = $terms_tree[intval( $term->parent )];
            $path[] = $term->name;
        }

        return implode(self::CATEGORY_SEPARATOR, array_reverse( $path ) );
    }

}