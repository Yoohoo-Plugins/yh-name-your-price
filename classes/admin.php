<?php
/**
 * Class for all admin settings.
 * @since 1.0.0
 */
class YH_Name_Your_Price_Admin {

    // Istantiate the class and all required hooks.
    public function __construct() {
        // Product Meta
        add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'woocommerce_product_write_panel_tabs' ) );
        add_action( 'woocommerce_product_data_panels', array( $this, 'woocommerce_product_data_panels' ) );

        // JS for admin
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    }

    /**
     * Add JavaScript and CSS files required for admin.
     * @since 1.0.0
     */
    public function admin_enqueue_scripts() {
        wp_register_style( 'yh-nyp-admin', plugins_url( '/assets/css/admin.css', __DIR__ ), array(), null );
        wp_enqueue_style( 'yh-nyp-admin' );
        wp_enqueue_script( 'yh-nyp-admin', plugins_url( '/assets/js/admin.js', __DIR__ ), array( 'jquery' ), YH_NYP_VERSION );
    }
    /**
     * Add product meta option label that links to settings page.
     * @since 1.0.0
     */
    public function woocommerce_product_write_panel_tabs() {
    ?>
        <li class="yh_name_your_price"><a href="#yh_name_your_price_data"><span><?php esc_html_e( 'Name Your Price', 'yh-name-your-price' ); ?></span></a></li>
	<?php
    }

    /**
     * Add product settings options page within product meta.
     * @since 1.0.0
     */
    public function woocommerce_product_data_panels() {

        // Get Settings
        ?>
            <div id="yh_name_your_price_data" class="panel woocommerce_options_panel">
                <?php
                // Checkbox for Name Your Price
                woocommerce_wp_checkbox(
                    array(
                        'id' => '_yh_is_nyp_product',
                        'label' => 'Enable Name Your Price',
                        'description' => 'Select this option to enable name your price functionality'
                    )
                );
                ?>
                <div class='yh-show-if'>
                <?php
                // Minimum Value
                woocommerce_wp_text_input(
                    array(
                        'id' => '_yh_min_value',
                        'label' => 'Minimum Amount',
                        'data_type' => 'price'
                    )
                ); 

                // Maximum Value
                woocommerce_wp_text_input(
                    array(
                        'id' => '_yh_max_value',
                        'label' => 'Maximum Amount',
                        'data_type' => 'price',
                    )
                );

                // Show custom option checkbox.
                woocommerce_wp_checkbox(
                    array(
                        'id' => '_yh_allow_custom_input',
                        'label' => 'Custom amounts',
                        'description' => 'Allow customers to enter their own amounts for the product.'
                    )
                    );
                ?>
                </div>
            </div>
        <?php
    }
} //end of class

new YH_Name_Your_Price_Admin();