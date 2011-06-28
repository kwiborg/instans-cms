<?php 
	// REMEMBER TO EXIT HERE WHEN SCRIPT IS NOT IN USE 
	exit;
/*
2007-01-15 - 	Fra og med RELEASE: VERSION 2.6.4 kan varer tilhøre mere end én varegruppe.
				Dette script konverterer eksisterende varer til ny struktur.
*/
	include($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
	include($_SERVER[DOCUMENT_ROOT]."/cms_language.inc.php");
	include_once($cmsAbsoluteServerPath."/sharedfunctions.inc.php");	
	include_once($cmsAbsoluteServerPath."/frontend/frontend_common.inc.php");
	connect_to_db(); 

	$sql = "select * from SHOP_PRODUCTS";
	$res = mysql_query($sql);
	
	while ($row = mysql_fetch_assoc($res)) {
		$sql = "insert into SHOP_PRODUCTS_GROUPS (PRODUCT_ID, GROUP_ID) values ($row[ID], $row[GROUP_ID])";
		if (mysql_query($sql)) {
			$i++;
		}
	}

	echo "$i produkter konverteret til ny varegruppe struktur";
?>