<?php
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_CUSTOMBOXES", true);
 if ($dothis == "rediger") {
  $datarow = hentRow($id, "CUSTOM_BOXES");
 }
 
 if ($dothis == "opret" && $trin == "1") {
  if (!$id) {
   $nyt_id = opretNyRow("CUSTOM_BOXES");
   header("location: index.php?content_identifier=customboxes&id=$nyt_id&dothis=opret&trin=1"); // kald siden igen, nu med ID
   exit;
  }
 }  
 
 if ($dothis == "gem" && $det_nye_id) {
  $sql = "update CUSTOM_BOXES set HEADING='$heading', CONTENT='$content', HEADING_BGCOL='$heading_bgcol', HEADING_TEXTCOL='$heading_textcol', CONTENT_BGCOL='$content_bgcol', CONTENT_TEXTCOL='$content_textcol',
  TYPE='$boxtype', UNFINISHED='0', SITE_ID='$_SESSION[SELECTED_SITE]' where ID='$det_nye_id'";
  mysql_db_query($dbname, $sql);
  header("location: index.php?content_identifier=customboxes");
  exit;
 }  

 if ($dothis == "videretiltrin2") {
  $sql = "update CUSTOM_BOXES set TITLE='$title', TYPE='$boxtype_res' where ID='$det_nye_id'";
  mysql_db_query($dbname, $sql);
  header("location: index.php?content_identifier=customboxes&id=$det_nye_id&boxtype=$boxtype_res&dothis=$mode&trin=2"); // kald siden igen, nu med ID
  exit;
 }  

 if ($dothis == "addlink" && $det_nye_id && $page_to_add) {
  $sql = "insert into RELATED_CONTENT (SRC_TABEL, SRC_ID, REL_TABEL, REL_ID, CUSTOMBOX_ID) values ('', '-1', '', '$page_to_add', '$det_nye_id')";
  mysql_db_query($dbname, $sql);
  $sql = "update CUSTOM_BOXES set HEADING='$heading', CONTENT='$content', HEADING_BGCOL='$heading_bgcol', HEADING_TEXTCOL='$heading_textcol', CONTENT_BGCOL='$content_bgcol', CONTENT_TEXTCOL='$content_textcol' where ID='$det_nye_id'";
  mysql_db_query($dbname, $sql);
  $datarow = hentRow($det_nye_id, "CUSTOM_BOXES");
  $dothis="$mode";
  $trin="2";
  $boxtype="2";
  $id=$det_nye_id;
 }  

 if ($dothis == "removelink" && $rel_id) {
  $sql = "delete from RELATED_CONTENT where ID='$rel_id'";
  mysql_db_query($dbname, $sql);
  $sql = "update CUSTOM_BOXES set HEADING='$heading', CONTENT='$content', HEADING_BGCOL='$heading_bgcol', HEADING_TEXTCOL='$heading_textcol', CONTENT_BGCOL='$content_bgcol', CONTENT_TEXTCOL='$content_textcol' where ID='$det_nye_id'";
  mysql_db_query($dbname, $sql);
  $datarow = hentRow($det_nye_id, "CUSTOM_BOXES");
  $dothis="$mode";
  $trin="2";
  $boxtype="2";
  $id=$det_nye_id;
 }  
 
 if ($dothis == "sletbox"){
  $sql = "delete from RELATED_CONTENT where CUSTOMBOX_ID='$id'";
  mysql_db_query($dbname, $sql);
  $sql = "delete from CUSTOM_BOXES where ID='$id'";
  mysql_db_query($dbname, $sql);
  header("location: index.php?content_identifier=customboxes");
  exit;  
 }

 
?>