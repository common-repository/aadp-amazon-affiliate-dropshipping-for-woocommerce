<?php

/**
 * @package aadp
 * @version 0.0.8
 */

namespace Aadp\Base;

use Aadp\Api\SettingsApi;

class Activate
{
    public static function activate()
    {
        flush_rewrite_rules();


        if (!get_option('product_importer')) {
            update_option('product_importer', 'enable');
        }

        if (!get_option('amazon_hero_search')) {
            update_option('amazon_hero_search', 'enable');
        }

        if (!get_option('use_for')) {
            update_option('use_for', 'affiliate');
        }

        if (!get_option('default_store')) {
            update_option('default_store', 'https://www.amazon.com/');
        }
        if (!get_option('import_as')) {
            update_option('import_as', 'simple');
        }

        if (!get_option('cart_import_category')) {
            $cat = SettingsApi::store_categories();
            update_option('cart_import_category', $cat['default']);
        }

        if (!get_option('wp_amazon_country')) {
            update_option('wp_amazon_country',
                [
                    'Australia'            => 'https://www.amazon.com.au/',
                    'Brazil'               => 'https://www.amazon.com.br/',
                    'Canada'               => 'https://www.amazon.ca/',
                    'China'                => 'https://www.amazon.cn/',
                    'France'               => 'https://www.amazon.fr/',
                    'Germany'              => 'https://www.amazon.de/',
                    'India'                => 'https://www.amazon.in/',
                    'Italy'                => 'https://www.amazon.it/',
                    'Japan'                => 'https://www.amazon.co.jp/',
                    'Mexico'               => 'https://www.amazon.com.mx/',
                    'Netherland'           => 'https://www.amazon.nl/',
                    'Poland'               => 'https://www.amazon.pl/',
                    'Saudi Arabia'         => 'https://www.amazon.sa/',
                    'Singapore'            => 'https://www.amazon.sg/',
                    'Spain'                => 'https://www.amazon.es/',
                    'Sweden'               => 'https://www.amazon.se/',
                    'Turkey'               => 'https://www.amazon.com.tr/',
                    'United Arab Emirates' => 'https://www.amazon.ae/',
                    'United Kingdom'       => 'https://www.amazon.co.uk/',
                    'United States'        => 'https://www.amazon.com/',
                ]
            );
        }
        if (!get_option('amazon_associate_tag')) {
            update_option('amazon_associate_tag',
                [
                    [
                        'site' => 'https://www.amazon.com/',
                        'tag'  => '0205-21',
                    ],
                ]
            );
        }
        if (!get_option('buy_now_action')) {
            update_option('buy_now_action', 'onsite');
        }
        if (!get_option('enable_no_follow')) {
            update_option('enable_no_follow', 'yes');
        }
        

        // self::createTodaysDealCategory();
        self::createTag();

       
    }
    public static function createTag()
    {
        $tagId = wp_insert_term('amazon', 'product_tag');

    }
}
