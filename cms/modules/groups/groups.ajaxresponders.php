<?php
	header("Content-type: text/html; charset=UTF-8");
	include_once($_SERVER[DOCUMENT_ROOT]."/cms_config.inc.php");
	include_once($_SERVER[DOCUMENT_ROOT]."/cms/common.inc.php");
	connect_to_db();
	switch ($_POST["do"]){
		case "reorder":
			$temp = explode("&", $_POST["order"]);
			foreach ($temp as $line){
				eval("$".$line.";");
			}
			foreach ($sortme as $id){
				$pos++;
				$sql = "update USERS_GROUPS set POSITION='$pos' where USER_ID='$id' and GROUP_ID='$_POST[group_id]'";
				mysql_query($sql);
			}
		break;
	}
?>