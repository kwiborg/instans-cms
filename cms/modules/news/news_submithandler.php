<?php  
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_NEWS", true);
 
// Get current newsfeed
if ($dothis == "opret" || $dothis == "oversigt" || !$dothis) {
	if ((!isset($_GET[newsfeedid])) || ($_GET[newsfeedid]=="")) {
		$sql = "select ID from NEWSFEEDS where SITE_ID in (0,'$_SESSION[SELECTED_SITE]') order by NAME asc LIMIT 1";
		$result = mysql_query($sql);
		$filter_newsfeedid = mysql_result($result,0);
	} else {
		$filter_newsfeedid = $_GET[newsfeedid];
	}
}

 if ($dothis == "rens") {
  $_POST["Indhold"] = cleanHTMLfromWord($_POST["Indhold"]);
  $datarow = hentRow($_POST["det_nye_id"], "NEWS");  
  gemRow($datarow[CHANGED_DATE], $_POST, $_SESSION["CMS_USER"]["USER_ID"], "NEWS", $datarow[UNFINISHED]);
  header("location: index.php?content_identifier=news&dothis=rediger&id=".$_POST["det_nye_id"]);
  exit;
 }
 
 if ($dothis == "opret")
 {
  if (!$id)
  {
   $nyt_id = opretNyRow("NEWS");
   header("location: index.php?content_identifier=$content_identifier&id=$nyt_id&dothis=opret&newsfeedid=$filter_newsfeedid"); // kald siden igen, nu med ID
   exit;
  }  
 }
 
// Check datapermission
if ($dothis == "rediger" || $dothis == "slet" || $dothis == "gem") {
	if (!check_data_permission("DATA_CMS_NEWSITEM_ACCESS", "NEWS", $_GET["id"], "", $_SESSION["CMS_USER"]["USER_ID"])) {
		echo "Du har ikke adgang til at udføre denne funktion";
		exit;
	}
}
 
 if ($dothis == "rediger" && $id) {
	$pagedata = hentRow($id, "NEWS");
	$imageid = $pagedata[IMAGE_ID];
	if ($imageid == 0) {
		$imageid = "";	  	
	} else {
		$image_url = returnImageUrl($imageid);
	}
 }

 if ($dothis == "slet" && $id)
 {
  sletRow($id, "NEWS");
  update_feed("NEWSFEEDS", $_GET["newsfeedid"]);
  $filter_newsfeedid = $_GET["newsfeedid"];
  header("location: index.php?content_identifier=news&dothis=oversigt&newsfeedid=$filter_newsfeedid");
  exit;   
 }
  
 if ($dothis == "gem") 
 {
  gemRow(time(), $_POST, $_SESSION["CMS_USER"]["USER_ID"], "NEWS", 0);
  $filter_newsfeedid = $_POST["newsfeedid"];
  // save_tags($_POST[taglist], "NEWS", $_POST[det_nye_id], $_SESSION[SELECTED_SITE]);
  update_feed("NEWSFEEDS", $_POST[newsfeedid]);
  header("location: index.php?content_identifier=news&dothis=oversigt&newsfeedid=$filter_newsfeedid");
  exit;
 }
 
 if ($dothis == "newsfeed" && $id && $feed != "")
 {
  newsfeedState($id, $feed);
  $filter_newsfeedid = $_GET["newsfeedid"];
  header("location: index.php?content_identifier=news&dothis=oversigt&sortby=$sortby&sortdir=$sortdir&newsfeedid=$filter_newsfeedid");
  exit;
 }
 
 if ($dothis == "newsletter" && $id && $newsletter_id != "")
 {
  newsletterState($id, $newsletter_id);
  $filter_newsfeedid = $_GET["newsfeedid"];
  header("location: index.php?content_identifier=news&dothis=oversigt&sortby=$sortby&sortdir=$sortdir&newsfeedid=$filter_newsfeedid");
  exit;
 }

 if ($dothis == "frontpage" && $id && $state != "")
 {
  newsFrontpageState($id, $state);
  $filter_newsfeedid = $_GET["newsfeedid"];
  header("location: index.php?content_identifier=news&dothis=oversigt&sortby=$sortby&sortdir=$sortdir&newsfeedid=$filter_newsfeedid&offset=$offset");
  exit;
 }
 
?>