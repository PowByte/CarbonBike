<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}	/* sincronizare comenzi in ManFin */

	// add the action



	//!!!!!!!!!Dezactivat temporar pana rezolv creare cod partener in checkout (guest sau cu cont)
	add_action( 'woocommerce_order_status_changed', 'action_woocommerce_order_status_changed', 10, 4 );

	// define the woocommerce_order_status_changed callback
	function action_woocommerce_order_status_changed( $this_get_id, $this_status_transition_from, $this_status_transition_to, $order ) {
		// make action magic happen here...

		// echo "<pre style='padding-left: 50%; padding-top: 100px'>";
		// print_r($this_status_transition_to);
		// echo "</pre>";
		// die();

		if($this_status_transition_to == "update"){
			delete_post_meta($order->get_id(), '_id_comanda_manfin');
			$order->add_order_note( "ManFin ID comanda sters. Comanda poate fi modificata! Sterge comanda ManFin inainte de confirmare.");
			$order->save();
		}
		//comment for debug
		if( $this_status_transition_to == "confirmed" ){
		// if( $this_status_transition_to == "confirmed"  || true ){

			$data_comanda = $order->get_date_created();
			$order_wp_id = $order->get_id();
			$customer_id = $order->get_customer_id();

			global $manfin_db;

			if(!$manfin_db) return;

			// echo "<pre style='padding-left: 50%; padding-top: 100px'>";
			// print_r($data_comanda);
			// echo "</pre>";
			// echo "<pre style='padding-left: 50%; padding-top: 100px'>";
			// print_r($data_comanda->date('Y-m-d H:i:s'));
			// echo "</pre>";

			/* CodPartener - cand trimite comanda trebuie sa fie cod deja existent in ERP */
			$CodPartener = get_user_meta( $customer_id, '_ManFin_CodPartener', true);



			$customer_note = $order->get_customer_note();

			if (empty($customer_note)) {
				$customer_note = "";
			}

			$ReferintaBT = get_post_meta($order_wp_id, "ipay_id", true);
			if($ReferintaBT){
				$customer_note .= " ipay_id=".$ReferintaBT;
			}

			/**** daca este user si nu are date trimise, trimite date *****/
			/**** daca este guest, trimite date si asociaza email *****/
			/* spWEBMVParteneriInsert - creaza partener ( juridic / fizic )*/
			/* spWEBMVPersoaneContactInsert - creaza partener contact  pers fiz */



			// Iterating through order shipping items
			foreach( $order->get_items( 'shipping' ) as $item_id => $item ){
				// Get the data in an unprotected array
				$item_data = $item->get_data();

				$shipping_data_method_title = $item_data['method_title'];
				break;
			}

			$PaymentMethod = $order->get_payment_method();

			if($shipping_data_method_title == "Livrare si ridicare din Magazin Targoviste, Bd. Ion C. Bratianu, nr. 49" && $PaymentMethod == "cod"){
				$PaymentMethod = "plataMagazin";
			}

			/*
				0 - Plata in magazin | ridicare din magazin + ramburs
				1 - Plata in avans
				2 - Plata ramburs | cod
				3 - Paypall |
				4 - Plata cu card online | ipay
				5 - Plata OP | bacs
			*/

			switch($PaymentMethod){
				case 'bacs':
					$CodModalitatePlata = 5;
					break;
				case 'ipay':
					$CodModalitatePlata = 4;
					break;
				case 'tbiro':
					$CodModalitatePlata = 5;
					break;
				case 'plataMagazin':
					$CodModalitatePlata = 0;
					break;
				default:
					$CodModalitatePlata = 2;
					break;
			}

			if($CodPartener == "Nesincronizat"){

				$user_link = wp_nonce_url( add_query_arg( array(
					'action' => 'switch_to_user',
					'user_id' => $customer_id,
					'nr' => 1,
				), wp_login_url() ), "switch_to_user_{$customer_id}" );

				$switch_note = 'ManFin Parterner ID: '. $CodPartener .', comanda nu se poate trimite in ManFin! <a href="'. $user_link .'" target="_blank">Switch to user</a>';
				$order->add_order_note( $switch_note );
				$order->save();
				return;
			}

			$order->add_order_note( 'ManFin Parterner ID: '. $CodPartener  );
			$order->save();

			$id_comanda_manfin = $order->get_meta('_id_comanda_manfin');

			if( $id_comanda_manfin == ""){
				$query = "EXECUTE spWEBMVComenziPrimiteInsert
										@DataComandaClient = '".$data_comanda->date('Y-m-d H:i:s')."',
										@CodOfertaClient = 0 ,
										@CodAgentVanzare = '-1',
										@DataOnorareComandaClient = '".$data_comanda->date('Y-m-d H:i:s')."',
										@CodPartener = '$CodPartener',
										@CodCVZ = 1 ,
										@CodContract = 0 ,
										@Descriere = '$customer_note',
										@ReferintaClient = '$order_wp_id',
										@TermenPlata = 7,
										@CodModalitatePlata = '$CodModalitatePlata'";

				$pdo = $manfin_db->prepare($query);

				$pdo->bindColumn(1, $id_comanda, PDO::PARAM_STR);
				//comment for debug
				$pdo->execute(array(1));


				// echo "<pre>";
				// print_r($query);
				// echo "</pre>";
				// die();

				/*/comment for debug/*/
				if( $row_produs  = $pdo->fetch(PDO::FETCH_BOUND) ) {

					$id_comanda_manfin =  $id_comanda;
					$pdo->closeCursor();

					update_post_meta($order_wp_id, "_id_comanda_manfin", $id_comanda_manfin);
					$order->add_order_note( "ManFin ID Comanda: #" .$id_comanda_manfin );
					$order->save();
				}

			}

			if( $id_comanda_manfin != "" ) {
				// Get and Loop Over Order Items
				foreach ( $order->get_items() as $item_id => $item ) {

					$product_id = $item->get_product_id();

					$product = $item->get_product();


					$quantity = $item->get_quantity();

					$product_regular_price = $product->get_regular_price();
					$product_sale_price = $product->get_sale_price();

					$product_order_price = $item->get_subtotal() / $quantity;
					$item_sale_subtotal = $product_order_price / 1.19 ;


					if( $product_order_price < $product_sale_price ){
						$subtotal = $product_regular_price / 1.19 ;
					}else{
						$subtotal = $item_sale_subtotal ;
					}



					// echo $product_sale_price;
					// echo $item->get_total();
					// echo "<pre>";
					// print_r($item);
					//  echo "</pre>";
					//
					// die();

					/*/
					// Calcule vechi comentate
					// $variation_id = $item->get_variation_id();
					// $name = $item->get_name();
					// $subtotal = number_format( $item->get_subtotal() / 1.19 , 2 );
					// $subtotal = $item->get_subtotal() / $quantity / 1.19 ;
					// $total = $item->get_total();
					// $tax = $item->get_subtotal_tax();
					// $taxclass = $item->get_tax_class();
					// $taxstat = $item->get_tax_status();
					// $allmeta = $item->get_meta_data();
					/*/
					$wp_manfin_id = get_post_meta($product_id, "_wp_manfin_id", true);

					/* de verificat dupa program de lucru galatek */
					// $update_link = 'https://www.galatek.ro/wp-json/pbt/v1/product-update/' . $wp_manfin_id;
					//
					// wp_remote_request($update_link);

					/*

					de asteptat dupa remote request apoi rulez in continuare ca sa ia ultimul pret din update

					 */

					/*/ $pret = get_post_meta($product_id, "_price", true) / 1.19;
					// $pret = $product->get_price_excluding_tax();
					// $type = $item->get_type();/*/

					// $pret_final = number_format( $item_total, 2 );
					// echo "<pre>";
					// var_dump($item);
					// echo "</pre>";
					// die();

					/*[spWEBMVComenziPrimiteContinut]
					@CodComandaClient bigint,
					@CodStoc bigint,
					@Cantitate decimal(19,4),
					@Pret money,
					@PretValuta money,
					@PretRecomandat money,
					@Observatii nvarchar(400)*/

					// $observatii_comanda = "Montaj in magazin: Da";

					$observatii_comanda = "";

					$query_2 = "EXECUTE spWEBMVComenziPrimiteContinut
								@CodComandaClient = $id_comanda_manfin ,
								@CodStoc = $wp_manfin_id ,
								@Cantitate = $quantity ,
								@Pret = $subtotal ,
								@PretValuta = $subtotal ,
								@PretRecomandat = $subtotal ,
								@Observatii = '$observatii_comanda'";

					$pdo = $manfin_db->prepare($query_2);
					//comment for debug
					$pdo->execute();
					$pdo->closeCursor();


					// var_dump($sale_subtotal > $subtotal);
					// var_dump($subtotal);
					// var_dump($sale_subtotal);

					$suma_discount = 0;
					$coupon = $item->get_data('coupon');

					if(!empty($coupon)){
						$suma_discount += ( $coupon['subtotal'] - $coupon['total'] ) / 1.19;
						$suma_discount = $suma_discount / $quantity ;
					}
					// echo "<pre>";
					// print_r($item->get_data('coupon'));
					// echo "</pre>";
					/*/ $regular_subtotal = $quantity * $product_regular_price / 1.19;/*/

					if( $product_order_price < $product_sale_price ){
						$suma_discount += $subtotal - $item_sale_subtotal;

					}

					// var_dump($suma_discount);
					// die();

					if( $suma_discount > 0 ){

						$quantity_discount =  '-'.$quantity;
						//linie discount
						$query_3 = "EXECUTE spWEBMVComenziPrimiteContinut
									@CodComandaClient = $id_comanda_manfin ,
									@CodStoc = 7561 ,
									@Cantitate = $quantity_discount ,
									@Pret = $suma_discount ,
									@PretValuta = $suma_discount ,
									@PretRecomandat = $suma_discount ,
									@Observatii = ''";

						// var_dump($query_3);
						// die();

						$pdo = $manfin_db->prepare($query_3);
						//comment for debug
						$pdo->execute();
						$pdo->closeCursor();
					}

					// echo "<pre style='padding-left: 50%; padding-top: 100px'>";
					// var_dump($query_2);
					// echo "</pre>";


				}

				/* de trimis transport in comanda */
				// $shipping_cost = $order->get_total_shipping();
				// $shipping_cost = number_format( $order->get_total_shipping() / 1.19 , 2 );
				$shipping_cost = $order->get_total_shipping() / 1.19 ;
				$query_3 = "EXECUTE spWEBMVComenziPrimiteContinut
								@CodComandaClient = $id_comanda_manfin ,
								@CodStoc = 7707 ,
								@Cantitate = '1' ,
								@Pret = $shipping_cost ,
								@PretValuta = $shipping_cost ,
								@PretRecomandat = $shipping_cost ,
								@Observatii = ''";

				$pdo = $manfin_db->prepare($query_3);
				$pdo->execute();
				$pdo->closeCursor();
			}
		}
	}


