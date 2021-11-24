<?php
/**
 * Class for all admin settings.
 * @since 1.0.0
 */
class YH_Name_Your_Price_Admin {

    /**
     * __construct function - initialize all hooks needed for admin.
     * @since 1.0.0
     */
    public function __construct() {
       
        // Product meta setup and saving.
        add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'woocommerce_product_write_panel_tabs' ) );
        add_action( 'woocommerce_product_data_panels', array( $this, 'woocommerce_product_data_panels' ) );
        add_action( 'woocommerce_process_product_meta', array( $this, 'woocommerce_process_product_meta' ) );

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
        global $post;

        // Get Settings
        if ( ! empty( $post->ID ) ) {
            $cbvalue = get_post_meta( $post->ID, '_yh_is_nyp_product', true );
        }

        if ( empty( $cbvalue ) ) {
            $cbvalue = NULL;
        }

        ?>
            <div id="yh_name_your_price_data" class="panel woocommerce_options_panel">
                <?php
                // Checkbox for Name Your Price
                woocommerce_wp_checkbox(
                    array(
                        'id' => '_yh_is_nyp_product',
                        'label' => 'Enable Name Your Price',
                        'description' => 'Select this option to enable name your price functionality',
                        'cbvalue' => $cbvalue
                    )
                );
                ?>
                <div class='yh-show-if'>
                <?php
                // Allow user's to change the label of Name Your Price on product.
                woocommerce_wp_text_input(
                    array(
                        'id' => '_yh_nyp_label',
                        'label' => 'Name Your Price Label',
                        'placeholder' => 'Name Your Price',
                        'description' => 'Write a custom label on the frontend.',
                        'class' => 'yh-med-input'
                    )
                );
                // Minimum Value
                woocommerce_wp_text_input(
                    array(
                        'id' => '_yh_min_value',
                        'label' => 'Minimum Amount',
                        'data_type' => 'price',
                        'description' => 'Set the minimum value a customer may enter. Set it to 0 to make free purchases allowed.',
                        'class' => 'yh-small-input'
                    )
                ); 

                // Maximum Value
                woocommerce_wp_text_input(
                    array(
                        'id' => '_yh_max_value',
                        'label' => 'Maximum Amount',
                        'data_type' => 'price',
                        'description' => 'Set the maximum value a customer may enter. Set it to 0 or leave it blank to allow any amount.',
                        'class' => 'yh-small-input'
                    )
                );
                ?>
                </div>
            </div>
        <?php
    }

    /**
     * Save product settings here.
     * @since 1.0.0
     */
    public function woocommerce_process_product_meta() {
        global $post_id;

    if ( isset( $_POST['_yh_is_nyp_product'] ) 
		&& !empty( $_POST['_yh_is_nyp_product'] )
		&& $_POST['_yh_is_nyp_product'] !== 'no' ) {
		$is_nyp = 1;
	} else {
		$is_nyp = 0;
	}

    if ( isset ( $is_nyp ) ) {
		update_post_meta( $post_id, '_yh_is_nyp_product', $is_nyp );
	}

    // Update label value.
    if ( isset( $_POST['_yh_nyp_label'] ) ) {
        $label = sanitize_text_field( $_POST['_yh_nyp_label'] );
        update_post_meta( $post_id, '_yh_nyp_label', $label );
    }

    // Update min value
    if ( isset( $_POST['_yh_min_value'] ) ) {
        $min_value = (float) $_POST['_yh_min_value'];
        update_post_meta( $post_id, '_yh_min_value', $min_value );
    }

    // Update max value
    if ( isset( $_POST['_yh_max_value'] ) ) {
        $max_value = (float) $_POST['_yh_max_value'];
        update_post_meta( $post_id, '_yh_max_value', $max_value );
    }

    }

} //end of class

new YH_Name_Your_Price_Admin();