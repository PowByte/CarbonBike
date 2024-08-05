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

	/**Prevent user from updating their emails**/
	// add_action( 'woocommerce_after_edit_account_form', 'disable_edit_email_address' );

	function disable_edit_email_address( ) {
		$script = '<script type="text/javascript">'.
				  'var account_email = document.getElementById("account_email");'.
				  'if(account_email) { '.
				  '     account_email.readOnly = true; '.
				  '     account_email.className += " disable-input";'.
				  '}'.
				  '</script>';
		echo $script;
	}

	// add_action( 'woocommerce_save_account_details_errors', 'prevent_user_update_email', 10, 2 );

	function prevent_user_update_email( &$error, &$user ){
		$current_user = get_user_by( 'id', $user->ID );
		$current_email = $current_user->user_email;
		if( $current_email !== $user->user_email){
			$error->add( 'error', 'Mailul nu poate fi schimbat.');
		}
	}

	/**override checkout billing email**/
	add_filter( 'woocommerce_checkout_get_value', 'filter_woocommerce_checkout_get_value', 10, 2 );
	function filter_woocommerce_checkout_get_value( $null, $input ) {

		return $null;
	};

	/**override account billing email**/
	add_action('woocommerce_form_field', 'filter_woocommerce_form_field', 10, 4);
	function filter_woocommerce_form_field($field, $key, $args, $value){
		if( $key == 'billing_email' && is_user_logged_in()){
			$current_user = wp_get_current_user();
			if(!is_null($current_user->user_email)){
				$field = str_replace( $value, $current_user->user_email, $field );
			}
		}
		return $field;
	}

	add_filter('woocommerce_shipping_fields', 'custom_woocommerce_shipping_fields',  10, 2 );
	function custom_woocommerce_shipping_fields($fields){
		$fields['shipping_first_name']['priority'] = 20;
		$fields['shipping_first_name']['class'] = array( 'form-row-last', );
		$fields['shipping_last_name']['priority'] = 10;
		$fields['shipping_last_name']['class'] = array('form-row-first');
		unset($fields['shipping_company']);

		return $fields;
	}
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
								'Persoana Fizica' 	=> 'Persoana Fizica',
								'Persoana Juridica' => 'Persoana Juridica'
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
		// echo "<pre>";
		// print_r($fields['billing_company']);
		// echo "</pre>";

		return $fields;
	}

	/*** dezactiveaza campuri in my account - save  ***/
	add_filter( 'woocommerce_update_customer_args', 'filter_woocommerce_update_customer_args', 10, 2 );
	function filter_woocommerce_update_customer_args( $array, $customer ) {

		$customer_id = $customer->get_id();

		if(get_user_meta( $customer_id, 'billing_last_name', true )) $customer->set_billing_last_name( get_user_meta($customer_id, 'billing_last_name', true));
		if(get_user_meta( $customer_id, 'billing_first_name', true )) $customer->set_billing_first_name(get_user_meta($customer_id, 'billing_first_name', true));
		if(get_user_meta( $customer_id, 'billing_email', true )) $customer->set_billing_email(get_user_meta($customer_id, 'billing_email', true));
		if(get_user_meta( $customer_id, 'billing_type', true )) $customer->update_meta_data('billing_type', get_user_meta($customer_id, 'billing_type', true ));
		if(get_user_meta( $customer_id, 'billing_company', true )) $customer->set_billing_company( get_user_meta($customer_id, 'billing_company', true));
		if(get_user_meta( $customer_id, 'billing_cui', true )) $customer->update_meta_data('billing_cui', get_user_meta($customer_id, 'billing_cui', true));

		// make filter magic happen here...

		return $array;
	};


	/* dezactiveaza campuri pe checkout - proccess	*/
	add_filter( 'woocommerce_checkout_posted_data', 'fill_order_billing_details' );
	function fill_order_billing_details( $data ) {

		$customer_id = get_current_user_id();

		if(get_user_meta( $customer_id, 'billing_last_name', true )) $data['billing_last_name']  = get_user_meta( $customer_id, 'billing_last_name', true );
		if(get_user_meta( $customer_id, 'billing_first_name', true )) $data['billing_first_name'] = get_user_meta( $customer_id, 'billing_first_name', true );
		if(get_user_meta( $customer_id, 'billing_email', true )) $data['billing_email']      = get_user_meta( $customer_id, 'billing_email', true );
		if(get_user_meta( $customer_id, 'billing_type', true )) $data['billing_type']      = get_user_meta( $customer_id, 'billing_type', true );
		if(get_user_meta( $customer_id, 'billing_company', true )) $data['billing_company']    = get_user_meta( $customer_id, 'billing_company', true );
		if(get_user_meta( $customer_id, 'billing_cui', true )) $data['billing_cui']      = get_user_meta( $customer_id, 'billing_cui', true );

		return $data;
	}

	// PHP: Disable Edit | Remove "(optional)" from our non required fields
	add_filter( 'woocommerce_form_field_args' , 'remove_checkout_optional_fields_label', 10, 3 );
	function remove_checkout_optional_fields_label( $args, $key, $value  ) {

		if( in_array( $key, array('billing_company', 'billing_cui'))){
			$args['required'] = true;
		}
		if(is_user_logged_in()){
			if( in_array( $key, array('billing_last_name', 'billing_first_name', 'billing_email', 'billing_type', 'billing_company', 'billing_cui'))){
				if(!is_null($value) && $value != ""){
					$args['custom_attributes']['readonly'] = 'readonly';
					// $args['custom_attributes']['disabled'] = 'true'; /* dezactiveaza campul din $_POST */
				}
			}
			if($key == 'billing_type'){
				if(!is_null($value) && $value != ""){
					$args['type'] = "text";
				}
			}
		}

		// var_dump($field);
		return $args;
	}

	// define the woocommerce_process_myaccount_field_<key> callback
	function filter_woocommerce_process_myaccount_field_key( $post_key ) {
		// make filter magic happen here...
		// var_dump($post_key);
		// die();
		return $post_key;
	};

	// add the filter
	// add_filter( "woocommerce_process_myaccount_field_billing_city", 'filter_woocommerce_process_myaccount_field_key', 10, 1 );
	// add_filter( "woocommerce_process_myaccount_field_billing_city", 'filter_woocommerce_process_myaccount_field_key', 10, 1 );

	// define the woocommerce_customer_save_address callback

	// do_action( 'woocommerce_new_customer', $customer->get_id(), $customer );

	// do_action( 'woocommerce_update_customer', $customer->get_id(), $customer );

	// add_action('personal_options_update', 'update_extra_profile_fields');
	// function update_extra_profile_fields($user_id){
		// die("hook works");
	// }



	// define the woocommerce_checkout_update_customer callback
	// add the action
	add_action( 'woocommerce_checkout_order_processed', 'action_woocommerce_checkout_order_processed', 10, 3 );
	function action_woocommerce_checkout_order_processed( $order_id, $posted_data, $order ) {
		// make action magic happen here...
		// echo "<pre>";
		// print_r($order_id);
		// echo "</pre>";
		// echo "<pre>";
		// print_r($posted_data);
		// echo "</pre>";
		// echo "<pre>";
		// print_r($order);
		// echo "</pre>";
		// die();
	};





	/*custom validation for company fields */
	add_action( 'woocommerce_after_save_address_validation', 'manfin_woocommerce_after_save_address_validation', 10, 4 );
	// define the woocommerce_after_save_address_validation callback
	function manfin_woocommerce_after_save_address_validation( $user_id, $load_address, $address, $customer ) {
		if( $load_address == "billing"){
			if($customer->get_meta('billing_type', true) == "Persoana Juridica"){
				if($customer->get_billing_company() == ""){
					wc_add_notice( __( 'Nume companie este un camp obligatoriu.', 'woocommerce' ), 'error', array( 'id' => 'billing_company' ) );
				}
				if($customer->get_meta('billing_cui', true) == ""){
					wc_add_notice( __( 'Cod Fiscal este un camp obligatoriu.', 'woocommerce' ), 'error', array( 'id' => 'billing_cui' ) );
				}

			}
		}
	};

	add_action( 'woocommerce_after_checkout_validation', 'manfin_woocommerce_after_checkout_validation', 10, 2);
	function manfin_woocommerce_after_checkout_validation( $data, $errors){
		if( $data['billing_type'] == "Persoana Juridica"){

			if($data['billing_company'] == ""){
				$errors->add( 'billing_company_required', apply_filters( 'woocommerce_checkout_required_field_notice', sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>Nume companie</strong>' ), 'Nume companie' ), array( 'id' => 'billing_company' ) );
			}
			if($data['billing_cui'] == ""){
				$errors->add( 'billing_cui_required', apply_filters( 'woocommerce_checkout_required_field_notice', sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>Cod Fiscal</strong>' ), 'Cod Fiscal' ), array( 'id' => 'billing_cui' ) );
			}
		}
	}
	// add_filter( 'woocommerce_checkout_fields', 'manfin_woocommerce_after_checkout_validation', 10, 1);
	// function manfin_woocommerce_after_checkout_validation( $field ){
		// print_r($field);
		// print_r($errors);

		// wp_die( '[{ "process_checkout" : '.json_encode ($posted_data).'}]' );
		// die();
	// }



	/*de verificat daca a mai fost un hook aici cu sg parametru*/


	// add_action('personal_options_update', 'manfin_woocommerce_customer_save_address'); // cautat alta solutie, hook asta merge doar cand editezi contul
	// add the action

	// http://hookr.io/actions/woocommerce_checkout_update_customer/
	// http://hookr.io/plugins/woocommerce/3.0.6/files/includes-class-wc-checkout/

	// do_action( 'woocommerce_before_checkout_process' );
	// do_action( 'woocommerce_checkout_order_processed', $order_id, $posted_data, $order );
	// do_action( 'woocommerce_new_customer', $customer->get_id(), $customer );

	// http://hookr.io/filters/woocommerce_json_search_found_customers/


	add_action( 'woocommerce_update_customer', 'manfin_woocommerce_customer_save_address', 10, 1 ); /** hook checkout ???? cand e activ se aplica si la user edit form  !!!!    **/

	// add_action('woocommerce_customer_save_address', 'manfin_woocommerce_customer_save_address', 10, 1 ); /*** woocommerce_update_customer ruleaza si pe save address ***/

	// function manfin_woocommerce_customer_save_address($user_id, $customer) {
	function manfin_woocommerce_customer_save_address($user_id) {
		// make action magic happen here...

		global $manfin_db;
		$customer = new WC_Customer( $user_id );




		$shipping_city = $billing_city = $customer->get_billing_city();
		$shipping_state = $billing_state = $customer->get_billing_state();

		if($shipping_state != ""){
			$FormaJuridica = $customer->get_meta('billing_type', true);

			if($FormaJuridica == "Persoana Juridica"){
				$DenumirePartener = $customer->get_billing_company();
				$CodFiscal = $customer->get_meta('billing_cui', true);
				$NrRegCom = $customer->get_meta('billing_regcom', true) ? $customer->get_meta('billing_regcom', true) : '';
			}else{
				$DenumirePartener = 'PF ' . $customer->get_billing_last_name() . ' ' . $customer->get_billing_first_name();
				$CodFiscal = '#Temporar' . $user_id;
				$NrRegCom = '';
			}

			/*Cod Fiscal user test*/
			// $CodFiscal = "FN7893";

			$Localitati = unserialize(get_option("_ManFin_Localitati"));
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


			if( get_user_meta($user_id, "_ManFin_CodPartener", true ) && ( get_user_meta($user_id, "_ManFin_CodPartener", true ) != "Nesincronizat" )){
				/** Client Sincronizat cu CodPartener ManFin - Trimite Update **/

				$CodPartener = $customer->get_meta('_ManFin_CodPartener', true);
				/*Cod Partener user test*/
				// $CodPartener = 7893;

				if($FormaJuridica == "Persoana Fizica"){
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
				try {
					$pdo = $manfin_db->prepare($query);
					$pdo->execute();
					$pdo->closeCursor();
				}catch (Exception $e) {
					if($FormaJuridica == "PJ"){
						/* notifica userul ca firma este deja inregistrata - contacteaza admin */
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
						try {
							$pdo = $manfin_db->prepare($query);
							$pdo->execute();
							$pdo->closeCursor();
						}catch (Exception $e) {
							$headers =  'From: No Reply <noreply@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
							$headers.="CC:marius@powbyte.com, valentin.pascaru@exgala.ro\r\n";
							$headers.='Content-Type: text/html; charset=UTF-8\r\n';
							$message = stripslashes ($e);
							wp_mail('mihai.topa@exgala.ro', 'Eroare Adaugare Clienti', $message, $headers);
						}
					}
				}
			}else{
				/** Client  Nesincronizat fara CodPartener ManFin - Trimite Insert  **/
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
					try {
						$pdo = $manfin_db->prepare($query);
						$pdo->bindColumn(1, $CodPartener, PDO::PARAM_INT);
						$pdo->bindColumn(2, $exista, PDO::PARAM_STR);
						$pdo->bindColumn(3, $DenumireaExista, PDO::PARAM_STR); // 0 si 1 daca e 1, trimit update cu denumire plus FN
						$pdo->execute(array(1, 2, 3));
					}catch (Exception $e) {
						$headers =  'From: No Reply <noreply@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
						$headers.="CC:marius@powbyte.com, valentin.pascaru@exgala.ro\r\n";
						$headers.='Content-Type: text/html; charset=UTF-8\r\n';
						$message = stripslashes ($e);
						wp_mail('mihai.topa@exgala.ro', 'Eroare Adaugare Clienti', $message, $headers);
					}

					if( $row_partener  = $pdo->fetch(PDO::FETCH_BOUND)) {
						// if( $row_partener  = $pdo->fetch() ) {
						// if( $row_partener  = $pdo->fetchAll() ) {

						$pdo->closeCursor();

						update_user_meta( $user_id, "_ManFin_CodPartener", $CodPartener);

						if( $FormaJuridica == "Persoana Fizica" ){
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
								try {
									$pdo = $manfin_db->prepare($query);
									$pdo->execute();
									$pdo->closeCursor();
								} catch (Exception $e) {
									/**** Nu se trimite user in ManFin ****/
									wc_add_notice( __( 'User nesincronizat in ManFin. Eroare 2.', 'woocommerce' ), 'error');
									$headers =  'From: No Reply <noreply@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
									$headers.="CC:marius@powbyte.com, valentin.pascaru@exgala.ro\r\n";
									$headers.='Content-Type: text/html; charset=UTF-8\r\n';
									$message = stripslashes ($e);
									wp_mail('mihai.topa@exgala.ro', 'User nesincronizat in ManFin. Eroare 2.', $message, $headers);
								}
							}
						}else{
							if($exista){
								/*Daca e PJ existent Updateaza data Firma */
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
								try {
									$pdo = $manfin_db->prepare($query);
									$pdo->execute();
									$pdo->closeCursor();
								} catch (Exception $e) {
									/**** Nu se trimite user in ManFin ****/
									wc_add_notice( __( 'User nesincronizat in ManFin. Eroare 3.', 'woocommerce' ), 'error');
									$headers =  'From: No Reply <noreply@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
									$headers.="CC:marius@powbyte.com, valentin.pascaru@exgala.ro\r\n";
									$headers.='Content-Type: text/html; charset=UTF-8\r\n';
									$message = stripslashes ($e);
									wp_mail('mihai.topa@exgala.ro', 'User nesincronizat in ManFin. Eroare 3.', $message, $headers);
								}
							}
						}
					}else{
						/**Add WP Noticess**/
						wc_add_notice( __( 'User nesincronizat in ManFin. Eroare 1.', 'woocommerce' ), 'error');
						update_user_meta( $user_id, "_ManFin_CodPartener", "Nesincronizat");$headers =  'From: No Reply <noreply@'.$_SERVER['HTTP_HOST'].'>' . "\r\n";
						$headers.="CC:marius@powbyte.com, valentin.pascaru@exgala.ro\r\n";
						$headers.='Content-Type: text/html; charset=UTF-8\r\n';
						$message = stripslashes ($e);
						wp_mail('mihai.topa@exgala.ro', 'User nesincronizat in ManFin. Eroare 1.', $message, $headers);
					}
			}
		}
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
					@Mobil = ''";
	*/
?>