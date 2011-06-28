<?php
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_SHOPPRODUCTS", true);

// What to do?
switch ($_REQUEST['dothis']) {
	case 'updateproductgroup':
		db_updateproductgroup();
		header("location: index.php?content_identifier=shopproductgroups");
		break;
	case 'deleteproductgroup':
		db_deleteproductgroup($_GET[groupid]);
		header("location: index.php?content_identifier=shopproductgroups");
		break;
}
?>