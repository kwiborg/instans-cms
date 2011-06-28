<?php
	header("Content-type: text/html; charset=UTF-8");
	include_once($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
	include_once($_SERVER[DOCUMENT_ROOT]."/cms/common.inc.php");
	include_once($_SERVER[DOCUMENT_ROOT]."/cms/modules/tags/tags_common.inc.php");
	connect_to_db();
	switch ($_POST["do"]){
		case "fetch_tags":
			echo AJAX_return_suggested_tags($_POST[letters], $_POST[allusedtags], $_POST[site_id]);
			break;
		case "ajax_tag_editsave":
			$sql = "update TAGS set TAGNAME = '$_POST[tagname]' where ID = '$_POST[tag_id]'";
			if (mysql_query($sql)) {
				echo stripslashes($_POST[tagname]);
			} else {
				echo "Fejl: Ikke omdøbt!";
			}
			break;
	}
?>