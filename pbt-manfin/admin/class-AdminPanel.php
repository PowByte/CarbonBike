<?php
class PBT_AdminPanel{
	public function init(){
		
		//Add a submenu page
		add_action('admin_menu', array($this, 'add_options_page'));
	}
			 
	public function add_options_page(){ 
		add_menu_page( 'Manager Financiar', 'Manager Financiar', 'manage_options', 'ManFin', array($this, 'pbt_main_page_content'), 'dashicons-cart', 56);
		add_submenu_page('ManFin', 'Manager Financiar', 'Dashboard', 'manage_options', 'ManFin');
		add_submenu_page('ManFin', 'Produse', 'Produse', 'manage_options', 'manfin-update-products',  array($this, 'manfin_update_products'));
		add_submenu_page('ManFin', 'Categorii', 'Categorii', 'manage_options', 'manfin-update-categs',  array($this, 'manfin_update_categs'));
		add_submenu_page('ManFin', 'Imagini', 'Imagini', 'manage_options', 'manfin-update-imgs',  array($this, 'manfin_update_imgs'));
		add_submenu_page('ManFin', 'Parametrii', 'Parametrii', 'manage_options', 'manfin-params-map',  array($this, 'manfin_params_maps'));
		add_submenu_page('ManFin', 'Localitati', 'Localitati', 'manage_options', 'manfin-cities-import',  array($this, 'manfin_cities_maps'));
		add_submenu_page('ManFin', 'Sterge Produse', 'Sterge Produse', 'manage_options', 'manfin-del-products',  array($this, 'manfin_del_products'));
	}
	
	/*main page*/
	public function pbt_main_page_content(){
		if (!current_user_can('manage_options')){ 
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		global $wpdb;
		require_once( 'views/header.php' );
		require_once( 'views/erp_main_page.php' );
		require_once( 'views/footer.php' );
	}
	
	
	/*manfin_update_product page*/
	public function manfin_update_products(){	
		if (!current_user_can('manage_options')){ 
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
		require_once( 'views/header.php' );
		
		echo '<div id="pbt_manfin_log" >';
		// print_r( manfin_manfin_update_product(5756) );
		// print_r( manfin_update_product(8218) );
		// print_r( manfin_update_product(3713) ); //delta
		
		$import = new ManFinImport();
		echo "<pre>";
		print_r( $import->manfin_import_product_helper(3713) );
		echo "</pre>";

		echo '</div><!-- /#pbt_manfin_log -->';
		// include( 'views/erp_update_product_page.php' );
	}

	
	/*manfin_update_categs page*/
	public function manfin_update_categs(){	
		if (!current_user_can('manage_options')){ 
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		require_once( 'views/header.php' );
		echo '<div id="pbt_manfin_log" >';
		manfin_update_products_cats();
		echo '</div><!-- /#pbt_manfin_log -->';
		// require_once( 'views/erp_update_product_page.php' );
	}
	
	/*manfin_update_imgs page*/
	public function manfin_update_imgs(){	
		if (!current_user_can('manage_options')){ 
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}		
		require_once( 'views/header.php' );
		// require_once( 'views/erp_update_product_page.php' );
	}
	
	/*manfin_params_maps page*/
	public function manfin_params_maps(){	
		if (!current_user_can('manage_options')){ 
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}		
		require_once( 'views/header.php' );	
		
		echo '<div id="pbt_manfin_log" >';
		$dinamic_content = manfin_update_map_helper();
		echo '</div><!-- /#pbt_manfin_log -->';
		
		
		include( 'views/pbt-manfin-params-map.php' );
	}
	
	/*manfin_params_maps page*/
	public function manfin_cities_maps(){	
		if (!current_user_can('manage_options')){ 
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}		
		require_once( 'views/header.php' );	
		
		echo '<div id="pbt_manfin_log" >';
		// $dinamic_content = manfin_update_map_helper();
		// /wp-json/pbt/v1/get-manfin-cities
		echo '</div><!-- /#pbt_manfin_log -->';
		
		
		// include( 'views/pbt-manfin-params-map.php' );
	}
	
	
	/*manfin_params_maps page*/
	public function manfin_del_products(){	
		if (!current_user_can('manage_options')){ 
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}		
		require_once( 'views/header.php' );
		
		// require_once( 'views/erp_update_product_page.php' );
		
			
		echo '<div id="pbt_manfin_log" >';
		manfin_delete_product_helper();
		echo '</div><!-- /#pbt_manfin_log -->';
	}
}
// PBT_AdminPanel::init();
$admin = new PBT_AdminPanel;
$admin->init();
?>