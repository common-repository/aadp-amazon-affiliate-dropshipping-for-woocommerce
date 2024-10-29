<?php

/**
 * @package aadp
 *  * @version 0.0.8
 */


namespace Aadp\Api\Callbacks;

use Aadp\Base\BaseController;

class AdminCallbacks extends BaseController
{
    public function adminPI()
    {
        return require_once("$this->plugin_path/templates/product_importer.php");
    }
    public function adminDocs()
    {
        return require_once("$this->plugin_path/templates/admin_docs.php");
    }
}
