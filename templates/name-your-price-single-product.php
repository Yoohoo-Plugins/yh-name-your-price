<?php
/**
 * Custom template for single product to add field for Name Your Price.
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die( 'File cannot be accessed directly' );

global $product;

$product_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
?>

<div id="yh_nyp_form" style="margin:10px 0px;">
	<?php do_action( 'yh_nyp_before_single_product_form' ); ?>
	<?php
	
	$label_text = apply_filters( 'yh_nyp_label_single_product', get_post_meta( $product_id, '_yh_nyp_label', true ) ?: __( 'Enter Amount', 'yh-name-your-price' ), $product_id );

	// Try to get predefined values, and see if they're empty or not.
	$get_set_values = get_post_meta( $product_id, '_yh_set_value', true );
	$set_values = ! empty( $get_set_values ) ? explode( '|', $get_set_values ) : '';
	?>
    <label for="yh_nyp_suggest_price_single"><?php echo esc_html( $label_text ); ?></label>
	<?php
	if ( ! empty( $set_values ) ) { 
		echo '<select name="yh_nyp_amount">';
		$set_values = apply_filters( 'yh__nyp_set_value_options', $set_values );
		foreach( $set_values as $key => $value ) {
			if ( is_numeric( $value ) ) {
				echo'<option value="' . esc_attr( $value ) . '">' . wc_price( $value ) . '</option>';
			}
		}
		echo '</select>';
	} else {
		$min_value = apply_filters( 'yh_nyp_min_value_allowed', get_post_meta( $product_id, '_yh_min_value', true ) ?: 0, $product_id );
		$max_value = apply_filters( 'yh_nyp_max_value_allowed', get_post_meta( $product_id, '_yh_max_value', true ) ?: 0, $product_id );
	?>
        <input type="number" id="yh_nyp_suggest_price_single" class="yh_nyp_sugg_price short" name="yh_nyp_amount" <?php if ($min_value != 0){ echo 'min="' . $min_value . '"'; } if ($max_value != 0){ echo 'max="' . $max_value . '"'; }?> />
		<br/><br/>
	<?php 
	} //endif
	do_action( 'yh_nyp_after_single_product_form' ); 
	?>
</div>