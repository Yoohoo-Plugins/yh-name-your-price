jQuery(document).ready(function(){
    jQuery( '#_yh_is_nyp_product' ).on('click', function(){
        if ( jQuery(this) .prop( 'checked' ) ){
            jQuery( '.yh-show-if' ).fadeIn();
        } else {
            jQuery( '.yh-show-if' ).fadeOut();
        }
    });
});