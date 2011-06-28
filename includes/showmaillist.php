<?php
  include_once($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
  include_once($cmsAbsoluteServerPath."/common.inc.php");
  include_once($_SERVER[DOCUMENT_ROOT]."/includes/cms_plugins/maillist/maillist_common.inc.php");
  connect_to_db();
  if (!$_SESSION[CURRENT_LANGUAGE]){
  	$_SESSION[CURRENT_LANGUAGE] = $defaultLanguage;
  }

	$site_id = $_SESSION[CURRENT_SITE];
// Check if maillist exists and has been sent out
	$sql = "select 
				count(*) 
			from 
				MAILLISTLETTERS N, 
				MAILLIST_HISTORY NH,
				MAILLIST_TEMPLATES NT
			where
				N.ID = NH.MAILLIST_ID and
				N.ID = '$_GET[maillistid]' and
				NH.SENDOUT_COMPLETETIME > 0 and
				N.DELETED = 0 and 
				NT.SITE_ID in (0,'$site_id')";
	$res = mysql_query($sql);
	if (mysql_result($res,0) == 0) {
		echo "Du har ikke adgang til at se denne mail.";
		exit;
	}	
				

// Get maillist main data (headertext etc)
$arr_maindata = returnMaillistMaindataArray($_GET[maillistid]);

$allowed = returnFieldValue("MAILLIST_TEMPLATES", "SHOW_IN_NEWSARCHIVE", "ID", $arr_maindata[MAILLISTTEMPLATE_ID]);
if ($allowed == 0) {
	$err = "Du har ikke adgang til at se denne mail.";
	$ticket = md5($arr_maindata[MAILLISTTEMPLATE_ID]."tid");
	if ($_GET[ticket] != $ticket) {
		$err .= " (wrong ticket)";
		echo $err;
		exit;
	}
}

// Get maillist items
$arr_items = returnMaillistItemArray($_GET[maillistid], $arr_maindata[IMAGES_DISPLAY]);

// Get formfields (for dynamic replacement of template tags)
$arr_formfields = returnFormfields();

// Get rendered mail content
$arr_content = maillist_build($_GET[maillistid], "site", $_SESSION[CURRENT_LANGUAGE], $arr_maindata, $arr_items, $arr_formfields);
$html = $arr_content[0];

// Add maillist stylesheet
$replace = "<head>";
$replace_with = "<head>
	<title>$arr_maindata[ARCHIVE_TITLE]</title>
	<link rel='stylesheet' type='text/css' href='$cmsDomain/includes/cms_plugins/maillist/frontend/maillist.css' />";
$html = str_replace($replace,$replace_with,$html);

// Add table to top of maillist
$replace = "</style>";

$replace_with = "</style><table class='maillist_show_topbar'><tr><td>".cmsTranslateBackend($_SESSION[CURRENT_LANGUAGE], "MaillistReading")." \"";
if ($arr_maindata[ARCHIVE_TITLE] == "") {
	$replace_with .= $arr_maindata[TITLE];
} else {
	$replace_with .= $arr_maindata[ARCHIVE_TITLE];
}
$replace_with .= "\".<br /><a href='../index.php?mode=maillist&amp;maillistid=$arr_maindata[MAILLISTTEMPLATE_ID]'>".cmsTranslateBackend($_SESSION[CURRENT_LANGUAGE], "MaillistBackToArchive")."</a></td></tr></table>";
$html = str_replace($replace,$replace_with,$html);


// Add end-table to bottom of maillist
$replace = "</body>";
$replace_with = "<table class='maillist_show_topbar'><tr><td>".cmsTranslateBackend($_SESSION[CURRENT_LANGUAGE], "MaillistReading")." \"";
if ($arr_maindata[ARCHIVE_TITLE] == "") {
	$replace_with .= $arr_maindata[TITLE];
} else {
	$replace_with .= $arr_maindata[ARCHIVE_TITLE];
}
$replace_with .= "\".<br /><a href='../index.php?mode=maillist&amp;maillistid=$arr_maindata[MAILLISTTEMPLATE_ID]'>".cmsTranslateBackend($_SESSION[CURRENT_LANGUAGE], "MaillistBackToArchive")."</a></td></tr></table></body>";
$html = str_replace($replace,$replace_with,$html);

echo $html;
?>