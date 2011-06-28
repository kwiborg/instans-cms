<?php 
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_GROUPS", true);
 /*
 
 Denne submithandler udgør den kode, som sidder i toppen af den "samlede" PAGES-side.
 Den holder styr på, hvad der sker, når siden submittes og/eller indlæses.
 
 */
 
 if ($dothis == "opret" )
 {
  if (!$id)
  {
   $nyt_id = opretNyRow("GROUPS");
   header("location: ?content_identifier=$content_identifier&id=$nyt_id&dothis=opret&parent_id=$parent_id"); // kald siden igen, nu med ID
   exit;
  }  
 }

 if ($dothis == "rediger" && $id)
 {
  $pagedata = hentRow($id, "GROUPS");
 }
  
 if ($dothis == "gem") 
 {
  gemRow(time(), $_POST, $_SESSION["CMS_USER"]["USER_ID"], "GROUPS", 0);
  establishDbFieldIntersections($_POST[det_nye_id]);
  header("location: index.php?content_identifier=groups&dothis=oversigt");
  exit;
 }
 
 if ($dothis == "slet" && $id)
 {
  sletRow($id, "GROUPS");
  updateUsersGroups($id);
  header("location: index.php?content_identifier=groups&dothis=oversigt");
  exit;   
 }
  
 if ($dothis == "addmember" && $user_id && $group_id)
 {
  addGroupMember($user_id, $group_id);
  header("location: index.php?content_identifier=groups&dothis=medlemmer&id=$group_id");
  exit;     
 }

 if ($dothis == "removemember" && $user_id && $group_id)
 {
  removeGroupMember($user_id, $group_id);
  header("location: index.php?content_identifier=groups&dothis=medlemmer&id=$group_id");
  exit;     
 }

 if ($_POST[dothis] == "switch_group"){
 	header("location: index.php?content_identifier=groups&dothis=medlemmer&id=$_POST[det_nye_id]&group_id=".$_POST[show_group_id]);
	exit;
 }

 if ($_POST[dothis] == "usersearch"){
 	header("location: index.php?content_identifier=groups&dothis=medlemmer&id=$_POST[det_nye_id]&usersearch=".$_POST[usersearch]);
	exit;
 }

?>