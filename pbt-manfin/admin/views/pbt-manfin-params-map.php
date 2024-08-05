<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


// echo "<pre>";
// print_r($dinamic_content);
// echo "</pre>";
// var_dump($dinamic_content["params"]);

?>

<div id="col-container">


	<div id="col-left">

		<div class="col-wrap">

			<?php esc_attr_e( 'Parametrii Afisati', 'WpAdminStyle' ); ?>
			<div class="inside">
				<table class="widefat">
					<tr>
						<th>Denumire</th>
						<th>Cod</th>
					</tr>
					<?php
					foreach ($dinamic_content["active_params"] as $id_param => $param_value){
						?>

						<tr>
							<td><?php echo $param_value;?></td>
							<td><?php echo $id_param;?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
		</div><!-- /col-wrap -->

	</div><!-- /col-left -->

	<div id="col-right">

		<div class="col-wrap">
			<?php esc_attr_e( 'Parametrii Disponibili', 'WpAdminStyle' ); ?>
			<div class="inside">
				<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
					<table class="widefat">
						<tr>
							<th>Denumire</th>
							<th>Cod</th>
							<th>Afiseaza</th>
						</tr>
						<?php
						foreach ($dinamic_content["params"] as $id_param => $param_value){
							if($param_value == "Pas lant"){
								$param_value = "Pas lant (Inch)";
							}
							?>
							<tr>
								<td><?php echo $param_value;?></td>
								<td><?php echo $id_param;?></td>
								<td><input type="checkbox" name="manfin_active_params[<?php echo $id_param;?>]" value="<?php echo $param_value;?>" <?php echo pbt_is_active_param($id_param, $dinamic_content["active_params"]);?>/></td>
							</tr>
							<?php
						}
						?>
						<tr>
							<td colspan="3">
								<?php
								wp_nonce_field( 'manfin_params_save', 'manfin_params_nonce' );
								submit_button();
								?>
							</td>
						</tr>
					</table>
				</form>
			</div>

		</div><!-- /col-wrap -->

	</div><!-- /col-right -->

</div><!-- /col-container -->



<div class="clearfix"></div>

<!-- https://code.tutsplus.com/tutorials/creating-custom-admin-pages-in-wordpress-1--cms-26829 <br />
http://www.wpcodingdev.com/blog/how-to-create-custom-form-in-wordpress-admin-panel/ <br />
https://stackoverflow.com/questions/54748657/add-product-attributes-with-values-to-a-product-in-woocommerce <br />
https://github.com/woocommerce/woocommerce/blob/master/includes/abstracts/abstract-wc-product.php <br /> -->