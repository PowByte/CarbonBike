<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}



	// Utility function that returns the correct product object instance
	function wc_get_product_object_type( $type ){
		// Get an instance of the WC_Product object (depending on his type)
		if( isset($args['type']) && $args['type'] === 'variable' ){
			$product = new WC_Product_Variable();
		} elseif( isset($args['type']) && $args['type'] === 'grouped' ){
			$product = new WC_Product_Grouped();
		} elseif( isset($args['type']) && $args['type'] === 'external' ){
			$product = new WC_Product_External();
		} else {
			$product = new WC_Product_Simple(); // "simple" By default
		}

		if( ! is_a( $product, 'WC_Product' ) )
			return false;
		else
			return $product;
	}


	function powbyte_hex2bin($hex){

			$string='';
			/* PDO + MS Access + ODBC apparently injects a NULL every 255 characters */
			$hex = str_replace("\x00", "", $hex);

			return pack("H*", $hex);
		}

	// Custom function for product creation (For Woocommerce 3+ only)
	function pbt_crud_create_product( $args ){

		global $woocommerce;

		if( ! function_exists('wc_get_product_object_type') && ! function_exists('wc_prepare_product_attributes') )
			return false;

		// Get an empty instance of the product object (defining it's type)
		$product = wc_get_product_object_type( $args['type'] );

		if( ! $product )
			return false;

		// Product name (Title) and slug
		$product->set_name( $args['name'] ); // Name (title).
		if( isset( $args['slug'] ) )
			$product->set_name( $args['slug'] );

		// Description and short description:
		$product->set_description( $args['description'] );
		$product->set_short_description( $args['short_description'] );

		// Status ('publish', 'pending', 'draft' or 'trash')
		$product->set_status( isset($args['status']) ? $args['status'] : 'publish' );

		// Visibility ('hidden', 'visible', 'search' or 'catalog')
		$product->set_catalog_visibility( isset($args['visibility']) ? $args['visibility'] : 'visible' );

		// Featured (boolean)
		$product->set_featured(  isset($args['featured']) ? $args['featured'] : false );

		// Virtual (boolean)
		$product->set_virtual( isset($args['virtual']) ? $args['virtual'] : false );

		// Prices
		$product->set_regular_price( $args['regular_price'] );
		$product->set_sale_price( isset( $args['sale_price'] ) ? $args['sale_price'] : '' );
		$product->set_price( isset( $args['sale_price'] ) ? $args['sale_price'] :  $args['regular_price'] );
		if( isset( $args['sale_price'] ) ){
			$product->set_date_on_sale_from( isset( $args['sale_from'] ) ? $args['sale_from'] : '' );
			$product->set_date_on_sale_to( isset( $args['sale_to'] ) ? $args['sale_to'] : '' );
		}

		// Downloadable (boolean)
		$product->set_downloadable(  isset($args['downloadable']) ? $args['downloadable'] : false );
		if( isset($args['downloadable']) && $args['downloadable'] ) {
			$product->set_downloads(  isset($args['downloads']) ? $args['downloads'] : array() );
			$product->set_download_limit(  isset($args['download_limit']) ? $args['download_limit'] : '-1' );
			$product->set_download_expiry(  isset($args['download_expiry']) ? $args['download_expiry'] : '-1' );
		}

		// Taxes
		if ( get_option( 'woocommerce_calc_taxes' ) === 'yes' ) {
			$product->set_tax_status(  isset($args['tax_status']) ? $args['tax_status'] : 'taxable' );
			$product->set_tax_class(  isset($args['tax_class']) ? $args['tax_class'] : '' );
		}

		// SKU and Stock (Not a virtual product)
		if( isset($args['virtual']) && ! $args['virtual'] ) {
			$product->set_sku( isset( $args['sku'] ) ? $args['sku'] : '' );
			$product->set_manage_stock( isset( $args['manage_stock'] ) ? $args['manage_stock'] : false );
			$product->set_stock_status( isset( $args['stock_status'] ) ? $args['stock_status'] : 'instock' );
			if( isset( $args['manage_stock'] ) && $args['manage_stock'] ) {
				$product->set_stock_status( $args['stock_qty'] );
				$product->set_backorders( isset( $args['backorders'] ) ? $args['backorders'] : 'no' ); // 'yes', 'no' or 'notify'
			}
			$product->set_low_stock_amount(isset( $args['low_stock_amount'] ) ? $args['low_stock_amount'] : 'instock');
		}

		// Sold Individually
		$product->set_sold_individually( isset( $args['sold_individually'] ) ? $args['sold_individually'] : false );

		// Weight, dimensions and shipping class
		$product->set_weight( isset( $args['weight'] ) ? $args['weight'] : '' );
		$product->set_length( isset( $args['length'] ) ? $args['length'] : '' );
		$product->set_width( isset(  $args['width'] ) ?  $args['width']  : '' );
		$product->set_height( isset( $args['height'] ) ? $args['height'] : '' );
		if( isset( $args['shipping_class_id'] ) )
			$product->set_shipping_class_id( $args['shipping_class_id'] );

		// Upsell and Cross sell (IDs)
		$product->set_upsell_ids( isset( $args['upsells'] ) ? $args['upsells'] : '' );
		$product->set_cross_sell_ids( isset( $args['cross_sells'] ) ? $args['upsells'] : '' );

		// Attributes and default attributes
		if( isset( $args['attributes'] ) )
			$product->set_attributes( wc_prepare_product_attributes($args['attributes']) );
		if( isset( $args['default_attributes'] ) )
			$product->set_default_attributes( $args['default_attributes'] ); // Needs a special formatting

		// Reviews, purchase note and menu order
		$product->set_reviews_allowed( isset( $args['reviews_allowed'] ) ? $args['reviews_allowed'] : false );
		$product->set_purchase_note( isset( $args['note'] ) ? $args['note'] : '' );
		if( isset( $args['menu_order'] ) )
			$product->set_menu_order( $args['menu_order'] );

		// Product categories and Tags
		if( isset( $args['category_ids'] ) )
			$product->set_category_ids($args['category_ids']);
		if( isset( $args['tag_ids'] ) )
			$product->set_tag_ids( $args['tag_ids'] );


		// Images and Gallery
		$product->set_image_id( isset( $args['image_id'] ) ? $args['image_id'] : "" );
		$product->set_gallery_image_ids( isset( $args['gallery_ids'] ) ? $args['gallery_ids'] : array() );

		if(isset( $args['custom_fields'] )){
			foreach( $args['custom_fields'] as $meta_key => $meta_value ) {
				$product->update_meta_data( $meta_key , $meta_value );
			}
		}

		// echo "<pre>";
		// print_r($product);
		// var_dump($product->data_store);
		// echo "</pre>";
		// die();

		// echo " ---- args: ";
		// print_r($args['description']);
		// echo " ---- ";

		## --- SAVE PRODUCT --- ##
		$product_id = $product->save();

		// die($product_id);

		return $product_id;
	}


	function pbt_crud_update_product( $args ){

		global $woocommerce;

		if( ! function_exists('wc_get_product_object_type') && ! function_exists('wc_prepare_product_attributes') )
			return false;

		// Get an empty instance of the product object (defining it's type)
		$product = wc_get_product( $args['ID'] );

		if( ! $product )
			return false;

		$product->set_name( $args["post_title"] );
		$product->set_description( $args["description"] );
		$product->set_short_description( $args['short_description'] );

		// Prices
		$product->set_regular_price( $args['regular_price'] );
		$product->set_sale_price( isset( $args['sale_price'] ) ? $args['sale_price'] : '' );
		// $product->set_price( isset( $args['sale_price'] ) ? $args['sale_price'] :  $args['regular_price'] );
		// if( isset( $args['sale_price'] ) ){
		// 	$product->set_date_on_sale_from( isset( $args['sale_from'] ) ? $args['sale_from'] : '' );
		// 	$product->set_date_on_sale_to( isset( $args['sale_to'] ) ? $args['sale_to'] : '' );
		// }

		// SKU and Stock (Not a virtual product)

		$product->set_sku( isset( $args['sku'] ) ? $args['sku'] : '' );
		$product->set_manage_stock( isset( $args['manage_stock'] ) ? $args['manage_stock'] : false );
		$product->set_stock_status( isset( $args['stock_status'] ) ? $args['stock_status'] : 'instock' );
		if( isset( $args['manage_stock'] ) && $args['manage_stock'] ) {
			// var_dump($args['stock_qty']);
			// die();
			// $product->set_stock_status( $args['stock_qty'] );
			/*** DE VERIFICAT EXACT CUM SE UPDATEAZA STOCKUL ***/
			$product->set_stock_quantity( $args['stock_qty'] );
			$product->set_backorders( isset( $args['backorders'] ) ? $args['backorders'] : 'no' ); // 'yes', 'no' or 'notify'
		}
		// $product->set_low_stock_amount(isset( $args['low_stock_amount'] ) ? $args['low_stock_amount'] : 'instock');

		// Weight, dimensions and shipping class
		$product->set_weight( isset( $args['weight'] ) ? $args['weight'] : '' );

		// Reviews, purchase note and menu order
		$product->set_reviews_allowed( isset( $args['reviews_allowed'] ) ? $args['reviews_allowed'] : false );
		$product->set_purchase_note( isset( $args['note'] ) ? $args['note'] : '' );
		if( isset( $args['menu_order'] ) )
			$product->set_menu_order( $args['menu_order'] );


		// Product categories and Tags
		if( isset( $args['category_ids'] ) )
			$product->set_category_ids( $args['category_ids'] );

		// var_dump($args['tag_ids']);
		// die("CRUD");

		if( isset( $args['tag_ids'] ) )
			$product->set_tag_ids( $args['tag_ids'] );

		if(isset( $args['custom_fields'] )){
			foreach( $args['custom_fields'] as $meta_key => $meta_value ) {
				$product->update_meta_data( $meta_key , $meta_value );
			}
		}
		// echo "<pre>";
		// print_r($product);
		// echo "</pre>";
		$product->save();


	}

	// Utility function that prepare product attributes before saving
	function wc_prepare_product_attributes( $attributes ){
		global $woocommerce;

		$data = array();
		$position = 0;

		foreach( $attributes as $taxonomy => $values ){
			if( ! taxonomy_exists( $taxonomy ) )
				continue;

			// Get an instance of the WC_Product_Attribute Object
			$attribute = new WC_Product_Attribute();

			$term_ids = array();

			// Loop through the term names
			foreach( $values['term_names'] as $term_name ){
				if( term_exists( $term_name, $taxonomy ) )
					// Get and set the term ID in the array from the term name
					$term_ids[] = get_term_by( 'name', $term_name, $taxonomy )->term_id;
				else
					continue;
			}

			$taxonomy_id = wc_attribute_taxonomy_id_by_name( $taxonomy ); // Get taxonomy ID

			$attribute->set_id( $taxonomy_id );
			$attribute->set_name( $taxonomy );
			$attribute->set_options( $term_ids );
			$attribute->set_position( $position );
			// $attribute->set_position( $values['position'] );
			$attribute->set_visible( $values['is_visible'] );
			$attribute->set_variation( $values['for_variation'] );

			$data[$taxonomy] = $attribute; // Set in an array

			$position++; // Increase position
		}
		return $data;
	}

	// function upload_picture_from_ManFIN($id, $foto_string, $type, $CodStoc){
	function upload_picture_from_ManFIN($id, $type, $CodStoc){

		$wp_upload_dir = wp_get_upload_dir();
		$wp_upload_url = wp_upload_dir();

		switch($type){

			case "category":

				check_upload_path( $wp_upload_dir["basedir"]."/manfin_pics/");
				check_upload_path( $wp_upload_dir["basedir"]."/manfin_pics/categs/");
				$temp_image_path = check_upload_path( $wp_upload_dir["basedir"]."/manfin_pics/categs" );
				$temp_image_url = $wp_upload_url["baseurl"]."/manfin_pics/categs";

				$image_url = $temp_image_url . '/foto.jpg';
				$file = fopen( $temp_image_path . '/foto.jpg', "w" );

				fwrite( $file, $foto_string);
				fclose( $file);

				// echo "<br>image_url:";
				// var_dump($image_url);


				$image = open_image($temp_image_path . '/foto.jpg');
				if(is_resource($image)) {
					$width = imagesx($image);
					$height = imagesy($image);
					$new_width = 100;
					$new_height = $height * ($new_width/$width);
					$thumb = imagecreatetruecolor($new_width, $new_height);
					imagecopyresampled($thumb, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
					imagejpeg($thumb, $temp_image_path . '/foto.jpg');
				}

				die();

				$attach_id = manfin_upload_img_to_wp($image_url, $product->get_name() . '-' . $i );

				echo "<br>image_url:";
				var_dump($attach_id);
				echo "upload product category";

				break;
			case "product":
				echo "upload product picture";

				$query1="EXECUTE spWEBSelectFoto ".$CodStoc.", 1";

				break;
			default:
				return "case not matched!";
				break;
		}
	}


	function check_upload_path($temp_image_path){
		if ( !is_dir($temp_image_path) ){
			wp_mkdir_p($temp_image_path);
		}
		return $temp_image_path;
	}

	function manfin_get_products_ids() {

		$products_IDs = get_posts( array(
			'post_type' => 'product',
			'numberposts' => -1,
			'post_status' => 'publish',
			'fields' => 'ids',
		));

		return $products_IDs;
	}

	function manfin_get_products_ids_from_category_by_ID( $category_id ) {

		$products_IDs = new WP_Query( array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'fields' => 'ids',
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field' => 'term_id',
					'terms' => $category_id,
					'operator' => 'IN',
				)
			)
		) );

		return $products_IDs;
	}

	function check_if_attach_exists($path){
		global $wpdb;
		$image_src = $path;
		$query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE guid='$image_src'";
		$count = intval($wpdb->get_var($query));

		if($count){
			return true;
		}else{
			return false;
		}
	}




	/* Hook-uri sincronizare comenzi */

	// Add New Order Statuses to WooCommerce

	add_filter( 'wc_order_statuses', 'wpex_wc_add_order_statuses' );
	function wpex_wc_add_order_statuses( $order_statuses ) {
		$order_statuses['wc-confirmed']   = _x( 'Comanda Confirmata', 'WooCommerce Order status', 'pbt_manfin' );
		$order_statuses['wc-update']      = _x( 'Update Comanda', 'WooCommerce Order status', 'pbt_manfin' );
		$order_statuses['wc-pending']     = _x( 'Plata in Asteptare', 'WooCommerce Order status', 'pbt_manfin' );
		$order_statuses['wc-processing']  = _x( 'Comanda in Procesare', 'WooCommerce Order status', 'pbt_manfin' );
		$order_statuses['wc-on-hold']     = _x( 'Comanda in Asteptare', 'WooCommerce Order status', 'pbt_manfin' );
		$order_statuses['wc-completed']   = _x( 'Comanda Finalizata', 'WooCommerce Order status', 'pbt_manfin' );
		$order_statuses['wc-cancelled']   = _x( 'Comanda Anulata', 'WooCommerce Order status', 'pbt_manfin' );
		$order_statuses['wc-refunded']    = _x( 'Comanda Stornata ', 'WooCommerce Order status', 'pbt_manfin' );
		$order_statuses['wc-shipped']     = _x( 'Comanda Expediata', 'WooCommerce Order status', 'pbt_manfin' );
		$order_statuses['wc-failed']      = _x( 'Comanda Esuata', 'WooCommerce Order status', 'pbt_manfin' );
		// $order_statuses['wc-delivery-order-status'] = _x( 'Suntem pe drum catre dvs', 'WooCommerce Order status', 'text_domain' );
		// echo "<pre style='padding-left: 50%; padding-top: 100px'>";
		// print_r($order_statuses);
		// echo "</pre>";
		// die();

		return $order_statuses;
	}

	// Register New Order Statuses
	add_filter( 'init', 'wpex_wc_register_post_statuses' );
	function wpex_wc_register_post_statuses() {
		register_post_status( 'wc-confirmed', array(
				'label'						=> _x( 'Comanda Confirmata', 'WooCommerce Order status', 'pbt_manfin' ),
				'public'					=> true,
				'exclude_from_search'		=> false,
				'show_in_admin_all_list'	=> true,
				'show_in_admin_status_list'	=> true,
				'label_count'				=> _n_noop( 'Comanda Confirmata (%s)', 'Comanda Confirmata (%s)', 'pbt_manfin' )
			) );
		register_post_status( 'wc-update', array(
				'label'						=> _x( 'Update Comanda', 'WooCommerce Order status', 'pbt_manfin' ),
				'public'					=> false,
				'exclude_from_search'		=> false,
				'show_in_admin_all_list'	=> true,
				'show_in_admin_status_list'	=> true,
				'label_count'				=> _n_noop( 'Update Comanda (%s)', 'Update Comanda (%s)', 'pbt_manfin' )
			) );
		register_post_status( 'wc-pending', array(
				'label'						=> _x( 'Plata in Asteptare', 'WooCommerce Order status', 'pbt_manfin' ),
				'public'					=> true,
				'exclude_from_search'		=> false,
				'show_in_admin_all_list'	=> true,
				'show_in_admin_status_list'	=> true,
				'label_count'				=> _n_noop( 'Plata in Asteptare (%s)', 'Plata in Asteptare (%s)', 'pbt_manfin' )
			) );
		register_post_status( 'wc-processing', array(
				'label'						=> _x( 'Comanda in Procesare', 'WooCommerce Order status', 'pbt_manfin' ),
				'public'					=> true,
				'exclude_from_search'		=> false,
				'show_in_admin_all_list'	=> true,
				'show_in_admin_status_list'	=> true,
				'label_count'				=> _n_noop( 'Comanda in Procesare (%s)', 'Comanda in Procesare (%s)', 'pbt_manfin' )
			) );
		register_post_status( 'wc-on-hold', array(
				'label'						=> _x( 'Comanda in Asteptare', 'WooCommerce Order status', 'pbt_manfin' ),
				'public'					=> true,
				'exclude_from_search'		=> false,
				'show_in_admin_all_list'	=> true,
				'show_in_admin_status_list'	=> true,
				'label_count'				=> _n_noop( 'Comanda in Asteptare (%s)', 'Comanda in Asteptare (%s)', 'pbt_manfin' )
			) );
		register_post_status( 'wc-completed', array(
				'label'						=> _x( 'Comanda Finalizata', 'WooCommerce Order status', 'pbt_manfin' ),
				'public'					=> true,
				'exclude_from_search'		=> false,
				'show_in_admin_all_list'	=> true,
				'show_in_admin_status_list'	=> true,
				'label_count'				=> _n_noop( 'Comanda Finalizata (%s)', 'Comanda Finalizata (%s)', 'pbt_manfin' )
			) );
		register_post_status( 'wc-cancelled', array(
				'label'						=> _x( 'Comanda Anulata', 'WooCommerce Order status', 'pbt_manfin' ),
				'public'					=> true,
				'exclude_from_search'		=> false,
				'show_in_admin_all_list'	=> true,
				'show_in_admin_status_list'	=> true,
				'label_count'				=> _n_noop( 'Comanda Anulata (%s)', 'Comanda Anulata (%s)', 'pbt_manfin' )
			) );
		register_post_status( 'wc-refunded', array(
				'label'						=> _x( 'Comanda Stornata', 'WooCommerce Order status', 'pbt_manfin' ),
				'public'					=> true,
				'exclude_from_search'		=> false,
				'show_in_admin_all_list'	=> true,
				'show_in_admin_status_list'	=> true,
				'label_count'				=> _n_noop( 'Comanda Stornata (%s)', 'Comanda Stornata (%s)', 'pbt_manfin' )
			) );
		register_post_status( 'wc-shipped', array(
				'label'						=> _x( 'Comanda Expediata', 'WooCommerce Order status', 'pbt_manfin' ),
				'public'					=> true,
				'exclude_from_search'		=> false,
				'show_in_admin_all_list'	=> true,
				'show_in_admin_status_list'	=> true,
				'label_count'				=> _n_noop( 'Comanda Expediata (%s)', 'Comanda Expediata (%s)', 'pbt_manfin' )
			) );
		register_post_status( 'wc-failed', array(
				'label'						=> _x( 'Comanda Esuata', 'WooCommerce Order status', 'pbt_manfin' ),
				'public'					=> true,
				'exclude_from_search'		=> false,
				'show_in_admin_all_list'	=> true,
				'show_in_admin_status_list'	=> true,
				'label_count'				=> _n_noop( 'Comanda Esuata (%s)', 'Comanda Esuata (%s)', 'pbt_manfin' )
			) );
	}

	add_filter( 'wc_order_is_editable', 'filter_wc_order_is_editable', 10, 2 );
	function filter_wc_order_is_editable( $editable, $order ) {
		// Compare
		if ( $order->get_status() == 'update' ) {
			$editable = true;
		}

		return $editable;
	}

	function PTB_roundUp($number, $nearest){
		return $number + ($nearest - fmod($number, $nearest));
	}

	function pbt_formula_pret($pret){
		/* Aplic TVA */
		/*NumberUtil::round( $line_price - $base_taxes_total + $modded_taxes_total, wc_get_price_decimals() )*/
		$pret = $pret * 1.19;
		$pret_vechi = $pret;
		$pret = number_format( $pret, 0, ".", "");
		if( $pret < 31 ){
			if( $pret_vechi > $pret ){
				$pret = $pret + 0.5;
			}
		}else{
			if( $pret_vechi > $pret ){
				$pret = $pret + 1;
			}
		}
		return $pret;
	}

	function pbt_get_terms_by_CodGrupa($CodGrupa){
		$get_categ_name_args = array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'meta_query' => array(
				 array(
					'key'       => '_CodGrupa',
					'value'     => $CodGrupa,
					'compare'   => '='
				 )
			)
		);
		return get_terms( $get_categ_name_args );
	}


	/**
	 * Method to delete Woo Product
	 *
	 * @param int $id the product ID.
	 * @param bool $force true to permanently delete product, false to move to trash.
	 * @return \WP_Error|boolean
	 */
	function pbt_deleteProduct($id, $force = FALSE){
		$product = wc_get_product($id);

		if(empty($product))
			return new WP_Error(999, sprintf(__('No %s is associated with #%d', 'woocommerce'), 'product', $id));

		// If we're forcing, then delete permanently.
		if ($force)
		{
			if ($product->is_type('variable'))
			{
				foreach ($product->get_children() as $child_id)
				{
					$child = wc_get_product($child_id);
					$child->delete(true);
				}
			}
			elseif ($product->is_type('grouped'))
			{
				foreach ($product->get_children() as $child_id)
				{
					$child = wc_get_product($child_id);
					$child->set_parent_id(0);
					$child->save();
				}
			}

			$product->delete(true);
			$result = $product->get_id() > 0 ? false : true;
		}
		else
		{
			$product->delete();
			$result = 'trash' === $product->get_status();
		}

		if (!$result)
		{
			return new WP_Error(999, sprintf(__('This %s cannot be deleted', 'woocommerce'), 'product'));
		}

		// Delete parent product transients.
		if ($parent_id = wp_get_post_parent_id($id))
		{
			wc_delete_product_transients($parent_id);
		}
		return true;
	}



	function pbt_is_active_param($id, $active_params){
		if(isset($active_params[$id])){
			return "checked";
		}else{
			return null;
		}
	}

	function pbt_redirect() {

        // To make the Coding Standards happy, we have to initialize this.
        if ( ! isset( $_POST['_wp_http_referer'] ) ) { // Input var okay.
            $_POST['_wp_http_referer'] = wp_login_url();
        }

        // Sanitize the value of the $_POST collection for the Coding Standards.
        $url = sanitize_text_field(
            wp_unslash( $_POST['_wp_http_referer'] ) // Input var okay.
        );

        // Finally, redirect back to the admin page.
        wp_safe_redirect( urldecode( $url ) );
        exit;

    }

	function pbt_has_valid_nonce($wp_nonce_name, $action){
		// If the field isn't even in the $_POST, then it's invalid.

        if ( ! isset( $_POST[$wp_nonce_name] ) ) { // Input var okay.
            return false;
        }

        $field  = wp_unslash( $_POST[$wp_nonce_name] );

        return wp_verify_nonce( $field, $action );
	}

	function pbt_save_manfin_params(){

		// First, validate the nonce and verify the user as permission to save.
        if ( ! ( pbt_has_valid_nonce("manfin_params_nonce", "manfin_params_save") && current_user_can( 'manage_options' ) ) ) {
            // TODO: Display an error message.
			echo "Invalid Nonce";
        }

        // If the above are valid, sanitize and save the option.
        if ( null !== wp_unslash( $_POST["manfin_active_params"] ) ) {

            $value = sanitize_text_field(serialize( $_POST["manfin_active_params"] ));
            update_option( "_ManFin_Active_Params", $value );

        }

        pbt_redirect();
	}

	// define the woocommerce_shortcode_products_query callback
	function pbt_filter_woocommerce_shortcode_products_query( $query_args, $atts, $loop_name ) {
		// make filter magic happen here...
		/*output hook : apply_filters( 'woocommerce_shortcode_products_query_results', $results, $this ) */
		if(isset($_GET["debug"])){
			// echo "<pre>";
			// var_dump($loop_name);
			// print_r($query_args);
			// echo "</pre>";
		}
		switch ($loop_name) {
			case "featured_products":
				/*Param Best Deal*/
				unset($query_args["tax_query"]);
				$query_args['meta_query'] = array(
					array(
						'key' => '_MFP_9',
						'value' => 'Da',
					),
					array(
						'key' => '_stock_status',
						'value' => 'instock',
					)
				);
				// $query_args['meta_key'] = '_wc_average_rating'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				// $query_args['order']    = 'DESC';
				// $query_args['orderby']  = 'meta_value_num';
				break;
			default:
				$query_args['meta_query'] = array(
					array('key' => '_stock_status', //meta key name here
						  'value' => 'instock',
					)
				);
			break;
		}
		if(isset($_GET["debug"])){
			echo "<pre>";
			var_dump($loop_name);
			// print_r($query_args);
			echo "</pre>";
		}
		return $query_args;
	};

	// add the filter
	add_filter( 'woocommerce_shortcode_products_query', 'pbt_filter_woocommerce_shortcode_products_query', 10, 3 );

	/**Sortare implicita dupa stoc **/

	add_filter( 'woocommerce_get_catalog_ordering_args', 'pbt_first_sort_by_stock_amount', 9999 );

	function pbt_first_sort_by_stock_amount( $args ) {
	   $args['orderby'] = 'meta_value';
	   $args['order'] = 'ASC';
	   $args['meta_key'] = '_stock_status';
	   return $args;
	}

	/**
	 * @param array $array
	 * @param string $value
	 * @param bool $asc - ASC (true) or DESC (false) sorting
	 * @param bool $preserveKeys
	 * @return array
	 * */
	function sortBySubValue($array, $value, $asc = true, $preserveKeys = false)
	{
		if (is_object(reset($array))) {
			$preserveKeys ? uasort($array, function ($a, $b) use ($value, $asc) {
				return $a->{$value} == $b->{$value} ? 0 : ($a->{$value} <=> $b->{$value}) * ($asc ? 1 : -1);
			}) : usort($array, function ($a, $b) use ($value, $asc) {
				return $a->{$value} == $b->{$value} ? 0 : ($a->{$value} <=> $b->{$value}) * ($asc ? 1 : -1);
			});
		} else {
			$preserveKeys ? uasort($array, function ($a, $b) use ($value, $asc) {
				return $a[$value] == $b[$value] ? 0 : ($a[$value] <=> $b[$value]) * ($asc ? 1 : -1);
			}) : usort($array, function ($a, $b) use ($value, $asc) {
				return $a[$value] == $b[$value] ? 0 : ($a[$value] <=> $b[$value]) * ($asc ? 1 : -1);
			});
		}
		return $array;
	}

	// Add new stock status options
	function filter_woocommerce_product_stock_status_options( $status ) {
		// Add new statuses
		$status['unavailable'] = __( 'Unavailable', 'woocommerce' );

		return $status;
	}
	add_filter( 'woocommerce_product_stock_status_options', 'filter_woocommerce_product_stock_status_options', 10, 1 );

	// Availability text
	function filter_woocommerce_get_availability_text( $availability, $product ) {
		switch( $product->get_stock_status() ) {
			case 'unavailable':
				$availability = __( 'Unavailable', 'woocommerce' );
			break;
		}

		return $availability;
	}
	add_filter( 'woocommerce_get_availability_text', 'filter_woocommerce_get_availability_text', 10, 2 );

	// Availability class
	function filter_woocommerce_get_availability_class( $class, $product ) {
		switch( $product->get_stock_status() ) {
			case 'unavailable':
				$class = 'not-available';
			break;
		}

		return $class;
	}
	add_filter( 'woocommerce_get_availability_class', 'filter_woocommerce_get_availability_class', 10, 2 );
?>