<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);

	function manfin_update_product($CodStoc){

		echo "<pre>";
		print_r(manfin_update_product_helper($CodStoc));
		echo "</pre>";

	}

	function manfin_delete_product_helper(){
		global $manfin_db;

		$query = "EXECUTE spWEBMVStocuriIDToate";



		try {
			$pdo = $manfin_db->prepare($query);
			$pdo->execute();
		}catch (Exception $e) {
			echo "Nu se poate conecta la ManFin: ". $e;
		}

		$coduri_stocuri = array();

		while($row_CodStoc  = $pdo->fetch()) {

			$coduri_stocuri[] = $row_CodStoc['CodStoc'];

		}
		$pdo->closeCursor();

		$args = array(
			'limit'     	=> -1,
			'meta_key'     	=> '_wp_manfin_id',
			'meta_value' 	=>  $coduri_stocuri,
			'meta_compare' 	=> 'NOT IN',
			// 'orderby' 		=> 'meta_value',
			// 'order' 		=> 'DESC',
		);

		$products_to_delete = wc_get_products( $args );

		// $products_to_delete = new WP_Query($params);
		// global $post, $product;

		if( is_array ( $products_to_delete ) ) {
			echo "<table>";
			foreach ( $products_to_delete as $product ) {

				if(isset($_GET["manfin-delete-products"]) && $_GET["manfin-delete-products"]=="yes"){
					echo "sterge";
					pbt_deleteProduct($product->get_id()); //1766
					$show_message = "01";
				}

				echo "<tr>";

					echo "<td width='80'>". $product->get_meta("_wp_manfin_id") . "</td>";
					echo "<td><a href='". get_admin_url() ."post.php?post=".$product->get_id() ."&action=edit' target='_blank'>" . $product->get_name() . "</a></td>";
					echo "<td width='80' align='center'>" .  $product->get_catalog_visibility() . "</td>";

				echo "<tr>";

			} // end while
			echo "</table>";
			if(isset($show_message)){
				wp_redirect( admin_url("admin.php?page=pbt-manfin-del-products&pbtmsg=".$show_message ) );
			}

		}else{
			echo "nothing found";
		}

		wp_reset_postdata();
	}


	function manfin_update_products_img(){
		/*/Update Pictures/*/
		$products_ids = manfin_get_products_ids();

		echo "<pre>";
		print_r($products_ids);
		echo "</pre>";

		manfin_importpictures(2495);
	}

	function manfin_update_products_cats(){

		global $manfin_db;
		$all_product_categs_ids_list = array();

		/* update clase */

		$pdo = $manfin_db->prepare("EXECUTE spWEBMVClaseStocuri");
		$pdo->execute();
		while ($row_clase = $pdo->fetch(PDO::FETCH_ASSOC)) {
			if(isset($term_meta_CodClasa)){
				unset($term_meta_CodClasa);
			}
			echo "<pre>";
			// if($row_clase['CodClasa'] == 8){
				$meta_args = array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
					'meta_query' => array(
					 	array(
							'key'       => '_CodClasa',
							'value'     => $row_clase['CodClasa'],
							'compare'   => '='
					 	)
					)
				);

				$current_terms = get_terms( $meta_args );

				if(is_array($current_terms) && !empty($current_terms)){
					$current_term = $current_terms[0];
				}else{
					$current_term = "";
				}

				if(is_object ($current_term)){
					$current_term_id = $current_term->term_id;
					$term_meta_CodClasa = get_term_meta($current_term_id, '_CodClasa', true);
				}else{
					$current_term_id = '';
					// $term_meta_CodClasa = '';
				}

				$product_cat_args = array(
					'cat_ID'               => $current_term_id,
					'taxonomy'             => 'product_cat',
					'cat_name'             => $row_clase['DenumireClasa'],
					'category_description' => $row_clase['Descriere'],
					'category_nicename'    => $row_clase['DenumireClasa']
				);
				// echo "<pre>";
				// print_r($product_cat_args);
				// echo "<pre>";
				/*insert or update the category*/
				wp_insert_category($product_cat_args);


				if(empty($current_term_id)){

					$inserted_term = get_term_by("name", $row_clase['DenumireClasa'],  'product_cat');
					$current_term_id = $inserted_term->term_id;
				}

				if( !isset($term_meta_CodClasa) ){
					/*save Cod Clasa to Category*/
					add_term_meta( $current_term_id, '_CodClasa', $row_clase['CodClasa'], true );
				}
				// $all_product_categs_ids_list[] =  $current_term_id;
				// upload_picture_from_ManFIN($current_term_id, $row_clase['6'], "category");
				echo $row_clase['DenumireClasa'] . " - Updated ...";
			// }
			echo "</pre>";// break;
			// delete_term_meta($term_id, '_CodClasa', $row_clase['CodClasa']);
		}
		echo "<b>Clase sincronizate</b> <br/> <br/>";

		$pdo->closeCursor();
		// exit;

		/* update grupe */

		$pdo = $manfin_db->prepare("EXECUTE spWEBMVGrupeStocuri");
		$pdo->execute();

		while ($row_grupe = $pdo->fetch(PDO::FETCH_ASSOC)) {
			if(isset($term_meta_CodGrupa)){
				unset($term_meta_CodGrupa);
			}
			echo "<pre>";
			// cod grupa 75 : zapada
			// print_r($row_grupe);
			// echo "</pre>";
			// if($row_grupe['CodGrupa'] == 44){
				$meta_args = array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
					'meta_query' => array(
					 	array(
							'key'       => '_CodGrupa',
							'value'     => $row_grupe['CodGrupa'],
							'compare'   => '='
					 	)
					)
				);

				$current_terms = get_terms( $meta_args );
				if(!empty($current_terms)){
					$current_term = $current_terms[0];
				}else{
					$current_term = "";
				}

				// print_r($current_term);

				if(is_object ($current_term)){
					$current_term_id = $current_term->term_id;
					$term_meta_CodGrupa = get_term_meta($current_term_id, '_CodGrupa', true);
				}else{
					$current_term_id = '';
					// $term_meta_CodClasa = '';
				}

				$parent_meta_args = array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
					'meta_query' => array(
					 	array(
							'key'       => '_CodClasa',
							'value'     => $row_grupe['CodClasa'],
							'compare'   => '='
					 	)
					)
				);
				$parent_meta_args = get_terms( $parent_meta_args );

				if(!empty($parent_meta_args)){
					$parent_current_term = $parent_meta_args[0];
				}else{
					$parent_current_term = "";
				}

				if(is_object ($parent_current_term)){
					$parent_current_term_id = $parent_current_term->term_id;
				}else{
					$parent_current_term_id = '';
				}
				$check_name_args = array(
					'taxonomy'   => 'product_cat',
					'parent'     => 0,
					'hide_empty' => false,
					'name' 		 => $row_grupe['DenumireGrupa'],
					'include '	 => $parent_current_term_id
				);

				$check_name = get_terms( $check_name_args );

				if(is_array($check_name) && !empty($check_name)){
					$slug = $row_grupe['CodGrupa'] . "-" . $row_grupe['DenumireGrupa'];
					// echo "<pre>";
					// print_r($check_name);
					// echo "<pre>";
					// die($extra_slug);
				}else{
					$slug = $row_grupe['DenumireGrupa'];
				}
				$product_cat_args = array(
					'cat_ID'               => $current_term_id,
					'taxonomy'             => 'product_cat',
					'cat_name'             => $row_grupe['DenumireGrupa'],
					'category_description' => $row_grupe['Descriere'],
					'category_nicename'    => $slug,
					'category_parent'      => $parent_current_term_id
				);
			// }
			// print_r($product_cat_args);
			// die($slug);

			wp_insert_category($product_cat_args);


			if(empty($current_term_id)){

				$inserted_term = get_term_by("slug", $slug,  'product_cat');
				$current_term_id = $inserted_term->term_id;
			}

			// var_dump($term_meta_CodGrupa);

			if( !isset($term_meta_CodGrupa)){
				/*save Cod Grupa to Category*/
				add_term_meta( $current_term_id, '_CodGrupa', $row_grupe['CodGrupa'], true );
			}

			// wp_update_term($current_term_id, 'product_cat', array('slug' => $row_grupe['DenumireGrupa']));

			// $all_product_categs_ids_list[] =  $current_term_id;

			echo $row_grupe['DenumireGrupa'] . " - Updated ...";
			echo "</pre>";

			// delete_term_meta($term_id, '_CodGrupa', $row_grupe['CodGrupa']);
			// break;
		}
		echo "<b>Grupe sincronizate</b>";
		/* end update category */
	}







// exit;
?>