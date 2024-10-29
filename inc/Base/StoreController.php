<?php

/**
 * @package aadp
 */

namespace Aadp\Base;


class StoreController extends BaseController
{
    public function register()
    {
        add_action('wp_ajax_aadp_imported_product_redirect_to_amzon', [$this, 'aadp_imported_product_redirect_to_amzon']);
        add_action('wp_ajax_no_priv_aadp_imported_product_redirect_to_amzon', [$this, 'aadp_imported_product_redirect_to_amzon']);
        //Place order to amazon directly
        add_action('wp_ajax_aadp_cart_to_amazon_cart_url', [$this, 'aadp_cart_to_amazon_cart_url']);
        add_action('wp_ajax_nopriv_aadp_cart_to_amazon_cart_url', [$this, 'aadp_cart_to_amazon_cart_url']);


    }
 
    public static function aadp_search_tag($affiliate_tags, $product_store)
    {
        foreach ($affiliate_tags as $key => $value) {
            if ($value['site'] == $product_store) {

                $tag = $value['tag'];
                return $tag;
            }
        }
        return null;
    }
    public static function aadp_imported_product_redirect_to_amzon()
    {
        $url = "";

        if (isset($_POST['product_id']) && $_POST['product_id'] != "") {
            $product_id    = wc_clean($_POST['product_id']);
            $product       = wc_get_product($product_id);
            $product_asin  = $product ? $product->get_meta('aadp_asin') : '';
            $product_store = $product ? $product->get_meta('aadp_store') : '';
            $affiliate_tags = get_option('amazon_associate_tag');
            $tag = self::aadp_search_tag($affiliate_tags, $product_store);
            $product_type = wc_clean($_POST['product_type']);
            if ($product_asin != "" && $tag != null) {
                $button_action = get_option('buy_now_action');
                if ($product_type == 'variable') {
                    $url = $product_store . 'dp/' . $product_asin . '?tag=' . $tag;
                } else if ($button_action == 'redirect') {
                    $url = $product_store . 'gp/aws/cart/add.html?AssociateTag=' . $tag . '&ASIN.1=' . $product_asin . '&Quantity.1=1';
                } else if ($button_action == 'details') {
                    $url = $product_store . 'dp/' . $product_asin . '?tag=' . $tag;
                }
            }
            wp_send_json($url);
            wp_die();
        }
    }

    public function aadp_cart_to_amazon_cart_url()
    {
        global $woocommerce;
        $items = $woocommerce->cart->get_cart();
        if (count($items) >= 1) {
            $counter   = 1;
            $url       = [];
            foreach ($items as $item => $values) {
                $_product = apply_filters('woocommerce_cart_item_product', $values['data'], $values, $item);          
                $woocommerce->cart->remove_cart_item($item);
                $p_asin  = $_product->get_meta('aadp_asin');
                $p_store = $_product->get_meta('aadp_store');
                $host = [];
                foreach ($url as $key => $value) {
                    $p_url  = parse_url($value);
                    $host[] = 'https://' . $p_url["host"] . '/';
                }
                if (in_array($p_store, $host)) {
                    $index = array_search($p_store, $host);
                    $url[$index] .= '&ASIN.' . $counter . '=' . $p_asin . '&Quantity.' . $counter . '=' . $values['quantity'];
                } else {
                    $affiliate_tags = get_option('amazon_associate_tag');
                    $tag            = self::aadp_search_tag($affiliate_tags, $p_store);
                    $url[]          = $p_store . 'gp/aws/cart/add.html?AssociateTag=' . $tag . '&ASIN.' . $counter . '=' . $p_asin . '&Quantity.' . $counter . '=' . $values['quantity'];
                }
                $host = null;
                $counter++;
            }
            wp_send_json($url);
            wp_die();
        }

    }
   

}

