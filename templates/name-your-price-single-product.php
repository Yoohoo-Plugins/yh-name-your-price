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
	<?php //add an action here. ?>
	<?php
	    $label_text = get_post_meta( $product_id, '_yh_nyp_label', true );
	?>
        <label for="yh_nyp_suggest_price_single"><?php echo esc_html( $label_text ); ?></label>
        <input type="text"  id="yh_nyp_suggest_price_single" class="yh_nyp_sugg_price short"  name="yh_nyp_amount"/>
	<?php //add an action here. ?>
</div>