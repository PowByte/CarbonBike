<?php
/**
 * Plugin Name: Manager Financiar - Soft Expert
 * Plugin URI: http://www.powbyte.com
 * Description: Import automat produse
 * Version: 1.0
 * Author: Marius D.
 * Author URI: http://www.powbyte.com
 */
if ( ! defined( 'WPINC' ) ) {
    exit; // Exit if accessed directly
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// error_reporting(0);

// Include the shared and public dependencies.
include_once( plugin_dir_path( __FILE__ ) . 'shared/class-deserializer.php' );
include_once( plugin_dir_path( __FILE__ ) . 'public/class-content-messenger.php' );

// Include the dependencies needed to instantiate the plugin.
foreach ( glob( plugin_dir_path( __FILE__ ) . 'admin/*.php' ) as $file ) {
    include_once $file;
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action( 'plugins_loaded', 'pbt_manfin_admin_settings' );
}



/**
 * Starts the plugin.
 *
 * @since 1.0.0
 */
function pbt_manfin_admin_settings() {

	// Setup and initialize the class for saving our options.
	$serializer = new Serializer();
	$serializer->init();

	// Setup the class used to retrieve our option value.
	$deserializer = new Deserializer();

	// Setup the administrative functionality.
	$admin = new Submenu( new Submenu_Page( $deserializer ) );
	$admin->init();

	// Setup the public facing functionality.
	$public = new Content_Messenger( $deserializer );
	$public->init();
	
	/*Custom Pages by Powbyte*/
	
	// Setup the administrative functionality.
	$admin = new Submenu( new Submenu_Page( $deserializer ) );
	$admin->init();

}
	
/*
register and unregister hooks
*/
register_activation_hook(__FILE__,'pbt_ManFin_install');
register_uninstall_hook(__FILE__, 'pbt_ManFin_uninstall');