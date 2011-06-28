<?php
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_SHOPDISCOUNTS", true);

// What to do?
switch ($_REQUEST['dothis']) {
	case 'updategroupdiscounts':
		db_updategroupdiscounts();
		header("location: index.php?content_identifier=shopgroupdiscounts&gid=$_POST[gid]&usermessage=ok");
		break;
}
?>