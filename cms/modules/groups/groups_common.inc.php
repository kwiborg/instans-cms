<?php 
	include_once($_SERVER[DOCUMENT_ROOT]."/cms/modules/users/users_common.inc.php");
	
	function groups_return_allowed_formfields($group_id){
		global $newsletter_allowed_db_fields_on_form;
		$sql = "
			select 
			ID, FIELD_NAME, TABLE_NAME, TEMPLATE_TAG, POSITION, TEMPLATETAG_ONLY, CMS_LABEL
			from NEWSLETTER_FORMFIELDS 
			order by TABLE_NAME asc, POSITION asc
		";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)){
			$field_names[$row[TABLE_NAME]][$row[FIELD_NAME]] = array(
				"FIELD_NAME" => $row["FIELD_NAME"],
				"FIELD_LABEL" => $row["CMS_LABEL"],
				"FIELD_ID" => $row["ID"],
				"TEMPLATETAG_ONLY" => $row["TEMPLATETAG_ONLY"]
			);
		}
		if (!is_array($field_names)) {
			return "<p>Ingen felter til defineret.</p>";
		}
		foreach($field_names as $tablename => $fields){
			$html .= "<table cellpadding='2' cellspacing='2'>";
			foreach ($fields as $db_fieldname => $fielddata){
				$sql = "select ID, MANDATORY from GROUPS_FORMFIELDS where FIELD_ID='".$fielddata[FIELD_ID]."' and GROUP_ID='$group_id'";
				$result_dbfields = mysql_query($sql);
				$row_dbfields = mysql_fetch_assoc($result_dbfields);
				$html .= "
					<tr>
						<td align='right'>".($fielddata[FIELD_LABEL] ? $fielddata[FIELD_LABEL] : $db_fieldname)."</td>
						<td align='center'>
							<select class='inputfelt_kort reg' name='fieldid_".$fielddata[FIELD_ID]."'>
								<option value='0'".(!mysql_num_rows($result_dbfields) ? " selected " : "").">Kan ikke udfyldes</option>
								<option value='1'".(($row_dbfields["ID"] && $row_dbfields["MANDATORY"]==0) ? " selected " : "").">Kan udfyldes</option>
								<option value='2'".(($row_dbfields["ID"] && $row_dbfields["MANDATORY"]==1) ? " selected " : "").">SKAL udfyldes</option>
							</select>
						</td>
					</tr>
				";
			}
			$html .= "</table>";
		}
		return $html;
	}
	
	function establishDbFieldIntersections($group_id){
		$sql = "delete from GROUPS_FORMFIELDS where GROUP_ID='$group_id'";
		mysql_query($sql);
		foreach ($_POST as $key => $val){
			if (strstr($key, "fieldid_")){
				$temp = explode("_", $key);
				$field_id = $temp[1];
				if ($val == 1){ 
					$sql = "
						insert into GROUPS_FORMFIELDS (GROUP_ID, FIELD_ID, MANDATORY) 
						values ('$group_id','$field_id', '0')
					";
					mysql_query($sql);
				}
				if ($val == 2){ 
					$sql = "
						insert into GROUPS_FORMFIELDS (GROUP_ID, FIELD_ID, MANDATORY) 
						values ('$group_id','$field_id', '1')
					";
					mysql_query($sql);
				}
			}
		}	
	}
	
	function groups_newslettergroup_only($user_id){
		$nlg = returnFieldValue("GENERAL_SETTINGS", "NEWSLETTER_GROUPID", "ID", $_SESSION[SELECTED_SITE]);
		$sql = "
			select 
				UG.GROUP_ID 
			from 
				GROUPS G, USERS_GROUPS UG 
			where
				UG.USER_ID='$user_id' and
				G.SITE_ID in (0,'$_SESSION[SELECTED_SITE]')
		";
		$res = mysql_query($sql);
		if (mysql_num_rows($res) == 1){
			$row = mysql_fetch_assoc($res);
			if ($row[GROUP_ID] == $nlg){
				return true;
			}
		} else {
			return false;
		}
	}
	
	function groups_notify_user($group_id, $current_id){
		$sql = "select DISTINCT U.ID, U.FIRSTNAME, U.LASTNAME, U.USERNAME from USERS U, GROUPS G, USERS_GROUPS UG where U.ID = UG.USER_ID and UG.GROUP_ID = G.ID and U.DELETED='0' and U.UNFINISHED='0' and G.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') order by U.FIRSTNAME asc, U.LASTNAME asc, U.USERNAME asc";
		$res = mysql_query($sql);
		$html .= "<select name='notify_user_id' class='inputfelt_kort reg'>";
		$html .= "<option value='0'>Ingen notificering</option>";
		while ($row = mysql_fetch_assoc($res)){
			if (!groups_newslettergroup_only($row[ID])){
				$html .= "<option value='".$row[ID]."' ".($current_id == $row[ID] ? "selected" : "").">$row[FIRSTNAME] $row[LASTNAME] ($row[USERNAME])</option>";
			} 
		}
		$html .= "</select>";
		return $html;
	}

	function groups_temp_group($group_id, $current_id){
		$sql = "select 
					ID, 
					GROUP_NAME 
				from 
					GROUPS 
				where 
					ID != '$group_id' and 
					DELETED='0' and 
					UNFINISHED='0' and 
					HIDDEN='0' and
					SITE_ID in (0,'$_SESSION[SELECTED_SITE]') 
				order by 
					GROUP_NAME asc, ID asc";
		$res = mysql_query($sql);
		$html .= "<select name='landing_group_id' class='inputfelt_kort reg'>";
		$html .= "<option value='0'>Ingen midlertidig placering</option>";
		while ($row = mysql_fetch_assoc($res)){
			$html .= "<option value='".$row[ID]."' ".($current_id == $row[ID] ? "selected" : "").">$row[GROUP_NAME]</option>";
		}
		$html .= "</select>";
		return $html;
	}

	function new_show_groupmembers($group_id){
		$sortby = $_GET[sortby];
		$sortdir = $_GET[sortdir];
		$sql = "
			SELECT DISTINCT U. *, UG.POSITION 
			FROM USERS U, USERS_GROUPS UG, GROUPS G
			WHERE 
				G.ID = UG.GROUP_ID and
				G.SITE_ID in (0,'$_SESSION[SELECTED_SITE]') and
				U.ID = UG.USER_ID and
				U.UNFINISHED = '0' and
				U.DELETED = '0' and
				UG.GROUP_ID = '$group_id'
		";
		if (!$sortdir){
	  		$sortdir = "DESC";
		}
		if ($sortby && $sortdir){
			if ($sortby == "POSITION"){
				$sql .= " order by UG.$sortby $sortdir";
			} else {
				$sql .= " order by U.$sortby $sortdir";
			}
		}
		$tomt_ikon = "<img src='images/piltom.gif' border='0'>";
		if ($sortdir == "DESC"){
			$sortdir = "ASC"; 
			$sortdir_changed=true;
			$ikon = "<img src='images/pilned.gif' border='0'>";
		}
		if ($sortdir == "ASC" && !$sortdir_changed){
			$sortdir = "DESC";
			$ikon = "<img src='images/pilop.gif' border='0'>";
		}
		$users_result = mysql_query($sql);
		$html .= "
			<table class='oversigt'>
				<tr class='trtop'>
					<td class='kolonnetitel'><a href='index.php?content_identifier=groups&dothis=medlemmer&sortby=FIRSTNAME&id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&usersearch=$searchwords' class='kolonnetitel'>Fornavn&nbsp;" . (($sortby=="FIRSTNAME") ?  $ikon : $tomt_ikon) . "</td>
					<td class='kolonnetitel'><a href='index.php?content_identifier=groups&dothis=medlemmer&sortby=LASTNAME&id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&usersearch=$searchwords' class='kolonnetitel'>Efternavn&nbsp;" . (($sortby=="LASTNAME") ?  $ikon : $tomt_ikon) . "</td>
					<td class='kolonnetitel'><a href='index.php?content_identifier=groups&dothis=medlemmer&sortby=USERNAME&id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&usersearch=$searchwords' class='kolonnetitel'>Brugernavn&nbsp;" . (($sortby=="USERNAME") ?  $ikon : $tomt_ikon) . "</td>
					<td class='kolonnetitel'><a href='index.php?content_identifier=groups&dothis=medlemmer&sortby=POSITION&id=$group_id&sortdir=$sortdir&filter_author=$filter_author&filter_time=$filter_time&filter_menu=$filter_menu&usersearch=$searchwords' class='kolonnetitel'>Position&nbsp;" . (($sortby=="POSITION") ?  $ikon : $tomt_ikon) . "</td>
					<!--<td class='kolonnetitel'>Funktioner</td>-->
				</tr>
	  	";
		if ($sortby == "POSITION"){
			$html .= "</table>";
		}
		while ($user_row = mysql_fetch_array($users_result)){
			if ($sortby == "POSITION"){
				$html .= "
					<div style='border:1px solid #aaa; padding:3px; margin:3px 0 3px 0' id='userpos_$user_row[ID]'>
						<table>
							<tr>
								<td>
									<input class='lilleknap' value='Fjern medlemskab' type='button' name='MEMBER_$user_row[ID]' id='MEMBER_$user_row[ID]' onclick='removeMember($user_row[ID], $group_id)' />
									<!--<input class='lilleknap' value='Rediger bruger' type='button' name='' id='' onclick='location=\"index.php?content_identifier=users&dothis=rediger&id=$user_row[ID]\"' />-->
								</td>
								<td><a href='index.php?content_identifier=users&dothis=rediger&id=$user_row[ID]'>$user_row[FIRSTNAME] $user_row[LASTNAME] ($user_row[USERNAME])</a></td>
							</tr>
						</table>
					</div>
				";
			} else {
				$html .= "
					<tr>
						<td>$user_row[FIRSTNAME]</td> 
						<td>$user_row[LASTNAME]</td>
						<td>$user_row[USERNAME]</td>
						<td>$user_row[POSITION]</td>
						<td>
							<input class='lilleknap' value='Fjern medlemskab' type='button' name='MEMBER_$user_row[ID]' id='MEMBER_$user_row[ID]' onclick='removeMember($user_row[ID], $group_id)' />
							<input class='lilleknap' value='Rediger bruger' type='button' name='' id='' onclick='location=\"index.php?content_identifier=users&dothis=rediger&id=$user_row[ID]\"' />
						</td>
					</tr>
				";
			}
		}
		if ($sortby != "POSITION"){
			$html .= "</table>";
		}
		return $html;
	}
 
	
	
?>