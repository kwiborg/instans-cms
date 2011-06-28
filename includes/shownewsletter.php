<?php
  include_once($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
  //include_once($cmsAbsoluteServerPath."/common.inc.php");
  include($cmsAbsoluteServerPath."/sharedfunctions.inc.php");
  include($cmsAbsoluteServerPath."/frontend/frontend_common.inc.php");
  require_once($_SERVER[DOCUMENT_ROOT]."/cms/scripts/smarty/libs/Smarty.class.php");	

  include_once($cmsAbsoluteServerPath."/modules/newsletter/newsletter_common.inc.php");
  connect_to_db();
  if (!$_SESSION[CURRENT_LANGUAGE]){
  	$_SESSION[CURRENT_LANGUAGE] = $defaultLanguage;
  }

	$site_id = $_SESSION[CURRENT_SITE];

// Check if newsletter exists and has been sent out
	$sql = "select 
				count(*) 
			from 
				NEWSLETTERS N, 
				NEWSLETTER_HISTORY NH,
				NEWSLETTER_TEMPLATES NT
			where
				N.TEMPLATE_ID = NT.ID and
				N.ID = NH.NEWSLETTER_ID and
				N.ID = '$_GET[newsletterid]' and
				NH.SENDOUT_COMPLETETIME > 0 and
				N.DELETED = 0 ".($site_id  ? "and 
				NT.SITE_ID in (0,'$site_id')" : "");
	$res = mysql_query($sql);
	if (mysql_result($res,0) == 0) {
		echo "Du har ikke adgang til at se dette nyhedsbrev.";
		exit;
	}	
			
// Get newsletter main data (headertext etc)
$arr_maindata = returnNewsletterMaindataArray($_GET[newsletterid]);
//print_r($arr_maindata);
$allowed = returnFieldValue("NEWSLETTER_TEMPLATES", "SHOW_IN_NEWSARCHIVE", "ID", $arr_maindata[NEWSLETTERTEMPLATE_ID]);
if ($allowed == 0) {
	$err = "Du har ikke adgang til at se dette nyhedsbrev.";
	$ticket = md5($_GET[newsletterid]."tid");
	if ($_GET[ticket] != $ticket) {
		$err .= " (wrong ticket)";
		echo $err;
		exit;
	}
}

// Get newsletter items
$arr_items = returnNewsletterItemArray($_GET[newsletterid], $arr_maindata[IMAGES_DISPLAY]);

// Get formfields (for dynamic replacement of template tags)
$arr_formfields = returnFormfields();

// Get renderes newsletter content
$arr_content = newsletter_build($_GET[newsletterid], "site", $_SESSION[CURRENT_LANGUAGE], $arr_maindata, $arr_items, $arr_formfields);
$html = $arr_content[0];

// Add newsletter stylesheet
$replace = "<head>";
$replace_with = "<head>
	<meta http-equiv='content-type' content='text/html;charset=UTF-8' />
	<title>$arr_maindata[ARCHIVE_TITLE]</title>
	<link rel='stylesheet' type='text/css' href='$cmsURL/modules/newsletter/frontend/newsletter.css' />";
$html = str_replace($replace,$replace_with,$html);

// Add table to top of newsletter
$replace = "</style>";

$replace_with = "</style><table class='newsletter_show_topbar'><tr><td>".cmsTranslate($_SESSION[CURRENT_LANGUAGE], "NewsletterReading")." \"";
if ($arr_maindata[ARCHIVE_TITLE] == "") {
	$replace_with .= $arr_maindata[TITLE];
} else {
	$replace_with .= $arr_maindata[ARCHIVE_TITLE];
}
$replace_with .= "\".<br /><a href='/index.php?mode=newsletter&amp;newsletterid=$arr_maindata[NEWSLETTERTEMPLATE_ID]'>".cmsTranslate($_SESSION[CURRENT_LANGUAGE], "NewsletterBackToArchive")."</a></td></tr></table>";
$html = str_replace($replace,$replace_with,$html);


// Add end-table to bottom of newsletter
$replace = "</body>";
$replace_with = "<table class='newsletter_show_topbar'><tr><td>".cmsTranslate($_SESSION[CURRENT_LANGUAGE], "NewsletterReading")." \"";
if ($arr_maindata[ARCHIVE_TITLE] == "") {
	$replace_with .= $arr_maindata[TITLE];
} else {
	$replace_with .= $arr_maindata[ARCHIVE_TITLE];
}
$replace_with .= "\".<br /><a href='/index.php?mode=newsletter&amp;newsletterid=$arr_maindata[NEWSLETTERTEMPLATE_ID]'>".cmsTranslate($_SESSION[CURRENT_LANGUAGE], "NewsletterBackToArchive")."</a></td></tr></table></body>";
$html = str_replace($replace,$replace_with,$html);

echo $html;
?>