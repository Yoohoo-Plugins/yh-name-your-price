<?php

class YH_Name_Your_Price_Frontend {
    
    /**
     * __construct function - initialize all hooks needed for frontend.
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'yh_nyp_load_custom_single_product_template' ) );
        add_filter( 'woocommerce_get_price_html', array( $this, 'hide_default_price_html' ), 20, 2 );

        //Cart Filters
        add_filter( 'woocommerce_add_cart_item_data', array( $this, 'yh_nyp_add_cart_item_data' ), 20, 3 );
        add_filter( 'woocommerce_add_cart_item', array( $this, 'yh_nyp_add_cart_item' ), 20, 1 );
        add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'yh_nyp_add_cart_validation' ), 20, 4 );
        add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 20, 2 );


    }

    /**
     * Load custom product for input fields.
     * @since 1.0.0
     */
    public function yh_nyp_load_custom_single_product_template() {
        global $product;

        $product_id = YH_Name_Your_Price::get_product_id( $product );
        $args = array( 'product_id' => $product_id );
		$args['args'] = $args;
        
        // Load custom template if product is a Name Your Price Product
        if ( YH_Name_Your_Price::is_nyp_product( $product_id ) ) {
            wc_get_template( 'name-your-price-single-product.php', $args, YH_NYP_TEMPLATE_PATH, YH_NYP_TEMPLATE_PATH );
        }
    }

    /**
     * Hide the default price for Name Your Price products.
     * @since 1.0.0
     */
    public function hide_default_price_html( $price, $product ) {
    // hide the price for now.
    $product_id = YH_Name_Your_Price::get_product_id( $product );
    
    // Hide the Price for now.
    if ( YH_Name_Your_Price::is_nyp_product( $product_id ) ) {
        $price = '';
    }

    return $price;

    }


    /**
     * Set the cart item data.
     * @since 1.0.0
     * @param array $cart_item_data The cart item.
     * @return array
     */
    public function yh_nyp_add_cart_item( $cart_item_data ) {

    $product_id = $cart_item_data['variation_id'] ? $cart_item_data['variation_id'] : $cart_item_data['product_id'];

    // Check to see if the product is a Name Your Price product, if so let's figure out how much to set it to.
    if ( YH_Name_Your_Price::is_nyp_product( $product_id ) ) {

        $product = wc_get_product( $product_id );

        if ( isset( $cart_item_data['yh_nyp_amount'] ) ) {

            $product = $cart_item_data['data'];
            $product->set_price( $cart_item_data['yh_nyp_amount'] );

        }

    }

    return $cart_item_data;

    }

    /**
     * Store in cart item data the information about name your price.
     * @since 1.0.0
     */
    public function yh_nyp_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {

        if ( isset( $_REQUEST['yh_nyp_amount'] ) ) { 
            
            if ( $variation_id ) {
                $product_id = $variation_id;
            }

            $cart_item_data['yh_nyp_amount'] = (float) wp_unslash( $_REQUEST['yh_nyp_amount'] );
            $cart_item_data = apply_filters( 'ywcnp_add_cart_item_data', $cart_item_data, $product_id );

        }

        return $cart_item_data;
    }

    /**
		 * Check if this add to cart action is valid
		 * @since 1.0.0
		 * @param bool   $passed Temporary pass check.
		 * @param int    $product_id The product id.
		 * @param int    $quantity The quantity.
		 * @param string $variation_id The variation id.
		 *
		 * @return bool
		 */
		public function yh_nyp_add_cart_validation( $passed, $product_id, $quantity, $variation_id = '' ) {

            $error_message = '';

			if ( $variation_id ) {
				$product_id = $variation_id;
			}

			if ( ! YH_Name_Your_Price::is_nyp_product( $product_id ) ) {
				return $passed;
			}

			if ( ! isset( $_REQUEST['yh_nyp_amount'] ) || empty( $_REQUEST['yh_nyp_amount'] ) ) {
				$amount = 0;
			} else {
				$amount = wp_unslash( $_REQUEST['yh_nyp_amount'] );
			}

			$amount = apply_filters( 'yh_nyp_get_price', floatval( $amount ) );

			if ( ! is_numeric( $amount ) ) {
				$error_message = 'invalid_price';
				$passed        = false;
			}

			if ( $amount < 0 ) {
				$error_message = 'negative_price';
				$passed        = false;
			}

			if ( $error_message ) {
				wc_add_notice( $error_message, 'error' );
			}

			return $passed;

		}

        /**
         * Apply the pricing from the session.
         */
        public function get_cart_item_from_session( $cart_item, $values ) {
			if ( isset( $values['yh_nyp_amount'] ) ) {
				$cart_item['yh_nyp_amount'] = apply_filters(
					'yh_nyp_session_cart_item_amount',
					$values['yh_nyp_amount'],
					$cart_item,
					$values
				);

				$cart_item = $this->yh_nyp_add_cart_item( $cart_item );
			}

			return $cart_item;
		}

} // End Class
new YH_Name_Your_Price_Frontend();