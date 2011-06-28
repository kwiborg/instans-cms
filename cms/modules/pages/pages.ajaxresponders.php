<?php
header("Content-type: text/html; charset=UTF-8");
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
checkLoggedIn();
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/modules/pages/pages_common.inc.php');

switch ($_POST['do']) {
	// Related pages
	case 'ajax_returnMenuslist':
		echo returnMenuslist();
		break;
	case 'ajax_returnAttachedPages':
		echo returnAttachedPages($_POST[page_id],"PAGES");
		break;
	case 'ajax_returnAvailablePages':
		echo "<table class='oversigt'>";
		echo returnAvailablePages("", 0,0, $_POST[menu_id]);
		echo "</table>";
		break;
	case 'ajax_addRelatedPage':
		echo addRelatedContent("PAGES", $_POST[page_id], "PAGES", $_POST[rel_id]);
		break;
	case 'ajax_removeRelatedPage':
		echo removeRelatedContent("PAGES", $_POST[page_id], "PAGES", $_POST[rel_id]);
		break;
	// Related news
	case 'ajax_addRelatedNews':
		echo addRelatedContent("PAGES", $_POST[page_id], "NEWS", $_POST[rel_id]);
		break;
	case 'ajax_removeRelatedNews':
		echo removeRelatedContent("PAGES", $_POST[page_id], "NEWS", $_POST[rel_id]);
		break;
	// Related events
	case 'ajax_addRelatedEvent':
		echo addRelatedContent("PAGES", $_POST[page_id], "EVENTS", $_POST[rel_id]);
		break;
	case 'ajax_removeRelatedEvent':
		echo removeRelatedContent("PAGES", $_POST[page_id], "EVENTS", $_POST[rel_id]);
		break;
	// Related Boxes
	case 'ajax_attachRelatedBoxcustom':
		echo addRelatedContent("PAGES", $_POST[page_id], "CUSTOM_BOXES", $_POST[rel_id]);
		break;
	case 'ajax_removeRelatedBoxcustom':
		echo removeRelatedContent("PAGES", $_POST[page_id], "CUSTOM_BOXES", $_POST[rel_id]);
		break;
	case 'ajax_attachRelatedBoxnormal':
		echo modifyRelatedBoxesNormal($_POST[page_id], $_POST[rel_id], "attach");
		break;
	case 'ajax_removeRelatedBoxnormal':
		echo modifyRelatedBoxesNormal($_POST[page_id], $_POST[rel_id], "remove");
		break;
	case 'ajax_returnRewriteKeyword':
		if ($_POST[heading] != "") {
			$key = return_rewrite_keyword($_POST[heading], $_POST[pageid], $_POST[tablename], $_POST[siteid]);
		}
		save_rewrite_keyword($key, $_POST[pageid], $_POST[tablename], $_POST[siteid]);
		echo $key;
	break;

}
?>