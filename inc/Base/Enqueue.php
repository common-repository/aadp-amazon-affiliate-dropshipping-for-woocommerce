<?php

/**
 * @package aadp
 */

namespace Aadp\Base;

use \Aadp\Base\BaseController;

class Enqueue extends BaseController
{
    public function register()
    {

        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend']);
    }

    public function enqueue()
    {
        //enqueue all our scripts here...
        wp_enqueue_script('media-upload');
        wp_enqueue_media();
        wp_enqueue_style('mypluginstyle', $this->plugin_url . 'assets/mystyle.css');
        wp_enqueue_style('bootstrap_style', $this->plugin_url . 'assets/bootstrap.min.css');
        wp_enqueue_script('bootstrap', $this->plugin_url . 'assets/bootstrap.bundle.min.js', ['jquery'], '1.0', true);
        wp_enqueue_script('loading_bar', $this->plugin_url . 'assets/loading-bar.min.js', ['jquery'], '1.0', true);
        wp_enqueue_style('loading_bar', $this->plugin_url . 'assets/loading-bar.min.css');

        // define script url
        $script_url = ($this->plugin_url . 'assets/ajax-admin.js');
        // enqueue script
        wp_enqueue_script('ajax-admin', $script_url, ['jquery']);
        // create nonce
        $nonce = wp_create_nonce('ajax_admin');

        // define script
        $script = [
            'nonce'    => $nonce,
            'ajax_url' => admin_url('admin-ajax.php'),
        ];

        // localize script
        wp_localize_script('ajax-admin', 'ajax_admin', $script);

        wp_enqueue_script('product-importer-script', $this->plugin_url . 'assets/product-importer.js', ['jquery'], '1.0', true);

        $product_importer_nonce = wp_create_nonce('product_importer_script');

        $product_importer_script = [
            'nonce'               => $product_importer_nonce,
            'ajax_url'            => admin_url('admin-ajax.php'),
            'product_import_type' => get_option('import_as'),
        ];
        // localize script
        wp_localize_script('product-importer-script', 'product_importer_script', $product_importer_script);
    }
    public function enqueue_frontend()
    {
        // wp_enqueue_script('pluigns-js', get_theme_file_uri('/assets/js/plugins.js'), ['jquery'], '1.0', true);
        //enqueue frontend
        wp_enqueue_style('font-awesome', $this->plugin_url . 'assets/font-awesome.min.css');
        wp_enqueue_style('myplugin-frontend-style', $this->plugin_url . 'assets/myplugin-frontend-style.css');
        wp_enqueue_style('bootstrap_style', $this->plugin_url . 'assets/bootstrap.min.css');
        wp_enqueue_script('bootstrap', $this->plugin_url . 'assets/bootstrap.bundle.min.js', ['jquery'], '1.0', true);
        //wp_enqueue_style('xzoom-css', $this->plugin_url . 'assets/xzoom.css');
        //wp_enqueue_script('xzoom-js', $this->plugin_url . 'assets/xzoom.min.js', ['jquery'], '1.0', true);
        // enqueue script
        wp_enqueue_script('myplugin-frontend-script', $this->plugin_url . 'assets/myplugin-frontend-script.js', ['jquery'], '1.0', true);
        // create nonce
        $frontend_nonce = wp_create_nonce('myplugin_frontend_script');
        // define script
        $frontend_script = [
            'nonce'         => $frontend_nonce,
            'ajax_url'      => admin_url('admin-ajax.php'),
            'use_for'       => get_option('use_for'),
            'button_action' => get_option('buy_now_action'),
            'store_country' => get_option('wp_amazon_country'),
            // 'page_number'      => get_option('product_page_number'),
            // 'product_per_page' => get_option('product_per_page'),
        ];
        // localize script
        wp_localize_script('myplugin-frontend-script', 'myplugin_frontend_script', $frontend_script);
    }
}
