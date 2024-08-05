<?php
/***sincronizare clienti***/
/***

woocommerce_checkout_posted_data
https://wordpress.stackexchange.com/questions/353923/woocommerce-conditional-required-checkout-fields

woocommerce_after_checkout_validation
https://stackoverflow.com/questions/50495397/custom-checkout-fields-validation-in-woocommerce-3?rq=1

jquery sample
https://stackoverflow.com/questions/46299376/woocommerce-conditional-custom-checkout-fields

https://stackoverflow.com/questions/50495397/custom-checkout-fields-validation-in-woocommerce-3?rq=1

conditional fields checkout:
https://calebburks.com/conditionally-showhide-woocommerce-checkout-fields/


declar campurile PJ optional si apoi daca billing_type este PF, folosesc woocommerce_after_save_address_validation sa afisez obligatoriu
https://stackoverflow.com/questions/49012380/conditional-custom-checkout-fields-validation-issue-in-woocommerce

***/
		
			 
	// add the action 
	// add_action( 'woocommerce_after_save_address_validation', 'manfin_woocommerce_after_save_address_validation', 10, 4 ); 
	// define the woocommerce_after_save_address_validation callback 
	function manfin_woocommerce_after_save_address_validation( $user_id, $load_address, $address, $customer ) { 
		// make action magic happen here... 
		echo "<pre>";
		print_r($customer->get_meta('billing_type', true));
		if($customer->get_meta('billing_type', true) == "PF"){
			
		}
		echo "</pre>";
		die();
	}; 
	
	add_filter('woocommerce_billing_fields', 'custom_woocommerce_billing_fields',  10, 2 );

	function custom_woocommerce_billing_fields($fields){

		$fields['billing_type'] = array(
			'label' 		=> __('Forma Juridica', 'woocommerce'), // Add custom field label
			// 'placeholder' 	=> _x('Selecteaza', 'placeholder', 'woocommerce'), // Add custom field placeholder
			'required' 		=> true, // if field is required or not
			'priority' 		=> 29,
			'clear'			=> true, // add clear or not
			'type' 			=> 'select', // add field type
			// 'class'			=> array('company-field'),    // add class name
			'options'		=>array(
								'PF' 	=> 'Persoana Fizica',
								'PJ' 	=> 'Persoana Juridica'
							)
		);
		$fields['billing_cui'] = array(
			'label' => __('CUI', 'woocommerce'), // Obligatoriu
			'placeholder' => _x('Cod Fiscal', 'placeholder', 'woocommerce'), // Add custom field placeholder
			'required' => false, // if field is required or not
			'priority' => 31,
			'clear' => false, // add clear or not
			'type' => 'text', // add field type
			'class' => array('form-row-first', 'company-field', 'company-hide')    // add class name
		);
		$fields['billing_regcom'] = array(
			'label' => __('Nr. registrul comertului', 'woocommerce'), // Optional
			// 'placeholder' => _x('Cod Fiscal', 'placeholder', 'woocommerce'), // Add custom field placeholder
			'required' => false, // if field is required or not
			'priority' => 32,
			'clear' => true, // add clear or not
			'type' => 'text', // add field type
			'class' =>array('form-row-last', 'company-field', 'company-hide')    // add class name
		);
		
		$fields['billing_email']['priority'] = 21;
		$fields['billing_email']['class'] = array('form-row-first');
		
		$fields['billing_phone']['priority'] = 22;
		$fields['billing_phone']['class'] = array('form-row-last');
		
		$fields['billing_company']['class'] = array('company-field', 'company-hide');
		
		
		$fields['billing_first_name']['priority'] = 20;
		$fields['billing_first_name']['class'] = array( 'form-row-last', );
		$fields['billing_last_name']['priority'] = 10;
		$fields['billing_last_name']['class'] = array('form-row-first');
		
		
		// $fields['billing_phone'] = array(
			// 'label' => __('Telefon', 'woocommerce'), // Add custom field label
			// 'required' => true, // if field is required or not
			// 'priority' => 22,
			// 'clear' => true, // add clear or not
			// 'type' => 'text', // add field type
			// 'class' => array('form-row-last')    // add class name
		// );
		
		// billing_city
		echo "<pre>";
		// print_r($fields);
		echo "</pre>";
		
		return $fields;
	}
	
	// PHP: Remove "(optional)" from our non required fields
	add_filter( 'woocommerce_form_field' , 'remove_checkout_optional_fields_label', 10, 4 );
	function remove_checkout_optional_fields_label( $field, $key, $args, $value ) {
		if( in_array( $key, array('billing_company', 'billing_cui'))){
		
			// Only on checkout page
			// if( is_checkout() && ! is_wc_endpoint_url() ) {
				$optional = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
				$required = '&nbsp;<abbr class="required" title="' . esc_html__( 'optional', 'woocommerce' ) . '">*</abbr>';
				$field = str_replace( $optional, $required, $field );
			// }
				
		}
		// var_dump($key);
		// var_dump($field);
		return $field;
	}
	
	// define the woocommerce_process_myaccount_field_<key> callback 
	function filter_woocommerce_process_myaccount_field_key( $post_key ) { 
		// make filter magic happen here... 
		var_dump($post_key);
		die();
		return $post_key; 
	}; 
			 
	// add the filter 
	// add_filter( "woocommerce_process_myaccount_field_billing_city", 'filter_woocommerce_process_myaccount_field_key', 10, 1 );
	// add_filter( "woocommerce_process_myaccount_field_billing_city", 'filter_woocommerce_process_myaccount_field_key', 10, 1 );
	
	// define the woocommerce_customer_save_address callback 
	
	// do_action( 'woocommerce_new_customer', $customer->get_id(), $customer );
	
	// do_action( 'woocommerce_update_customer', $customer->get_id(), $customer );
	
	add_action('personal_options_update', 'update_extra_profile_fields');
 
	function update_extra_profile_fields($user_id) {
		global $manfin_db;
		$customer = new WC_Customer( $user_id );
		
		$FormaJuridica = $customer->get_meta('billing_type', true);
		
		if($FormaJuridica == "PJ"){
			$DenumirePartener = $customer->get_billing_company();
			$CodFiscal = $customer->get_meta('billing_cui', true);
			$NrRegCom = $customer->get_meta('billing_regcom', true) ? $customer->get_meta('billing_regcom', true) : '';
		}else{
			$DenumirePartener = $customer->get_billing_last_name() . ' ' . $customer->get_billing_first_name();
			$CodFiscal = '#Temporar' . $user_id;
			$NrRegCom = '';
		}
		
		$CodFiscal = "FN7893";
		
		$Localitati = unserialize(get_option("_ManFin_Localitati"));		
		
		$shipping_city = $billing_city = $customer->get_billing_city();
		$shipping_state = $billing_state = $customer->get_billing_state();
		$Localitati_Curente_billing = $Localitati[$billing_state];
		$CodLocalitateAdresaLivrare = $CodLocalitateAdresaLivrare = $CodLocalitate = array_search ($billing_city , $Localitati_Curente_billing);
		
		$AdresaPartener1 	= $customer->get_billing_address_1();
		$AdresaPartener2 	= $customer->get_billing_address_2();
		$AdresaLivrare 		= $AdresaPartener1 . ' ' .$AdresaPartener2;
		$Telefon1 			= $customer->get_billing_phone();
		$Email 				= $customer->get_billing_email();
		
		// if($load_address == "shipping"){
			/***shipping***/
			// echo "shipping<br />";
			if($customer->get_shipping_city()) $shipping_city = $customer->get_shipping_city();
			if($customer->get_shipping_city()){
				$shipping_state =  $customer->get_shipping_state();
				$Localitati_Curente_shipping = $Localitati[$shipping_state];
				$CodLocalitateAdresaLivrare = array_search ($shipping_city , $Localitati_Curente_shipping);
			} 
			if($customer->get_shipping_address_1()) $AdresaLivrare =  $customer->get_shipping_address_1();
			if($customer->get_shipping_address_2()) $AdresaLivrare =   $AdresaLivrare . ', ' . $customer->get_shipping_address_2();	
		// }
				
		if( get_user_meta($user_id, "_ManFin_CodParterner" ) || true ){
			/** Client Sincronizat cu CodPartener ManFin - Trimite Update **/
			
			$CodPartener = $customer->get_meta('_ManFin_CodParterner', true);
			$CodPartener = 7893;
			
			if($FormaJuridica == "PF"){	
				$CodFiscal   =  'FN'.$CodPartener;
			}
			
			$query = "EXECUTE spWEBMVParteneriUpdate
						@CodPartener = '$CodPartener',
						@DenumirePartener='$DenumirePartener',
						@NrRegCom='$NrRegCom',
						@CodFiscal='$CodFiscal',
						@CodObiectActivitate='1',
						@CodLocalitate='$CodLocalitate',
						@AdresaPartener1='$AdresaPartener1',
						@AdresaPartener2='$AdresaPartener2',
						@Telefon1='$Telefon1',
						@Telefon2='',
						@Fax1='',
						@Fax2='' ,
						@Email='$Email',
						@Web='',
						@Observatii='',
						@CodLocalitateAdresaLivrare='$CodLocalitateAdresaLivrare',
						@AdresaLivrare='$AdresaLivrare',
						@CodAgentVanzare='-1'";
							
			echo "<pre>";
			print_r($query);
			echo "</pre>";
			die();
			
			try {
				$pdo = $manfin_db->prepare($query);					
				$pdo->execute();
				$pdo->closeCursor();
			}catch (Exception $e) {
				if($FormaJuridica == "PJ"){	
					/* notifica userul ca firmal este deja inregistrata - contacteaza admin */
				}else{
					
					$DenumirePartener = $DenumirePartener .' #'. $CodPartener;					
					$query = "EXECUTE spWEBMVParteneriUpdate
						@CodPartener = '$CodPartener',
						@DenumirePartener='$DenumirePartener',
						@NrRegCom='$NrRegCom',
						@CodFiscal='$CodFiscal',
						@CodObiectActivitate='1',
						@CodLocalitate='$CodLocalitate',
						@AdresaPartener1='$AdresaPartener1',
						@AdresaPartener2='$AdresaPartener2',
						@Telefon1='$Telefon1',
						@Telefon2='',
						@Fax1='',
						@Fax2='' ,
						@Email='$Email',
						@Web='',
						@Observatii='',
						@CodLocalitateAdresaLivrare='$CodLocalitateAdresaLivrare',
						@AdresaLivrare='$AdresaLivrare',
						@CodAgentVanzare='-1'";
					$pdo = $manfin_db->prepare($query);				
					$pdo->execute();
					$pdo->closeCursor();
				}
			}
		}else{
			/** Client  NeSincronizat fara CodPartener ManFin - Trimite Insert  **/
			$query = "EXECUTE spWEBMVParteneriInsert
						@DenumirePartener='$DenumirePartener',
						@NrRegCom='$NrRegCom',
						@CodFiscal='$CodFiscal',
						@CodObiectActivitate='1',
						@CodLocalitate='$CodLocalitate',
						@AdresaPartener1='$AdresaPartener1',
						@AdresaPartener2='$AdresaPartener2',
						@Telefon1='$Telefon1',
						@Telefon2='',
						@Fax1='',
						@Fax2='' ,
						@Email='$Email',
						@Web='',
						@Observatii='',
						@CodLocalitateAdresaLivrare='$CodLocalitateAdresaLivrare',
						@AdresaLivrare='$AdresaLivrare',
						@CodAgentVanzare='-1'";
				// echo "<pre>";
				// print_r($query);
				// echo "</pre>";
				// die();
							
				$pdo = $manfin_db->prepare($query);	
				$pdo->bindColumn(1, $CodPartener, PDO::PARAM_INT);
				$pdo->bindColumn(2, $exista, PDO::PARAM_STR);
				$pdo->bindColumn(3, $DenumireaExista, PDO::PARAM_STR); // 0 si 1 daca e 1, trimit update cu denumire plus FN
				// $pdo->execute(array(1));	
				// $pdo->execute(array(1, 2));	
				
				// $pdo->execute();	
				$pdo->execute(array(1, 2, 3));	
				
				// var_dump($pdo);
				
				if( $row_partener  = $pdo->fetch(PDO::FETCH_BOUND)) {
						// if( $row_partener  = $pdo->fetch() ) {
						// if( $row_partener  = $pdo->fetchAll() ) {
						
						// echo "<pre>";
						// print_r($row_partener);
						// echo "</pre>";
						// echo "<pre>";
						// echo "Cod Partener : ". $CodPartener;
						// echo "</pre>";
						// echo "<pre>";
						// echo "Existent: ". $exista;
						// echo "</pre>";
						// echo "<pre>";
						// echo "DenumireaExista: ". $DenumireaExista;
						// echo "</pre>";
						// var_dump($CodPartener);
						// var_dump($exista);			
						$pdo->closeCursor();
						
						update_user_meta( $user_id, "_ManFin_CodParterner", $CodPartener); 
						
						if( $FormaJuridica == "PF" ){
							$CodFiscal = 'FN'.$CodPartener;
							if($DenumireaExista == 1 ) $DenumirePartener = $DenumirePartener .' #'. $CodPartener;
							// $DenumirePartener = $DenumirePartener . "-TEST"; // test modificare denumire
							
							$query = "EXECUTE spWEBMVParteneriUpdate
										@CodPartener = '$CodPartener',
										@DenumirePartener='$DenumirePartener',
										@NrRegCom='$NrRegCom',
										@CodFiscal='$CodFiscal',
										@CodObiectActivitate='1',
										@CodLocalitate='$CodLocalitate',
										@AdresaPartener1='$AdresaPartener1',
										@AdresaPartener2='$AdresaPartener2',
										@Telefon1='$Telefon1',
										@Telefon2='',
										@Fax1='',
										@Fax2='' ,
										@Email='$Email',
										@Web='',
										@Observatii='',
										@CodLocalitateAdresaLivrare='$CodLocalitateAdresaLivrare',
										@AdresaLivrare='$AdresaLivrare',
										@CodAgentVanzare='-1'";							
							echo "<pre>";
							print_r($query);
							echo "</pre>";
							die();
							try {
								$pdo = $manfin_db->prepare($query);				
								$pdo->execute();
								$pdo->closeCursor();
							} catch (Exception $e) {
								$DenumirePartener = $DenumirePartener .' #'. $CodPartener;					
								$query = "EXECUTE spWEBMVParteneriUpdate
									@CodPartener = '$CodPartener',
									@DenumirePartener='$DenumirePartener',
									@NrRegCom='$NrRegCom',
									@CodFiscal='$CodFiscal',
									@CodObiectActivitate='1',
									@CodLocalitate='$CodLocalitate',
									@AdresaPartener1='$AdresaPartener1',
									@AdresaPartener2='$AdresaPartener2',
									@Telefon1='$Telefon1',
									@Telefon2='',
									@Fax1='',
									@Fax2='' ,
									@Email='$Email',
									@Web='',
									@Observatii='',
									@CodLocalitateAdresaLivrare='$CodLocalitateAdresaLivrare',
									@AdresaLivrare='$AdresaLivrare',
									@CodAgentVanzare='-1'";
								$pdo = $manfin_db->prepare($query);				
								$pdo->execute();
								$pdo->closeCursor();
							}
						}
				}else{
					/**Add WP Noticess**/
				}
		}
		// die();
	}
	
	add_action( 'woocommerce_customer_save_address', 'manfin_woocommerce_customer_save_address', 10, 2 ); 
	// add the action 
	
	// add_action( 'woocommerce_update_customer', 'action_woocommerce_customer_save_address', 10, 2 ); 
	
	function manfin_woocommerce_customer_save_address( $user_id, $load_address ) { 
		// make action magic happen here... 
		

		global $manfin_db;
		$customer = new WC_Customer( $user_id );
		
		$FormaJuridica = $customer->get_meta('billing_type', true);
		
		if($FormaJuridica == "PJ"){
			$DenumirePartener = $customer->get_billing_company();
			$CodFiscal = $customer->get_meta('billing_cui', true);
			$NrRegCom = $customer->get_meta('billing_regcom', true) ? $customer->get_meta('billing_regcom', true) : '';
		}else{
			$DenumirePartener = $customer->get_billing_last_name() . ' ' . $customer->get_billing_first_name();
			$CodFiscal = '#Temporar' . $user_id;
			$NrRegCom = '';
		}
		
		$CodFiscal = "FN7893";
		
		$Localitati = unserialize(get_option("_ManFin_Localitati"));		
		
		$shipping_city = $billing_city = $customer->get_billing_city();
		$shipping_state = $billing_state = $customer->get_billing_state();
		$Localitati_Curente_billing = $Localitati[$billing_state];
		$CodLocalitateAdresaLivrare = $CodLocalitateAdresaLivrare = $CodLocalitate = array_search ($billing_city , $Localitati_Curente_billing);
		
		$AdresaPartener1 	= $customer->get_billing_address_1();
		$AdresaPartener2 	= $customer->get_billing_address_2();
		$AdresaLivrare 		= $AdresaPartener1 . ' ' .$AdresaPartener2;
		$Telefon1 			= $customer->get_billing_phone();
		$Email 				= $customer->get_billing_email();
		
		// if($load_address == "shipping"){
			/***shipping***/
			// echo "shipping<br />";
			if($customer->get_shipping_city()) $shipping_city = $customer->get_shipping_city();
			if($customer->get_shipping_city()){
				$shipping_state =  $customer->get_shipping_state();
				$Localitati_Curente_shipping = $Localitati[$shipping_state];
				$CodLocalitateAdresaLivrare = array_search ($shipping_city , $Localitati_Curente_shipping);
			} 
			if($customer->get_shipping_address_1()) $AdresaLivrare =  $customer->get_shipping_address_1();
			if($customer->get_shipping_address_2()) $AdresaLivrare =   $AdresaLivrare . ', ' . $customer->get_shipping_address_2();	
		// }
				
		if( get_user_meta($user_id, "_ManFin_CodParterner" ) || true ){
			/** Client Sincronizat cu CodPartener ManFin - Trimite Update **/
			
			$CodPartener = $customer->get_meta('_ManFin_CodParterner', true);
			$CodPartener = 7893;
			
			if($FormaJuridica == "PF"){	
				$CodFiscal   =  'FN'.$CodPartener;
			}
			
			$query = "EXECUTE spWEBMVParteneriUpdate
						@CodPartener = '$CodPartener',
						@DenumirePartener='$DenumirePartener',
						@NrRegCom='$NrRegCom',
						@CodFiscal='$CodFiscal',
						@CodObiectActivitate='1',
						@CodLocalitate='$CodLocalitate',
						@AdresaPartener1='$AdresaPartener1',
						@AdresaPartener2='$AdresaPartener2',
						@Telefon1='$Telefon1',
						@Telefon2='',
						@Fax1='',
						@Fax2='' ,
						@Email='$Email',
						@Web='',
						@Observatii='',
						@CodLocalitateAdresaLivrare='$CodLocalitateAdresaLivrare',
						@AdresaLivrare='$AdresaLivrare',
						@CodAgentVanzare='-1'";
							
			// echo "<pre>";
			// print_r($query);
			// echo "</pre>";
			// die();
			
			try {
				$pdo = $manfin_db->prepare($query);					
				$pdo->execute();
				$pdo->closeCursor();
			}catch (Exception $e) {
				if($FormaJuridica == "PJ"){	
					/* notifica userul ca firmal este deja inregistrata - contacteaza admin */
				}else{
					
					$DenumirePartener = $DenumirePartener .' #'. $CodPartener;					
					$query = "EXECUTE spWEBMVParteneriUpdate
						@CodPartener = '$CodPartener',
						@DenumirePartener='$DenumirePartener',
						@NrRegCom='$NrRegCom',
						@CodFiscal='$CodFiscal',
						@CodObiectActivitate='1',
						@CodLocalitate='$CodLocalitate',
						@AdresaPartener1='$AdresaPartener1',
						@AdresaPartener2='$AdresaPartener2',
						@Telefon1='$Telefon1',
						@Telefon2='',
						@Fax1='',
						@Fax2='' ,
						@Email='$Email',
						@Web='',
						@Observatii='',
						@CodLocalitateAdresaLivrare='$CodLocalitateAdresaLivrare',
						@AdresaLivrare='$AdresaLivrare',
						@CodAgentVanzare='-1'";
					$pdo = $manfin_db->prepare($query);				
					$pdo->execute();
					$pdo->closeCursor();
				}
			}
		}else{
			/** Client  NeSincronizat fara CodPartener ManFin - Trimite Insert  **/
			$query = "EXECUTE spWEBMVParteneriInsert
						@DenumirePartener='$DenumirePartener',
						@NrRegCom='$NrRegCom',
						@CodFiscal='$CodFiscal',
						@CodObiectActivitate='1',
						@CodLocalitate='$CodLocalitate',
						@AdresaPartener1='$AdresaPartener1',
						@AdresaPartener2='$AdresaPartener2',
						@Telefon1='$Telefon1',
						@Telefon2='',
						@Fax1='',
						@Fax2='' ,
						@Email='$Email',
						@Web='',
						@Observatii='',
						@CodLocalitateAdresaLivrare='$CodLocalitateAdresaLivrare',
						@AdresaLivrare='$AdresaLivrare',
						@CodAgentVanzare='-1'";
				// echo "<pre>";
				// print_r($query);
				// echo "</pre>";
				// die();
							
				$pdo = $manfin_db->prepare($query);	
				$pdo->bindColumn(1, $CodPartener, PDO::PARAM_INT);
				$pdo->bindColumn(2, $exista, PDO::PARAM_STR);
				$pdo->bindColumn(3, $DenumireaExista, PDO::PARAM_STR); // 0 si 1 daca e 1, trimit update cu denumire plus FN
				// $pdo->execute(array(1));	
				// $pdo->execute(array(1, 2));	
				
				// $pdo->execute();	
				$pdo->execute(array(1, 2, 3));	
				
				// var_dump($pdo);
				
				if( $row_partener  = $pdo->fetch(PDO::FETCH_BOUND)) {
						// if( $row_partener  = $pdo->fetch() ) {
						// if( $row_partener  = $pdo->fetchAll() ) {
						
						// echo "<pre>";
						// print_r($row_partener);
						// echo "</pre>";
						// echo "<pre>";
						// echo "Cod Partener : ". $CodPartener;
						// echo "</pre>";
						// echo "<pre>";
						// echo "Existent: ". $exista;
						// echo "</pre>";
						// echo "<pre>";
						// echo "DenumireaExista: ". $DenumireaExista;
						// echo "</pre>";
						// var_dump($CodPartener);
						// var_dump($exista);			
						$pdo->closeCursor();
						
						update_user_meta( $user_id, "_ManFin_CodParterner", $CodPartener); 
						
						if( $FormaJuridica == "PF" ){
							$CodFiscal = 'FN'.$CodPartener;
							if($DenumireaExista == 1 ) $DenumirePartener = $DenumirePartener .' #'. $CodPartener;
							// $DenumirePartener = $DenumirePartener . "-TEST"; // test modificare denumire
							
							$query = "EXECUTE spWEBMVParteneriUpdate
										@CodPartener = '$CodPartener',
										@DenumirePartener='$DenumirePartener',
										@NrRegCom='$NrRegCom',
										@CodFiscal='$CodFiscal',
										@CodObiectActivitate='1',
										@CodLocalitate='$CodLocalitate',
										@AdresaPartener1='$AdresaPartener1',
										@AdresaPartener2='$AdresaPartener2',
										@Telefon1='$Telefon1',
										@Telefon2='',
										@Fax1='',
										@Fax2='' ,
										@Email='$Email',
										@Web='',
										@Observatii='',
										@CodLocalitateAdresaLivrare='$CodLocalitateAdresaLivrare',
										@AdresaLivrare='$AdresaLivrare',
										@CodAgentVanzare='-1'";							
							// echo "<pre>";
							// print_r($query);
							// echo "</pre>";
							// die();
							try {
								$pdo = $manfin_db->prepare($query);				
								$pdo->execute();
								$pdo->closeCursor();
							} catch (Exception $e) {
								$DenumirePartener = $DenumirePartener .' #'. $CodPartener;					
								$query = "EXECUTE spWEBMVParteneriUpdate
									@CodPartener = '$CodPartener',
									@DenumirePartener='$DenumirePartener',
									@NrRegCom='$NrRegCom',
									@CodFiscal='$CodFiscal',
									@CodObiectActivitate='1',
									@CodLocalitate='$CodLocalitate',
									@AdresaPartener1='$AdresaPartener1',
									@AdresaPartener2='$AdresaPartener2',
									@Telefon1='$Telefon1',
									@Telefon2='',
									@Fax1='',
									@Fax2='' ,
									@Email='$Email',
									@Web='',
									@Observatii='',
									@CodLocalitateAdresaLivrare='$CodLocalitateAdresaLivrare',
									@AdresaLivrare='$AdresaLivrare',
									@CodAgentVanzare='-1'";
								$pdo = $manfin_db->prepare($query);				
								$pdo->execute();
								$pdo->closeCursor();
							}
						}
				}else{
					/**Add WP Noticess**/
				}
		}
		// die();
	}


	
	
	/***se trimite doar billing address***/
	/***pentru  PF inserez fara cod fiscal sau cod fiscal temporar, extrag CodPartener si Updatez CodFiscal cu FN + CodPartener***/
	/***$DenumirePartener = 'PF ' . $usr_nume   --- fara PF in fata***/
	/***PJ au cod fiscal si nu se rescriu***/
	/***PF adminul va putea edita CodPartener Din Wordpress cand va fi cazul***/
	/***cod fiscal test : 2951122410051 / cod partener 7893 ***/
	
	
	/*
	$query = "EXECUTE spWEBMVParteneriInsert
				@DenumirePartener='".$DenumirePartener."' ,
				@NrRegCom='".$usr_nr_reg_comert."' ,
				@CodFiscal='".$usr_cnp_cod_fiscal."' ,
				@CodObiectActivitate='".$CodObiectActivitate."' ,
				@CodLocalitate='".$CodLocalitate."' ,
				@AdresaPartener1='".$usr_adresa_partener_1."' ,
				@AdresaPartener2='".$usr_adresa_partener_1."' ,
				@Telefon1='".$usr_telefon_1."' ,
				@Telefon2='".$usr_telefon_2."' ,
				@Fax1='".$usr_fax_1."' ,
				@Fax2='".$usr_fax_2."' ,
				@Email='".$usr_email."' ,
				@Web='".$Web."' ,
				@Observatii='',
				@CodLocalitateAdresaLivrare='".$usr_cod_localitate_livrare."' ,
				@AdresaLivrare='".$usr_adresa_livrare."',
				@CodAgentVanzare='".COD_AGENT_VANZARE."'";
	
	$query = "spWEBMVPersoaneContactInsert
					@CodPartener = ".$lastmsid.",
					@NumePersoanaContact = '".$NumePersoanaContact."',
					@PrenumePersoanaContact = '".$PrenumePersoanaContact."',
					@Telefon = '".$usr_telefon_1."',
					@Fax = '".$usr_fax1."',
					@Email = '".$usr_email."',
					@Mobil = ''";   */
?>