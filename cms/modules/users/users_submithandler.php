<?php 
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_USERS", true);
 
 if ($dothis == "opret")
 {
  if (!$id)
  {
   $nyt_bruger_id = opretNyBruger();
   header("location: index.php?content_identifier=$content_identifier&id=$nyt_bruger_id&dothis=opret"); // kald siden igen, nu med ID
   exit;
  }  
 }
 
 if ($dothis == "rediger" && $id)
 {
  $pagedata = hentRow($id, "USERS");
 }
  
 if ($dothis == "gem") 
 {
  $sql = "select * from USERS where USERNAME='$_POST[username]'";
  $result = mysql_db_query($dbname, $sql);
	gemBruger($_POST);
	save_varied_fields($_POST);
	if ($notify_user == "on") { // && $_POST[mode] == "opret"
		$mail_domain = returnFieldValue("SITES", "EMAIL_DOMAIN", "SITE_ID", $_SESSION["SELECTED_SITE"]);
		$site_domain = returnFieldValue("SITES", "BASE_URL", "SITE_ID", $_SESSION["SELECTED_SITE"]);
		$site_domain .= returnFieldValue("SITES", "SITE_PATH", "SITE_ID", $_SESSION["SELECTED_SITE"]);

		sendNewUserMail($_POST[email], $_POST[firstname], $_POST[lastname], $site_domain, $_POST[username], $_POST[password1], $site_domain, "no-reply@".$mail_domain);
	}
   	header("location: index.php?content_identifier=users&dothis=oversigt&usersearch=$_POST[backtosearch]&group_id=$_POST[backtogroup]");
   	exit;
 }
 
 if ($dothis == "resendinfo" && $userid) {
  $sql = "select * from USERS where ID='$userid'";
  $result = mysql_db_query($dbname, $sql); 
  $row = mysql_fetch_array($result);
	$mail_domain = returnFieldValue("SITES", "EMAIL_DOMAIN", "SITE_ID", $_SESSION["SELECTED_SITE"]);
	$site_domain = returnFieldValue("SITES", "BASE_URL", "SITE_ID", $_SESSION["SELECTED_SITE"]);
	$site_domain .= returnFieldValue("SITES", "SITE_PATH", "SITE_ID", $_SESSION["SELECTED_SITE"]);
	sendNewUserMail($row[EMAIL], $row[FIRSTNAME], $row[LASTNAME], $site_domain, $row[USERNAME], $row[PASSWORD], $site_domain, "no-reply@".$mail_domain);
  $infoResent = true;
  $dothis = "oversigt";
 }
 
 if ($dothis == "slet" && $id)
 {
  sletRow($id, "USERS");
  header("location: index.php?content_identifier=users&dothis=oversigt");
  exit;   
 }

 if ($dothis == "transfer" && $userid && $transfertoid)
 {
  $landing_group_id = returnFieldValue("GROUPS", "LANDING_GROUP_ID", "ID", $transfertoid);
  $sql = "update USERS set TRANSFER_TO_GROUP='0' where ID='$userid' limit 1";
  mysql_query($sql);
  $sql = "update USERS_GROUPS set GROUP_ID='$transfertoid' where USER_ID='$userid' and GROUP_ID='$landing_group_id'";
  mysql_query($sql);
  $userdata = hentRow($userid, "USERS");
  $mail_domain = returnFieldValue("SITES", "EMAIL_DOMAIN", "SITE_ID", $_SESSION["SELECTED_SITE"]);
  $sitename 	 = returnFieldValue("SITES", "SITE_NAME", "SITE_ID", $_SESSION["SELECTED_SITE"]);
  sendNewUserMail($userdata[EMAIL], $userdata[FIRSTNAME], $userdata[LASTNAME], $sitename, $userdata[USERNAME], $userdata[PASSWORD], $sitename, "no-reply@".$mail_domain);
  header("location: index.php?content_identifier=users&dothis=oversigt");
  exit;   
 }
 
 if ($_POST[dothis] == "switch_group"){
 	header("location: index.php?content_identifier=users&group_id=".$_POST[show_group_id]);
	exit;
 }

 if ($_POST[dothis] == "usersearch"){
 	header("location: index.php?content_identifier=users&usersearch=".$_POST[usersearch]);
	exit;
 }
  
  
?>