<?php

/**
 * @package aadp
 */

namespace Aadp\Base;

use WC_Product_Attribute;
use WC_Product_Simple;
use WC_Product_Variable;
// use \Aadp\Base\ScrapingController;
use WC_Product_Variation;
use \Aadp\Api\Callbacks\AdminCallbacks;
use \Aadp\Api\SettingsApi;
use \Aadp\Base\BaseController;

class ProductImporterController extends BaseController
{
    public $settings;
    public $callbacks;
    public $subpages = [];

    public function register()
    {
        if (!$this->activated('product_importer')) {
            return;
        }
        $this->settings  = new SettingsApi();
        $this->callbacks = new AdminCallbacks();
        $this->setSubpages();
        $this->settings->addSubPages($this->subpages)->register();

        add_action('wp_ajax_aadp_get_department', [$this, 'aadp_get_department']);
        add_action('wp_ajax_product_find', [$this, 'product_find']);
        add_action('wp_ajax_aadp_product_research', [$this, 'aadp_product_research']);
        add_action('wp_ajax_aadp_cargo_import', [$this, 'aadp_cargo_import']);
        // add_action('init', [$this, 'aadp_cargo_import']);
        add_action('wp_ajax_aadp_store_cargo', [$this, 'aadp_store_cargo']);
        add_action('wp_ajax_aadp_get_existing_category', [$this, 'aadp_get_existing_category']);
        add_action('wp_ajax_aadp_remove_to_cargo', [$this, 'aadp_remove_to_cargo']);
        add_action('wp_loaded', [$this, 'boot_session']);

    }
    public function boot_session()
    {
        session_start();
    }
    public function product_importer_display_settings_page()
    {
        // check if user is allowed access
        if (!current_user_can('manage_options')) {
            return;
        }
    }
    public function setSubPages()
    {
        $this->subpages = [
            [
                'parent_slug' => 'wp_amazon',
                'page_title'  => "Product Importer",
                'menu_title'  => "Product Importer",
                'capability'  => 'manage_options',
                'menu_slug'   => 'wp_amazon_product_importer',
                'product_importer_display_settings_page',
                'callback'    => [$this->callbacks, 'adminPI'],
                null,
            ],
            [
                'parent_slug' => 'wp_amazon',
                'page_title'  => "AADP Amazon Affiliate & Dropshipping Plugins Docs",
                'menu_title'  => "AADP Docs",
                'capability'  => 'manage_options',
                'menu_slug'   => 'aadp_amazon_affiliate_dropshipping_docs',
                'aadp_docs',
                'callback'    => [$this->callbacks, 'adminDocs'],
                null,
            ],
            [
                'page_title'  => '',
                'parent_slug' => 'wp_amazon',
                'menu_title'  => "Upgrade to Premium",
                'capability'  => 'manage_options',
                'menu_slug'   => 'https://amazonplugins.com/amazon-affiliate-dropshipping-plugin-with-product-research-for-woocommerce/',
                'callback'    => [],
                null,
            ],

        ];
    }
    public static function aadp_get_department()
    {
        if (isset($_POST['store'])) {
            $store_url = esc_url_raw($_POST['store']);
            $data      = ScrapingController::scrap_department($store_url);
            $html      = "";
            foreach ($data as $key => $value) {
                $html .= '<option value="' . esc_attr($key,'aadp') . '">' . esc_html($value,'aadp') . '</option>';
            }
            wp_send_json($html);
            wp_die();
        } else {
            $store_url = 'https://www.amazon.com/';
            $data      = ScrapingController::scrap_department($store_url);
            $html      = "";
            foreach ($data as $key => $value) {
                $html .= '<option value="' . esc_attr($key,'aadp') . '">' . esc_html($value,'aadp') . '</option>';
            }
            return $html;
        }
    }

    public function product_find()
    {
        check_ajax_referer('product_importer_script', 'nonce');

        $url          = esc_url_raw($_POST['url']);
        $store        = esc_url_raw($_POST['store']);
        $current_page = wc_clean($_POST['page']);
        $total_pages  = wc_clean($_POST['total_page']);
        $result       = ScrapingController::scrap_find_product($url);

        $intPage = (int) $result['total_page'];
        if (!empty($intPage)) {
            $max_pages = $intPage;
        } else {
            $max_pages = (int) $total_pages;
        }
        $base = preg_replace('/&page=[^&]*/', '', $url);

        if ($max_pages > 1) {
            $pages = paginate_links([
                'base'    => $base . '%_%',
                'format'  => '&page=%#%',
                'current' => $current_page,
                'total'   => $max_pages,
            ]);
        } else {
            $pages = '';
        }
        $allowed_html = [
            'div' => [
                'class'=>[]
            ],
            'span' => [
                'class' => []
            ]
        ];
        $html = '<div class="container cart-drawer-push aadp-pi-content">';
        $html = '<div class="row">';

        $html .= '<div class="col-8">';
        $html .= '<div class="mt-4 aadp-pagination">';
        $html .= '<input type="hidden" id="total-pages" value="' . esc_attr($max_pages, 'aadp') . '">';
        $html .= $pages;

        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="col-4 btn_aadp_all">';
        $html .= '<button type="button" class="btn btn-warning btn-sm"  id=aadp-add-all>Add All Products</button>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="row"';
        $html .= "data-masonry='{";
        $html .= '"percentPosition": true ';
        $html .= "}'";
        $html .= '>';
        foreach ($result['data'] as $key => $value) {
            $html .=
                '<div class="col-lg-3 col-md-6 mb-4  aadp-product" >' .
                '    <div class="card h-100 card-custom">' .
                '		<a href="' . esc_url($store, 'aadp') . esc_attr($value['product_url'], 'aadp') . '"><img class="card-img-top img-responsive" src="' . esc_url($value['image'],'aadp') . '" alt=""></a>' .
                '        <div class="card-body">' .
                '            <span class="badge badge-warning">' . esc_html($value['badge'], 'aadp') . '</span><span class="badge badge-danger">' . esc_html($value['sponsored'], 'aadp' ). '</span><span class="badge badge-success">' . esc_html($value['discount'], 'aadp') . '</span>' . '<span class="badge badge-info">' . esc_html($value['prime'], 'aadp') . '</span>' .
                '            <p class="card-title p_title"> <a href="' . esc_url($store,'aadp') . esc_attr($value['product_url'], 'aadp') . '">' . esc_html($value['tittle'], 'aadp') . '</a></p>';
            if (!empty($value['book_data'])) {
                foreach ($value['book_data'] as $bookKey => $bookValue) {
                    $html .= '<div>';
                    $html .=
                        '<h5 class="card-title">' . esc_html($bookValue['type'],'aadp') . '</h5>';
                    $html .=
                        '<h6 class="card-title"><span>' . esc_html($bookValue['selling_price'],'aadp') . '</span>' . '<span><del>' . esc_html($bookValue['regular_price'],'aadp') . '</del></span></h6>';
                    $html .= '</div>';
                }
                $html .= '<input type="hidden" class="aadp-type" value="book">';
            } else {
                $html .=
                    '<h6 class="card-title"><span>' . esc_html($value['selling_price'], 'aadp') . '</span>' . '<span><del>' . esc_html($value['regular_price'], 'aadp') . '</del></span></h6>';
                $html .= '<input type="hidden" class="aadp-type" value="product">';
            }

            $html .=
                '            <p class="card-text"></p>';
            if ($value['ratings'] != "") {
                $html .= '<small class="text-muted">' . wp_kses(ScrapingController::starRating($value['ratings']), $allowed_html)  . '</small>';
            } else {

            }
            '            <small class="text-muted">(' . esc_html($value['reviews'], 'aadp') . ')</small>' .
                '        </div>' .
                '        <div class="card-footer">';

            $html .= '<div class="row">';
            $html .= '<div class="col-md-12 col-sm-12">';
            $html .= '<button class="btn btn-sm btn-success aadp-cargo" data-product-link="' . esc_url($store,'aadp') . esc_attr($value['product_url'], 'aadp') . '" data-product-img="' . esc_url($value['image'],'aadp'). '" data-product-title="' . esc_html($value['tittle'], 'aadp'). '" data-product-store="' . esc_url($store,'aadp') . '"> Add to Cargo </button>';
            $html .= '</div>';
            $html .= '<div class="col-md-12 col-sm-12">';
            $html .= '<button class="btn btn-sm btn-info aadp-product-research" data-product-link="' . esc_url($store, 'aadp') . esc_attr($value['product_url'],'aadp') . '">Product Research</button>';
            $html .= '</div>';

            $html .= '</div>';

            $html .=
                '        </div>' .
                '    </div>' .
                '</div>';
        }
        $html .= '</div>';
        $html .= '<div class="mb-4 aadp-pagination">';
        $html .= $pages;
        $html .= '</div></div>';
        wp_send_json_success($html);
        wp_die();
    }

    public static function aadp_sanitize_array(&$array)
    {

        foreach ($array as $key => &$value) {
            if (preg_match('/(http|ftp|mailto)/', $value)) {
                $sanitizedArray[wc_clean($key)] = esc_url_raw($value);
            } else {
                $sanitizedArray[wc_clean($key)] = wc_clean($value);

            }
            // sanitize if value is not an array

        }

        return $sanitizedArray;

    }
    public function aadp_cargo_import()
    {
        check_ajax_referer('product_importer_script', 'nonce');
        $data = $this->aadp_sanitize_array($_POST['import_data']);
        // wp_send_json_success($data);
        // wp_die();
        // $data = [
        //     "title"=> "A Promised Land",
        //     "url"=> "https://www.amazon.com//A-Promised-Land-Obama-Audiobook/dp/B08HGH9JMF/ref=sr_1_1?dchild=1&keywords=obama&qid=1621877454&sr=8-1",
        //     "cargo_key"=> "1",
        //     "type"=> "product",
        //     "cat_type"=> "x-cat",
        //     "cat_val"=> "15"
        // ];

        if (isset($_SESSION['aadp_cargo'])) {
            unset($_SESSION['aadp_cargo'][$data['cargo_key']]);
        }
        if ($data['cat_type'] == 'n-cat') {
            $cat             = wp_insert_term($data['cat_val'], 'product_cat');
            $data['cat_val'] = $cat['term_id'];
        }
        if ($data['type'] == 'product') {

            $data['product'] = ScrapingController::scrap_import_product($data['url'] . '?th=1&psc=1');
// var_dump($data);exit;
            if (!empty($data['product'])) {
                if (isset($data['product']['book']) && $data['product']['book'] == true) {
                    $r = $this->aadp_book_importer($data);
                    if (!empty($r)) {
                        $data['message']              = $data['title'] . ' is imported successfully as variable product. id: <a href="' . get_edit_post_link($r['product_id']) . '">' . $r['product_id'] . '</a>';
                        $data['product']['variation'] = null;
                    } else {
                        $data['message']              = $data['title'] . '  is currently unavailable or out of stock.';
                        $data['product']['variation'] = null;

                    }
                    wp_send_json_success($data);
                    wp_die();
                } else {
                    $rData = $this->create_product($data);
                    if (isset($rData['product']['variation']) && $rData['product']['variation'] == null) {
                        $rData['message'] = $data['title'] . ' is imported successfully as simple product. id: <a href="' . get_edit_post_link($rData['product_id']) . '">' . $rData['product_id'] . '</a>';
                    } else {
                        $rData['message'] = $data['title'] . ' is imported successfully as variable product. id: <a href="' . get_edit_post_link($rData['product_id']) . '">' . $rData['product_id'] . '</a>';
                    }
                    wp_send_json_success($rData);
                    wp_die();
                }

            } else {
                $data['message'] = $data['title'] . ' is currently unavailable or out of stock.';
                wp_send_json_success($data);
                wp_die();
            }
        } else {

            $r = $this->aadp_book_importer($data);
            if (!empty($r)) {
                $data['message']              = $data['title'] . ' is imported successfully as variable product. id: <a href="' . get_edit_post_link($r['product_id']) . '">' . $r['product_id'] . '</a>';
                $data['product']['variation'] = null;
            } else {
                $data['message']              = $data['title'] . '  is currently unavailable or out of stock.';
                $data['product']['variation'] = null;

            }

            wp_send_json_success($data);
            wp_die();
        }
    }
    public function aadp_remove_to_cargo()
    {
        check_ajax_referer('product_importer_script', 'nonce');
        $key = wc_clean($_POST['cargo_key']);

        if (isset($_SESSION['aadp_cargo'])) {
            unset($_SESSION['aadp_cargo'][$key]);
        }
        wp_send_json_success();
    }

    public function create_product($data)
    {
        $objProduct = new WC_Product_Simple();

        $objProduct->set_name($data['product']['product_name']); //Set product name.
        $objProduct->set_status('publish'); //Set product status.
        // $objProduct->set_featured(TRUE); //Set if the product is featured.                          | bool
        $objProduct->set_catalog_visibility('visible'); //Set catalog visibility.                   | string $visibility Options: 'hidden', 'visible', 'search' and 'catalog'.
        $objProduct->set_description($data['product']['description']); //Set product description.
        $objProduct->set_short_description($data['product']['overview'] . '<br>' . $data['product']['feature']); //Set product short description.
        $objProduct->set_sku(uniqid()); //Set SKU
        $objProduct->set_slug($data['product']['product_name'] . '-' . uniqid());
        if ($data['product']['deal_price'] == null) {
            $objProduct->set_regular_price($data['product']['regular_price']); //Set the product's regular price.
        } else {
            $objProduct->set_regular_price($data['product']['deal_price']); //Set the product's regular price.
            $objProduct->set_sale_price($data['product']['regular_price']); //Set the product's sale price.

        }
        $objProduct->set_manage_stock(false); //Set if product manage stock.                         | bool
        $objProduct->set_stock_status('instock'); //Set stock status.                               | string $status 'instock', 'outofstock' and 'onbackorder'
        $objProduct->set_backorders('no'); //Set backorders.                                        | string $backorders Options: 'yes', 'no' or 'notify'.
        $objProduct->set_sold_individually(false); //Set if should be sold individually.            | bool

        $objProduct->set_average_rating($data['product']['average_ratings']); //Set the product average rating.
        $objProduct->set_rating_counts($data['product']['rating_count']); //Set the product rating count.

        $feature_image_id = $this->asd_product_upload($data['product']['images'][0]['hiRes']);
        $objProduct->set_image_id($feature_image_id); //Set main image ID.                                         | int|string $image_id Product image id.
        //remove feature image from array
        unset($data['product']['images'][0]);
        // Re-index the array elements

        $galleryImages = array_values($data['product']['images']);
        $galleryIds    = $this->asd_gallery_upload($galleryImages);
        $objProduct->set_gallery_image_ids($galleryIds); //Set gallery attachment ids.                       | array $image_ids List of image ids.

        $objProduct->set_category_ids([$data['cat_val']]);
        $tag = get_term_by('name', 'amazon', 'product_tag');
        $objProduct->set_tag_ids([$tag->term_id]);

        $objProduct->add_meta_data('aadp_asin', $data['product']['ASIN']);
        $objProduct->add_meta_data('aadp_store', $data['store']);

        $product_id         = $objProduct->save(); //Saving the data to create new product, it will return product ID.
        $data['product_id'] = $product_id;

        return $data;
    }

    public function asd_product_upload($file)
    {
        $desc       = 'some description' . uniqid();
        $file_array = ['name' => wp_basename($file), 'tmp_name' => download_url($file)];
        // If error storing temporarily, return the error.
        if (is_wp_error($file_array['tmp_name'])) {
            return $file_array['tmp_name'];
        }
        // Do the validation and storage stuff.
        $id = media_handle_sideload($file_array, $desc);
        // If error storing permanently, unlink.
        if (is_wp_error($id)) {
            @unlink($file_array['tmp_name']);
            return $id;
        }
        return $id;
    }
    public function asd_gallery_upload($files)
    {
        // var_dump($files);exit;
        $ids = [];
        foreach ($files as $key => $value) {
            // var_dump($value);exit;
            if (array_key_exists("hiRes", $value) && $value['hiRes'] !== null) {
                $ids[] = $this->asd_product_upload($value['hiRes']);
            } else {
                $ids[] = $this->asd_product_upload($value['large']);
            }
        }
        return $ids;
    }
    public function aadp_book_importer($bdata)
    {
        $results = ScrapingController::scrap_books($bdata['url']);
        // wp_send_json_success($results);
        // wp_die();
        $product = new WC_Product_Variable();
        $product->set_name($results['title']);
        $product->set_short_description($results['feature_desc']);
        $product->set_status('publish'); //Set product status.
        $product->set_catalog_visibility('visible'); //Set catalog visibility.                   | string $visibility Options: 'hidden', 'visible', 'search' and 'catalog'.
        $product->set_slug($results['title'] . '-' . uniqid());
        $product->set_sku(uniqid());
        $product->set_manage_stock(false); //Set if product manage stock.                         | bool
        $product->set_stock_status('instock'); //Set stock status.                               | string $status 'instock', 'outofstock' and 'onbackorder'
        $product->set_backorders('no'); //Set backorders.                                        | string $backorders Options: 'yes', 'no' or 'notify'.
        $product->set_sold_individually(false); //Set if should be sold individually.            | bool
        $product->set_average_rating($results['average_ratings']); //Set the product average rating.
        $product->set_rating_counts($results['rating_count']); //Set the product rating count.

        $product->add_meta_data('aadp_asin', ScrapingController::get_asin_by_url($bdata['url']));
        $product->add_meta_data('aadp_store', $bdata['store']);

        $i = 0;
        foreach ($results['book-type'] as $key => $value) {
            $data = ScrapingController::scrap_book_details($value['type-url']);
            if (!empty($data)) {
                $data['asin']                  = ScrapingController::get_asin_by_url($value['type-url']);
                $attr_option[]                 = $value['type-name'];
                $bookData[$value['type-name']] = $data;
                if ($i == 0) {
                    $results['images'] = $data['images'];
                }
                $i++;
            }
        }

        if (empty($bookData)) {
            return;
        }

        $featureImage = $this->asd_product_upload($results['images'][0]['mainUrl']);
        $product->set_image_id($featureImage); //Set main image ID.                                         | int|
        //remove feature image from array
        unset($results['images'][0]);
        // Re-index the array elements
        $galleryImages = array_values($results['images']);
        $galleryIds    = $this->asd_gallery_upload($galleryImages);
        $product->set_gallery_image_ids($galleryIds); //Set gallery attachment ids.                       | array
        $attributes = $this->set_book_attributes($attr_option);
        $product->set_attributes($attributes); //Set product attributes.                   | array $raw_attributes
        $product->set_category_ids([$bdata['cat_val']]);
        $tag = get_term_by('name', 'amazon', 'product_tag');
        $product->set_tag_ids([$tag->term_id]);

        $bp['product_id'] = $product->save();
        $bp['variation']  = [];
        foreach ($bookData as $key => $value) {
            $variation = new WC_Product_Variation();
            $variation->set_parent_id($bp['product_id']);
            $variation->set_attributes(["type" => $key]);
            $variation->set_status('publish');
            $variation->set_sku($value['asin'] . '-' . uniqid());
            $variation->set_price($value['price']);
            $variation->set_regular_price($value['price']);
            $variation->set_stock_status("instock");
            $variation->set_description($value['product_details']);
            $featureImageV = $this->asd_product_upload($value['images'][0]['mainUrl']);
            $variation->set_image_id($featureImageV); //Set main image ID.                                         |
            //remove feature image from array
            unset($value['images'][0]);
            // Re-index the array elements
            $galleryImagesV = array_values($value['images']);
            $galleryIds     = $this->asd_gallery_upload($galleryImagesV);
            $variation->set_gallery_image_ids($galleryIds); //Set gallery attachment ids.                       |
            $variation->add_meta_data('aadp_asin', $value['asin']);
            $variation->add_meta_data('aadp_store', $bdata['store']);
            $variation->add_meta_data('aadp_type', $bdata['book']);
            $bp['variation'][] = $variation->save();
        }
        // wp_send_json_success($bookData);wp_die();
        // return $bookData;
        return $bp;
    }
    public function set_book_attributes($option)
    {
        $attribute = new WC_Product_Attribute();
        $attribute->set_id(wc_attribute_taxonomy_id_by_name('type')); //if passing the attribute name to get the ID
        $attribute->set_name('Type'); //attribute name
        $attribute->set_options($option); // attribute value
        $attribute->set_position(0); //attribute display order
        $attribute->set_visible(1); //attribute visiblity
        $attribute->set_variation(1); //to use this attribute as varint or not
        $attributes[] = $attribute;
        return $attributes;
    }

    public function aadp_store_cargo()
    {
        check_ajax_referer('product_importer_script', 'nonce');

        $cargo = [
            'title' => sanitize_text_field($_POST['title']),
            'img'   => esc_url_raw($_POST['img']),
            'url'   => esc_url_raw($_POST['url']),
            'store' => esc_url_raw($_POST['store']),
            'type'  => sanitize_text_field($_POST['type']),
        ];

        $_SESSION['aadp_cargo'][] = $cargo;
        $key                      = wc_clean(array_key_last($_SESSION['aadp_cargo']));
        wp_send_json_success($key);
    }
    public function aadp_get_existing_category()
    {
        check_ajax_referer('product_importer_script', 'nonce');
        $taxonomy     = 'product_cat';
        $orderby      = 'name';
        $show_count   = 0; // 1 for yes, 0 for no
        $pad_counts   = 0; // 1 for yes, 0 for no
        $hierarchical = 1; // 1 for yes, 0 for no
        $empty        = 0;
        $args         = [
            'taxonomy'     => $taxonomy,
            'orderby'      => $orderby,
            'show_count'   => $show_count,
            'pad_counts'   => $pad_counts,
            'hierarchical' => $hierarchical,
            'hide_empty'   => $empty,
        ];
        $categories = get_categories($args);

        if (!empty($categories)) {
            $html = '<div class="form-group" id="aadp-xc">';
            $html .= '<select class="form-select form-select-sm" id="aadp-category" aria-label=".form-select-sm example">';
            $html .= '  <option selected>Select One</option>';
            foreach ($categories as $category) {
                if ($category->slug == "uncategorized") {
                    $selected = 'selected';
                } else {
                    $selected = '';
                }
                $html .= ' <option value="' . esc_attr($category->name, 'aadp') . '"' . esc_attr($selected, 'aadp') . '>' . esc_html($category->name, 'aadp'). '</option>';
            }
            $html .= '</select>';
            $html .= '</div>';
        }
        wp_send_json_success($html);
    }
    public function aadp_product_research()
    {
        check_ajax_referer('product_importer_script', 'nonce');
        $url  = esc_url_raw($_POST['url']) . '?th=1&psc=1';
        $data = ScrapingController::scrap_product_research($url);
        ob_start();
        ?>
<div class="modal fade" id="researchModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title aadp_modal_title" id="exampleModalLongTitle"><?php esc_html_e( $data['title'],'aadp') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <!-- <span aria-hidden="true">&times;</span> -->
                </button>
            </div>
            <div class="modal-body">
                <ul>
                    <li class="aadp_list1"><strong>Store :</strong> <a href="<?php esc_html_e($data['store_link'], 'aadp');?>"><?php esc_html_e($data['store_name'], 'aadp');?></a></li>
                    <li class="aadp_list2"><strong>Seller response on customer question :</strong> <?php esc_html_e($data['answered_question'], 'aadp')?></li>
                    <li class="aadp_list1"><strong>Availability :</strong> <?php esc_html_e($data['availability'], 'aadp')?></li>
                    <li class="aadp_list2"><strong>Ships from :</strong> <?php esc_html_e ($data['ships_from'], 'aadp');?></li>
                    <li class="aadp_list1"> <strong>Sold by :</strong> <?php esc_html_e($data['sold_by'], 'aadp')?></li>
                    <li class="aadp_list3"><?php esc_html_e($data['sales_rank'], 'aadp')?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php
$html = ob_get_clean();
        wp_send_json_success($html);
    }

}