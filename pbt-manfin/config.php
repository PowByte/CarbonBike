<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ERROR);
// error_reporting(E_ALL);
/* BEGIN CONECTARE DB MsSQL */

    $db_sql_user = "ManFinMV_RFN";

    $db_sql_pass = "rfn@2014";

    $db_sql_name = "ManFinRFN";

    $db_sql_type = "odbc";

    $db_sql_host = "91.213.76.14";

    $db_sql_port = '1434';

    define("DB_SQL_USER", $db_sql_user);

    define("DB_SQL_PASS", $db_sql_pass);

    define("DB_SQL_NAME", $db_sql_name);

    define("DB_SQL_TYPE", $db_sql_type);

    define("DB_SQL_HOST", $db_sql_host);

    define("DB_SQL_PORT", $db_sql_port);

	global $manfin_db;

	function connectTo($host, $port, $timeout = 2)
	{
		$ip = fSockOpen($host, $port, $errno, $errstr, $timeout);
		return $ip != false;
	}
	// if(is_admin()){
	// 	var_dump(connectTo('91.213.76.14', '1434'));
	// }
		// echo "<br />";

		// echo 'Server IP Address: ' . $_SERVER['SERVER_ADDR'];

		/* $url = 'https://gradinita24bv.ro/endpoint.php';

		// Initialize a CURL session.
		$ch = curl_init();

		// Set the URL that you want to GET by using the CURLOPT_URL option.
		curl_setopt($ch, CURLOPT_URL, $url);

		// Set the option to return the response as a string instead of outputting it directly.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Execute the request and fetch the response. Check for errors.
		$response = curl_exec($ch);
		if ($response === false) {
			echo 'Curl error: ' . curl_error($ch);
		}

		// Close the CURL session.
		curl_close($ch);

		// Output the response.
		// echo 'Response from Server A: ' . $response; */

		try{
			if (connectTo($db_sql_host, $db_sql_port)){
				$manfin_db = new PDO("odbc:DRIVER=FreeTDS;TDS_Version=7.4;Client_Charset=utf8;SERVER=". DB_SQL_HOST .";PORT=". DB_SQL_PORT .";Database=".DB_SQL_NAME.";", DB_SQL_USER, DB_SQL_PASS);
				$manfin_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}else{
				echo "<br />";
				echo("Unable to open database.");
				$manfin_db = false;
			}
			// $manfin_db = new PDO("odbc:DRIVER=FreeTDS;SERVER=86.122.122.58;PORT=1434;Database=ManFin;", "ManFInMV", "exgala@2016");


			// echo "<pre>";
			// print_r($manfin_db);
			// echo "</pre>";
			// die();
			// $objCharset = $manfin_db->query("SELECT @@VERSION AS 'SQL Server Version'");

			// die($objCharset->fetch(PDO::FETCH_NUM)[0]);
			// $manfin_db->exec("set names utf8");

		}catch(PDOException $exception)	{
			if(isset($_GET['debug']))
				echo("Unable to open database.<br />Error message:<br /><br />$exception.");
			// $manfin_db->rollBack();
		}
	// }
?>
