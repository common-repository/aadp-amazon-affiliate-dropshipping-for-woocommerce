<?php
defined('ABSPATH') || exit;

?>
<div class="aadp_cargo_right">
    <a class=" " href="#" id="cart">
        <img src="<?php echo $this->plugin_url . '/images/hnet.com-image.ico' ?>" height="10px"
            style="width: 25px;height: 30px;" width="10px">
        <span class="badge bg-danger" id="aadp-cargo-counter">
            <?php

if (isset($_SESSION['aadp_cargo'])) {
    echo count($_SESSION['aadp_cargo']);
} else {
    echo 0;
}

?>
        </span>
    </a>
</div>
<div class="container" id="aadp-cargo-box" style="display:none">
    <div class="shopping-cart">
        <ul class="shopping-cart-items">
            <?php
if (isset($_SESSION['aadp_cargo']) && count($_SESSION['aadp_cargo']) > 0) {
    foreach ($_SESSION['aadp_cargo'] as $key => $value) {
        ?>
            <li class="clearfix aadp-import-cargo-url" data-product-title="<?php echo wc_clean($value['title']); ?>"
                data-product-url="<?php echo esc_url_raw($value['url']); ?>" data-store="<?php echo esc_url_raw($value['store']) ?>"
                data-type="<?php echo wc_clean($value['type']) ?>" data-cargo-key="<?php echo wc_clean($key); ?>">
                <div class="row">
                    <div class="col-2 ">
                        <img class="item-img" src="<?php echo esc_url_raw($value['img']); ?>" alt="item1" />
                    </div>
                    <div class="col-8 ">
                        <span class="item-name"><a
                                href="<?php echo esc_url_raw($value['url']); ?>"><?php echo wc_clean($value['title']); ?></a></span>
                    </div>
                    <div class="col-2">
                        <button type="button" class="btn btn-sm btn-danger aadp-cargo-remove">x</button>
                    </div>

                </div>
            </li>
            <?php
}
} else {
    ?>
            <li class="aadp-empty-cargo"><h6>Your cargo is currently empty.</h6></li>
            <?php
}

?>
        </ul>
        <div class="aadp_loader">
            <div id="myItem1"></div>
        </div>
        <form id="import-and-generate-shortcode">
            <div class='row'>
                <div class='col-6 category_type'>
                    <h6 class="aadp_title">Category:</h6>
                    <div class="">
                        <input class="form-check-input aadp-product-comparisons" type="radio" name="aadp-category-type"
                            id="aadp-existing-category" checked value="x-cat">
                        <label class="form-check-label aadp_lebel" for="#aadp-existing-category">
                            Existing Category
                        </label>
                    </div>
                    <?php
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
    ?>
                    <div class="form-group" id="aadp-xc">
                        <select class="form-select form-select-sm dropdown" id="aadp-category"
                            aria-label=".form-select-sm example">
                            <option class="dropdown-menu" selected>Select One</option>
                            <?php
foreach ($categories as $category) {
        if ("uncategorized" == $category->slug) {
            $selected = 'selected';
        } else {
            $selected = '';
        }

        ?>
                            <option value="<?php esc_attr_e( $category->term_taxonomy_id, 'aadp') ?>" <?php esc_attr_e( $selected, 'aadp' )?>>
                                <?php esc_attr_e( $category->name,'aadp') ?>
                            </option>
                            <?php
}

    ?>
                        </select>
                    </div>
                    <?php
}

?></div>
<div class='col-6 category_types'>
                    <div class="button-fix">
                        <input class="form-check-input aadp-product-comparisons" type="radio" name="aadp-category-type"
                            id="create-new-category" value="n-cat">
                        <label class="form-check-label aadp_lebel" for="create-new-category">
                            Create New Category
                        </label>
                    </div>
                </div> </div>
                <div class="offset-4 col-6"><button class="btn btn-block btn-lg btn-primary button-fix-1"
                        id="importShortCode">Import</button></div>
            </div>
        </form>

    </div>
    <!--end shopping-cart -->
</div>
<!--end container -->