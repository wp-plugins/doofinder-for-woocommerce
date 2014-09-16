<?php
/**
 * Feed class for Doofinder for WooCommerce
 *
 * @package WordPress
 * @subpackage WooCommerce_Doofinder
 * @author Doofinder
 * @category Admin
 * @version 0.1.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_Doofinder_Feed' ) ) :

class WC_Doofinder_Feed {

    const XML_NAMESPACE = 'http://base.doofinder.com/ns/1.0';
    const XML_IMAGE_TYPE = 'shop_single'; // TODO: make configurable

    const GRP_SEPARATOR = '%%';
    const CAT_SEPARATOR = ' &gt; ';

    private $include_taxes;
    private $display_prices;
    private $terms_cache;
    private $paths_cache;

    public function __construct()
    {
        $this->include_taxes = ( get_option('woocommerce_tax_display_shop') == 'inc' );
        $this->display_prices = true; // TODO: make configurable

        $this->terms_cache = array();
        $this->paths_cache = array();

        foreach( get_terms('product_cat') as $term )
        {
            $this->terms_cache[$term->term_id] = $term;
        }
    }

    public function render()
    {
        $max_items = -1;
        $language = strtoupper( substr( get_locale(), 0, 2 ) );
        $currency = get_woocommerce_currency();

        $rss = new WC_Doofinder_SimpleXML('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"></rss>');

        $channel = $rss->addChild( 'channel' );
        $channel->addChild( 'title', get_bloginfo( 'name' ) );
        $channel->addChild( 'link', get_home_url() );
        $channel->addChild( 'description' )->addCData( sanitize_text_field( get_bloginfo( 'description' ) ) );

        $products = new WP_Query(array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'ignore_sticky_posts' => 1,

            'meta_query' => array(
                array(
                    'key' => '_visibility',
                    'value' => array('search', 'visible'),
                    'compare' => 'IN',
                ),
            ),

            'posts_per_page' => $max_items,

            'orderby' => 'ID',
            'order' => 'ASC',

            'cache_results' => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ));

        // TODO: paging with "posts_per_page" and "offset"
        // TODO: brand, gtin, condition, other attributes

        while( $products->have_posts() ) {
            $products->the_post();
            $product = get_product( get_the_ID() );

            $item = $channel->addChild( 'item' );
            $item->addChild( 'id', get_the_ID());
            $item->addChild( 'title' )->addCData( sanitize_text_field( get_the_title() ) );
            $item->addChild( 'description' )->addCData( sanitize_text_field( get_the_content() ) );
            $item->addChild( 'link', get_permalink() );
            $item->addChild( 'product_type', '')->addCData( sanitize_text_field( $this->getCategories( get_the_ID() ) ) );

            $availability = $product->is_purchasable() && $product->is_in_stock() ? 'in stock' : 'out of stock';
            $item->addChild( 'availability', $availability );

            if ( $mpn = $product->get_sku() )
            {
                $item->addChild( 'mpn', $mpn );
            }

            if ( $image_url = $this->getImage( get_post_thumbnail_id() ) ) {
                $item->addChild( 'image_link', $image_url);
            }

            if ( $this->display_prices ) {
                list($regular_price, $sale_price) = $this->getPrices( $product );

                if ( $regular_price ) {
                    $item->addChild( 'price', $regular_price);
                }

                if ( $sale_price ) {
                    $field_name = $regular_price ? 'sale_price' : 'price';
                    $item->addChild( $field_name, $sale_price);
                }
            }
        }

        wp_reset_postdata();

        $xml = dom_import_simplexml( $rss )->ownerDocument;
        $xml->formatOutput = true;
        return $xml->saveXML();
    }

    private function getCategories($id)
    {
        $paths = array();
        foreach( get_the_terms( $id, 'product_cat' ) as $term )
        {
            $paths[] = $this->getCategoryPath( $term );
        }
        $this->cleanPaths( $paths );

        return implode( self::GRP_SEPARATOR, $paths );
    }

    private function cleanPaths(&$paths)
    {
        sort($paths);
        for ($x = 0, $i = 1, $j = count($paths); $i < $j; $x = $i++)
            if ( strpos( $paths[$i], $paths[$x] ) === 0 )
                unset($paths[$x]);
    }

    private function getCategoryPath($term)
    {
        if (isset($this->paths_cache[$term->term_id])) {
            return $this->paths_cache[$term->term_id];
        }

        $term_id = $term->term_id;

        $path = array();
        $path[] = $term->name;

        while ($term->parent)
        {
            $term = $this->terms_cache[$term->parent];
            $path[] = $term->name;
        }

        $path = implode( self::CAT_SEPARATOR, array_reverse($path) );
        $this->paths_cache[$term_id] = $path;

        return $path;
    }

    private function getImage($image_id)
    {
        if ( $image_id ) {
            if ( $image_url = wp_get_attachment_image_src( $image_id, self::XML_IMAGE_TYPE ) ) {
                return $image_url[0];
            }
        }
        return false;
    }

    private function getPrices($product)
    {
        $pricing = array();
        $regular_price = false;
        $sale_price = false;

        if ( $product->is_type('variable') ) {
            // class-wc-product-variable.php
            $regular_price = $product->get_variation_regular_price( 'min' );
            if ( $product->is_on_sale() ) {
                $sale_price = $product->get_variation_sale_price( 'min' );
            }
        } else {
            // Mirar: abstract-wc-product.php
            $regular_price = $product->get_regular_price();
            if ( $product->is_on_sale() ) {
                $sale_price = $product->get_sale_price();
            }
        }

        if ( $this->include_taxes ) {
            $pricing[] = $product->get_price_including_tax( 1, $regular_price );
            $pricing[] = $sale_price ? $product->get_price_including_tax( 1, $sale_price ) : false;
        } else {
            $pricing[] = $product->get_price_excluding_tax( 1, $regular_price );
            $pricing[] = $sale_price ? $product->get_price_excluding_tax( 1, $sale_price ) : false;
        }

        return $pricing;
    }
}

endif;
