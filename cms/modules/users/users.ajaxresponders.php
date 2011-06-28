<?php
	include_once ($_SERVER['DOCUMENT_ROOT'].'/cms/common.inc.php');
	connect_to_db();
	header("Content-type: text/html; charset=UTF-8");
	switch($_POST[action]){
		case "check_if_user_exists":
			$sql = "select ID from USERS where USERNAME='$_POST[username]' and ID != '$_POST[userid]' and DELETED='0' and UNFINISHED='0'";
			$result = mysql_query($sql);
			echo mysql_num_rows($result);
		break;
	}
?>