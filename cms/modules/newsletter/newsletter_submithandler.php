<?php
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_NEWSLETTERSEND", true);
include_once ('newsletter_common.inc.php');

switch ($dothis) {
case "insert":
	saveNewsletter();
	header("location: index.php?content_identifier=newsletter&filter_template=$_POST[newsletter_template_id]");
	break;
case "update":
	saveNewsletter();
	header("location: index.php?content_identifier=newsletter&filter_template=$_POST[newsletter_template_id]");
	break;
case "delete":
	sletRow($_GET[nid], "NEWSLETTERS");
	header("location: index.php?content_identifier=newsletter&filter_template=$_GET[ntid]");
	break;
}
?>