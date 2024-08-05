<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// error_reporting(0);

class PBT_Rest_Controller extends WP_REST_Controller {

	function __construct() {

		global $manfin_db;

		if(!$manfin_db) return;

		if(is_null($manfin_db)){
			try{

				// $manfin_db = new PDO("odbc:DRIVER=FreeTDS;SERVER=86.122.122.58;PORT=1434;Database=ManFin;", "ManFInMV", "exgala@2016");
				$manfin_db = new PDO("odbc:DRIVER=FreeTDS;TDS_Version=7.4;Client_Charset=utf8;SERVER=". DB_SQL_HOST .";PORT=". DB_SQL_PORT .";Database=".DB_SQL_NAME.";", DB_SQL_USER, DB_SQL_PASS);
				$manfin_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


				// echo "<pre>";
				// print_r($manfin_db);
				// echo "</pre>";
				// die();

			}catch(PDOException $exception)	{
				ob_start();

				new WP_Error( 'error', 'Unable to open database.<br />Error message:<br /><br />$exception.', array( 'status' => 404 ) );
				$this->output = ob_get_clean();

				return new WP_REST_Response($this->output, 200);
			}
		}
		// echo "ECHO";
		// var_dump(DB_SQL_HOST);

	}

	public function register_routes() {
		$namespace = 'pbt/v1';
		$path = '/product-update/(?P<CodStoc>\d+)';

		register_rest_route( $namespace, '/' . $path, [
		  array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'pbt_rest_update_product' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' )
				),

		]);


		$path = '/get-manfin-ids/';

		register_rest_route( $namespace, '/' . $path, [
		  array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_all_manfin_ids' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' )
				),

		]);

		$path = '/update-stocks/';

		register_rest_route( $namespace, '/' . $path, [
		  array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'update_stocks' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' )
				),

		]);

		$path = '/get-manfin-cities/';

		register_rest_route( $namespace, '/' . $path, [
		  array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_all_cities' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' )
				),

		]);
	}

	public function get_all_manfin_ids(){

		global $manfin_db;

		if(!$manfin_db) return;

		ob_start();

		$query = "EXECUTE spWEBMVStocuriIDToate";

		$pdo = $manfin_db->prepare($query);
		$pdo->execute();

		$coduri_stocuri = array();

		while($row_CodStoc  = $pdo->fetch(PDO::FETCH_ASSOC)) {

			$coduri_stocuri[] = $row_CodStoc['CodStoc'];

		}

		// echo "<pre>";
		// echo count($coduri_stocuri);
		// echo "</pre>";

		$pdo->closeCursor();

		$this->output = ob_get_clean();

		$message["type"] = "success";
		$message["numar-produse"] = count($coduri_stocuri);
		$message["ids"] = $coduri_stocuri;
		$message["message"] = "CodStoc-uri incarcate";

		if(isset($message)){
			// $this->output = json_encode($message);
			$this->output =  $message;
		}

		// $this->output = json_encode();

		return new WP_REST_Response($this->output, 200);
	}

	public function update_stocks(){
		/*salveaza log*/
		$plugin_log_path = WP_PLUGIN_DIR . '/pbt-manfin/log';
		check_upload_path($plugin_log_path);

		if ( is_dir( $plugin_log_path ) ) {
			$cron_log_file = $plugin_log_path . "/cron.log";
			$file = fopen( $cron_log_file , "a" );
			fwrite( $file, "Start Time: " . date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) )  . "\n");
			fclose( $file);
		}

		$start = microtime(true);

		ob_start();

		$import = new ManFinImport();

		$message = $import->manfin_import_update_stocks();

		$this->output = ob_get_clean();

		// $message["type"] = "success";
		// $message["numar-produse"] = count($coduri_stocuri);
		// $message["ids"] = $coduri_stocuri;

		$time_elapsed_secs = microtime(true) - $start;
		$message["durata update in secunde"] = $time_elapsed_secs;

		if(isset($message)){
			// $this->output = json_encode($message);
			$this->output =  $message;
		}

		// $this->output = json_encode();
		/*salveaza log*/
		if ( is_dir( $plugin_log_path ) ) {
			$cron_log_file = $plugin_log_path . "/cron.log";
			$file = fopen( $cron_log_file , "a" );
			fwrite( $file, "Finish Time: " . date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) )  . " | Timelapse: " .  number_format($time_elapsed_secs, 2, '.', '') . " secunde\n");
			fclose( $file);
		}else{
			$message["log path"] = "Not Found!";
		}


		return new WP_REST_Response($this->output, 200);
	}

	public function get_all_cities(){

		global $manfin_db;

		if(!$manfin_db) return;

		ob_start();

		$query = "EXECUTE spWEBMVJudeteLocalitati";

		$pdo = $manfin_db->prepare($query);
		$pdo->execute();

		foreach( $pdo->fetchAll(PDO::FETCH_ASSOC) as $Localitate ) {
			// echo "<pre>";
			// print_r($Localitate);
			// echo "</pre>";
			// die();
			// str_replace('VR', 'VN', $Localitate['Judet']);
			if($Localitate['CodLocalitate'] != "1"){
				$ManFin_Localitati[$Localitate['Judet']][$Localitate['CodLocalitate']] = $Localitate['Localitate'];
			}
		}


		ksort($ManFin_Localitati);

		foreach($ManFin_Localitati as $key => $Localitati){
			$woo_states = WC()->countries->get_states( "RO" );
			foreach($woo_states as $state_code => $state_name){
				if($key == $state_name){
					$ManFin_Localitati[$state_code] = $Localitati;
					unset($ManFin_Localitati[$key]);
					break;
				}
			}
		}
		// if(isset($ManFin_Localitati['VR'])){
		// 	$ManFin_Localitati['VN'] = $ManFin_Localitati['VR'];
		// 	unset($ManFin_Localitati['VR']);
		// }
		// echo "<pre>";
		// print_r($ManFin_Localitati);
		// echo "</pre>";
		// die();
		update_option('_ManFin_Localitati', serialize($ManFin_Localitati));
		// update_option('_ManFin_Localitati_Map', serialize($ManFin_Localitati_Map));


		$this->output = ob_get_clean();

		$message["type"] = "success";
		$message["numar-judete"] = count($ManFin_Localitati);
		// $message["ids"] = $coduri_localitati;

		if(isset($message)){
			// $this->output = json_encode($message);
			$this->output =  $message;
		}

		// $this->output = json_encode();

		return new WP_REST_Response($this->output, 200);
	}

	public function pbt_rest_update_product($request) {

		global $manfin_db;

		if(!$manfin_db) return;

		ob_start();

		// var_dump($request);

		// var_dump(manfin_manfin_update_product_helper($request['CodStoc']));
		$import = new ManFinImport();

		$message = $import->manfin_import_product_helper($request['CodStoc']);

		// var_dump($message);
		// die();

		if( $message === false ){
			return new WP_Error( 'invalid_CodStoc', 'CodStoc Invalid', array( 'status' => 404 ) );
		}

		$this->output = ob_get_clean();

		// var_dump($this->output);

		if(isset($message)){
			$this->output = $message;
		}
		// echo "<script>window.close();</script>";
		// $posts = get_posts($args);


		// if (empty($posts)) {

				// return new WP_Error( 'empty_category', 'there is no post in this category', array( 'status' => 404 ) );
		// }
		$response = new WP_REST_Response($this->output, 200);

		return $response;
	}

	public function get_items_permissions_check($request) {
		return true;
	}

}
?>