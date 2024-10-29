<?php

/**
 * @package aadp
 *  * @version 0.0.8
 */

namespace Aadp\Pages;

use \Aadp\Api\SettingsApi;
use \Aadp\Base\BaseController;

class Dashboard extends BaseController
{

    public $settings; //variable created to store data to create SettingsApi()

    public $pages = []; //foreach arrays value passed by $this->admin_pages = $pages or $pages 01...
    public $callbacks_mngr;

    public function register()
    {
        $this->settings = new SettingsApi();
        $this->setPages(); //ref. hints: initialize - 01
        $this->settings->addPages($this->pages)->withSubPage('Settings')->register();

    }

    public function setPages()
    {
        $this->pages = [
            [
                'page_title' => 'AADP : Amazon Affiliate Affiliate WordPress Plugin for WooCommerce',
                'menu_title' => 'AADP - Amazon',
                'capability' => 'manage_options',
                'menu_slug'  => 'wp_amazon',
                'callback'   => [$this, 'page_template'],
                // 'callback'   => array($this->callbacks, 'adminDashboard'),
                'icon_url'   => 'dashicons-amazon',
                'position'   => 110,
            ],
        ];
    }

    public function page_template()
    {
        ?>
<div class="wrap" id="aadp_settings">
    <h2> <?php  __('AADP - Amazon Affiliate & Dropshipping Plugin for WooCommerce Settings', 'aadp') ?></h2>
    <div class="notice notice-success is-dismissible">
        <p class="aadp-advert"> <strong>We need your support</strong> to keep updating and improving the plugin. Please,
            help us by leaving a good review * * * * * :) Thanks!)
            <br><br> You are now using our <strong>AADP - Amazon Affiliate & Dropshipping Plugin for
                WooCommerce</strong> Free version. Try premium version to empower your business in the world and save
            your time.<br><br><a
                href="https://amazonplugins.com/amazon-affiliate-dropshipping-plugin-with-product-research-for-woocommerce/"><button
                    class="btn btn-warning btn-large">Update Premium Version</button> </a>
        </p>
    </div>
    <div class="notice notice-success is-dismissible">
        <p class="aadp-advert"><strong>Amazon Plugins recommend AADP compatible theme</strong> to empower your business
            in the world.<a href="https://amazonplugins.com/outsource-wordpress-development/"><span> </span><button
                    class="btn btn-warning btn-large">Checkout Our Affiliate Theme</button> </a>
        </p>
    </div>
    <?php
        
        $tab = '';
        if (isset($_GET['tab']) && $_GET['tab']) {
            $tab .= sanitize_text_field($_GET['tab']);
        }
        // Show page tabs
        if (is_array($this->settings->settings) && 1 < count($this->settings->settings)) {
            ?>
    <h2 class="nav-tab-wrapper">
        <?php

            $c = 0;
            foreach ($this->settings->settings as $section => $data) {

                // Set tab class
                $class = 'nav-tab';
                if (!isset($_GET['tab'])) {
                    if (0 == $c) {
                        $class .= ' nav-tab-active';
                    }
                } else {
                    if (isset($_GET['tab']) && $section == $_GET['tab']) {
                        $class .= ' nav-tab-active';
                    }
                }

                // Set tab link
                $tab_link = add_query_arg(['tab' => $section]);
                if (isset($_GET['settings-updated'])) {
                    $tab_link = remove_query_arg('settings-updated', $tab_link);
                }

                // Output tab
                ?>
        <a href="<?php echo esc_url ($tab_link, 'aadp' )?>"
            class="<?php esc_attr_e($class) ?>"><?php esc_html_e($data['title'], 'aadp') ?> </a>
        <?php
                ++$c;
            }
            ?>
    </h2>
    <?php
        }
        ?>
    <form method="post" class="form_settings" action="options.php" enctype="multipart/form-data">
        <?php
        // Get settings fields
        // ob_start();
        settings_fields('aadp_settings');
        do_settings_sections('aadp_settings');
        // $content = ob_get_clean();

        // echo $content;
?>
        <p class="submit">
            <input type="hidden" name="tab" value="<?php esc_attr_e($tab, 'aadp') ?>" />
            <input name="Submit" type="submit" class="button-primary"
                value="<?php esc_attr_e('Save Settings', 'aadp') ?>" />
        </p>
    </form>
</div>
<?php
    }
}