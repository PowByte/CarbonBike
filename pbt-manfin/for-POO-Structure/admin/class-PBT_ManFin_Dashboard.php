<?php
/**
 * Creates the submenu page for the plugin.
 *
 * @package Custom_Admin_Settings
 */
 
/**
 * Creates the submenu page for the plugin.
 *
 * Provides the functionality necessary for rendering the page corresponding
 * to the submenu with which this page is associated.
 *
 * @package Custom_Admin_Settings
 */
class PBT_ManFin_Dashboard {
	
	public function __construct( $deserializer ) {
		$this->deserializer = $deserializer;
	}
		
	/**
    * This function renders the contents of the page associated with the Submenu
    * that invokes the render method. In the context of this plugin, this is the
    * Submenu class.
    */
    public function render($page) {
        include_once( plugin_dir_path( __FILE__ ) . '/views/'.$page.'.php' );
    }
}