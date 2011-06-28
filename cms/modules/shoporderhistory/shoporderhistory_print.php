<?php 
	include($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
	session_start();
	if (!$_SESSION["CMS_USER"]) {
		header("location: ../../login.php");
	}
	include($_SERVER[DOCUMENT_ROOT]."/cms/common.inc.php");
	include($_SERVER[DOCUMENT_ROOT]."/cms/modules/shoporderhistory/shoporderhistory_common.inc.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<meta http-equiv='content-type' content='text/html; charset=UTF-8' />
		<link rel="stylesheet" href="/cms/modules/shoporderhistory/shoporderhistory_style.css" />
<style>
table{
	font-size:9px;
	margin:0;
	width:100%;
}
div.order{
	border:0;
	margin:0;
	padding:0;
}
</style>
	</head>
	<body style="margin:0">
		<?=show_order_history(0, "EXPANDED", "", "")?>
		<script>window.print();</script>
	</body>
</html>