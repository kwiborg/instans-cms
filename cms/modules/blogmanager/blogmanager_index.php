<?php
if (!$_SESSION["CMS_USER"]){
	header("location: ../../login.php");	
}
switch ($_GET[dothis]) {
case "rediger":
	if ($_GET[id]) {
		echo blogForm();
	}
	break;
case "opret":
	if ($_GET[id]) {
		echo blogForm();
	}
	break;
default:
	echo listBlogs();
	break;
}
?>
