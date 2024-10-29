<?php

namespace Aadp\Base;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class ScrapingController
{
    public static $i = 0;
    public static function createClient()
    {
        $client = new Client(HttpClient::create([
            'headers' => [
                'user-agent'                => $_SERVER['HTTP_USER_AGENT'], // will be forced using 'Symfony BrowserKit' in executing
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language'           => 'en-US,en;q=0.5',
                'Referer'                   => 'https://wwww.amazon.com/',
                'Upgrade-Insecure-Requests' => '1',
                'Save-Data'                 => 'on',
                'Pragma'                    => 'no-cache',
                'Cache-Control'             => 'no-cache',
            ],
        ]));

        $client->setServerParameter('HTTP_USER_AGENT', $_SERVER['HTTP_USER_AGENT']);
        return $client;
    }
    public static function scrap_department($store_url)
    {
        $client  = self::createClient();
        $crawler = $client->request('GET', $store_url);

        $data = $crawler->filter('#searchDropdownBox > option')->each(function ($node) {
            $item = [
                substr($node->attr('value'), 13) => $node->text(),
            ];
            return $item;
        });
        $result = array_reduce($data, 'array_merge', []);
        return $result;
    }
    public static function scrap_find_product($url)
    {
        $client = self::createClient();

        $client->request('GET', $url);
        $html    = $client->getResponse()->getContent();
        $crawler = new Crawler($html);

        $data = [];
        // $book   = '';
        $data[] = $crawler->filter('div.s-result-item.s-asin')->each(function ($node) {
            // echo $node->html();
            $bookData = [];
            $isBook   = $node->filter('div.s-result-item.s-asin > div > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div.sg-row > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div > div.a-section.a-spacing-none.a-spacing-top-small > div:nth-child(1) > a')->count('');

            $isBook2 = $node->filter('div.s-result-item.s-asin > div > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div > div.a-section.a-spacing-none.a-spacing-top-small > div:nth-child(1) > a')->count();

            // echo "1". $node->filter('div.s-result-item.s-asin > div > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div > div.a-section.a-spacing-none.a-spacing-top-small > div:nth-child(1) > a')->html('');
            // echo "2". $node->filter('div.s-result-item.s-asin > div > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div.sg-row > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div > div.a-section.a-spacing-none.a-spacing-top-small > div:nth-child(1) > a')->html('');
            //#search > div.s-desktop-width-max.s-opposite-dir > div > div.s-matching-dir.sg-col-16-of-20.sg-col.sg-col-8-of-12.sg-col-12-of-16 > div > span:nth-child(4) > div.s-main-slot.s-result-list.s-search-results.sg-row > div:nth-child(1) > div > span > div > div > div:nth-child(2) > div.sg-col.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20 > div > div.sg-row > div.sg-col.sg-col-4-of-12.sg-col-4-of-16.sg-col-4-of-20 > div > div.a-section.a-spacing-none.a-spacing-top-small > div:nth-child(1) > a

            //#search > div.s-desktop-width-max.s-opposite-dir > div > div.s-matching-dir.sg-col-16-of-20.sg-col.sg-col-8-of-12.sg-col-12-of-16 > div > span:nth-child(4) > div.s-main-slot.s-result-list.s-search-results.sg-row > div.s-result-item.s-asin > div > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div > div.a-section.a-spacing-none.a-spacing-top-small > div:nth-child(1) > a

            // $isTrue = $node->filter('div.sg-col-inner > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div > div:nth-child(4) > div:nth-child(1) > div:nth-child(1) > div:nth-child(2) > a')->text('');
            // var_dump($isTrue);exit;
            if ($isBook == true || $isBook2 == true) {
                // $bookData = [
                //     'type' => $node->filter(' div.sg-col-inner > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div> div >div:nth-child(1)>a')->text(''),
                //     'selling_price' =>
                // ];
                //#search > div.s-desktop-width-max.s-desktop-content.sg-row > div.sg-col-16-of-20.sg-col.sg-col-8-of-12.sg-col-12-of-16 > div > span:nth-child(4) > div.s-main-slot.s-result-list.s-search-results.sg-row > div:nth-child(1) > div.sg-col-inner > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div > div.a-section.a-spacing-none.a-spacing-top-small
                $bookData[] = $node->filter(' div.sg-col-inner > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.div.sg-row > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div> div.a-section.a-spacing-none.a-spacing-top-small')->each(function ($subnode) {
                    $bookItemData = [
                        'type'          => $subnode->filter('a')->text(''),
                        "selling_price" => $subnode->filter('span.a-price > span.a-offscreen')->text(''),
                        'regular_price' => $subnode->filter('span.a-price.a-text-price > span.a-offscreen')->text(''),
                        // 'note' => $subnode->filter('.a-color-secondary')->text(''),
                    ]; //
                    // var_dump($bookItemData);exit;
                    return $bookItemData;
                });
                //#search > div.s-desktop-width-max.s-desktop-content.sg-row > div.sg-col-16-of-20.sg-col.sg-col-8-of-12.sg-col-12-of-16 > div > span:nth-child(4) > div.s-main-slot.s-result-list.s-search-results.sg-row > div:nth-child(1) > div.sg-col-inner > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div > div:nth-child(4) > div:nth-child(1) > div:nth-child(1) > div:nth-child(2) > a
                $isTrue = $node->filter('div.sg-col-inner > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div.sg-row > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div > div.a-section.a-spacing-none.a-spacing-top-mini > div:nth-child(1) > div:nth-child(1) > div:nth-child(2) > a')->count();
                // var_dump($isTrue);
                // exit;
                if ($isTrue == true) {
                    $bookData[] = $node->filter(' div.sg-col-inner > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div.sg-row > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div> div.a-section.a-spacing-none.a-spacing-top-mini > div:nth-child(1) > div:nth-child(1)')->each(function ($subnode) {
                        // var_dump($subnode);exit;
                        $bookItemData = [
                            'type'          => $subnode->filter('a')->text(''),
                            "selling_price" => $subnode->filter('span.a-price > span.a-offscreen')->text(''),
                            'regular_price' => $subnode->filter('span.a-price.a-text-price > span.a-offscreen')->text(''),
                            // 'note' => $subnode->filter('.a-color-secondary')->text(''),
                        ]; //
                        // var_dump($bookItemData);
                        // exit;
                        return $bookItemData;
                    });
                    $isTrueAgain = $node->filter('div.sg-col-inner > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div.sg-row > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div > div.a-section.a-spacing-none.a-spacing-top-mini > div:nth-child(1) > div:nth-child(2) > div:nth-child(2) > a')->count();

                    if ($isTrueAgain == true) {
                        $bookData[] = $node->filter(' div.sg-col-inner > span > div > div > div:nth-child(2) > div.sg-col-4-of-12.sg-col-8-of-16.sg-col-12-of-20.sg-col > div > div.sg-row > div.sg-col-4-of-12.sg-col-4-of-16.sg-col.sg-col-4-of-20 > div> div.a-section.a-spacing-none.a-spacing-top-mini > div:nth-child(1) > div:nth-child(2)')->each(function ($subnode) {
                            $bookItemData = [
                                'type'          => $subnode->filter('a')->text(''),
                                "selling_price" => $subnode->filter('span.a-price > span.a-offscreen')->text(''),
                                'regular_price' => $subnode->filter('span.a-price.a-text-price > span.a-offscreen')->text(''),
                                // 'note' => $subnode->filter('.a-color-secondary')->text(''),
                            ]; //
                            return $bookItemData;
                        });
                    }
                }
            }
            $book = array_reduce($bookData, 'array_merge', []);
            // var_dump($ggg);exit;
            $item = [
                // "asin" => $node->filter('div.s-asin')->attr('data-asin'),
                "tittle"        => $node->filter('h2')->text(''),
                "image"         => $node->filter('img')->attr('src'),
                "ratings"       => chop($node->filter('div.a-section.a-spacing-none.a-spacing-top-micro > div.a-row.a-size-small > span')->text(''), 'out of 5 stars'),
                "reviews"       => $node->filter('div.a-section.a-spacing-none.a-spacing-top-micro > div.a-row.a-size-small > span  > a > span')->text(0),
                "selling_price" => $node->filter('span.a-price > span.a-offscreen')->text(''),
                'regular_price' => $node->filter('span.a-price.a-text-price > span.a-offscreen')->text(''),
                'discount'      => $node->filter('span.s-coupon-unclipped')->text(''),
                'badge'         => $node->filter('span.a-badge-label-inner.a-text-ellipsis')->text(''),
                'sponsored'     => $node->filter('div > span > span > span.s-label-popover-hover > span')->text(''),
                'prime'         => $node->filter('span.aok-relative.s-icon-text-medium.s-prime')->text(''),
                'product_url'   => $node->filter('h2 > a')->attr('href'),
                'book_data'     => $book,
            ];
            //#search > div.s-desktop-width-max.s-desktop-content.sg-row > div.sg-col-16-of-20.sg-col.sg-col-8-of-12.sg-col-12-of-16 > div > span:nth-child(4) > div.s-main-slot.s-result-list.s-search-results.sg-row > div:nth-child(4) > div > span > div > div > div > div > div:nth-child(6) > div.a-row.a-size-base.a-color-secondary.s-align-children-center > div.a-row.s-align-children-center > span > span.aok-relative.s-icon-text-medium.s-prime > i
            // $product ='';
            // $product .= '<button><a href="">'.$node->filter('h2 > a')->attr('href').'</a></button>';
            // $book
            // $bookData = [

            // ];

            return $item;
        });
        $result['data']       = array_reduce($data, 'array_merge', []);
        $result['total_page'] = $crawler->filter('.a-pagination > li.a-disabled')->last()->text("");
        // var_dump($result);exit;
        return $result;
    }

    public static function starRating($star)
    {
        $parseStar = (int) $star;
        $fraction  = (float) $star - $parseStar;
        $starR     = '<div class="stars">';
        if (.25 < $fraction && $fraction < .76) {
            $gold = $parseStar;
            $grey = 5 - $parseStar - 1;
            for ($i = 0; $i < $gold; $i++) {
                $starR .= '<span class="star on"></span>';
            }
            $starR .= '<span class="star half"></span>';

            for ($i = 0; $i < $grey; $i++) {
                $starR .= '<span class="star"></span>';
            }
        } else if ($fraction < .25) {
            $gold = $parseStar;
            $grey = 5 - $gold;
            for ($i = 0; $i < $gold; $i++) {
                $starR .= '<span class="star on" ></span>';
            }
            for ($i = 0; $i < $grey; $i++) {
                $starR .= '<span class="star"></span>';
            }
        } else if ($fraction > .75) {
            $gold = 1 + $parseStar;
            $grey = 5 - $gold;
            for ($i = 0; $i < $gold; $i++) {
                $starR .= '<span class="star on" ></span>';
            }
            for ($i = 0; $i < $grey; $i++) {
                $starR .= '<span class="star"></span>';
            }
        }
        $starR .= '</div>';
        return $starR;
    }
    public static function scrap_product($url)
    {
        //very basic usage
        $client  = self::createClient();
        $crawler = $client->request('GET', $url);

        $productData = $crawler->filter('body')->each(function ($node) use (&$url) {
            $availability = $node->filter('#outOfStock')->count();
            if ($availability == false) {
                $productPrice = 'Price: <span>' . esc_html(self::get_product_price($node), 'aadp') . '</span>';
            } else {

                $productPrice = 'Currently unavailable';

            }
            $variation = self::check_variation($node);

            if (empty($variation)) {
                $productAttr      = null;
                $productVariation = null;
            } else {
                $productAttr      = self::get_attribute($variation[0]);
                $productVariation = self::asinToDimensionIndexMap($variation[0]);
            }

            $data = [
                "asin"      => self::get_asin_by_url($url),
                // 'asin'      => $node->filter('#ASIN')->attr('value'),
                'title'     => $node->filter('#productTitle')->text(''),
                // 'feature' => $node->filter('#featurebullets_feature_div')->html(''),
                'rating'    => self::scrap_rating($node),
                'images'    => self::get_product_images($node),
                'price'     => $productPrice,
                'attribute' => $productAttr,
                'variation' => $productVariation,
                'feature'   => self::scrap_feature($node),
            ];

            return $data;
        });

        $reduceArray = array_reduce($productData, 'array_merge', []);
        return $reduceArray;
    }
    public static function scrap_import_product($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);
        $html = $client->getResponse()->getContent();

        $crawler          = new Crawler($html);
        $swatchElement    = $crawler->filter('.swatchElement')->count();
        $mediaTab_heading = $crawler->filter('.mediaTab_heading')->count();

        if ($swatchElement == true || $mediaTab_heading == true) {
             $data['book'] = true;
             return $data;
        }
        $dealProduct = $crawler->filter('body')->each(function ($node) use (&$url) {
            $availability = $node->filter('#outOfStock')->count();
            if ($availability == false) {
                $productImages = self::get_product_images($node);
                // $productAsin = $node->filter('#cerberus-data-metrics')->attr('data-asin');
                // $productAsin = $node->filter('#ASIN')->attr('value');
                $productAsin = self::get_asin_by_url($url);
                //wp_send_json($productAsin );exit;
                if ($productAsin == "") {
                    $productAsin = $node->filter('#ASIN')->attr('value');
                }

                $productName      = $node->filter('#productTitle')->text('');
                $productStoreLink = $node->filter('#bylineInfo')->attr('href');
                $productStore     = $node->filter('#bylineInfo')->text('');
                $productOverview  = self::aadp_clean_html($node->filter('#productOverview_feature_div')->html(''));
                $productSoldBy    = $node->filter('#tabular-buybox-truncate-1')->text('');
                $detailBullet     = $node->filter('#detailBullets_feature_div')->count();
                $proDetail        = $node->filter('#prodDetails')->count();
                $techDetail       = $node->filter('#tech')->count();
                if ($detailBullet == true) {
                    $productDescription = self::aadp_clean_html($node->filter('#detailBullets_feature_div')->html(''));
                } elseif ($proDetail == true) {
                    $productDescription = self::aadp_clean_html($node->filter('#prodDetails')->html(''));
                } elseif ($techDetail) {
                    $productDescription = self::aadp_clean_html($node->filter('#tech')->html(''));
                }
                $productFeature = self::aadp_clean_html($node->filter('#featurebullets_feature_div')->html(''));

                $productRating      = $node->filter('#acrPopover')->count() ? chop($node->filter('#acrPopover')->attr('title'), 'out of 5 stars') : 0;
                $productRatingCount = chop($node->filter('#acrCustomerReviewText')->text(''), ' ratings');

                $noDeal = $node->filter('#priceblock_ourprice')->count();
                if ($noDeal == true) {
                    $productRegularPrice = preg_replace('/[^A-Za-z0-9\-.]/', '', $node->filter('#priceblock_ourprice')->text(''));
                    if ($node->filter('#listPriceLegalMessage')->count() == true) {
                        $productDealPrice = preg_replace('/[^A-Za-z0-9\-.]/', '', $node->filter('#listPriceLegalMessage')->previousAll()->text(''));
                    } else {
                        $productDealPrice = null;
                    }
                    $productDealSavings = null;

                } else {
                    $productRegularPrice = preg_replace('/[^A-Za-z0-9\-.]/', '', $node->filter('.priceBlockStrikePriceString')->text(''));
                    $productDealPrice    = preg_replace('/[^A-Za-z0-9\-.]/', '', $node->filter('#priceblock_dealprice')->text(''));
                    $productDealSavings  = $node->filter('#dealprice_savings')->text('');

                }

                $product = [
                    'ASIN'            => $productAsin,
                    'product_name'    => $productName,
                    'product_store'   => $productStore,
                    'store_link'      => $productStoreLink,
                    'regular_price'   => $productRegularPrice,
                    'deal_price'      => $productDealPrice,
                    'deal_savings'    => $productDealSavings,
                    'average_ratings' => $productRating,
                    'rating_count'    => $productRatingCount,
                    'overview'        => $productOverview,
                    'sold_by'         => $productSoldBy,
                    'feature'         => $productFeature,
                    'description'     => $productDescription,
                    'images'          => $productImages,
                ];
            } else {
                $product = [];
            }
            return $product;
        });
        $dProduct = array_reduce($dealProduct, 'array_merge', []);
        return $dProduct;
    }

    public static function aadp_clean_html($html)
    {

        $excludeScriptTags = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $excludeStyleTags  = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $excludeScriptTags);
        $a                 = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si", '<$1$2>', $excludeStyleTags);
        $b                 = preg_replace('/<([^<\/>]*)([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU', '', $a);
        $result            = preg_replace('/\s+/', ' ', $b);
        return $result;
    }
    public static function scrap_feature($node)
    {
        $feature = $node->filter('#feature-bullets > ul > li')->each(function ($subNode) {
            return $subNode->text();
        });
        return $feature;
    }
    public static function scrap_rating($node)
    {
        $countRating = $node->filter('#acrPopover')->count();
        if ($countRating == true) {
            $rating = chop($node->filter('#acrPopover')->attr('title'), 'out of 5 stars');
        } else {
            $rating = false;
        }
        return $rating;
    }
    public static function get_product_images($node)
    {

        $script = $node->filter('script')->each(function ($subNode) {
            if (strpos($subNode->html(), 'ImageBlockATF')) {
                return $subNode->html();
            }
        });
        $imageScript = array_values(array_filter($script));
        $stepOne     = explode("'colorImages': { 'initial': ", $imageScript[0]);
        $stepTwo     = explode("'colorToAsin", $stepOne[1]);
        $finalStep   = str_replace("}]},", "}]", $stepTwo[0]);
        $images      = json_decode($finalStep, true);
        return $images;
    }
    public static function get_product_price($node)
    {
        $deal       = $node->filter('#priceblock_dealprice_row')->count();
        $priceBlock = $node->filter('#priceblock_ourprice')->count();
        if ($deal == true) {
            $price['list']      = $node->filter('.priceBlockStrikePriceString')->text();
            $price['with_deal'] = $node->filter('#priceblock_dealprice')->text();
            $price['save']      = $node->filter('.priceBlockSavingsString')->text();
        } elseif ($priceBlock == true) {

            $salePrice = $node->filter('#priceblock_saleprice')->count();
            if ($salePrice == true) {
                $price = $node->filter('#priceblock_saleprice')->text();
            } else {
                $price = $node->filter('#priceblock_ourprice')->text();
            }
        } else {

            $price = $node->filter('#olp-upd-new > span > a > span.a-size-base.a-color-price')->text();
        }
        return $price;
    }
    public static function check_variation($node)
    {
        $script = $node->filter('script')->each(function ($subNode) {
            if (strpos($subNode->html(), 'variationValues')) {
                return $subNode->html();
            }
        });
        $variationScript = array_values(array_filter($script));
        return $variationScript;
    }
    //get variations
    public static function get_variation($variation_data)
    {
        $var_v_parts      = explode('"asinVariationValues" :', $variation_data);
        $var_v_1          = explode('}},', $var_v_parts[1]);
        $productVariation = json_decode($var_v_1[0] . '}}', true);
        return $productVariation;
    }
    //get attributes
    public static function get_attribute($variation_data)
    {
        $var_v_parts = explode('"variationValues" :', $variation_data);
        $var_v_1     = explode('}', $var_v_parts[1]);
        $productAttr = json_decode($var_v_1[0] . '}', true);
        // $productAttr = json_decode(str_replace('|','&',$var_v_1[0]) . '}', true);

        return $productAttr;
    }
    //asinToDimensionIndexMap
    public static function asinToDimensionIndexMap($variation_data)
    {
        $var_v_parts      = explode('"asinToDimensionIndexMap" :', $variation_data);
        $var_v_1          = explode('},', $var_v_parts[1]);
        $productVariation = json_decode($var_v_1[0] . '}', true);
        // $productVariation = json_decode(str_replace('|','&',$var_v_1[0]) . '}', true);
        return $productVariation;
    }
    //
    public static function selected_variations($variation_data)
    {
        $var_v_parts       = explode('"selected_variations" :', $variation_data);
        $var_v_1           = explode('},', $var_v_parts[1]);
        $selectedVariation = json_decode($var_v_1[0] . '}', true);
        // $selectedVariation = json_decode(str_replace('|','&',$var_v_1[0]) . '}', true);
        return $selectedVariation;
    }
    // get ASIN by url
    public static function get_asin_by_url($url)
    {
        $url_parts = explode('?', $url);
        if (isset($url_parts[0]) && $url_parts[0] != "") {
            $product_url = $url_parts[0];
        } else {
            $product_url = $url;
        }
        $result  = "";
        $pattern = "([A-Z0-9]{10})(?:[/?]|$)";
        $pattern = escapeshellarg($pattern);

        preg_match($pattern, $product_url, $matches);

        if ($matches && isset($matches[1])) {
            $result = $matches[1];
        }

        return $result;
    }

    public static function scrap_books($url)
    {
        $client  = self::createClient();
        $crawler = $client->request('GET', $url);
        // $bookImages = $crawler->filter('body')->each(function ($node) {
        //     return self::get_book_images($node);
        // });
        // $formattedBookImages  = array_reduce($bookImages, 'array_merge', array());
        // $formattedBookImagesAgain  = array_reduce($formattedBookImages, 'array_merge', array());

        $html              = $client->getResponse()->getContent();
        $excludeScriptTags = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $excludeStyleTags  = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $excludeScriptTags);
        // $excludeStyleTags = preg_replace('#<style(.*?)>(.*?)</style>#is', '',$html);
        $newCrawler = new Crawler($excludeStyleTags);

        $book = $newCrawler->filter('body')->each(function ($node) use (&$url) {
            //#acrPopover > span.a-declarative > a > i.a-icon.a-icon-star.a-star-5 > span
            ////*[@id="acrPopover"]/span[1]/a/i[1]/span
            //#a-autoid-13-announce > span:nth-child(1)
            $swatchElement = $node->filter('.swatchElement')->count();

            $mediaTab_heading = $node->filter('.mediaTab_heading')->count();
            if ($swatchElement == true) {
                $bookType = $node->filter('.swatchElement')->each(function ($subnode) use (&$url) {
                    $word = str_word_count($subnode->filter('.a-button-text')->text(), 1);
                    if ($subnode->filter(".selected ")->count() == false) {

                        $aa = [
                            // 'type-name' => preg_replace('/\W|\d/', '', $subnode->filter('.a-button-text')->text()),
                            'type-name' => $word[0],
                            'type-url'  => 'https://www.amazon.com/' . $subnode->filter('.a-button-text')->attr('href'), // add store variable when
                        ];
                    } else {
                        $aa = [
                            'type-name' => $word[0],
                            'type-url'  => $url,
                        ];
                    }

                    return $aa;
                });
            } else if ($mediaTab_heading == true) {
                $bookType = $node->filter('#mediaTabs_tabSet>li')->each(function ($subnode) use (&$url) {
                    // $subnode->parentNode->removeChild($subnode->last());
                    // $subnode->reduce(function (Crawler $node, $i) {
                    //     // filters every other node
                    //     return ($i % 2) == 0;
                    // });

                    $aa = [
                        'type-name' => $subnode->filter('.mediaTab_title')->text(),
                        'type-url'  => 'https://www.amazon.com' . $subnode->filter('a')->attr('href'),
                    ];
                    return $aa;
                });
                array_pop($bookType);
            }
            $author = $node->filter('.author')->each(function ($subnode) {
                return $subnode->filter('.a-size-medium')->text('');
            });
            $item = [
                'title'           => trim($node->filter('#productTitle')->text('')),
                'author'          => $author,
                'feature_desc'    => $node->filter('#bookDescription_feature_div>noscript')->text(''),
                'book-type'       => $bookType,
                // 'images' => self::get_book_images($node),
                'average_ratings' => chop($node->filter('#acrPopover')->attr('title'), 'out of 5 stars'),
                'rating_count'    => chop($node->filter('#acrCustomerReviewText')->text(''), ' ratings'),
            ];
            return $item;
        });
        $bookData = array_reduce($book, 'array_merge', []);
        // $bookData['images'] = $formattedBookImages;

        return $bookData;
    }
    public static function scrap_book_details($url)
    {
        $client  = self::createClient();
        $crawler = $client->request('GET', $url);

        $html              = $client->getResponse()->getContent();
        $excludeScriptTags = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        $excludeStyleTags  = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $excludeScriptTags);
        // $excludeStyleTags = preg_replace('#<style(.*?)>(.*?)</style>#is', '',$html);
        $newCrawler = new Crawler($excludeStyleTags);
        $book       = $newCrawler->filter('body')->each(function ($node) use (&$crawler) {
            // $dimension = $node->filter('#searchDropdownBox > option[selected="selected"]')->attr('value');
            $kindle    = $node->filter('#kcpApp_feature_div')->count();
            $audioBook = $node->filter('#audiblebuyboxv2_feature_div')->count();
            // if (strpos($dimension, 'stripbooks') !== false || strpos($dimension, 'aps') !== false) {

            if ($kindle == false && $audioBook == false) {

                $availabilityCount = $node->filter('#availability > span.a-size-medium.a-color-state')->count();
                $countOutOfStock   = $node->filter('#outOfStock')->count();

                if ($availabilityCount == false && $countOutOfStock == false) {

                    $bookImages = $crawler->filter('body')->each(function ($node) {
                        return self::get_book_images($node);
                    });
                    $formattedBookImages = array_reduce($bookImages, 'array_merge', []);
                    if ($node->filter('.swatchElement.selected')->count() == true) {
                        preg_match_all('!\d+\.?\d+!', $node->filter('.swatchElement.selected')->text(''), $match);
                        $price = $match[0][0];
                    } else {
                        if ($node->filter('#mediaOlpInTab')->count() == true) {
                            $price = $node->filter('#mediaOlpInTab > div > div > div.a-fixed-right-grid-col.accordion-row-left-content.a-col-left > div:nth-child(2) > div > div:nth-child(1) > span > span > span')->text();

                        } elseif ($node->filter('#newBuyBox_263333')->count() == true) {
                            $price = $node->filter('#newBuyBoxPrice')->text();
                        } elseif ($node->filter('#newAccordionRow_263333')->count() == true) {
                            $price = $node->filter('#newBuyBoxPrice')->text();
                        }
                    }

                    $p = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si", '<$1$2>', $node->filter('#detailBulletsWrapper_feature_div')->html(''));
                    $q = preg_replace('/<([^<\/>]*)([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU', '', $p);
                    $r = preg_replace('/\s+/', ' ', $q);
                    // $bookDW = self::scrap_dimension_weight($node);

                    $items = [
                        // 'asin' => $node->filter('#ASIN')->attr('value'),
                        'price'           => $price,
                        'product_details' => $r,
                        'images'          => $formattedBookImages,
                        // 'dw'              => $bookDW,
                    ];
                    return $items;
                } else {
                    return [];
                }
            } else {
                return [];
            }
        });
        $bookData = array_reduce($book, 'array_merge', []);
        return $bookData;
    }
    public static function get_book_images($node)
    {
        $script = $node->filter('script')->each(function ($subNode) {
            if (strpos($subNode->html(), 'imageGalleryData')) {
                return $subNode->html();
            }
        });

        $imageScript = array_values(array_filter($script));
        if (!empty($imageScript)) {
            $stepOne   = explode("'imageGalleryData' :", $imageScript[0]);
            $stepTwo   = explode("'centerColMargin'", $stepOne[1]);
            $finalStep = str_replace("}],", "}]", $stepTwo[0]);
            $images    = json_decode($finalStep, true);
        } else if ($node->filter('#imgBlkFront')->count() == true) {
            $k                    = $node->filter('#imgBlkFront')->attr('data-a-dynamic-image');
            $images[0]['mainUrl'] = key((array) json_decode($k));
        } else {
            $images = [];
        }
        return $images;
    }

    public static function scrap_product_research($url)
    {

        $client  = self::createClient();
        $crawler = $client->request('GET', $url);
        $data    = $crawler->filter('body')->each(function ($node) {

            $item = [
                "title"             => $node->filter('#productTitle')->text('N/A'),
                "store_link"        => $node->filter('#bylineInfo')->attr('href'),
                "store_name"        => $node->filter('#bylineInfo')->text('N/A'),
                "answered_question" => $node->filter('#askATFLink')->text('N/A'),
                "availability"      => $node->filter('#outOfStock')->text('In Stock'),
                "sales_rank"        => self::aadp_clean_html(self::get_seller_rank($node)),
                "ships_from"        => $node->filter('#tabular-buybox-truncate-0')->text(''),
                "sold_by"           => $node->filter('#tabular-buybox-truncate-1')->text(''),
            ];

            return $item;
        });
        $data = array_reduce($data, 'array_merge', []);
        return $data;

    }

    public static function get_seller_rank($node)
    {
        $detailBullet = $node->filter('#detailBullets_feature_div')->count();
        $proDetail    = $node->filter('#prodDetails')->count();
        if ($detailBullet == true) {
            $data = $node->filter('#SalesRank')->html('N/A');
        } else if ($proDetail) {
            if ($node->filter('#productDetails_detailBullets_sections1')->count() == true) {
                $filthyData = $node->filter('#productDetails_detailBullets_sections1 > tr > th')->each(function ($subNode) {
                    if ($subNode->text('') === 'Best Sellers Rank') {
                        return $subNode->nextAll()->html('');
                    }
                });

            } else {
                $filthyData = [];
            }
            $data = '<b>Amazon Best Sellers Rank:</b>' . implode('', array_filter($filthyData));
        } else {
            $data = '';
        }
        return $data;

    }

}
