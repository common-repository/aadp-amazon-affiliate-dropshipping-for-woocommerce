<?php

/**
 * @package aadp
 * @version 0.0.8
 */

namespace Aadp\Base;

class BaseController
{
    public $plugin_path;
    public $plugin_url;
    public $plugin;
    public $managers = array();
    public $general = array();
    public function __construct()
    {

        $this->plugin_path = plugin_dir_path(dirname(__FILE__, 2));
        $this->plugin_url  = plugin_dir_url(dirname(__FILE__, 2));
        $this->plugin      = plugin_basename(dirname(__FILE__, 3)) . '/aadp.php';

    }


    public function activated(string $option)
    {
        $optionValue = get_option($option);
        if($optionValue == "enable"){
            return true;
        }else{
            return false;
        }
    }
}
