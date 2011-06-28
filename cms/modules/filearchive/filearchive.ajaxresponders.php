<?php

foreach ($_GET as $key => $value) {
	$_POST[$key] = $value;
}

header("Content-type: text/html; charset=UTF-8");
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
checkLoggedIn();
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/modules/filearchive/filearchive_common.inc.php');

switch ($_POST['do']) {
	case 'ajax_returnAvailablefiles':
		$output = filMappeListe();
		echo $output[0];
		echo "<script type='text/javascript'>";
			echo $output[1];
  		echo "</script>";
		break;
}
?>
