<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//Get the active tab from the $_GET param
$default_tab = "ManFin";
$tab = isset($_GET['page']) ? $_GET['page'] : $default_tab;


$dashboard_url			= admin_url("admin.php?page=ManFin");
$update_products_url	= admin_url("admin.php?page=manfin-update-products");
$update_categs_url		= admin_url("admin.php?page=manfin-update-categs");
$update_imgs_url		= admin_url("admin.php?page=manfin-update-imgs");
$params_map_url			= admin_url("admin.php?page=manfin-params-map");
$cities_import_url		= admin_url("admin.php?page=manfin-cities-import");
$del_products_url		= admin_url("admin.php?page=manfin-del-products");

?>
<div id="wrap">
	<h2>Manager Financiar</h2>

	<h2 id="nav-tab-wrapper">
		<a href="<?php echo $dashboard_url;?>" class="nav-tab <?php if($tab===$default_tab):?>nav-tab-active<?php endif; ?>">Dashboard</a>
		<a href="<?php echo $update_products_url;?>" class="nav-tab <?php if($tab==='manfin-update-products'):?>nav-tab-active<?php endif; ?>">Produse</a>
		<a href="<?php echo $update_categs_url;?>" class="nav-tab <?php if($tab==='manfin-update-categs'):?>nav-tab-active<?php endif; ?>">Categorii</a>
		<a href="<?php echo $update_imgs_url;?>" class="nav-tab <?php if($tab==='manfin-update-imgs'):?>nav-tab-active<?php endif; ?>">Imagini</a>
		<a href="<?php echo $params_map_url;?>" class="nav-tab <?php if($tab==='manfin-params-map'):?>nav-tab-active<?php endif; ?>">Mapari Parametrii</a>
		<a href="<?php echo $cities_import_url;?>" class="nav-tab <?php if($tab==='manfin-cities-import'):?>nav-tab-active<?php endif; ?>">Import Locatitati</a>
		<a href="<?php echo $del_products_url;?>" class="nav-tab <?php if($tab==='manfin-del-products'):?>nav-tab-active<?php endif; ?>">Sterge Produse</a>
	</h2>
	
	
	<div id="#log-wrapper">
		<?php 
			
			$message = isset($_GET['pbtmsg']) ? $_GET['pbtmsg'] : "";
			switch($message):
				case '01':
					echo "Produse Sterse";
					break;
				default:
					/*do nothing*/
					break;
			endswitch;
		?>
	</div>

    <div class="tab-content">
		<?php 
		switch($tab) :
		case 'manfin-update-products':
			echo 'Update Produse'; //Put your HTML here
			?>
			<br />
			<br />
			<input name="product-ID" placeholder="ID Produs"/>
			<a class="button button-primary button-large">Actualizeaza dupa CodStoc</a> 
			<br />
			<br />
			<a id="pbt-update-all" class="button button-primary button-large">Actualizeaza Toate Produsele</a> 
			<?php
			break;
		case 'manfin-update-categs':
			echo 'Update Categorii';
			break;
		case 'manfin-params-map':
			echo 'Mapari';
			break;
		case 'manfin-cities-import':
			echo '<a class="button button-primary button-large" target="_blank" href="'.get_site_url().'/wp-json/pbt/v1/get-manfin-cities" >Actualizeaza Locatitati</a>';
			break;
		case 'manfin-del-products':
			echo 'Sterge Produse Fara Bifa Web';
			include "delete_products.php";
			break;
		default:
			echo 'Default tab';
			break;
    endswitch; ?>
	
	
	
	
	
	
	
	
	