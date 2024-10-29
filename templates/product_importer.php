<?php

use Aadp\Base\ProductImporterController;

settings_errors();
?>


<div class="container">
    <div class="">
        <div class=" mt-4" id="aadp-gol">
            <nav>
                <div class="nav navbar-left nav-tabs nav_custom_css mt-2" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active custom_a" id="nav-keyword-tab" data-toggle="tab"
                        href="#nav-keyword" role="tab" aria-controls="nav-keyword" aria-selected="true"><strong>KEYWORD
                            SEARCH</strong></a>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active mb-2" id="nav-keyword" role="tabpanel"
                    aria-labelledby="nav-keyword-tab">
                    <div class="row ">
                        <div class="searc_form_css">
                            <nav class="navbar navbar-light ">
                                <form class="form-inline">
                                    <div class="input-group mb-4">
                                        <?php
$store_url  = get_option('wp_amazon_country');
$department = ProductImporterController::aadp_get_department();
?>
                                        <select class="form-select me-2 aadp-country-store"
                                            aria-label="Default select example">
                                            <?php foreach ($store_url as $key => $value) {
    ?>
                                            <option value="<?php echo esc_url( $value, 'aadp'); ?>" <?php if ($key == "United States") {
        echo "selected";
    }?>>
                                                <?php esc_html_e($key, 'aadp');?></option>
                                            <?php
}
?>
                                        </select>
                                        <select class="form-select me-2 aadp-department"
                                            aria-label="Default select example">
                                            <?php
$allowed_html = [
    'option' => [
        'value' => [],
    ],
];
echo wp_kses($department, $allowed_html);
?>
                                        </select>
                                        <input class="form-control aadp-keyword" type="search" size="80"
                                            placeholder="Search" aria-label="Search">
                                        <button class="btn btn-warning mr-2 my-sm-0 aadp-search"
                                            type="submit">Search</button>
                                    </div>
                                </form>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
require_once "$this->plugin_path/templates/add_to_cargo.php";
?>


        <div id="aadp-import-product"> </div>

    </div>
</div>