jQuery(document).ready(function(){
    // Show on page load if pre-saved.
    if ( jQuery( '#_yh_is_nyp_product').prop( 'checked' ) ) {
        jQuery( '.yh-show-if' ).show();
    }

    jQuery( '#_yh_is_nyp_product' ).on('click', function(){
        if ( jQuery(this) .prop( 'checked' ) ){
            jQuery( '.yh-show-if' ).fadeIn();
        } else {
            jQuery( '.yh-show-if' ).fadeOut();
        }
    });
});