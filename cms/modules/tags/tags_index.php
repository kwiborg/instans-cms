<?php
if (!$_SESSION["CMS_USER"]){
	header("location: ../../login.php");	
}
switch ($_GET[dothis]) {
/*
case "rediger":
	if ($_GET[id]) {
//		echo tagForm();
	}
	break;
*/
default:
	echo listTags();
	break;
}
?>
