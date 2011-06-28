<?php
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_SHOPPRODUCTS", true);

// What to do?
switch ($_REQUEST['dothis']) {
	case 'updateproduct':
		db_updateproduct();
		header("location: index.php?content_identifier=shopproducts&groupid=$_POST[groupid]");
		break;
	case 'deleteproduct':
		db_deleteproduct($_GET[id]);
		header("location: index.php?content_identifier=shopproducts&groupid=$_GET[groupid]");
		break;
	case "removerelations":
		db_removerelations($_POST);
		if ($_POST[this_product_id]){
			header("location: index.php?content_identifier=shopproducts&dothis=relatedproducts&productid=".$_POST[this_product_id]);
			exit;
		}
		if ($_POST[this_group_id]){
			header("location: index.php?content_identifier=shopproducts&dothis=relatedproducts&groupid=".$_POST[this_group_id]);
			exit;
		}
		break;
	case "addrelations":
		db_addrelations($_POST);
		if ($_POST[this_product_id]){
			header("location: index.php?content_identifier=shopproducts&dothis=relatedproducts&productid=".$_POST[this_product_id]);
			exit;
		}
		if ($_POST[this_group_id]){
			header("location: index.php?content_identifier=shopproducts&dothis=relatedproducts&groupid=".$_POST[this_group_id]);
			exit;
		}
		break;
}
?>