<?php
/**
 * Plugin Name: Name Your Price
 * Description: Allow customer's to enter their own amount on products to help increase your sales.
 * Version: 1.0.0
 * Author: Yoohoo Plugins
 * Author URI: https://yoohooplugins.com
 */

defined( 'ABSPATH' ) || die( 'File cannot be accessed directly' );

// Constants
define( 'YH_NYP_VERSION', '1.0.0' );
define( 'YH_NYP_DIR', dirname( __FILE__ ) );
define( 'YH_NYP_BASENAME', plugin_basename( __FILE__ ) );

// Includes
require_once YH_NYP_DIR . '/classes/admin.php';

class YH_Name_Your_Price {
    
    public function __construct() {
        add_action( 'init', array( $this, 'hooks' ), 1 );
    }

    /**
     * Initialize all hooks here.
     * @since 1.0.0
     */
    public function hooks() {
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

} // end of class.

new YH_Name_Your_Price();
