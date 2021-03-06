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
        add_action( 'woocommerce_process_product_meta', array( $this, 'woocommerce_process_product_meta' ), 50 );

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
    <style>
        #woocommerce-coupon-data ul.wc-tabs li.custom_tab a::before,
        #woocommerce-product-data ul.wc-tabs li.custom_tab a::before,
        .woocommerce ul.wc-tabs li.custom_tab a::before {
            font-family: Dashicons;
            content: "gg";
        }
    </style>
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
                        'label' => __( 'Enable Name Your Price', 'yh-name-your-price' ),
                        'description' => __( 'Select this option to enable name your price functionality', 'yh-name-your-price' ),
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
                        'label' => __( 'Name Your Price Label', 'yh-name-your-price' ),
                        'placeholder' => __( 'Enter Amount', 'yh-name-your-price' ),
                        'description' => __( 'Write a custom label for the frontend.', 'yh-name-your-price' ),
                        'class' => 'yh-med-input'
                    )
                );
                // Minimum Value
                woocommerce_wp_text_input(
                    array(
                        'id' => '_yh_min_value',
                        'label' => __( 'Minimum Amount', 'yh-name-your-price' ),
                        'data_type' => 'price',
                        'description' => __( 'Set the minimum value a customer may enter. Set it to 0 to make free purchases allowed.', 'yh-name-your-price' ),
                        'class' => 'yh-small-input'
                    )
                ); 

                // Maximum Value
                woocommerce_wp_text_input(
                    array(
                        'id' => '_yh_max_value',
                        'label' => __( 'Maximum Amount', 'yh-name-your-price' ),
                        'data_type' => 'price',
                        'description' => __( 'Set the maximum value a customer may enter. Set it to 0 or leave it blank to allow any amount.', 'yh-name-your-price' ),
                        'class' => 'yh-small-input'
                    )
                );

                // Predefined values
                woocommerce_wp_textarea_input(
                    array(
                        'id' => '_yh_set_value',
                        'label' => __( 'Set Values', 'yh-name-your-price' ),
                        'data_type' => 'price',
                        // 'description' => __( 'Set the maximum value a customer may enter. Set it to 0 or leave it blank to allow any amount.', 'yh-name-your-price' ),
                        // 'class' => '',
                        'placeholder' => __( 'Enter predefined price options separated by "|" ( i.e. 1|5|10 )' )
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
        
        //Check post meta now
        if ( get_post_meta( $post_id, '_yh_is_nyp_product', true ) ) {
            $is_nyp = get_post_meta( $post_id, '_yh_is_nyp_product', true );
        }

        // Let's not try to update things if it's a Name Your Price product.  
        if ( ! $is_nyp ) {
            return;
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

        // Update the set value
        if ( isset( $_POST['_yh_set_value'] ) ) {
            $set_value = sanitize_text_field( $_POST['_yh_set_value'] );
            update_post_meta( $post_id, '_yh_set_value', $set_value );
        }

        if ( empty( $_POST['_regular_price'] ) || get_post_meta( $post_id, '_regular_price', true ) == '' ) {
            if ( ! empty( $max_value ) ) {
                update_post_meta( $post_id, '_regular_price', $max_value );
                update_post_meta( $post_id, '_price', $max_value );
            } elseif ( ! empty( $min_value ) ) {
                update_post_meta( $post_id, '_regular_price', $max_value );
                update_post_meta( $post_id, '_price', $max_value );
            } else {
                update_post_meta( $post_id, '_regular_price', '999' ); //Default value.
                update_post_meta( $post_id, '_price', '999' ); //Default value.
            }
        }

    }

} //end of class

new YH_Name_Your_Price_Admin();