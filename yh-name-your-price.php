<?php
/**
 * Plugin Name:       Name Your Price
 * Plugin URI:        https://yoohooplugins.com/plugins/name-your-price/
 * Description:       Allow customer's to enter their own amount on WooCommerce products to help increase your sales.
 * Version:           1.2
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            Yoohoo Plugins
 * Author URI:        https://yoohooplugins.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       yh-name-your-price
 * Domain Path:       /languages
 * 
 * WC tested up to: 7.5.1
 */

defined( 'ABSPATH' ) || die( 'File cannot be accessed directly' );

// Constants
define( 'YH_NYP_VERSION', '1.2' );
define( 'YH_NYP_DIR', dirname( __FILE__ ) );
define( 'YH_NYP_BASENAME', plugin_basename( __FILE__ ) );

//Template path for custom Woo Templates when needed.
define( 'YH_NYP_TEMPLATE_PATH', YH_NYP_DIR . '/templates/' );

// Includes
require_once YH_NYP_DIR . '/classes/admin.php';
require_once YH_NYP_DIR . '/classes/class-frontend.php';

class YH_Name_Your_Price {
    
    public function __construct() {
        add_action( 'init', array( $this, 'hooks' ), 1 );

        add_action( 'before_woocommerce_init', function() {
            if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'product_block_editor', __FILE__, true );
            }
        } );

    }

    /**
     * Initialize all hooks here.
     * @since 1.0.0
     */
    public function hooks() {
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
    }

    /**
     * Allow localization for strings.
     * @since 1.0.0
     */
    public function load_plugin_textdomain(){
        load_plugin_textdomain( 'yh-name-your-price', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
    }

    /**
     * Function to check if WooCommerce is active or not.
     * @since 1.0.0
     */
    public static function is_woo_active() {
        if ( class_exists( 'WooCommerce' ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Helper function to determine if a product is a Name Your Price product or not.
     * @since 1.0.0
     * @param int $product_id The product or post ID.
     * @return boolean
     */
    public static function is_nyp_product( $product_id = null ) {
        if ( empty( $product_id ) ) {
            $is_nyp = false;
        }

        $product_is_nyp = get_post_meta( $product_id, '_yh_is_nyp_product', true );

        if ( $product_is_nyp ) {
            $is_nyp = true;
        } else {
            $is_nyp = false;
        }

        return $is_nyp;
    }

    /**
     * Get the Product ID of a product.
     * @since 1.0.0
     * @param object The WC product object.
     * @return int The Product ID of the parent or simple product.
     */
    public static function get_product_id( $product ) {
       
        if ( ! $product ) {
            return $product;
        }
        $product_id = $product->get_parent_id() ? $product->get_parent_id() :$product->get_id();
        return $product_id;
    }

} // end of class.

new YH_Name_Your_Price();
