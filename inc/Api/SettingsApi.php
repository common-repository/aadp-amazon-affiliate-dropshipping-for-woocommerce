<?php

/**
 * @package aadp
 *  * @version 0.0.8
 */

namespace Aadp\Api;

class SettingsApi
{
    public $admin_pages    = []; //hints: 01
    public $admin_subpages = []; //hints: 02...
    public $settings       = []; //03>01>01
    public $base           = '';

    public function register()
    {
        $this->base = 'wp-amazon';

        if (!empty($this->admin_pages) || !empty($this->admin_subpages)) {
            //method callback addAdminMenu() 01...
            add_action('admin_menu', [$this, 'addAdminMenu']);
        }
        /*Change menu-order*/
        // if (self::aadp_check_license_activation() == 'active') {
        //     add_filter('custom_menu_order', [$this, 'aadp_submenu_order']);
        // }

        // Initialize settings

        add_action('init', [$this, 'aadp_init_settings'], 11);

        // Register plugin settings
        add_action('admin_init', [$this, 'aadp_register_settings']);

        add_action('wp_ajax_license_key_validate', [$this, 'aadp_license_verify']);

    }

    //register method called addAdminMenu() 01 ...
    public function addAdminMenu()
    {
        //$this->admin_pages is a array, thats why foreach loop called 01...
        foreach ($this->admin_pages as $page) {
            //need to load data for ['page_title'] or others arrays values from SettingsApi 01...
            add_menu_page($page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback'], $page['icon_url'], $page['position']);
        }

        //hints: 02...
        foreach ($this->admin_subpages as $page) {;

            add_submenu_page($page['parent_slug'], $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback']);
        }
    }

    //foreach $this->admin_pages as $page (above) arrays value passed by $this->admin_pages = $pages or $pages 01...
    public function addPages(array $pages)
    {
        $this->admin_pages = $pages;
        return $this;
    }

    //hints: 02...
    public function withSubPage(string $title = null)
    {
        if (empty($this->admin_pages)) {
            return $this;
        }

        $admin_page = $this->admin_pages[0];
        $subpage    = [
            [
                'parent_slug' => $admin_page['menu_slug'],
                'page_title'  => $admin_page['page_title'],
                'menu_title'  => ($title) ? $title : $admin_page['menu_title'],
                'capability'  => $admin_page['capability'],
                'menu_slug'   => $admin_page['menu_slug'],
                'callback'    => $admin_page['callback'],
                // 'position' => 1

            ],
        ];
        $this->admin_subpages = $subpage;
        return $this;
    }

    //hints: 02...
    public function addSubPages(array $pages)
    {
        $this->admin_subpages = array_merge($this->admin_subpages, $pages);
        return $this;
    }
    public static function store_categories()
    {
        if (class_exists('WooCommerce')) {
            $terms = get_terms(
                ['taxonomy' => 'product_cat', 'hide_empty' => false]
            );

            $categories = [];
            if (count($terms) > 0) {
                foreach ($terms as $term) {
                    $categories[$term->term_id] = $term->name;
                }
            }
            return ['options' => $categories, 'default' => $terms[0]->term_id];
        } else {
            return ['options' => [], 'default' => 0];
        }
    }

    public function aadp_submenu_order($menu_ord)
    {
        global $submenu;
        // Enable the next line to see all menu orders
        // echo '<pre>'.print_r($submenu,true).'</pre>';
        // var_dump($submenu["wp_amazon"]);
        // exit;

        $arr   = [];
        $arr[] = $submenu["wp_amazon"][1]; //my original order was 5,10,15,16,17,18
        $arr[] = $submenu["wp_amazon"][0];
        $arr[] = $submenu["wp_amazon"][2];
        $arr[] = $submenu["wp_amazon"][3];
        $arr[] = $submenu["wp_amazon"][4];
        $arr[] = $submenu["wp_amazon"][5];
        // $arr[]                = $submenu["wp_amazon"][5];
        $submenu["wp_amazon"] = $arr;

        return $menu_ord;
    }
    public function settings_section($section)
    {
        $html = $this->settings[$section['id']]['description'];
        esc_html_e($html, 'aadp');
    }
    public function aadp_register_settings()
    {
        if (is_array($this->settings)) {
            // Check posted/selected tab
            $current_section = '';
            if (isset($_POST['tab']) && $_POST['tab']) {
                $current_section = sanitize_text_field($_POST['tab']);
            } else {
                if (isset($_GET['tab']) && $_GET['tab']) {
                    $current_section = sanitize_text_field($_GET['tab']);
                }
            }

            foreach ($this->settings as $section => $data) {
                if ($current_section && $current_section != $section) {
                    continue;
                }
                // Add section to page
                add_settings_section($section, $data['title'], [$this, 'settings_section'], 'aadp_settings');
                foreach ($data['fields'] as $field) {
                    // Validation callback for field
                    $validation = '';
                    if (isset($field['callback'])) {
                        $validation = $field['callback'];
                    }
                    // Register field
                    $option_name = $this->base . $field['id'];
                    $option_name = $field['id'];
                    register_setting('aadp_settings', $option_name, $validation);
                    // Add field to page

                    add_settings_field($field['id'], $field['label'], [$this, 'display_field'], 'aadp_settings', $section, ['field' => $field, 'prefix' => '']);
                }
                if (!$current_section) {
                    break;
                }
            }
        }
    }
    public function display_field($data = [], $post = false, $echo = true)
    {
        // Get field info
        if (isset($data['field'])) {
            $field = $data['field'];
        } else {
            $field = $data;
        }
        // Check for prefix on option name
        $option_name = '';
        if (isset($data['prefix'])) {
            $option_name = $data['prefix'];
        }
        // Get saved data
        $data = '';
        if ($post) {
            // Get saved field data
            $option_name .= $field['id'];
            $option = get_post_meta($post->ID, $field['id'], true);
            // Get data to display in field
            if (isset($option)) {
                $data = $option;
            }
        } else {
            // Get saved option
            $option_name .= $field['id'];
            $option = get_option($option_name);

            // Get data to display in field
            if (isset($option)) {
                $data = $option;
            }
        }

        // Show default data if no option saved and default is supplied
        if ($data === false && isset($field['default'])) {
            $data = $field['default'];
        } elseif ($data === false) {
            $data = '';
        }

        $html = '';

        switch ($field['type']) {
            case 'radio':
                foreach ($field['options'] as $k => $v) {
                    $checked = false;
                    if ($k == $data) {
                        $checked = true;
                    }
                    ?>
<p><label for="<?php esc_attr_e($field['id'] . '_' . $k, 'aadp');?>"> <input type="radio"
            <?php echo checked($checked, true, false) ?> name="<?php esc_attr_e($option_name, 'aadp')?>"
            value="<?php esc_attr_e($k, 'aadp')?>" id="<?php esc_attr_e($field['id'] . '_' . $k, 'aadp');?>" />
        <?php esc_html_e($v, 'aadp')?> </label></p>
<?php
}
                break;
            case 'select':
                ?>
<select name="<?php esc_attr_e($option_name, 'aadp')?>" id="<?php esc_attr_e($field['id'], 'aadp')?>">
    <?php
foreach ($field['options'] as $k => $v) {
                    $selected = false;
                    if ($k == $data) {
                        $selected = true;
                    }
                    ?>
    <option <?php echo selected($selected, true, false)?> value="<?php esc_attr_e($k, 'aadp')?>">
        <?php esc_attr_e($v, 'aadp')?></option>
    <?php
}
                ?>
</select>
<?php
break;
            case 'affiliate_tag':

                $amazon_stores = get_option('wp_amazon_country');
                ?>
<div class="aadp-affiliate-tags-wrapper">
    <?php
$store_counter = 0;
                foreach ($data as $single_data) {
                    ?>
    <div class="aadp-affiliate-tag-pair">
        <select name="<?php esc_attr_e($option_name);?>[<?php echo $store_counter; ?>][site]">
            <?php foreach ($amazon_stores as $country => $url) {
                        ?>
            <option <?php if (isset($single_data['site']) && $single_data['site'] == $url) {
                            echo "selected";
                        }?> value="<?php esc_attr_e($url, 'aadp')?>">
                <?php esc_html_e($country, 'aadp');?></option>
            <?php }?>
        </select>
        <input type="text" name="<?php esc_attr_e($option_name);?>[<?php esc_attr_e($store_counter, 'aadp');?>][tag]"
            value="<?php if (isset($single_data['tag'])) {
                        esc_attr_e($single_data['tag'], 'aadp');
                    }?>" placeholder="<?php _e('Associate ID', 'aadp')?>" />
        <?php if ($store_counter > 0) {?>
        <button class="button aadp-affiliate-tag-btn-remove" type="button">X</button>
        <?php }?>
    </div>
    <?php
$store_counter++;
                }
                ?>
</div>

<div class="aadp-add-affiliate-tag-more" style="margin-top: 10px">
    <button class="button button-primary" id="aadp-affiliate-tag-btn-add" type="button">+Add</button>
</div>

<?php
break;
        }
        if (!$echo) {
            return $html;
        }

        esc_html_e($html, 'aadp');
    }
    public function aadp_init_settings()
    {
        $this->settings = $this->setSettings();
    }
    public function setSettings()
    {

        $aadp_categories = self::store_categories();
        $args['general'] = [
            'title'       => __('General', 'aadp'),
            'description' => __('In this general settings you can activate product importer options & amazon hero search.', 'aadp'),
            'fields'      => [
                [
                    'id'      => 'product_importer',
                    'label'   => __('Product Importer', 'aadp'),
                    'type'    => 'radio',
                    'options' => ['enable' => 'Enabled', 'disable' => 'Disabled'],
                    'default' => 'enable',
                    'tooltip' => 'before enable the feature must need to be install woocommerce plguin',
                ],
                [
                    'id'          => 'amazon_hero_search',
                    'label'       => __('Amazon Hero Search', 'aadp'),
                    'description' => __('[aadp_search]'),
                    'type'        => 'radio',
                    'options'     => ['enable' => 'Enabled', 'disable' => 'Disabled'],
                    'default'     => 'enable',
                ],

            ],
        ];
        $args['plugin_configuration'] = [
            'title'       => __('Plugin Configuration', 'aadp'),
            'description' => __('Plugin configuration settings mainly for affiliate & dropshipping settings.', 'aadp'),
            'fields'      => [
                [
                    'id'      => 'use_for',
                    'label'   => __('Bussiness For', 'aadp'),
                    'tooltip' => __('Select a Business for which AADP Business will be used.', 'aadp'),
                    'type'    => 'radio',
                    'options' => ['affiliate' => 'Affiliate Marketing'],
                    'default' => 'affiliate',
                ],
                [
                    'id'          => 'default_store',
                    'label'       => __('Default Store', 'aadp'),
                    'description' => __('Set default store'),
                    'type'        => 'select',
                    'options'     => array_flip(get_option('wp_amazon_country')),
                    'default'     => 'https://www.amazon.com/',
                ],
                [
                    'id'      => 'import_as',
                    'label'   => __('Product Import As'),
                    'type'    => 'radio',
                    'options' => ['simple' => 'Simple Product'],
                    'default' => 'simple',
                ],

                [
                    'id'          => 'cart_import_category',
                    'label'       => __('Default Category for Add to cart', 'aadp'),
                    'description' => __('The Product will be imported to selected category (if product does not imported ) before adding to cart when user will click on add to cart button .', 'aadp'),
                    'type'        => 'select',
                    'options'     => $aadp_categories['options'],
                    'default'     => $aadp_categories['default'],
                ],

            ],
        ];
        $args['amazon_affiliate'] = [
            'title'       => __('Amazon Affiliate', 'aadp'),
            'description' => __('Amazon affiliate multi store settings page.', 'aadp'),
            'fields'      => [
                [
                    'id'          => 'amazon_associate_tag',
                    'label'       => __('Affiliate Associate Tags', 'aadp'),
                    'description' => __('', 'aadp'),
                    'type'        => 'affiliate_tag',
                    'placeholder' => __('Associate ID', 'aadp'),
                ],
                [
                    'id'          => 'buy_now_action',
                    'label'       => __('Action: Buy Now Button', 'aadp'),
                    'description' => __('Which price format will be display on the store front', 'aadp'),
                    'type'        => 'radio',
                    'options'     => ['onsite' => 'Product will add to your woo cart then if customer click for checkout then it will redirect to Amazon checkout', 'redirect' => 'Redirect Amazon Cart Page (for Affiliate)', 'details' => 'Redirect Amazon Product Details (for Affiliate)'],
                    'default'     => 'onsite',
                    'placeholder' => __('Buy Now Button Action', 'aadp'),
                ],
                [
                    'id'          => 'enable_no_follow',
                    'label'       => __('Enable No Follow to Link', 'aadp'),
                    'description' => __('If "YES" option is checked then it will add nofollow value to rel attribule of proudct anchor tag .', 'aadp'),
                    'type'        => 'radio',
                    'options'     => ['on' => 'Yes', 'off' => 'No'],
                    'default'     => 'on',
                    'placeholder' => __('No Follow Link', 'aadp'),
                ],

            ],
        ];
        return $args;

    }
}