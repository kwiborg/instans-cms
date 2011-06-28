<?php
// What to do?
switch ($_REQUEST['dothis']) {
	case 'opret':
		echo returnform_shopproductgroups();
		break;
	case 'rediger':
		echo returnform_shopproductgroups($_GET[id]);
		break;
	default:
		echo return_productgroups();
}
?>