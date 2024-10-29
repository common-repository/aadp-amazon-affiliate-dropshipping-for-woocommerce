<?php

/**
 * @package aadp
 * @version 0.0.8
 */

namespace Aadp;

final class Init
{
    // bhnnhjgjh
    /**
     * store all the classes inside array
     * return array full list of classes
     */
    public static function get_services()
    {
        # code...
// echo "hi";exit;
            return [
                Pages\Dashboard::class,
                Base\Enqueue::class,
                Base\ProductImporterController::class,
                Base\StoreController::class,
                Base\CustomRequestController::class,
            ];


    }

    //loop through the classes, initialize them, and call the register method() if it exists...
    public static function register_services()
    {

        foreach (self::get_services() as $class) {

            $service = self::instantiate($class);
            if (method_exists($service, 'register')) {

                $service->register();

            }
            //     // create nonce

        }
    }

    /**
     * initialize the class
     * $class from services array
     * return the class instance new instance of the class
     */

    private static function instantiate($class)
    {
        $service = new $class();
        return $service;

    }
}
