<?php
	if ($arr_content[action] == "register" && $arr_content[groupid]){
		$html .= groups_registration_form($arr_content[groupid]);
	} else if ($arr_content[action] == "pending" && $arr_content[groupid]){
		$html .= groups_pending_receipt($arr_content[groupid]);
	} else if ($arr_content[action] == "registered" && $arr_content[groupid]){
		$html .= groups_registration_receipt($arr_content[groupid]);
	} else if ($arr_content[action] == "edit_details" && $_SESSION[LOGGED_IN]){
		$html .= groups_registration_form($arr_content[groupid], "EDIT");
	} 
	echo $html;
?>
