<?php
/**
 * Plugin Name: Manager Financiar - Soft Expert
 * Plugin URI: http://www.powbyte.com
 * Description: Import automat produse - Delta
 * Version: 1.1.4
 * Author: Marius D.
 * Author URI: http://www.powbyte.com
 */
// exit;
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if ( ! defined( 'MANFIN_PLUGIN_FILE_PATH' ) ) {
	define( 'MANFIN_PLUGIN_FILE_PATH', plugin_dir_path( __FILE__ ) );
}

register_activation_hook(__FILE__, 'activate_pbt_manfin');
register_deactivation_hook(__FILE__, 'deactivate_pbt_manfin');
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wc-dpd-activator.php
 */
function activate_pbt_manfin(){
	// require_once plugin_dir_path(__FILE__) . 'includes/class-wc-dpd-activator.php';
	// require_once plugin_dir_path(__FILE__) . 'includes/db/class-wc-dpd-db.php';
	// Wc_Dpd_Activator::activate();
	// Wc_Dpd_DB::DPD_Activation();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wc-dpd-deactivator.php
 */
function deactivate_pbt_manfin(){
	// require_once plugin_dir_path(__FILE__) . 'includes/class-wc-dpd-deactivator.php';
	// require_once plugin_dir_path(__FILE__) . 'includes/db/class-wc-dpd-db.php';
	// Wc_Dpd_Deactivator::deactivate();
	// Wc_Dpd_DB::DPD_Deactivation();
}
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// error_reporting(0);




/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {



	/**
	 * Load ManFin Helpers
	*/
	foreach ( glob( MANFIN_PLUGIN_FILE_PATH . 'helpers/*.php' ) as $file ) {
		include_once $file;
	}


	class PBT_ManFin_ERP {
		const VERSION = '1.1.2';

		/**
         * Construct class
         */
        public function __construct() {
            add_action( 'plugins_loaded', array( $this, 'init') );
        }

		/**
         * Plugin init
		*/
        public function init() {
            // $this->load_libraries();
            // $this-> init_places();

			$this->mixed_hooks();
			$this->load_rest_admin_libraries();

			if ( is_admin() ) {
				$this->load_admin_scripts();
				$this->load_admin_libraries();
				$this->admin_hooks();
			}else{
				$this->front_hooks();
				$this->load_front_scripts();
			}
        }

		public function admin_hooks(){
			add_action( 'admin_post', 'pbt_save_manfin_params' );
		}

		public function front_hooks(){

		}

		public function mixed_hooks(){

		}

		/**
         * Load scripts
		*/
        public function load_admin_scripts() {

			/**
			 * Register and enqueue a custom stylesheet in the WordPress admin.
			 */
			add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts'));
		}
		public function admin_scripts() {
			// wp_register_style( 'custom_wp_admin_css', get_template_directory_uri() . '/admin-style.css', false, '1.0.0' );
			// wp_enqueue_style( 'custom_wp_admin_css' );
			$siteurl = get_option('siteurl');

			$backend_css = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/assets/css/backend.css';

			$js_url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/assets/js/pbt-manfin.js';

			$city_select_js_url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/assets/js/pbt-manfin-city-select.js';
			wp_enqueue_script( 'pbt-custom-js' , $js_url, array('jquery'));
			// wp_enqueue_script( 'pbt-city-select-js' , $city_select_js_url, array('jquery'));
			// var_dump(is_admin());
			if(is_admin()){
				wp_register_style('manfin-backend', $backend_css);
				wp_enqueue_style( 'manfin-backend' );
			}

			wp_localize_script( 'pbt-custom-js', 'pbt_manfin_params', array(
				// 'cities' => get_cities(),
				'siteurl' => $siteurl,
				'i18n_select_city_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' )
			) );
			// wp_enqueue_style('wvp-frontend-style', $url);
		}

		public function load_front_scripts() {

		}

		/**
         * Load ManFin Libraries
		*/
        public function load_admin_libraries($scope = NULL) {
			// Include admin classes.
			foreach ( glob( MANFIN_PLUGIN_FILE_PATH . 'admin/*.php' ) as $file ) {
				include_once $file;
			}
        }

		/**
         * Load ManFin Rest API Libraries
		*/
        public function load_rest_admin_libraries($scope = NULL) {
			// Include admin classes.
			foreach ( glob( MANFIN_PLUGIN_FILE_PATH . 'rest/*.php' ) as $file ) {
				include_once $file;
			}
			add_action( 'rest_api_init', array($this, 'register_wp_api_endpoints' ));
        }

		public function register_wp_api_endpoints() {
			$rest_import_controller = new PBT_Rest_Controller();
			$rest_import_controller->register_routes();
		}

		/**
         * Load scripts
		*/
        public function apply_filters($scope = NULL) {
			$version = get_bloginfo('version');
			/**
			 * Force GD for Image handle (WordPress 3.5 or better)
			 * Thanks (@nikcree)
			 *
			 * @since 1.5
			 */
			if ($version >= 3.5) {
				add_filter('wp_image_editors', array($this, 'ms_image_editor_default_to_gd_fix'));
			}
		}

		public function ms_image_editor_default_to_gd_fix( $editors ) {
			$gd_editor = 'WP_Image_Editor_GD';

			$editors = array_diff( $editors, array( $gd_editor ) );
			array_unshift( $editors, $gd_editor );

			return $editors;
		}
	}
	new PBT_ManFin_ERP;
}



/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	require_once('config.php');
	// require_once('helpers/pbt-manfin.php');
	// require_once('helpers/pbt-manfin-import.php');
	// require_once('helpers/pbt-manfin-export-orders.php');
	// require_once('helpers/pbt-manfin-sync-clients.php');

	// require_once('helpers/pbt-manfin-imports-inc.php');
	// require_once('helpers/pbt-rest-import.php');

	/*
	Actionhooks
	*/
	// add_action('init', 'pbt_plugin_frontend_head', 9999); //all db requests
	// add_action('admin_head', 'webinars_videos_program_plugin_head'); //admin css


	/*frontend page*/

	add_action( 'wp_enqueue_scripts', 'pbt_plugin_frontend_css', 1001 );//frontend css
	function pbt_plugin_frontend_css(){
		$siteurl = get_option('siteurl');
		$frontend_css = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/assets/css/frontend.css';
		$frontend_js  = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/assets/js/manfin.js';
		wp_register_style('manfin-frontend', $frontend_css, rand(10,1000));
		wp_register_script('manfin-scripts', $frontend_js, array('jquery'), rand(10,1000));
		wp_enqueue_style( 'manfin-frontend' );
		wp_enqueue_script( 'manfin-scripts' );
	}

	add_action('wp_head', 'pbt_plugin_frontend_head');
	function pbt_plugin_frontend_head(){

		if(is_woocommerce()){
			require_once( 'helpers/frontent-hooks.php' );
		}
	}


}





?>