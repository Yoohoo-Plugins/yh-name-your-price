<?php
/**
 * Class for all admin settings.
 * @since 1.0.0
 */
use Automattic\WooCommerce\Admin\BlockTemplates\BlockInterface;

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

        // Settings for admin area and global settings
        add_filter( 'woocommerce_get_sections_products', array( $this, 'name_your_price_sections_products' ), 99 );
        add_filter( 'woocommerce_get_settings_products', array( $this, 'name_your_price_all_settings' ), 10, 2 );
        add_action( 'woocommerce_update_options_products_yh_nyp', array( $this, 'name_your_price_validate_license_save' ) );

        // Settings for Product Builder
        add_action( 'woocommerce_block_template_area_product-form_after_add_block_general', array( $this, 'product_builder_name_your_price_group' ) );
        add_action( 'woocommerce_layout_template_after_instantiation', array( $this, 'add_product_builder_settings' ), 10, 3 );

        add_action( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

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
                        'placeholder' => esc_html__( 'Enter predefined price options separated by "|" ( i.e. 1|5|10 )' )
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

    // ---- Admin global settings functions go below ---- // 

    /**
     * Add settings section for Name Your Price global settings.
     * 
     * @since 1.1
     */
    public function name_your_price_sections_products( $sections ) {
        $sections['yh_nyp'] = esc_html__( 'Name Your Price', 'name-your-price' );
	    return $sections;
    }

    /**
     * Show options for the Name Your Price product settings
     * 
     * @since 1.1
     */
    public function name_your_price_all_settings( $settings, $current_section ) {
        /**
         * Check the current section is what we want
         **/
        if ( $current_section == 'yh_nyp' ) {
            $settings = array();
            // Add Title to the Settings
            $settings[] = array( 
                'name' => esc_html__( 'Name Your Price Settings', 'name-your-price' ), 
                'type' => 'title', 
                'id' => 'yh_nyp_settings_title' 
            );
            
            $license_status = get_option( 'yh_nyp_license_status' );

            if ( ! $this::is_license_expired() ) {
                $license_desc = sprintf( __( 'Congratulations, your license key is active. <strong>Expires: %s</strong>. <br>To deactivate your license key, please remove it from the field and visit %s.', 'name-your-price' ), $license_status['expires'], '<a href="https://yoohooplugins.com/account/license-keys/" target="_blank">Yoohoo Plugins</a>' );
            } else {
                $license_desc = esc_html__( 'The license key is invalid. Please purchase/renew your license key or try again.', 'name-your-price' );
            }

            // Add second text field option
            $settings[] = array(
                'name'     => esc_html__( 'License Key', 'name-your-price' ),
                'id'       => 'yh_nyp_license_key',
                'desc_tip' => esc_html__( 'Enter your license key here to activate your product.', 'name-your-price' ),
                'type'     => 'password',
                'desc'     => $license_desc,
            );
            
            $settings[] = array( 'type' => 'sectionend', 'id' => 'yh_nyp' );
        
        }

        return $settings;
    }

    public function name_your_price_validate_license_save() {
        $license_key = sanitize_text_field( $_REQUEST['yh_nyp_license_key'] );

        // Activate or deactivate the license key on saving here.
        // data to send in our API request
        $api_params = array(
            'edd_action' => 'activate_license',
            'license'    => trim( $license_key ),
            'item_id'    => YH_NYP_PLUGIN_ID, // The ID of the item in EDD
            'url'        => home_url()
        );

        // Call the custom API.
        $response = wp_remote_post( YOOHOO_STORE, array( 'timeout' => 15, 'sslverify' => true, 'body' => $api_params ) );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );
        }

        if ( ! empty( $message ) ) {
            echo $message;
        }

        $license_data = json_decode( wp_remote_retrieve_body( $response ) );

        $license_status = array();
        $license_status['license'] = $license_data->license;
        $license_status['expires'] = $license_data->expires;

        // Save license status to database.
        update_option( 'yh_nyp_license_status', $license_status );
    }

    /**
     * Check if the license key has expired or not.
     *
     * @return boolean $r True if license is expired, false if not.
     */
    public static function is_license_expired() {

        $license_status = get_option( 'yh_nyp_license_status' );
        $expiry_date = $license_status['expires'];

        // If their license is never expiring, return false.
        if ( $expiry_date == 'lifetime' ) {
            return false;
        }

	    $today = date( 'Y-m-d H:i:s' );
 
        if ( $expiry_date < $today ) {
            $r = true;
        } else {
            $r = false;
        }

        return $r;

    }

    // ---- New functions for the Product Builder ---- //

    /**
     * Add support for the new product builder.
     * Add a new group to products.
     */
    public function product_builder_name_your_price_group( BlockInterface $general_group ) {
        $parent = $general_group->get_parent();
      
        $parent->add_group(
          [
            'id'         => 'my_pmpro-group',
            'order'      => $general_group->get_order() + 999,
            'attributes' => [
              'title' => __( 'Name Your Price', 'yh-name-your-price' ),
            ],
          ]
        );
    }

    /// Add settings to Product Builder
	public function add_product_builder_settings( $layout_template_id, $layout_template_area, $layout_template ) {
	    $name_your_price = $layout_template->get_group_by_id( 'my_pmpro-group' );

        $settings_section = $name_your_price->add_section(
            array(
                'id'         => 'yh-name-your-price-section',
                'order'      => 10,
                'attributes' => array(
                    'title'       => __( 'Name Your Price Settings', 'yh-name-your-price' ),
                    // 'description' => __( 'This info will be displayed on the product page, category pages, social media, and search results.', 'yh-name-your-price' ),
                ),
            )
        );

        if ( $settings_section ) {

	        $settings_section->add_block(
                array(
        			'id'    => 'name-your-price-checkbox',
                    'blockName'  => 'woocommerce/product-checkbox-field',
	            	'order' => 10,
	            	'attributes' => array(
	            		'label' => __( 'Enable Name Your Price', 'yh-name-your-price' ),
                        'property' => 'meta_data._yh_is_nyp_product',
                        'tooltip' => __( 'Enable this option to allow customers to enter their own price.', 'yh-name-your-price' ),
                    
                    )
                )
            );

            $settings_section->add_block(
                array(
                  'id'         => 'name-your-price-label',
                  'blockName'  => 'woocommerce/product-text-field',
                  'order'      => 20,
                  'attributes' => array(
                    'label'       => __( 'Label', 'yh-name-your-price' ),
                    'property'    => 'meta_data._yh_nyp_label',
                    'placeholder' => __( 'Enter Amount', 'yh-name-your-price' ),
                    'help'        => __( 'Write a custom label for the frontend', 'yh-name-your-price'),
                  ),
                )
            );

            // // Add minimum field.
            $settings_section->add_block(
                array(
                    'id'    => 'name-your-price-min',
                    'order' => 30,
                    'blockName'  => 'woocommerce/product-pricing-field',
                    'attributes' => array(
                        'label' => __( 'Minimum Value', 'yh-name-your-price' ),
                        'property' => 'meta_data._yh_min_value',
                        'help' => __( 'Enter the minimum value for the Name Your Price field. Leave blank for no minimum.', 'yh-name-your-price' ),
                    ),
                )
            );

            // Add maximum field.
            $settings_section->add_block(
                array(
                    'id'    => 'name-your-price-max',
                    'order' => 70,
                    'blockName'  => 'woocommerce/product-pricing-field',
                    'attributes' => array(
                        'label' => __( 'Maximum Value', 'yh-name-your-price' ),
                        'property' => 'meta_data._yh_max_value',
                        'help' => __( 'Enter the maximum value for the Name Your Price field. Leave blank for no maximum.', 'yh-name-your-price' ),
                    ),
                )
            );

            // Add textarea field now.
            $settings_section->add_block(
                array(
                    'id'    => 'name-your-price-set-value',
                    'order' => 80,
                    'blockName'  => 'woocommerce/product-text-area-field',
                    'attributes' => array(
                        'label' => __( 'Set Value', 'yh-name-your-price' ),
                        'property' => 'meta_data._yh_set_value',
                        'help'     => __( 'Enter predefined price options separated by "|" ( i.e. 1|5|10 ). This will override any min or max value.', 'yh-name-your-price' ),
                    ),
                )
            );
        }
	}

    // Add license notification to plugin meta.
    public function plugin_row_meta( $links, $file ) {
        if ( strpos( $file, 'yh-name-your-price.php' ) !== false ) {

            // Get the license status and change color etc.
            $license_status = get_option( 'yh_nyp_license_status' );

            $is_active = false;
            if ( ! $this::is_license_expired() ) {
                $is_active = true;
            }

            $css = 'color:red;';
            if ( $is_active ) {
                $css = 'color:green;';
            }

            $row_meta = array(
                'license' => '<a style="' . esc_attr( $css ) . '" href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=yh_nyp' ) . '">' . esc_html__( 'License Settings', 'yh-name-your-price' ) . '</a>',
            );
            return array_merge( $links, $row_meta );
        }
        return (array) $links;
    }

} //end of class

new YH_Name_Your_Price_Admin();
