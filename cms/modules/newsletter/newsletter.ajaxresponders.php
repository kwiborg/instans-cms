<?php
header("Content-type: text/html; charset=UTF-8");
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
include_once ('newsletter_common.inc.php');
checkLoggedIn();

/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
*/
switch ($_POST['do']) {
	case 'ajax_saveReordered':
		saveReordered();
		break;
	case 'ajax_returnSingleitem':
		// Used for returning a single ORIGINAL contentitem
		echo returnSingleItem($_POST[type], $_POST[id], $_POST[summary], $_POST[rich]);
		break;
	case 'ajax_saveNewsletterItem':
		saveNewsletterItem();
		break;
	case "ajax_updateItemlist":
		echo list_newsletter_items($_POST[newsletter_id]);
		break;
	case "ajax_deleteItem":
		sletRow($_POST[id], "NEWSLETTER_ITEMS");
		break;
	case "ajax_loadSingleItem":
		echo returnSingleNewsletterItem($_POST[id]);
		break;
	case "ajax_updateInterestgroup":
		echo updateInterestgroup($_POST[nid], $_POST[id], $_POST[mode], $_POST[ntid], $_POST[temp]);
		break;
	case "ajax_InitNewsletterSend":
		$history_id = new_newsletter_history($_POST[nid]);
		if ($history_id != 0) {
			echo "SUCCESS|||||$history_id";
		} else {
			echo "ERROR|||||Kunne ikke initialisere udsendelse!";
		}
		break;
	case "ajax_buildNewsletterRecipientlist":
		if (build_newsletter_recipientlist($_POST[hid])) {
			echo "SUCCESS|||||$_POST[hid]";
		} else {
			echo "ERROR|||||Kunne ikke oprette modtagerliste!";
		}
		break;
	case "ajax_newsletterSendout_do":
		$arr_result = newsletter_sendout_do($_POST[hid],$_POST[batch]);
		if ($arr_result) {
			$response = "SUCCESS|||||".$arr_result[0]."|||||$_POST[hid]|||||";
			$recipients = array_reverse($arr_result[1]);
			foreach ($recipients as $recipient) {
				$response .= "<div>$recipient[FIRSTNAME] $recipient[LASTNAME] &lt;$recipient[EMAIL]&gt;</div>\n";
			}
			echo $response;
		} else {
			echo "ERROR|||||Der opstod en fejl i udsendelsen!";
		}
		break;
	case "ajax_newsletterSendoutCleanup":
		if (newsletter_sendout_cleanup($_POST[hid])) {
			echo "SUCCESS|||||$_POST[hid]";
		} else {
			echo "ERROR|||||Der opstod en fejl i udsendelsen!";
		}
		break;
}
?>