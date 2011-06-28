<?php
header("Content-type: text/html; charset=UTF-8");
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/sharedfunctions.inc.php');
//$current_site_id = $_SESSION[SELECTED_SITE];
//$current_basepath = returnBASE_URL($current_site_id);
checkLoggedIn();

switch ($_REQUEST['do']) {
	case "ajax_comment_approve":
		if (comment_approve($_GET[comment_id])) {
			echo "SUCCESS|||Kommentar godkendt|||$_GET[comment_id]";
		} else {
			echo "ERROR|||Der opstod en fejl og kommentaren blev ikke godkendt|||$_GET[comment_id]";
		}
		break;
	case "ajax_comment_reject":
		if (comment_reject($_GET[comment_id])) {
			echo "SUCCESS|||Kommentar afvist|||$_GET[comment_id]";
		} else {
			echo "ERROR|||Der opstod en fejl og kommentaren blev ikke afvist|||$_GET[comment_id]";
		}
		break;
	case "ajax_comment_whitelist":
		$arr_whiteliststatus = add_whitelist(false, "BLOGS", $_GET[blog_id], $_GET[comment_id]);
		$commenter_email = $arr_whiteliststatus[0];
		if (comment_approve($_GET[comment_id])) {
			echo "SUCCESS|||Kommentar godkendt og $commenter_email er whitelisted|||$_GET[comment_id]";
		} else {
			echo "ERROR|||Der opstod en fejl og kommentaren blev ikke godkendt|||$_GET[comment_id]";
		}
		break;
	case "ajax_comment_whitelist_revoke":
		$arr_whiteliststatus = remove_whitelist($_GET[commenter_email], "BLOGS", $_GET[blog_id], false);
		$commenter_email = $arr_whiteliststatus[0];
		if (comment_reject($_GET[comment_id])) {
			echo "SUCCESS|||Kommentar afvist og $commenter_email er fjernet fra whitelist|||$_GET[comment_id]";
		} else {
			echo "ERROR|||Der opstod en fejl og kommentaren blev ikke afvist|||$_GET[comment_id]";
		}
		break;
	case "ajax_comment_isspam":
		if (comment_markspam($_GET[comment_id]) && comment_reject($_GET[comment_id])) {
			echo "SUCCESS|||Kommentar markeret som spam|||$_GET[comment_id]";
		} else {
			echo "ERROR|||Der opstod en fejl og kommentaren blev ikke markeret som spam|||$_GET[comment_id]";
		}
		break;
	case "ajax_comment_isham":
		if (comment_markham($_GET[comment_id]) && comment_approve($_GET[comment_id])) {
			echo "SUCCESS|||Kommentarens spam-markering er fjernet|||$_GET[comment_id]";
		} else {
			echo "ERROR|||Der opstod en fejl og kommentaren er stadig markeret som spam|||$_GET[comment_id]";
		}
		break;
	case "ajax_comment_editsave":
		if ($str_updatetext = updatecommenttext($_POST[comment_id], $_POST[commenttext])) {
			echo $str_updatetext;
		} else {
			echo "Der opstod en fejl og kommentar-teksten blev ikke opdateret.";
		}
		break;
	case "ajax_comment_delete":
		sletRow($_GET[comment_id], "COMMENTS");
		echo "SUCCESS|||Kommentaren er slettet|||$_GET[comment_id]";
		break;
		
}

function updatecommenttext($int_commentid, $str_commenttext) {
	// Function to update commenttext from CMS
	$str_commenttext = trim($str_commenttext);
	if ($str_commenttext == "") {
		return "";
	}
	$str_commenttext =  db_safedata(rawurldecode($str_commenttext));
//	$str_commenttext =  db_safedata(utf8_decode(rawurldecode($str_commenttext)));
	$sql = "update COMMENTS set COMMENT = '$str_commenttext' where ID = $int_commentid";
	if (mysql_query($sql)) {
		return unhtmlentities(stripslashes($str_commenttext));
	} else {
		return false;
	}
}

function comment_approve($comment_id){
	$sql = "update COMMENTS set APPROVED = 1 where ID = $comment_id";
	if (mysql_query($sql)) {
		return true;
	} else {
		return false;
	}
}

function comment_reject($comment_id){
	$sql = "update COMMENTS set APPROVED = 0 where ID = $comment_id";
	if (mysql_query($sql)) {
		return true;
	} else {
		return false;
	}
}

function comment_markspam($comment_id){
	$sql = "update COMMENTS set IS_SPAM = 1 where ID = $comment_id";
	if (mysql_query($sql)) {
		return true;
	} else {
		return false;
	}
}

function comment_markham($comment_id){
	$sql = "update COMMENTS set IS_SPAM = 0 where ID = $comment_id";
	if (mysql_query($sql)) {
		return true;
	} else {
		return false;
	}
}


?>