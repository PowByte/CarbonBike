<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_filter( "woocommerce_display_product_attributes", "manfin_custom_attributes" , 10, 2 );
function manfin_custom_attributes($product_attributes, $product){		

	if(isset($product_attributes["weight"])) unset($product_attributes["weight"]);
	
	$product_id = $product->get_id();
	$product_active_params = unserialize(get_post_meta($product_id, "_ManFin_Active_Product_Params", true));
	if(is_array($product_active_params)){
		foreach($product_active_params as $product_active_param ){
			
			$product_attributes[ 'attribute_' . $product_active_param["term_name"] ] = array(
				'label' => __($product_active_param["term_name"]),
				'value' => __($product_active_param["term_value"]),
			);
		}
	}
		// var_dump($product_attributes);
		
	return $product_attributes;
}


	
add_filter( 'woocommerce_product_tabs', "manfin_check_product_tabs" , 10 );
function manfin_check_product_tabs($product_tabs){
	
	// echo "<pre>";
	// print_r($product_tabs);
	// echo "</pre>";
	
	if( !isset($product_tabs["additional_information"]) ){
		$product_tabs["additional_information"] = array(
            "title" 	=> __("Informații suplimentare"),
            "priority" 	=> 20,
            "callback"	=> "woocommerce_product_additional_information_tab"
        );
	}
	
	return $product_tabs;
}
?>