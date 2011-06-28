<?php
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_GENERAL", true);
 if ($dothis == "gem")
 {
  gemRow(time(), $_POST, $_SESSION["CMS_USER"]["USER_ID"], "GENERAL_SETTINGS", 0);
  header("location: ?content_identifier=general&dothis=oversigt&saved=1");
  exit;
 }
 
 $data = hentRow("$_SESSION[SELECTED_SITE]", "GENERAL_SETTINGS");

?>