<?php  
 if (!$_SESSION["CMS_USER"]) header("location: ../../login.php");
checkPermission("CMS_EVENTS", true);

if ($dothis == "rediger" || $dothis == "slet" || $dothis == "gem") {
	if (!check_data_permission("DATA_CMS_EVENT_ACCESS", "EVENTS", $_GET["id"], "", $_SESSION["CMS_USER"]["USER_ID"])) {
		echo "Du har ikke adgang til at udføre denne funktion";
		exit;
	}
}

// Get current calendar
if ($dothis == "opret" || $dothis == "oversigt" || !$dothis) {
	if ((!isset($_GET[filter_calendar])) || ($_GET[filter_calendar]=="")) {
//echo "DB:";
		$sql = "select ID from CALENDARS order by NAME asc LIMIT 1";
		$result = mysql_query($sql);
		$filter_calendar = mysql_result($result,0);
	} else {
		$filter_calendar = $_GET[filter_calendar];
	}
}
 if ($dothis == "opret")
 {
  if (!$id)
  {
   $nyt_id = opretNyRow("EVENTS");
   header("location: index.php?content_identifier=$content_identifier&id=$nyt_id&filter_calendar=$filter_calendar&dothis=opret"); // kald siden igen, nu med ID
   exit;
  }  
 }
 
if ($dothis == "rediger" && $id) {
	$pagedata = hentRow($id, "EVENTS");
	$imageid = $pagedata[IMAGE_ID];
	if ($imageid == 0) {
		$imageid = "";	  	
	} else {
		$image_url = returnImageUrl($imageid);
	}
}

 if ($dothis == "slet" && $id)
 {
  sletRow($id, "EVENTS");
  header("location: index.php?content_identifier=$content_identifier&filter_calendar=$filter_calendar&dothis=oversigt");
  exit;   
 }
  
 if ($dothis == "gem") 
 {
  gemRow(time(), $_POST, $_SESSION["CMS_USER"]["USER_ID"], "EVENTS", 0);
	$filter_calendar = $_POST[calendar_id_res];
  header("location: index.php?content_identifier=$content_identifier&filter_calendar=$filter_calendar&dothis=oversigt");
  exit;
 }
 
 if ($dothis == "calendar" && $id && $calendar != "")
 {
  calendarState($id, $calendar);
  header("location: index.php?content_identifier=events&dothis=oversigt&sortby=$sortby&sortdir=$sortdir");
  exit;
 }
?>