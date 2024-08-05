<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}

	class ManFinImport{

		/**
		 * Static-only class.
		 */
		public function __construct() {}

		public function manfin_import_product_helper($CodStoc){

			$start = microtime(true);

			global $manfin_db;

			// if($CodStoc != 14441) die();

			ob_start();

			try {
				/*/ iau detaliile produsului conform id MF /*/
				$query_1 = "EXECUTE spWEBMVStocuriDetalii @CodStoc=". $CodStoc;
				$pdo = $manfin_db->prepare($query_1);
				$pdo->execute();
			} catch (Exception $e) {
				/**** Nu se trimite user in ManFin ****/
				$headers =  'From: No Reply <noreply@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
				$headers.="CC:marius@powbyte.com, valentin.pascaru@exgala.ro\r\n";
				$headers.='Content-Type: text/html; charset=UTF-8\r\n';
				$message = stripslashes ($e);
				wp_mail('mihai.topa@exgala.ro', 'Nu se pot sincroniza produsele. Eroare 1.', $message, $headers);
				return;
			}



			if( $row_produs  = $pdo->fetch()) {

				// print_r($row_produs);
				// die();

				$pdo->closeCursor();

				$query_2 = "EXECUTE spWebMVStocuriParametri @CodStoc = ". $CodStoc;
				$params = $manfin_db->prepare($query_2);
				$params->execute();

				$params_map = unserialize(get_option("_ManFin_Params"));
				$active_params_map = unserialize(get_option("_ManFin_Active_Params"));

				$product_params = array();

				while( $row_param  = $params->fetch()) {

					$row_param["NumeParametru"] = $params_map[$row_param["CodParametru"]];

					if($row_param["NumeParametru"] == "Pas lant"){
						$row_param["NumeParametru"] = "Pas lant (Inch)";
					}
					//maybe use url encode in the future
					$product_params[] = array(
						'CodParametru' 	=> $row_param["CodParametru"],
						'term_name' 	=> $row_param["NumeParametru"],
						'term_value' 	=> str_replace('"', '', $row_param["Valoare"]),
						'position' 		=> $row_param["Prioritate"]
					);
					// if($row_param['NumeParametru'] == "Latime" && $row_param['CodParametru'] == "1"){
					// 	$Latime = $row_param['Valoare'];
					// }
					// if($row_param['NumeParametru'] == "Inaltime" && $row_param['CodParametru'] == "2"){
					// 	$Inaltime = $row_param['Valoare'];
					// }
					// if($row_param['NumeParametru'] == "Raza" && $row_param['CodParametru'] == "3"){
					// 	$Raza = $row_param['Valoare'];
					// }
					// if($row_param['NumeParametru'] == "Marca" && $row_param['CodParametru'] == "9"){
					// 	$Marca = $row_param['Valoare'];
					// }
					// if($row_param['NumeParametru'] == "Sezon" && $row_param['CodParametru'] == "5"){
					// 	$Sezon = $row_param['Valoare'];
					// }
					// break;
				}
				// print_r($product_params);
				// die();
				usort($product_params, function($a, $b) {
					return $a['position'] <=> $b['position'];
				});

				$product_active_params = array();
				$custom_fields = array();

				foreach($product_params as $product_param){
					/*Creare custom fields/meta pentru filtrare */
					// echo "<pre>";
					// var_dump($product_param["term_name"]);
					// print_r($product_param);
					// echo "</pre>";
					if(is_array($active_params_map)){
						if(in_array($product_param['term_name'], $active_params_map)){
							$product_active_params[] = $product_param;
						}
					}
					/*Individual meta field for filter*/
					$custom_fields_name = "_MFP_".$product_param["CodParametru"];
					$custom_fields[$custom_fields_name] = $product_param["term_value"];
					// break;
				}

				// var_dump(get_product_by_wp_manfin_id( $row_produs["CodStoc"] ));
				// die();
				// if( $product = $this->get_product_by_sku( $row_produs["DenumireStoc"] )){
				if( $product = get_product_by_wp_manfin_id( $row_produs["CodStoc"] )){
					/*/update product/*/
					// echo "update produs ---";

					$product_categs = pbt_get_terms_by_CodGrupa($row_produs["CodGrupa"]);

					if( is_array($product_categs) && !empty($product_categs)){

						$product_categ = $product_categs[0];
						$product_categ_slug = $product_categ->slug;
						$product_categ_id = $product_categ->term_id;
						$product_main_categ_id = $product_categ->parent;
						$categs_to_insert = array($product_main_categ_id, $product_categ_id);
						$message["categ"] = "cu categorii";
					}else{
						$product_categ = "";
						$product_categ_slug = "";
						$product_categ_id = "";
						$product_main_categ_id = "";
						$categs_to_insert = "";
						$message["categ"] = "fara categorii";
					}

					$product_id = $product->get_id();
					$product_CodStoc = get_post_meta($product_id, "_wp_manfin_id", true);

					// echo "<br /> updating ID WP: " . $product_id . " ...";
					// echo "<br /> updating CodStoc: " . $product_CodStoc . " ...";

					if(! isset($row_produs["DenumireSEO"])) $row_produs["DenumireSEO"] = $row_produs["DenumireStocClar"];
					if(! isset($row_produs["DescriereSEO"])) $row_produs["DescriereSEO"] = $row_produs["Descriere"];
					if(! isset($row_produs["LinkSEO"])) $row_produs["LinkSEO"] = $row_produs["DenumireStocClar"];

					$custom_fields['rank_math_description'] = $row_produs["DescriereSEO"];
					$custom_fields['_ManFin_Product_Params'] = serialize($product_params);
					$custom_fields['_ManFin_Active_Product_Params'] = serialize($product_active_params);
					$custom_fields['_wp_manfin_id'] = $row_produs["CodStoc"];

					// echo "<pre>";
					// print_r($custom_fields);
					// echo "<pre>";

					// die();
					// $tags_to_insert = array(26);
					// echo  $row_produs["Descriere"];
					// die("manfin import");
					// wp_die();

					$descriere_specificatii = $row_produs["Descriere"] . $row_produs["SpecificatiiTehnice"];

					$update_product_args = array(
						'post_type' 		=> 'product',
						'ID' 				=> $product_id,
						'post_status' 		=> "publish",
						'post_title' 		=> $row_produs["DenumireStocClar"],
						'name'              => __($row_produs["DenumireStocClar"], "woocommerce"),
						'sku'               => $row_produs["DenumireStoc"],
						'manage_stock'      => true,
						'description'       => $descriere_specificatii,
						'short_description' => __($row_produs["DescriereSEO"], "woocommerce"),
						'stock_qty'         => $row_produs["StocNumeric"],
						'low_stock_amount'  => "1",
						'backorders'        => "no",
						'weight'            => $row_produs["Greutate"],
						'regular_price'     => pbt_formula_pret( $row_produs["PretListaClienti"] ), // product price
						'sale_price'        => pbt_formula_pret( $row_produs["PretEMag"] ),
						'reviews_allowed'   => true,
						'category_ids'    	=> $categs_to_insert,
						// 'tag_ids'    		=> $tags_to_insert,
						// 'attributes'    	=> $product_attributes,
						'custom_fields' 	=> $custom_fields
					);

					// echo "<pre>";
					// print_r($update_product_args);
					// die();
					// echo "</pre>";

					pbt_crud_update_product($update_product_args);

					// echo "<pre>";
					// print_r($row_produs);
					// echo json_encode($product, v);
					// echo "</pre>";

					// 1. Updating the stock quantity
					// update_post_meta($product_id, '_stock', $row_produs["StocNumeric"]);
					update_post_meta($product_id, '_ManFin_Product_Params', serialize($product_params));

					// 2. Updating the stock quantity
					// update_post_meta( $product_id, '_stock_status', wc_clean( $out_of_stock_status ) );

					// 3. Updating post term relationship
					// wp_set_post_terms( $product_id, 'outofstock', 'product_visibility', true );

					// And finally (optionally if needed)
					// wc_delete_product_transients( $product_id ); // Clear/refresh the variation cache

					// var_dump($product_categs);

					// print_r($product_categ);

					/*Disable pictures updates till launch */
					// if(isset($_GET["poza"])){
						// die("pune poza");
						manfin_importpictures($product_id, $product_CodStoc, $product->get_sku(), $product->get_name(), $product_categ_slug);
					// }
					/* Delta adauga brand */
					// if( !is_null($Marca) &&  $Marca != "" && taxonomy_exists("tyre_brand")){
						// wp_set_object_terms($product_id, $Marca, "tyre_brand");
					// }
					/* Delta adauga sezon */
					// if( !is_null($Sezon) &&  $Sezon != "" && taxonomy_exists("tyre_type")){
						// wp_set_object_terms($product_id, $Sezon, "tyre_type");
					// }

					update_post_meta($product_id, "_wp_manfin_id", $row_produs["CodStoc"]);

					$message["type"] = "success";
					$message["CodStoc"] = $row_produs["CodStoc"];
					$message["id_produs_wp"] = $product_id;
					$message["pret"] =  $update_product_args["regular_price"];
					$message["stoc"] =  $row_produs["StocNumeric"];
					$message["message"] = $row_produs["DenumireStoc"] . " - produs actualizat";
					// $message["params"] = serialize($product_params);

				}else{
					/*/insert product/*/

					$product_categs = pbt_get_terms_by_CodGrupa($row_produs["CodGrupa"]);

					if( is_array($product_categs) && !empty($product_categs)){

						$product_categ = $product_categs[0];
						$product_categ_slug = $product_categ->slug;
						$product_categ_id = $product_categ->term_id;
						$product_main_categ_id = $product_categ->parent;

					}else{
						$product_categ = "";
						$product_categ_slug = "";
						$product_categ_id = "";
						$product_main_categ_id = "";
					}

					$message["CodGrupa"] = $row_produs["CodGrupa"];

					if( !empty($product_categ) || !empty($product_main_categ_id) ){

						// echo "insert produs --- ";

						$categs_to_insert = array($product_main_categ_id, $product_categ_id);

						if(! isset($row_produs["DenumireSEO"])) $row_produs["DenumireSEO"] = $row_produs["DenumireStocClar"];
						if(! isset($row_produs["DescriereSEO"])) $row_produs["DescriereSEO"] = $row_produs["Descriere"];
						if(! isset($row_produs["LinkSEO"])) $row_produs["LinkSEO"] = $row_produs["DenumireStocClar"];

						// if(false){
							$descriere_specificatii = $row_produs["Descriere"] . $row_produs["SpecificatiiTehnice"];
							$new_product =  array(
								'type'              => '', // Simple product by default
								'post_author' 		=> "7", /*de facut parametru configurabil*/
								'status' 		 	=> "publish",
								'virtual' 		 	=> false,
								// 'name'              => __($row_produs["DenumireStocClar"], "woocommerce"),
								'description'       => $descriere_specificatii,
								'short_description' => __($row_produs["DescriereSEO"], "woocommerce"),
								'name'              => $row_produs["LinkSEO"],
								'sku'               => $row_produs["DenumireStoc"],
								'manage_stock'      => true,
								'low_stock_amount'  => "1",
								'backorders'        => "no",
								'stock_qty'         => $row_produs["StocNumeric"],
								'weight'            => $row_produs["Greutate"],
								'regular_price'     => pbt_formula_pret( $row_produs["PretListaClienti"] ), // product price
								'sale_price'        => pbt_formula_pret( $row_produs["PretEMag"] ),
								'reviews_allowed'   => true,
								'category_ids'    	=> $categs_to_insert,
								// 'attributes'    	=> $product_attributes,
								'custom_fields' 	=> array(
														// 'rank_math_description'  => $row_produs["DescriereSEO"],
														'_ManFin_Product_Params' => serialize($product_params),
														'_wp_manfin_id' 		 => $row_produs["CodStoc"]

													)
							);

							$product_id = pbt_crud_create_product( $new_product );

							manfin_importpictures($product_id, $row_produs["CodStoc"], $row_produs["DenumireStoc"], $row_produs["DenumireStocClar"], $product_categ_slug);

							/*/ add Rank Math details /*/

							// update_post_meta($product_id, 'rank_math_description', $row_produs["DescriereSEO"]);
							// update_post_meta($product_id, '_ManFin_Product_Params', serialize($product_params));
							// update_post_meta($product_id, "_wp_manfin_id", $row_produs["CodStoc"]);

						// }

						$message["type"] = "success";
						$message["CodStoc"] = $row_produs["CodStoc"];
						$message["id_produs_wp"] = $product_id;
						$message["pret"] = $new_product["regular_price"];
						$message["message"] = $row_produs["DenumireStoc"] . " - produs adaugat";
					}else{
						$message["type"] = "success";
						$message["message"] = $row_produs["DenumireStoc"] . " - produs fara grupa activa";
					}
				}
			}
			// var_dump($message);
			// if(isset($message)){
				return $message;
			// }else{
				// return false;
			// }
		}

		public function manfin_import_update_stocks(){

			// ini_set('display_errors', 1);
			// ini_set('display_startup_errors', 1);
			// error_reporting(E_ALL);

			global $manfin_db;

			$products = $this->get_products();


			foreach ($products as $product) {

				if ( $product ) {

					$CodStoc = $product->get_meta('_wp_manfin_id');

					/*/ iau detaliile produsului conform id MF /*/

					$query = "EXECUTE spWEBMVStocuriDetalii @CodStoc=". $CodStoc;
					// $pdo = $manfin_db->prepare($query);
					// $pdo->execute();
					try {
						$pdo = $manfin_db->prepare($query);
						$pdo->execute();
					}catch (Exception $e) {
						$headers =  'From: No Reply <noreply@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
						$headers.="CC:marius@powbyte.com, valentin.pascaru@exgala.ro\r\n";
						$headers.='Content-Type: text/html; charset=UTF-8\r\n';
						$message = stripslashes ($e);
						wp_mail('mihai.topa@exgala.ro', 'Eroare Update Stocuri', $message, $headers);
					}

					if( $row_produs = $pdo->fetch(PDO::FETCH_ASSOC)) {

						//1568

						$pret_nou_regular = pbt_formula_pret($row_produs["PretListaClienti"]);
						$pret_nou_sale = pbt_formula_pret($row_produs["PretEMag"]);

						$message[$CodStoc]['id produs'] = $product->get_id();
						$message[$CodStoc]['CodStoc'] = $CodStoc;
						$message[$CodStoc]['Stoc'] = $row_produs["StocNumeric"];
						$message[$CodStoc]['PretSiteSale'] = $product->get_sale_price();
						$message[$CodStoc]['PretSiteRegular'] = $product->get_regular_price();
						$message[$CodStoc]['PretNouRegular'] = ($pret_nou_regular)?$pret_nou_regular:'';
						$message[$CodStoc]['PretNouSale'] = ($pret_nou_sale)?$pret_nou_sale:'';
						// $message["Date ManFin"] = $row_produs;


						$product->set_manage_stock(true);
						$product->set_stock_quantity( $row_produs["StocNumeric"] );

						if( $pret_nou_sale >= $pret_nou_regular){
							$product->set_regular_price( $pret_nou_sale );
							$product->set_sale_price('');
						}else{
							$product->set_regular_price($pret_nou_regular);
							$product->set_sale_price($pret_nou_sale);
						}

					}else{
						/* verifica daca comunica cu erp aici sau inainte de execute */
						$message[$CodStoc]['sql'] = "Nu s-a gasit produs";
						$product->set_stock_quantity(0);
						$product->set_manage_stock(false);
						$product->set_stock_status('unavailable');
					}
				}
				$product->save();
			}

			return $message;

		}

		private function get_products() {

			$args = array(
				'status' => 'publish',
				'limit' => -1
			);

			$products = wc_get_products( $args );

			return $products;
		}

	}



	function get_product_by_sku( $sku ) {

		global $wpdb;

		$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );

		if ( $product_id ) return new WC_Product( $product_id );

		return null;
	}

	function get_product_by_wp_manfin_id( $_wp_manfin_id ) {

		global $wpdb;

		$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wp_manfin_id' AND meta_value='%s' LIMIT 1", $_wp_manfin_id ) );

		if ( $product_id ) return new WC_Product( $product_id );

		return null;
	}


	function manfin_update_map_helper(){

		global $manfin_db;


		if (isset($_POST["do"])) {

		}

		$query_3 = "EXECUTE spWEBMVParametri";
		$params_map = $manfin_db->prepare($query_3);
		$params_map->execute();

		$params_map_list = array();

		while( $row_param_map  = $params_map->fetch()) {
			$params_map_list[$row_param_map["Cod"]] = $row_param_map["Denumire"];
		}

		if( ! get_option('_ManFin_Params') ){
			// Option does not exist - add option
			add_option("_ManFin_Params", serialize($params_map_list));
		}else{
			// Option does exist - update options option
			update_option("_ManFin_Params", serialize($params_map_list));
		}

		// echo "<pre>";
		// print_r($params_map_list);
		// echo "</pre>";

		// wc_prepare_product_attributes($params_map_list);

		$dinamic_content["params"] = $params_map_list;
		$dinamic_content["active_params"] = unserialize(get_option("_ManFin_Active_Params"));

		$params_map->closeCursor();

		return $dinamic_content;
	}

	function manfin_importpictures($product_id, $CodStoc, $sku, $slug, $categ_slug){

		global $manfin_db;
		global $wpdb;
		$product = new WC_Product( $product_id );

		// $temp_path =
		// $updated_products_ids = array();
		// $updated_pictures_ids = array();

		// $product = new WC_Product( $product_id );

		// echo '<pre>';
		// print_r($product);
		// echo '</pre>';

		// $sku = $product->get_sku();

		// var_dump($row_img[$pic_index]);

		$i = 1;
		// $i = 2;
		do{

			$pic_name = preg_replace("/[^A-Za-z0-9\_\-]/", '', str_replace(" ", "-", $slug . "-" . $i))  . '.jpg';

			if(!is_null($categ_slug)){
				$pic_name = $categ_slug . "-" .$pic_name;
			}

			// echo "<br />" . "EXECUTE spWEBSelectFoto " . $CodStoc . ", " . $i;
			// $pic_index = "Foto_" . $i;

			$wp_upload_dir = wp_get_upload_dir();
			$wp_upload_url = wp_upload_dir();

			check_upload_path( $wp_upload_dir["basedir"]."/manfin_pics/");
			check_upload_path( $wp_upload_dir["basedir"]."/manfin_pics/products/");

			$temp_image_path = check_upload_path( $wp_upload_dir["basedir"]."/manfin_pics/products/" . $sku );
			$temp_image_url = $wp_upload_url["baseurl"]."/manfin_pics/products/" . $sku;

			// Images and Gallery

			// $pdo = $manfin_db->prepare("EXECUTE spWEBSelectFoto 4681, 1");
			$pdo = $manfin_db->prepare("EXECUTE spWEBSelectFoto " . $CodStoc . ", " . $i);

			$pdo->bindColumn(1, $blob_data, PDO::PARAM_STR);
			$pdo->bindColumn(2, $foto_name, PDO::PARAM_STR);
			$pdo->execute(array(1, 2));


			if( $result = $pdo->fetch(PDO::FETCH_BOUND)) {

				if($blob_data != ""){
					$binary_foto = powbyte_hex2bin($blob_data);

					check_upload_path($temp_image_path);

					$filename = $temp_image_path . "/" . $pic_name;

					$file = fopen( $filename , "w" );

					fwrite( $file, $binary_foto);
					fclose( $file);

					$temp_image_url = $temp_image_url. "/" . $pic_name ;

					// echo "<img src='". $temp_image_url ."'>";
					// echo "<br />";

					// Prepare an array of post data for the attachment.
					$post_mime = wp_check_filetype( $pic_name, null );
					$attachment = array(
						'guid'           => $temp_image_url,
						'post_mime_type' => $post_mime["type"],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', $slug ),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);

					/*verifica daca imaginea exista*/
					// $query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE guid='$temp_image_url'";
					// $count = intval($wpdb->get_var($query));

					/*sterge imagine existenta */

					$query = "DELETE FROM {$wpdb->posts} WHERE guid='$temp_image_url'";
					$wpdb->query($query);
					// die();


					// echo "<br /> guid: ";
					// var_dump($temp_image_url);
					// echo "<br /> i: ";
					// var_dump($i);

					// die();

					// if($count == 0){
						// Insert the attachment.
						$attach_id = wp_insert_attachment( $attachment, $filename, $product_id );

						// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
						require_once( ABSPATH . 'wp-admin/includes/image.php' );

						// Generate the metadata for the attachment, and update the database record.

						$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
						wp_update_attachment_metadata( $attach_id, $attach_data );


						// echo '<pre>';
						// print_r($attach_data);
						// var_dump($product);
						// var_dump($attach_id);
						// echo '</pre>';

						if($i != 1){

							if(!isset($gallery_ids)){
								$gallery_ids = array();
							}

							array_push($gallery_ids, $attach_id);

							// echo "<br /> attach id: ";
							// var_dump($attach_id);
							// echo "<br /> i: ";
							// var_dump($i);
						}else{
							$upload_id = $attach_id;
							$gallery_ids = null;
						}
					// }else{
						// $upload_id = null;
						// $gallery_ids = null;
					// }
					// set_post_thumbnail( $product_id, $attach_id );
					// $product->set_image_id( isset( $attach_id ) ? $attach_id : "" );


					// break;
				}
			}
			$pdo->closeCursor();

			// var_dump($result[$pic_index]);

			++$i;
			// sleep(5);
			// break;
		} while($blob_data && $i <= 10);

		// echo "<br /> Gallery ids: ";
		// var_dump($gallery_ids);

		if($upload_id){
			$product->set_image_id( isset( $upload_id ) ? $upload_id : "" );
		}
		if($gallery_ids){
			$product->set_gallery_image_ids( isset( $gallery_ids ) ? $gallery_ids : array() );
		}
		$product->save();
	}



