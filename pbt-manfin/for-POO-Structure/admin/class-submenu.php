<?php
/**
 * Creates the submenu item for the plugin.
 *
 * @package Custom_Admin_Settings
 */
 
/**
 * Creates the submenu item for the plugin.
 *
 * Registers a new menu item under 'Tools' and uses the dependency passed into
 * the constructor in order to display the page corresponding to this menu item.
 *
 * @package Custom_Admin_Settings
 */
class Submenu {
 
	/**
	* A reference the class responsible for rendering the submenu page.
	*
	* @var    Submenu_Page
	* @access private
	*/
    private $submenu_page;
 
    /**
	* Initializes all of the partial classes.
	*
	* @param Submenu_Page $submenu_page A reference to the class that renders the
	* page for the plugin.
	*/
    public function __construct( $submenu_page ) {
        $this->submenu_page = $submenu_page;
    }
 
    /**
	* Adds a submenu for this plugin to the 'Tools' menu.
	*/
    public function init() {
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
    }
 
    /**
	* Creates the submenu item and calls on the Submenu Page object to render
	* the actual contents of the page.
	*/
    public function add_options_page() {
		
		
		add_menu_page( 'Manager Financiar', 'Manager Financiar', 'manage_options', 'pbt-ManFin', array( $this->submenu_page, 'render' ), 'dashicons-cart', 56);
		// add_menu_page( 'Manager Financiar', 'Manager Financiar', 'manage_options', 'pbt-ManFin', 'pbt_main_page_content', 'dashicons-cart', 56);
		add_submenu_page('pbt-ManFin', 'Manager Financiar', 'Dashboard', 'manage_options', 'pbt-ManFin');
		add_submenu_page('pbt-ManFin', 'Produse', 'Produse', 'manage_options', 'pbt-manfin-update-products', 'manfin_update_products');
		add_submenu_page('pbt-ManFin', 'Categorii', 'Categorii', 'manage_options', 'pbt-manfin-update-categs', 'manfin_update_categs');
		add_submenu_page('pbt-ManFin', 'Imagini', 'Imagini', 'manage_options', 'pbt-manfin-update-imgs', 'manfin_update_imgs');
		add_submenu_page('pbt-ManFin', 'Parametrii', 'Parametrii', 'manage_options', 'pbt-manfin-params-map', 'manfin_params_maps');
		add_submenu_page('pbt-ManFin', 'Sterge Produse', 'Sterge Produse', 'manage_options', 'pbt-manfin-del-products', 'manfin_del_products');

		// add_options_page(
            // 'Manager Financiar',
            // 'Manager Financiar',
            // 'manage_options',
            // 'custom-admin-page',
            // array( $this->submenu_page, 'render' )
        // );
    }
}