<?php
header("Content-type: text/html; charset=UTF-8");
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
checkLoggedIn();
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/modules/attachments/attachments_common.inc.php');

switch ($_POST['do']) {
	case 'ajax_returnAttachedfiles':
		echo returnAttachedfiles($_POST["id"], $_POST["tabel"]);
		break;
	case 'ajax_attachFile':
		echo attachFile($_POST["file_id"], $_POST["id"], $_POST["tabel"]);
		break;
	case 'ajax_removeAttachment':
		echo removeAttachment($_POST["file_id"], $_POST["id"], $_POST["tabel"]);
		break;
}
?>