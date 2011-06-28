<?php
// What to do?
switch ($_REQUEST['dothis']) {
	case 'opret':
		echo returnform_shopproducts();
		break;
	case 'rediger':
		echo returnform_shopproducts($_GET[id]);
		break;
	case "relatedproducts":
		echo related_products($_GET[productid], $_GET[groupid]);
		break;
	default:
		echo return_products($_GET[groupid]);
}
?>