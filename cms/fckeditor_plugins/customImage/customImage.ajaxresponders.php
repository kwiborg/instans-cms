<?php
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
checkLoggedIn();
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/modules/picturearchive/picturearchive_common.inc.php');

header("Content-type: text/html; charset=UTF-8");

switch ($_REQUEST['do']) {
	case 'returnFolderImages':
		echo returnFolderImages($_GET["fid"]);
		break;
	case 'returnImageFolders':
		echo returnImageFolders();
		break;
}
?>