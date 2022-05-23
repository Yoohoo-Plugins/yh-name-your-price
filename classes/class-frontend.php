<?php

class YH_Name_Your_Price_Frontend {

	/**
	 * __construct function - initialize all hooks needed for frontend.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'yh_nyp_load_custom_single_product_template' ) );
		add_filter( 'woocommerce_get_price_html', array( $this, 'hide_default_price_html' ), 20, 2 );

		// Cart Filters
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'yh_nyp_add_cart_item_data' ), 20, 3 );
		add_filter( 'woocommerce_add_cart_item', array( $this, 'yh_nyp_add_cart_item' ), 20, 1 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'yh_nyp_add_cart_validation' ), 20, 4 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 20, 2 );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'loop_add_to_cart_link' ), 20, 2 );

	}

	/**
	 * Load custom product for input fields.
	 *
	 * @since 1.0.0
	 */
	public function yh_nyp_load_custom_single_product_template() {
		global $product;

		$product_id   = YH_Name_Your_Price::get_product_id( $product );
		$args         = array( 'product_id' => $product_id );
		$args['args'] = $args;

		// Load custom template if product is a Name Your Price Product
		if ( YH_Name_Your_Price::is_nyp_product( $product_id ) ) {
			wc_get_template( 'name-your-price-single-product.php', $args, YH_NYP_TEMPLATE_PATH, YH_NYP_TEMPLATE_PATH );
		}
	}

	/**
	 * Hide the default price for Name Your Price products.
	 *
	 * @since 1.0.0
	 */
	public function hide_default_price_html( $price, $product ) {

		$product_id = YH_Name_Your_Price::get_product_id( $product );

		// Hide the Price for now.
		if ( YH_Name_Your_Price::is_nyp_product( $product_id ) ) {

			$set_values = get_post_meta( $product_id, '_yh_set_value', true ) ?: '';

			// If no set values, allow min/max values.
			if ( empty( $set_values ) ) {
				$min_value = get_post_meta( $product_id, '_yh_min_value', true ) ?: 0;
				$max_value = get_post_meta( $product_id, '_yh_max_value', true ) ?: 0;

				if ( empty( $product->get_regular_price() ) ) {
					$product->set_regular_price( $max_value );
				}

				// If both are empty, let's set default text here.
				if ( empty( $min_value ) && empty( $max_value ) ) {
					$price = 'Enter any amount.';
				} elseif ( empty( $min_value ) && ! empty( $max_value ) ) {
					$price = 'Enter an amount less than ' . wc_price( $max_value );
				} elseif ( ! empty( $min_value ) && empty( $max_value ) ) {
					$price = 'Enter an amount greater than ' . wc_price( $min_value );
				} elseif ( ! empty( $min_value ) && ! empty( $max_value ) ) {
					$price = __( sprintf( 'Enter an amount between %s and %s', wc_price( $min_value ), wc_price( $max_value ) ), 'yh-name-your-price' );
				}
			} else {
				$price = 'Choose an amount';
			}

			$price = apply_filters( 'yh_nyp_price_text', $price );
		}

		return $price;

	}


	/**
	 * Set the cart item data.
	 *
	 * @since 1.0.0
	 * @param array $cart_item_data The cart item.
	 * @return array
	 */
	public function yh_nyp_add_cart_item( $cart_item_data ) {

		$product_id = ! empty( $cart_item_data['variation_id'] ) ? $cart_item_data['variation_id'] : $cart_item_data['product_id'];

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
	 *
	 * @since 1.0.0
	 */
	public function yh_nyp_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {

		if ( isset( $_REQUEST['yh_nyp_amount'] ) ) {

			if ( $variation_id ) {
				$product_id = $variation_id;
			}

			$cart_item_data['yh_nyp_amount'] = (float) wp_unslash( $_REQUEST['yh_nyp_amount'] );
			$cart_item_data                  = apply_filters( 'ywcnp_add_cart_item_data', $cart_item_data, $product_id );

		}

		return $cart_item_data;
	}

	/**
	 * Check if this add to cart action is valid
	 *
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

		// Get Min and Max Amounts for a product.
		$min_value = get_post_meta( $product_id, '_yh_min_value', true ) ?: 0;
		$max_value = get_post_meta( $product_id, '_yh_max_value', true ) ?: 0;

		$set_values = explode( '|', get_post_meta( $product_id, '_yh_set_value', true ) ) ?: 0;

		if ( ! isset( $_REQUEST['yh_nyp_amount'] ) || empty( $_REQUEST['yh_nyp_amount'] ) ) {
			$amount = 0;
		} else {
			$amount = wp_unslash( $_REQUEST['yh_nyp_amount'] );
		}

		$amount = apply_filters( 'yh_nyp_get_price', floatval( $amount ) );

		if ( ! empty( $set_values ) ) {
            // Loop through each option, make sure a value matches. If it doesn't, throw an error
            $price_match = false;
            foreach ( $set_values as $key => $value ) { 
                // Amount is okay, we're just gonna break and continue.
                if ( (float) $value == $amount ) {
                    $price_match = true;
                }
            }

            // Price isn't okay, bail.
            if ( ! $price_match ) {
                $error_message = __( 'Amount has been modified.', 'yh-name-your-price' );
                $passed = false;
            }
		}

		if ( ! is_numeric( $amount ) ) {
			$error_message = __( 'It seems you have entered an invalid price, please can you try again with numeric values only.', 'yh-name-your-price' );
			$passed        = false;
		}

		if ( $amount < 0 ) {
			$error_message = __( 'Only values that are 0 or above is allowed. Please try again.', 'yh-name-your-price' );
			$passed        = false;
		}

		if ( ! empty( $min_value ) ) {
			if ( $amount < $min_value ) {
				$error_message = __( 'Please enter a value higher than ' . wc_price( $min_value ), 'yh-name-your-price' );
				$passed        = false;
			}
		}

		if ( ! empty( $max_value ) ) {
			if ( $amount > $max_value ) {
				$error_message = __( 'Please enter a value less than ', wc_price( $max_value ), 'yh-name-your-price' );
				$passed        = false;
			}
		}

		if ( $error_message ) {
			wc_add_notice( $error_message, 'error' );
		}

			return $passed;

	}

		/**
		 * Apply the pricing from the session.
		 *
		 * @since 1.0.0
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

    /**
     * Adjust the Add To Cart Link to rather redirect to the NYP product so customer's may enter an amount.
     *
     * @since 1.0.0
     */
	public function loop_add_to_cart_link( $link, $product ) {

		$product_id = YH_Name_Your_Price::get_product_id( $product );

		if ( YH_Name_Your_Price::is_nyp_product( $product_id ) ) {
			// Let's build the button link and make it filterable.
			$link = get_permalink( $product_id );

			$link = '<a href="' . esc_url( $link ) . '" data-quantity="1" class="button product_type_simple add_to_cart_button" data-product_id="' . $product_id . '" data-product_sku="" aria-label="' . esc_html( $product->get_title() ) . '" rel="nofollow">' . apply_filters( 'yh_nyp_shop_page_product_button_text', __( 'View Product', 'yh-name-your-price' ) ) . '</a>';

		}
		return $link;
	}

} // End Class
new YH_Name_Your_Price_Frontend();
