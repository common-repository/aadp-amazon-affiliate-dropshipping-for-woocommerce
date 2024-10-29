<?php

/**
 * @package aadp
 * @version 0.0.8
 */

namespace Aadp\Base;
 class Deactivate{
     public static function deactivate(){
        add_action( 'shutdown', 'flush_rewrite_rules');
     }
 }
 