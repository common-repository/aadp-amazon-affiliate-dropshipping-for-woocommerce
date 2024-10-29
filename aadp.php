<?php
/*
 * Plugin Name: AADP: Amazon Affiliate & Dropshipping for WooCommerce
 * Plugin URI: https://amazonplugins.com/amazon-affiliate-dropshipping-plugin-with-product-research-for-woocommerce/
 * Description: AADP - Amazon Affiliate & Dropshipping Plugin for WooCommerce helps you to give extra enhancing features for affiliators and sellers. AADP -  Amazon Affiliate WordPress Plugin is the best rated plugin by amazon affiliators . Just activate and find your businness as a top leading.
 * Version: 0.0.8
 * Author: Amazon Plugins
 * Author URI: https://amazonplugins.com/
 * Text Domain: aadp
 * WC requires at least: 4.0
 * WC tested up to: 5.7.2
 * Domain Path: /lang/
 * License: GPLv2 or later
 * @package WordPress
 */

defined('ABSPATH') or die('Hey, you can\t access this page, you silly human');

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
    // echo "hfgdfg";exit;
}

include_once ABSPATH . 'wp-admin/includes/plugin.php';
include_once ABSPATH . 'wp-includes/pluggable.php';

function aadp_woocommerce_is_active()
{
    return is_plugin_active('woocommerce/woocommerce.php');
}
// ghghghghghg
function aadp_decative_without_woo()
{
    if (!aadp_woocommerce_is_active()) {
        deactivate_plugins(plugin_basename(__FILE__));
        unset($_GET['activate']); // Input variable okay.
        //showing error message.
        add_action('admin_notices', 'aadp_admin_notice__error');
    }
}
add_action('admin_init', 'aadp_decative_without_woo');
function aadp_admin_notice__error()
{
    $class   = 'notice notice-error';
    $message = __('AADP Amazon Affiliate & Dropshipping Plugin for WooCommerce requires the WooCommerce plugin to be installed and active. You can download WooCommerce <a href=' . admin_url() . 'plugin-install.php?tab=plugin-information&plugin=woocommerce>here</a>', 'wp-amazon-shop');
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), __($message));
}
//this code run during activation
function activate_wp_amazon()
{
    Aadp\Base\Activate::activate();
}
register_activation_hook(__FILE__, 'activate_wp_amazon');

//this code run during deactivation
function deactivate_wp_amazon()
{
    Aadp\Base\Deactivate::deactivate();
}
register_activation_hook(__FILE__, 'deactivate_wp_amazon');

if (class_exists('Aadp\Init')) {
    Aadp\Init::register_services();

}
