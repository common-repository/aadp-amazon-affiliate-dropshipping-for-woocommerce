<?php

namespace Aadp\Base;

class CustomRequestController extends BaseController
{
    public function register()
    {
        if (!$this->activated('amazon_hero_search')) {
            return;
        }
        add_shortcode('aadp_search', [$this, 'aadp_serach_form']);
        add_action('wp_ajax_get_department', [$this, 'get_department']);
        add_action('wp_ajax_nopriv_get_department', [$this, 'get_department']);
        add_action('wp_ajax_search_product', [$this, 'search_product']);
        add_action('wp_ajax_nopriv_search_product', [$this, 'search_product']);
        add_action('wp_ajax_aadp_affiliate_view_details', [$this, 'aadp_affiliate_view_details']);
        add_action('wp_ajax_nopriv_aadp_affiliate_view_details', [$this, 'aadp_affiliate_view_details']);
        add_action('wp_ajax_nopriv_change_variation', [$this, 'change_variation']);
        add_action('wp_ajax_change_variation', [$this, 'change_variation']);
    }
    public function aadp_serach_form()
    {
        $store_url  = get_option('wp_amazon_country');
        $department = $this->get_department();
        ?>
<div class="form-group aadp_searc_amazon row">
    <label class="col col-form-label aadp_mar_fixing" for="">Search On Amazon </label>
    <select class="col aadp_store_country" name="" id="store-country">
        <?php foreach ($store_url as $key => $value) {
            ?>
        <option value="<?php esc_attr_e( $value,'aadp'); ?>" <?php if ($key == "United States") {
                echo "selected";
            }?>><?php esc_html_e( $key, 'aadp'); ?></option>
        <?php
}
        ?>
    </select>
    <select class="col aadp_store_country" name="" id="department">
        <?php
foreach ($department as $key => $value) {
            # code...
            ?>
        <option value=<?php esc_attr_e( $key, 'aadp') ?>><?php esc_html_e( $value, 'aadp') ?></option>
        <?php
}
        ?>
    </select>


    <input id="aadp-search-input" class="col aadp-search-input" required type="text" name="aadp-search-keyword">
    <button type="button" id="aadp-search-btn" class="col aadp-search-btn"><?php _e('Search', 'aadp')?></button>
</div>

<div id="aadp-import-product"></div>
<?php
}

    public function get_department()
    {
        if (isset($_POST['store'])) {
            $store_url = esc_url_raw($_POST['store']);

            $data = $this->scrap_department($store_url);
            $html = "";
            foreach ($data as $key => $value) {
                $html .= '<option value="' . esc_attr($key,'aadp') . '">' . esc_html($value, 'aadp') . '</option>';
            }

            wp_send_json($html);
            wp_die();
        } else {
            $store_url = 'https://www.amazon.com/';
            $data      = $this->scrap_department($store_url);
            return $data;
        }
    }
    public function scrap_department($store_url)
    {
        $client  = ScrapingController::createClient();
        $crawler = $client->request('GET', $store_url);
        $data    = $crawler->filter('#searchDropdownBox > option')->each(function ($node) {
            $item = [
                substr($node->attr('value'), 13) => $node->text(),
            ];
            return $item;
        });
        $result = array_reduce($data, 'array_merge', []);
        return $result;
    }
    public function search_product()
    {
        check_ajax_referer('myplugin_frontend_script', 'nonce');
        $url   = esc_url_raw($_POST['url']);
        $store = esc_url_raw($_POST['store']);

        $result = ScrapingController::scrap_find_product($url);
        $allowed_html = [
            'div' => [
                'class'=>[]
            ],
            'span' => [
                'class' => []
            ]
        ];
        $html = '<div class="container">';
        $html .= '<div class="row">';
        $i = 0;
        foreach ($result['data'] as $key => $value) {
            $html .=
                '<div class="col-lg-3 col-md-3 mb-4 aadp-product">' .
                '    <div class="card h-100 card-custom"><a href="' .esc_url($store,'aadp') . esc_attr($value['product_url'], 'aadp') . '"><img class="img-responsive" src="' . esc_url($value['image'],'aadp') . '" alt=""></a>' .
                '        <div class="card-body">' .
                '            <h5><span>' . esc_html($value['selling_price'], 'aadp') . '</span>' . '<span><del>' .esc_html( $value['regular_price'],'aadp') . '</del></span></h5>' . '<span class="badge badge-warning">' . esc_html($value['badge'], 'aadp' ). '</span><span class="badge badge-danger">' . esc_html($value['sponsored'], 'aadp') . '</span><span class="badge badge-success">' . esc_html($value['discount'], 'aadp') . '</span>' . '<span class="badge badge-info">' . esc_html($value['prime'], 'aadp') . '</span>' .
                '            <h4 class="card-title card-title-design"> <a href="' . esc_url($store, 'aadp') . esc_attr($value['product_url'], 'aadp') . '">' . esc_html($value['tittle'], 'aadp') . '</a></h4>' .
                '            <p class="card-text"></p>';
            if ($value['ratings'] != "") {
                $html .= '            <small class="text-muted">' . wp_kses(ScrapingController::starRating($value['ratings']), $allowed_html) . '</small>';
            } else {
            }
            '            <small class="text-muted">(' . esc_html($value['reviews'], 'aadp') . ')</small>' .
                '        </div>' .
                '        <div class="card-footer">';
            if (get_option('use_for') == 'affiliate') {
                $html .= '<button class="aadp-affiliate-view-details" data-product-link="' . esc_url($store, 'aadp') . esc_attr($value['product_url']) . '" > View Details </button>';
            } else {
                $html .= '<button class="aadp-custom-request" data-product-link="' . esc_url($store, 'aadp') . esc_attr($value['product_url'], 'aadp') . '"> Custom Request </button>';
            }
            $html .=
                '        </div>' .
                '    </div>' .
                '</div>';
            $i++;
            if ($i == 8) {
                break;
            }
        }
        $html .= '</div>';
        $html .= '</div>';
        wp_send_json_success($html);
        wp_die();

    }
    public function aadp_affiliate_view_details()
    {
        check_ajax_referer('myplugin_frontend_script', 'nonce');
        ob_start();
        $request = esc_url_raw($_POST['request']);
        $data    = ScrapingController::scrap_product($request);
        ?>
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  bd-example-modal-lg modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><?php esc_html_e( $data['title'], 'aadp') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <!-- <span aria-hidden="true">&times;</span> -->
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6" id="tabbed_image_gallery">
                        <?php echo CustomRequestController::tabbed_image_gallery($data['images']); ?>
                    </div>
                    <div class="col-6 aaadp_text_alignment">
                        <div class="aadp-availability"></div>
                        <div id="variation-price">
                            <?php
echo wp_kses(  $data['price'], ['span'=>[]] );
        ?>
                        </div>
                        <div>
                            <?php
foreach ($data['attribute'] as $key => $value) {
            ?>
                            <label for=""><?php
echo ucfirst(str_replace('_', ' ', esc_attr($key,'aadp'))) . ":";
            ?></label>
                            <select name="" id="" class="attribute" data-attr=<?php
esc_attr_e($key, 'aadp') 
            ?>>
                                <option value="">Select</option>
                                <?php
foreach ($value as $attributeKey => $attributeValue) {
                ?>
                                <option value="<?php
esc_attr_e( $attributeKey, 'aadp');
                ?>"><?php
                esc_html_e( $attributeValue, 'aadp');
                ?></option>
                                <?php
}
            ?>
                            </select>
                            <?php
}
        ?>
                        </div>
                        <div>
                            <ul>
                                <?php
foreach ($data['feature'] as $key => $value) {
            echo "<li>" . esc_html($value, 'aadp') . "</li>";
        }
        ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="form-group btn-des">
                        <label for="quantity">Quantity *</label>
                        <input type="number" class="form-control" id="quantity" min="1" value="1" required>
                        
                        <?php
 $parse          = parse_url( $request );
 $store          = $parse['scheme'] . '://' . $parse['host'] . '/';
 $affiliate_tags = get_option( 'amazon_associate_tag' );
 $tag            = StoreController::aadp_search_tag( $affiliate_tags, $store );
?>
                <a href="<?php echo esc_url( $request, 'aadp' ) ?>" class="btn btn-block btn-primary" id="aadp-add-to-cart"
                    data-product-link="<?php echo esc_url( $request, 'aadp' ) ?>" aadp-affiliate-tag="<?php esc_attr_e( $tag, 'aadp' )?>"
                    data-asin="<?php esc_attr_e( $data['asin'], 'aadp' );?>">Add to cart</a>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<?php
$html['template'] = ob_get_clean();
        if (is_array($data['variation']) && !empty($data['variation'])) {
            $html['variation'] = $data['variation'];
        }
        wp_send_json($html);
        wp_die();
    }
    public function change_variation()
    {
        check_ajax_referer('myplugin_frontend_script', 'nonce');
        $url                    = esc_url_raw($_POST['variationLink']);
        $data                   = ScrapingController::scrap_product($url);
        $ajaxData               = [];
        $ajaxData['galleryTab'] = $this->tabbed_image_gallery($data['images']);
        $ajaxData['price']      = $data['price'];
        wp_send_json($ajaxData);
        wp_die();
    }
    public static function tabbed_image_gallery($images)
    {
        $html  = '<img class="xzoom" src="' . esc_url($images[0]['large'], 'aadp') . '" xoriginal="' . esc_url($images[0]['hiRes'],'aadp') . '" /><div class="xzoom-thumbs">';
        $first = true;
        foreach ($images as $key => $value) {
            if ($first) {
                $html .= '<a href="' . esc_url($value['hiRes'], 'aadp') . '"><img class="xzoom-gallery" width="80" src="' . esc_url($value['large'], 'aadp') . '" ></a>';
                // $x++;
                // echo $x;
                $first = false;
            } else {
                $html .= '<a href="' . esc_url($value['hiRes'], 'aadp') . '"><img class="xzoom-gallery" width="80" src="' . esc_url($value['large'], 'aadp') . '"></a>';
            }
        }
        $html .= '</div>';
        return $html;
    }

}