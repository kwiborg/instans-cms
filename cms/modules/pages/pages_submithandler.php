<?php 
if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
if ($dothis != "") {
	checkPermission("CMS_PAGES", true);
}

if ($dothis == "opret") {
	if (!$id) {
		$nyt_id = opretNyRow("PAGES");
		// kald siden igen, nu med ID
		header("location: index.php?content_identifier=$content_identifier&id=$nyt_id&dothis=opret&menuid=$menuid&parentid=$parentid"); 			exit;
	}  
}
 
if (($dothis == "rediger" || $returnto != "") && $id) {
	$pagedata = hentRow($id, "PAGES");
}

// Check data permission
if ($dothis == "rediger" || $dothis == "slet" || $dothis == "gem") {
	if (!check_data_permission("DATA_CMS_PAGE_ACCESS", "PAGES", $_GET["id"], "", $_SESSION["CMS_USER"]["USER_ID"])) {
		echo "Du har ikke adgang til at udføre denne funktion";
		exit;
	}
}


 if ($dothis == "slet" && $id && $menuid) {
  unset($_SESSION[LINKED_PAGES]);
  $sql = "select ID from PAGES where UNFINISHED='0' and DELETED='0' and CONTENT like '%index.php?pageid=$id%'";
  $result = mysql_db_query($dbname, $sql);
  if (mysql_num_rows($result)>0 && !$ignoreadvarsel){
   while($row = mysql_fetch_array($result)) {
    $_SESSION[LINKED_PAGES][] = $row[ID];
   }
   header("location: index.php?content_identifier=pages&dothis=advarsel&pageid=$id&menuid=$menuid");
   exit;
  } else {
   sletRow($id, "PAGES");
   header("location: index.php?content_identifier=pages&dothis=oversigt&menuid=$menuid");
   exit;   
  }
 }
  
 if ($dothis == "gem") {
  gemRow(time(), $_POST, $_SESSION["CMS_USER"]["USER_ID"], "PAGES", 0);
  save_tags($_POST[taglist], "PAGES", $_POST[det_nye_id], $_SESSION[SELECTED_SITE]);
  header("location: index.php?content_identifier=pages&dothis=oversigt&menuid=$_POST[menuid]&jumpto=$_POST[det_nye_id]");
  exit;
 }
 
 if ($dothis == "preview") {
  $datarow = hentRow($_POST["det_nye_id"], "PAGES");  
  gemRow($datarow[CHANGED_DATE], $_POST, $_SESSION["CMS_USER"]["USER_ID"], "PAGES", $datarow[UNFINISHED]);
  $grant = md5(rand(1,time()));
  $sql = "insert into GRANTS (PAGE_ID, GRANTCODE, USER_ID) values ('$_POST[det_nye_id]', '$grant', '" . $_SESSION[CMS_USER][USER_ID] . "')";
  mysql_db_query($dbname, $sql);
  header("location: index.php?content_identifier=pages&dothis=rediger&id=$_POST[det_nye_id]&parentid=$_POST[parentid]&menuid=$_POST[menuid]&showpreview=1&grant=$grant");
  exit;
 }

/* */
 if ($dothis == "addrel" && $thispageid && $relpageid && $thistabel && $reltabel) {
  $sql = "insert into RELATED_CONTENT (SRC_TABEL, SRC_ID, REL_TABEL, REL_ID) values ('$thistabel','$thispageid','$reltabel','$relpageid')";
  $result = mysql_db_query($dbname, $sql) or die(mysql_error());
  header("location: index.php?content_identifier=$content_identifier&dothis=related&pageid=$thispageid&tabel=$thistabel&menuid=$menuid"); 
  exit;
 } 
 
 if ($dothis == "removerel" && $relid) {
  $sql = "delete from RELATED_CONTENT where ID='$relid'";
  $result = mysql_db_query($dbname, $sql) or die(mysql_error());
  header("location: index.php?content_identifier=$content_identifier&dothis=related&pageid=$pageid&tabel=$tabel&menuid=$menuid"); 
  exit;
 }  
  
 if ($dothis == "removeforms") {
  if ($id) { 
   $sql_galleries = "delete from PAGES_FORMS where PAGE_ID=$id";
   $result_galleries = mysql_db_query($dbname, $sql_galleries) or die(mysql_error());
   header("location: index.php?content_identifier=pages&menuid=$menuid");
   exit;
  }  
 }
/* */
 if ($dothis == "flytop" && $id) {
  flytOp($id);
  header("location: index.php?content_identifier=pages&dothis=oversigt&menuid=$menuid");
  exit;
 }
 
 if ($dothis == "flytned" && $id) {
  flytNed($id);
  header("location: index.php?content_identifier=pages&dothis=oversigt&menuid=$menuid");
  exit;
 } 
 
 if ($dothis == "newparent") {
  newparent($pagetomoveid, $newparentid);
  header("location: index.php?content_identifier=pages&menuid=$menuid"); 
  exit;    
 }
 
 if ($dothis == "gemfastebokse") {
  foreach ($_POST as $key => $value){
   if ($value=="on") $value="1";
   $$key = $value;
   if (strstr($key, "custombox_")) {
    $temp = explode("_", $key);
	$customboxeslist .= "_$temp[1]_";
   }
  }
  $sql = "update BOX_SETTINGS set NEWS='$show_news', EVENTS='$show_events', SEARCH='$show_search', STF='$show_stf', NEWSLETTER='$show_newsletter', LASTEDITED='$show_lastedited', CUSTOM='$customboxeslist' where PAGE_ID='$det_nye_id'";  
  mysql_db_query($dbname, $sql) or die(mysql_error());
  if ($add_to_subpages == "1") {
   $sql = "select ID from PAGES where PARENT_ID='$det_nye_id'";
   $result = mysql_db_query($dbname, $sql) or die(mysql_error());
   while ($row = mysql_fetch_array($result)) {
    $sql = "update BOX_SETTINGS set NEWS='$show_news', EVENTS='$show_events', SEARCH='$show_search', STF='$show_stf', NEWSLETTER='$show_newsletter', LASTEDITED='$show_lastedited', CUSTOM='$customboxeslist' where PAGE_ID='$row[ID]'";  
    mysql_db_query($dbname, $sql) or die(mysql_error());
   }
  }
  header("location: index.php?content_identifier=pages&menuid=$menuid"); 
 }
  
?>